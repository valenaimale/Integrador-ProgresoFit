<!DOCTYPE html>
<html lang="es">

<head>
    <title>crear-cuenta</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/crear-cuenta.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <h2>Crear cuenta de gimnasio</h2>
        <form action="/crearCuenta" id="form-alta-gim" method="post" novalidate>
            <fieldset>
                <legend>Datos del gimnasio:</legend>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Ej.:tuemail@email.com">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej.: Sport Club Lujan">
                <label for="telefono">Telefono (opcional)</label>
                <input type="tel" id="telefono" name="telefono" placeholder="Ej.:11 3703 4068">
                <label for="direccion">Direccion</label>
                <input type="text" id="direccion" name="direccion" placeholder="Ej.:Calle Falsa 1234">
                <label for="horarios">Horarios (opcional)</label>  <!-- puede que el gimnasioeste recien arrancando y aun no sepa sus horarios -->
                <input type="text" id="horarios" name="horarios" placeholder="Ej.:9hs a 12hs">
                <label for="logo">Logo (opcional)</label>
                <input type="file" id="logo" name="logo" accept="image/jpeg, image/png, image/webp">
                <label for="descripcion">Descripcion (opcional)</label>
                <textarea id="descripcion" name="descripcion">
                <label for="servicios">Servicios (opcional)</label>
                <input type="text" id="servicios" name="servicios" placeholder="Ej.: clases de pileta, funcional, zumba y musculacion">    
                <label for="contra_nueva">Contraseña</label>
                <input type="password" id="contra_nueva" name="contra_nueva">
                <label for="contra_nueva_repetida">Confirmar contraseña</label>
                <input type="password" id="contra_nueva_repetida" name="contra_nueva_repetida">
            </fieldset>
            <button type="submit">Crear cuenta de gimnasio</button>
        </form>
        <div class="form-link">
            <p>¿Ya tienes una cuenta? <a href="/inicio-sesion-gim">Iniciar sesión</a></p>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>

</body>

</html>