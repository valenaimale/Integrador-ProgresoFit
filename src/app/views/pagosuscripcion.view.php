<!DOCTYPE html>
<html lang="es">

<head>
    <title>Pagos y Suscripción</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/formularios.css">
    <link rel="stylesheet" href="/assets/css/perfil.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <h2>Seleccioná tu plan de suscripción</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <form action="/pago-suscripcion" method="post" class="pago-grid">
            <div class="planes-seleccion-container">
                <section class="planes-seleccion">
                    <?php foreach ($planes as $plan): ?>
                        <label class="card-plan">
                            <input type="radio" name="plan" value="<?= htmlspecialchars($plan['nombre']) ?>" required>
                            <div class="plan-info">
                                <h3><?= strtoupper(htmlspecialchars($plan['nombre'])) ?></h3>
                                <p class="precio">$<?= number_format($plan['precio'], 0, ',', '.') ?> / mes</p>
                                <ul>
                                    <li>Acceso ilimitado a rutinas</li>
                                    <li>Seguimiento de progreso</li>
                                    <?php if ($plan['nombre'] === 'premium'): ?>
                                        <li>Asesoramiento personalizado</li>
                                    <?php elseif ($plan['nombre'] === 'elite'): ?>
                                        <li>Asesoramiento personalizado</li>
                                        <li>Acceso a eventos exclusivos</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </section>
            </div>

            <section class="pago-form">
                <fieldset>
                    <legend>Datos de facturación (Simulado)</legend>
                    <label for="nombre-titular">Nombre del titular</label>
                    <input type="text" id="nombre-titular" name="nombre-titular" placeholder="Tal como aparece en la tarjeta" required>
                    
                    <label for="numero-tarjeta">Número de tarjeta</label>
                    <input type="text" id="numero-tarjeta" name="numero-tarjeta" placeholder="XXXX XXXX XXXX XXXX" required>
                    
                    <div class="form-row">
                        <div>
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123" required>
                        </div>
                        <div>
                            <label for="fecha_ven">Vencimiento</label>
                            <input type="month" id="fecha_ven" name="fecha_ven" required>
                        </div>
                    </div>
                </fieldset>
                <button type="submit" class="btn-primario">Confirmar Suscripción</button>
            </section>
        </form>
    </main>
    <?php require 'parts/footer.view.php'; ?>

</body>

</html>
