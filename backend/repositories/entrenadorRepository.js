import { pool } from '../database/connection.js';

export async function findAll() {
  const { rows } = await pool.query(
    `SELECT u.id, u.nombre, u.email,
            e.id AS perfil_id, e.especialidad, e.descripcion, e.horario,
            g.id AS gimnasio_id, g.nombre AS gimnasio_nombre
     FROM usuarios u
     LEFT JOIN entrenadores e ON e.usuario_id = u.id
     LEFT JOIN gimnasios g ON g.id = e.gimnasio_id
     WHERE u.rol = 'ENTRENADOR'
     ORDER BY u.nombre`
  );
  return rows;
}

export async function findByUsuarioId(usuarioId) {
  const { rows: [row] } = await pool.query(
    `SELECT u.id, u.nombre, u.email,
            e.id AS perfil_id, e.especialidad, e.descripcion, e.horario,
            g.id AS gimnasio_id, g.nombre AS gimnasio_nombre
     FROM usuarios u
     LEFT JOIN entrenadores e ON e.usuario_id = u.id
     LEFT JOIN gimnasios g ON g.id = e.gimnasio_id
     WHERE u.id = $1 AND u.rol = 'ENTRENADOR'`,
    [usuarioId]
  );
  return row;
}

export async function suscribir(entrenadorId, alumnoId) {
  const result = await pool.query(
    'INSERT INTO entrenador_alumnos (entrenador_id, alumno_id) VALUES ($1, $2) ON CONFLICT DO NOTHING',
    [entrenadorId, alumnoId]
  );
  return result.rowCount > 0;
}

export async function desuscribir(entrenadorId, alumnoId) {
  await pool.query(
    'DELETE FROM entrenador_alumnos WHERE entrenador_id = $1 AND alumno_id = $2',
    [entrenadorId, alumnoId]
  );
}

export async function findMisEntrenadores(alumnoId) {
  const { rows } = await pool.query(
    `SELECT u.id, u.nombre, e.especialidad, e.descripcion, e.horario
     FROM entrenador_alumnos ea
     INNER JOIN usuarios u ON u.id = ea.entrenador_id
     LEFT JOIN entrenadores e ON e.usuario_id = u.id
     WHERE ea.alumno_id = $1`,
    [alumnoId]
  );
  return rows;
}

export async function upsertPerfil(usuarioId, { especialidad, descripcion, horario, gimnasio_id }) {
  await pool.query(
    `INSERT INTO entrenadores (usuario_id, especialidad, descripcion, horario, gimnasio_id)
     VALUES ($1, $2, $3, $4, $5)
     ON CONFLICT (usuario_id) DO UPDATE SET
       especialidad = EXCLUDED.especialidad,
       descripcion  = EXCLUDED.descripcion,
       horario      = EXCLUDED.horario,
       gimnasio_id  = EXCLUDED.gimnasio_id`,
    [usuarioId, especialidad || null, descripcion || null, horario || null, gimnasio_id || null]
  );
}
export async function findAlumnosByEntrenador(entrenadorId) {
  const { rows } = await pool.query(
    `SELECT DISTINCT u.id, u.nombre, u.email
     FROM usuarios u
     INNER JOIN alumno_rutinas ar ON ar.alumno_id = u.id
     WHERE ar.asignado_por = $1
     ORDER BY u.nombre`,
    [entrenadorId]
  );
  return rows;
}

export async function create({usuario_id , nombre, horario, email, descripcion, especialidad }) {
    const { rows: [{ id }] } = await pool.query(
        'INSERT INTO entrenadores (usuario_id, horario, descripcion, especialidad) VALUES ($1, $2, $3, $4) RETURNING id',
        [usuario_id || null, horario || null, descripcion || null, especialidad || null]
    );
    return { id, usuario_id, horario, descripcion, especialidad};
}
