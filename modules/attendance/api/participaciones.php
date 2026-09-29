<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/socket_client.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Option to filter by user or event
        $evento_id = $_GET['evento_id'] ?? null;
        $user_id = $_GET['user_id'] ?? null;

        $sql = "SELECT p.*, e.nombre as evento_nombre, u.name as usuario_nombre 
                FROM participaciones_evento p 
                JOIN eventos e ON p.evento_id = e.id 
                JOIN users u ON p.user_id = u.id 
                WHERE 1=1";
        
        $params = [];
        if ($evento_id) { $sql .= " AND p.evento_id = ?"; $params[] = $evento_id; }
        if ($user_id)   { $sql .= " AND p.user_id = ?";   $params[] = $user_id; }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $participaciones = $stmt->fetchAll();
        
        echo json_encode(['status' => 'success', 'data' => $participaciones]);
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        
        $evento_id = $data->evento_id ?? null;
        $user_id = $data->user_id ?? null;
        $puntos_obtenidos = $data->puntos_obtenidos ?? 0;
        $user_lat = $data->user_lat ?? null;
        $user_lng = $data->user_lng ?? null;
        $foto_base64 = $data->foto_base64 ?? null;
        $hora_entrada = $data->hora_entrada ?? null;  // Timestamp del cliente
        $precision_gps = $data->precision_gps ?? null; // Precisión GPS en metros

        if (empty($evento_id) || empty($user_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'evento_id y user_id son obligatorios']);
            exit;
        }

        // VALIDACIÓN DE ASISTENCIA DUPLICADA
        $stmt_check = $pdo->prepare("SELECT id FROM participaciones_evento WHERE evento_id = ? AND user_id = ?");
        $stmt_check->execute([$evento_id, $user_id]);
        if ($stmt_check->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya registró su asistencia para este evento.']);
            exit;
        }

        // --- PROCESAMIENTO DE FOTO ---
        $foto_path = null;
        if ($foto_base64) {
            $img_parts = explode(";base64,", $foto_base64);
            if (count($img_parts) === 2) {
                $img_type_aux = explode("image/", $img_parts[0]);
                $img_type = $img_type_aux[1] ?? 'jpg';
                $img_base64 = base64_decode($img_parts[1]);
                $file_name = "asistencia_" . $user_id . "_" . $evento_id . "_" . time() . "." . $img_type;
                $file_path = "../../../uploads/asistencias/" . $file_name;
                
                if (file_put_contents($file_path, $img_base64)) {
                    $foto_path = "uploads/asistencias/" . $file_name;
                }
            }
        }

        // VALIDACIÓN DE GEOLOCALIZACIÓN
        $stmt = $pdo->prepare("SELECT e.distancia_maxima_m, s.latitud, s.longitud 
                              FROM eventos e 
                              JOIN sedes s ON e.sede_id = s.id 
                              WHERE e.id = ?");
        $stmt->execute([$evento_id]);
        $geo = $stmt->fetch();

        if ($geo && $geo['latitud'] && $geo['longitud']) {
            if ($user_lat === null || $user_lng === null) {
                http_response_code(400);
                echo json_encode(['error' => 'Se requiere acceso a su ubicación para marcar asistencia.']);
                exit;
            }

            // Distancia en metros (Fórmula Haversine)
            $earthRadius = 6371000;
            $latFrom = deg2rad($user_lat);
            $lonFrom = deg2rad($user_lng);
            $latTo = deg2rad($geo['latitud']);
            $lonTo = deg2rad($geo['longitud']);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            $distance = $angle * $earthRadius;

            $maxDistance = $geo['distancia_maxima_m'] ?? 500;

            if ($distance > $maxDistance) {
                http_response_code(403);
                echo json_encode([
                    'error' => 'Está demasiado lejos de la sede del evento.',
                    'distancia_actual' => round($distance, 2) . 'm',
                    'permitido' => $maxDistance . 'm'
                ]);
                exit;
            }
        }

        $ip_equipo = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO participaciones_evento 
                               (evento_id, user_id, puntos_obtenidos, estado, hora_entrada, foto_validacion, latitud_registro, longitud_registro, precision_gps, ip_equipo, user_agent) 
                               VALUES (?, ?, ?, 'validado', ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$evento_id, $user_id, $puntos_obtenidos, $hora_entrada, $foto_path, $user_lat, $user_lng, $precision_gps, $ip_equipo, $user_agent]);

        $id = $pdo->lastInsertId();

        // --- REALTIME TRIGGER ---
        // Fetch full data for broadcasting (Screaming Architecture needs context)
        $stmtData = $pdo->prepare("SELECT p.*, e.nombre as evento_nombre, u.name as usuario_nombre 
                                    FROM participaciones_evento p 
                                    JOIN eventos e ON p.evento_id = e.id 
                                    JOIN users u ON p.user_id = u.id 
                                    WHERE p.id = ?");
        $stmtData->execute([$id]);
        $fullData = $stmtData->fetch();
        if ($fullData) {
            broadcast_event('attendance_registered', $fullData);
        }

        echo json_encode([
            'status' => 'success', 
            'message' => 'Participación registrada exitosamente',
            'id' => $id,
            'foto' => $foto_path
        ]);
    }
    else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
