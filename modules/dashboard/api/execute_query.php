<?php
require_once '../../../shared/core/db.php';

require_once '../../../shared/core/auth_helper.php';

header('Content-Type: application/json');

$user = require_auth($pdo);

// Solo administradores o superadmin pueden ejecutar queries personalizadas
if (!in_array($user['role'], ['admin', 'superadmin'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para esta acción']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || (!isset($input['table']) && !isset($input['is_sql']))) {
    echo json_encode(['status' => 'error', 'message' => 'Parámetros inválidos']);
    exit;
}

// ==========================================
// MODO SQL PERSONALIZADO (RAW SQL)
// ==========================================
if (isset($input['is_sql']) && $input['is_sql'] === true) {
    $raw_sql = trim($input['raw_sql'] ?? '');
    
    // 1. Validar que comience estrictamente con SELECT
    if (stripos($raw_sql, 'SELECT') !== 0) {
        echo json_encode(['status' => 'error', 'message' => 'Seguridad: La consulta debe comenzar obligatoriamente con la palabra SELECT.']);
        exit;
    }
    
    // 2. Validar que no contenga palabras reservadas destructivas o de encadenamiento
    $forbidden = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'TRUNCATE', 'REPLACE', 'GRANT', 'REVOKE', 'EXEC', 'UNION', 'INTO', ';', '--'];
    foreach ($forbidden as $word) {
        // Añadimos espacios alrededor para evitar falsos positivos dentro de otras palabras, ej: "selecT_UPDATE_date" 
        // pero stripos es suficiente para bloquear palabras crudas de alto riesgo.
        if (preg_match("/\b{$word}\b/i", $raw_sql) || strpos($raw_sql, ';') !== false) {
             echo json_encode(['status' => 'error', 'message' => "Seguridad: Comando no permitido detectado [ {$word} ]. Solo modo lectura."]);
             exit;
        }
    }
    
    try {
        $stmt = $pdo->query($raw_sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $labels = [];
        $values = [];
        
        // Autodetectar las columnas devueltas: 
        // 1ra Columna -> Label (Eje X)
        // 2da Columna -> Value (Eje Y)
        if (count($results) > 0) {
           $keys = array_keys($results[0]);
           $label_key = $keys[0]; 
           $value_key = isset($keys[1]) ? $keys[1] : $keys[0]; 
           
           foreach ($results as $row) {
               $labels[] = $row[$label_key] ?? 'N/A';
               $values[] = (float)$row[$value_key];
           }
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => [
                'labels' => $labels,
                'values' => $values
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit; // Fin de la ejecución si procesamos SQL Crudo
}

// ==========================================
// MODO BUILDER ESTÁNDAR (CONSTRUCTOR GUI)
// ==========================================

$table = $input['table'];
$aggregation = strtoupper($input['aggregation']); // COUNT, SUM, AVG, MAX, MIN
$target_column = isset($input['target_column']) ? $input['target_column'] : 'id';
$group_by = isset($input['group_by']) ? $input['group_by'] : null;

// Validar contra whitelist para prevenir SQL Injection
$allowed_tables = ['users', 'redes', 'sedes', 'eventos', 'participaciones_evento', 'premios'];
$allowed_aggs = ['COUNT', 'SUM', 'AVG', 'MAX', 'MIN'];

if (!in_array($table, $allowed_tables) || !in_array($aggregation, $allowed_aggs)) {
    echo json_encode(['status' => 'error', 'message' => 'Tabla o agregación no permitida']);
    exit;
}

// Prevenir inyección en nombres de columnas (solo alfanumérico y guiones bajos)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $target_column) || ($group_by && !preg_match('/^[a-zA-Z0-9_]+$/', $group_by))) {
    echo json_encode(['status' => 'error', 'message' => 'Nombre de columna inválido']);
    exit;
}

try {
    if ($group_by) {
        // Query agrupada (ej: para gráficos de barras, pie)
        $sql = "SELECT {$group_by} as label, {$aggregation}({$target_column}) as value FROM {$table} WHERE deleted_at IS NULL GROUP BY {$group_by} ORDER BY value DESC";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $labels = [];
        $values = [];
        foreach ($results as $row) {
            $labels[] = $row['label'] ?? 'N/A';
            $values[] = (float)$row['value'];
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => [
                'labels' => $labels,
                'values' => $values
            ]
        ]);
        
    } else {
        // Query de métrica única (ej: para KPI Cards)
        // Ignoramos deleted_at si no existe en la tabla (premios, sedes, participaciones no tienen)
        $has_deleted_at = in_array($table, ['users', 'redes', 'eventos']);
        $where = $has_deleted_at ? "WHERE deleted_at IS NULL" : "";
        
        $sql = "SELECT {$aggregation}({$target_column}) as value FROM {$table} {$where}";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => [
                'value' => (float)$result['value']
            ]
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error ejecutando la consulta: ' . $e->getMessage()
    ]);
}
?>
