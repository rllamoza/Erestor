<?php
// Este script está diseñado para ejecutarse vía Cron diario
require_once __DIR__ . '/../api/db.php';

echo "Iniciando snapshot diario del Motor de Ranking...\n";

try {
    $pdo->beginTransaction();

    // 1. Calcular Ranking de cada red
    $sql = "
        SELECT 
            r.id as red_id,
            COUNT(DISTINCT u.id) as miembros_activos,
            COALESCE(SUM(pe.puntos_obtenidos), 0) as puntos_eventos,
            COALESCE(SUM(he.cantidad_horas), 0) as puntos_horas_extras,
            (COALESCE(SUM(pe.puntos_obtenidos), 0) + COALESCE(SUM(he.cantidad_horas), 0)) as total_puntos,
            CASE 
                WHEN COUNT(DISTINCT u.id) > 0 THEN 
                    (COALESCE(SUM(pe.puntos_obtenidos), 0) + COALESCE(SUM(he.cantidad_horas), 0)) / COUNT(DISTINCT u.id)
                ELSE 0 
            END as promedio_puntos
        FROM redes r
        LEFT JOIN users u ON u.red_id = r.id AND u.activo = 1
        LEFT JOIN participaciones_evento pe ON pe.user_id = u.id
        LEFT JOIN horas_extras he ON he.user_id = u.id
        WHERE r.deleted_at IS NULL AND r.activa = 1
        GROUP BY r.id
    ";
    
    $stmt = $pdo->query($sql);
    $rankings = $stmt->fetchAll();

    // 2. Insertar snapshot en la base de datos
    $insertStmt = $pdo->prepare("INSERT INTO ranking_snapshots (red_id, total_puntos, promedio_puntos, fecha_calculo) VALUES (?, ?, ?, NOW())");
    
    $count = 0;
    foreach ($rankings as $row) {
        $insertStmt->execute([
            $row['red_id'],
            $row['total_puntos'],
            $row['promedio_puntos']
        ]);
        $count++;
    }

    $pdo->commit();
    echo "Snapshot de $count redes guardado exitosamente.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error guardando snapshot: " . $e->getMessage() . "\n";
}
?>
