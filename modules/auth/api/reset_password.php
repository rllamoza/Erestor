<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$code = trim($data['code'] ?? '');
$password = $data['password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

if (!$email || empty($code) || empty($password) || empty($confirmPassword)) {
    echo json_encode(['status' => 'error', 'error' => 'Todos los campos son requeridos']);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode(['status' => 'error', 'error' => 'Las contraseñas no coinciden']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['status' => 'error', 'error' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

try {
    // 1. Verify code matches and is recent (15 mins)
    $stmtCode = $pdo->prepare("SELECT email FROM email_verifications WHERE email = ? AND code = ? AND created_at >= NOW() - INTERVAL 15 MINUTE");
    $stmtCode->execute([$email, $code]);
    if (!$stmtCode->fetch()) {
        echo json_encode(['status' => 'error', 'error' => 'Código de verificación inválido o expirado']);
        exit;
    }

    // 2. Update password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmtUpdate = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ? AND deleted_at IS NULL");
    $stmtUpdate->execute([$hash, $email]);

    if ($stmtUpdate->rowCount() > 0) {
        // 3. Clear code
        $stmtDelete = $pdo->prepare("DELETE FROM email_verifications WHERE email = ?");
        $stmtDelete->execute([$email]);

        echo json_encode(['status' => 'success', 'message' => 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.']);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'No se pudo actualizar la contraseña.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => 'Error de servidor: ' . $e->getMessage()]);
}
?>
