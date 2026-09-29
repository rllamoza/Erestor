<?php
/**
 * ERESTOR - API de Telemetría KPI en Tiempo Real
 * GET /api/telemetry/kpi.php
 * Provee los datos para las Bento KPI Cards del dashboard ejecutivo
 */
header("Content-Type: application/json");
require_once "../../shared/core/db.php";
require_once "../../shared/core/auth_helper.php";

$user = require_auth($pdo);

try {
    $hoy = date("Y-m-d");
    $ahora = date("Y-m-d H:i:s");

    // ─── 1. KPIs de Asistencia de Hoy ──────────────────────────────────────
    $kpiAsis = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN foto_validacion IS NOT NULL AND foto_validacion != '' THEN 1 ELSE 0 END) as con_foto,
            SUM(CASE WHEN latitud_registro IS NOT NULL THEN 1 ELSE 0 END) as con_gps,
            SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes
        FROM participaciones_evento
        WHERE DATE(created_at) = ?
    ");
    $kpiAsis->execute([$hoy]);
    $asis = $kpiAsis->fetch();

    // ─── 2. Eventos activos en este momento ────────────────────────────────
    $eventosActStmt = $pdo->query("
        SELECT COUNT(*) as activos, 
               GROUP_CONCAT(nombre SEPARATOR ', ') as nombres
        FROM eventos
        WHERE activo = 1 AND NOW() BETWEEN fecha_inicio AND fecha_fin
    ");
    $eventosAct = $eventosActStmt->fetch();

    // ─── 3. Voluntarios activos totales ────────────────────────────────────
    $volStmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN estado_actividad = 'en_evento' THEN 1 ELSE 0 END) as en_evento,
            SUM(CASE WHEN estado_actividad = 'activo' THEN 1 ELSE 0 END) as activos
        FROM users 
        WHERE activo = 1 AND deleted_at IS NULL
    ");
    $vol = $volStmt->fetch();

    // ─── 4. Ranking de redes esta semana ───────────────────────────────────
    $rankingStmt = $pdo->query("
        SELECT 
            r.nombre,
            r.codigo,
            COUNT(pe.id) as asistencias,
            COALESCE(SUM(pe.puntos_obtenidos), 0) as puntos
        FROM redes r
        LEFT JOIN users u ON u.red_id = r.id AND u.activo = 1
        LEFT JOIN participaciones_evento pe ON pe.user_id = u.id 
            AND YEARWEEK(pe.created_at, 1) = YEARWEEK(NOW(), 1)
        WHERE r.activa = 1 AND r.deleted_at IS NULL
        GROUP BY r.id, r.nombre, r.codigo
        ORDER BY puntos DESC, asistencias DESC
        LIMIT 10
    ");
    $ranking = $rankingStmt->fetchAll();

    // ─── 5. Timeline de asistencias de hoy (por hora) ──────────────────────
    $timelineStmt = $pdo->prepare("
        SELECT 
            HOUR(created_at) as hora,
            COUNT(*) as cantidad
        FROM participaciones_evento
        WHERE DATE(created_at) = ?
        GROUP BY HOUR(created_at)
        ORDER BY hora ASC
    ");
    $timelineStmt->execute([$hoy]);
    $timeline = $timelineStmt->fetchAll();

    // ─── 6. Alertas y pendientes ───────────────────────────────────────────
    $alertasStmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM participaciones_evento
        WHERE estado = 'pendiente' AND DATE(created_at) = ?
    ");
    $alertasStmt->execute([$hoy]);
    $alertas = $alertasStmt->fetch()['total'];

    // ─── 7. Últimas 5 marcaciones ──────────────────────────────────────────
    $ultimasStmt = $pdo->query("
        SELECT 
            pe.created_at,
            pe.estado,
            pe.puntos_obtenidos,
            u.name,
            u.avatar,
            e.nombre as evento,
            r.nombre as red
        FROM participaciones_evento pe
        JOIN users u ON pe.user_id = u.id
        JOIN eventos e ON pe.evento_id = e.id
        LEFT JOIN redes r ON u.red_id = r.id
        ORDER BY pe.created_at DESC
        LIMIT 5
    ");
    $ultimas = $ultimasStmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "timestamp" => $ahora,
        "kpi" => [
            "marcaciones_hoy" => (int)$asis["total"],
            "con_foto" => (int)$asis["con_foto"],
            "con_gps" => (int)$asis["con_gps"],
            "pendientes_revision" => (int)$asis["pendientes"],
            "precision_biometrica_pct" => $asis["total"] > 0
                ? round(($asis["con_foto"] / $asis["total"]) * 100, 1) : 0,
            "cobertura_gps_pct" => $asis["total"] > 0
                ? round(($asis["con_gps"] / $asis["total"]) * 100, 1) : 0,
            "eventos_activos" => (int)$eventosAct["activos"],
            "eventos_activos_nombres" => $eventosAct["nombres"] ?? "",
            "voluntarios_total" => (int)$vol["total"],
            "voluntarios_en_evento" => (int)$vol["en_evento"],
            "voluntarios_activos" => (int)$vol["activos"],
            "alertas_pendientes" => (int)$alertas
        ],
        "ranking_semanal" => $ranking,
        "timeline_hoy" => $timeline,
        "ultimas_marcaciones" => $ultimas
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
