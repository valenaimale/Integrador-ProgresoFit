import { pool } from '../database/connection.js';

export async function findAll() {
  const { rows } = await pool.query(
    `SELECT r.id, r.titulo, r.descripcion, r.objetivo, r.created_at,
            u.nombre AS entrenador_nombre
     FROM rutinas r
     LEFT JOIN usuarios u ON u.id = r.entrenador_id
     ORDER BY r.created_at DESC`
  );
  return rows;
}

export async function findByAlumno(alumnoId) {
  const { rows } = await pool.query(
    `SELECT r.id, r.titulo, r.descripcion, r.objetivo, r.created_at,
            u.nombre AS entrenador_nombre
     FROM rutinas r
     INNER JOIN alumno_rutinas ar ON ar.rutina_id = r.id
     LEFT JOIN  usuarios u ON u.id = r.entrenador_id
     WHERE ar.alumno_id = $1
     ORDER BY ar.fecha_asignacion DESC`,
    [alumnoId]
  );
  return rows;
}

export async function findById(id) {
  // Main routine
  const { rows: [rutina] } = await pool.query(
    `SELECT r.id, r.titulo, r.descripcion, r.objetivo, r.entrenador_id, r.created_at,
            u.nombre AS entrenador_nombre
     FROM rutinas r
     LEFT JOIN usuarios u ON u.id = r.entrenador_id
     WHERE r.id = $1`,
    [id]
  );
  if (!rutina) return null;

  // Training days + exercises in one query
  const { rows } = await pool.query(
    `SELECT
       e.id AS dia_id, e.nombre_dia, e.grupo_muscular, e.duracion_minutos, e.orden AS dia_orden,
       ej.id AS ejercicio_id, ej.api_id, ej.nombre AS ejercicio_nombre, ej.musculos,
       ej.equipamiento, ej.dificultad,
       ee.id AS pivot_id, ee.series_repeticiones, ee.orden AS ej_orden
     FROM entrenamientos e
     LEFT JOIN entrenamiento_ejercicios ee ON ee.entrenamiento_id = e.id
     LEFT JOIN ejercicios ej ON ej.id = ee.ejercicio_id
     WHERE e.rutina_id = $1
     ORDER BY e.orden, ee.orden`,
    [id]
  );

  // Group flat rows into nested structure
  const diasMap = new Map();
  for (const row of rows) {
    if (!diasMap.has(row.dia_id)) {
      diasMap.set(row.dia_id, {
        id: row.dia_id,
        nombre_dia: row.nombre_dia,
        grupo_muscular: row.grupo_muscular,
        duracion_minutos: row.duracion_minutos,
        orden: row.dia_orden,
        ejercicios: []
      });
    }
    if (row.ejercicio_id) {
      diasMap.get(row.dia_id).ejercicios.push({
        pivot_id: row.pivot_id,
        id: row.ejercicio_id,
        api_id: row.api_id,
        nombre: row.ejercicio_nombre,
        musculos: row.musculos,
        equipamiento: row.equipamiento,
        dificultad: row.dificultad,
        series_repeticiones: row.series_repeticiones,
        orden: row.ej_orden
      });
    }
  }

  rutina.dias = Array.from(diasMap.values());

  const { rows: asignados } = await pool.query(
    `SELECT u.id, u.nombre, u.email
     FROM alumno_rutinas ar
     INNER JOIN usuarios u ON u.id = ar.alumno_id
     WHERE ar.rutina_id = $1`,
    [id]
  );
  rutina.alumnos_asignados = asignados;

  return rutina;
}

export async function create({ titulo, descripcion, objetivo, entrenador_id }) {
  const { rows: [{ id }] } = await pool.query(
    'INSERT INTO rutinas (titulo, descripcion, objetivo, entrenador_id) VALUES ($1, $2, $3, $4) RETURNING id',
    [titulo, descripcion || null, objetivo || null, entrenador_id]
  );
  return { id, titulo, descripcion, objetivo, entrenador_id };
}

export async function update(id, { titulo, descripcion, objetivo }) {
  await pool.query(
    'UPDATE rutinas SET titulo = $1, descripcion = $2, objetivo = $3 WHERE id = $4',
    [titulo, descripcion || null, objetivo || null, id]
  );
}

export async function remove(id) {
  await pool.query('DELETE FROM rutinas WHERE id = $1', [id]);
}

export async function isOwner(rutinaId, userId) {
  const { rows: [row] } = await pool.query(
    'SELECT id FROM rutinas WHERE id = $1 AND entrenador_id = $2',
    [rutinaId, userId]
  );
  return !!row;
}

// --- Training days ---

export async function addDia(rutinaId, { nombre_dia, grupo_muscular, duracion_minutos, orden }) {
  const { rows: [{ id }] } = await pool.query(
    'INSERT INTO entrenamientos (rutina_id, nombre_dia, grupo_muscular, duracion_minutos, orden) VALUES ($1, $2, $3, $4, $5) RETURNING id',
    [rutinaId, nombre_dia, grupo_muscular || null, duracion_minutos || null, orden || 1]
  );
  return { id, rutina_id: rutinaId, nombre_dia, grupo_muscular, duracion_minutos, orden };
}

export async function removeDia(diaId) {
  await pool.query('DELETE FROM entrenamientos WHERE id = $1', [diaId]);
}

export async function diaPertenece(diaId, rutinaId) {
  const { rows: [row] } = await pool.query(
    'SELECT id FROM entrenamientos WHERE id = $1 AND rutina_id = $2',
    [diaId, rutinaId]
  );
  return !!row;
}

// --- Exercises within a day ---

export async function addEjercicioToDia(diaId, { ejercicio_id, series_repeticiones, orden }) {
  const { rows: [{ id }] } = await pool.query(
    'INSERT INTO entrenamiento_ejercicios (entrenamiento_id, ejercicio_id, series_repeticiones, orden) VALUES ($1, $2, $3, $4) RETURNING id',
    [diaId, ejercicio_id, series_repeticiones || null, orden || 1]
  );
  return { id, entrenamiento_id: diaId, ejercicio_id, series_repeticiones, orden };
}

export async function removeEjercicioFromDia(pivotId) {
  await pool.query('DELETE FROM entrenamiento_ejercicios WHERE id = $1', [pivotId]);
}

// --- Assignment ---

export async function asignarAlumno(rutinaId, alumnoId, asignadoPor) {
  const result = await pool.query(
    'INSERT INTO alumno_rutinas (rutina_id, alumno_id, asignado_por) VALUES ($1, $2, $3) ON CONFLICT DO NOTHING',
    [rutinaId, alumnoId, asignadoPor]
  );
  return result.rowCount > 0;
}

export async function desasignarAlumno(rutinaId, alumnoId) {
  await pool.query(
    'DELETE FROM alumno_rutinas WHERE rutina_id = $1 AND alumno_id = $2',
    [rutinaId, alumnoId]
  );
}
