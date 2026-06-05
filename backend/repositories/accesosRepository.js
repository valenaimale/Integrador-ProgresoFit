import { pool } from '../database/connection.js';

// Usa una conexión de transacción (conn) cuando se le pasa; si no, usa el pool directo.
const q = (conn, sql, params) => conn ? conn.query(sql, params) : pool.query(sql, params);

export async function upsertToken(usuarioId, token, expiresAt) {
  await pool.query(
    `INSERT INTO gym_tokens (usuario_id, token, expires_at, usado)
     VALUES (?, ?, ?, 0)
     ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), usado = 0`,
    [usuarioId, token, expiresAt]
  );
}

export async function findToken(token, conn) {
  const [[row]] = await q(conn,
    'SELECT id, usuario_id, expires_at, usado FROM gym_tokens WHERE token = ?',
    [token]
  );
  return row || null;
}

export async function marcarTokenUsado(token, conn) {
  await q(conn, 'UPDATE gym_tokens SET usado = 1 WHERE token = ?', [token]);
}

export async function getAforo(gimnasioId, conn) {
  const [[row]] = await q(conn,
    'SELECT ocupacion_actual, capacidad_maxima FROM gimnasio_aforo WHERE gimnasio_id = ?',
    [gimnasioId]
  );
  return row || null;
}

export async function incrementarAforo(gimnasioId, conn) {
  await q(conn,
    'UPDATE gimnasio_aforo SET ocupacion_actual = ocupacion_actual + 1 WHERE gimnasio_id = ?',
    [gimnasioId]
  );
}

export async function decrementarAforo(gimnasioId, conn) {
  await q(conn,
    'UPDATE gimnasio_aforo SET ocupacion_actual = GREATEST(0, ocupacion_actual - 1) WHERE gimnasio_id = ?',
    [gimnasioId]
  );
}

export async function getUltimoAcceso(usuarioId, gimnasioId, conn) {
  const [[row]] = await q(conn,
    `SELECT tipo FROM accesos_gimnasio
     WHERE usuario_id = ? AND gimnasio_id = ?
     ORDER BY registrado_at DESC LIMIT 1`,
    [usuarioId, gimnasioId]
  );
  return row || null;
}

export async function insertarAcceso(usuarioId, gimnasioId, tipo, conn) {
  const [result] = await q(conn,
    'INSERT INTO accesos_gimnasio (usuario_id, gimnasio_id, tipo) VALUES (?, ?, ?)',
    [usuarioId, gimnasioId, tipo]
  );
  return result.insertId;
}

export async function getSuscripcionActiva(usuarioId, conn) {
  const [[row]] = await q(conn,
    `SELECT id FROM suscripciones
     WHERE usuario_id = ? AND estado = 'activa' AND fecha_fin >= CURDATE()
     LIMIT 1`,
    [usuarioId]
  );
  return row || null;
}

export async function getHistorial(usuarioId) {
  const [rows] = await pool.query(
    `SELECT ag.id, ag.tipo, ag.registrado_at, g.nombre AS gimnasio_nombre
     FROM accesos_gimnasio ag
     JOIN gimnasios g ON g.id = ag.gimnasio_id
     WHERE ag.usuario_id = ?
     ORDER BY ag.registrado_at DESC
     LIMIT 50`,
    [usuarioId]
  );
  return rows;
}
