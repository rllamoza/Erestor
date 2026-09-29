<?php
try {
    require_once '../../../shared/core/db.php';
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
