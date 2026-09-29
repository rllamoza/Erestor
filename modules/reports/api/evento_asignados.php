<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);
if (!in_array($user['role'], ['admin', 'superadmin', 'coordinador', 'supervisor'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']); exit;
}

$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        // Return all events for the selector dropdown
        $stmt = $pdo->query("
            SELECT e.id, e.nombre, e.fecha_inicio, e.fecha_fin, s.nombre as sede_nombre,
                   a.nombre as area_nombre,
                   (SELECT COUNT(*) FROM eventos_usuarios eu WHERE eu.evento_id = e.id) as total_asignados,
                   (SELECT COUNT(*) FROM participaciones_evento pe WHERE pe.evento_id = e.id) as total_asistidos
            FROM eventos e
            LEFT JOIN sedes s ON e.sede_id = s.id
            LEFT JOIN areas_servicio a ON e.area_id = a.id
            WHERE e.deleted_at IS NULL
            ORDER BY e.fecha_inicio DESC
        ");
        $eventos = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $eventos]);

    } elseif ($action === 'detalle') {
        // Return assigned users for a specific event
        $evento_id = (int)($_GET['evento_id'] ?? 0);
        if (!$evento_id) {
            echo json_encode(['error' => 'evento_id requerido']); exit;
        }

        // Event info
        $stmtEv = $pdo->prepare("
            SELECT e.*, s.nombre as sede_nombre, a.nombre as area_nombre
            FROM eventos e
            LEFT JOIN sedes s ON e.sede_id = s.id
            LEFT JOIN areas_servicio a ON e.area_id = a.id
            WHERE e.id = ?
        ");
        $stmtEv->execute([$evento_id]);
        $evento = $stmtEv->fetch();

        // Assigned users with attendance status
        $stmtUsers = $pdo->prepare("
            SELECT 
                u.id,
                u.name,
                u.email,
                eu.rol as rol_asignado,
                r.nombre as red,
                a.nombre as area,
                CASE 
                    WHEN pe.id IS NOT NULL THEN 'Asistió'
                    ELSE 'No asistió'
                END as asistencia,
                pe.puntos_obtenidos,
                pe.created_at as fecha_asistencia
            FROM eventos_usuarios eu
            JOIN users u ON eu.user_id = u.id
            LEFT JOIN redes r ON u.red_id = r.id
            LEFT JOIN areas_servicio a ON u.area_id = a.id
            LEFT JOIN participaciones_evento pe ON pe.evento_id = eu.evento_id AND pe.user_id = eu.user_id
            WHERE eu.evento_id = ?
            ORDER BY asistencia DESC, u.name ASC
        ");
        $stmtUsers->execute([$evento_id]);
        $asignados = $stmtUsers->fetchAll();

        // Summary stats
        $total = count($asignados);
        $asistidos = count(array_filter($asignados, fn($r) => $r['asistencia'] === 'Asistió'));
        $tasa = $total > 0 ? round(($asistidos / $total) * 100, 1) : 0;

        echo json_encode([
            'status' => 'success',
            'evento' => $evento,
            'asignados' => $asignados,
            'stats' => [
                'total_asignados' => $total,
                'total_asistidos' => $asistidos,
                'no_asistidos' => $total - $asistidos,
                'tasa_asistencia' => $tasa
            ]
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
