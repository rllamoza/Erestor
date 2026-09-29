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
    // Check if email exists AND is active
    $stmtCheck = $pdo->prepare("SELECT id, deleted_at FROM users WHERE email = ?");
    $stmtCheck->execute([$email]);
    $existing = $stmtCheck->fetch();

    if ($existing && $existing['deleted_at'] === null) {
        echo json_encode(['status' => 'error', 'error' => 'El correo electrónico ya está en uso']);
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

    // Send the email
    $emailSent = EmailService::sendOTP($email, $otp);

    if ($emailSent) {
        $response = ['status' => 'success', 'message' => 'Código de verificación enviado al correo'];
        
        // DEV MODE: Always return the code for local testing so the user can proceed without a real inbox
        // Remove this condition for production
        if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
            $response['dev_code'] = $otp;
            error_log("DEV OTP GENERATED FOR $email: $otp");
        }
        
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'No se pudo enviar el correo, verifica la configuración SMTP']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => 'Error de servidor: ' . $e->getMessage()]);
}
?>
