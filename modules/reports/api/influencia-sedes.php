<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';

try {
    // Influencia Territorial: Miembros red en sede / total miembros en sede
    // First, get total members per sede
    $stmt = $pdo->query("SELECT sede_id, COUNT(id) as total_sede FROM users WHERE activo = 1 AND sede_id IS NOT NULL GROUP BY sede_id");
    $sedesTotalesRows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Then get members per red per sede
    $sql = "
        SELECT 
            s.id as sede_id,
            s.nombre as sede_nombre,
            r.id as red_id,
            r.nombre as red_nombre,
            COUNT(u.id) as miembros_red_sede
        FROM sedes s
        JOIN users u ON u.sede_id = s.id AND u.activo = 1
        JOIN redes r ON u.red_id = r.id AND r.activa = 1
        WHERE s.deleted_at IS NULL AND s.activa = 1
        GROUP BY s.id, s.nombre, r.id, r.nombre
        ORDER BY s.nombre, red_nombre
    ";
    
    $stmt = $pdo->query($sql);
    $sedesRedes = $stmt->fetchAll();
    
    $influencia = [];
    foreach ($sedesRedes as $row) {
        $totalSede = $sedesTotalesRows[$row['sede_id']] ?? 0;
        $indice = $totalSede > 0 ? (float)($row['miembros_red_sede'] / $totalSede) : 0;
        
        $influencia[] = [
            'sede' => $row['sede_nombre'],
            'red' => $row['red_nombre'],
            'miembros_en_sede' => $row['miembros_red_sede'],
            'total_miembros_sede' => $totalSede,
            'indice_influencia' => round($indice, 3),
            'es_dominante' => $indice > 0.5
        ];
    }
    
    echo json_encode(['status' => 'success', 'data' => $influencia]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
