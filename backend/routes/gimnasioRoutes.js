import { Router } from 'express';
import * as gimnasioController from '../controllers/gimnasioController.js';
import { authenticateToken, checkRole } from '../middleware/authMiddleware.js';

const router = Router();//mini router de la clase Express de Node.js

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

//IMPLEMENTACION PARA ALTA DE GIMNASIO LIBRE (PUEDE CREARLO CUALQUIERA)
router.post('/registrar', gimnasioController.registrar);
export default router;
//cada metodo de router recibe la ruta relativa, un middleware (una funcion opcional que se 
//ejecuta antes del handler) y el handler (la funcion del controlador que maneja la request)