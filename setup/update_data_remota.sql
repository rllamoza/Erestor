-- SEGURIDAD: Desactivar temporalmente revisión de llaves foráneas para evitar conflictos de orden
SET FOREIGN_KEY_CHECKS = 0;

-- 1. ACTUALIZACIÓN DE ÁREAS DE SERVICIO
INSERT IGNORE INTO `areas_servicio` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Sistemas', 'Soporte técnico y desarrollo'),
(2, 'Producción', 'Multimedia y streaming'),
(3, 'Alabanza', 'Música y coros'),
(4, 'Fotografía', 'Captura de eventos'),
(5, 'Servidores', 'Atención y logística'),
(6, 'Kids', 'Ministerio infantil');

-- 2. ACTUALIZACIÓN DE SEDES (GEOLOCALIZACIÓN)
INSERT INTO `sedes` (`id`, `nombre`, `ubicacion`, `latitud`, `longitud`, `distancia_maxima_m`) VALUES
(5, 'SEDE LINCE', 'JR. RISSO 271, LINCE', -12.08380000, -77.03450000, 500),
(6, 'SEDE CENTRAL', 'CERCADO DE LIMA', -12.05730000, -77.05810000, 300),
(14, 'SEDE VILLA EL SALVADOR', 'AV. CENTRAL - UNTELS', -12.20350000, -76.93510000, 300)
ON DUPLICATE KEY UPDATE 
    latitud = VALUES(latitud), 
    longitud = VALUES(longitud), 
    distancia_maxima_m = VALUES(distancia_maxima_m);

-- 3. ACTUALIZACIÓN DE REDES (CON COD_MUJERES)
INSERT INTO `redes` (`id`, `codigo`, `nombre`, `lider`, `codmujeres`) VALUES
(4, 'A11', 'Red A11', 'CESAR CERVANTES', 'M-A11'),
(5, 'A12', 'Red A12', 'MIGUEL GARCIA', 'M-A12'),
(48, 'A2O', 'Red A2O', 'CRISTINA MANTILLA', 'M-A2O')
ON DUPLICATE KEY UPDATE 
    codmujeres = VALUES(codmujeres), 
    lider = VALUES(lider);

-- 4. CONFIGURACIONES GLOBALES
INSERT INTO `configuraciones` (`clave`, `valor`, `descripcion`) VALUES
('distancia_maxima_km', '0.5', 'Distancia máxima permitida para registrar asistencia'),
('latitud_fija', '-12.046374', 'Latitud del punto central'),
('longitud_fija', '-77.042793', 'Longitud del punto central')
ON DUPLICATE KEY UPDATE 
    valor = VALUES(valor);

-- RE-ACTIVAR REVISIÓN
SET FOREIGN_KEY_CHECKS = 1;
