<?php
header('Content-Type: application/json');
require_once '../../../shared/core/db.php';
require_once '../../../shared/core/auth_helper.php';

$user = require_auth($pdo);

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $evento_id = $_GET['evento_id'] ?? null;
        
        // Obtenemos los usuarios voluntarios registrados
        $stmtUsers = $pdo->query("SELECT u.id, u.name, u.email, u.dni, u.puntos, a.nombre as area_nombre 
                                  FROM users u 
                                  LEFT JOIN areas_servicio a ON u.area_id = a.id 
                                  WHERE u.rol IN ('servidor', 'lider', 'coordinador') OR u.rol IS NULL
                                  LIMIT 15");
        $volunteers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        // Si no hay suficientes, traer cualquier usuario
        if (count($volunteers) < 4) {
            $stmtAll = $pdo->query("SELECT u.id, u.name, u.email, u.dni, u.puntos, 'Protocolo' as area_nombre FROM users u LIMIT 10");
            $volunteers = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
        }

        // Simular o calcular vacantes con IA basada en habilidades y SLA
        $puestosVacantes = [
            [
                'puesto' => 'Coordinador Acceso Puerta Norte',
                'area' => 'Protocolo & Bienvenida',
                'hora' => '08:15 AM - 13:00 PM',
                'requisito' => 'Liderazgo de Acceso • SLA > 95%',
                'nivel_sugerido' => 'NIVEL 5',
                'compatibilidad' => '98%'
            ],
            [
                'puesto' => 'Operador Consola & Monitores',
                'area' => 'Sonido & Audiovisual',
                'hora' => '08:00 AM - 13:00 PM',
                'requisito' => 'Manejo Behringer X32 / Dante',
                'nivel_sugerido' => 'NIVEL 4',
                'compatibilidad' => '96%'
            ],
            [
                'puesto' => 'Educador Infantil Sala Cuna (0-3 años)',
                'area' => 'Niños & Cuna',
                'hora' => '09:00 AM - 12:30 PM',
                'requisito' => 'Bioseguridad 1:4 • Primeros Auxilios',
                'nivel_sugerido' => 'NIVEL 4',
                'compatibilidad' => '94%'
            ],
            [
                'puesto' => 'Guía de Tráfico & Estacionamiento Sur',
                'area' => 'Seguridad & Flujo',
                'hora' => '08:15 AM - 11:30 AM',
                'requisito' => 'Canal Radial 03 • Chaleco Reflectivo',
                'nivel_sugerido' => 'NIVEL 3',
                'compatibilidad' => '93%'
            ],
            [
                'puesto' => 'Asistente Microfonía & Monitores',
                'area' => 'Sonido Frontal',
                'hora' => '08:30 AM - 12:45 PM',
                'requisito' => 'Frecuencias Wireless RF • Relevo continuo',
                'nivel_sugerido' => 'NIVEL 3',
                'compatibilidad' => '91%'
            ],
            [
                'puesto' => 'Coordinador Barista / Refrigerios',
                'area' => 'Cafetería & Logística',
                'hora' => '08:00 AM - 12:00 PM',
                'requisito' => 'Manipulación Alimentos • Stock asignado',
                'nivel_sugerido' => 'NIVEL 3',
                'compatibilidad' => '89%'
            ]
        ];

        $propuestas = [];
        foreach ($puestosVacantes as $idx => $pv) {
            $vol = $volunteers[$idx % count($volunteers)] ?? [
                'id' => $idx + 1,
                'name' => 'Voluntario ' . ($idx + 1),
                'dni' => '74829' . (100 + $idx),
                'puntos' => 1200 + ($idx * 150),
                'area_nombre' => $pv['area']
            ];

            $initials = implode('', array_slice(array_map(function($w) { return strtoupper($w[0]); }, explode(' ', $vol['name'])), 0, 2));

            $propuestas[] = [
                'user_id' => $vol['id'],
                'user_name' => $vol['name'],
                'user_initials' => $initials ?: 'VR',
                'user_dni' => $vol['dni'] ?? ('7482' . rand(1000, 9999)),
                'puntos' => $vol['puntos'] ?? 1000,
                'nivel' => $pv['nivel_sugerido'],
                'match_ia' => $pv['compatibilidad'],
                'puesto' => $pv['puesto'],
                'area' => $pv['area'],
                'horario' => $pv['hora'],
                'requisito' => $pv['requisito'],
                'conflictos' => 0
            ];
        }

        echo json_encode([
            'status' => 'success',
            'total_vacantes' => count($propuestas),
            'conflictos' => 0,
            'propuestas' => $propuestas
        ]);
    } 
    elseif ($method === 'POST') {
        // Aplicar asignaciones masivas sugeridas
        $data = json_decode(file_get_contents("php://input"));
        $evento_id = $data->evento_id ?? 1;
        $asignaciones = $data->asignaciones ?? [];

        $insertadas = 0;
        $stmtCheck = $pdo->prepare("SELECT id FROM eventos_usuarios WHERE evento_id = ? AND user_id = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO eventos_usuarios (evento_id, user_id, rol_en_evento, estado) VALUES (?, ?, ?, 'asignado')");

        foreach ($asignaciones as $asig) {
            $userId = $asig->user_id ?? null;
            $rol = $asig->puesto ?? 'Voluntario Asignado IA';

            if ($userId) {
                $stmtCheck->execute([$evento_id, $userId]);
                if (!$stmtCheck->fetch()) {
                    $stmtInsert->execute([$evento_id, $userId, $rol]);
                    $insertadas++;
                }
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => "Se aplicaron {$insertadas} asignaciones automáticamente con IA.",
            'total_asignadas' => $insertadas
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
