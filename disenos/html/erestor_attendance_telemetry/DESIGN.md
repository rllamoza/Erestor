---
name: Erestor Attendance Telemetry
colors:
  surface: '#0f131d'
  surface-dim: '#0f131d'
  surface-bright: '#353944'
  surface-container-lowest: '#0a0e18'
  surface-container-low: '#171b26'
  surface-container: '#1c1f2a'
  surface-container-high: '#262a35'
  surface-container-highest: '#313540'
  on-surface: '#dfe2f1'
  on-surface-variant: '#bcc9cd'
  inverse-surface: '#dfe2f1'
  inverse-on-surface: '#2c303b'
  outline: '#869397'
  outline-variant: '#3d494c'
  surface-tint: '#4cd7f6'
  primary: '#4cd7f6'
  on-primary: '#003640'
  primary-container: '#06b6d4'
  on-primary-container: '#00424f'
  inverse-primary: '#00687a'
  secondary: '#4edea3'
  on-secondary: '#003824'
  secondary-container: '#00a572'
  on-secondary-container: '#00311f'
  tertiary: '#2fd9f4'
  on-tertiary: '#00363e'
  tertiary-container: '#00b7cf'
  on-tertiary-container: '#00434d'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#acedff'
  primary-fixed-dim: '#4cd7f6'
  on-primary-fixed: '#001f26'
  on-primary-fixed-variant: '#004e5c'
  secondary-fixed: '#6ffbbe'
  secondary-fixed-dim: '#4edea3'
  on-secondary-fixed: '#002113'
  on-secondary-fixed-variant: '#005236'
  tertiary-fixed: '#a2eeff'
  tertiary-fixed-dim: '#2fd9f4'
  on-tertiary-fixed: '#001f25'
  on-tertiary-fixed-variant: '#004e5a'
  background: '#0f131d'
  on-background: '#dfe2f1'
  surface-variant: '#313540'
typography:
  headline-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: 56px
  headline-xl-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  title-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 22px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
  label-sm:
    fontFamily: Inter
    fontSize: 10px
    fontWeight: '600'
    lineHeight: 14px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-kiosk: 2.5rem
  margin: 1rem
  margin-tablet: 2rem
  margin-kiosk: 3.5rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style

This design system establishes a high-precision, utilitarian aesthetic engineered specifically for high-throughput kiosk terminals and mobile attendance tracking. Built for fast-paced check-ins of staff and volunteers, the design pairs the technological authority of aerospace instrumentation with the friction-free legibility required for ambient public displays.

### Design Movement: High-Tech Glassmorphism & Cyber-Precision
The visual language fuses deep optical glass surfaces with crisp, electric luminescent boundaries:
- **Atmosphere:** Deep vacuum dark-mode, removing glare in high-brightness environments while eliminating eye strain during night shifts.
- **Tone:** Authoritative, instantaneous, calibrated, and secure.
- **Sensory Feedback:** Crisp laser-like scan boundaries, optical blur underlays, precision telemetry indicators, and electric focal glows that intuitively direct physical movement (e.g., RFID badge positioning, facial framing, QR sweeps).

## Colors

The palette is anchored in ultra-deep slate and navy foundations, allowing high-luminescence chromatic signals to denote telemetry, recognition confidence, and verification states without UI clutter.

### Primary Spectrum (Electric Cyan)
- **Base Primary (`#06B6D4`):** Primary action triggers, active radio/scanner sweeps, and focal interactive states.
- **Cyan Glow / Highlight (`#22D3EE`):** Active biometric target zones, scan reticles, and high-priority glyphs.
- **Deep Cyan Anchor (`#0891B2`):** Button presses, border highlights on active components, and subtle surface ambient gradients.

### Status Spectrum (Vivid Emerald & Diagnostics)
- **Emerald Active (`#10B981`):** Verified identity, successful punch-in/out, optimal camera alignment, healthy terminal sync.
- **Emerald Deep (`#059669`):** Subdued success backgrounds and container surfaces.
- **Warning Amber (`#F59E0B`):** Shift mismatch, unassigned roster position, or retry prompt.
- **Critical Coral (`#EF4444`):** Verification rejected, unauthorized area entry, network offline.

