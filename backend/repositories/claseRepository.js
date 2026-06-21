import { pool } from '../database/connection.js';

// Usa una conexión de transacción (conn) cuando se le pasa; si no, usa el pool directo.
const q = (conn, sql, params) => conn ? conn.query(sql, params) : pool.query(sql, params);

// Clases ACTIVAS de un gimnasio, ordenadas por fecha/hora.
export async function listarPorGimnasio(gimnasioId) {
  const [rows] = await pool.query(
    `SELECT id, gimnasio_id, nombre, descripcion, fecha, hora_inicio, hora_fin,
            cupo_maximo, inscriptos, estado, created_at
     FROM clases
     WHERE gimnasio_id = ? AND estado = 'ACTIVA'
     ORDER BY fecha, hora_inicio`,
    [gimnasioId]
  );
  return rows;
}

// Clases ACTIVAS futuras de todos los gimnasios (para que el alumno explore).
export async function listarDisponibles() {
  const [rows] = await pool.query(
    `SELECT c.id, c.gimnasio_id, c.nombre, c.descripcion, c.fecha, c.hora_inicio, c.hora_fin,
            c.cupo_maximo, c.inscriptos, c.estado, g.nombre AS gimnasio_nombre,
            (c.cupo_maximo - c.inscriptos) AS cupos_restantes,
            (c.cupo_maximo - c.inscriptos > 0 AND c.cupo_maximo - c.inscriptos <= 3) AS pocos_cupos
     FROM clases c
     JOIN gimnasios g ON g.id = c.gimnasio_id
     WHERE c.estado = 'ACTIVA' AND c.fecha >= CURDATE()
     ORDER BY c.fecha, c.hora_inicio`
  );
  return rows;
}

export async function findById(id, conn) {
  const [[row]] = await q(conn,
    `SELECT id, gimnasio_id, nombre, descripcion, fecha, hora_inicio, hora_fin,
            cupo_maximo, inscriptos, estado, created_at
     FROM clases WHERE id = ?`,
    [id]
  );
  return row || null;
}

export async function create({ gimnasio_id, nombre, descripcion, fecha, hora_inicio, hora_fin, cupo_maximo }) {
  const [result] = await pool.query(
    `INSERT INTO clases (gimnasio_id, nombre, descripcion, fecha, hora_inicio, hora_fin, cupo_maximo)
     VALUES (?, ?, ?, ?, ?, ?, ?)`,
    [gimnasio_id, nombre, descripcion || null, fecha, hora_inicio, hora_fin || null, cupo_maximo]
  );
  return findById(result.insertId);
}

export async function cancelar(id) {
  await pool.query(`UPDATE clases SET estado = 'CANCELADA' WHERE id = ?`, [id]);
}

export async function existeInscripcion(claseId, usuarioId, conn) {
  const [[row]] = await q(conn,
    'SELECT id FROM clase_inscripciones WHERE clase_id = ? AND usuario_id = ?',
    [claseId, usuarioId]
  );
  return !!row;
}

export async function insertarInscripcion(claseId, usuarioId, conn) {
  const [result] = await q(conn,
    'INSERT INTO clase_inscripciones (clase_id, usuario_id) VALUES (?, ?)',
    [claseId, usuarioId]
  );
  return result.insertId;
}

// Elimina la inscripción. Devuelve affectedRows (0 si no estaba anotado).
export async function eliminarInscripcion(claseId, usuarioId, conn) {
  const [result] = await q(conn,
    'DELETE FROM clase_inscripciones WHERE clase_id = ? AND usuario_id = ?',
    [claseId, usuarioId]
  );
  return result.affectedRows;
}

// Incremento condicional: solo suma si la clase está activa y hay lugar. Devuelve affectedRows.
export async function ocuparCupo(claseId, conn) {
  const [result] = await q(conn,
    `UPDATE clases
        SET inscriptos = inscriptos + 1
      WHERE id = ? AND estado = 'ACTIVA' AND inscriptos < cupo_maximo`,
    [claseId]
  );
  return result.affectedRows; // 0 => sin cupo
}

export async function liberarCupo(claseId, conn) {
  await q(conn,
    `UPDATE clases SET inscriptos = GREATEST(0, inscriptos - 1) WHERE id = ?`,
    [claseId]
  );
}

// Clases a las que está anotado el alumno (con datos del gimnasio).
export async function listarInscripcionesDeUsuario(usuarioId) {
  const [rows] = await pool.query(
    `SELECT c.id, c.gimnasio_id, c.nombre, c.descripcion, c.fecha, c.hora_inicio, c.hora_fin,
            c.cupo_maximo, c.inscriptos, c.estado, g.nombre AS gimnasio_nombre,
            ci.created_at AS inscripto_at
     FROM clase_inscripciones ci
     JOIN clases c    ON c.id = ci.clase_id
     JOIN gimnasios g ON g.id = c.gimnasio_id
     WHERE ci.usuario_id = ?
     ORDER BY c.fecha, c.hora_inicio`,
    [usuarioId]
  );
  return rows;
}

// Quiénes se anotaron a una clase (para el gimnasio).
export async function listarInscriptosDeClase(claseId) {
  const [rows] = await pool.query(
    `SELECT u.id, u.nombre, u.email, ci.created_at AS inscripto_at
     FROM clase_inscripciones ci
     JOIN usuarios u ON u.id = ci.usuario_id
     WHERE ci.clase_id = ?
     ORDER BY ci.created_at`,
    [claseId]
  );
  return rows;
}
