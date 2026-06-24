# Design: Integración Mercado Pago Checkout Pro

## Technical Approach

Delegar el procesamiento de pagos a Mercado Pago Checkout Pro mediante su SDK de Node.js. El backend Express crea preferencias de pago, el frontend PHP redirige al usuario a MP, y un webhook validado con HMAC actualiza el estado del pago y la suscripción de forma asincrónica. La tabla `pagos` registra todos los intentos con trazabilidad completa.

## Architecture Decisions

### Decision: SDK de MP en backend Node.js, no en PHP

| Alternativa | Tradeoff | Decisión |
|---|---|---|
| `mercadopago` npm v3 (backend) | SDK oficial ESM, mismo runtime que el resto del backend | ✅ Elegido |
| SDK PHP de MP | Obliga a instalar dependencia PHP, rompe la separación de responsabilidades | ❌ Descartado |
| API REST directa sin SDK | Más boilerplate, manejo manual de firmas y retry | ❌ Descartado |

**Rationale**: El backend Express ya maneja toda la lógica de negocio (suscripciones, usuarios). El SDK oficial de MP para Node.js v3 es ESM nativo, compatible con la arquitectura actual (`"type": "module"` en package.json). Mantener el pago en Node.js evita exponer el `ACCESS_TOKEN` al frontend PHP.

### Decision: Webhook con validación HMAC (`X-Signature`)

| Alternativa | Tradeoff | Decisión |
|---|---|---|
| X-Signature HMAC-SHA256 | MP firma el body + headers; validación stateless sin llamadas extra | ✅ Elegido |
| IP whitelist | MP no publica rangos fijos; frágil en la nube | ❌ Descartado |
| AccessToken en query param | Inseguro, expone el token | ❌ Descartado |

**Rationale**: MP envía `X-Signature` como `sha256=<HMAC>` calculado sobre `body + X-Request-Id + X-Provider-Credentials` usando un secret compartido. Es stateless, seguro, y no requiere roundtrips.

### Decision: Webhook como fuente de verdad (no el return URL)

| Alternativa | Tradeoff | Decisión |
|---|---|---|
| Webhook es fuente de verdad | El estado final lo determina MP async. Return URL solo muestra UI | ✅ Elegido |
| Return URL actualiza DB directo | Inseguro (URL pública), frágil si el usuario cierra el browser antes del redirect | ❌ Descartado |

**Rationale**: El webhook de MP es el único mecanismo confiable para conocer el estado real del pago. El return URL (`success_url` / `failure_url`) solo determina qué pantalla ve el usuario. La suscripción se activa ÚNICAMENTE cuando el webhook confirma `payment.approved`.

### Decision: `suscribirse()` se divide en dos pasos (crear suscripción → crear preferencia)

En vez de modificar `POST /suscripciones` para que devuelva un `init_point`, mantenemos endpoints separados:
1. `POST /suscripciones` crea la suscripción con estado `pendiente` (hoy crea `activa` directo — lo cambiamos)
2. `POST /mercadopago/preference` crea la preferencia contra MP y asocia el `pago.pendiente` a esa suscripción

**Rationale**: Separación de concerns. La suscripción existe como entidad antes del pago. Si MP falla, la suscripción queda `pendiente` y se puede reintentar sin duplicar registros.

