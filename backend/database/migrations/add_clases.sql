-- ============================================================
-- Migration: Clases / Actividades con cupo
-- Run against a running PostgreSQL container:
--   docker exec -i progresofit-postgres psql -U progresofit_user -d progresofit < backend/database/migrations/add_clases.sql
-- ============================================================

DO $$ BEGIN
  CREATE TYPE clase_estado_type AS ENUM ('ACTIVA','CANCELADA');
EXCEPTION WHEN duplicate_object THEN NULL;
END $$;

CREATE TABLE IF NOT EXISTS clases (
  id               SERIAL PRIMARY KEY,
  gimnasio_id      INT NOT NULL,
  nombre           VARCHAR(255) NOT NULL,
  descripcion      TEXT,
  fecha            DATE NOT NULL,
  hora_inicio      TIME NOT NULL,
  hora_fin         TIME,
  cupo_maximo      INT NOT NULL,
  inscriptos       INT NOT NULL DEFAULT 0,
  estado           clase_estado_type NOT NULL DEFAULT 'ACTIVA',
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (gimnasio_id) REFERENCES gimnasios(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_gimnasio_fecha ON clases(gimnasio_id, fecha);

CREATE TABLE IF NOT EXISTS clase_inscripciones (
  id            SERIAL PRIMARY KEY,
  clase_id      INT NOT NULL,
  usuario_id    INT NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_clase_usuario UNIQUE (clase_id, usuario_id),
  FOREIGN KEY (clase_id)   REFERENCES clases(id)    ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)  ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_clase_inscripciones_usuario ON clase_inscripciones(usuario_id);
