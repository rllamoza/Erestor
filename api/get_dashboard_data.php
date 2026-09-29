<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Basic stats
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE deleted_at IS NULL AND activo = 1");
    $total_users = $stmt->fetch()['total_users'];

    $stmt = $pdo->query("SELECT COUNT(*) as total_redes FROM redes WHERE deleted_at IS NULL AND activa = 1");
    $active_redes = $stmt->fetch()['total_redes'];

    $stmt = $pdo->query("SELECT COUNT(*) as total_sedes FROM sedes WHERE deleted_at IS NULL AND activa = 1");
    $active_sedes = $stmt->fetch()['total_sedes'];

    // Snapshot data (Historico)
    $stmt = $pdo->query("
        SELECT r.nombre as red_nombre, s.total_puntos, DATE_FORMAT(s.fecha_calculo, '%Y-%m') as mes
        FROM ranking_snapshots s
        JOIN redes r ON s.red_id = r.id
        ORDER BY mes ASC, red_id ASC
    ");
    $snapshots = $stmt->fetchAll();

    $historicalData = [];
    $labels = [];
    foreach($snapshots as $snap) {
        $mes = $snap['mes'];
        if(!in_array($mes, $labels)) {
            $labels[] = $mes;
        }
        $historicalData[$snap['red_nombre']][] = $snap['total_puntos'];
    }

    // Ranking Absoluto para AreaChart / BarChart
    $stmt = $pdo->query("
        SELECT 
            r.nombre as red_nombre,
            (COALESCE(SUM(pe.puntos_obtenidos), 0) + COALESCE(SUM(he.cantidad_horas), 0)) as total_puntos
        FROM redes r
        LEFT JOIN users u ON u.red_id = r.id AND u.activo = 1
        LEFT JOIN participaciones_evento pe ON pe.user_id = u.id
        LEFT JOIN horas_extras he ON he.user_id = u.id
        WHERE r.deleted_at IS NULL AND r.activa = 1
        GROUP BY r.id, r.nombre
        ORDER BY total_puntos DESC
    ");
    $ranking_absoluto = $stmt->fetchAll();

    // Influencia SEDES
    $stmt = $pdo->query("SELECT s.nombre as sede, COUNT(u.id) as miembros FROM sedes s LEFT JOIN users u ON u.sede_id = s.id AND u.activo = 1 GROUP BY s.id, s.nombre");
    $sedes_stats = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'stats' => [
            'users' => $total_users,
            'redes' => $active_redes,
            'sedes' => $active_sedes
        ],
        'charts' => [
            'historical_labels' => $labels,
            'historical_data' => $historicalData,
            'ranking_absoluto' => $ranking_absoluto,
            'sedes_stats' => $sedes_stats
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
