import * as suscripcionRepository from '../repositories/suscripcionRepository.js';

const PLANES = {
  basico:  { precio: 2999, duracion_dias: 30 },
  premium: { precio: 4999, duracion_dias: 30 },
  elite:   { precio: 7999, duracion_dias: 30 }
};

export function listarPlanes() {
  return Object.entries(PLANES).map(([nombre, datos]) => ({ nombre, ...datos }));
}

export async function miSuscripcion(usuarioId) {
  return await suscripcionRepository.findActiveByUsuario(usuarioId);
}

export async function suscribirse(usuarioId, plan) {
  const planData = PLANES[plan];
  if (!planData) {
    throw new Error(`Plan inválido. Opciones disponibles: ${Object.keys(PLANES).join(', ')}`);
  }

  const hoy = new Date();
  const fechaFin = new Date(hoy);
  fechaFin.setDate(fechaFin.getDate() + planData.duracion_dias);

  const formatDate = (d) => d.toISOString().slice(0, 10);

  return await suscripcionRepository.create({
    usuario_id: usuarioId,
    plan,
    precio: planData.precio,
    fecha_inicio: formatDate(hoy),
    fecha_fin: formatDate(fechaFin)
  });
}
