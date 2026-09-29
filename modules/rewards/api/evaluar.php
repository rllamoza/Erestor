<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';

/* 
 * Este endpoint evalúa en tiempo real a los ganadores según 
 * los criterios definados en el sprint 5 de los requerimientos:
 * 1. Mayor ranking absoluto
 * 2. Mayor ranking ajustado
 * 3. Mayor influencia territorial (sede más competitiva)
 */

try {
    $ganadores = [];

    // 1. Mayor Ranking Absoluto (Red Ganadora Global)
    $sql_absoluto = "
        SELECT r.nombre as red_nombre, (COALESCE(SUM(pe.puntos_obtenidos), 0) + COALESCE(SUM(he.cantidad_horas), 0)) as total_puntos
        FROM redes r
        LEFT JOIN users u ON u.red_id = r.id AND u.activo = 1
        LEFT JOIN participaciones_evento pe ON pe.user_id = u.id
        LEFT JOIN horas_extras he ON he.user_id = u.id
        WHERE r.deleted_at IS NULL AND r.activa = 1
        GROUP BY r.id, r.nombre
        ORDER BY total_puntos DESC LIMIT 1
    ";
    $ganador_absoluto = $pdo->query($sql_absoluto)->fetch();
    if ($ganador_absoluto) {
        $ganadores['premio_ranking_absoluto'] = [
            'descripcion' => 'Red con mayor poder global',
            'ganador' => $ganador_absoluto['red_nombre'],
            'puntos' => $ganador_absoluto['total_puntos']
        ];
    }

    // 2. Mayor Ranking Ajustado (Red más Eficiente)
    $sql_ajustado = "
        SELECT 
            r.nombre as red_nombre,
            (COALESCE(SUM(pe.puntos_obtenidos), 0) + COALESCE(SUM(he.cantidad_horas), 0)) / COUNT(DISTINCT u.id) as ranking_ajustado
        FROM redes r
        JOIN users u ON u.red_id = r.id AND u.activo = 1
        LEFT JOIN participaciones_evento pe ON pe.user_id = u.id
        LEFT JOIN horas_extras he ON he.user_id = u.id
        WHERE r.deleted_at IS NULL AND r.activa = 1
        GROUP BY r.id, r.nombre
        HAVING COUNT(DISTINCT u.id) > 0
        ORDER BY ranking_ajustado DESC LIMIT 1
    ";
    $ganador_ajustado = $pdo->query($sql_ajustado)->fetch();
    if ($ganador_ajustado) {
        $ganadores['premio_ranking_ajustado'] = [
            'descripcion' => 'Red con mejor eficiencia por miembro',
            'ganador' => $ganador_ajustado['red_nombre'],
            'puntos_por_miembro' => round($ganador_ajustado['ranking_ajustado'], 2)
        ];
    }

    // 3. Influencia (Red Dominante en Sede más poblada)
    $sql_influencia = "
        SELECT 
            s.nombre as sede_nombre,
            r.nombre as red_nombre,
            COUNT(u.id) as miembros_red
        FROM sedes s
        JOIN users u ON u.sede_id = s.id AND u.activo = 1
        JOIN redes r ON u.red_id = r.id AND r.activa = 1
        WHERE s.deleted_at IS NULL AND s.activa = 1
        GROUP BY s.id, s.nombre, r.id, r.nombre
        ORDER BY miembros_red DESC LIMIT 1
    ";
    $ganador_influencia = $pdo->query($sql_influencia)->fetch();
    if ($ganador_influencia) {
        $ganadores['premio_influencia_territorial'] = [
            'descripcion' => 'Red con mayor dominio en un territorio',
            'ganador' => $ganador_influencia['red_nombre'],
            'territorio_ganado' => $ganador_influencia['sede_nombre'],
            'miembros_activos' => $ganador_influencia['miembros_red']
        ];
    }

    echo json_encode(['status' => 'success', 'premios_calculados' => $ganadores]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
