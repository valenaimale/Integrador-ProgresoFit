-- ============================================================
-- ProgresoFit - Seed data (auto-init)
-- Password for ALL users: 123456
-- Hash generado con bcrypt (cost=10)
-- ============================================================

-- Usuarios de ejemplo
INSERT INTO usuarios (id, nombre, email, password, rol) VALUES
(1, 'Admin ProgresoFit', 'admin@progresofit.com',          '$2b$10$j2ZAvVb0yFNgBZBTJ.7HB.g0ive9J3omfjB14D56NNh5C7hg7dCe2', 'ADMIN'),
(2, 'Carlos Entrenador', 'carlos@test.com',                '$2b$10$j2ZAvVb0yFNgBZBTJ.7HB.g0ive9J3omfjB14D56NNh5C7hg7dCe2', 'ENTRENADOR'),
(3, 'María Alumna',      'maria@test.com',                 '$2b$10$j2ZAvVb0yFNgBZBTJ.7HB.g0ive9J3omfjB14D56NNh5C7hg7dCe2', 'ALUMNO'),
(4, 'Gimnasio Central',  'gimnasio@gimnasiocentral.com',   '$2b$10$j2ZAvVb0yFNgBZBTJ.7HB.g0ive9J3omfjB14D56NNh5C7hg7dCe2', 'GIMNASIO')
ON CONFLICT (id) DO NOTHING;

-- Gimnasio de ejemplo (usuario_id=4 es la cuenta del gimnasio)
INSERT INTO gimnasios (id, usuario_id, nombre, direccion, horarios, telefono, email, descripcion, servicios, activo) VALUES
(1, 4, 'Gimnasio Central',
    'Av. Siempre Viva 123',
    'Lun a Vie 7:00-22:00, Sáb 9:00-18:00',
    '11-5555-0123',
    'contacto@gimnasiocentral.com',
    'Gimnasio equipado con maquinaria de última generación',
    'Musculación, CrossFit, Yoga, Pilates, Functional Training',
    TRUE)
ON CONFLICT (id) DO NOTHING;

-- Perfil de entrenador (extiende a carlos@test.com)
INSERT INTO entrenadores (id, usuario_id, especialidad, descripcion, horario, gimnasio_id) VALUES
(1, 2,
 'Fuerza y Acondicionamiento',
 'Entrenador certificado con más de 10 años de experiencia. Especialista en hipertrofia y powerlifting.',
 'Lun a Vie 7:00-13:00',
 1)
ON CONFLICT (id) DO NOTHING;

-- Rutina de ejemplo
INSERT INTO rutinas (id, titulo, descripcion, objetivo, entrenador_id) VALUES
(1,
 'Rutina Fuerza Total',
 'Rutina de cuerpo completo enfocada en ejercicios compuestos. Ideal para quienes recién arrancan o quieren retomar.',
 'Hipertrofia y fuerza general',
 2)
ON CONFLICT (id) DO NOTHING;

-- Días de entrenamiento
INSERT INTO entrenamientos (id, rutina_id, nombre_dia, grupo_muscular, duracion_minutos, orden) VALUES
(1, 1, 'Lunes — Pecho y Tríceps',      'Pecho, Hombro anterior, Tríceps',  60, 1),
(2, 1, 'Miércoles — Espalda y Bíceps', 'Espalda, Hombro posterior, Bíceps', 60, 2),
(3, 1, 'Viernes — Piernas',            'Cuádriceps, Glúteos, Femorales',    60, 3)
ON CONFLICT (id) DO NOTHING;

-- Ejercicios precargados (simulan datos cacheados de wger.de)
INSERT INTO ejercicios (id, nombre, descripcion, dificultad, musculos, equipamiento, api_id) VALUES
(1,
 'Press de banca',
 'Ejercicio compuesto para pectoral mayor. Acostado en banco plano, bajá la barra al pecho y presioná hacia arriba.',
 'media',
 'Pectoral mayor, Hombro anterior, Tríceps',
 'Barra, Banco plano, Discos',
 '11'),
