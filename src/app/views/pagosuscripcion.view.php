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
        <h2>Pago de suscripción</h2>
        <div class="perfil-grid">
            <aside class="info-lista">
                <h2>Detalles del plan</h2>
                <dl>
                    <dt>Plan</dt>
                    <dd>Plan Premium</dd>

                    <dt>Precio</dt>
                    <dd>$2.999 / mes</dd>

                    <dt>Incluye</dt>
                    <dd>Acceso ilimitado a rutinas, asesoramiento personalizado y descuentos en gimnasios asociados</dd>

                    <dt>Duración</dt>
                    <dd>Mensual (se renueva automáticamente)</dd>
                </dl>
            </aside>

            <form action="/formularioPago" method="post">
                <fieldset>
                    <legend>Datos personales</legend>
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" placeholder="ej.: María" required>
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" placeholder="ej.: Pérez" required>
                    <label for="documento">Documento</label>
                    <input type="text" id="documento" name="documento" placeholder="ej.: 12345678" required>
                </fieldset>
                <fieldset>
                    <legend>Datos de facturación</legend>
                    <label for="numero-tarjeta">Número de tarjeta</label>
                    <input type="text" id="numero-tarjeta" name="numero-tarjeta" placeholder="XXXX XXXX XXXX XXXX" required>
                    <label for="nombre-titular">Nombre del titular</label>
                    <input type="text" id="nombre-titular" name="nombre-titular" placeholder="Tal como aparece en la tarjeta" required>
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" placeholder="123" required>
                    <label for="fecha_ven">Fecha de vencimiento</label>
                    <input type="date" id="fecha_ven" name="fecha_ven" required>
                </fieldset>
                <button type="submit">Confirmar pago</button>
            </form>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>

</body>

</html>
