<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);

$method = $_SERVER['REQUEST_METHOD'];

// Los coordinadores y supervisores también pueden ver la información
$allowedRoles = ['admin', 'superadmin', 'coordinador', 'supervisor'];

$mis_eventos = isset($_GET['mis_eventos']) ? 1 : 0;

if ($method !== 'GET' && !in_array($user['role'], ['admin', 'superadmin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para esta acción']);
    exit;
}
// Allow GET if it's explicitly fetching their own assigned events (mis_eventos=1)
if ($method === 'GET' && !$mis_eventos && !in_array($user['role'], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver esta información general']);
    exit;
}

try {
    if ($method === 'GET') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        $params = [];
        $where = "WHERE e.deleted_at IS NULL";

        if ($search) {
            $where .= " AND (e.nombre LIKE :search OR e.descripcion LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // Restricción si se pide explícitamente "mis eventos" (para la vista de asistencia)
        $join = "";
        if ($mis_eventos) {
            $join = "INNER JOIN eventos_usuarios ep ON e.id = ep.evento_id";
            $where .= " AND ep.user_id = :user_id";
            // Filter by date and time: Only show events that are happening NOW or within their range
            $where .= " AND NOW() BETWEEN e.fecha_inicio AND e.fecha_fin";
            $params[':user_id'] = $user['id'];
        }

        $totalStmt = $pdo->prepare("SELECT COUNT(DISTINCT e.id) FROM eventos e $join $where");
        foreach ($params as $key => $val) {
            $totalStmt->bindValue($key, $val);
        }
        $totalStmt->execute();
        $totalRecords = (int)$totalStmt->fetchColumn();

        $sql = "SELECT DISTINCT e.*, a.nombre as area_nombre, s.nombre as sede_nombre 
                FROM eventos e 
                LEFT JOIN areas_servicio a ON e.area_id = a.id 
                LEFT JOIN sedes s ON e.sede_id = s.id 
                $join 
                $where ORDER BY e.id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $eventos = $stmt->fetchAll();
        
        echo json_encode([
            'status' => 'success', 
            'data' => $eventos, 
            'total_records' => $totalRecords,
            'page' => $page,
            'limit' => $limit
        ]);
        exit;
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        $nombre = $data->nombre ?? '';
        $descripcion = $data->descripcion ?? '';
        $area_id = $data->area_id ?? null;
        $sede_id = $data->sede_id ?? null;
        $fecha_inicio = $data->fecha_inicio ?? null;
        $fecha_fin = $data->fecha_fin ?? null;
        $puntos = $data->puntos_base ?? 0;
        $distancia = $data->distancia_maxima_m ?? 500;

        $es_recurrente = $data->es_recurrente ?? false;
        $dias_semana = $data->dias_semana ?? [];

        if ($es_recurrente && !empty($dias_semana)) {
            $startRange = new DateTime(str_replace('T', ' ', $fecha_inicio));
            $endRange = new DateTime(str_replace('T', ' ', $fecha_fin));
            
            $startTime = $startRange->format('H:i:s');
            $endTime = $endRange->format('H:i:s');
            
            $periodEnd = clone $endRange;
            $periodEnd->modify('+1 day'); 

            $interval = new DateInterval('P1D');
            $period = new DatePeriod($startRange, $interval, $periodEnd);

            $serie_id = uniqid('ser_', true);

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO eventos (serie_id, nombre, descripcion, area_id, sede_id, fecha_inicio, fecha_fin, puntos_base, distancia_maxima_m) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($period as $date) {
                    $dayOfWeek = $date->format('w'); 
                    if (in_array((string)$dayOfWeek, $dias_semana)) {
                        $currentDate = $date->format('Y-m-d');
                        $fullStart = "$currentDate $startTime";
                        $fullEnd = "$currentDate $endTime";
                        $stmt->execute([$serie_id, $nombre, $descripcion, $area_id, $sede_id, $fullStart, $fullEnd, $puntos, $distancia]);
                    }
                }
                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Serie de eventos creados']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO eventos (nombre, descripcion, area_id, sede_id, fecha_inicio, fecha_fin, puntos_base, distancia_maxima_m) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $area_id, $sede_id, str_replace('T', ' ', $fecha_inicio), str_replace('T', ' ', $fecha_fin), $puntos, $distancia]);
            echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
        }
        exit;
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"));
        $id = $data->id ?? null;
        $editar_serie = $data->editar_serie ?? false;
        $serie_id = $data->serie_id ?? null;

        if ($editar_serie && $serie_id) {
             $startT = str_replace('T', ' ', $data->fecha_inicio);
             $endT = str_replace('T', ' ', $data->fecha_fin);
             $startTime = date('H:i:s', strtotime($startT));
             $endTime = date('H:i:s', strtotime($endT));

             $stmt = $pdo->prepare("UPDATE eventos SET 
                nombre = ?, descripcion = ?, area_id = ?, sede_id = ?, 
                fecha_inicio = CONCAT(DATE(fecha_inicio), ' ', ?), 
                fecha_fin = CONCAT(DATE(fecha_fin), ' ', ?), 
                puntos_base = ?, distancia_maxima_m = ? 
                WHERE serie_id = ?");
             $stmt->execute([
                $data->nombre, $data->descripcion, $data->area_id, $data->sede_id, 
                $startTime, $endTime, 
                $data->puntos_base, $data->distancia_maxima_m ?? 500, $serie_id
             ]);
             echo json_encode(['status' => 'success', 'message' => 'Serie actualizada']);
        } else {
            $stmt = $pdo->prepare("UPDATE eventos SET nombre = ?, descripcion = ?, area_id = ?, sede_id = ?, fecha_inicio = ?, fecha_fin = ?, puntos_base = ?, distancia_maxima_m = ? WHERE id = ?");
            $stmt->execute([
                $data->nombre, $data->descripcion, $data->area_id, $data->sede_id, 
                str_replace('T', ' ', $data->fecha_inicio), 
                str_replace('T', ' ', $data->fecha_fin), 
                $data->puntos_base, $data->distancia_maxima_m ?? 500, $id
            ]);
            echo json_encode(['status' => 'success']);
        }
        exit;
    }
    elseif ($method === 'DELETE') {
        // Support bulk delete: ?ids=1,2,3 or ?id=1
        $ids_raw = $_GET['ids'] ?? $_GET['id'] ?? null;
        if (!$ids_raw) {
            http_response_code(400);
            echo json_encode(['error' => 'ID(s) required']);
            exit;
        }
        // Sanitize: only integers allowed
        $ids = array_filter(array_map('intval', explode(',', $ids_raw)));
        if (empty($ids)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid IDs provided']);
            exit;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("UPDATE eventos SET deleted_at = NOW() WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        echo json_encode(['status' => 'success', 'deleted' => count($ids)]);
    }
    elseif ($method === 'PATCH') {
        // Bulk edit: update specific fields for multiple events
        if (!in_array($user['role'], ['admin', 'superadmin'])) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permisos']);
            exit;
        }
        $data = json_decode(file_get_contents("php://input"));
        $ids = array_filter(array_map('intval', $data->ids ?? []));
        if (empty($ids)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid IDs']);
            exit;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $setClauses = [];
        $params = [];
        if (isset($data->puntos_base))     { $setClauses[] = "puntos_base = ?";      $params[] = (int)$data->puntos_base; }
        if (isset($data->distancia_maxima_m)) { $setClauses[] = "distancia_maxima_m = ?"; $params[] = (int)$data->distancia_maxima_m; }
        if (isset($data->area_id))         { $setClauses[] = "area_id = ?";          $params[] = (int)$data->area_id; }
        if (isset($data->sede_id))         { $setClauses[] = "sede_id = ?";          $params[] = (int)$data->sede_id; }
        if (isset($data->fecha_inicio))    { $setClauses[] = "fecha_inicio = ?";     $params[] = str_replace('T', ' ', $data->fecha_inicio); }
        if (isset($data->fecha_fin))       { $setClauses[] = "fecha_fin = ?";        $params[] = str_replace('T', ' ', $data->fecha_fin); }

        if (empty($setClauses)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            exit;
        }
        $sql = "UPDATE eventos SET " . implode(', ', $setClauses) . " WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($params, $ids));
        echo json_encode(['status' => 'success', 'updated' => count($ids)]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
