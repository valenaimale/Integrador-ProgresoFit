import { Router } from 'express';
import * as ejercicioController from '../controllers/ejercicioController.js';
import { authenticateToken } from '../middleware/authMiddleware.js';

const router = Router();

// All authenticated users can browse the exercise catalog from the external API
router.get('/',    authenticateToken, ejercicioController.getAll);
router.get('/:id', authenticateToken, ejercicioController.getById);

export default router;
