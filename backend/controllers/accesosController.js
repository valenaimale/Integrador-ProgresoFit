import * as accesosService from '../services/accesosService.js';

export async function generarQr(req, res) {
  try {
    const result = await accesosService.generarQr(req.user.id);
    res.json(result);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}

export async function escanear(req, res) {
  try {
    const { token, gimnasio_id } = req.body;
    if (!token || !gimnasio_id) {
      return res.status(400).json({ error: 'Faltan campos: token y gimnasio_id son requeridos' });
    }
    const result = await accesosService.registrarAcceso(token, gimnasio_id);
    res.json(result);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}

export async function getAforo(req, res) {
  try {
    const result = await accesosService.getAforo(req.params.gimnasioId);
    res.json(result);
  } catch (error) {
    res.status(error.status || 500).json({ error: error.message });
  }
}

export async function getHistorial(req, res) {
  try {
    const rows = await accesosService.getHistorial(req.user.id);
    res.json(rows);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
}
