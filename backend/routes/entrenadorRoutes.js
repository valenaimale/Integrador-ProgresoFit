import { Router } from 'express';
import * as entrenadorController from '../controllers/entrenadorController.js';
import { authenticateToken, checkRole } from '../middleware/authMiddleware.js';

const router = Router();

// Any logged-in user can view trainer profiles
router.get('/',    authenticateToken, entrenadorController.getAll);
router.get('/:id', authenticateToken, entrenadorController.getById);

// Trainers update their own profile; admins can update any trainer's profile
router.put('/:id', authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), entrenadorController.upsertPerfil);

export default router;
