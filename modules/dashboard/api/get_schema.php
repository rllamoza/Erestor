<?php
require_once '../../../shared/core/db.php';

header('Content-Type: application/json');

// Tablas permitidas por seguridad para el Dashboard Builder
$allowed_tables = [
    'users' => [
        'name' => 'Usuarios',
        'columns' => ['id' => 'Numérico', 'role' => 'Categoría', 'sede_id' => 'Categoría', 'area_id' => 'Categoría', 'activo' => 'Booleano', 'created_at' => 'Fecha']
    ],
    'redes' => [
        'name' => 'Redes',
        'columns' => ['id' => 'Numérico', 'activa' => 'Booleano', 'created_at' => 'Fecha']
    ],
    'sedes' => [
        'name' => 'Sedes',
        'columns' => ['id' => 'Numérico', 'distancia_maxima_m' => 'Numérico', 'activa' => 'Booleano']
    ],
    'eventos' => [
        'name' => 'Eventos',
        'columns' => ['id' => 'Numérico', 'red_id' => 'Categoría', 'sede_id' => 'Categoría', 'puntos_base' => 'Numérico', 'distancia_maxima_m' => 'Numérico', 'fecha_inicio' => 'Fecha', 'activo' => 'Booleano']
    ],
    'participaciones_evento' => [
        'name' => 'Participaciones (Asistencia)',
        'columns' => ['id' => 'Numérico', 'evento_id' => 'Categoría', 'user_id' => 'Categoría', 'puntos_obtenidos' => 'Numérico', 'created_at' => 'Fecha']
    ],
    'premios' => [
        'name' => 'Premios',
        'columns' => ['id' => 'Numérico', 'tipo' => 'Categoría', 'activo' => 'Booleano']
    ]
];

echo json_encode([
    'status' => 'success',
    'data' => $allowed_tables
]);
?>
