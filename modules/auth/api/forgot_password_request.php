<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/utils/EmailService.php';

$data = json_decode(file_get_contents('php://input'), true);
$emailRaw = trim($data['email'] ?? '');
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['status' => 'error', 'error' => 'Correo electrónico inválido']);
    exit;
}

try {
    // Check if user exists
    $stmtCheck = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND deleted_at IS NULL");
    $stmtCheck->execute([$email]);
    $user = $stmtCheck->fetch();

    if (!$user) {
        // Por seguridad, no revelamos si el correo existe o no en este paso específico, 
        // pero el requerimiento implica que debemos enviar el código si existe.
        // Si no existe, podemos simplemente fallar silenciosamente o dar un mensaje genérico.
        echo json_encode(['status' => 'error', 'error' => 'No se encontró una cuenta con este correo']);
        exit;
    }

    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));

    // Delete any old codes for this email
    $stmtDelete = $pdo->prepare("DELETE FROM email_verifications WHERE email = ?");
    $stmtDelete->execute([$email]);

    // Insert new OTP
    $stmtInsert = $pdo->prepare("INSERT INTO email_verifications (email, code) VALUES (?, ?)");
    $stmtInsert->execute([$email, $otp]);

    // Send the password reset email
    $emailSent = EmailService::sendPasswordResetOTP($email, $otp);

    if ($emailSent) {
        $response = ['status' => 'success', 'message' => 'Código de recuperación enviado al correo'];
        
        // DEV MODE
        if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
            $response['dev_code'] = $otp;
        }
        
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Error al enviar el correo']);
    }

} catch (Exception $e) {
    error_log("Forgot Password Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => 'Error al procesar la solicitud de recuperación']);
}
?>