### Neutral & Glass Layering
- **Surface Abyss (`#0B0F19`):** Root canvas background for kiosks and mobile viewport.
- **Surface Level 1 (`#111827`):** Base structural panels, side navigation rails, static containers.
- **Surface Level 2 (`#1E293B`):** Interactive cards, modal backdrops, flyout trays.
- **Surface Glass Border (`rgba(34, 211, 238, 0.15)`): Crisp perimeter delineation on semi-translucent cards.
- **Text High-Contrast (`#F9FAFB`):** Primary data labels, employee names, timestamps.
- **Text Muted (`#94A3B8`):** Department codes, helper strings, secondary metadata.

## Typography

Typography balances rapid scanning with dense metric reporting. 

- **Display & Headings (Plus Jakarta Sans):** Selected for its geometric structural confidence and crisp edge definitions on LCD/OLED kiosk hardware. It lends an ultra-modern, welcoming, yet precise presence.
- **Body & Telemetry (Inter):** Highly legible at narrow sizes with tall x-height and distinct glyph disambiguation (1, l, I). Essential for operational accuracy in low-light check-in bays.
- **Monospace Telemetry Rule:** For timestamps, RFID tokens, volunteer roster IDs, and scan latencies, use tabular numerical figures (`font-variant-numeric: tabular-nums`) to prevent layout shifts during live validation ticks.
- **Letter Spacing:** All uppercase label levels (`label-sm`, `label-md`) must utilize wide tracking (`+0.05em` to `+0.08em`) to enforce readability across kiosk glance distances (1.5 to 2.5 meters).

## Layout & Spacing

The layout model adapts across handheld mobile viewports for volunteers, administrative tablets, and standalone wall-mounted kiosks.

### Grid & Composition Rules
- **Mobile Viewport (360px – 600px):** Single-column fluid layout with `margin: 1rem` (`16px`). Touch targets adhere to a minimum 48px boundary. The camera viewfinder or NFC target area occupies the upper 45% of the viewport.
- **Tablet / Management Viewport (601px – 1024px):** 8-column fluid grid, `gutter: 1.5rem` (`24px`), `margin-tablet: 2rem` (`32px`). Split dual-pane: live attendance timeline on the left (60%), manual override and supervisor actions on the right (40%).
- **Wall Kiosk Display (1080px × 1920px Portrait & 1920px × 1080px Landscape):** 12-column rigid grid with `gutter-kiosk: 2.5rem` (`40px`) and `margin-kiosk: 3.5rem` (`56px`). Elements are deliberately enlarged for gross-motor physical interactions; touch targets scale up to a mandatory 64px height minimum.

### Ergonomic Spacing Strategy
- Kiosks place the interactive focal trigger area between 1000mm and 1400mm from ground level (the center-lower screen band).
- Spacing gaps (`space-lg`, `space-xl`) isolate critical confirmation triggers from reset or cancel buttons to eliminate mis-taps in busy shift transitions.

## Elevation & Depth

Visual hierarchy does not use drop shadows, which cause muddy gradients on high-brightness kiosk panels. Instead, depth is articulated through **tonal glass layering**, **optical cyan boundary lines**, and **spectral neon backdrops**.

### Hierarchy of Planes
1. **Base Plane (Floor):** Raw `#0B0F19` canvas, textured with a very faint isometric grid overlay (`rgba(34, 211, 238, 0.03)`).
2. **Structural Panels (Tier 1):** `#111827` at 85% opacity with `backdrop-filter: blur(16px)` and a subtle bottom stroke of `rgba(255, 255, 255, 0.04)`.
3. **Elevated Dynamic Cards (Tier 2):** `#1E293B` at 65% opacity, `backdrop-filter: blur(24px)`, bounded by a `1px` continuous border in `rgba(34, 211, 238, 0.15)`.
4. **Active Biometric / Scan Hub (Tier 3):** Frosted glass surface accompanied by an exterior diffuse aura: `box-shadow: 0 0 32px rgba(6, 182, 212, 0.25)`.
5. **Success Confirmation Overlay (Tier 4):** Surface with an ambient emerald halo: `box-shadow: 0 0 48px rgba(16, 185, 129, 0.35)`.

