<?php
require_once 'shared/core/db.php';
try {
    $pdo->exec("ALTER TABLE eventos CHANGE COLUMN red_id area_id INT");
    echo "Column 'red_id' successfully renamed to 'area_id' in 'eventos' table.";
} catch (Exception $e) {
    if (strpos($e->getMessage(), "Unknown column 'red_id'") !== false) {
        echo "Column 'red_id' already renamed or missing.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
