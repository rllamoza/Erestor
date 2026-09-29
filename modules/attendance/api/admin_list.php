<?php
header("Content-Type: application/json");
require_once "../../../shared/core/db.php";
require_once "../../../shared/core/auth_helper.php";

$user = require_auth($pdo);

$allowedRoles = ["admin", "superadmin", "coordinador", "supervisor"];
if (!in_array($user["role"], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para esta accion"]);
    exit;
}
try {
    $sql = "SELECT 
                p.id,
                p.created_at as hora_marcado,
                p.hora_entrada,
                p.puntos_obtenidos,
                p.estado,
                p.observaciones,
                p.foto_validacion,
                p.latitud_registro,
                p.longitud_registro,
                p.precision_gps,
                p.ip_equipo,
                p.user_agent,
                e.nombre as evento_nombre,
                e.tipo as evento_tipo,
                e.color as evento_color,
                e.fecha_inicio,
                u.id as user_id,
                u.name as usuario_nombre,
                u.email as usuario_email,
                u.dni as usuario_dni,
                u.avatar as usuario_avatar,
                u.role as usuario_role,
                r.nombre as red_nombre,
                s.nombre as sede_nombre,
                a.nombre as area_nombre
            FROM participaciones_evento p
            JOIN eventos e ON p.evento_id = e.id
            JOIN users u ON p.user_id = u.id
            LEFT JOIN redes r ON u.red_id = r.id
            LEFT JOIN sedes s ON u.sede_id = s.id
            LEFT JOIN areas_servicio a ON u.area_id = a.id
            WHERE 1=1";

    $params = [];
    if (!empty($_GET["fecha"])) {
        $sql .= " AND DATE(p.created_at) = ?";
        $params[] = $_GET["fecha"];
    }
    if (!empty($_GET["evento_id"])) {
        $sql .= " AND p.evento_id = ?";
        $params[] = (int)$_GET["evento_id"];
    }
    if (!empty($_GET["estado"])) {
        $sql .= " AND p.estado = ?";
        $params[] = $_GET["estado"];
    }
    if (!empty($_GET["q"])) {
        $q = "%" . trim($_GET["q"]) . "%";
        $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR e.nombre LIKE ? OR r.nombre LIKE ?)";
        $params = array_merge($params, [$q, $q, $q, $q]);
    }
    $sql .= " ORDER BY p.created_at DESC";
    $limit = (int)($_GET["limit"] ?? 200);
    $sql .= " LIMIT $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    $hoy = date("Y-m-d");
    $kpiStmt = $pdo->prepare("SELECT COUNT(*) as total_hoy, SUM(CASE WHEN foto_validacion IS NOT NULL AND foto_validacion != \"\" THEN 1 ELSE 0 END) as con_foto, SUM(CASE WHEN latitud_registro IS NOT NULL THEN 1 ELSE 0 END) as con_gps, SUM(CASE WHEN estado = \"validado\" THEN 1 ELSE 0 END) as validados, SUM(CASE WHEN estado = \"pendiente\" THEN 1 ELSE 0 END) as pendientes FROM participaciones_evento WHERE DATE(created_at) = ?");
    $kpiStmt->execute([$hoy]);
    $kpi = $kpiStmt->fetch();
    $eventosActivos = $pdo->query("SELECT COUNT(*) as activos FROM eventos WHERE activo = 1 AND NOW() BETWEEN fecha_inicio AND fecha_fin")->fetch()["activos"];
    $totalVoluntarios = $pdo->query("SELECT COUNT(*) as total FROM users WHERE activo = 1 AND deleted_at IS NULL")->fetch()["total"];

    echo json_encode([
        "status" => "success",
        "data" => $data,
        "kpi" => [
            "marcaciones_hoy" => (int)$kpi["total_hoy"],
            "con_foto" => (int)$kpi["con_foto"],
            "con_gps" => (int)$kpi["con_gps"],
            "validados" => (int)$kpi["validados"],
            "pendientes" => (int)$kpi["pendientes"],
            "precision_biometrica_pct" => $kpi["total_hoy"] > 0 ? round(($kpi["con_foto"] / $kpi["total_hoy"]) * 100, 1) : 0,
            "cobertura_gps_pct" => $kpi["total_hoy"] > 0 ? round(($kpi["con_gps"] / $kpi["total_hoy"]) * 100, 1) : 0,
            "eventos_activos" => (int)$eventosActivos,
            "total_voluntarios" => (int)$totalVoluntarios
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
