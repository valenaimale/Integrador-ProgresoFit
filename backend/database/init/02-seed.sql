-- ============================================================
-- ProgresoFit - Seed data (auto-init)
-- Password for ALL users: 123456
-- Hash generado con bcrypt (cost=10)
-- ============================================================

-- Usuarios de ejemplo
INSERT IGNORE INTO usuarios (id, nombre, email, password, rol) VALUES
(1, 'Admin ProgresoFit', 'admin@progresofit.com', '$2b$10$j2ZAvVb0yFNgBZBTJ.7HB.g0ive9J3omfjB14D56NNh5C7hg7dCe2', 'ADMIN'),
(2, 'Carlos Entrenador', 'carlos@test.com',       '$2b$10$j2ZAvVb0yFNgBZBTJ.7HB.g0ive9J3omfjB14D56NNh5C7hg7dCe2', 'ENTRENADOR'),
(3, 'María Alumna',      'maria@test.com',        '$2b$10$j2ZAvVb0yFNgBZBTJ.7HB.g0ive9J3omfjB14D56NNh5C7hg7dCe2', 'ALUMNO');

-- Gimnasio de ejemplo
INSERT IGNORE INTO gimnasios (id, nombre, direccion, horarios, telefono, email, descripcion, servicios, activo) VALUES
(1, 'Gimnasio Central',
    'Av. Siempre Viva 123',
    'Lun a Vie 7:00-22:00, Sáb 9:00-18:00',
    '11-5555-0123',
    'contacto@gimnasiocentral.com',
    'Gimnasio equipado con maquinaria de última generación',
    'Musculación, CrossFit, Yoga, Pilates, Functional Training',
    1);

-- Perfil de entrenador (extiende a carlos@test.com)
INSERT IGNORE INTO entrenadores (id, usuario_id, especialidad, descripcion, horario, gimnasio_id) VALUES
(1, 2,
 'Fuerza y Acondicionamiento',
 'Entrenador certificado con más de 10 años de experiencia. Especialista en hipertrofia y powerlifting.',
 'Lun a Vie 7:00-13:00',
 1);

-- Rutina de ejemplo
INSERT IGNORE INTO rutinas (id, titulo, descripcion, objetivo, entrenador_id) VALUES
(1,
 'Rutina Fuerza Total',
 'Rutina de cuerpo completo enfocada en ejercicios compuestos. Ideal para quienes recién arrancan o quieren retomar.',
 'Hipertrofia y fuerza general',
 2);

-- Días de entrenamiento
INSERT IGNORE INTO entrenamientos (id, rutina_id, nombre_dia, grupo_muscular, duracion_minutos, orden) VALUES
(1, 1, 'Lunes — Pecho y Tríceps',      'Pecho, Hombro anterior, Tríceps',  60, 1),
(2, 1, 'Miércoles — Espalda y Bíceps', 'Espalda, Hombro posterior, Bíceps', 60, 2),
(3, 1, 'Viernes — Piernas',            'Cuádriceps, Glúteos, Femorales',    60, 3);

-- Ejercicios precargados (simulan datos cacheados de wger.de)
INSERT IGNORE INTO ejercicios (id, nombre, descripcion, dificultad, musculos, equipamiento, api_id) VALUES
(1,
 'Press de banca',
 'Ejercicio compuesto para pectoral mayor. Acostado en banco plano, bajá la barra al pecho y presioná hacia arriba.',
 'media',
 'Pectoral mayor, Hombro anterior, Tríceps',
 'Barra, Banco plano, Discos',
 '011'),
(2,
 'Dominadas',
 'Colgate de la barra con agarre prono y elevá tu cuerpo hasta que la barbilla pase la barra.',
 'media',
 'Dorsal ancho, Bíceps, Hombro posterior',
 'Barra de dominadas',
 '015'),
(3,
 'Sentadilla',
 'Con la barra en los hombros, flexioná rodillas y cadera como si te sentaras en una silla, y volvé a subir.',
 'media',
 'Cuádriceps, Glúteos, Femorales, Core',
 'Barra, Soporte de sentadilla',
 '012'),
(4,
 'Press militar',
 'De pie, presioná la barra desde los hombros hacia arriba hasta extender completamente los brazos.',
 'media',
 'Hombros, Tríceps, Core',
 'Barra, Discos',
 '024'),
(5,
 'Remo con barra',
 'Inclinado con el torso a 45°, traccioná la barra hacia el abdomen manteniendo la espalda recta.',
 'media',
 'Espalda media, Dorsal, Bíceps',
 'Barra, Discos',
 '017');

-- Asignación de ejercicios a cada día
INSERT IGNORE INTO entrenamiento_ejercicios (id, entrenamiento_id, ejercicio_id, series_repeticiones, orden) VALUES
-- Lunes: Press banca + Press militar
(1, 1, 1, '4x8-10', 1),
(2, 1, 4, '3x10',   2),
-- Miércoles: Dominadas + Remo
(3, 2, 2, '3x8',    1),
(4, 2, 5, '4x10',   2),
-- Viernes: Sentadilla
(5, 3, 3, '4x10',   1);

-- Suscripción activa para María
INSERT IGNORE INTO suscripciones (id, usuario_id, plan, precio, estado, fecha_inicio, fecha_fin) VALUES
(1, 3, 'premium', 4999.00, 'activa', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY));

-- Asignación: Carlos le asigna la rutina de fuerza a María
INSERT IGNORE INTO alumno_rutinas (id, alumno_id, rutina_id, asignado_por) VALUES
(1, 3, 1, 2);
