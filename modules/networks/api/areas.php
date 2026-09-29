<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);

$method = $_SERVER['REQUEST_METHOD'];

// Los coordinadores y supervisores también pueden ver la información detallada (paginación/búsqueda)
// Los usuarios normales necesitan listar áreas para su perfil
$allowedRoles = ['admin', 'superadmin', 'coordinador', 'supervisor'];

if ($method !== 'GET' && !in_array($user['role'], ['admin', 'superadmin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para esta acción']);
    exit;
}
// Eliminamos la restricción de rol para GET para permitir el selector en el perfil
/*
if ($method === 'GET' && !in_array($user['role'], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver esta información']);
    exit;
}
*/

try {
    if ($method === 'GET') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        $all = isset($_GET['all']) && $_GET['all'] === 'true';
        $params = [];
        $where = ""; // Assuming areas table doesn't use soft delete or adjust if it does

        if ($search) {
            $where = "WHERE nombre LIKE ? OR descripcion LIKE ?";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm];
        }

        $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM areas_servicio $where");
        $totalStmt->execute($params);
        $totalRecords = (int)$totalStmt->fetchColumn();

        $sql = "SELECT * FROM areas_servicio $where ORDER BY id DESC";
        
        if (!$all) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $areas = $stmt->fetchAll();
        
        echo json_encode([
            'status' => 'success', 
            'data' => $areas, 
            'total_records' => $totalRecords,
            'page' => $page,
            'limit' => $limit
        ]);
        exit;
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        $nombre = $data->nombre ?? '';
        $descripcion = $data->descripcion ?? '';

        $stmt = $pdo->prepare("INSERT INTO areas_servicio (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute([$nombre, $descripcion]);
        echo json_encode(['status' => 'success', 'message' => 'Area created', 'id' => $pdo->lastInsertId()]);
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"));
        $id = $data->id ?? null;
        $nombre = $data->nombre ?? '';
        $descripcion = $data->descripcion ?? '';

        $stmt = $pdo->prepare("UPDATE areas_servicio SET nombre = ?, descripcion = ? WHERE id = ?");
        $stmt->execute([$nombre, $descripcion, $id]);
        echo json_encode(['status' => 'success', 'message' => 'Area updated']);
    }
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        $stmt = $pdo->prepare("DELETE FROM areas_servicio WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Area deleted']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
