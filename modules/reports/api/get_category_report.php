<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);
if (!in_array($user['role'], ['admin', 'superadmin', 'coordinador', 'supervisor'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']); exit;
}

$category = $_GET['category'] ?? 'personal';
$dateStart = $_GET['start'] ?? date('Y-m-01');
$dateEnd = $_GET['end'] ?? date('Y-m-t');

try {
    $result = [];

    switch($category) {
        case 'personal':
            // R1: Directory
            $result['directory'] = $pdo->query("SELECT u.id, u.name, u.email, u.role, u.activo, r.nombre as red, a.nombre as area, s.nombre as sede FROM users u LEFT JOIN redes r ON u.red_id=r.id LEFT JOIN areas_servicio a ON u.area_id=a.id LEFT JOIN sedes s ON u.sede_id=s.id")->fetchAll();
            // R2: Altas/Bajas (History)
            $result['history'] = $pdo->query("SELECT status, reason, created_at FROM user_status_history ORDER BY created_at DESC LIMIT 50")->fetchAll();
            // R3 & R4: Orphans
            $result['no_network'] = $pdo->query("SELECT name, email FROM users WHERE red_id IS NULL AND activo=1")->fetchAll();
            $result['no_area'] = $pdo->query("SELECT name, email FROM users WHERE area_id IS NULL AND activo=1")->fetchAll();
            // R5: Roles Distribution
            $result['roles_chart'] = $pdo->query("SELECT role as label, COUNT(*) as count FROM users GROUP BY role")->fetchAll();
            break;

        case 'redes':
            // R7: Distribution
            $result['distribution'] = $pdo->query("SELECT r.nombre as label, COUNT(u.id) as count FROM redes r LEFT JOIN users u ON u.red_id=r.id WHERE r.deleted_at IS NULL GROUP BY r.id")->fetchAll();
            // R8: Ranking Absoluto
            $result['ranking_abs'] = $pdo->query("SELECT r.nombre, SUM(pe.puntos_obtenidos) as puntos FROM redes r JOIN users u ON u.red_id=r.id JOIN participaciones_evento pe ON pe.user_id=u.id GROUP BY r.id ORDER BY puntos DESC")->fetchAll();
            // R10: Growth
            $result['growth'] = $pdo->query("SELECT r.nombre, COUNT(u.id) as activos FROM redes r LEFT JOIN users u ON u.red_id=r.id WHERE u.activo=1 GROUP BY r.id")->fetchAll();
            break;

        case 'sedes':
            // R12: Influence TERRITORIAL
            $result['influence'] = $pdo->query("SELECT s.nombre as sede, r.nombre as red, COUNT(u.id) as count FROM sedes s JOIN users u ON u.sede_id=s.id JOIN redes r ON u.red_id=r.id GROUP BY s.id, r.id")->fetchAll();
            // R13: Sede Distribution
            $result['distribution'] = $pdo->query("SELECT s.nombre as label, COUNT(u.id) as count FROM sedes s LEFT JOIN users u ON u.sede_id=s.id GROUP BY s.id")->fetchAll();
            break;

        case 'eventos':
            // R16: Global Rate
            $totalAsig = $pdo->query("SELECT COUNT(*) FROM eventos_usuarios")->fetchColumn();
            $totalAsis = $pdo->query("SELECT COUNT(*) FROM participaciones_evento")->fetchColumn();
            $result['global_rate'] = $totalAsig > 0 ? round(($totalAsis/$totalAsig)*100, 2) : 0;
            // R19: Top Volunteers
            $result['top_volunteers'] = $pdo->query("SELECT u.name, COUNT(pe.id) as eventos FROM users u JOIN participaciones_evento pe ON pe.user_id=u.id GROUP BY u.id ORDER BY eventos DESC LIMIT 10")->fetchAll();
            // R21: Upcoming
            $result['upcoming'] = $pdo->query("SELECT nombre, fecha_inicio, puntos_asignados FROM eventos WHERE fecha_inicio >= CURRENT_DATE ORDER BY fecha_inicio ASC LIMIT 5")->fetchAll();
            break;

        case 'horas':
            // R22: Ranking Horas
            $result['ranking'] = $pdo->query("SELECT u.name, SUM(he.cantidad_horas) as total FROM users u JOIN horas_extras he ON he.user_id=u.id GROUP BY u.id ORDER BY total DESC LIMIT 15")->fetchAll();
            // R24: Evolution
            $result['evolution'] = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, SUM(cantidad_horas) as count FROM horas_extras GROUP BY mes ORDER BY mes DESC LIMIT 6")->fetchAll();
            break;
            
        case 'premios':
            $result['history'] = $pdo->query("SELECT p.nombre as premio, u.name as usuario, r.nombre as red, pe.created_at FROM rewards_history pe JOIN premios p ON pe.reward_id=p.id JOIN users u ON pe.user_id=u.id LEFT JOIN redes r ON u.red_id=r.id ORDER BY pe.created_at DESC LIMIT 20")->fetchAll();
            break;

        case 'areas':
            // R27: Distribution
            $result['distribution'] = $pdo->query("SELECT a.nombre as label, COUNT(u.id) as count FROM areas_servicio a LEFT JOIN users u ON u.area_id=a.id WHERE a.deleted_at IS NULL GROUP BY a.id")->fetchAll();
            break;

        case 'dashboard':
            // R29: Dashboard KPIs are mostly handled by get_full_stats.php
            // R30: Trends - Growth of active users per network over last 6 months
            $result['trends'] = $pdo->query("SELECT r.nombre, DATE_FORMAT(u.fecha_alta, '%Y-%m') as mes, COUNT(u.id) as count 
                                            FROM redes r 
                                            JOIN users u ON u.red_id = r.id 
                                            WHERE u.fecha_alta IS NOT NULL
                                            GROUP BY r.id, mes 
                                            ORDER BY mes ASC")->fetchAll();
            break;
    }

    echo json_encode(['status' => 'success', 'data' => $result]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
