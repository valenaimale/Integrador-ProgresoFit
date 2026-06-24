# Proposal: Integración Mercado Pago Checkout Pro

## Intent

El sistema actual crea suscripciones en DB sin validar pagos reales y el formulario frontend envía datos de tarjeta crudos (inseguro, no-PCI). Necesitamos pagos reales seguros delegando el procesamiento a Mercado Pago Checkout Pro.

## Scope

### In Scope
- Backend: `POST /mercadopago/preference` crea preferencia y devuelve URL de checkout
- Backend: `POST /mercadopago/webhook` recibe notificaciones MP (idempotente)
- Frontend: botón "Pagar con Mercado Pago" reemplaza formulario de tarjeta
- DB: tabla `pagos` separada (id, suscripcion_id, monto, estado, mp_preference_id, mp_payment_id, mp_status, mp_status_detail, creado_en, actualizado_en)
- Backend: actualiza estado de suscripción al confirmar pago vía webhook
- Dev: ngrok tunnel para webhooks locales
- Env: `MP_ACCESS_TOKEN`, `MP_WEBHOOK_SECRET`, `MP_SUCCESS_URL`, `MP_FAILURE_URL`, `MP_PENDING_URL`

### Out of Scope
- Recurrencia automática (suscripciones MP recurrentes — pago único por 30 días)
- Checkout API (credential-on-file/tokenización propia)
- Planes free trial o período de gracia
- Reembolsos, cancelaciones o disputas
- Migración de suscripciones existentes

## Capabilities

### New Capabilities
- `mercadopago-pagos`: integración con Mercado Pago Checkout Pro (preference creation + webhook processing + pago único por suscripción)

### Modified Capabilities
None — no existing specs to modify.

## Approach

1. **Backend** (Express): endpoint `POST /mercadopago/preference` recibe `plan_id`, crea preference MP via SDK `mercadopago` v3, guarda `pagos` con `pendiente`, devuelve `init_point`
2. **Frontend** (Twig): reemplazar formulario tarjeta por botón "Pagar con MP" que redirige a `init_point`
3. **Webhook MP**: endpoint `POST /mercadopago/webhook` validado con `X-Signature`, busca `pagos` por `mp_preference_id`, actualiza estado y fecha de suscripción. Diseño idempotente: si el pago ya fue procesado, no re-ejecuta
4. **DB migration**: crear tabla `pagos` con Phinx + raw SQL en backend
5. **Flujo completo**: usuario selecciona plan → crea suscripción (pendiente) → redirect MP → paga → vuelve a webapp → webhook confirma → suscripción activa

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `backend/src/routes/mercadopago.routes.js` | New | Rutas preference + webhook |
| `backend/src/controllers/mercadopago.controller.js` | New | Lógica createPreference + handleWebhook |
| `backend/src/services/mercadopago.service.js` | New | SDK MP, preference creation, event validation |
| `backend/src/middleware/webhook-auth.js` | New | Validación firma webhook MP |
| `backend/src/repositories/pago.repository.js` | New | CRUD tabla pagos |
| `backend/db/migrations/xxxxxx_create_pagos_table.js` | New | Migración tabla pagos |
| `frontend/src/controllers/PagosuscripcionController.php` | Modified | Reemplazar POST (tarjeta) por redirect a MP |
| `frontend/src/views/pagosuscripcion.html.twig` | Modified | Botón MP en vez de formulario tarjeta |
| `frontend/src/views/pago-exitoso.html.twig` | New | Confirmación post-pago |
| `frontend/src/views/pago-fallido.html.twig` | New | Error post-pago |
| `backend/.env.example` | Modified | Nuevas vars MP |
| `docker-compose.yml` | Modified | Variable MP_ACCESS_TOKEN |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Webhook no llega (timeout/red) | Medium | Diseño idempotente + cola de reintentos en DB (campo `intentos` + `proximo_intento`) |
| MP_ACCESS_TOKEN expuesto en frontend | Low | Solo se usa en backend. Frontend nunca ve el token |
| Webhook spoofing | Low | Validar firma HMAC con `X-Signature` + secret compartido |
| ngrok caído en dev | Medium | Script de restart automático, log de URL pública |

## Rollback Plan

1. Deshabilitar ruta webhook en Express (comentar `mercadopago.routes.js`)
2. Revertir `PagosuscripcionController.php` al formulario de tarjeta anterior
3. Revertir migración `pagos` con `phinx rollback`
4. Restaurar `.env` sin vars MP

## Dependencies

- `mercadopago` npm package v3 (ESM)
- Cuenta de desarrollador Mercado Pago con `ACCESS_TOKEN` de test
- ngrok (`brew install ngrok`)

## Success Criteria

- [ ] `POST /mercadopago/preference` devuelve `init_point` válido que redirige a MP
- [ ] Webhook procesa pago aprobado y actualiza suscripción a `activa` con `fecha_fin = hoy + 30 días`
- [ ] Pago rechazado no activa suscripción
- [ ] Webhook duplicado no procesa dos veces (idempotencia probada)
- [ ] Frontend muestra botón MP sin datos de tarjeta en formulario
- [ ] Tabla `pagos` registra todos los intentos con trazabilidad completa
