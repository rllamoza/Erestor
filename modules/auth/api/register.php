<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$dni = trim($data['dni'] ?? '');
$emailRaw = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = $data['password'] ?? '';
$sede_id = $data['sede_id'] ?? null;
$red_id = $data['red_id'] ?? null;
$area_id = $data['area_id'] ?? null;

// Validations
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
$code = trim($data['code'] ?? '');

if (empty($name) || empty($dni) || !$email || empty($password) || !$sede_id || !$red_id || !$area_id || empty($code)) {
    echo json_encode(['status' => 'error', 'error' => 'Nombre, DNI, Correo, Contraseña, Sede, Red, Área y código son obligativos']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['status' => 'error', 'error' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

try {
    // 0. Verify the OTP code one last time to prevent direct POST attempts
    $stmtCode = $pdo->prepare("SELECT email FROM email_verifications WHERE email = ? AND code = ? AND created_at >= NOW() - INTERVAL 15 MINUTE");
    $stmtCode->execute([$email, $code]);
    if (!$stmtCode->fetch()) {
        echo json_encode(['status' => 'error', 'error' => 'Código de verificación inválido o expirado. Por favor, solicita uno nuevo.']);
        exit;
    }
    // Check if email exists
    $stmtCheck = $pdo->prepare("SELECT id, deleted_at FROM users WHERE email = ?");
    $stmtCheck->execute([$email]);
    $existing = $stmtCheck->fetch();

    if ($existing) {
        if ($existing['deleted_at'] === null) {
            echo json_encode(['status' => 'error', 'error' => 'El correo electrónico ya está en uso']);
            exit;
        } else {
            // Reactivate soft-deleted user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, dni = ?, phone = ?, password = ?, role = 'usuario', sede_id = ?, red_id = ?, area_id = ?, activo = 1, fecha_alta = NOW(), deleted_at = NULL WHERE id = ?");
            $stmt->execute([$name, $dni, $phone, $hash, $sede_id, $red_id, $area_id, $existing['id']]);
            
            echo json_encode(['status' => 'success', 'message' => 'Cuenta reactivada correctamente']);
            exit;
        }
    }

    // Insert new user
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, dni, email, phone, password, role, sede_id, red_id, area_id, activo, fecha_alta) VALUES (?, ?, ?, ?, ?, 'usuario', ?, ?, ?, 1, NOW())");
    $stmt->execute([$name, $dni, $email, $phone, $hash, $sede_id, $red_id, $area_id]);
    
    echo json_encode(['status' => 'success', 'message' => 'Registro exitoso']);

} catch (PDOException $e) {
    error_log("Registration Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => 'Error interno durante el registro']);
}
?>
