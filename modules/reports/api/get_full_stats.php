<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);

// Role check
if (!in_array($user['role'], ['admin', 'superadmin', 'coordinador', 'supervisor'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver reportes']);
    exit;
}

try {
    // 1. Total Active Users
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE activo = 1")->fetchColumn();

    // 2. Total Networks
    $totalRedes = $pdo->query("SELECT COUNT(*) FROM redes WHERE activa = 1 AND deleted_at IS NULL")->fetchColumn();

    // 3. Total Areas
    $totalAreas = $pdo->query("SELECT COUNT(*) FROM areas_servicio WHERE deleted_at IS NULL")->fetchColumn();

    // 4. Global Attendance Avg (%) - Simplified
    $totalAsig = $pdo->query("SELECT COUNT(*) FROM eventos_usuarios")->fetchColumn();
    $totalAsis = $pdo->query("SELECT COUNT(*) FROM participaciones_evento")->fetchColumn();
    $avgAttendance = $totalAsig > 0 ? round(($totalAsis / $totalAsig) * 100, 1) : 0;

    // 5. Extra Hours This Month
    $extraHoursMonth = $pdo->query("SELECT SUM(cantidad_horas) FROM horas_extras WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)")->fetchColumn() ?: 0;

    // 6. Top Network (by points or members, let's do points)
    $topNetwork = $pdo->query("
        SELECT r.nombre 
        FROM redes r 
        LEFT JOIN users u ON u.red_id = r.id 
        LEFT JOIN participaciones_evento pe ON pe.user_id = u.id 
        GROUP BY r.id 
        ORDER BY SUM(COALESCE(pe.puntos_obtenidos, 0)) DESC 
        LIMIT 1
    ")->fetchColumn() ?: 'N/A';

    echo json_encode([
        'status' => 'success',
        'kpis' => [
            'total_users' => $totalUsers,
            'total_redes' => $totalRedes,
            'total_areas' => $totalAreas,
            'avg_attendance' => $avgAttendance,
            'extra_hours_month' => $extraHoursMonth,
            'top_network' => $topNetwork
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
