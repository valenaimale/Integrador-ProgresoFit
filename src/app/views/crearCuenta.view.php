<!DOCTYPE html>
<html lang="es">

<head>
    <title>crear-cuenta</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/formularios.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <h2>Crear cuenta</h2>
        <?php if (!empty($error)): ?>
            <p style="color:var(--rojo); margin-bottom:1rem; font-weight:600;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form action="/crearCuenta" method="post" novalidate>
            <fieldset>
                <legend>Datos personales:</legend>
                <label for="nombre_apellido">Nombre y apellido</label>
                <input type="text" id="nombre_apellido" name="nombre_apellido" placeholder="Ej.: Maria Perez">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Ej.:tuemail@email.com">
                <label for="telefono">Telefono (opcional)</label>
                <input type="tel" id="telefono" name="telefono" placeholder="Ej.:11 11111111">
                <label for="fotoperfil">Foto de perfil (opcional)</label>
                <input type="file" id="fotoperfil" name="fotoperfil" accept="image/jpeg, image/png, image/webp">
                <label for="contraseña">Contraseña</label>
                <input type="password" id="contraseña" name="contraseña">
                <label for="ccontraseña">Confirmar contraseña</label>
                <input type="password" id="ccontraseña" name="ccontraseña">
                <fieldset>
                    <legend>Tipo de cuenta</legend>
                    <label>
                        <input type="radio" name="tipo_cuenta" value="gimnasio" checked>
                        Gimnasio
                    </label>
                    <label>
                        <input type="radio" name="tipo_cuenta" value="entrenador">
                        Entrenador
                    </label>
                    <label>
                        <input type="radio" name="tipo_cuenta" value="regular">
                        Regular
                    </label>
                </fieldset>
            </fieldset>

            <button type="submit">Crear cuenta</button>
        </form>
        <div class="form-link">
            <p>¿Ya tienes una cuenta? <a href="inicio-sesion.html">Iniciar sesión</a></p>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>

</body>

</html>