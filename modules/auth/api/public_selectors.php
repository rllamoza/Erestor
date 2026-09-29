<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';

try {
    // Fetch active Redes
    $stmtRedes = $pdo->prepare("SELECT id, nombre, codigo FROM redes WHERE deleted_at IS NULL AND activa = 1 ORDER BY nombre ASC");
    $stmtRedes->execute();
    $redes = $stmtRedes->fetchAll();

    // Fetch active Sedes
    $stmtSedes = $pdo->prepare("SELECT id, nombre FROM sedes WHERE deleted_at IS NULL AND activa = 1 ORDER BY nombre ASC");
    $stmtSedes->execute();
    $sedes = $stmtSedes->fetchAll();

    // Fetch active Areas
    $stmtAreas = $pdo->prepare("SELECT id, nombre FROM areas_servicio ORDER BY nombre ASC");
    $stmtAreas->execute();
    $areas = $stmtAreas->fetchAll();
    
    echo json_encode([
        'status' => 'success', 
        'redes' => $redes,
        'sedes' => $sedes,
        'areas' => $areas
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
