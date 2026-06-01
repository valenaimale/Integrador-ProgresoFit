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
