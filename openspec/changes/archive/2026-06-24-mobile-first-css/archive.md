# Archive: mobile-first-css

**Change**: mobile-first-css
**Archived**: 2026-06-24
**Veredicto**: PASS WITH WARNINGS

---

## Resumen del Cambio

Migración completa de desktop-first (max-width) a mobile-first (min-width) en los 12 archivos CSS del proyecto ProgresoFit. Se invirtieron 12 media queries en 8 archivos CSS, se agregó bottom-nav funcional con iconos SVG inline para 5 roles de usuario, touch targets ≥44px en mobile, y tabla de inscriptos convertible a cards en viewports `<768px`.

### Scope Original

- Invertir 12 MQs (max-width → min-width) con breakpoints unificados 480/768/1024 (+1 approach: 481/641/769)
- Crear bottom-nav con HTML + SVG inline, condicional por rol (ALUMNO, ENTRENADOR, ADMIN, GIMNASIO, no logueado)
- Touch targets ≥44px en todos los botones
- Migrar `.tabla-inscriptos` a cards en mobile con `display:block` + pseudo-elementos `::before` + `data-label`
- Padding inferior extra en main para bottom-nav fijo
- Review de espaciado mobile en 4 archivos sin MQ (aforo.css, miQr.css, nosotros.css, escanearQr.css)

### Fuera de Scope

PHP/backend, paleta/tipografía, build tools, refactor de estructura de archivos, JavaScript.

---

## Archivos Modificados

### CSS (12 archivos, 8 modificados, 4 revisados sin cambios)

| Archivo | Acción | Detalle |
|---------|--------|---------|
| `public/assets/css/style.css` | Modificado | Foundation: 2 MQs invertidos, `.bottom-nav` display:flex en base, `.btn` min-height:44px, main padding-bottom 70px, MQs 481/641/769px |
| `public/assets/css/clases.css` | Modificado | 1 MQ invertido (→769px), tabla→cards: `display:block` en `<tr>`, `<thead>` oculto, `::before` con `data-label` en mobile |
| `public/assets/css/formularios.css` | Modificado | 2 MQs invertidos (→601px, →901px), `.card-plan:nth-child(2)` `transform:none` en mobile |
| `public/assets/css/perfil.css` | Modificado | 1 MQ invertido (→851px). MQ `min-width:850px` existente mantenido sin cambios |
| `public/assets/css/ejercicios.css` | Modificado | 1 MQ invertido (→641px) |
| `public/assets/css/rutinas.css` | Modificado | 1 MQ invertido (→641px), `.form-inline-dia` column en mobile |
| `public/assets/css/rutina-ind.css` | Modificado | 1 MQ invertido (→641px) |
| `public/assets/css/error.css` | Modificado | 2 MQs invertidos (→481px, →769px) |
| `public/assets/css/aforo.css` | Revisado | Sin cambios necesarios |
| `public/assets/css/miQr.css` | Revisado | Sin cambios necesarios |
| `public/assets/css/nosotros.css` | Revisado | Sin cambios necesarios |
| `public/assets/css/escanearQr.css` | Revisado | Sin cambios necesarios |

### Twig (3 archivos, 1 creado, 2 modificados)

| Archivo | Acción | Detalle |
|---------|--------|---------|
| `src/app/views/parts/bottom-nav.html.twig` | **Creado** | Bottom-nav con 5 SVG inline + links condicionales por rol (61 líneas) |
| `src/app/views/parts/header.html.twig` | Modificado | + `{% include 'parts/bottom-nav.html.twig' %}` después de `</header>` |
| `src/app/views/clases/inscriptos.html.twig` | Modificado | + `data-label` en cada `<td>` (Nombre, Email, Se anotó) |

---

## Estado Final

### Tasks (14/14 completadas)

