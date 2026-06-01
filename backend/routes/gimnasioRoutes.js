import { Router } from 'express';
import * as gimnasioController from '../controllers/gimnasioController.js';
import { authenticateToken, checkRole } from '../middleware/authMiddleware.js';

const router = Router();

// Any logged-in user can list and view detail
router.get('/', authenticateToken, gimnasioController.getAll);
router.get('/me', authenticateToken, checkRole(['GIMNASIO']), gimnasioController.getMe);
router.get('/:id', authenticateToken, gimnasioController.getById);

// Gym account manages its own profile
router.put('/me', authenticateToken, checkRole(['GIMNASIO']), gimnasioController.updateMe);

// ADMIN-only: create, update any gym, delete
router.post('/', authenticateToken, checkRole(['ADMIN']), gimnasioController.create);
router.put('/:id', authenticateToken, checkRole(['ADMIN']), gimnasioController.update);
router.delete('/:id', authenticateToken, checkRole(['ADMIN']), gimnasioController.remove);

export default router;
