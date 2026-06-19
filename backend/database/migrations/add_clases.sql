-- ============================================================
-- Migración: Clases / Actividades con cupo
-- Aplicar en una DB ya inicializada (el volumen MySQL no re-corre 01-schema.sql):
--   docker exec -i progresofit-mysql mysql -uprogresofit_user -pprogresofit_pass progresofit < backend/database/migrations/add_clases.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS clases (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  gimnasio_id      INT NOT NULL,
  nombre           VARCHAR(255) NOT NULL,
  descripcion      TEXT,
  fecha            DATE NOT NULL,
  hora_inicio      TIME NOT NULL,
  hora_fin         TIME,
  cupo_maximo      INT NOT NULL,
  inscriptos       INT NOT NULL DEFAULT 0,
  estado           ENUM('ACTIVA','CANCELADA') NOT NULL DEFAULT 'ACTIVA',
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_gimnasio_fecha (gimnasio_id, fecha),
  FOREIGN KEY (gimnasio_id) REFERENCES gimnasios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clase_inscripciones (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  clase_id      INT NOT NULL,
  usuario_id    INT NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_clase_usuario (clase_id, usuario_id),
  INDEX idx_usuario (usuario_id),
  FOREIGN KEY (clase_id)   REFERENCES clases(id)    ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
