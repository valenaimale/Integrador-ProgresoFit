import * as entrenadorService from '../services/entrenadorService.js';

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

export async function getAlumnos(req, res) {
  try {
    const alumnos = await entrenadorService.listarAlumnos();
    res.json(alumnos);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
}

export async function getMisAlumnos(req, res) {
  try {
    // Si el usuario es ENTRENADOR, listamos sus alumnos asignados
    // Si es ADMIN, tal vez queremos pasarle un ID por params, pero por ahora simplificamos
    const entrenadorId = req.user.rol === 'ENTRENADOR' ? req.user.id : req.params.id;
    const alumnos = await entrenadorService.listarMisAlumnos(entrenadorId);
    res.json(alumnos);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
}
