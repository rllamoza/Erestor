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
        $where = "WHERE deleted_at IS NULL";

        if ($search) {
            $where .= " AND (nombre LIKE ? OR ubicacion LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm];
        }

        // Count total records
        $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM sedes $where");
        $totalStmt->execute($params);
        $totalRecords = (int)$totalStmt->fetchColumn();

        // Fetch paginated data
        $sql = "SELECT * FROM sedes $where ORDER BY id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $sedes = $stmt->fetchAll();
        
        echo json_encode([
            'status' => 'success', 
            'data' => $sedes, 
            'total_records' => $totalRecords,
            'page' => $page,
            'limit' => $limit
        ]);
        exit;
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        
        $nombre = $data->nombre ?? '';
        $ubicacion = $data->ubicacion ?? '';
        $latitud = $data->latitud ?? null;
        $longitud = $data->longitud ?? null;
        $distancia = $data->distancia_maxima_m ?? 500;

        if (empty($nombre)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre is required']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO sedes (nombre, ubicacion, latitud, longitud, distancia_maxima_m) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $ubicacion, $latitud, $longitud, $distancia]);

        echo json_encode([
            'status' => 'success', 
            'message' => 'Sede created successfully',
            'id' => $pdo->lastInsertId()
        ]);
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"));
        $id = $data->id ?? null;
        $nombre = $data->nombre ?? '';
        $ubicacion = $data->ubicacion ?? '';
        $latitud = $data->latitud ?? null;
        $longitud = $data->longitud ?? null;
        $distancia = $data->distancia_maxima_m ?? 500;

        if (!$id || empty($nombre)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID and Nombre are required']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE sedes SET nombre = ?, ubicacion = ?, latitud = ?, longitud = ?, distancia_maxima_m = ? WHERE id = ?");
        $stmt->execute([$nombre, $ubicacion, $latitud, $longitud, $distancia, $id]);

        echo json_encode(['status' => 'success', 'message' => 'Sede updated successfully']);
    }
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Sede ID required']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE sedes SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Sede deleted successfully']);
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
