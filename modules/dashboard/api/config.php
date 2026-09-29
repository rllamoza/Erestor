<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT clave, valor, descripcion FROM configuraciones");
        $configs = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $configs]);
    } 
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->clave) || !isset($data->valor)) {
            http_response_code(400);
            echo json_encode(['error' => 'Clave y valor requeridos']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE configuraciones SET valor = ? WHERE clave = ?");
        $stmt->execute([$data->valor, $data->clave]);

        echo json_encode(['status' => 'success', 'message' => 'Configuración actualizada']);
    } 
    else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de servidor: ' . $e->getMessage()]);
}
?>
