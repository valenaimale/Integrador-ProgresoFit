# Mercado Pago Checkout Pro — Specification

## Purpose

Integrar Mercado Pago Checkout Pro como método de pago único para suscripciones de 30 días. El sistema delega el procesamiento a MP, recibe notificaciones vía webhook, y activa suscripciones solo con pago confirmado.

## Business Rules

| # | Rule | Detail |
|---|------|--------|
| 1 | **Estados de pago** | `pendiente` → `aprobado` \| `rechazado` \| `cancelado` \| `expirado`. Transición unidireccional. |
| 2 | **Pago-suscripción** | Una suscripción puede tener N intentos de pago. Solo UNO puede estar `aprobado` por suscripción. |
| 3 | **Reintentos** | Si webhook falla, campo `intentos` se incrementa. Máximo 5, luego se marca `fallido`. |
| 4 | **Idempotencia** | Si `mp_payment_id` ya existe en `pagos`, el webhook SHALL NOT modificar nada. |
| 5 | **Expiración** | Preferencia no pagada tras 24 h se considera expirada. Webhooks con `mp_preference_id` sin registro SHALL ser ignorados. |
| 6 | **Webhook es fuente de verdad** | El estado final del pago lo determina MP vía webhook. El return del frontend NO modifica estados. |

## Database — Tabla `pagos`

```sql
CREATE TABLE pagos (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  suscripcion_id   INT NOT NULL,
  monto            DECIMAL(10,2) NOT NULL,
  estado           ENUM('pendiente','aprobado','rechazado','cancelado','expirado') DEFAULT 'pendiente',
  mp_preference_id VARCHAR(255),
  mp_payment_id    VARCHAR(255) NULL,
  mp_status        VARCHAR(50),
  mp_status_detail VARCHAR(255),
  intentos         INT DEFAULT 0,
  creado_en        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (suscripcion_id) REFERENCES suscripciones(id),
  UNIQUE KEY uk_mp_payment (mp_payment_id)
);
```

## API Contract

### POST /mercadopago/preference

**Request**:
```json
{
  "suscripcion_id": 42,
  "plan_id": 5,
  "monto": 3000.00,
  "titular": "Juan Pérez",
  "email": "juan@example.com",
  "descripcion": "ProgresoFit - Premium 30d"
}
```

**Response 200**:
```json
{
  "preference_id": "123456789-abc",
  "init_point": "https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=...",
  "expires_at": "2026-06-21T12:00:00Z"
}
```

**Response 4xx**:
```json
{ "error": "validation_error", "message": "Monto debe ser > 0" }
```

### POST /mercadopago/webhook

**Headers**: `X-Signature: sha256=...`, `X-Request-Id: <uuid>`

**Request**:
```json
{
  "action": "payment.updated",
  "api_version": "v1",
  "data": { "id": 987654321 }
}
```

**Response 200 (processed)**:
```json
{ "status": "processed", "payment_id": 987654321, "pago_id": 1 }
```

**Response 200 (idempotent)**:
```json
{ "status": "already_processed", "payment_id": 987654321, "pago_id": 1 }
```

**Response 401**:
```json
{ "error": "unauthorized", "message": "Invalid webhook signature" }
```

## Requirements

### Requirement: CREAR_PREFERENCIA

The system MUST expose `POST /mercadopago/preference` that creates a MP preference via SDK, persists a `pago` with `pendiente`, and returns `init_point`.

#### Scenario: Creación exitosa

- GIVEN suscripción válida con monto > 0
- WHEN POST /mercadopago/preference con suscripcion_id y plan_id
- THEN el sistema crea preferencia en MP, guarda pago `pendiente`, y devuelve init_point válido

#### Scenario: Datos inválidos

- GIVEN monto <= 0 o suscripcion_id inexistente
- WHEN POST /mercadopago/preference
- THEN respuesta 4xx, NO se persiste ningún pago

#### Scenario: Preference expirada

- GIVEN una preferencia creada hace > 24 h sin pago
- WHEN el webhook recibe notificación de expiración
- THEN pago se marca `expirado`, suscripción NO cambia

### Requirement: PROCESAR_WEBHOOK

The system MUST expose `POST /mercadopago/webhook` that validates X-Signature, fetches payment details from MP, and updates `pagos` + `suscripciones`.

#### Scenario: Pago aprobado → suscripción activa

- GIVEN un pago `pendiente` vinculado a una suscripción
- WHEN webhook con action=payment.updated y data.id aprobado
- THEN valida firma, busca pago por mp_preference_id, actualiza pago a `aprobado`, suscripción a `activa`, fecha_fin = hoy + 30 días. Responde 200.

#### Scenario: Pago rechazado

- GIVEN un pago `pendiente`
- WHEN webhook con pago rechazado
- THEN pago a `rechazado`, suscripción sigue en estado original. Responde 200.

#### Scenario: Webhook duplicado (idempotencia)

- GIVEN mp_payment_id ya registrado en `pagos`
- WHEN MP reenvía el mismo webhook
- THEN responde 200 `already_processed`, NO modifica nada

#### Scenario: Firma inválida

- GIVEN request sin X-Signature o firma incorrecta
- WHEN POST /mercadopago/webhook
- THEN 401, NO procesa datos

### Requirement: INTERFAZ_PAGO

The frontend MUST show a "Pagar con Mercado Pago" button that redirects to `init_point`, replacing the credit-card form.

#### Scenario: Usuario paga y vuelve

- GIVEN usuario redirigido a MP que completó el pago
- WHEN MP redirige a success_url
- THEN frontend muestra pago-exitoso.html.twig con confirmación. Suscripción se activa asincrónicamente vía webhook.

#### Scenario: Usuario cancela

- GIVEN usuario en pantalla de MP
- WHEN usuario cancela
- THEN MP redirige a failure_url, frontend muestra pago-fallido.html.twig con opción de reintentar

## Acceptance Criteria

| Escenario | Criterio |
|-----------|----------|
| Creación exitosa | init_point devuelve URL válida de MP Sandbox |
| Pago aprobado | suscripción.estado = `activa`, fecha_fin = hoy + 30d |
| Pago rechazado | suscripción.estado NO cambia, pago.estado = `rechazado` |
| Idempotencia | Segundo webhook con mismo mp_payment_id no modifica filas |
| Firma inválida | 401, log de advertencia, 0 filas afectadas |
| Cancelación | pago.estado = `cancelado`, suscripción sin cambios |
| Expiración | pago.estado = `expirado` sin activar suscripción |