## Data Flow

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│  Frontend    │       │  Backend     │       │  Mercado     │       │  Backend     │
│  PHP/Twig    │       │  Express     │       │  Pago API    │       │  MySQL       │
└──────┬───────┘       └──────┬───────┘       └──────┬───────┘       └──────┬───────┘
       │                      │                       │                      │
       │  1. Selecciona plan  │                       │                      │
       │─────────────────────►│                       │                      │
       │                      │                       │                      │
       │  2. POST /suscripciones (plan)               │                      │
       │─────────────────────►│                       │                      │
       │                      │  3. INSERT suscripción (pendiente)           │
       │                      │─────────────────────────────────────────────►│
       │                      │                       │                      │
       │  4. {suscripcion_id} │                       │                      │
       │◄─────────────────────│                       │                      │
       │                      │                       │                      │
       │  5. POST /mercadopago/preference              │                      │
       │─────────────────────►│                       │                      │
       │                      │  6. POST SDK /checkout/preferences           │
       │                      │──────────────────────►│                      │
       │                      │  7. {preference_id, init_point}              │
       │                      │◄──────────────────────│                      │
       │                      │                       │                      │
       │                      │  8. INSERT pago (pendiente)                  │
       │                      │─────────────────────────────────────────────►│
       │                      │                       │                      │
       │  9. {init_point}     │                       │                      │
       │◄─────────────────────│                       │                      │
       │                      │                       │                      │
       │ 10. REDIRECT a MP    │                       │                      │
       │─────────────────────────────────────────────►│                      │
       │                      │                       │                      │
       │        (Usuario paga en MP)                  │                      │
       │                      │                       │                      │
       │ 11. Redirect success/failure_url             │                      │
       │◄─────────────────────────────────────────────│                      │
       │                      │                       │                      │
       │ 12. POST /mercadopago/webhook (async)        │                      │
       │                      │◄──────────────────────│                      │
       │                      │                       │                      │
       │                      │ 13. Valida X-Signature│                      │
       │                      │                       │                      │
       │                      │ 14. GET /v1/payments/{id}                    │
       │                      │──────────────────────►│                      │
       │                      │ 15. {status, detail}  │                      │
       │                      │◄──────────────────────│                      │
       │                      │                       │                      │
       │                      │ 16. UPDATE pago → aprobado                   │
       │                      │─────────────────────────────────────────────►│
       │                      │                       │                      │
       │                      │ 17. UPDATE suscripción → activa, fecha_fin   │
       │                      │─────────────────────────────────────────────►│
       │                      │                       │                      │
       │                      │ 18. {status: processed}                      │
       │                      │──────────────────────►│                      │
```

## File Changes

### Backend (Express — Node.js)

| File | Action | Description |
|------|--------|-------------|
| `backend/routes/mercadopago.routes.js` | Create | Rutas: `POST /preference`, `POST /webhook` |
| `backend/controllers/mercadopago.controller.js` | Create | Handlers: `createPreference`, `handleWebhook` |
| `backend/services/mercadopago.service.js` | Create | SDK MP: crear preferencia, consultar pago, validar webhook |
| `backend/services/suscripcionService.js` | Modify | `suscribirse()` crea con estado `pendiente`. Nueva `activarPorPago()` |
| `backend/controllers/suscripcionController.js` | Modify | `suscribirse()` ahora acepta suscripciones `pendiente` |
| `backend/repositories/pago.repository.js` | Create | CRUD pagos, búsqueda por mp_preference_id y mp_payment_id |
| `backend/repositories/suscripcionRepository.js` | Modify | Nuevo `updateEstado()` y `findById()` |
| `backend/middleware/webhookAuth.js` | Create | Middleware de validación HMAC X-Signature |
| `backend/database/init/02-pagos.sql` | Create | Migración tabla `pagos` |
| `backend/src/server.js` | Modify | Montar `mercadopagoRoutes` en `/mercadopago` |
| `backend/package.json` | Modify | Agregar `"mercadopago": "^3"` |

### Frontend (PHP — Twig + MVC)

| File | Action | Description |
|------|--------|-------------|
| `src/app/controllers/PagosuscripcionController.php` | Modify | `suscribir()`: llama a backend en 2 pasos, redirige a MP |
| `src/app/views/pagosuscripcion.html.twig` | Modify | Reemplazar form de tarjeta por botón "Pagar con MP" |
| `src/app/views/pago-exitoso.html.twig` | Create | Confirmación post-pago con datos de la suscripción |
| `src/app/views/pago-fallido.html.twig` | Create | Error post-pago con botón de reintentar |

### Configuración

| File | Action | Description |
|------|--------|-------------|
| `backend/.env.example` | Create | Template con vars MP |
| `docker-compose.yml` (root) | Modify | Agregar `MP_ACCESS_TOKEN` al servicio backend |
| `backend/docker-compose.yml` | Modify | Agregar vars MP al env_file |

## Interfaces / Contracts

### MercadoPagoService

```js
// services/mercadopago.service.js

/**
 * Crea una preferencia de pago en Mercado Pago.
 * @param {Object} params
 * @param {number} params.suscripcionId
 * @param {number} params.monto
 * @param {string} params.titular
 * @param {string} params.email
 * @param {string} params.descripcion  // ej: "ProgresoFit - Premium 30d"
 * @returns {Promise<{preferenceId: string, initPoint: string, expiresAt: string}>}
 */
