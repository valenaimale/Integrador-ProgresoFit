# Tasks: Migración CSS Mobile-First — ProgresoFit

## Resumen

14 tareas organizadas de menor a mayor riesgo. Orden de ejecución por fases: foundation → templates → CSS simples → CSS moderados → tabla→cards → sweeps → verificación.

3 archivos Twig tocados (1 nuevo, 2 modificados). 12 archivos CSS modificados/revisados. ~316 líneas estimadas de diff total.

---

## Fase 1 — Foundation

### T1: style.css — foundation mobile-first

**Archivos**: `public/assets/css/style.css`

**Descripción**: Migrar los 2 MQs de max-width:640px a mobile-first. La base (sin MQ) pasa a ser mobile (0-479px). Se agregan 3 MQs min-width: 481px, 641px, 769px con los estilos desktop correspondientes. Además:
- `.bottom-nav`: cambiar `display: none` → `display: flex` en base, `display: none` en min-width:641px
- `main`: padding mobile-first (20px 14px 70px) en base, desktop padding en 641px+ (calc + 80px)
- `main`: max-width:960px se mueve de base a min-width:769px
- `.grid-entrenadores`: base = 4 cols, 641px+ = auto-fill (la 480px transition se maneja con min-width:481px)
- Footer: base = flex-direction:column + padding:28px 16px 80px; 641px+ = row + padding:40px 32px 24px
- `.btn`, `.btn-perfil`: agregar `min-height: 44px` en base (touch targets)

**Dependencias**: Ninguna
**Líneas estimadas (diff)**: ~65
**Riesgo**: 🟢 Bajo (CSS-only, foundation que el resto de tareas asume)

- [x] 1. `.bottom-nav` display:none → display:flex
- [x] 2. `main` padding → mobile values
- [x] 3. Mover max-width:960px a min-width:769px
- [x] 4. Crear @media (min-width: 481px) para grid-entrenadores 4col
- [x] 5. Crear @media (min-width: 641px) con header nav, bottom-nav, footer
- [x] 6. Eliminar los 2 MQs max-width:640px viejos
- [x] 7. Agregar min-height:44px a .btn, .btn-perfil

---

## Fase 2 — Templates (bottom-nav)

### T2: Crear bottom-nav.html.twig

**Archivos**: `src/app/views/parts/bottom-nav.html.twig` (CREAR)

**Descripción**: Nuevo partial con `<nav class="bottom-nav">` que contiene iconos SVG inline + links condicionales por rol. Misma lógica de roles que `header.html.twig` pero en formato bottom-nav (icono + texto). 5 SVG inline (home, calendar, users/qr, scan, user). Clase `.active` para ruta actual usando `app.request.uri` si está disponible.

**Dependencias**: T1 (necesita el CSS de .bottom-nav funcional)
**Líneas estimadas**: ~65

- [x] Crear partial con todos los roles (ALUMNO, ENTRENADOR, ADMIN, GIMNASIO, no logueado)
- [x] 5 SVG inline para cada sección
- [x] Clase .active condicional por ruta

---

### T3: Modificar header.html.twig — incluir bottom-nav

**Archivos**: `src/app/views/parts/header.html.twig`

**Descripción**: Agregar `{% include 'parts/bottom-nav.html.twig' %}` al final del archivo (después de `</header>`).

**Dependencias**: T2
**Líneas estimadas**: ~3

- [x] Agregar include del bottom-nav después de </header>

---

## Fase 3 — CSS simples (inversión directa de 1 MQ)

### T4: ejercicios.css

**Archivos**: `public/assets/css/ejercicios.css`

**Descripción**: Invertir MQ de max-width:640px a mobile-first. Mover `grid-template-columns: 1fr` del MQ a base. Envolver `grid-template-columns: repeat(auto-fill, minmax(280px, 1fr))` en `@media (min-width: 641px)`.

**Dependencias**: T1
**Líneas estimadas**: ~8
**Riesgo**: 🟢 Bajo

- [x] Base: grid-ejercicios 1 columna
- [x] @media (min-width: 641px): multi-col

---

### T5: rutina-ind.css

**Archivos**: `public/assets/css/rutina-ind.css`

**Descripción**: Invertir MQ de max-width:640px a mobile-first. Mover estilos de mobile (`.ejercicio-item` column, `.rutina-header` padding reducido, h2 font-size chico) a base. Envolver estilos desktop en `@media (min-width: 641px)`.

**Dependencias**: T1
**Líneas estimadas**: ~12
**Riesgo**: 🟢 Bajo

- [x] Base: .ejercicio-item column, .ejercicio-acciones full width
- [x] Base: .rutina-header padding 24px, h2 1.8rem
- [x] @media (min-width: 641px): restore desktop layout

---

### T6: error.css

**Archivos**: `public/assets/css/error.css`

**Descripción**: Invertir 2 MQs (max-width:768px → min-width:769px, max-width:480px → min-width:481px).

**Dependencias**: T1
**Líneas estimadas**: ~15
**Riesgo**: 🟢 Bajo

- [x] Base: mobile values (header 100px, footer 130px, main padding 2rem 1rem, h1 1.2rem)
- [x] @media (min-width: 481px): h1 1.5rem, volver-inicio a desktop
- [x] @media (min-width: 769px): restore full desktop (header 130px, footer 170px, main 3rem, h1 2rem)

---

## Fase 4 — CSS moderados

### T7: formularios.css

**Archivos**: `public/assets/css/formularios.css`

**Descripción**: Invertir 2 MQs (max-width:900px → min-width:901px, max-width:600px → min-width:601px). Agregar `transform: none` a `.card-plan:nth-child(2)` en mobile.

