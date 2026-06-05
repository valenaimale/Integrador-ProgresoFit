import * as entrenadorService from '../services/entrenadorService.js';
import * as entrenadorRepository from '../repositories/entrenadorRepository.js';

export async function getAll(req, res) {
  try {
    const entrenadores = await entrenadorService.listarEntrenadores();
    res.json(entrenadores);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
}

export async function getById(req, res) {
  try {
    const entrenador = await entrenadorService.obtenerEntrenador(req.params.id);
    res.json(entrenador);
  } catch (error) {
    res.status(404).json({ error: error.message });
  }
}

export async function suscribirse(req, res) {
  try {
    if (req.user.rol !== 'ALUMNO') {
      return res.status(403).json({ error: 'Solo los alumnos pueden suscribirse a un entrenador' });
    }
    await entrenadorRepository.suscribir(req.params.id, req.user.id);
    res.json({ message: 'Suscripción exitosa' });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}

export async function desuscribirse(req, res) {
  try {
    await entrenadorRepository.desuscribir(req.params.id, req.user.id);
    res.json({ message: 'Suscripción cancelada' });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}

export async function upsertPerfil(req, res) {
  try {
    const { especialidad, descripcion, horario, gimnasio_id } = req.body;
    const perfil = await entrenadorService.actualizarPerfil(
      req.user,
      req.params.id,
      { especialidad, descripcion, horario, gimnasio_id }
    );
    res.json(perfil);
  } catch (error) {
    res.status(403).json({ error: error.message });
  }
}

export async function registrar(req, res) {
  try {
    const {nombre, email, password, horario, descripcion, especialidad} = req.body;
    const user = await entrenadorService.registrarEntrenador({nombre, email, password, horario, descripcion, especialidad});
    res.status(201).json({ message: 'Entrenador registrado exitosamente', user });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}