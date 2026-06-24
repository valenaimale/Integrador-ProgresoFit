import crypto from 'crypto';
import QRCode from 'qrcode';
import { pool } from '../database/connection.js';
import * as repo from '../repositories/accesosRepository.js';

export async function generarQr(usuarioId) {
  const suscripcion = await repo.getSuscripcionActiva(usuarioId);
  if (!suscripcion) {
    const err = new Error('No tenés una suscripción activa');
    err.status = 403;
    throw err;
  }

  const token = crypto.randomBytes(32).toString('hex');
  const expiresAt = new Date(Date.now() + 60 * 60 * 1000); // 1 hora

  await repo.upsertToken(usuarioId, token, expiresAt);

  const qrImage = await QRCode.toDataURL(token, { errorCorrectionLevel: 'M', width: 300 });

  return { qr_image: qrImage, token, expires_at: expiresAt };
}

export async function registrarAcceso(token, gimnasioId) {
  const conn = await pool.connect();
  try {
    await conn.query('BEGIN');

    // 1. Validar token (FOR UPDATE para serializar escaneos concurrentes)
    const gymToken = await repo.findToken(token, conn);
    if (!gymToken) {
      await conn.query('ROLLBACK');
      const err = new Error('QR inválido');
      err.status = 404;
      throw err;
    }
    if (new Date(gymToken.expires_at) < new Date()) {
      await conn.query('ROLLBACK');
      const err = new Error('QR vencido');
      err.status = 410;
      throw err;
    }
    if (gymToken.usado) {
      await conn.query('ROLLBACK');
      const err = new Error('QR ya fue utilizado');
      err.status = 409;
      throw err;
    }

    const usuarioId = gymToken.usuario_id;

    // 2. Verificar suscripción activa
    const suscripcion = await repo.getSuscripcionActiva(usuarioId, conn);
    if (!suscripcion) {
      await conn.query('ROLLBACK');
      const err = new Error('El alumno no tiene una suscripción activa');
      err.status = 403;
      throw err;
    }

    // 3. Lockear fila de aforo para serializar concurrencia
    const aforo = await repo.getAforo(gimnasioId, conn);
    if (!aforo) {
      await conn.query('ROLLBACK');
      const err = new Error('Gimnasio no encontrado');
      err.status = 404;
      throw err;
    }

    // 4. Determinar dirección: ENTRADA o SALIDA según último registro
    const ultimo = await repo.getUltimoAcceso(usuarioId, gimnasioId, conn);
    const tipo = (!ultimo || ultimo.tipo === 'SALIDA') ? 'ENTRADA' : 'SALIDA';

    // 5. Control de capacidad (solo para ENTRADA)
    if (tipo === 'ENTRADA' && aforo.ocupacion_actual >= aforo.capacidad_maxima) {
      await conn.query('ROLLBACK');
      const err = new Error(`Gimnasio al límite de capacidad (${aforo.capacidad_maxima} personas)`);
      err.status = 409;
      throw err;
    }

    // 6. Registrar acceso
    await repo.insertarAcceso(usuarioId, gimnasioId, tipo, conn);

    // 7. Actualizar aforo
    if (tipo === 'ENTRADA') {
      await repo.incrementarAforo(gimnasioId, conn);
    } else {
      await repo.decrementarAforo(gimnasioId, conn);
    }

    // 8. Marcar token como usado (requiere nuevo QR para próximo acceso)
    await repo.marcarTokenUsado(token, conn);

    await conn.query('COMMIT');

    const nuevaOcupacion = tipo === 'ENTRADA'
      ? aforo.ocupacion_actual + 1
      : Math.max(0, aforo.ocupacion_actual - 1);

    return {
      tipo,
      usuario_id: usuarioId,
      gimnasio_id: gimnasioId,
      ocupacion_actual: nuevaOcupacion,
      capacidad_maxima: aforo.capacidad_maxima
    };
  } catch (err) {
    // Solo hacer rollback si la transacción sigue abierta (no la cerramos antes)
    try { await conn.query('ROLLBACK'); } catch (_) {}
    throw err;
  } finally {
    conn.release();
  }
}

export async function getAforo(gimnasioId) {
  const aforo = await repo.getAforo(gimnasioId);
  if (!aforo) {
    const err = new Error('Gimnasio no encontrado');
    err.status = 404;
    throw err;
  }
  return { gimnasio_id: gimnasioId, ...aforo };
}

export async function getHistorial(usuarioId) {
  return repo.getHistorial(usuarioId);
}
