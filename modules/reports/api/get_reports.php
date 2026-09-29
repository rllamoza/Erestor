<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);

// Role check
$allowedRoles = ['admin', 'superadmin', 'coordinador', 'supervisor'];
if (!in_array($user['role'], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver reportes']);
    exit;
}

try {
    // 1. Comprehensive Users List
    $sqlUsers = "SELECT u.id, u.name, u.email, u.role, u.activo, 
                        r.nombre as red_nombre, 
                        a.nombre as area_nombre, 
                        s.nombre as sede_nombre 
                 FROM users u 
                 LEFT JOIN redes r ON u.red_id = r.id 
                 LEFT JOIN areas_servicio a ON u.area_id = a.id 
                 LEFT JOIN sedes s ON u.sede_id = s.id";
    $stmt = $pdo->query($sqlUsers);
    $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Chart Data: By Network
    $sqlNetwork = "SELECT r.nombre as label, COUNT(u.id) as count 
                   FROM users u 
                   LEFT JOIN redes r ON u.red_id = r.id 
                   GROUP BY r.nombre";
    $stmt = $pdo->query($sqlNetwork);
    $chartNetwork = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Chart Data: By Area
    $sqlArea = "SELECT a.nombre as label, COUNT(u.id) as count 
                FROM users u 
                LEFT JOIN areas_servicio a ON u.area_id = a.id 
                GROUP BY a.nombre";
    $stmt = $pdo->query($sqlArea);
    $chartArea = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Chart Data: By Role
    $sqlRole = "SELECT role as label, COUNT(id) as count 
                FROM users 
                GROUP BY role";
    $stmt = $pdo->query($sqlRole);
    $chartRole = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Event Attendance Report
    $sqlEvents = "SELECT id, nombre, fecha_inicio FROM eventos ORDER BY fecha_inicio DESC";
    $stmt = $pdo->query($sqlEvents);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $eventsAttendance = [];
    foreach ($events as $evento) {
        $eventoId = $evento['id'];
        
        // Asignados
        $sqlAssigned = "SELECT u.id, u.name, u.email, u.role, r.nombre as red_nombre 
                        FROM eventos_usuarios eu 
                        JOIN users u ON eu.user_id = u.id 
                        LEFT JOIN redes r ON u.red_id = r.id
                        WHERE eu.evento_id = ?";
        $stmtA = $pdo->prepare($sqlAssigned);
        $stmtA->execute([$eventoId]);
        $assigned = $stmtA->fetchAll(PDO::FETCH_ASSOC);

        // Asistieron
        $sqlAttended = "SELECT u.id, u.name, u.email, u.role, r.nombre as red_nombre, p.created_at as fecha_asistencia 
                        FROM participaciones_evento p 
                        JOIN users u ON p.user_id = u.id 
                        LEFT JOIN redes r ON u.red_id = r.id
                        WHERE p.evento_id = ?";
        $stmtAtt = $pdo->prepare($sqlAttended);
        $stmtAtt->execute([$eventoId]);
        $attended = $stmtAtt->fetchAll(PDO::FETCH_ASSOC);

        // Faltaron (Asignados - Asistieron)
        $attendedIds = array_column($attended, 'id');
        $absent = array_filter($assigned, function($u) use ($attendedIds) {
            return !in_array($u['id'], $attendedIds);
        });

        // Add to result
        $eventsAttendance[] = [
            'id' => $evento['id'],
            'nombre' => $evento['nombre'],
            'fecha_inicio' => $evento['fecha_inicio'],
            'total_asignados' => count($assigned),
            'total_asistencias' => count($attended),
            'total_faltas' => count($absent),
            'asistieron' => $attended,
            'faltaron' => array_values($absent) // re-index array
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'users' => $usersData,
            'charts' => [
                'by_network' => $chartNetwork,
                'by_area' => $chartArea,
                'by_role' => $chartRole
            ],
            'events_attendance' => $eventsAttendance
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
