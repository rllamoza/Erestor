<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $user_id = $_GET['user_id'] ?? null;
        $sql = "SELECT h.*, u.name as usuario_nombre FROM horas_extras h JOIN users u ON h.user_id = u.id";
        $params = [];
        if ($user_id) {
            $sql .= " WHERE h.user_id = ?";
            $params[] = $user_id;
        }
        $sql .= " ORDER BY h.fecha DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $horas = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $horas]);
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        $user_id = $data->user_id ?? null;
        $cantidad = $data->cantidad_horas ?? 0;
        $fecha = $data->fecha ?? date('Y-m-d');
        $observacion = $data->observacion ?? '';

        $stmt = $pdo->prepare("INSERT INTO horas_extras (user_id, cantidad_horas, fecha, observacion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $cantidad, $fecha, $observacion]);
        echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
