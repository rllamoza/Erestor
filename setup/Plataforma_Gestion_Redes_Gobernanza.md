# Plataforma de Gestión por Redes con Hamuy Territorial

## 1. Visión General

Sistema empresarial para gestionar redes organizacionales con: -
Hamuy territorial por mayoría en sedes - Ranking doble (absoluto y
ajustado) - Sistema de premios - Reportes estratégicos - Arquitectura
híbrida (local / nube mediante .env)

Arquitectura: - Backend: PHP 8.2 + Laravel - Base de datos: MySQL 8+ -
Autenticación: JWT - API REST versionada (/api/v1) - Frontend SPA
desacoplado

------------------------------------------------------------------------

# 2. Arquitectura General

## 2.1 Backend

-   Laravel 10+ (Prototipado en PHP PDO Vainilla)
-   Patrón Repository
-   Services Layer
-   Jobs en cola
-   Middleware por rol
-   Soft Deletes
-   Auditoría

Estructura:

app/ ├── Models ├── Http/ │ ├── Controllers │ ├── Middleware ├──
Services ├── Repositories ├── Jobs ├── Policies ├── DTO

database/ ├── migrations ├── seeders

------------------------------------------------------------------------

# 2.2 Mejoras Añadidas en Desarrollo (Prototipo V1)

Durante la construcción del prototipo funcional, se añadieron las siguientes mejoras y reglas obligatorias:

1. **Diseño Visual Neón (Dark Theme)**: Toda la interfaz UI aplica estética Cyberpunk / Dark Neón (Cian, Rosa, Morado) con utilidades de Glassmorphism.
2. **Dashboard Gráfico**: Integración de métricas de crecimiento y distribuciones demográficas usando Chart.js con un Glow effect plugin propio.
3. **Navegabilidad (UX)**: Implementación de un Menú Lateral (Sidebar) Responsivo y botón de Menú Hamburguesa en dispositivos móviles para acceder a todos los CRUDs y reportes.
4. **Seguridad (DB Role)**: Desvinculación de los accesos Root por defecto. Se utiliza un usuario específico (`usr_asi_app`) aislado únicamente a la base de datos `redes_Hamuy` centralizado a través de un archivo global `api/config.php`.
5. **Acciones de Productividad (Importación)**: Los módulos principales (Usuarios, Redes, Sedes) incluyen accesos dedicados en la interfaz para añadir nuevos registros "+ NUEVO" y habilitan cargas masivas a partir de archivos con el botón "↑ IMPORTAR CSV/EXCEL".

------------------------------------------------------------------------

# 3. Modelo de Base de Datos

## users

-   id (PK)
-   name
-   email (unique)
-   password
-   role (superadmin, coordinador, supervisor, usuario)
-   red_id (FK nullable)
-   sede_id (FK)
-   activo (boolean)
-   timestamps
-   deleted_at

## redes

-   id
-   nombre
-   descripcion
-   coordinador_id (FK users)
-   activa
-   timestamps
-   deleted_at

## sedes

-   id
-   nombre
-   ubicacion
-   activa
-   timestamps
-   deleted_at

## eventos

-   id
-   nombre
-   descripcion
-   red_id (FK)
-   sede_id (FK nullable)
-   fecha_inicio
-   fecha_fin
-   puntos_base
-   activo
-   timestamps
-   deleted_at

## participaciones_evento

-   id
-   evento_id (FK)
-   user_id (FK)
-   puntos_obtenidos
-   created_at

## horas_extras

-   id
-   user_id (FK)
-   cantidad_horas
-   fecha
-   observacion
-   created_at

## tardanzas

-   id
-   user_id (FK)
-   minutos
-   fecha
-   observacion
-   created_at

## premios

-   id
-   nombre
-   descripcion
-   tipo (red / usuario)
-   criterio
-   activo
-   created_at

## ranking_snapshots

-   id
-   red_id
-   total_puntos
-   promedio_puntos
-   fecha_calculo
-   created_at

------------------------------------------------------------------------

# 4. Reglas de Negocio

## 4.1 Puntos Totales por Red

Total puntos = SUM(puntos_obtenidos) + SUM(horas_extras)

Las tardanzas: - No restan puntos - Solo se muestran en reportes

------------------------------------------------------------------------

## 4.2 Ranking Absoluto

Ordenado por: Total puntos acumulados

------------------------------------------------------------------------

## 4.3 Ranking Ajustado

Total puntos / número de miembros activos

------------------------------------------------------------------------

## 4.4 Índice de Influencia por Sede

Fórmula:

Miembros red en sede / total miembros en sede

Si resultado \> 0.5: Red dominante en esa sede

------------------------------------------------------------------------

# 5. API REST

Base: /api/v1

## Auth

POST /auth/login\
POST /auth/logout\
POST /auth/refresh

## Usuarios

GET /users\
POST /users\
GET /users/{id}\
PUT /users/{id}\
DELETE /users/{id}

## Redes

CRUD completo

## Sedes

CRUD completo

## Eventos

CRUD completo

## Reportes

GET /reportes/ranking-absoluto\
GET /reportes/ranking-ajustado\
GET /reportes/influencia-sedes\
GET /reportes/horas-extras\
GET /reportes/tardanzas\
GET /reportes/top-redes\
GET /reportes/crecimiento-mensual

------------------------------------------------------------------------

# 6. Seguridad

-   JWT
-   Middleware por rol
-   Rate limiting
-   Validaciones robustas
-   Protección contra SQL injection
-   Logs de auditoría
-   API versionada

------------------------------------------------------------------------

# 7. Configuración Híbrida

Archivo .env:

APP_ENV=local \| production

Variables: DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD

Sistema cambia configuración automáticamente según entorno.

------------------------------------------------------------------------

# 8. Motor Automático de Ranking

-   Job en cola para recalcular ranking cuando:
    -   Se registra evento
    -   Se agregan horas extra
    -   Se modifica participación
-   Snapshot diario automático
-   Cache Redis para optimización

------------------------------------------------------------------------

# 9. Sistema de Premios

Criterios: - Mayor ranking absoluto - Mayor ranking ajustado - Mayor
crecimiento mensual - Mayor influencia territorial

------------------------------------------------------------------------

# 10. Indicadores Estratégicos

-   Red con mayor expansión
-   Sede más competitiva
-   Participación promedio por red
-   Evolución histórica

------------------------------------------------------------------------

# 11. Plan de Implementación

## Sprint 1

-   Autenticación
-   CRUD usuarios
-   CRUD redes
-   CRUD sedes

## Sprint 2

-   Eventos
-   Participaciones
-   Horas extra
-   Tardanzas

## Sprint 3

-   Motor ranking
-   Influencia territorial
-   Snapshots

## Sprint 4

-   Dashboard frontend
-   Gráficos comparativos

## Sprint 5

-   Sistema de premios
-   Exportación Excel / PDF
-   Auditoría completa

------------------------------------------------------------------------

# Resultado Esperado

Plataforma empresarial robusta con:

-   Hamuy estructurada
-   Competencia saludable
-   Métricas estratégicas
-   Escalabilidad
-   Seguridad avanzada
