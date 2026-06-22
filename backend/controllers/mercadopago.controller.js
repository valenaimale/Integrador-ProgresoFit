import * as mercadopagoService from '../services/mercadopago.service.js';
import * as pagoRepository from '../repositories/pago.repository.js';
import * as suscripcionRepository from '../repositories/suscripcionRepository.js';
import * as suscripcionService from '../services/suscripcionService.js';

export async function createPreference(req, res) {
  try {
    const { suscripcion_id, monto, titular, email, descripcion } = req.body;

    if (!suscripcion_id) {
      return res.status(400).json({
        error: 'validation_error',
        message: 'suscripcion_id es requerido',
      });
    }

    if (!monto || Number(monto) <= 0) {
      return res.status(400).json({
        error: 'validation_error',
        message: 'Monto debe ser > 0',
      });
    }

    const suscripcion = await suscripcionRepository.findById(suscripcion_id);
    if (!suscripcion) {
      return res.status(404).json({
        error: 'not_found',
        message: 'Suscripción no encontrada',
      });
    }

    let preference;
    try {
      preference = await mercadopagoService.createPreference({
        suscripcionId: suscripcion_id,
        monto: Number(monto),
        titular: titular || '',
        email: email || '',
        descripcion: descripcion || 'ProgresoFit - Suscripción',
      });
    } catch (mpError) {
      console.error('[MercadoPago] Error al crear preferencia:', mpError);
      return res.status(502).json({
        error: 'payment_gateway_error',
        message: 'MP no disponible',
      });
    }

    const pago = await pagoRepository.create({
      suscripcion_id,
      monto: Number(monto),
      mp_preference_id: preference.preferenceId,
    });

    return res.status(200).json({
      preference_id: preference.preferenceId,
      init_point: preference.initPoint,
      expires_at: preference.expiresAt,
    });
  } catch (error) {
    console.error('[MercadoPago] createPreference error:', error);
    return res.status(500).json({ error: 'internal_error', message: error.message });
  }
}

export async function handleWebhook(req, res) {
  const body = req.webhookBody || req.body;

  if (body.type !== 'payment') {
    return res.status(200).json({ status: 'ignored', reason: 'not a payment notification' });
  }

  const paymentId = body?.data?.id;

  if (!paymentId) {
    return res.status(200).json({
      error: 'bad_request',
      message: 'Missing data.id',
    });
  }

  try {
    const existingPago = await pagoRepository.findByPaymentId(paymentId);
    if (existingPago) {
      return res.status(200).json({
        status: 'already_processed',
        payment_id: paymentId,
        pago_id: existingPago.id,
      });
    }

    let mpPayment;
    try {
      mpPayment = await mercadopagoService.getPayment(paymentId);
    } catch (mpError) {
      console.error('[MercadoPago] Error consultando MP API:', mpError);
      return res.status(200).json({
        status: 'retry_later',
        payment_id: paymentId,
      });
    }

    const { status, statusDetail, externalReference } = mpPayment;
    const suscripcionId = externalReference;

    if (!suscripcionId) {
      console.warn('[MercadoPago] Webhook sin external_reference:', paymentId);
      return res.status(200).json({
        status: 'ignored',
        payment_id: paymentId,
      });
    }

    const pagos = await pagoRepository.findBySuscripcion(suscripcionId);
    const pago = pagos.find(p => p.estado === 'pendiente' && !p.mp_payment_id);

    if (!pago) {
      console.warn('[MercadoPago] No hay pago pendiente para la suscripción:', suscripcionId);
      return res.status(200).json({
        status: 'ignored',
        payment_id: paymentId,
      });
    }

    const updateFields = {
      mp_payment_id: String(paymentId),
      mp_status: status,
      mp_status_detail: statusDetail,
    };

    switch (status) {
      case 'approved':
        updateFields.estado = 'aprobado';
        await pagoRepository.updateStatus(pago.id, updateFields);
        await suscripcionService.activarPorPago(pago.suscripcion_id);
        break;

      case 'rejected':
        updateFields.estado = 'rechazado';
        await pagoRepository.updateStatus(pago.id, updateFields);
        break;

      case 'cancelled':
        updateFields.estado = 'cancelado';
        await pagoRepository.updateStatus(pago.id, updateFields);
        break;

      case 'expired':
        updateFields.estado = 'expirado';
        await pagoRepository.updateStatus(pago.id, updateFields);
        break;

      default:
        await pagoRepository.updateStatus(pago.id, updateFields);
        break;
    }

    return res.status(200).json({
      status: 'processed',
      payment_id: paymentId,
      pago_id: pago.id,
    });
  } catch (error) {
    console.error('[MercadoPago] handleWebhook error:', error);
    return res.status(200).json({
      status: 'error',
      message: 'Internal error processing webhook',
    });
  }
}
