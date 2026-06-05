<!DOCTYPE html>
<html lang="es">

<head>
    <title>Crear cuenta regular</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/formularios.css">
    <script src="/assets/js/constructorElementos.js"></script>
    <script src="/assets/js/crear-cuenta-regular.js"></script>
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <h2>Crear cuenta</h2>
        <?php if (!empty($error)): ?>
            <p style="color:var(--rojo); margin-bottom:1rem; font-weight:600;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form action="/crearCuenta" id="form-crear-cuenta-regular" method="post" novalidate>
            <fieldset>
                <legend>Datos personales:</legend>
                <label for="nombre_apellido">Nombre y apellido</label>
                <input type="text" id="nombre_apellido" name="nombre_apellido" placeholder="Ej.: Maria Perez">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Ej.:tuemail@email.com">
                <label for="contraseña">Contraseña</label>
                <input type="password" id="contraseña" name="contraseña">
                <label for="ccontraseña">Confirmar contraseña</label>
                <input type="password" id="ccontraseña" name="ccontraseña">
                <label for="fotodeperfil">Foto de perfil (opcional)</label>
                <input type="file" id="fotodeperfil" name="fotodeperfil" accept="image/jpeg, image/png, image/webp">
                </fieldset>
            <button type="submit">Crear cuenta</button>
        </form>
        <div class="form-link">
            <p>¿Ya tienes una cuenta? <a href="inicio-sesion">Iniciar sesión</a></p>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>

</body>

</html>