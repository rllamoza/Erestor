<?php
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

header('Content-Type: application/json');

$user = require_auth($pdo);

$data = json_decode(file_get_contents('php://input'), true);
$imageB64 = $data['image'] ?? '';

if (empty($imageB64)) {
    echo json_encode(['status' => 'error', 'error' => 'Imagen no proporcionada']);
    exit;
}

try {
    // Basic base64 decode and save
    if (preg_match('/^data:image\/(\w+);base64,/', $imageB64, $type)) {
        $imageB64 = substr($imageB64, strpos($imageB64, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif

        if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
            throw new Exception('Formato de imagen no válido');
        }

        $decodedImage = base64_decode($imageB64);

        if ($decodedImage === false) {
            throw new Exception('Error al decodificar imagen');
        }

        // Validación profunda: Verificar si es realmente una imagen válida
        $tmpFile = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tmpFile, $decodedImage);
        $check = getimagesize($tmpFile);
        unlink($tmpFile);

        if ($check === false) {
            throw new Exception('El archivo proporcionado no es una imagen válida');
        }

        // Limitar tamaño (ej: 2MB)
        if (strlen($decodedImage) > 2 * 1024 * 1024) {
            throw new Exception('La imagen es demasiado grande. Máximo 2MB.');
        }

        $imageB64 = $decodedImage; // Usar el binario decodificado para file_put_contents
    } else {
        throw new Exception('Data URI no válida');
    }

    $avatarDir = '../../../shared/assets/images/avatars/';
    if (!is_dir($avatarDir)) {
        mkdir($avatarDir, 0777, true);
    }

    $fileName = 'avatar_' . $user['id'] . '_' . time() . '.' . $type;
    $filePath = $avatarDir . $fileName;

    if (file_put_contents($filePath, $imageB64)) {
        $publicPath = '../../shared/assets/images/avatars/' . $fileName;
        
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$publicPath, $user['id']]);

        // Update session
        $_SESSION['user']['avatar'] = $publicPath;

        echo json_encode(['status' => 'success', 'avatar_url' => $publicPath]);
    } else {
        throw new Exception('Error al guardar archivo en el servidor');
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
