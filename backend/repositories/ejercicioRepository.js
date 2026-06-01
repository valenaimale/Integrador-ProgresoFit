import { pool } from '../database/connection.js';

export async function findByApiId(apiId) {
  const [[row]] = await pool.query(
    'SELECT * FROM ejercicios WHERE api_id = ?',
    [String(apiId)]
  );
  return row;
}

export async function findById(id) {
  const [[row]] = await pool.query(
    'SELECT * FROM ejercicios WHERE id = ?',
    [id]
  );
  return row;
}

// Save an exercise that came from the external API (lazy cache)
export async function saveFromApi({ nombre, descripcion, dificultad, musculos, equipamiento, video_url, api_id }) {
  const [result] = await pool.query(
    `INSERT INTO ejercicios (nombre, descripcion, dificultad, musculos, equipamiento, video_url, api_id)
     VALUES (?, ?, ?, ?, ?, ?, ?)`,
    [nombre, descripcion || null, dificultad || 'media', musculos || null, equipamiento || null, video_url || null, String(api_id)]
  );
  return { id: result.insertId, nombre, descripcion, dificultad, musculos, equipamiento, video_url, api_id };
}