async function createPreference({ suscripcionId, monto, titular, email, descripcion }) {}

/**
 * Consulta el estado de un pago en MP.
 * @param {number} paymentId - ID del pago en MP
 * @returns {Promise<{status: string, statusDetail: string}>}
 */
async function getPayment(paymentId) {}

/**
 * Valida la firma HMAC del webhook.
 * @param {Object} headers - req.headers (X-Signature, X-Request-Id)
 * @param {string} body - Raw body como string
 * @returns {boolean}
 */
function validateWebhookSignature(headers, body) {}
```

### MercadoPagoController

```js
// controllers/mercadopago.controller.js

/**
 * POST /mercadopago/preference
 * Body: { suscripcion_id, plan_id, monto, titular, email, descripcion }
 * Valida: monto > 0, suscripcion_id existe, plan_id válido
 * Crea preferencia en MP, persiste pago pendiente, devuelve init_point
 */
async function createPreference(req, res) {}

/**
 * POST /mercadopago/webhook
 * Headers: X-Signature, X-Request-Id
 * Body: { action, api_version, data: { id } }
 * Valida firma, consulta MP, actualiza pago + suscripción
 * Idempotente: si mp_payment_id ya existe, responde 200 without changes
 */
async function handleWebhook(req, res) {}
```

### PagoRepository

```js
// repositories/pago.repository.js

async function create(data) {}           // INSERT pago con estado pendiente
async function findById(id) {}           // SELECT por id
async function findByPreferenceId(preferenceId) {}  // SELECT por mp_preference_id
async function findByPaymentId(paymentId) {}        // SELECT por mp_payment_id
async function updateStatus(id, data) {}            // UPDATE estado, mp_status, intentos
async function findBySuscripcion(suscripcionId) {}  // Todos los pagos de una suscripción
```

### SuscripcionRepository — nuevas funciones

```js
// repositories/suscripcionRepository.js — agregar:

async function findById(id) {}           // SELECT por id (necesario para validar suscripcion_id)
async function updateEstado(id, estado, fechaFin) {}  // UPDATE estado y fecha_fin
```

### WebhookAuth middleware

```js
// middleware/webhookAuth.js
// Verifica X-Signature header contra MP_WEBHOOK_SECRET
// Usa crypto.createHmac('sha256', secret)
// Si inválido: 401 { error: "unauthorized", message: "Invalid webhook signature" }
// Si válido: req.mpWebhookVerified = true, next()
// También extrae X-Request-Id para logging/idempotencia
```

### SuscripcionService — cambios

```js
// services/suscripcionService.js — modificar suscribirse():

export async function suscribirse(usuarioId, plan) {
  // Mismo cálculo de precios/fechas, pero estado = 'pendiente' en vez de 'activa'
  return await suscripcionRepository.create({
    usuario_id: usuarioId,
    plan,
    precio: planData.precio,
    fecha_inicio: formatDate(hoy),
    fecha_fin: null,  // sin fecha hasta que se pague
    estado: 'pendiente'
  });
}

// Nueva función:
export async function activarPorPago(suscripcionId) {
  const hoy = new Date();
  const fechaFin = new Date(hoy);
  fechaFin.setDate(fechaFin.getDate() + 30);
  return await suscripcionRepository.updateEstado(
    suscripcionId,
    'activa',
    formatDate(fechaFin)
  );
}
```

**Nota**: Requiere agregar `'pendiente'` al ENUM de `suscripciones.estado` en la migración: `ALTER TABLE suscripciones MODIFY COLUMN estado ENUM('activa','cancelada','vencida','pendiente') DEFAULT 'pendiente';`.

### Frontend — PagosuscripcionController cambios

```php
// controllers/PagosuscripcionController.php

