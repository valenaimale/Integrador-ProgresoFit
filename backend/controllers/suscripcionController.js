import * as suscripcionService from '../services/suscripcionService.js';

export async function getPlanes(req, res) {
  res.json(suscripcionService.listarPlanes());
}

export async function getMiSuscripcion(req, res) {
  try {
    const suscripcion = await suscripcionService.miSuscripcion(req.user.id);
    res.json(suscripcion || null);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
}

export async function suscribirse(req, res) {
  try {
    const { plan } = req.body;
    if (!plan) return res.status(400).json({ error: 'El campo plan es obligatorio' });

    const suscripcion = await suscripcionService.suscribirse(req.user.id, plan);
    res.status(201).json(suscripcion);
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}
