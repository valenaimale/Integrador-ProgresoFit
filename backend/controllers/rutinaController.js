import * as rutinaService from '../services/rutinaService.js';

export async function getAll(req, res) {
  try {
    const rutinas = await rutinaService.listarRutinas(req.user);
    res.json(rutinas);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
}

export async function getById(req, res) {
  try {
    const rutina = await rutinaService.obtenerRutina(req.params.id);
    res.json(rutina);
  } catch (error) {
    res.status(404).json({ error: error.message });
  }
}

export async function create(req, res) {
  try {
    const { titulo, descripcion, objetivo } = req.body;
    const rutina = await rutinaService.crearRutina(req.user, { titulo, descripcion, objetivo });
    res.status(201).json(rutina);
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}

export async function update(req, res) {
  try {
    const { titulo, descripcion, objetivo } = req.body;
    await rutinaService.editarRutina(req.user, req.params.id, { titulo, descripcion, objetivo });
    res.json({ message: 'Rutina actualizada' });
  } catch (error) {
    res.status(403).json({ error: error.message });
  }
}

export async function remove(req, res) {
  try {
    await rutinaService.eliminarRutina(req.user, req.params.id);
    res.json({ message: 'Rutina eliminada' });
  } catch (error) {
    res.status(403).json({ error: error.message });
  }
}

// --- Training days ---

export async function addDia(req, res) {
  try {
    const dia = await rutinaService.agregarDia(req.user, req.params.id, req.body);
    res.status(201).json(dia);
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}

export async function removeDia(req, res) {
  try {
    await rutinaService.eliminarDia(req.user, req.params.rutinaId, req.params.diaId);
    res.json({ message: 'Día eliminado' });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}

// --- Exercises within a day ---

export async function addEjercicio(req, res) {
  try {
    const pivot = await rutinaService.agregarEjercicioADia(
      req.user,
      req.params.rutinaId,
      req.params.diaId,
      req.body
    );
    res.status(201).json(pivot);
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}

export async function removeEjercicio(req, res) {
  try {
    await rutinaService.eliminarEjercicioDeDia(
      req.user,
      req.params.rutinaId,
      req.params.diaId,
      req.params.pivotId
    );
    res.json({ message: 'Ejercicio removido del día' });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}

// --- Assignment ---

export async function asignar(req, res) {
  try {
    const result = await rutinaService.asignarRutina(req.user, req.params.id, req.body.alumno_id);
    res.status(201).json(result);
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}

export async function desasignar(req, res) {
  try {
    await rutinaService.desasignarRutina(req.user, req.params.rutinaId, req.params.alumnoId);
    res.json({ message: 'Asignación removida' });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}
