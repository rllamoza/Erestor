<?php
/**
 * ERESTOR - API Validación de Asistencia
 * PATCH /attendance/api/validar.php
 * Permite a admins validar, rechazar o dejar pendiente una marcación
 */
header("Content-Type: application/json");
require_once "../../../shared/core/db.php";
require_once "../../../shared/core/auth_helper.php";

$user = require_auth($pdo);
$allowedRoles = ["admin", "superadmin", "coordinador", "supervisor"];
if (!in_array($user["role"], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(["error" => "Sin permisos"]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

try {
    if ($method === "POST" || $method === "PATCH") {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int)($data["id"] ?? 0);
        $estado = $data["estado"] ?? null;
        $observaciones = $data["observaciones"] ?? null;

        if (!$id || !$estado) {
            http_response_code(400);
            echo json_encode(["error" => "id y estado son requeridos"]);
            exit;
        }
        if (!in_array($estado, ["validado", "rechazado", "pendiente"])) {
            http_response_code(400);
            echo json_encode(["error" => "estado invalido"]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE participaciones_evento SET estado = ?, observaciones = ?, validado_por = ? WHERE id = ?");
        $stmt->execute([$estado, $observaciones, $user["id"], $id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Registro no encontrado"]);
            exit;
        }

        // Audit log
        $pdo->prepare("INSERT INTO erestor_audit_log (user_id, accion, modulo, referencia_id, datos_extra, ip) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$user["id"], "validar_asistencia:$estado", "attendance", $id, json_encode(["estado" => $estado, "obs" => $observaciones]), $_SERVER["REMOTE_ADDR"] ?? null]);

        echo json_encode(["status" => "success", "message" => "Estado actualizado a: $estado"]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