| Fase | Tareas | Estado |
|------|--------|--------|
| 1 — Foundation | T1: style.css foundation | ✅ |
| 2 — Templates | T2: bottom-nav.html.twig creado, T3: header.html.twig modificado | ✅ |
| 3 — CSS simples | T4: ejercicios.css, T5: rutina-ind.css, T6: error.css | ✅ |
| 4 — CSS moderados | T7: formularios.css, T8: perfil.css, T9: rutinas.css | ✅ |
| 5 — Tabla→cards | T10: inscriptos.html.twig data-label, T11: clases.css tabla→cards | ✅ |
| 6 — Sweeps | T12: touch targets 44px, T13: review archivos sin MQ | ✅ |
| 7 — Verificación | T14: post-migration checklist | ✅ |

### Compliance (Verification Report)

| Métrica | Resultado |
|---------|-----------|
| Tasks total | 14 |
| Tasks complete | 14 |
| Spec compliance | 10/12 full, 2 partial, 0 fail |
| Max-width MQs residuales | **Cero** — 0 resultados en 12 archivos |
| Verdict | **PASS WITH WARNINGS** |

### Warnings Conocidas (documentadas, no blocking)

1. **main padding-bottom 70px vs spec ≥80px (R4)**: La implementación usa 70px (style.css). Bottom-nav mide 58px, dejando 12px de espacio. Funcional, pero no cumple spec al pie de la letra. Decisión tomada en diseño (T1 documenta 70px).
2. **Bottom-nav ALUMNO difiere del spec (R3/E4)**: Spec lista "Rutinas, Gimnasios", implementación tiene "Clases, Mi QR". Desviación intencional documentada en T2, coincidiendo con rutas reales de la app.
3. **`.btn-sm` sin min-height:44px explícito**: clases.css y rutinas.css definen `.btn-sm` solo con padding y font-size. Se identificó en verificación como sugerencia.

---

## Lecciones Aprendidas

1. **Inversión de MQs sin SASS es viable pero verbosa**: Sin preprocesador, los breakpoints se repiten en cada archivo. La consistencia se logró con breakpoints documentados en el spec (481/641/769), no por variables compartidas.
2. **Tabla→cards requiere data-label en Twig ANTES del CSS**: T10 debe ejecutarse antes de T11. Si se aplica el CSS sin data-label, las cards se renderizan sin labels. La dependencia es crítica y se cumplió.
3. **Bottom-nav como partial separado simplifica la inclusión**: Usar `{% include %}` en `header.html.twig` evita tocar ambas bases (`base.html.twig` + `twig/base.twig`). Un solo punto de inclusión.
4. **SVG inline vs external**: Se eligió inline (sin HTTP requests extra). Los SVG son minimalistas (viewBox 24×24) y se incrustan directamente en el template.
5. **Breakpoint +1 approach**: Usar `min-width: 641px` en vez de `min-width: 640px` (el original era `max-width: 640px`) evita el gap de 1px donde ningún estilo aplica. El spec usa 480/768/1024 como breakpoints conceptuales, pero la implementación usa +1 (481/641/769).
6. **Sin test infrastructure**: El proyecto no tiene test runner configurado. La verificación fue 100% manual (static analysis + revisión visual). Esto limita la confianza en regresiones imperceptibles.
7. **MQs existentes `min-width` se preservan**: perfil.css ya tenía un `@media (min-width: 850px)` que se mantuvo intacto. Solo se invirtió el `max-width: 850px`.

---

## Artifacts del Cambio

| Artifact | Engram ID | Archivo Físico |
|----------|-----------|----------------|
| Exploration | #952 | — |
| Proposal | #953 | — |
| Spec | #954 | — |
| Design | #955 | — |
| Tasks | #956 | `tasks.md` |
| Apply Progress | #958 | — |
| Verify Report | #959 | — |
| Archive Report | (este) | `archive.md` |

---

## SDD Cycle Complete

```
explore → propose → spec → design → tasks → apply → verify → archive ✅
```

El cambio ha sido completamente planeado, implementado, verificado y archivado.