### Lens Flare & Scanline Rules
Interactive scanner viewfinders feature an animated horizontal laser sweep line (`#22D3EE`) terminating in blurred gradient wings, projecting depth directly into the physical space before the hardware camera.

## Shapes

The shape system employs calibrated geometry: precise curves on interactive nodes paired with cut or bracketed indicators for technical telemetry.

- **Base Radius (`0.5rem` / `8px`):** Standard data chips, telemetry badges, text input containers, and micro-dialogs.
- **Large Radius (`1rem` / `16px`):** Main interactive panels, camera viewports, employee profile identity modules, and confirmation popovers.
- **Corner Brackets & Reticles:** Viewfinders and biometric target boundaries reject full enclosures; they utilize 4 corner-brackets with a `2px` stroke, length of `24px`, and a tight `4px` corner fillet to reinforce the scanning instrument metaphor.
- **Pill Tags (`9999px`):** Reserved exclusively for live telemetry status (e.g., "KIOSK 04 - ONLINE", "VERIFIED", "SYNCING").

## Components

### Buttons & Interactive Triggers
- **Primary Kiosk Action (e.g., "Confirmar Registro"):** Solid `#06B6D4` fill with high-contrast `#0B0F19` bold typography. Hover/Focus reveals a cyan outer glow (`box-shadow: 0 0 20px rgba(34, 211, 238, 0.5)`). Height: 56px (Mobile), 64px (Kiosk).
- **Secondary Action (e.g., "Ingreso Manual con DNI"):** Transparent glass background (`rgba(30, 41, 59, 0.6)`), `1px` border of `rgba(34, 211, 238, 0.3)`, text in `#F9FAFB`.
- **Destructive / Reset:** Deep charcoal background with low-opacity coral perimeter (`rgba(239, 68, 68, 0.4)`), shifting to solid red only on intentional long-press.

### Verification Cards & Personnel Feed
- Multi-layered glass surface (`#111827` at 75% opacity) with volunteer photo inset inside a circular mask ringed with a `2px` status stroke (`#10B981` for active shift, `#06B6D4` for upcoming, `#F59E0B` for unassigned).
- Displays volunteer role, assigned zone, and punch time with tabular Inter monospace numerals.

### Biometric Scan Reticle
- Centered viewport frame (square with corner-only tick borders).
- Live facial/QR recognition indicator: pulses in Electric Cyan (`#22D3EE`) during detection, instantly locks into Vivid Emerald (`#10B981`) with a localized sonic and tactile ping upon successful match.

### Telemetry Badges & Status Chips
- Pill-shaped container (`height: 28px`), semi-translucent charcoal fill (`rgba(17, 24, 39, 0.8)`).
- Prefix consists of an animated pulsing status dot (`6px` diameter with an expanding ring animation) and uppercase, wide-tracked `label-sm` text.
- Emerald denotes synced, Cyan denotes active scanner, Amber denotes offline caching mode.

### Input Fields (PIN / DNI Access)
- Dark inset containers (`#0B0F19`) with `1.5px` border in `rgba(34, 211, 238, 0.2)`. 
- Focused state increases border luminance to `#22D3EE` with a subtle inner wash.
- Virtual numeric pinpad on kiosks provides oversized keys (72px × 72px) with distinct glass-press depth transitions.

### Confirmation Banner (Punch Success)
- Full-width modal slide-in anchored to bottom or screen center.
- Dynamic radial gradient burst radiating `#10B981` at 20% opacity against black glass.
- Large checkmark glyph enclosed in an emerald glow circle, showing precise verification timestamp down to seconds (`HH:MM:SS`).