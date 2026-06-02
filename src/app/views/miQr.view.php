<!DOCTYPE html>
<html lang="es">

<head>
    <title>Mi QR de acceso - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/miQr.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <div class="qr-container">
            <h2>Mi QR de acceso al gimnasio</h2>

            <?php if (!empty($error)): ?>
                <div class="qr-error">
                    <p><?= htmlspecialchars($error) ?></p>
                    <?php if (str_contains($error, 'suscripción')): ?>
                        <a href="/pagos-suscripcion" class="btn-primario">Ver planes de suscripción</a>
                    <?php endif; ?>
                </div>
            <?php elseif (!empty($qrData)): ?>
                <div class="qr-card">
                    <p class="qr-instruccion">Mostrá este código al encargado del gimnasio para registrar tu entrada o salida.</p>

                    <div class="qr-imagen">
                        <img src="<?= htmlspecialchars($qrData['qr_image']) ?>"
                             alt="Código QR de acceso"
                             width="300" height="300">
                    </div>

                    <div class="qr-info">
                        <p class="qr-aviso">Este QR es de <strong>un solo uso</strong>. Una vez escaneado, deberás generar uno nuevo.</p>
                        <p class="qr-expira">
                            Válido hasta:
                            <strong id="expira-en">
                                <?= date('H:i', strtotime($qrData['expires_at'])) ?> hs
                            </strong>
                        </p>
                    </div>

                    <form method="POST" action="/mi-qr/regenerar" class="qr-regenerar">
                        <button type="submit" class="btn-secundario">Generar nuevo QR</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
