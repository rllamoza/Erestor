<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);

$method = $_SERVER['REQUEST_METHOD'];

// Los coordinadores y supervisores también pueden ver la información
$allowedRoles = ['admin', 'superadmin', 'coordinador', 'supervisor'];

if ($method !== 'GET' && !in_array($user['role'], ['admin', 'superadmin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para esta acción']);
    exit;
}
if ($method === 'GET' && !in_array($user['role'], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver esta información']);
    exit;
}

try {
    if ($method === 'GET') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        $params = [];
        $where = "WHERE r.deleted_at IS NULL";

        if ($search) {
            $where .= " AND (r.nombre LIKE ? OR r.codigo LIKE ? OR r.codmujeres LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }

        $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM redes r $where");
        $totalStmt->execute($params);
        $totalRecords = (int)$totalStmt->fetchColumn();

        $sql = "SELECT r.*, u.name as coordinador_nombre 
                FROM redes r 
                LEFT JOIN users u ON r.coordinador_id = u.id 
                $where ORDER BY r.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $redes = $stmt->fetchAll();
        
        echo json_encode([
            'status' => 'success', 
            'data' => $redes, 
            'total_records' => $totalRecords,
            'page' => $page,
            'limit' => $limit
        ]);
        exit;
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        $nombre = $data->nombre ?? '';
        $codigo = $data->codigo ?? '';
        $codmujeres = $data->codmujeres ?? '';
        $descripcion = $data->descripcion ?? '';
        $coordinador_id = $data->coordinador_id ?? null;

        if (empty($nombre)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre is required']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO redes (nombre, codigo, codmujeres, descripcion, coordinador_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $codigo, $codmujeres, $descripcion, $coordinador_id]);

        echo json_encode(['status' => 'success', 'message' => 'Red created', 'id' => $pdo->lastInsertId()]);
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"));
        $id = $data->id ?? null;
        $nombre = $data->nombre ?? '';
        $codigo = $data->codigo ?? '';
        $codmujeres = $data->codmujeres ?? '';
        $descripcion = $data->descripcion ?? '';
        $coordinador_id = $data->coordinador_id ?? null;

        if (!$id || empty($nombre)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID and Nombre are required']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE redes SET nombre = ?, codigo = ?, codmujeres = ?, descripcion = ?, coordinador_id = ? WHERE id = ?");
        $stmt->execute([$nombre, $codigo, $codmujeres, $descripcion, $coordinador_id, $id]);
        echo json_encode(['status' => 'success', 'message' => 'Red updated']);
    }
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Red ID required']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE redes SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Red deleted']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
