-- ============================================================
-- ProgresoFit - Schema (auto-init)
-- Runs automatically on first `docker compose up` via init dir.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(255) DEFAULT NULL,
  email      VARCHAR(255) NOT NULL,
  password   VARCHAR(255) NOT NULL,
  rol        ENUM('ADMIN','ENTRENADOR','ALUMNO','GIMNASIO') NOT NULL DEFAULT 'ALUMNO',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY email (email)
);

CREATE TABLE IF NOT EXISTS gimnasios (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNIQUE DEFAULT NULL,
  nombre      VARCHAR(255) NOT NULL,
  direccion   TEXT,
  horarios    TEXT,
  telefono    VARCHAR(50),
  email       VARCHAR(255),
  descripcion TEXT,
  servicios   TEXT,
  activo      TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS entrenadores (
  id           INT AUTO_INCREMENT PRIMARY KEY,
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
  id            INT AUTO_INCREMENT PRIMARY KEY,
  titulo        VARCHAR(255) NOT NULL,
  descripcion   TEXT,
  objetivo      VARCHAR(255),
  entrenador_id INT,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (entrenador_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS entrenamientos (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  rutina_id        INT NOT NULL,
  nombre_dia       VARCHAR(100) NOT NULL,
  grupo_muscular   VARCHAR(255),
  duracion_minutos INT,
  orden            INT DEFAULT 1,
  FOREIGN KEY (rutina_id) REFERENCES rutinas(id) ON DELETE CASCADE
);

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

CREATE TABLE IF NOT EXISTS entrenamiento_ejercicios (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  entrenamiento_id    INT NOT NULL,
  ejercicio_id        INT NOT NULL,
  series_repeticiones VARCHAR(100),
  orden               INT DEFAULT 1,
  FOREIGN KEY (entrenamiento_id) REFERENCES entrenamientos(id) ON DELETE CASCADE,
  FOREIGN KEY (ejercicio_id)     REFERENCES ejercicios(id)     ON DELETE CASCADE
);

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

CREATE TABLE IF NOT EXISTS entrenador_alumnos (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  entrenador_id   INT NOT NULL,
  alumno_id       INT NOT NULL,
  fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_relacion (entrenador_id, alumno_id),
  FOREIGN KEY (entrenador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (alumno_id)     REFERENCES usuarios(id) ON DELETE CASCADE
);

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
