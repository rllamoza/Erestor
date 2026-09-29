<?php
require_once 'shared/core/db.php';
try {
    $pdo->exec("ALTER TABLE eventos ADD COLUMN serie_id VARCHAR(50) NULL AFTER id");
    echo "Column serie_id added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