(2,
 'Dominadas',
 'Colgate de la barra con agarre prono y elevá tu cuerpo hasta que la barbilla pase la barra.',
 'media',
 'Dorsal ancho, Bíceps, Hombro posterior',
 'Barra de dominadas',
 '15'),
(3,
 'Sentadilla',
 'Con la barra en los hombros, flexioná rodillas y cadera como si te sentaras en una silla, y volvé a subir.',
 'media',
 'Cuádriceps, Glúteos, Femorales, Core',
 'Barra, Soporte de sentadilla',
 '12'),
(4,
 'Press militar',
 'De pie, presioná la barra desde los hombros hacia arriba hasta extender completamente los brazos.',
 'media',
 'Hombros, Tríceps, Core',
 'Barra, Discos',
 '24'),
(5,
 'Remo con barra',
 'Inclinado con el torso a 45°, traccioná la barra hacia el abdomen manteniendo la espalda recta.',
 'media',
 'Espalda media, Dorsal, Bíceps',
 'Barra, Discos',
 '17')
ON CONFLICT (id) DO NOTHING;

-- Asignación de ejercicios a cada día
INSERT INTO entrenamiento_ejercicios (id, entrenamiento_id, ejercicio_id, series_repeticiones, orden) VALUES
-- Lunes: Press banca + Press militar
(1, 1, 1, '4x8-10', 1),
(2, 1, 4, '3x10',   2),
-- Miércoles: Dominadas + Remo
(3, 2, 2, '3x8',    1),
(4, 2, 5, '4x10',   2),
-- Viernes: Sentadilla
(5, 3, 3, '4x10',   1)
ON CONFLICT (id) DO NOTHING;

-- Suscripción activa para María
INSERT INTO suscripciones (id, usuario_id, plan, precio, estado, fecha_inicio, fecha_fin) VALUES
(1, 3, 'premium', 4999.00, 'activa', CURRENT_DATE, CURRENT_DATE + INTERVAL '30 days')
ON CONFLICT (id) DO NOTHING;

-- Relación entrenador-alumno (Carlos entrena a María)
INSERT INTO entrenador_alumnos (id, entrenador_id, alumno_id) VALUES
(1, 2, 3)
ON CONFLICT ON CONSTRAINT unique_relacion DO NOTHING;

-- Asignación: Carlos le asigna la rutina de fuerza a María
INSERT INTO alumno_rutinas (id, alumno_id, rutina_id, asignado_por) VALUES
(1, 3, 1, 2)
ON CONFLICT ON CONSTRAINT unique_asignacion DO NOTHING;

-- Aforo inicial del gimnasio
INSERT INTO gimnasio_aforo (gimnasio_id, capacidad_maxima, ocupacion_actual) VALUES
(1, 50, 0)
ON CONFLICT (gimnasio_id) DO NOTHING;

-- Reset sequences to avoid PK conflicts with new inserts
SELECT setval('usuarios_id_seq', (SELECT COALESCE(MAX(id), 0) FROM usuarios));
SELECT setval('gimnasios_id_seq', (SELECT COALESCE(MAX(id), 0) FROM gimnasios));
SELECT setval('entrenadores_id_seq', (SELECT COALESCE(MAX(id), 0) FROM entrenadores));
SELECT setval('rutinas_id_seq', (SELECT COALESCE(MAX(id), 0) FROM rutinas));
SELECT setval('entrenamientos_id_seq', (SELECT COALESCE(MAX(id), 0) FROM entrenamientos));
SELECT setval('ejercicios_id_seq', (SELECT COALESCE(MAX(id), 0) FROM ejercicios));
SELECT setval('entrenamiento_ejercicios_id_seq', (SELECT COALESCE(MAX(id), 0) FROM entrenamiento_ejercicios));
SELECT setval('suscripciones_id_seq', (SELECT COALESCE(MAX(id), 0) FROM suscripciones));
SELECT setval('entrenador_alumnos_id_seq', (SELECT COALESCE(MAX(id), 0) FROM entrenador_alumnos));
SELECT setval('alumno_rutinas_id_seq', (SELECT COALESCE(MAX(id), 0) FROM alumno_rutinas));
