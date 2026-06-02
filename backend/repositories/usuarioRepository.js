import { pool } from '../database/connection.js';

export async function findById(id) {
  const [rows] = await pool.query(
    'SELECT id, nombre, email, rol, created_at FROM usuarios WHERE id = ?',
    [id]
  );
  return rows[0];
}

export async function findByEmail(email) {
  const [rows] = await pool.query('SELECT id FROM usuarios WHERE email = ?', [email]);
  return rows[0];
}

// obtiene solo el hash de la contraseña — se usa exclusivamente para verificar la contraseña actual antes de cambiarla
export async function findPasswordById(id) {
  const [rows] = await pool.query('SELECT password FROM usuarios WHERE id = ?', [id]);
  return rows[0]?.password;
}

export async function updateProfile(id, fields) {
  const setClauses = [];
  const params = [];

  if (fields.nombre !== undefined) { setClauses.push('nombre = ?');   params.push(fields.nombre); }
  if (fields.email  !== undefined) { setClauses.push('email = ?');    params.push(fields.email); }
  if (fields.password !== undefined) { setClauses.push('password = ?'); params.push(fields.password); }

  if (setClauses.length === 0) return;

  params.push(id);
  await pool.query(`UPDATE usuarios SET ${setClauses.join(', ')} WHERE id = ?`, params);
}

export async function create({ nombre, email, password, rol }) {
  const [result] = await pool.query(
    'INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)',
    [nombre || null, email, password, rol]
  );
  return { id: result.insertId, nombre: nombre || null, email, rol };
}
