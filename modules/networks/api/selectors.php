<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';

try {
    $stmt = $pdo->prepare("SELECT id, nombre, codigo, codmujeres, lider FROM redes WHERE redes.deleted_at IS NULL ORDER BY nombre ASC");
    $stmt->execute();
    $redes = $stmt->fetchAll();
    
    echo json_encode([
        'status' => 'success', 
        'data' => $redes
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
