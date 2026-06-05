<!DOCTYPE html>
<html lang="es">

<head>
    <title>Crear cuenta - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <section>
            <h2>¿Cómo querés registrarte?</h2>
            <a href="/crearCuenta" class="btn btn-primary">Regular</a>
            <a href="/crearCuentaEntrenador" class="btn btn-outline">Entrenador</a>
            <a href="/crearCuentaGym" class="btn btn-outline">Gimnasio</a>
        </section>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
