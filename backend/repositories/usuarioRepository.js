import { pool } from '../database/connection.js';

export async function findById(id) {
  const { rows } = await pool.query(
    'SELECT id, nombre, email, rol, foto_url, created_at FROM usuarios WHERE id = $1',
    [id]
  );
  return rows[0];
}

export async function findByEmail(email) {
  const { rows } = await pool.query('SELECT id FROM usuarios WHERE email = $1', [email]);
  return rows[0];
}

// obtiene solo el hash de la contraseña — se usa exclusivamente para verificar la contraseña actual antes de cambiarla
export async function findPasswordById(id) {
  const { rows } = await pool.query('SELECT password FROM usuarios WHERE id = $1', [id]);
  return rows[0]?.password;
}

export async function updateProfile(id, fields) {
  const setClauses = [];
  const params = [];
  let paramIndex = 1;

  if (fields.nombre    !== undefined) { setClauses.push(`nombre = $${paramIndex++}`);    params.push(fields.nombre); }
  if (fields.email     !== undefined) { setClauses.push(`email = $${paramIndex++}`);     params.push(fields.email); }
  if (fields.password  !== undefined) { setClauses.push(`password = $${paramIndex++}`);  params.push(fields.password); }
  if (fields.foto_url  !== undefined) { setClauses.push(`foto_url = $${paramIndex++}`);  params.push(fields.foto_url); }

  if (setClauses.length === 0) return;

  params.push(id);
  await pool.query(`UPDATE usuarios SET ${setClauses.join(', ')} WHERE id = $${paramIndex}`, params);
}

export async function findMisAlumnos(entrenadorId) {
  const { rows } = await pool.query(
    `SELECT u.id, u.nombre, u.email
     FROM entrenador_alumnos ea
     INNER JOIN usuarios u ON u.id = ea.alumno_id
     WHERE ea.entrenador_id = $1
     ORDER BY u.nombre`,
    [entrenadorId]
  );
  return rows;
}

export async function create({ nombre, email, password, rol, foto_url = null }) {
  const { rows: [{ id }] } = await pool.query(
    'INSERT INTO usuarios (nombre, email, password, rol, foto_url) VALUES ($1, $2, $3, $4, $5) RETURNING id',
    [nombre || null, email, password, rol, foto_url]
  );
  return { id, nombre: nombre || null, email, rol, foto_url };
}

export async function findAllByRol(rol) {
  const { rows } = await pool.query(
    'SELECT id, nombre, email, created_at FROM usuarios WHERE rol = $1 ORDER BY nombre',
    [rol]
  );
  return rows;
}
