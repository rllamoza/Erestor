<?php
/**
 * Helper de Autenticación para APIs
 */

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

function get_authenticated_user($pdo = null) {
    if (session_status() === PHP_SESSION_NONE) {
        // Configuración de seguridad para la cookie de sesión
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        
        // Si el sitio usa HTTPS, activar Secure y SameSite
        // (En entornos locales HTTP esto podría dar problemas, así que lo hacemos dinámico)
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
    }

    // 1. Prioridad: Sesión de PHP
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }

    // 2. Fallback: Token Bearer en Header
    if ($pdo) {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $hashedToken = hash('sha256', $token);
            $stmt = $pdo->prepare("SELECT id, name, email, phone, role, avatar, red_id, area_id, sede_id FROM users WHERE remember_token = ? AND activo = 1 LIMIT 1");
            $stmt->execute([$hashedToken]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user'] = $user; // Restaurar sesión
                return $user;
            }
        }
    }

    return null;
}

function require_auth($pdo = null) {
    $user = get_authenticated_user($pdo);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'error' => 'No autorizado']);
        exit;
    }
    return $user;
}