public function suscribir()
{
    // 1. Validar sesión y plan seleccionado (igual que antes)

    // 2. Crear suscripción pendiente
    $susc = $this->api->post('/suscripciones', ['plan' => $plan], $_SESSION['jwt']);

    if (!$susc['ok']) {
        header("Location: /pago-suscripcion?error=" . urlencode($susc['data']['error'] ?? 'Error'));
        exit;
    }

    $suscripcionId = $susc['data']['id'];
    // Obtener precio y descripción del plan (desde $_POST o desde $susc['data'])
    $monto = $susc['data']['precio'];
    $nombrePlan = $susc['data']['plan'];

    // 3. Crear preferencia MP
    $pref = $this->api->post('/mercadopago/preference', [
        'suscripcion_id' => $suscripcionId,
        'plan_id'        => $plan,
        'monto'          => $monto,
        'titular'        => $_POST['nombre-titular'] ?? $_SESSION['user']['nombre'],
        'email'          => $_SESSION['user']['email'],
        'descripcion'    => "ProgresoFit - {$nombrePlan} 30d"
    ], $_SESSION['jwt']);

    if (!$pref['ok']) {
        header("Location: /pago-suscripcion?error=" . urlencode($pref['data']['message'] ?? 'Error al conectar con MP'));
        exit;
    }

    // 4. Redirigir a MP
    header("Location: " . $pref['data']['init_point']);
    exit;
}
```

### Frontend — vistas nuevas

**pago-exitoso.html.twig**: Muestra "¡Pago exitoso!" con datos de la suscripción, botón "Ir a mi perfil". Recibe `suscripcion_id` y `plan` vía query params (el return URL de MP incluye `?suscripcion_id=X&plan=Y`).

**pago-fallido.html.twig**: Muestra "El pago no pudo procesarse", botón "Reintentar" que redirige a `/pago-suscripcion?suscripcion_id=X&reintento=1`. NO recibe datos sensibles.

## Database — Migración

Archivo: `backend/database/init/02-pagos.sql`

```sql
-- Tabla pagos
CREATE TABLE IF NOT EXISTS pagos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar estado 'pendiente' a suscripciones
ALTER TABLE suscripciones
  MODIFY COLUMN estado ENUM('activa','cancelada','vencida','pendiente') DEFAULT 'pendiente';
```

## Webhook Processing — Lógica Idempotente

```
POST /mercadopago/webhook
  │
  ├─ webhookAuth middleware: validar X-Signature
  │   ├─ Inválida → 401, log warn, descartar
  │   └─ Válida → continuar
  │
  ├─ Extraer data.id (mp_payment_id) del body
  │
  ├─ Buscar pago por mp_payment_id en DB
  │   ├─ Ya existe → 200 { status: "already_processed" } → FIN
  │   └─ No existe → continuar
  │
  ├─ Buscar pago por mp_preference_id (usando el ID guardado al crear preferencia)
  │   ├─ No existe (preference desconocida) → 200 { status: "ignored" } → FIN
  │   └─ Existe → continuar
  │
  ├─ Consultar GET /v1/payments/{id} en MP API
  │   ├─ Error de red → incrementar intentos, 200 { status: "retry_later" } → FIN
  │   └─ Éxito → continuar
  │
  ├─ Según status de MP:
  │   ├─ "approved" → UPDATE pago estado=aprobado, UPDATE suscripción estado=activa+fecha_fin
  │   ├─ "rejected" → UPDATE pago estado=rechazado (suscripción sin cambios)
  │   ├─ "cancelled" → UPDATE pago estado=cancelado (suscripción sin cambios)
  │   └─ Otros → UPDATE pago con mp_status recibido (suspendido, in_process, etc.)
  │
  └─ 200 { status: "processed", payment_id, pago_id }
