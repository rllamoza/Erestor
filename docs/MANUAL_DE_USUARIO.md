# 📖 Manual de Operaciones y Usuario // ERESTOR OPS v4.2

## 1. Introducción al Sistema
ERESTOR OPS está diseñado para facilitar la administración operativa de brigadas y eventos en organizaciones con alta exigencia logística y territorial. 

Dependiendo del rol asignado a su credencial, la interfaz mostrará las herramientas que le correspondan:

---

## 2. Guía por Rol de Usuario

### 2.1 Rol: Voluntario / Usuario General
**Propósito:** Registrar asistencia y mantener actualizados sus canales de comunicación.

1. **Acceso al Sistema:**
   - Ingrese su DNI o correo institucional y su PIN/clave en el portal de login.
   - El sistema detectará su perfil y lo redirigirá inmediatamente al **Terminal Kiosco de Asistencia**.
2. **Marcación de Asistencia:**
   - En el Kiosco, el sistema detecta de forma automática el evento y turno activo al que está asignado el voluntario.
   - Presione el botón de confirmación táctil o escaneo facial para registrar su presencia.
   - La plataforma registrará la marca de tiempo exacta, su verificación facial y la geocerca de la sede.
3. **Gestión de Mi Perfil:**
   - Acceda mediante el enlace superior a `Mi Perfil`.
   - Podrá modificar:
     - Nombre para mostrar
     - Teléfono celular o WhatsApp de contacto
     - Sede o Red preferida
     - Contraseña de acceso
     - Tema visual (Cyberpunk Neón / Dark Táctico)
   - **Nota de Seguridad:** Por normativa institucional, los campos **DNI** y **Correo Electrónico** están protegidos y bloqueados contra edición manual. Si requiere actualizarlos, solicítelo al coordinador de su sede.

---

### 2.2 Rol: Supervisor / Coordinador de Sede
**Propósito:** Coordinar brigadas, auditar marcaciones y gestionar la cobertura de turnos.

1. **Dashboard Táctico:**
   - Visualización de indicadores clave (KPIs): Aforo en sitio, porcentaje de puntualidad, tasa de doble marcación prevenida y estado de los torniquetes/kioscos.
2. **Planificador Drag & Drop (`Asignaciones`):**
   - Panel visual estilo Kanban con columnas por evento/turno.
   - Arrastre las tarjetas de los voluntarios para reasignarlos entre turnos o sedes.
   - El sistema alertará visualmente si una persona ya tiene un turno superpuesto o si se excede el aforo del recinto.
3. **Simulación Sandbox IA:**
   - Ubicado mediante el botón `[🧪 Simulación Sandbox IA]` dentro del menú de Asignaciones.
   - Utilice esta herramienta ante contingencias masivas o emergencias.
   - El motor de IA analiza las vacantes críticas y redistribuye al personal disponible más cercano con roles compatibles.
4. **Auditoría Forense de Asistencia:**
   - Revise el listado de marcaciones en tiempo real con evidencia fotográfica y precisión métrica del GPS.
   - Valide o descarte marcaciones anómalas registradas fuera de la geocerca.

---

### 2.3 Rol: Superadmin / Administrador Central
**Propósito:** Configuración global de la plataforma, sedes territoriales y catálogo de usuarios.

1. **Gestión de Sedes y Geocercas:**
   - Alta y edición de sedes con delimitación de radio perimétrico en metros y capacidad máxima de voluntarios.
2. **Gestión de Redes:**
   - Creación de equipos de brigadas y asignación de coordinadores responsables.
3. **Programación de Eventos y Series:**
   - Generación de turnos regulares o eventos especiales con calendario unificado.
4. **Administración de Usuarios:**
   - Alta masiva de voluntarios mediante importación de archivos CSV o registro individual con generación de credenciales tácticas iniciales.

---

## 3. Resolución de Problemas Frecuentes

| Problema | Causa Probable | Solución |
| :--- | :--- | :--- |
| **"Credenciales no autorizadas"** | DNI o contraseña errónea | Verifique ingresar el DNI con el formato correcto o solicite reinicio de PIN a su coordinador. |
| **"Demasiados intentos fallidos"** | Rate limiter activado (5 intentos erróneos) | Espere 5 minutos para que el firewall táctico rehabilite el intento desde su IP. |
| **"Ubicación fuera de geocerca"** | GPS con baja precisión o lejanía de la sede | Asegúrese de activar el permiso de ubicación del navegador y encontrarse dentro del radio del campus. |
| **"No puedo cambiar mi DNI o correo"** | Restricción de seguridad institucional | Es el comportamiento esperado. Solo un Superadmin puede modificar estas claves primarias. |
