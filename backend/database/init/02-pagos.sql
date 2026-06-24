-- ============================================================
-- ProgresoFit - Pagos / Mercado Pago (auto-init)
-- Se ejecuta automáticamente tras 01-schema.sql
-- ============================================================

-- ENUM type for pagos.estado (separate from suscripcion_estado_type)
CREATE TYPE pago_estado_type AS ENUM ('pendiente','aprobado','rechazado','cancelado','expirado');

CREATE TABLE IF NOT EXISTS pagos (
  id               SERIAL PRIMARY KEY,
  suscripcion_id   INT NOT NULL,
  monto            DECIMAL(10,2) NOT NULL,
  estado           pago_estado_type DEFAULT 'pendiente',
  mp_preference_id VARCHAR(255),
  mp_payment_id    VARCHAR(255) NULL,
  mp_status        VARCHAR(50),
  mp_status_detail VARCHAR(255),
  intentos         INT DEFAULT 0,
  creado_en        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (suscripcion_id) REFERENCES suscripciones(id),
  CONSTRAINT uk_mp_payment UNIQUE (mp_payment_id)
);

-- Trigger to auto-update actualizado_en on row change
CREATE TRIGGER trg_pagos_actualizado_en
  BEFORE UPDATE ON pagos
  FOR EACH ROW
  EXECUTE FUNCTION update_actualizado_en();

-- Agregar estado 'pendiente' al ENUM de suscripciones
ALTER TYPE suscripcion_estado_type ADD VALUE IF NOT EXISTS 'pendiente';

-- Hacer fecha_fin nullable (las suscripciones pendientes no tienen fecha de fin)
ALTER TABLE suscripciones
  ALTER COLUMN fecha_fin DROP NOT NULL;
