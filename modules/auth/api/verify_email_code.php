<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$emailRaw = trim($data['email'] ?? '');
$code = trim($data['code'] ?? '');
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);

if (!$email || empty($code)) {
    echo json_encode(['status' => 'error', 'error' => 'Correo y código son requeridos']);
    exit;
}

try {
    // Check if code matches and is not older than 15 minutes
    $stmt = $pdo->prepare("SELECT email FROM email_verifications WHERE email = ? AND code = ? AND created_at >= NOW() - INTERVAL 15 MINUTE");
    $stmt->execute([$email, $code]);
    $valid = $stmt->fetch();

    if ($valid) {
        // Marcar como verificado (por ejemplo, eliminando el código)
        // Pero lo dejaremos para que register.php lo valide una última vez.
        // Opcional: Podríamos tener una columna `verified_at` en esa tabla.
        
        echo json_encode(['status' => 'success', 'message' => 'Código verificado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Código inválido o expirado']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => 'Error de servidor: ' . $e->getMessage()]);
}
?>
