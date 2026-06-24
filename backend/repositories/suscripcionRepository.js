import { pool } from '../database/connection.js';

export async function findActiveByUsuario(usuarioId) {
  const { rows: [row] } = await pool.query(
    `SELECT id, usuario_id, plan, precio, estado, fecha_inicio, fecha_fin, created_at
     FROM suscripciones
     WHERE usuario_id = $1 AND estado = 'activa'
     ORDER BY created_at DESC
     LIMIT 1`,
    [usuarioId]
  );
  return row;
}

export async function create({ usuario_id, plan, precio, fecha_inicio, fecha_fin, estado = 'pendiente' }) {
  // Cancel any existing active subscription first
  await pool.query(
    `UPDATE suscripciones SET estado = 'cancelada'
     WHERE usuario_id = $1 AND estado = 'activa'`,
    [usuario_id]
  );

  const { rows: [{ id }] } = await pool.query(
    `INSERT INTO suscripciones (usuario_id, plan, precio, fecha_inicio, fecha_fin, estado)
     VALUES ($1, $2, $3, $4, $5, $6) RETURNING id`,
    [usuario_id, plan, precio, fecha_inicio, fecha_fin, estado]
  );
  return { id, usuario_id, plan, precio, estado, fecha_inicio, fecha_fin };
}

export async function findById(id) {
  const { rows: [row] } = await pool.query(
    'SELECT id, usuario_id, plan, precio, estado, fecha_inicio, fecha_fin, created_at FROM suscripciones WHERE id = $1',
    [id]
  );
  return row;
}

export async function updateEstado(id, estado, fechaFin) {
  const result = await pool.query(
    'UPDATE suscripciones SET estado = $1, fecha_fin = $2 WHERE id = $3',
    [estado, fechaFin, id]
  );
  return result.rowCount > 0;
}
