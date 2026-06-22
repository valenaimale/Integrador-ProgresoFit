import { validateWebhookSignature } from '../services/mercadopago.service.js';

export function webhookAuth(req, res, next) {
  try {
    const xSignature = req.headers['x-signature'];
    const xRequestId = req.headers['x-request-id'];

    let body;
    if (typeof req.body === 'string') {
      body = JSON.parse(req.body);
    } else if (Buffer.isBuffer(req.body)) {
      body = JSON.parse(req.body.toString('utf-8'));
    } else {
      body = req.body;
    }

    const dataId = body?.data?.id;

    const isValid = validateWebhookSignature(xSignature, xRequestId, dataId);

    if (!isValid) {
      return res.status(401).json({ error: 'Invalid webhook signature' });
    }

    req.webhookBody = body;
    next();
  } catch (error) {
    console.error('Webhook auth error:', error.message);
    return res.status(401).json({ error: 'Webhook authentication failed' });
  }
}
