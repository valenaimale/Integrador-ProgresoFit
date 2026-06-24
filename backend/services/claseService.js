import { pool } from '../database/connection.js';
import * as repo from '../repositories/claseRepository.js';
import * as gimnasioRepository from '../repositories/gimnasioRepository.js';

// Resuelve el gimnasio del usuario logueado (rol GIMNASIO). Nunca se confía en el body.
async function resolverGimnasioId(user) {
  if (user.gimnasio_id) return user.gimnasio_id;
  const gimnasio = await gimnasioRepository.findByUsuarioId(user.id);
  if (!gimnasio) {
    const err = new Error('No tenés un perfil de gimnasio asociado');
    err.status = 403;
    throw err;
  }
  return gimnasio.id;
}

function esFechaPasada(fecha) {
  // fecha en formato YYYY-MM-DD. Comparamos contra el día de hoy.
  const hoy = new Date();
  hoy.setHours(0, 0, 0, 0);
  const f = new Date(`${fecha}T00:00:00`);
  return f < hoy;
}

export async function crearClase(user, { nombre, descripcion, fecha, hora_inicio, hora_fin, cupo_maximo }) {
  if (!['GIMNASIO', 'ADMIN'].includes(user.rol)) {
    const err = new Error('No tenés permiso para crear clases');
    err.status = 403;
    throw err;
  }

  if (!nombre || !nombre.trim()) throw badRequest('El nombre de la clase es obligatorio');
  if (!fecha) throw badRequest('La fecha es obligatoria');
  if (!hora_inicio) throw badRequest('La hora de inicio es obligatoria');
  if (esFechaPasada(fecha)) throw badRequest('La fecha no puede ser pasada');

  const cupo = Number(cupo_maximo);
  if (!Number.isInteger(cupo) || cupo <= 0) throw badRequest('El cupo debe ser un número mayor a 0');

  if (hora_fin && hora_fin <= hora_inicio) throw badRequest('La hora de fin debe ser posterior a la de inicio');

  const gimnasio_id = await resolverGimnasioId(user);

  return repo.create({
    gimnasio_id,
    nombre: nombre.trim(),
    descripcion,
    fecha,
    hora_inicio,
    hora_fin: hora_fin || null,
    cupo_maximo: cupo,
  });
}

export async function listarMisClases(user) {
  const gimnasio_id = await resolverGimnasioId(user);
  return repo.listarPorGimnasio(gimnasio_id);
}

export async function listarDisponibles() {
  return repo.listarDisponibles();
}

export async function cancelarClase(user, claseId) {
  const clase = await repo.findById(claseId);
  if (!clase) throw notFound('Clase no encontrada');
  await verificarPropiedad(user, clase);
  await repo.cancelar(claseId);
  return { id: claseId, estado: 'CANCELADA' };
}

export async function listarInscriptos(user, claseId) {
  const clase = await repo.findById(claseId);
  if (!clase) throw notFound('Clase no encontrada');
  await verificarPropiedad(user, clase);
  return repo.listarInscriptosDeClase(claseId);
}

export async function inscribir(user, claseId) {
  const conn = await pool.connect();
  try {
    await conn.query('BEGIN');

    const clase = await repo.findById(claseId, conn);
    if (!clase) {
      await conn.query('ROLLBACK');
      throw notFound('Clase no encontrada');
    }
    if (clase.estado !== 'ACTIVA') {
      await conn.query('ROLLBACK');
      throw conflict('La clase no está disponible');
    }
    if (esFechaPasada(formatFecha(clase.fecha))) {
      await conn.query('ROLLBACK');
      throw conflict('La clase ya pasó');
    }

    if (await repo.existeInscripcion(claseId, user.id, conn)) {
      await conn.query('ROLLBACK');
      throw conflict('Ya estás anotado en esta clase');
    }

    // Incremento condicional atómico: si devuelve 0, no había cupo.
    const ocupado = await repo.ocuparCupo(claseId, conn);
    if (ocupado === 0) {
      await conn.query('ROLLBACK');
      throw conflict('La clase está llena');
    }

    await repo.insertarInscripcion(claseId, user.id, conn);
    await conn.query('COMMIT');

    return {
      clase_id: claseId,
      inscriptos: clase.inscriptos + 1,
      cupo_maximo: clase.cupo_maximo,
    };
  } catch (err) {
    try { await conn.query('ROLLBACK'); } catch (_) {}
    throw err;
  } finally {
    conn.release();
  }
}

export async function cancelarInscripcion(user, claseId) {
  const conn = await pool.connect();
  try {
    await conn.query('BEGIN');

    const eliminadas = await repo.eliminarInscripcion(claseId, user.id, conn);
    if (eliminadas > 0) {
      await repo.liberarCupo(claseId, conn);
    }

    await conn.query('COMMIT');
    return { clase_id: claseId, cancelada: eliminadas > 0 };
  } catch (err) {
    try { await conn.query('ROLLBACK'); } catch (_) {}
    throw err;
  } finally {
    conn.release();
  }
}

export async function misInscripciones(user) {
  return repo.listarInscripcionesDeUsuario(user.id);
}

// --- helpers ---

async function verificarPropiedad(user, clase) {
  if (user.rol === 'ADMIN') return;
  const gimnasio_id = await resolverGimnasioId(user);
  if (String(clase.gimnasio_id) !== String(gimnasio_id)) {
    const err = new Error('No tenés permiso sobre esta clase');
    err.status = 403;
    throw err;
  }
}

function formatFecha(fecha) {
  // El driver puede devolver un Date; normalizamos a YYYY-MM-DD.
  if (fecha instanceof Date) return fecha.toISOString().slice(0, 10);
  return String(fecha).slice(0, 10);
}

function badRequest(msg) { const e = new Error(msg); e.status = 400; return e; }
function notFound(msg)   { const e = new Error(msg); e.status = 404; return e; }
function conflict(msg)   { const e = new Error(msg); e.status = 409; return e; }
