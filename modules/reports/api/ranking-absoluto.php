<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';

try {
    // Ranking Absoluto: Total puntos = SUM(puntos_obtenidos) + SUM(horas_extras)
    $sql = "
        SELECT 
            r.id as red_id,
            r.nombre as red_nombre,
            COALESCE(SUM(pe.puntos_obtenidos), 0) as puntos_eventos,
            COALESCE(SUM(he.cantidad_horas), 0) as puntos_horas_extras,
            (COALESCE(SUM(pe.puntos_obtenidos), 0) + COALESCE(SUM(he.cantidad_horas), 0)) as total_puntos
        FROM redes r
        LEFT JOIN users u ON u.red_id = r.id AND u.activo = 1
        LEFT JOIN participaciones_evento pe ON pe.user_id = u.id
        LEFT JOIN horas_extras he ON he.user_id = u.id
        WHERE r.deleted_at IS NULL AND r.activa = 1
        GROUP BY r.id, r.nombre
        ORDER BY total_puntos DESC
    ";
    
    $stmt = $pdo->query($sql);
    $ranking = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'data' => $ranking]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
