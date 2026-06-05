import { Router } from 'express';
import * as accesosController from '../controllers/accesosController.js';
import { authenticateToken, checkRole } from '../middleware/authMiddleware.js';

const router = Router();

// ALUMNO: genera/refresca su QR personal
router.post('/mi-qr',    authenticateToken, checkRole(['ALUMNO']),         accesosController.generarQr);

// ALUMNO: historial de sus propios accesos
router.get('/historial', authenticateToken, checkRole(['ALUMNO']),         accesosController.getHistorial);

// ADMIN: escanea un QR para registrar entrada/salida
router.post('/escanear', authenticateToken, checkRole(['ADMIN']),          accesosController.escanear);

// ADMIN/ENTRENADOR/ALUMNO: consulta aforo actual de un gimnasio
router.get('/aforo/:gimnasioId', authenticateToken, checkRole(['ADMIN', 'ENTRENADOR', 'ALUMNO']), accesosController.getAforo);

export default router;
