<?php
require_once 'shared/core/db.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN theme VARCHAR(50) DEFAULT 'theme-corporate' AFTER avatar");
    echo "Column 'theme' added successfully to users table.";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'theme' already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
