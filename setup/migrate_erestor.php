<?php
/**
 * ERESTOR ATTENDANCE TELEMETRY - DATABASE MIGRATION
 * Agrega campos y tablas necesarios para los nuevos diseños Stitch
 * Seguro: Solo agrega columnas/tablas si no existen (idempotente)
 */
require_once "../shared/core/db.php";

$log = [];

function runSafe($pdo, $sql, &$log) {
    try {
        $pdo->exec($sql);
        $log[] = "✅ OK: " . substr(trim($sql), 0, 80);
    } catch (PDOException $e) {
        // Ignorar errores de columna/tabla ya existente (1060 = duplicate column, 1050 = table exists)
        if (in_array($e->errorInfo[1], [1060, 1061, 1050, 1065])) {
            $log[] = "⏭️ YA EXISTE: " . substr(trim($sql), 0, 80);
        } else {
            $log[] = "❌ ERROR: " . $e->getMessage() . " | SQL: " . substr(trim($sql), 0, 80);
        }
    }
}

// 1. participaciones_evento - campo estado para auditoría
runSafe($pdo, "ALTER TABLE participaciones_evento ADD COLUMN estado ENUM(\"pendiente\",\"validado\",\"rechazado\") NOT NULL DEFAULT \"validado\" AFTER puntos_obtenidos", $log);

// 2. participaciones_evento - campo hora_entrada explícita (vs created_at que puede diferir)
runSafe($pdo, "ALTER TABLE participaciones_evento ADD COLUMN hora_entrada DATETIME NULL AFTER estado", $log);

// 3. participaciones_evento - campo observaciones para notas de auditoría
runSafe($pdo, "ALTER TABLE participaciones_evento ADD COLUMN observaciones TEXT NULL AFTER hora_entrada", $log);

// 4. participaciones_evento - campo validado_por (admin que validó manualmente)
runSafe($pdo, "ALTER TABLE participaciones_evento ADD COLUMN validado_por INT NULL AFTER observaciones", $log);

// 5. participaciones_evento - campo precision_gps para mostrar en UI
runSafe($pdo, "ALTER TABLE participaciones_evento ADD COLUMN precision_gps DECIMAL(8,2) NULL AFTER longitud_registro", $log);

// 6. eventos_usuarios - estado de presencia para el Kanban de asignaciones
runSafe($pdo, "ALTER TABLE eventos_usuarios ADD COLUMN estado ENUM(\"asignado\",\"confirmado\",\"ausente\",\"tardanza\") NOT NULL DEFAULT \"asignado\" AFTER rol_en_evento", $log);

// 7. eventos_usuarios - notas adicionales para el coordinador
runSafe($pdo, "ALTER TABLE eventos_usuarios ADD COLUMN notas VARCHAR(255) NULL AFTER estado", $log);

// 8. eventos - campo tipo para distinguir turnos regulares vs eventos especiales
runSafe($pdo, "ALTER TABLE eventos ADD COLUMN tipo ENUM(\"turno\",\"evento\",\"capacitacion\",\"emergencia\") NOT NULL DEFAULT \"turno\" AFTER nombre", $log);

// 9. eventos - campo capacidad maxima de voluntarios
runSafe($pdo, "ALTER TABLE eventos ADD COLUMN capacidad_max INT NULL DEFAULT 0 AFTER tipo", $log);

// 10. eventos - campo color para identificación visual en el kanban
runSafe($pdo, "ALTER TABLE eventos ADD COLUMN color VARCHAR(10) NULL DEFAULT \"#06B6D4\" AFTER capacidad_max", $log);

// 11. usuarios - campo dni para identificación biométrica
runSafe($pdo, "ALTER TABLE users ADD COLUMN dni VARCHAR(20) NULL AFTER name", $log);

// 12. usuarios - campo voluntario_desde para calcular antigüedad
runSafe($pdo, "ALTER TABLE users ADD COLUMN voluntario_desde DATE NULL AFTER sede_id", $log);

// 13. usuarios - campo estado_actividad para KPI telemetría en vivo
runSafe($pdo, "ALTER TABLE users ADD COLUMN estado_actividad ENUM(\"activo\",\"en_evento\",\"inactivo\",\"suspendido\") NOT NULL DEFAULT \"activo\" AFTER activo", $log);

// 14. tabla erestor_kpi_snapshots para telemetría en tiempo real
runSafe($pdo, "CREATE TABLE IF NOT EXISTS erestor_kpi_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    marcaciones_total INT DEFAULT 0,
    marcaciones_con_foto INT DEFAULT 0,
    marcaciones_con_gps INT DEFAULT 0,
    usuarios_activos INT DEFAULT 0,
    eventos_activos INT DEFAULT 0,
    promedio_precision_gps DECIMAL(8,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $log);

// 15. tabla erestor_audit_log para trazabilidad de acciones
runSafe($pdo, "CREATE TABLE IF NOT EXISTS erestor_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    referencia_id INT NULL,
    datos_extra JSON NULL,
    ip VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_modulo (modulo),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $log);

// 16. Actualizar valor default del tema en configuraciones a theme-erestor
runSafe($pdo, "INSERT INTO configuraciones (clave, valor, descripcion) VALUES (\"tema_predeterminado\", \"theme-erestor\", \"Tema visual predeterminado del sistema Erestor\") ON DUPLICATE KEY UPDATE valor = \"theme-erestor\"", $log);

// 17. Crear índice para búsquedas rápidas por fecha en participaciones
runSafe($pdo, "ALTER TABLE participaciones_evento ADD INDEX idx_created_date (created_at)", $log);

// 18. Crear índice para búsquedas de usuarios activos hoy
runSafe($pdo, "ALTER TABLE participaciones_evento ADD INDEX idx_evento_user (evento_id, user_id)", $log);

header("Content-Type: text/plain; charset=utf-8");
echo "=== MIGRACIÓN ERESTOR ATTENDANCE TELEMETRY ===\n";
echo "Fecha: " . date("Y-m-d H:i:s") . "\n\n";
foreach ($log as $line) {
    echo $line . "\n";
}
echo "\n✅ Migración completada.\n";
?>
