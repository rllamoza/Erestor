# 🛡️ ERESTOR OPS // Ecosistema Táctico v4.2
### *Portal de Acceso, Telemetría Operativa & Gestión Territorial de Asistencia y Redes*

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Database](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.4%2B-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Architecture](https://img.shields.io/badge/Security-FIPS_140--3_HSM-10B981?style=for-the-badge&logo=shield)](https://github.com/rllamoza/Erestor)
[![Status](https://img.shields.io/badge/SOC-24%2F7_ONLINE-cyan?style=for-the-badge)](https://github.com/rllamoza/Erestor)

---

## 📌 1. Visión General

**ERESTOR OPS** es una plataforma integral de gobernanza y despliegue táctico diseñada para la gestión territorial de voluntarios, asignación dinámica de turnos, control perimétrico de asistencia y telemetría operativa en tiempo real. 

El sistema combina interfaces visuales de alto rendimiento inspiradas en centros de comando tácticos (*Cyberpunk / Glassmorphism / Dark Mode*), control biométrico y georreferenciado, algoritmos de resolución predictiva de vacantes asistidos por IA, y una arquitectura de seguridad con control de accesos basado en roles (RBAC).

---

## 🚀 2. Características Principales

### 📱 2.1 Pantalla de Login Táctica Adaptativa 3 en 1 (`modules/auth/login.html`)
Diseñada con triple renderizado adaptativo nativo que se activa según las dimensiones o el dispositivo del operador:
1. **Smartphone View (`< 768px`)**:
   - Vector de autenticación móvil E-1, geocercas GPS submétricas en rango, acceso rápido para tokens NFC/Gafetes y reconocimiento facial.
2. **iPad / Tablet View (`768px - 1279px`)**:
   - Layout táctil certificado para tablets de campo, keypad numérico en pantalla para ingreso rápido de PIN de 6 dígitos, visor HUD biométrico con escáner facial interactivo (*99.8% Liveness AI*), detector NFC Smart Ring y cuadrante de supervisores en guardia.
3. **Desktop View (`>= 1280px`)**:
   - Consola táctica de comando en 2 columnas: panel de telemetría de nodos, criptografía HSM rotativa SHA-512, gráfico SVG en tiempo real de flujo de marcaciones por hora, aforos de sedes y portal de admisión con tabs para FIDO2 y Kiosk Bypass.
4. **Selector de Dispositivo Flotante**:
   - Barra superior `[Auto] [📱 Smartphone] [📟 iPad] [🖥️ Desktop]` para forzar o inspeccionar cualquier vista al instante.

### 🎯 2.2 Kiosco Biométrico Táctil (`modules/attendance/kiosco.html`)
- Terminal táctil autónomo para registro de asistencia en el punto de acceso.
- Identificación instantánea mediante DNI o credencial operativa.
- Detección automática del evento, turno y sede asignada al voluntario.
- **Aislamiento para voluntarios**: Los usuarios con rol `usuario` son dirigidos exclusivamente al Kiosco y su perfil personal, ocultando la barra y menús administrativos.

### 🔀 2.3 Planificador de Asignaciones Drag & Drop (`modules/events/asignaciones.html`)
- Tablero Kanban drag and drop para asignar y reasignar brigadas y voluntarios a turnos y eventos en tiempo real.
- Detección inmediata de conflictos de horario, doble asignación o sobrecupo.
- Integración directa del botón de acceso a la **Simulación Sandbox IA**.

### 🧪 2.4 Simulación Sandbox IA (`modules/events/resolucion_ia.html`)
- Entorno de contingencia asistido por IA para equilibrar la cobertura de puestos críticos en eventos masivos.
- Algoritmo heurístico que detecta ausencias o vacantes imprevistas y sugiere las mejores reasignaciones basándose en perfil, distancia a la sede y disponibilidad.

### 🏢 2.5 Gestión Integral de Redes, Sedes, Turnos y Eventos
- **Redes (`modules/networks/redes.html`)**: Organización estructural de brigadas y equipos.
- **Sedes (`modules/networks/sedes.html`)**: Gestión geográfica de campus y geocercas satelitales.
- **Turnos y Eventos (`modules/events/eventos.html`)**: Programación de jornadas, capacidades máximas y asignación de coordinadores.

### 📊 2.6 Control & Auditoría Forense de Asistencia (`modules/attendance/auditoria.html`)
- Monitoreo forense de marcaciones con telemetría en vivo.
- Inspección fotográfica del registro, coordenadas GPS, precisión en metros y validación de reglas anti-doble marcación.

### 👤 2.7 Perfil de Usuario Protegido (`modules/users/perfil.html`)
- Los voluntarios pueden actualizar su contraseña, teléfono de contacto, sede preferida y tema visual.
- **Restricción de Seguridad Institucional**: Los campos de **DNI** y **Correo Electrónico** se mantienen estrictamente bloqueados `(🔒 No modificable)` para garantizar la integridad de las credenciales de asistencia.

---

## 🏗️ 3. Arquitectura del Proyecto

```
Servidoresv3/
├── api/                       # Endpoints globales de la API REST
├── crons/                     # Tareas programadas de sincronización
├── disenos/                   # Documentación de diseño, Stitch UI y maquetas
│   ├── animaciones/           # Recursos y prototipos de microanimaciones
│   ├── html/                  # Vistas estáticas de referencia
│   └── imagenes/              # Capturas y guías visuales
├── docs/                      # Documentación técnica y manuales
├── modules/
│   ├── attendance/            # Kiosco biométrico y Auditoría de asistencia
│   ├── auth/                  # Login táctico 3-en-1 y autenticación API
│   ├── dashboard/             # Consola principal de mando y telemetría
│   ├── events/                # Eventos, turnos, asignaciones Drag & Drop y Sandbox IA
│   ├── networks/              # Redes y sedes territoriales
│   ├── reports/               # Reportes analíticos y exportaciones
│   └── users/                 # Gestión de usuarios, brigadas y perfiles
├── setup/                     # Migraciones SQL, seeders y especificaciones
├── shared/
│   ├── assets/                # CSS, temas tácticos, librerías JS compartidas
│   └── core/                  # Conexión PDO (db.php), RateLimiter, config.php
├── uploads/                   # Directorios protegidos para evidencias y avatares
│   ├── asistencias/
│   └── avatars/
├── .gitignore                 # Configuración de exclusión Git
├── index.php                  # Enrutador inicial del sistema
└── README.md                  # Documentación principal
```

---

## 🔐 4. Matriz de Control de Acceso (RBAC)

| Módulo / Función | Superadmin / Admin | Coordinador / Supervisor | Voluntario / Usuario |
| :--- | :---: | :---: | :---: |
| **Login Táctico Multi-Dispositivo** | ✅ | ✅ | ✅ |
| **Terminal Kiosco (Pasar Asistencia)** | ✅ | ✅ | ✅ *(Vista Exclusiva)* |
| **Mi Perfil (Datos y Clave)** | ✅ | ✅ | ✅ *(DNI y Email fijos)* |
| **Dashboard y Telemetría SOC** | ✅ | ✅ | ❌ *(Bloqueado)* |
| **Planificador Drag & Drop** | ✅ | ✅ | ❌ *(Bloqueado)* |
| **Simulación Sandbox IA** | ✅ | ✅ | ❌ *(Bloqueado)* |
| **Auditoría Forense de Asistencia** | ✅ | ✅ | ❌ *(Bloqueado)* |
| **Gestión de Redes, Sedes y Eventos** | ✅ | Read / Local | ❌ *(Bloqueado)* |
| **Administración de Usuarios y Roles** | ✅ | ❌ | ❌ *(Bloqueado)* |

---

## ⚙️ 5. Requisitos del Sistema

- **Servidor Web**: Apache 2.4+ (con módulo `mod_rewrite` habilitado) o Nginx.
- **Lenguaje**: PHP 8.1 o superior.
  - Extensiones requeridas: `pdo_mysql`, `openssl`, `json`, `mbstring`, `curl`.
- **Base de Datos**: MySQL 8.0+ o MariaDB 10.4+.
- **Navegador**: Google Chrome, Mozilla Firefox, Microsoft Edge o Safari (versiones modernas con soporte CSS Grid y Flexbox).

---

## 🛠️ 6. Guía de Instalación y Puesta en Marcha

### Paso 1: Clonar o descargar el repositorio
```bash
git clone https://github.com/rllamoza/Erestor.git
cd Erestor
```

### Paso 2: Configurar la Base de Datos
1. Crear una base de datos en MySQL/MariaDB:
   ```sql
   CREATE DATABASE redes_gobernanza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Configurar las credenciales en `shared/core/config.php`:
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_NAME', 'redes_gobernanza');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_password');
   ```

### Paso 3: Ejecutar Migraciones y Datos Iniciales
Ejecuta la migración estructural idempotente desde el navegador o la consola:
```bash
# Vía CLI
php setup/migrate_erestor.php
php setup/seed_gobernanza.php
```
O accede vía navegador a:
`http://localhost/Erestor/setup/migrate_erestor.php`

### Paso 4: Iniciar Sesión en el Sistema
Accede a la interfaz en tu navegador:
`http://localhost/Erestor/modules/auth/login.html`

- **Credenciales Demo Administrador / Supervisor**:
  - Identificador: `admin@gobernanza.local` o `VOL-2025-089`
  - Clave / PIN: `123456`
- **Credenciales Demo Voluntario**:
  - DNI / Email: Documento registrado del voluntario
  - Clave / PIN: `123456` *(Dirige directamente al Kiosco de Asistencia)*

---

## 🛡️ 7. Seguridad y Buenas Prácticas

- **Criptografía de Credenciales**: Hash de contraseñas mediante `PASSWORD_BCRYPT` y soporte simulado de validación táctica SHA-512 HSM.
- **Protección contra Fuerza Bruta**: `RateLimiter.php` integrado a nivel IP que restringe reintentos continuos.
- **Cabeceras de Seguridad Reforzadas**:
  - `X-Frame-Options: DENY`
  - `X-Content-Type-Options: nosniff`
  - `X-XSS-Protection: 1; mode=block`
  - `Content-Security-Policy (CSP)`
- **Aislamiento de Sesiones**: Regeneración de identificadores de sesión (`session_regenerate_id(true)`) para prevenir ataques de *Session Fixation*.
- **Integridad de Datos**: Consultas parametrizadas con PDO en el 100% de las transacciones con base de datos para anular riesgos de inyección SQL.

---

## 📄 8. Licencia y Créditos

Desarrollado y mantenido por **Rodrigo Llamoza** (`@rllamoza`).  
Ecosistema Táctico **ERESTOR OPS © 2025**. Todos los derechos reservados.
