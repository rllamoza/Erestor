<?php
require_once 'shared/core/db.php';
$stmt = $pdo->query("SHOW COLUMNS FROM eventos");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . ": " . $col['Type'] . "\n";
}
