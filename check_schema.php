<?php
require_once 'shared/core/db.php';
$stmt = $pdo->query("DESCRIBE eventos");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " | " . $col['Type'] . " | " . $col['Null'] . " | " . $col['Key'] . " | " . $col['Default'] . " | " . $col['Extra'] . "\n";
}
