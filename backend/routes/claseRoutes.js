import { Router } from 'express';
import * as claseController from '../controllers/claseController.js';
import { authenticateToken, checkRole } from '../middleware/authMiddleware.js';

const router = Router();

// ALUMNO: explora clases disponibles (cualquier usuario logueado puede ver)
router.get('/disponibles',       authenticateToken,                                claseController.disponibles);

// ALUMNO: sus inscripciones
router.get('/mis-inscripciones', authenticateToken, checkRole(['ALUMNO']),         claseController.misInscripciones);

// ALUMNO: anotarse / cancelar inscripción
router.post('/:id/inscribir',    authenticateToken, checkRole(['ALUMNO']),         claseController.inscribir);
router.delete('/:id/inscribir',  authenticateToken, checkRole(['ALUMNO']),         claseController.cancelarInscripcion);

// GIMNASIO/ADMIN: gestión de clases
router.get('/mias',              authenticateToken, checkRole(['GIMNASIO', 'ADMIN']), claseController.misClases);
router.post('/',                 authenticateToken, checkRole(['GIMNASIO', 'ADMIN']), claseController.crear);
router.delete('/:id',            authenticateToken, checkRole(['GIMNASIO', 'ADMIN']), claseController.cancelarClase);
router.get('/:id/inscriptos',    authenticateToken, checkRole(['GIMNASIO', 'ADMIN']), claseController.inscriptos);

export default router;
