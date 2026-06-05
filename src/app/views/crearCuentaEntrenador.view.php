<!DOCTYPE html>
<html lang="es">

<head>
    <title>Crear cuenta para entrenador</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/perfil.css">

    <link rel="stylesheet" href="/assets/css/formularios.css">
    <script src="/assets/js/constructorElementos.js"></script>
    <script src="/assets/js/crear-cuenta-entrenador.js"></script>
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <h2>Crear cuenta de entrenador</h2>
        <?php if (!empty($error)): ?>
            <p style="color:var(--rojo); margin-bottom:1rem; font-weight:600;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form action="/crearCuentaEntrenador" id="form-crear-cuenta-entrenador" method="post" novalidate>
            <fieldset>
                <legend>Datos del entrenador:</legend>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Ej.:tuemail@email.com">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej.: Profe Maxi">
                <label for="horario">Horario (opcional)</label>  <!-- puede que el gimnasioeste recien arrancando y aun no sepa sus horarios -->
                <input type="text" id="horario" name="horario" placeholder="Ej.:9hs a 12hs">
                <label for="fotodeperfil">Foto de perfil (opcional)</label>
                <input type="file" id="fotodeperfil" name="fotodeperfil" accept="image/jpeg, image/png, image/webp">
                <label for="descripcion">Descripcion (opcional)</label>
                <textarea id="descripcion" name="descripcion"></textarea>
                <label for="especialidad">Especialidad (opcional)</label>
                <input type="text" id="especialidad" name="especialidad" placeholder="Ej.: funcional y zumba">    
                <label for="contra_nueva">Contraseña</label>
                <input type="password" id="contra_nueva" name="contra_nueva">
                <label for="contra_nueva_repetida">Confirmar contraseña</label>
                <input type="password" id="contra_nueva_repetida" name="contra_nueva_repetida">
            </fieldset>
            <button type="submit">Crear cuenta de entrenador</button>
        </form>
        <div class="form-link">
            <p>¿Ya tienes una cuenta? <a href="/inicio-sesion">Iniciar sesión</a></p>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>