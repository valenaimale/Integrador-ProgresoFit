import * as rutinaRepository from '../repositories/rutinaRepository.js';
import * as ejercicioRepository from '../repositories/ejercicioRepository.js';
import * as ejercicioService from './ejercicioService.js';

export async function listarRutinas(user) {
  if (user.rol === 'ALUMNO') {
    return await rutinaRepository.findByAlumno(user.id);
  }
  return await rutinaRepository.findAll();
}

export async function obtenerRutina(id) {
  const rutina = await rutinaRepository.findById(id);
  if (!rutina) throw new Error('Rutina no encontrada');
  return rutina;
}

export async function crearRutina(user, { titulo, descripcion, objetivo }) {
  if (!['ENTRENADOR', 'ADMIN'].includes(user.rol)) {
    throw new Error('Solo entrenadores y admins pueden crear rutinas');
  }
  if (!titulo) throw new Error('El título es obligatorio');
  return await rutinaRepository.create({ titulo, descripcion, objetivo, entrenador_id: user.id });
}

export async function editarRutina(user, id, data) {
  if (user.rol === 'ALUMNO') throw new Error('No tenés permiso para editar rutinas');

  if (user.rol === 'ENTRENADOR') {
    const esOwner = await rutinaRepository.isOwner(id, user.id);
    if (!esOwner) throw new Error('Solo podés editar tus propias rutinas');
  }

  await rutinaRepository.update(id, data);
}

export async function eliminarRutina(user, id) {
  if (user.rol === 'ALUMNO') throw new Error('No tenés permiso para eliminar rutinas');

  if (user.rol === 'ENTRENADOR') {
    const esOwner = await rutinaRepository.isOwner(id, user.id);
    if (!esOwner) throw new Error('Solo podés eliminar tus propias rutinas');
  }

  await rutinaRepository.remove(id);
}

// --- Training days ---

export async function agregarDia(user, rutinaId, diaData) {
  if (user.rol === 'ALUMNO') throw new Error('No tenés permiso');

  if (user.rol === 'ENTRENADOR') {
    const esOwner = await rutinaRepository.isOwner(rutinaId, user.id);
    if (!esOwner) throw new Error('Solo podés modificar tus propias rutinas');
  }

  if (!diaData.nombre_dia) throw new Error('El nombre del día es obligatorio');
  return await rutinaRepository.addDia(rutinaId, diaData);
}

export async function eliminarDia(user, rutinaId, diaId) {
  if (user.rol === 'ALUMNO') throw new Error('No tenés permiso');

  if (user.rol === 'ENTRENADOR') {
    const esOwner = await rutinaRepository.isOwner(rutinaId, user.id);
    if (!esOwner) throw new Error('Solo podés modificar tus propias rutinas');
  }

  const pertenece = await rutinaRepository.diaPertenece(diaId, rutinaId);
  if (!pertenece) throw new Error('El día no pertenece a esta rutina');

  await rutinaRepository.removeDia(diaId);
}

// --- Exercises within a day ---

export async function agregarEjercicioADia(user, rutinaId, diaId, { api_ejercicio_id, series_repeticiones, orden }) {
  if (user.rol === 'ALUMNO') throw new Error('No tenés permiso');

  if (user.rol === 'ENTRENADOR') {
    const esOwner = await rutinaRepository.isOwner(rutinaId, user.id);
    if (!esOwner) throw new Error('Solo podés modificar tus propias rutinas');
  }

  const pertenece = await rutinaRepository.diaPertenece(diaId, rutinaId);
  if (!pertenece) throw new Error('El día no pertenece a esta rutina');

  if (!api_ejercicio_id) throw new Error('api_ejercicio_id es obligatorio');

  // Lazy-cache: save exercise locally if not yet stored
  let ejercicio = await ejercicioRepository.findByApiId(api_ejercicio_id);
  if (!ejercicio) {
    const apiData = await ejercicioService.obtenerEjercicioExterno(api_ejercicio_id);
    ejercicio = await ejercicioRepository.saveFromApi(apiData);
  }

  return await rutinaRepository.addEjercicioToDia(diaId, { ejercicio_id: ejercicio.id, series_repeticiones, orden });
}

export async function eliminarEjercicioDeDia(user, rutinaId, diaId, pivotId) {
  if (user.rol === 'ALUMNO') throw new Error('No tenés permiso');

  if (user.rol === 'ENTRENADOR') {
    const esOwner = await rutinaRepository.isOwner(rutinaId, user.id);
    if (!esOwner) throw new Error('Solo podés modificar tus propias rutinas');
  }

  await rutinaRepository.removeEjercicioFromDia(pivotId);
}

// --- Assignment ---

export async function asignarRutina(user, rutinaId, alumnoId) {
  if (!['ENTRENADOR', 'ADMIN'].includes(user.rol)) {
    throw new Error('Solo entrenadores y admins pueden asignar rutinas');
  }
  if (!alumnoId) throw new Error('alumno_id es obligatorio');

  const assigned = await rutinaRepository.asignarAlumno(rutinaId, alumnoId, user.id);
  if (!assigned) throw new Error('La rutina ya estaba asignada a este alumno');
  return { message: 'Rutina asignada exitosamente' };
}

export async function desasignarRutina(user, rutinaId, alumnoId) {
  if (!['ENTRENADOR', 'ADMIN'].includes(user.rol)) {
    throw new Error('Solo entrenadores y admins pueden desasignar rutinas');
  }
  await rutinaRepository.desasignarAlumno(rutinaId, alumnoId);
}
