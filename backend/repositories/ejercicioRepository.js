import { pool } from '../database/connection.js';

export async function findByApiId(apiId) {
  const { rows: [row] } = await pool.query(
    'SELECT * FROM ejercicios WHERE api_id = $1',
    [String(apiId)]
  );
  return row;
}

export async function findById(id) {
  const { rows: [row] } = await pool.query(
    'SELECT * FROM ejercicios WHERE id = $1',
    [id]
  );
  return row;
}

// Save an exercise that came from the external API (lazy cache)
export async function saveFromApi({ nombre, descripcion, dificultad, musculos, equipamiento, video_url, api_id }) {
  const { rows: [{ id }] } = await pool.query(
    `INSERT INTO ejercicios (nombre, descripcion, dificultad, musculos, equipamiento, video_url, api_id)
     VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id`,
    [nombre, descripcion || null, dificultad || 'media', musculos || null, equipamiento || null, video_url || null, String(api_id)]
  );
  return { id, nombre, descripcion, dificultad, musculos, equipamiento, video_url, api_id };
}
