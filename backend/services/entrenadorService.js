import * as entrenadorRepository from '../repositories/entrenadorRepository.js';

export async function listarEntrenadores() {
  return await entrenadorRepository.findAll();
}

export async function obtenerEntrenador(usuarioId) {
  const entrenador = await entrenadorRepository.findByUsuarioId(usuarioId);
  if (!entrenador) throw new Error('Entrenador no encontrado');
  return entrenador;
}

export async function actualizarPerfil(user, usuarioId, data) {
  // Trainers update their own profile; admins can update any trainer's profile
  if (user.rol === 'ENTRENADOR' && user.id !== Number(usuarioId)) {
    throw new Error('Solo podés editar tu propio perfil');
  }
  if (user.rol === 'ALUMNO') {
    throw new Error('No tenés permiso para editar perfiles de entrenadores');
  }

  await entrenadorRepository.upsertPerfil(usuarioId, data);
  return await entrenadorRepository.findByUsuarioId(usuarioId);
}

export async function listarAlumnos() {
  const { findAllByRol } = await import('../repositories/usuarioRepository.js');
  return await findAllByRol('ALUMNO');
}

export async function listarMisAlumnos(entrenadorId) {
  return await entrenadorRepository.findAlumnosByEntrenador(entrenadorId);
}
