import { Router } from 'express';
import * as rutinaController from '../controllers/rutinaController.js';
import { authenticateToken, checkRole } from '../middleware/authMiddleware.js';

const router = Router();

// All authenticated users can list and view routines
// (the service layer filters results by role: students see only assigned routines)
router.get('/',    authenticateToken, rutinaController.getAll);
router.get('/:id', authenticateToken, rutinaController.getById);

// Only trainers and admins can create/edit/delete routines
router.post('/',    authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), rutinaController.create);
router.put('/:id',  authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), rutinaController.update);
router.delete('/:id', authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), rutinaController.remove);

// Training days management
router.post('/:id/dias',                       authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), rutinaController.addDia);
router.delete('/:rutinaId/dias/:diaId',        authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), rutinaController.removeDia);

// Exercises within a day
router.post('/:rutinaId/dias/:diaId/ejercicios',                  authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), rutinaController.addEjercicio);
router.delete('/:rutinaId/dias/:diaId/ejercicios/:pivotId',       authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), rutinaController.removeEjercicio);

// Routine assignment
router.post('/:id/asignar',                    authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), rutinaController.asignar);
router.delete('/:rutinaId/asignar/:alumnoId',  authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), rutinaController.desasignar);

export default router;
