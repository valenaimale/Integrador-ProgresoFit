import * as ejercicioService from '../services/ejercicioService.js';

export async function getAll(req, res) {
  try {
    const limit  = parseInt(req.query.limit)  || 20;
    const offset = parseInt(req.query.offset) || 0;
    const result = await ejercicioService.listarEjercicios({ limit, offset });
    res.json(result);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
}

export async function getById(req, res) {
  try {
    const ejercicio = await ejercicioService.obtenerEjercicioExterno(req.params.id);
    res.json(ejercicio);
  } catch (error) {
    res.status(404).json({ error: error.message });
  }
}
