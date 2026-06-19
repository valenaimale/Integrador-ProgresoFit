import * as claseService from '../services/claseService.js';

export async function crear(req, res) {
  try {
    const result = await claseService.crearClase(req.user, req.body);
    res.status(201).json(result);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}

export async function misClases(req, res) {
  try {
    const rows = await claseService.listarMisClases(req.user);
    res.json(rows);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}

export async function disponibles(req, res) {
  try {
    const rows = await claseService.listarDisponibles();
    res.json(rows);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}

export async function cancelarClase(req, res) {
  try {
    const result = await claseService.cancelarClase(req.user, req.params.id);
    res.json(result);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}

export async function inscriptos(req, res) {
  try {
    const rows = await claseService.listarInscriptos(req.user, req.params.id);
    res.json(rows);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}

export async function inscribir(req, res) {
  try {
    const result = await claseService.inscribir(req.user, req.params.id);
    res.json(result);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}

export async function cancelarInscripcion(req, res) {
  try {
    const result = await claseService.cancelarInscripcion(req.user, req.params.id);
    res.json(result);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}

export async function misInscripciones(req, res) {
  try {
    const rows = await claseService.misInscripciones(req.user);
    res.json(rows);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}
