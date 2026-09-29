<?php
try {
    require_once '../../../shared/core/db.php';
    $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER role");
    echo "Columna avatar añadida con éxito\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