```

## Retry Mechanism

| Escenario | Comportamiento |
|-----------|---------------|
| Webhook timeout | MP reintenta automáticamente hasta 24h (política de MP) |
| MP API caída al consultar payment | Se incrementa `intentos` en DB. Si `intentos >= 5`, el pago se marca como `fallido` y se loguea alerta |
| Webhook duplicado | Idempotencia por `mp_payment_id` UNIQUE — segundo intento devuelve `already_processed` |
| Preference expirada (24h sin pago) | Webhook con expiración marca pago como `expirado`. Suscripción sin cambios |

## Error Handling

| Capa | Error | Respuesta |
|------|-------|-----------|
| Controller | MP API no responde / SDK error | 502 `{ error: "payment_gateway_error", message: "MP no disponible" }` |
| Controller | Datos inválidos | 400 `{ error: "validation_error", message }` |
| Controller | suscripcion_id no existe | 404 `{ error: "not_found", message: "Suscripción no encontrada" }` |
| Controller | webhook firma inválida | 401 `{ error: "unauthorized" }` |
| Controller | Body webhook sin data.id | 400 `{ error: "bad_request" }` |
| Service | SDK MP lanza excepción | Capturada, logueada, relanzada como error con mensaje amigable |
| Repository | Unique key violation | Capturar, loguear, responder idempotente |
| Frontend | MP redirect falla | Pantalla de error con botón de reintentar |

## Security

| Aspecto | Medida |
|---------|--------|
| MP_ACCESS_TOKEN | Solo en backend, en variables de entorno. Nunca se envía al frontend |
| Webhook spoofing | Firma HMAC-SHA256 con `MP_WEBHOOK_SECRET`. Se valida en middleware antes de tocar DB |
| JWT en endpoints MP | `POST /mercadopago/preference` requiere JWT de alumno autenticado |
| Webhook endpoint | `POST /mercadopago/webhook` NO requiere JWT (MP no puede enviarlo). Se protege por HMAC |
| X-Request-Id | Se loguea para trazabilidad. Se usa en respuesta para MP detecte duplicados |
| Input sanitization | Express valida tipos (monto como number, emails válidos). SQL parametrizado via mysql2 |

## Environment Variables

```
# backend/.env.example — agregar:
MP_ACCESS_TOKEN=TEST-123456789-abc
MP_WEBHOOK_SECRET=tu_webhook_secret_de_mp
MP_SUCCESS_URL=http://localhost:8000/pago-exitoso
MP_FAILURE_URL=http://localhost:8000/pago-fallido
MP_PENDING_URL=http://localhost:8000/pago-suscripcion
```

## Dev Setup

1. **Cuenta MP Developers**: Crear cuenta de test en [mercadopago.com.ar/developers](https://www.mercadopago.com.ar/developers). Obtener `ACCESS_TOKEN` de test desde el panel de credenciales.
2. **ngrok** (solo para webhook local):
   ```bash
   brew install ngrok
   ngrok http 3000
   # Copiar URL pública (ej: https://abc123.ngrok.io)
   ```
3. **Configurar Webhook en MP**: En el dashboard de MP developers, configurar la URL del webhook apuntando a la URL de ngrok: `https://abc123.ngrok.io/mercadopago/webhook`. MP genera automáticamente el `WEBHOOK_SECRET`.
4. **Variables de entorno**: Copiar `backend/.env.example` a `backend/.env` y llenar valores reales.
5. **Instalar dependencia**: `cd backend && npm install mercadopago`.
6. **Ejecutar migración**: La migración `02-pagos.sql` se ejecuta automáticamente al levantar los contenedores (montada en `/docker-entrypoint-initdb.d`).

## Testing Strategy

| Layer | Qué probar | Cómo |
|-------|-----------|------|
| Unit | `mercadopago.service.validateWebhookSignature()` | Test vector: body + secret conocidos, verificar HMAC result |
| Unit | `mercadopago.service.createPreference()` | Mock SDK de MP, verificar que llama a MP con datos correctos |
| Unit | `pago.repository` CRUD | Test de integración contra MySQL de test |
| Unit | Idempotencia del webhook | Mock `findByPaymentId`, verificar que no hace UPDATE si ya existe |
| Integration | Flujo completo createPreference → webhook | Docker compose con MP sandbox real (ngrok) |
| Manual | Frontend redirect a MP | Click en "Pagar con MP", verificar que redirige a sandbox |
| Manual | Return URLs | Pagar/cancelar en sandbox, verificar que vuelve a pantalla correcta |

## Rollback

Ver proposal.md sección Rollback Plan. Adicional: si se revierte la migración SQL, ejecutar `DROP TABLE IF EXISTS pagos` y revertir el ALTER TABLE de suscripciones.

## Open Questions

- [ ] Verificar si el webhook de MP en sandbox envía `X-Provider-Credentials` (necesario para el cálculo HMAC según docs v3). Si no, ajustar la fórmula de firma.
- [ ] Confirmar si `suscripcion_id` debe pasarse como query param en `success_url`/`failure_url` o si el frontend puede resolverlo desde sesión. Se recomienda pasarlo como query param para stateless redirect.
- [ ] Definir si la vista `pago-exitoso.html.twig` necesita polling al backend para saber cuándo el webhook procesó el pago, o si mostramos "Suscripción pendiente de confirmación" con refresh automático.
