<?php
session_start();
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/RateLimiter.php';

$limiter = new RateLimiter();
$ip = $_SERVER['REMOTE_ADDR'];

// Check rate limit (5 attempts per IP in 5 minutes)
$check = $limiter->check($ip, 5, 300);
if ($check['exceeded']) {
    http_response_code(429); // Too Many Requests
    echo json_encode(['status' => 'error', 'error' => 'Demasiados intentos fallidos. Intenta de nuevo en ' . $check['retry_after'] . ' segundos.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['status' => 'error', 'error' => 'Email y contraseña son requeridos']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, dni, email, password, role, avatar, red_id, sede_id FROM users WHERE (email = ? OR dni = ?) AND activo = 1 LIMIT 1");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Regenerar ID de sesión tras login exitoso (Evita Session Fixation)
        session_regenerate_id(true);
        
        unset($user['password']);
        
        // Generar un token básico
        $token = bin2hex(random_bytes(32));
        
        // Guardar hash del token en DB para seguridad (Si roban la DB, no pueden usar los tokens)
        $hashedToken = hash('sha256', $token);
        $stmtToken = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmtToken->execute([$hashedToken, $user['id']]);

        $_SESSION['user'] = $user;
        $_SESSION['token'] = $token;

        $limiter->clear($ip);

        echo json_encode([
            'status' => 'success',
            'user' => $user,
            'token' => $token
        ]);
    } else {
        $limiter->hit($ip);
        echo json_encode(['status' => 'error', 'error' => 'Credenciales incorrectas o usuario inactivo']);
    }
} catch (PDOException $e) {
    // Loguear error real en el servidor (opcional)
    error_log("Login Error: " . $e->getMessage());
    // Retornar mensaje genérico al cliente
    echo json_encode(['status' => 'error', 'error' => 'Error interno en el sistema de autenticación']);
}
?>
