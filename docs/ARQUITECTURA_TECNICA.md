# 🏛️ Documentación de Arquitectura Técnica // ERESTOR OPS v4.2

## 1. Visión de Capas del Sistema

ERESTOR OPS implementa una arquitectura desacoplada y modular orientada a servicios ligeros (REST API) con una interfaz de usuario de alto impacto visual (*Single Page Experience* modular).

```
┌────────────────────────────────────────────────────────┐
│             CAPA DE PRESENTACIÓN (CLIENTE)             │
│  - TailwindCSS + Cyberpunk Design System               │
│  - Adaptive 3-in-1 Engine (Smartphone / iPad / Desktop)│
│  - Drag & Drop Kanban Engine                           │
│  - AI Simulation & Heuristic Balancing Canvas          │
│  - Web Biometric & Geolocation Service Workers         │
└───────────────────────────┬────────────────────────────┘
                            │ JSON sobre HTTPS / REST API
┌───────────────────────────▼────────────────────────────┐
│              CAPA DE APLICACIÓN (BACKEND)              │
│  - PHP 8.2+ con PDO Strict Mode                        │
│  - RateLimiter IP Protection                           │
│  - Role-Based Access Control (RBAC)                    │
│  - Audit Log Forense & Telemetry Snapshot Engine       │
└───────────────────────────┬────────────────────────────┘
                            │ PDO Prepared Statements
┌───────────────────────────▼────────────────────────────┐
│                 CAPA DE DATOS (MYSQL)                  │
│  - Tablas Relacionales (users, redes, sedes, eventos)  │
│  - Malla de Telemetría (erestor_kpi_snapshots)         │
│  - Trazabilidad y Seguridad (erestor_audit_log)        │
└────────────────────────────────────────────────────────┘
```

---

## 2. Especificación de Endpoints de la API

### 2.1 Autenticación y Perfil (`modules/auth/api/`)
- `POST /modules/auth/api/login.php`:
  - **Parámetros**: `{ "email": string, "password": string }` *(Acepta tanto correo institucional como DNI/Identificador táctico)*.
  - **Validaciones**: Rate limiting por IP, verificación de hash Bcrypt, control de cuenta activa.
  - **Respuesta Exitosa**: Retorna token de sesión, objeto `user` con roles y redirección contextual (Dashboard o Kiosco).
- `POST /modules/auth/api/update_profile.php`:
  - **Parámetros**: `{ "name", "phone", "area_id", "red_id", "password" }`.
  - **Restricción**: Omite y bloquea actualizaciones a `dni` y `email` para usuarios normales.
- `GET /modules/auth/api/get_profile.php`:
  - Retorna datos del usuario autenticado para inicializar vistas de perfil.

### 2.2 Asistencia y Kiosco (`modules/attendance/api/`)
- `POST /modules/attendance/api/registrar_asistencia.php`:
  - Registra marcación biométrica con foto opcional, coordenadas de geolocalización, cálculo de distancia submétrica y validación de regla anti-doble marcación.
- `GET /modules/attendance/api/get_auditoria.php`:
  - Retorna el log forense de marcaciones en tiempo real para la consola de supervisión.
- `GET /modules/attendance/api/get_kpi_telemetria.php`:
  - Retorna tasas de asistencia por sede, porcentaje de aforo en sitio y desglose de alertas de puntualidad.

### 2.3 Eventos, Asignaciones y Sandbox IA (`modules/events/api/`)
- `GET /modules/events/api/get_asignaciones.php`:
  - Retorna voluntarios y su estado en el evento activo (`asignado`, `confirmado`, `ausente`, `tardanza`).
- `POST /modules/events/api/actualizar_asignacion.php`:
  - Actualiza el estado o reasigna un voluntario mediante drag and drop.
- `POST /modules/events/api/simular_cobertura_ia.php`:
  - Ejecuta algoritmo heurístico de optimización para cubrir vacantes críticas basándose en roles compatibles y cercanía geográfica.

---

## 3. Modelo de Datos Relacional (Schema)

