# Catálogo de Animaciones y Microinteracciones - Erestor

Este documento compila todas las animaciones, curvas de aceleración y efectos interactivos implementados en el sistema de diseño de **Erestor - Sistema de Asistencia de Personal y Voluntarios**.

---

## 1. Animaciones de Escáner y Telemetría

### `laserSweep` (Barredor Láser Biométrico)
- **Selector:** `.animate-laser`
- **Duración:** 3.2s (`ease-in-out`, infinita)
- **Propiedades animadas:** `top` (6% → 92% → 6%), `opacity` (0.9 → 0.4 → 0.9)
- **Uso:** Línea de haz cian (`#22D3EE`) horizontal que barre el visor de reconocimiento facial y código QR.

### `cyanAura` (Resplandor Perimetral de Escáner)
- **Selector:** `.scanner-glow`
- **Duración:** 4s (`ease-in-out`, infinita)
- **Propiedades animadas:** `box-shadow` (24px a 38px de radio de difusión)
- **Uso:** Marco exterior del visor de cámara en el kiosco táctil.

### `pingRadar` (Onda de Estado Activo)
- **Selector:** `.animate-ping` / `.animate-ping-radar`
- **Duración:** 2s (`cubic-bezier(0, 0, 0.2, 1)`, infinita)
- **Propiedades animadas:** `transform: scale(2.2)`, `opacity: 0`
- **Uso:** Puntos de conexión en tiempo real (Socket conectado, GPS fijado, sensor activo).

---

## 2. Modales, Notificaciones y Feedback Háptico

### `emeraldSuccessBurst` (Confirmación Exitosa de Marcación)
- **Selector:** `.animate-success-burst`
- **Duración:** 0.6s (`cubic-bezier(0.16, 1, 0.3, 1)`)
- **Propiedades animadas:** `scale` (0.92 → 1.02 → 1.0) y halo `box-shadow` en esmeralda (`#10B981`).
- **Uso:** Modal flotante o banner de marcación confirmada con sonido y check animado.

### `alertStrobe` (Conflicto de Horario / Tardanza)
- **Selector:** `.animate-alert-strobe`
- **Duración:** 1.8s
- **Propiedades animadas:** `border-color` y `box-shadow` pulsando en rojo coral (`#EF4444`) o ámbar (`#F59E0B`).
- **Uso:** Modal de alerta de solapamiento o tardanza de voluntario.

### `modalSlideUp` (Despliegue de Ventanas Modales)
- **Selector:** `.animate-modal-pop`
- **Duración:** 0.35s (`cubic-bezier(0.16, 1, 0.3, 1)`)
- **Propiedades animadas:** `opacity: 0 → 1`, `translateY(28px) → translateY(0)`, `scale(0.97) → scale(1)`.
- **Uso:** Modales de detalle de voluntario, asignación y auditoría.

---

## 3. Interacciones Táctiles y Drag & Drop

### Elevación Glass en Hover (`.glass-interactive`)
- **Transición:** `all 0.25s cubic-bezier(0.4, 0, 0.2, 1)`
- **Efecto:** `translateY(-2px)`, borde iluminado en cian `rgba(34, 211, 238, 0.35)` y sombra difusa.
- **Uso:** Tarjetas de voluntarios en listas de asignación arrastrables y botones de kiosco.
