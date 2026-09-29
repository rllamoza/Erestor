<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);

// Solo administradores o superadmin pueden crear/editar/borrar usuarios
$method = $_SERVER['REQUEST_METHOD'];

// Los coordinadores y supervisores también pueden ver la información
// Los usuarios normales solo pueden ver SU PROPIA información
$allowedRoles = ['admin', 'superadmin', 'coordinador', 'supervisor'];
$isOwnProfile = ($method === 'GET' && isset($_GET['id']) && (int)$_GET['id'] === (int)$user['id']);

if ($method !== 'GET' && !in_array($user['role'], ['admin', 'superadmin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para esta acción']);
    exit;
}
if ($method === 'GET' && !in_array($user['role'], $allowedRoles) && !$isOwnProfile) {
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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $params = [];
        $where = "WHERE u.deleted_at IS NULL";

        if ($id) {
            $where .= " AND u.id = ?";
            $params[] = $id;
        } elseif ($search) {
            $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.role LIKE ? OR r.nombre LIKE ? OR r.codigo LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }

        // Count total records
        $countSql = "SELECT COUNT(*) FROM users u LEFT JOIN redes r ON u.red_id = r.id $where";
        $totalStmt = $pdo->prepare($countSql);
        $totalStmt->execute($params);
        $totalRecords = (int)$totalStmt->fetchColumn();

        // Fetch paginated data
        $sql = "SELECT u.id, u.name, u.dni, u.email, u.phone, u.role, u.red_id, r.nombre as red_nombre, r.lider as red_lider, r.codigo as red_codigo, u.sede_id, u.activo, u.area_id, a.nombre as area_nombre 
                FROM users u 
                LEFT JOIN areas_servicio a ON u.area_id = a.id 
                LEFT JOIN redes r ON u.red_id = r.id
                $where
                ORDER BY u.id DESC";
        
        if (!$id) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters manually to ensure correct types for LIMIT/OFFSET
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo json_encode([
            'status' => 'success', 
            'data' => $users, 
            'total_records' => $totalRecords,
            'page' => $page,
            'limit' => $limit
        ]);
        exit;
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        
        $name = trim(htmlspecialchars($data->name ?? ''));
        $emailRaw = trim($data->email ?? '');
        $phone = trim(htmlspecialchars($data->phone ?? ''));
        $password = $data->password ?? '';
        $role = $data->role ?? 'usuario';
        $sede_id = $data->sede_id ?? 1;
        $red_id = $data->red_id ?? null;
        $area_id = $data->area_id ?? null;

        $email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);

        if (empty($name) || empty($dni) || !$email || empty($password) || !$sede_id || !$red_id || !$area_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre, DNI, Correo, Contraseña, Sede, Red y Área son obligatorios']);
            exit;
        }

        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
            exit;
        }

        // Verificar si email ya existe (incluyendo borrados)
        $stmtCheck = $pdo->prepare("SELECT id, deleted_at FROM users WHERE email = ?");
        $stmtCheck->execute([$email]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            if ($existing['deleted_at'] === null) {
                http_response_code(409); // Conflict
                echo json_encode(['error' => 'El correo electrónico ya está en uso por otra cuenta activa']);
                exit;
            } else {
                // Lógica de Reactivación
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, dni = ?, phone = ?, password = ?, role = ?, sede_id = ?, red_id = ?, area_id = ?, activo = 1, fecha_alta = NOW(), fecha_baja = NULL, deleted_at = NULL WHERE id = ?");
                $stmt->execute([$name, $dni, $phone, $hash, $role, $sede_id, $red_id, $area_id, $existing['id']]);

                // Registrar Historial
                $stmtH = $pdo->prepare("INSERT INTO user_status_history (user_id, status, reason) VALUES (?, 'reactivacion', 're-registro desde panel')");
                $stmtH->execute([$existing['id']]);

                echo json_encode(['status' => 'success', 'message' => 'Usuario reactivado exitosamente', 'id' => $existing['id']]);
                exit;
            }
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, dni, email, phone, password, role, sede_id, red_id, area_id, activo, fecha_alta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$name, $dni, $email, $phone, $hash, $role, $sede_id, $red_id, $area_id]);
        $newId = $pdo->lastInsertId();

        // Registrar Historial
        $stmtH = $pdo->prepare("INSERT INTO user_status_history (user_id, status, reason) VALUES (?, 'alta', 'registro nuevo')");
        $stmtH->execute([$newId]);

        echo json_encode([
            'status' => 'success', 
            'message' => 'User created successfully',
            'id' => $newId
        ]);
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"));
        
        $id = $data->id ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
            exit;
        }

        // Check if it's a status-only update
        if (isset($data->activo) && count((array)$data) <= 2) {
            $activo = $data->activo ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE users SET activo = ? WHERE id = ?");
            $stmt->execute([$activo, $id]);
            echo json_encode(['status' => 'success', 'message' => 'User status updated successfully']);
            exit;
        }
        
        $name = trim(htmlspecialchars($data->name ?? ''));
        $dni = trim(htmlspecialchars($data->dni ?? ''));
        $phone = trim(htmlspecialchars($data->phone ?? ''));
        $role = $data->role ?? 'usuario';
        $sede_id = $data->sede_id ?? 1;
        $red_id = $data->red_id ?? null;
        $area_id = $data->area_id ?? null;
        $password = $data->password ?? null;

        if (empty($name) || empty($dni) || !$sede_id || !$red_id || !$area_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre, DNI, Sede, Red y Área son obligatorios']);
            exit;
        }
        
        $sql = "UPDATE users SET name = ?, dni = ?, phone = ?, role = ?, sede_id = ?, red_id = ?, area_id = ?";
        $params = [$name, $dni, $phone, $role, $sede_id, $red_id, $area_id];

        if (!empty($password)) {
            if (strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
                exit;
            }
            $sql .= ", password = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
    }
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID required']);
            exit;
        }
        
        // Soft delete y registro de baja
        $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW(), activo = 0, fecha_baja = NOW() WHERE id = ?");
        $stmt->execute([$id]);

        // Registrar Historial
        $stmtH = $pdo->prepare("INSERT INTO user_status_history (user_id, status, reason) VALUES (?, 'baja', 'eliminación lógica')");
        $stmtH->execute([$id]);
        
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
    }
    else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("User Management API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error de servidor al procesar usuarios']);
}
?>
