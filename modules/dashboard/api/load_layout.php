<?php
require_once '../../../shared/core/db.php';

header('Content-Type: application/json');

$name = isset($_GET['name']) ? $_GET['name'] : 'Principal'; // Por defecto carga uno llamado Principal

try {
    $stmt = $pdo->prepare("SELECT id, name, layout_json FROM custom_dashboards WHERE name = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$name]);
    $dashboard = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dashboard) {
        $layout = json_decode($dashboard['layout_json'], true);
        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => $dashboard['id'],
                'name' => $dashboard['name'],
                'layout' => $layout
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Dashboard no encontrado'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al cargar dashboard: ' . $e->getMessage()]);
}
?>
