import { Router } from 'express';
import * as usuarioController from '../controllers/usuarioController.js';
import { authenticateToken } from '../middleware/authMiddleware.js';

const router = Router();

// Solo ADMIN puede crear entrenadores
router.post('/entrenador', authenticateToken, usuarioController.createEntrenador);

// Cualquiera puede registrarse como alumno
router.post('/alumno', usuarioController.createAlumno);

// Entrenadores pueden listar sus propios alumnos
router.get('/mis-alumnos',      authenticateToken, usuarioController.getMisAlumnos);
// Alumnos pueden ver a qué entrenadores están suscriptos
router.get('/mis-entrenadores', authenticateToken, usuarioController.getMisEntrenadores);

// Cualquier logueado puede ver un perfil
router.get('/:id',  authenticateToken, usuarioController.getProfile);

// Users can edit their own profile (admins can edit any profile)
router.put('/:id',  authenticateToken, usuarioController.updateProfile);

export default router;
