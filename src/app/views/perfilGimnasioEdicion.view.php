<!DOCTYPE html>
<html lang="es">


<head>
    <title>Perfil del entrenador</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/perfil.css">
    <link rel="stylesheet" href="/assets/css/formularios.css">
    <script src="/assets/js/constructorElementos.js"></script>
    <script src="/assets/js/editar-perfil-gimnasio.js"></script>
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <div class="perfil-grid">
            <!-- Foto principal del entrenador -->
            <section class="perfil-hero">
                <figure>
                    <img src="foto-gimnasio.jpg" alt="Foto del entrenador">
                </figure>
            </section>

            <!-- Info principal del entrenador -->
            <div>
                <dl class="info-lista">
                    <dt>Email</dt>
                    <dd><a href="mailto:<?= htmlspecialchars($userData['email'] ?? '') ?>"><?= htmlspecialchars($userData['email'] ?? '') ?></a></dd>

                    <dt>Miembro desde</dt>
                    <dd><?= !empty($userData['created_at']) ? date('d/m/Y', strtotime($userData['created_at'])) : '' ?></dd>
                </dl>

            <form method="POST" id="form-editar-perfil-gim" action="/editar-perfil" class="info-lista" novalidate>
                <fieldset>
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($userData['nombre'] ?? '') ?>" placeholder="Ej.: Sport Club Lujan">
                    <label for="telefono">Telefono (opcional) </label>
                    <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($userData['telefono'] ?? '') ?>" placeholder="Ej.: 11 3703 4068">
                    <label for="direccion">Direccion</label>
                    <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($userData['direccion'] ?? '') ?>" placeholder="Ej.: Calle Falsa 1234">
                    <label for="horarios">Horarios (opcional)</label>
                    <input type="text" id="horarios" name="horarios" value="<?= htmlspecialchars($userData['horarios'] ?? '') ?>" placeholder="Ej.: 9hs a 12hs">
                    <label for="logo">Logo (opcional)</label>
                    <input type="file" id="logo" name="logo" value="<?= htmlspecialchars($userData['logo'] ?? '') ?>" accept="image/jpeg, image/png, image/webp">
                    <label for="descripcion">Descripcion (opcional)</label>
                    <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($userData['descripcion'] ?? '') ?></textarea>
                    <label for="servicios"> Servicios (opcional)</label>
                    <input type="tel" id="servicios" name="servicios" value="<?= htmlspecialchars($userData['servicios'] ?? '') ?>" placeholder="Ej.: clases de pileta, funcional, zumba y musculacion">
                    <label for="contra_actual"> Contraseña actual</label>
                    <input type="password" id="contra_actual" name="contra_actual">
                    <label for="contra_nueva"> Nueva Contraseña</label>
                    <input type="password" id="contra_nueva" name="contra_nueva">
                    <label for="contra_nueva_repetida"> Repetir nueva Contraseña</label>
                    <input type="password" id="contra_nueva_repetida" name="contra_nueva_repetida">
                </fieldset> 
                <?php if (!empty($error)): ?>
                    <p class="text-rojo"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </form>
            </div>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>

</body>

</html>
<!--<label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej.: Sport Club Lujan">
                <label for="telefono">Telefono (opcional)</label>
                <input type="tel" id="telefono" name="telefono" placeholder="Ej.:+54 011 11111111">
                <label for="direccion">Direccion</label>
                <input type="text" id="direccion" name="direccion" placeholder="Ej.:Calle Falsa 1234">
                <label for="horarios">Horarios (opcional)</label>  
                <input type="text" id="horarios" name="horarios" placeholder="Ej.:9hs a 12hs">
                <label for="logo">Agregar logo (opcional)</label>
                <input type="file" id="logo" name="logo" accept="image/jpeg, image/png, image/webp">
                <label for="descripcion">Descripcion (opcional)</label>
                <textarea id="descripcion" name="descripcion">
                <label for="servicios">Servicios (opcional)</label>
                <input type="text" id="servicios" name="servicios" placeholder="Ej.: clases de pileta, funcional, zumba y musculacion">    
                <label for="contraseña">Contraseña</label>
                <input type="password" id="contraseña" name="contraseña" placeholder="ej.:tucontraseña">
                <label for="ccontraseña">Confirmar contraseña</label>
                <input type="password" id="ccontraseña" name="ccontraseña" placeholder="ej.:tucontraseña">