import { Router } from 'express';
import * as mercadopagoController from '../controllers/mercadopago.controller.js';
import { authenticateToken } from '../middleware/authMiddleware.js';
import { webhookAuth } from '../middleware/webhookAuth.js';

const router = Router();

// Crear preferencia de pago — requiere JWT de alumno autenticado
router.post('/preference', authenticateToken, mercadopagoController.createPreference);

// Webhook de Mercado Pago — NO requiere JWT, se protege con validación HMAC
// La ruta necesita express.raw() para preservar el body crudo (configurado en server.js)
router.post('/webhook', webhookAuth, mercadopagoController.handleWebhook);

export default router;
