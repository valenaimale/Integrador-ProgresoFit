-- ============================================================
-- ProgresoFit - Pagos / Mercado Pago (auto-init)
-- Se ejecuta automáticamente tras 01-schema.sql
-- ============================================================

SET NAMES utf8mb4;

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

-- Agregar estado 'pendiente' al ENUM de suscripciones
ALTER TABLE suscripciones
  MODIFY COLUMN estado ENUM('activa','cancelada','vencida','pendiente') NOT NULL DEFAULT 'activa';

-- Hacer fecha_fin nullable (las suscripciones pendientes no tienen fecha de fin)
ALTER TABLE suscripciones
  MODIFY COLUMN fecha_fin DATE NULL;