**Dependencias**: T1
**Líneas estimadas**: ~25
**Riesgo**: 🟡 Medio

- [x] Invertir MQ 900: .planes-seleccion column en base, row en 901px+
- [x] Invertir MQ 600: .form-row 1fr en base, 1fr 1fr en 601px+
- [x] .card-plan:nth-child(2) transform:none en mobile, scale(1.05) en 901px+

---

### T8: perfil.css

**Archivos**: `public/assets/css/perfil.css`

**Descripción**: Invertir MQ de max-width:850px → min-width:851px. El MQ existente `@media (min-width: 850px)` en línea 67 (perfil-grid-cards → row) se MANTIENE igual.

**Dependencias**: T1
**Líneas estimadas**: ~15
**Riesgo**: 🟢 Bajo

- [x] Base: perfil-header-compacto column, foto 100px, h2 1.8rem
- [x] @media (min-width: 851px): restore desktop (row, foto 120px, h2 2.4rem)
- [x] min-width:850 existente se mantiene sin cambios

---

### T9: rutinas.css

**Archivos**: `public/assets/css/rutinas.css`

**Descripción**: Invertir MQ de max-width:640px → min-width:641px.

**Dependencias**: T1
**Líneas estimadas**: ~25
**Riesgo**: 🟡 Medio

- [x] Base: .panel-header column, .form-inline-dia column, .form-ejercicio-fila column, input[type=number] 100%
- [x] Base: .grid-programas 1fr
- [x] @media (min-width: 641px): restore desktop layouts

---

## Fase 5 — Tabla→cards (más riesgoso)

### T10: Modificar inscriptos.html.twig — data-label

**Archivos**: `src/app/views/clases/inscriptos.html.twig`

**Descripción**: Agregar atributo `data-label` a cada `<td>` del loop de inscriptos.

**Dependencias**: DEBE ejecutarse ANTES de T11
**Líneas estimadas**: ~3
**Riesgo**: 🟡 Medio

- [x] `<td data-label="Nombre">`
- [x] `<td data-label="Email">`
- [x] `<td data-label="Se anotó">`

---

### T11: clases.css — tabla→cards + MQ inversion

**Archivos**: `public/assets/css/clases.css`

**Descripción**: Invertir MQ, tabla→cards con display:block en mobile.

**Dependencias**: T10, T1
**Líneas estimadas**: ~50
**Riesgo**: 🔴 **Alto**

- [x] .grid-clases base: 1fr, 769px+: repeat(auto-fit, minmax(300px, 1fr))
- [x] .panel-header base: column, 769px+: row
- [x] Tabla→cards: thead hidden, tr display:block, td display:flex con ::before
- [x] @media (min-width: 769px): restore table layout (display:table-*)

---

## Fase 6 — Sweeps

### T12: Touch targets — min-height: 44px

**Archivos**: clases.css, ejercicios.css, rutinas.css, rutina-ind.css, perfil.css, error.css

**Descripción**: Agregar `min-height: 44px` a todos los botones que actualmente tienen padding fijo < 44px.

**Dependencias**: T1
**Líneas estimadas**: ~25
**Riesgo**: 🟢 Bajo

- [x] clases.css: .btn-secondary, .btn-danger
- [x] ejercicios.css: .btn-pagina
- [x] rutinas.css: .btn-secondary, .btn-danger, .btn-asignar, .btn-suscripto
- [x] rutina-ind.css: .btn-ver
- [x] perfil.css: .btn-suscribirse, #btn-editar
- [x] error.css: .volver-inicio a

---

### T13: Review archivos sin MQ (aforo, miQr, nosotros, escanearQr)

**Archivos**: aforo.css, miQr.css, nosotros.css, escanearQr.css

**Descripción**: Revisión visual de márgenes/padding en mobile.

**Dependencias**: Ninguna
**Líneas estimadas**: ~5
**Riesgo**: 🟢 Bajo

- [x] aforo.css: padding 0 16px en container, card padding 40px 32px — OK
- [x] miQr.css: padding 0 16px en container, card padding 32px 24px — OK
- [x] nosotros.css: section padding 28px — OK
- [x] escanearQr.css: padding 0 16px en container — OK

---

## Fase 7 — Verificación

### T14: Verificación post-migración

**Archivos**: Ninguno (checklist manual)

**Descripción**: Ejecutar checklist visual en 3 viewports (480px, 768px, 1024px+).

**Dependencias**: T1–T13
**Líneas estimadas**: 0
**Riesgo**: N/A

- [ ] Desktop ≥ 1024px: mismo layout que pre-migración
- [ ] Tablet 768px: bottom-nav oculto, header nav visible
- [ ] Mobile 375px: bottom-nav visible, touch targets ≥ 44px
- [ ] Mobile: tabla inscriptos como cards con labels
- [ ] No queda ningún `@media (max-width:` en los 12 archivos CSS

---

## Review Workload Forecast

| Métrica | Valor |
|---------|-------|
| **Total estimated changed lines** | ~316 |
| **400-line budget risk** | 🟢 **Bajo** |
| **Chained PRs recommended** | ❌ **No** |
| **Decision needed before apply** | ✅ Sí |

### Breakdown por fase

| Fase | Tareas | Líneas | Riesgo acumulado |
|------|--------|--------|------------------|
| 1 — Foundation | T1 | ~65 | Bajo |
| 2 — Templates | T2–T3 | ~68 | Bajo |
| 3 — CSS simples | T4–T6 | ~35 | Bajo |
| 4 — CSS moderados | T7–T9 | ~65 | Medio |
| 5 — Tabla→cards | T10–T11 | ~53 | **Alto** |
| 6 — Sweeps | T12–T13 | ~30 | Bajo |
| 7 — Verificación | T14 | 0 | N/A |
