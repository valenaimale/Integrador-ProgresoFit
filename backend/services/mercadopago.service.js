import 'dotenv/config';
import crypto from 'node:crypto';
import { MercadoPagoConfig, Preference, Payment } from 'mercadopago';

const client = new MercadoPagoConfig({ accessToken: process.env.MP_ACCESS_TOKEN });

export async function createPreference({ suscripcionId, monto, titular, email, descripcion }) {
  const body = {
    items: [
      {
        title: descripcion,
        quantity: 1,
        unit_price: Number(monto),
        currency_id: 'ARS',
      },
    ],
    payer: {
      name: titular,
      email: email,
    },
    back_urls: {
      success: process.env.MP_SUCCESS_URL,
      failure: process.env.MP_FAILURE_URL,
      pending: process.env.MP_PENDING_URL,
    },
    external_reference: String(suscripcionId),
  };

  if (process.env.MP_NOTIFICATION_URL) {
    body.notification_url = process.env.MP_NOTIFICATION_URL;
  }

  if (process.env.MP_SUCCESS_URL && !process.env.MP_SUCCESS_URL.includes('localhost')) {
    body.auto_return = 'approved';
  }
  const preference = new Preference(client);
  const result = await preference.create({ body });

  return {
    preferenceId: result.id,
    initPoint: result.sandbox_init_point || result.init_point,
    expiresAt:
      result.date_of_expiration || new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString(),
  };
}

export async function getPayment(paymentId) {
  const payment = new Payment(client);
  const result = await payment.get({ id: paymentId });

  return {
    status: result.status,
    statusDetail: result.status_detail,
    externalReference: result.external_reference,
  };
}

export function validateWebhookSignature(xSignature, xRequestId, dataId) {
  if (!process.env.MP_WEBHOOK_SECRET) {
    return true;
  }

  if (!xSignature) return false;

  const parts = {};
  xSignature.split(',').forEach(part => {
    const [key, value] = part.split('=');
    parts[key.trim()] = value.trim();
  });

  const ts = parts['ts'];
  const receivedHash = parts['v1'];

  if (!ts || !receivedHash) return false;

  const manifest = `id:${dataId};request-id:${xRequestId};ts:${ts};`;
  const computedHash = crypto.createHmac('sha256', process.env.MP_WEBHOOK_SECRET)
    .update(manifest)
    .digest('hex');

  try {
    return crypto.timingSafeEqual(
      Buffer.from(computedHash, 'hex'),
      Buffer.from(receivedHash, 'hex')
    );
  } catch {
    return false;
  }
}
