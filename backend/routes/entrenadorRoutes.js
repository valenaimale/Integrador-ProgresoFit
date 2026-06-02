import { Router } from 'express';
import * as entrenadorController from '../controllers/entrenadorController.js';
import { authenticateToken, checkRole } from '../middleware/authMiddleware.js';

const router = Router();

// Any logged-in user can view trainer profiles
router.get('/',    authenticateToken, entrenadorController.getAll);

// List of all students (for trainers and admins to pick for assignments)
router.get('/alumnos', authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), entrenadorController.getAlumnos);

// List of students assigned to the logged-in trainer
router.get('/mis-alumnos', authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), entrenadorController.getMisAlumnos);

router.get('/:id', authenticateToken, entrenadorController.getById);

// Trainers update their own profile; admins can update any trainer's profile
router.put('/:id', authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), entrenadorController.upsertPerfil);

export default router;