### Tabla `users`
- `id` (PK, INT Auto Increment)
- `name` (VARCHAR 100)
- `dni` (VARCHAR 20, INDEX) - Identificador biométrico / credencial
- `email` (VARCHAR 100, UNIQUE)
- `password` (VARCHAR 255)
- `role` (ENUM: `superadmin`, `admin`, `coordinador`, `supervisor`, `usuario`)
- `red_id` (FK -> `redes.id`, nullable)
- `sede_id` (FK -> `sedes.id`, nullable)
- `voluntario_desde` (DATE)
- `estado_actividad` (ENUM: `activo`, `en_evento`, `inactivo`, `suspendido`)
- `activo` (TINYINT 1)
- `created_at`, `updated_at`, `deleted_at`

### Tabla `redes`
- `id` (PK, INT Auto Increment)
- `nombre` (VARCHAR 100)
- `descripcion` (TEXT)
- `coordinador_id` (FK -> `users.id`)
- `activa` (TINYINT 1)

### Tabla `sedes`
- `id` (PK, INT Auto Increment)
- `nombre` (VARCHAR 100)
- `ubicacion` (VARCHAR 255)
- `latitud`, `longitud` (DECIMAL)
- `radio_geocerca_m` (INT DEFAULT 100)
- `capacidad_max` (INT)
- `activa` (TINYINT 1)

### Tabla `eventos`
- `id` (PK, INT Auto Increment)
- `serie_id` (VARCHAR 50, nullable)
- `nombre` (VARCHAR 100)
- `tipo` (ENUM: `turno`, `evento`, `capacitacion`, `emergencia`)
- `descripcion` (TEXT)
- `red_id` (FK -> `redes.id`)
- `sede_id` (FK -> `sedes.id`)
- `fecha_inicio`, `fecha_fin` (DATETIME)
- `capacidad_max` (INT)
- `color` (VARCHAR 10)
- `activo` (TINYINT 1)

### Tabla `eventos_usuarios` (Asignaciones)
- `id` (PK, INT Auto Increment)
- `evento_id` (FK -> `eventos.id`)
- `user_id` (FK -> `users.id`)
- `rol_en_evento` (VARCHAR 50)
- `estado` (ENUM: `asignado`, `confirmado`, `ausente`, `tardanza`)
- `notas` (VARCHAR 255)

### Tabla `participaciones_evento` (Marcaciones Forenses)
- `id` (PK, INT Auto Increment)
- `evento_id` (FK -> `eventos.id`)
- `user_id` (FK -> `users.id`)
- `foto_registro` (VARCHAR 255)
- `latitud_registro`, `longitud_registro` (DECIMAL)
- `precision_gps` (DECIMAL 8,2)
- `puntos_obtenidos` (INT)
- `estado` (ENUM: `pendiente`, `validado`, `rechazado`)
- `hora_entrada` (DATETIME)
- `observaciones` (TEXT)
- `validado_por` (INT, nullable)
- `created_at` (TIMESTAMP)

### Tabla `erestor_kpi_snapshots`
- `id` (PK, INT)
- `fecha` (DATE, UNIQUE)
- `marcaciones_total`, `marcaciones_con_foto`, `marcaciones_con_gps` (INT)
- `usuarios_activos`, `eventos_activos` (INT)
- `promedio_precision_gps` (DECIMAL 8,2)

---

## 4. Medidas de Seguridad Táctica

1. **Defensa contra Inyecciones**: 100% de consultas preparadas PDO con deshabilitación de `ATTR_EMULATE_PREPARES`.
2. **Defensa contra Brute-Force**: Clase `RateLimiter.php` que bloquea IPs que excedan 5 intentos fallidos en ventanas de 5 minutos.
3. **Session Fixation Prevention**: `session_regenerate_id(true)` ejecutado tras cada login exitoso.
4. **Content Security Policy (CSP)**: Cabeceras HTTP estrictas para neutralizar ataques XSS y Clickjacking.
5. **Principio de Mínimo Privilegio**: Aislamiento estricto de rutas y vistas para roles no autorizados.
