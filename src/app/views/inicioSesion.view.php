<!DOCTYPE html>
<html lang="es">

<head>
    <title>Inicio de Sesión - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/formularios.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <h2>Iniciar sesión</h2>
        <?php if (!empty($error)): ?>
            <p style="color:var(--rojo); margin-bottom:1rem; font-weight:600;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form action="/inicio-sesion" method="post">
            <fieldset>
                <legend>Datos personales:</legend>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="ej.:tuemail@email.com" required>
                <label for="contra">Contraseña</label>
                <input type="password" name="password" id="contra" placeholder="ej.:tucontraseña" required>
            </fieldset>
            <button type="submit">Iniciar sesión</button>
        </form>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>