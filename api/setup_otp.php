<?php
require_once 'db.php';
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS email_verifications (
            email VARCHAR(255) PRIMARY KEY,
            code VARCHAR(6) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Tabla email_verifications creada exitosamente.\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
