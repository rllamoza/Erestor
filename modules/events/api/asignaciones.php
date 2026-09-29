<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';
require_once '../../../shared/core/socket_client.php';

$user = require_auth($pdo);

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $evento_id = $_GET['evento_id'] ?? null;
        if (!$evento_id) {
            http_response_code(400);
            echo json_encode(['error' => 'evento_id requerido']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT eu.*, u.name, u.email, a.nombre as area_nombre 
                             FROM eventos_usuarios eu 
                             JOIN users u ON eu.user_id = u.id 
                             LEFT JOIN areas_servicio a ON u.area_id = a.id
                             WHERE eu.evento_id = ?");
        $stmt->execute([$evento_id]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        $evento_id = $data->evento_id ?? null;
        $user_id = $data->user_id ?? null;
        $rol = $data->rol ?? 'Voluntario';

        if (!$evento_id || !$user_id) {
            http_response_code(400);
            echo json_encode(['error' => 'evento_id y user_id requeridos']);
            exit;
        }

        // Evitar duplicados
        $check = $pdo->prepare("SELECT id FROM eventos_usuarios WHERE evento_id = ? AND user_id = ?");
        $check->execute([$evento_id, $user_id]);
        if ($check->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Usuario ya asignado a este evento']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO eventos_usuarios (evento_id, user_id, rol_en_evento) VALUES (?, ?, ?)");
        $stmt->execute([$evento_id, $user_id, $rol]);

        // Emit realtime update to the affected user
        broadcast_event('assignment_updated', ['user_id' => $user_id]);

        echo json_encode(['status' => 'success', 'message' => 'Usuario asignado']);
    } 
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de asignación requerido']);
            exit;
        }

        // Obtener user_id antes de borrar para avisarle
        $stmt = $pdo->prepare("SELECT user_id FROM eventos_usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM eventos_usuarios WHERE id = ?");
        $stmt->execute([$id]);

        if ($row && isset($row['user_id'])) {
            broadcast_event('assignment_updated', ['user_id' => $row['user_id']]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Asignación eliminada']);
    } 
    else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
