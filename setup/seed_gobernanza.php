<?php
require_once 'c:/xampp/htdocs/Servidoresv3/shared/core/db.php';

// Check if these main campuses exist in sedes, if not update or insert
$campuses = [
    [
        'nombre' => 'Sede Central (Campus Principal)',
        'ubicacion' => 'Av. El Sol 450 • Cobertura Multidominio • Sala Plenaria & Aulas A-D',
        'latitud' => -12.08840000,
        'longitud' => -77.03010000,
        'distancia_maxima_m' => 150,
        'activa' => 1
    ],
    [
        'nombre' => 'Sede Norte (Megacentro)',
        'ubicacion' => 'Carretera Panamericana Km 14 • 2 Niveles • Nave Industrial Adaptada',
        'latitud' => -11.95930000,
        'longitud' => -77.07250000,
        'distancia_maxima_m' => 120,
        'activa' => 1
    ],
    [
        'nombre' => 'Sede Sur (Distrito Tecnológico)',
        'ubicacion' => 'Paseo de la Floresta 880 • Centro Cívico Sur • Conexión Metro L1',
        'latitud' => -12.14690000,
        'longitud' => -76.99420000,
        'distancia_maxima_m' => 100,
        'activa' => 1
    ],
    [
        'nombre' => 'Sede Este (Valle Oriental)',
        'ubicacion' => 'Av. Las Palmeras 120 • Misión de Expansión Territorial',
        'latitud' => -11.97950000,
        'longitud' => -76.99770000,
        'distancia_maxima_m' => 80,
        'activa' => 1
    ]
];

foreach ($campuses as $c) {
    $stmt = $pdo->prepare("SELECT id FROM sedes WHERE nombre = ? LIMIT 1");
    $stmt->execute([$c['nombre']]);
    $id = $stmt->fetchColumn();
    if ($id) {
        $up = $pdo->prepare("UPDATE sedes SET ubicacion = ?, latitud = ?, longitud = ?, distancia_maxima_m = ?, activa = 1, deleted_at = NULL WHERE id = ?");
        $up->execute([$c['ubicacion'], $c['latitud'], $c['longitud'], $c['distancia_maxima_m'], $id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO sedes (nombre, ubicacion, latitud, longitud, distancia_maxima_m, activa) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([$c['nombre'], $c['ubicacion'], $c['latitud'], $c['longitud'], $c['distancia_maxima_m'], $c['activa']]);
    }
}

// Redes
$redesData = [
    [
        'codigo' => 'RJ',
        'codmujeres' => 'RJ-M',
        'nombre' => 'Red de Jóvenes (Generación Xtreme)',
        'lider' => 'Mateo Rivas',
        'descripcion' => 'Brigada juvenil de alto despliegue',
        'activa' => 1
    ],
    [
        'codigo' => 'RP',
        'codmujeres' => 'RP-M',
        'nombre' => 'Red de Profesionales & Técnicos',
        'lider' => 'Luciana Solís',
        'descripcion' => 'Especialistas multimedia, logística y educación',
        'activa' => 1
    ],
    [
        'codigo' => 'RM',
        'codmujeres' => 'RM-M',
        'nombre' => 'Red de Matrimonios & Familias',
        'lider' => 'Marcos Benítez',
        'descripcion' => 'Pastoral familiar y soporte comunitario',
        'activa' => 1
    ],
    [
        'codigo' => 'RI',
        'codmujeres' => 'RI-M',
        'nombre' => 'Red Intercesión, Protocolo & Servicio',
        'lider' => 'Carlos Mendoza',
        'descripcion' => 'Recepción, hospitalidad, acomodación y filtro',
        'activa' => 1
    ],
    [
        'codigo' => 'DF',
        'codmujeres' => 'DF-M',
        'nombre' => 'Damas de Fe',
        'lider' => 'Camila Rivas',
        'descripcion' => 'Ministerio femenino y apoyo pastoral',
        'activa' => 1
    ],
    [
        'codigo' => 'HV',
        'codmujeres' => 'HV-M',
        'nombre' => 'Hombres de Valor',
        'lider' => 'David Paredes',
        'descripcion' => 'Seguridad perimétrica, logística pesada y montajes',
        'activa' => 1
    ]
];

foreach ($redesData as $r) {
    $stmt = $pdo->prepare("SELECT id FROM redes WHERE codigo = ? OR nombre = ? LIMIT 1");
    $stmt->execute([$r['codigo'], $r['nombre']]);
    $id = $stmt->fetchColumn();
    if ($id) {
        $up = $pdo->prepare("UPDATE redes SET nombre = ?, lider = ?, descripcion = ?, activa = 1, deleted_at = NULL WHERE id = ?");
        $up->execute([$r['nombre'], $r['lider'], $r['descripcion'], $id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO redes (codigo, codmujeres, nombre, lider, descripcion, activa) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([$r['codigo'], $r['codmujeres'], $r['nombre'], $r['lider'], $r['descripcion'], $r['activa']]);
    }
}

echo "Gobernanza Sedes and Redes seeded successfully.\n";
