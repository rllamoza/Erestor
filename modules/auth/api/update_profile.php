<?php
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

header('Content-Type: application/json');

$user = require_auth($pdo);

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    if ($action === 'update_info') {
        $name = $data['name'] ?? '';
        $phone = $data['phone'] ?? '';
        $area_id = $data['area_id'] ?? null;
        $red_id = $data['red_id'] ?? null;

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'error' => 'Nombre requerido']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, area_id = ?, red_id = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $area_id, $red_id, $user['id']]);

        // Update session
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['area_id'] = $area_id;
        $_SESSION['user']['red_id'] = $red_id;

        echo json_encode(['status' => 'success']);
    } elseif ($action === 'change_theme') {
        $allowed = ['theme-corporate', 'theme-dark-advanced', 'theme-clean-slate', 'theme-glass', 'theme-bento', 'theme-brutalist'];
        $theme = $data['theme'] ?? 'theme-corporate';

        if (!in_array($theme, $allowed)) {
            echo json_encode(['status' => 'error', 'error' => 'Tema no válido']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
        $stmt->execute([$theme, $user['id']]);

        echo json_encode(['status' => 'success', 'theme' => $theme]);
    } elseif ($action === 'change_password') {
        $current = $data['current_password'] ?? '';
        $new = $data['new_password'] ?? '';

        if (strlen($new) < 6) {
            echo json_encode(['status' => 'error', 'error' => 'Contraseña demasiado corta']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $dbUser = $stmt->fetch();

        if (!$dbUser || !password_verify($current, $dbUser['password'])) {
            echo json_encode(['status' => 'error', 'error' => 'Contraseña actual incorrecta']);
            exit;
        }

        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user['id']]);

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
