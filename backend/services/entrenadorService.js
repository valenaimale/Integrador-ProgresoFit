import * as entrenadorRepository from '../repositories/entrenadorRepository.js';
import * as usuarioRepository from '../repositories/usuarioRepository.js';
import bcrypt from 'bcrypt';

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
export async function registrarEntrenador({nombre, email, password, horario, descripcion, especialidad}){

  if (!email || !password) throw new Error('Email y password son obligatorios');
      
      const existing = await usuarioRepository.findByEmail(email);
      if (existing) throw new Error('El correo ya está registrado');
      
      const hashed = await bcrypt.hash(password, 10);
      const usuario = await usuarioRepository.create({ nombre, email, password: hashed, rol: 'ENTRENADOR' });
      const entrenador = await entrenadorRepository.create({usuario_id: usuario.id, nombre, horario, email, descripcion, especialidad }); 
      return entrenador;
}
