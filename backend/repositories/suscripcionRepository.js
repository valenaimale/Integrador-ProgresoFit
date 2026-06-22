import { pool } from '../database/connection.js';

export async function findActiveByUsuario(usuarioId) {
  const [[row]] = await pool.query(
    `SELECT id, usuario_id, plan, precio, estado, fecha_inicio, fecha_fin, created_at
     FROM suscripciones
     WHERE usuario_id = ? AND estado = 'activa'
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
     WHERE usuario_id = ? AND estado = 'activa'`,
    [usuario_id]
  );

  const [result] = await pool.query(
    `INSERT INTO suscripciones (usuario_id, plan, precio, fecha_inicio, fecha_fin, estado)
     VALUES (?, ?, ?, ?, ?, ?)`,
    [usuario_id, plan, precio, fecha_inicio, fecha_fin, estado]
  );
  return { id: result.insertId, usuario_id, plan, precio, estado, fecha_inicio, fecha_fin };
}

export async function findById(id) {
  const [[row]] = await pool.query(
    'SELECT id, usuario_id, plan, precio, estado, fecha_inicio, fecha_fin, created_at FROM suscripciones WHERE id = ?',
    [id]
  );
  return row;
}

export async function updateEstado(id, estado, fechaFin) {
  const [result] = await pool.query(
    'UPDATE suscripciones SET estado = ?, fecha_fin = ? WHERE id = ?',
    [estado, fechaFin, id]
  );
  return result.affectedRows > 0;
}
