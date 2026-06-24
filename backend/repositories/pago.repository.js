import { pool } from '../database/connection.js';

/**
 * Crea un nuevo registro de pago con estado 'pendiente'.
 * @param {Object} data
 * @param {number} data.suscripcion_id
 * @param {number} data.monto
 * @param {string} data.mp_preference_id
 * @returns {Promise<Object>} Pago creado
 */
export async function create({ suscripcion_id, monto, mp_preference_id }) {
  const { rows: [{ id }] } = await pool.query(
    `INSERT INTO pagos (suscripcion_id, monto, mp_preference_id)
     VALUES ($1, $2, $3) RETURNING id`,
    [suscripcion_id, monto, mp_preference_id]
  );
  return findById(id);
}

/**
 * Busca un pago por su ID.
 * @param {number} id
 * @returns {Promise<Object|undefined>}
 */
export async function findById(id) {
  const { rows: [row] } = await pool.query(
    `SELECT id, suscripcion_id, monto, estado,
            mp_preference_id, mp_payment_id, mp_status, mp_status_detail,
            intentos, creado_en, actualizado_en
     FROM pagos WHERE id = $1`,
    [id]
  );
  return row;
}

/**
 * Busca un pago por mp_preference_id.
 * @param {string} preferenceId
 * @returns {Promise<Object|undefined>}
 */
export async function findByPreferenceId(preferenceId) {
  const { rows: [row] } = await pool.query(
    `SELECT id, suscripcion_id, monto, estado,
            mp_preference_id, mp_payment_id, mp_status, mp_status_detail,
            intentos, creado_en, actualizado_en
     FROM pagos WHERE mp_preference_id = $1`,
    [preferenceId]
  );
  return row;
}

/**
 * Busca un pago por mp_payment_id (usado para idempotencia del webhook).
 * @param {string} paymentId
 * @returns {Promise<Object|undefined>}
 */
export async function findByPaymentId(paymentId) {
  const { rows: [row] } = await pool.query(
    `SELECT id, suscripcion_id, monto, estado,
            mp_preference_id, mp_payment_id, mp_status, mp_status_detail,
            intentos, creado_en, actualizado_en
     FROM pagos WHERE mp_payment_id = $1`,
    [paymentId]
  );
  return row;
}

/**
 * Actualiza condicionalmente los campos de un pago.
 * Solo modifica los campos provistos en `fields`.
 * @param {number} id
 * @param {Object} fields
 * @param {string}  [fields.estado]
 * @param {string}  [fields.mp_payment_id]
 * @param {string}  [fields.mp_status]
 * @param {string}  [fields.mp_status_detail]
 * @param {number}  [fields.intentos]
 * @returns {Promise<Object|null>} Pago actualizado, o null si no hay cambios
 */
export async function updateStatus(id, fields) {
  const setClauses = [];
  const params = [];
  let paramIndex = 1;

  if (fields.estado !== undefined) {
    setClauses.push(`estado = $${paramIndex++}`);
    params.push(fields.estado);
  }
  if (fields.mp_payment_id !== undefined) {
    setClauses.push(`mp_payment_id = $${paramIndex++}`);
    params.push(fields.mp_payment_id);
  }
  if (fields.mp_status !== undefined) {
    setClauses.push(`mp_status = $${paramIndex++}`);
    params.push(fields.mp_status);
  }
  if (fields.mp_status_detail !== undefined) {
    setClauses.push(`mp_status_detail = $${paramIndex++}`);
    params.push(fields.mp_status_detail);
  }
  if (fields.intentos !== undefined) {
    setClauses.push(`intentos = $${paramIndex++}`);
    params.push(fields.intentos);
  }

  if (setClauses.length === 0) return null;

  params.push(id);
  await pool.query(
    `UPDATE pagos SET ${setClauses.join(', ')} WHERE id = $${paramIndex}`,
    params
  );
  return findById(id);
}

/**
 * Retorna todos los pagos de una suscripción, ordenados del más reciente al más antiguo.
 * @param {number} suscripcionId
 * @returns {Promise<Array<Object>>}
 */
export async function findBySuscripcion(suscripcionId) {
  const { rows } = await pool.query(
    `SELECT id, suscripcion_id, monto, estado,
            mp_preference_id, mp_payment_id, mp_status, mp_status_detail,
            intentos, creado_en, actualizado_en
     FROM pagos
     WHERE suscripcion_id = $1
     ORDER BY creado_en DESC`,
    [suscripcionId]
  );
  return rows;
}
