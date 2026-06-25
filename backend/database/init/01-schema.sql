-- ============================================================
-- ProgresoFit - Schema (auto-init)
-- Runs automatically on first `docker compose up` via init dir.
-- ============================================================

-- ============================================================
-- ENUM types (must exist before tables that reference them)
-- ============================================================

DO $$ BEGIN CREATE TYPE rol_type AS ENUM ('ADMIN','ENTRENADOR','ALUMNO','GIMNASIO'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE dificultad_type AS ENUM ('baja','media','alta'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE suscripcion_estado_type AS ENUM ('activa','cancelada','vencida'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE acceso_tipo_type AS ENUM ('ENTRADA','SALIDA'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE clase_estado_type AS ENUM ('ACTIVA','CANCELADA'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;

-- ============================================================
-- Trigger function for updated_at columns
-- ============================================================

CREATE OR REPLACE FUNCTION update_actualizado_en()
RETURNS TRIGGER AS $$
BEGIN
  NEW.actualizado_en = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ============================================================
-- Tables
-- ============================================================

CREATE TABLE IF NOT EXISTS usuarios (
  id         SERIAL PRIMARY KEY,
  nombre     VARCHAR(255) DEFAULT NULL,
  email      VARCHAR(255) NOT NULL,
  password   VARCHAR(255) NOT NULL,
  rol        rol_type NOT NULL DEFAULT 'ALUMNO',
  foto_url   TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT email UNIQUE (email)
);

CREATE TABLE IF NOT EXISTS gimnasios (
  id          SERIAL PRIMARY KEY,
  usuario_id  INT UNIQUE DEFAULT NULL,
  nombre      VARCHAR(255) NOT NULL,
  direccion   TEXT,
  horarios    TEXT,
  telefono    VARCHAR(50),
  email       VARCHAR(255),
  descripcion TEXT,
  servicios   TEXT,
  foto_url    TEXT DEFAULT NULL,
  activo      BOOLEAN DEFAULT TRUE,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS entrenadores (
  id           SERIAL PRIMARY KEY,
  usuario_id   INT NOT NULL UNIQUE,
  especialidad VARCHAR(255),
  descripcion  TEXT,
  horario      VARCHAR(255),
  gimnasio_id  INT,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
  FOREIGN KEY (gimnasio_id) REFERENCES gimnasios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS rutinas (
  id            SERIAL PRIMARY KEY,
  titulo        VARCHAR(255) NOT NULL,
  descripcion   TEXT,
  objetivo      VARCHAR(255),
  entrenador_id INT,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (entrenador_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS entrenamientos (
  id               SERIAL PRIMARY KEY,
  rutina_id        INT NOT NULL,
  nombre_dia       VARCHAR(100) NOT NULL,
  grupo_muscular   VARCHAR(255),
  duracion_minutos INT,
  orden            INT DEFAULT 1,
  FOREIGN KEY (rutina_id) REFERENCES rutinas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ejercicios (
  id           SERIAL PRIMARY KEY,
  nombre       VARCHAR(255) NOT NULL,
  descripcion  TEXT,
  dificultad   dificultad_type DEFAULT 'media',
  musculos     VARCHAR(255),
  equipamiento VARCHAR(255),
  video_url    VARCHAR(500),
  api_id       INT,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS entrenamiento_ejercicios (
  id                  SERIAL PRIMARY KEY,
  entrenamiento_id    INT NOT NULL,
  ejercicio_id        INT NOT NULL,
  series_repeticiones VARCHAR(100),
  orden               INT DEFAULT 1,
  FOREIGN KEY (entrenamiento_id) REFERENCES entrenamientos(id) ON DELETE CASCADE,
  FOREIGN KEY (ejercicio_id)     REFERENCES ejercicios(id)     ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS suscripciones (
  id          SERIAL PRIMARY KEY,
  usuario_id  INT NOT NULL,
  plan        VARCHAR(100) NOT NULL,
  precio      DECIMAL(10,2),
  estado      suscripcion_estado_type DEFAULT 'activa',
  fecha_inicio DATE NOT NULL,
  fecha_fin    DATE NOT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS entrenador_alumnos (
  id              SERIAL PRIMARY KEY,
  entrenador_id   INT NOT NULL,
  alumno_id       INT NOT NULL,
  fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT unique_relacion UNIQUE (entrenador_id, alumno_id),
  FOREIGN KEY (entrenador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (alumno_id)     REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS alumno_rutinas (
  id                SERIAL PRIMARY KEY,
  alumno_id         INT NOT NULL,
  rutina_id         INT NOT NULL,
  asignado_por      INT NOT NULL,
  fecha_asignacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT unique_asignacion UNIQUE (alumno_id, rutina_id),
  FOREIGN KEY (alumno_id)    REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (rutina_id)    REFERENCES rutinas(id)  ON DELETE CASCADE,
  FOREIGN KEY (asignado_por) REFERENCES usuarios(id)
);

-- ============================================================
-- Sistema de acceso por QR
-- ============================================================

CREATE TABLE IF NOT EXISTS gym_tokens (
  id          SERIAL PRIMARY KEY,
  usuario_id  INT NOT NULL,
  token       VARCHAR(64) NOT NULL,
  expires_at  TIMESTAMP NOT NULL,
  usado       BOOLEAN NOT NULL DEFAULT FALSE,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_token UNIQUE (token),
  CONSTRAINT uq_usuario_activo UNIQUE (usuario_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_expires ON gym_tokens(expires_at);

CREATE TABLE IF NOT EXISTS accesos_gimnasio (
  id            SERIAL PRIMARY KEY,
  usuario_id    INT NOT NULL,
  gimnasio_id   INT NOT NULL,
  tipo          acceso_tipo_type NOT NULL,
  registrado_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
  FOREIGN KEY (gimnasio_id) REFERENCES gimnasios(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_usuario ON accesos_gimnasio(usuario_id);
CREATE INDEX IF NOT EXISTS idx_gimnasio_tipo ON accesos_gimnasio(gimnasio_id, tipo);
CREATE INDEX IF NOT EXISTS idx_fecha ON accesos_gimnasio(registrado_at);

CREATE TABLE IF NOT EXISTS gimnasio_aforo (
  gimnasio_id      INT NOT NULL PRIMARY KEY,
  capacidad_maxima INT NOT NULL DEFAULT 50,
  ocupacion_actual INT NOT NULL DEFAULT 0,
  FOREIGN KEY (gimnasio_id) REFERENCES gimnasios(id) ON DELETE CASCADE
);

-- ============================================================
-- Clases / Actividades con cupo
-- ============================================================

CREATE TABLE IF NOT EXISTS clases (
  id               SERIAL PRIMARY KEY,
  gimnasio_id      INT NOT NULL,
  nombre           VARCHAR(255) NOT NULL,          -- ej: "Spinning / Bicicleta"
  descripcion      TEXT,
  fecha            DATE NOT NULL,
  hora_inicio      TIME NOT NULL,
  hora_fin         TIME,
  cupo_maximo      INT NOT NULL,
  inscriptos       INT NOT NULL DEFAULT 0,         -- contador denormalizado para control de cupo
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
  CONSTRAINT uq_clase_usuario UNIQUE (clase_id, usuario_id),  -- evita doble inscripción
  FOREIGN KEY (clase_id)   REFERENCES clases(id)    ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)  ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_clase_inscripciones_usuario ON clase_inscripciones(usuario_id);
