<?php
require_once '../../../shared/core/db.php';

require_once '../../../shared/core/auth_helper.php';

header('Content-Type: application/json');

$user = require_auth($pdo);

// Solo administradores o superadmin pueden guardar layouts
if (!in_array($user['role'], ['admin', 'superadmin'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para esta acción']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['name']) || !isset($input['layout'])) {
    echo json_encode(['status' => 'error', 'message' => 'Parámetros obligatorios: name, layout']);
    exit;
}

$name = $input['name'];
$layout = json_encode($input['layout']);

try {
    $stmt = $pdo->prepare("INSERT INTO custom_dashboards (name, layout_json) VALUES (?, ?) ON DUPLICATE KEY UPDATE layout_json = VALUES(layout_json)");
    $stmt->execute([$name, $layout]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Dashboard guardado con éxito',
        'id' => $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar dashboard: ' . $e->getMessage()]);
}
?>
