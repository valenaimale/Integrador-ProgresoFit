import { Router } from 'express';
import * as suscripcionController from '../controllers/suscripcionController.js';
import { authenticateToken, checkRole } from '../middleware/authMiddleware.js';

const router = Router();

// Available plans — any authenticated user can view them
router.get('/planes', authenticateToken, suscripcionController.getPlanes);

// Active subscription for the logged-in student
router.get('/mi-suscripcion', authenticateToken, checkRole(['ALUMNO']), suscripcionController.getMiSuscripcion);

// Subscribe to a plan (students only)
router.post('/', authenticateToken, checkRole(['ALUMNO']), suscripcionController.suscribirse);

export default router;
