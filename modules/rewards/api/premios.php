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
        $where = "WHERE p.activo = 1";

        if ($search) {
            $where .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ? OR p.criterio LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }

        // Count total records
        $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM premios p $where");
        $totalStmt->execute($params);
        $totalRecords = (int)$totalStmt->fetchColumn();

        // Fetch paginated data
        $sql = "SELECT p.* FROM premios p $where ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $premios = $stmt->fetchAll();
        
        echo json_encode([
            'status' => 'success', 
            'data' => $premios, 
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
        $tipo = $data->tipo ?? 'red'; // red o usuario
        $criterio = $data->criterio ?? '';

        if (empty($nombre) || empty($criterio)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre y criterio son obligatorios']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO premios (nombre, descripcion, tipo, criterio) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $tipo, $criterio]);

        echo json_encode([
            'status' => 'success', 
            'message' => 'Premio creado exitosamente',
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
