import bcrypt from 'bcrypt';
import * as usuarioRepository from '../repositories/usuarioRepository.js';

const SALT_ROUNDS = 10;

export async function createEntrenador(adminUser, { nombre, email, password }) {
  if (adminUser.rol !== 'ADMIN') {
    throw new Error('Solo un ADMIN puede crear entrenadores');
  }
  if (!email || !password) throw new Error('Email y password son obligatorios');

  const existing = await usuarioRepository.findByEmail(email);
  if (existing) throw new Error('El correo ya está registrado');

  const hashed = await bcrypt.hash(password, SALT_ROUNDS);
  return await usuarioRepository.create({ nombre, email, password: hashed, rol: 'ENTRENADOR' });
}

export async function createAlumno({ nombre, email, password }) {
  if (!email || !password) throw new Error('Email y password son obligatorios');

  const existing = await usuarioRepository.findByEmail(email);
  if (existing) throw new Error('El correo ya está registrado');

  const hashed = await bcrypt.hash(password, SALT_ROUNDS);
  return await usuarioRepository.create({ nombre, email, password: hashed, rol: 'ALUMNO' });
}

export async function updateProfile(requestingUser, targetId, { nombre, email, password, contra_actual }) {
  // solo el propio usuario puede editar su perfil; el admin puede editar cualquiera
  if (requestingUser.rol !== 'ADMIN' && requestingUser.id !== Number(targetId)) {
    throw new Error('Solo podés editar tu propio perfil');
  }

  const fields = {};
  if (nombre) fields.nombre = nombre;
  if (email)  fields.email  = email;

  // si viene una nueva contraseña, verificar que la actual sea correcta antes de cambiarla
  if (password) {
    // obtener el hash actual de la BD
    const hashActual = await usuarioRepository.findPasswordById(targetId);

    // comparar la contraseña ingresada con el hash almacenado
    const coincide = await bcrypt.compare(contra_actual ?? '', hashActual);

    // si no coincide, rechazar — no se actualiza nada
    if (!coincide) throw new Error('La contraseña actual es incorrecta');

    // si coincide, hashear la nueva y agregarla a los campos a actualizar
    fields.password = await bcrypt.hash(password, SALT_ROUNDS);
  }

  await usuarioRepository.updateProfile(targetId, fields);
  return await usuarioRepository.findById(targetId);
}

export async function getProfile(id) {
  const user = await usuarioRepository.findById(id);
  if (!user) throw new Error('Usuario no encontrado');
  return user;
}
