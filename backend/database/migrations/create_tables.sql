-- ============================================================
-- ProgresoFit - Migration: create missing tables
-- Run this once against the running MySQL container:
--   docker exec -i progresofit-db mysql -u root -proot progresofit < backend/database/migrations/create_tables.sql
-- ============================================================

-- Trainer profile (extends usuarios where rol='ENTRENADOR')
CREATE TABLE IF NOT EXISTS entrenadores (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT NOT NULL UNIQUE,
  especialidad VARCHAR(255),
  descripcion  TEXT,
  horario      VARCHAR(255),
  gimnasio_id  INT,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
  FOREIGN KEY (gimnasio_id) REFERENCES gimnasios(id) ON DELETE SET NULL
);

-- Workout routines
CREATE TABLE IF NOT EXISTS rutinas (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  titulo       VARCHAR(255) NOT NULL,
  descripcion  TEXT,
  objetivo     VARCHAR(255),
  entrenador_id INT,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (entrenador_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Training days within a routine
CREATE TABLE IF NOT EXISTS entrenamientos (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  rutina_id        INT NOT NULL,
  nombre_dia       VARCHAR(100) NOT NULL,
  grupo_muscular   VARCHAR(255),
  duracion_minutos INT,
  orden            INT DEFAULT 1,
  FOREIGN KEY (rutina_id) REFERENCES rutinas(id) ON DELETE CASCADE
);

-- Exercise catalog (lazy-cached from external API)
CREATE TABLE IF NOT EXISTS ejercicios (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(255) NOT NULL,
  descripcion  TEXT,
  dificultad   ENUM('baja','media','alta') DEFAULT 'media',
  musculos     VARCHAR(255),
  equipamiento VARCHAR(255),
  video_url    VARCHAR(500),
  api_id       VARCHAR(100),
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Exercises within a training day (pivot)
CREATE TABLE IF NOT EXISTS entrenamiento_ejercicios (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  entrenamiento_id    INT NOT NULL,
  ejercicio_id        INT NOT NULL,
  series_repeticiones VARCHAR(100),
  orden               INT DEFAULT 1,
  FOREIGN KEY (entrenamiento_id) REFERENCES entrenamientos(id) ON DELETE CASCADE,
  FOREIGN KEY (ejercicio_id)     REFERENCES ejercicios(id)     ON DELETE CASCADE
);

-- Subscriptions
CREATE TABLE IF NOT EXISTS suscripciones (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT NOT NULL,
  plan        VARCHAR(100) NOT NULL,
  precio      DECIMAL(10,2),
  estado      ENUM('activa','cancelada','vencida') DEFAULT 'activa',
  fecha_inicio DATE NOT NULL,
  fecha_fin    DATE NOT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Routine assignment (trainer -> student)
CREATE TABLE IF NOT EXISTS alumno_rutinas (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  alumno_id         INT NOT NULL,
  rutina_id         INT NOT NULL,
  asignado_por      INT NOT NULL,
  fecha_asignacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_asignacion (alumno_id, rutina_id),
  FOREIGN KEY (alumno_id)    REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (rutina_id)    REFERENCES rutinas(id)  ON DELETE CASCADE,
  FOREIGN KEY (asignado_por) REFERENCES usuarios(id)
);

-- Add missing columns to gimnasios for full registration form
-- NOTE: MySQL 8.0 does not support IF NOT EXISTS on ALTER TABLE ADD COLUMN.
-- Run this only once on a fresh DB. Skip if columns already exist.
ALTER TABLE gimnasios
  ADD COLUMN telefono    VARCHAR(50),
  ADD COLUMN email       VARCHAR(255),
  ADD COLUMN descripcion TEXT,
  ADD COLUMN servicios   TEXT;

-- ============================================================
-- Migration: gimnasios as accounts (GIMNASIO role)
-- Run ONLY on existing DBs. New DBs get this via 01-schema.sql.
-- ============================================================

-- Add GIMNASIO to usuarios role enum
ALTER TABLE usuarios
  MODIFY COLUMN rol ENUM('ADMIN','ENTRENADOR','ALUMNO','GIMNASIO') NOT NULL DEFAULT 'ALUMNO';

-- Link gimnasios to their owner user account
ALTER TABLE gimnasios
  ADD COLUMN usuario_id INT UNIQUE DEFAULT NULL,
  ADD CONSTRAINT fk_gimnasio_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL;
