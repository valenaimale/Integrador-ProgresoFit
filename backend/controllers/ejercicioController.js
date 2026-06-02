import * as ejercicioService from '../services/ejercicioService.js';
import * as ejercicioRepository from '../repositories/ejercicioRepository.js';

export async function getAll(req, res) {
  try {
    const limit  = parseInt(req.query.limit)  || 20;
    const offset = parseInt(req.query.offset) || 0;
    const q      = req.query.q || '';
    const result = await ejercicioService.listarEjercicios({ limit, offset, q });
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

export async function getLocalById(req, res) {
  try {
    const ejercicio = await ejercicioRepository.findById(req.params.id);
    if (!ejercicio) return res.status(404).json({ error: 'Ejercicio no encontrado' });
    res.json(ejercicio);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
}
