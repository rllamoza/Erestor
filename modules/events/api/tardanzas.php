<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $user_id = $_GET['user_id'] ?? null;
        $sql = "SELECT t.*, u.name as usuario_nombre FROM tardanzas t JOIN users u ON t.user_id = u.id";
        $params = [];
        if ($user_id) {
            $sql .= " WHERE t.user_id = ?";
            $params[] = $user_id;
        }
        $sql .= " ORDER BY t.fecha DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tardanzas = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $tardanzas]);
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        
        $user_id = $data->user_id ?? null;
        $minutos = $data->minutos ?? 0;
        $fecha = $data->fecha ?? date('Y-m-d');
        $observacion = $data->observacion ?? '';

        if (empty($user_id) || empty($minutos)) {
            http_response_code(400);
            echo json_encode(['error' => 'user_id y minutos son obligatorios']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO tardanzas (user_id, minutos, fecha, observacion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $minutos, $fecha, $observacion]);

        echo json_encode([
            'status' => 'success', 
            'message' => 'Tardanza registrada exitosamente',
            'id' => $pdo->lastInsertId()
        ]);
    }
    else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
