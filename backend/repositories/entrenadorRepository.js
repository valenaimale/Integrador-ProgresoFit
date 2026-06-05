import { pool } from '../database/connection.js';

export async function findAll() {
  const [rows] = await pool.query(
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
  const [[row]] = await pool.query(
    `SELECT u.id, u.nombre, u.email,
            e.id AS perfil_id, e.especialidad, e.descripcion, e.horario,
            g.id AS gimnasio_id, g.nombre AS gimnasio_nombre
     FROM usuarios u
     LEFT JOIN entrenadores e ON e.usuario_id = u.id
     LEFT JOIN gimnasios g ON g.id = e.gimnasio_id
     WHERE u.id = ? AND u.rol = 'ENTRENADOR'`,
    [usuarioId]
  );
  return row;
}

export async function suscribir(entrenadorId, alumnoId) {
  const [result] = await pool.query(
    'INSERT IGNORE INTO entrenador_alumnos (entrenador_id, alumno_id) VALUES (?, ?)',
    [entrenadorId, alumnoId]
  );
  return result.affectedRows > 0;
}

export async function desuscribir(entrenadorId, alumnoId) {
  await pool.query(
    'DELETE FROM entrenador_alumnos WHERE entrenador_id = ? AND alumno_id = ?',
    [entrenadorId, alumnoId]
  );
}

export async function findMisEntrenadores(alumnoId) {
  const [rows] = await pool.query(
    `SELECT u.id, u.nombre, e.especialidad, e.descripcion, e.horario
     FROM entrenador_alumnos ea
     INNER JOIN usuarios u ON u.id = ea.entrenador_id
     LEFT JOIN entrenadores e ON e.usuario_id = u.id
     WHERE ea.alumno_id = ?`,
    [alumnoId]
  );
  return rows;
}

export async function upsertPerfil(usuarioId, { especialidad, descripcion, horario, gimnasio_id }) {
  await pool.query(
    `INSERT INTO entrenadores (usuario_id, especialidad, descripcion, horario, gimnasio_id)
     VALUES (?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
       especialidad = VALUES(especialidad),
       descripcion  = VALUES(descripcion),
       horario      = VALUES(horario),
       gimnasio_id  = VALUES(gimnasio_id)`,
    [usuarioId, especialidad || null, descripcion || null, horario || null, gimnasio_id || null]
  );
}
export async function findAlumnosByEntrenador(entrenadorId) {
  const [rows] = await pool.query(
    `SELECT DISTINCT u.id, u.nombre, u.email
     FROM usuarios u
     INNER JOIN alumno_rutinas ar ON ar.alumno_id = u.id
     WHERE ar.asignado_por = ?
     ORDER BY u.nombre`,
    [entrenadorId]
  );
  return rows;
}

export async function create({usuario_id , nombre, horario, email, descripcion, especialidad }) {
    const [result] = await pool.query(
        'INSERT INTO entrenadores (usuario_id, horario, descripcion, especialidad) VALUES (?, ?, ?, ?)',
        [usuario_id || null, horario || null, descripcion || null, especialidad || null]
    );
    return { id: result.insertId, usuario_id, horario, descripcion, especialidad};
}
