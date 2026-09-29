<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Fetch all active users for selectors/assignments
    $stmt = $pdo->query("SELECT u.id, u.name, u.email, u.area_id, a.nombre as area_nombre 
                         FROM users u 
                         LEFT JOIN areas_servicio a ON u.area_id = a.id 
                         WHERE u.deleted_at IS NULL AND u.activo = 1
                         ORDER BY u.name ASC");
    $users = $stmt->fetchAll();
    
    echo json_encode([
        'status' => 'success', 
        'data' => $users
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
