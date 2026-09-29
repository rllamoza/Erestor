<?php
require_once 'shared/core/db.php';
$stmt = $pdo->query("SHOW COLUMNS FROM eventos");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "| Field | Type | Null | Key | Default | Extra |\n";
echo "|---|---|---|---|---|---|\n";
foreach ($columns as $col) {
    echo "| " . $col['Field'] . " | " . $col['Type'] . " | " . $col['Null'] . " | " . $col['Key'] . " | " . $col['Default'] . " | " . $col['Extra'] . " |\n";
}
