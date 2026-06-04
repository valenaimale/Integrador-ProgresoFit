import { Router } from 'express';
import * as entrenadorController from '../controllers/entrenadorController.js';
import { authenticateToken, checkRole } from '../middleware/authMiddleware.js';

const router = Router();

// Any logged-in user can view trainer profiles
router.get('/',    authenticateToken, entrenadorController.getAll);
router.get('/:id', authenticateToken, entrenadorController.getById);

// Alumnos can subscribe/unsubscribe to a trainer
router.post('/:id/suscribirse',   authenticateToken, entrenadorController.suscribirse);
router.delete('/:id/suscribirse', authenticateToken, entrenadorController.desuscribirse);

// Registrar un entrenador
router.post('/registrar', entrenadorController.registrar);

// Trainers update their own profile; admins can update any trainer's profile
router.put('/:id', authenticateToken, checkRole(['ENTRENADOR', 'ADMIN']), entrenadorController.upsertPerfil);

export default router;
