# Tasks: Integración Mercado Pago Checkout Pro

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~464 (330 new + 134 modified) |
| 400-line budget risk | Medium |
| Chained PRs recommended | Yes |
| Suggested split | PR 1: Foundation → PR 2: Endpoints → PR 3: Frontend |
| Delivery strategy | ask-on-risk |
| Chain strategy | stacked-to-main |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: stacked-to-main
400-line budget risk: Medium

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | DB + repos + services + webhook middleware (no behavior change) | PR 1 | ~190 lines, base=main |
| 2 | MP endpoints + routes + suscripcion modifications + server wiring | PR 2 | ~115 lines, base=main; depends on PR 1 |
| 3 | Frontend PHP/Twig + env/config | PR 3 | ~159 lines, base=main; depends on PR 2 |

## Phase 1: DB Migration

- [x] 1.1 Crear `backend/database/init/02-pagos.sql` — CREATE TABLE pagos (estados: pendiente/aprobado/rechazado/cancelado/expirado, FK→suscripciones, UNIQUE mp_payment_id) + ALTER suscripciones.estado para incluir 'pendiente'. Deps: 01-schema.sql. Done: archivo creado, migración ejecutable desde initdb. Est: ~25 lines.

## Phase 2: Backend Foundation

- [x] 2.1 Crear `backend/repositories/pago.repository.js` — CRUD: create(), findById(), findByPreferenceId(), findByPaymentId(), updateStatus(), findBySuscripcion(). Deps: 1.1 (tabla pagos existe). Done: todas las funciones implementadas con mysql2 pool.query parametrizado. Est: ~60 lines.
- [x] 2.2 Crear `backend/services/mercadopago.service.js` — createPreference() llama SDK MP, getPayment(paymentId), validateWebhookSignature(headers, body) con crypto.createHmac. Deps: 2.1 (persiste pago pendiente). Done: service con las 3 funciones exportadas. Est: ~80 lines.
- [x] 2.3 Crear `backend/middleware/webhookAuth.js` — extrae X-Signature, X-Request-Id, valida HMAC-SHA256 contra MP_WEBHOOK_SECRET. 401 si inválido. Deps: ninguna. Done: middleware conectable en ruta /webhook. Est: ~25 lines.

## Phase 3: Backend Endpoints

- [x] 3.1 Crear `backend/controllers/mercadopago.controller.js` — createPreference() valida input, llama service, persiste pago, devuelve init_point. handleWebhook() valida firma, busca pago, consulta MP API, updateStatus + activarPorPago si aprobado. Deps: 2.2, 2.3. Done: ambos handlers con manejo de errores 400/401/404/502. Est: ~80 lines.
- [x] 3.2 Crear `backend/routes/mercadopago.routes.js` — POST /preference (con authenticateToken), POST /webhook (con webhookAuth). Deps: 3.1. Done: rutas montables. Est: ~20 lines.
- [x] 3.3 Modificar `backend/src/server.js` — importar y app.use('/mercadopago', mercadopagoRoutes). Deps: 3.2. Done: raw body parser + import + ruta montada. Est: ~10 lines.

## Phase 4: Suscripciones Modifications

- [x] 4.1 Modificar `backend/repositories/suscripcionRepository.js` — agregar findById(id), updateEstado(id, estado, fechaFin). Deps: 1.1 (nuevo estado 'pendiente'). Done: funciones exportadas con SQL parametrizado + create() acepta estado dinámico. Est: ~30 lines.
- [x] 4.2 Modificar `backend/services/suscripcionService.js` — suscribirse() crea con estado='pendiente', fecha_fin=null. Nueva activarPorPago(suscripcionId) → updateEstado('activa', hoy+30d). Deps: 4.1. Done: lógica de activación asincrónica. Est: ~25 lines.
- [x] 4.3 Verificar `backend/controllers/suscripcionController.js` — devuelve suscripción con id, plan, precio, estado. Sin cambios necesarios. Est: ~0 lines.

## Phase 5: Frontend (PHP + Twig)

- [x] 5.1 Modificar `src/app/controllers/PagosuscripcionController.php` — suscribir() en 2 pasos: (1) POST /suscripciones, (2) POST /mercadopago/preference, redirige a init_point. Deps: 3.2, 4.2. Done: flujo completo sin datos de tarjeta. Est: ~35 lines.
- [x] 5.2 Modificar `src/app/views/pagosuscripcion.html.twig` — reemplazar form tarjeta por botón "Pagar con MP" que POSTea al controller. Deps: 5.1. Done: sin inputs de tarjeta, solo selección de plan + botón MP. Est: ~40 lines.
- [x] 5.3 Crear `src/app/views/pago-exitoso.html.twig` — pantalla de confirmación con datos de suscripción + botón "Ir a mi perfil". Deps: 5.1. Done: template renderizable con parámetros. Est: ~40 lines.
- [x] 5.4 Crear `src/app/views/pago-fallido.html.twig` — pantalla de error con botón "Reintentar". Deps: 5.1. Done: template renderizable sin datos sensibles. Est: ~35 lines.

## Phase 6: Configuration & Dev Setup

- [x] 6.1 Crear `backend/.env.example` — template con MP_ACCESS_TOKEN, MP_WEBHOOK_SECRET, MP_SUCCESS_URL, MP_FAILURE_URL, MP_PENDING_URL, MP_NOTIFICATION_URL. Deps: ninguna. Done: archivo creado con valores placeholder. Est: ~10 lines.
- [x] 6.2 Modificar `backend/package.json` — agregar `"mercadopago": "^3"` en dependencies. Deps: ninguna. Est: ~1 line.
- [x] 6.3 Modificar `docker-compose.yml` (root) y `backend/docker-compose.yml` — agregar MP_ACCESS_TOKEN, MP_WEBHOOK_SECRET, MP_SUCCESS_URL, MP_FAILURE_URL, MP_PENDING_URL, MP_NOTIFICATION_URL al environment del servicio backend. Deps: 6.1. Done: variables añadidas a ambos compose files. Est: ~6 lines.
- [x] 6.4 Manual: `cd backend && npm install mercadopago`. Deps: 6.2. Done: dependencia instalada (126 packages).

## Testing Note (strict_tdd: false)

No tests automáticos. Verificar manualmente:

| Qué probar | Cómo |
|------------|------|
| Creación preferencia | POST /mercadopago/preference con JWT válido → init_point |
| Webhook pago aprobado | Usar sandbox MP para pagar → verificar suscripción activa + fecha_fin |
| Idempotencia | Reenviar mismo webhook con curl → respuesta already_processed, 0 filas nuevas |
| Firma inválida | POST /webhook sin X-Signature → 401 |
| Frontend redirect | Click "Pagar con MP" → redirige a sandbox MP |
| Rechazo/cancelación | Cancelar en sandbox → pantalla fallido, suscripción sin cambios |
