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
    <script src="/assets/js/editar-perfil-alumno.js"></script>
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

            <form method="POST" action="/editar-perfil" id="form-editar-perfil-alumno" class="info-lista" novalidate>
                <fieldset>
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($userData['nombre'] ?? '') ?>" placeholder="ej.: Sport Club">
                    <label for="fotoperfil">Foto de perfil (opcional)</label>
                    <input type="file" id="fotoperfil" name="fotoperfil" value="<?= htmlspecialchars($userData['fotoperfil'] ?? '') ?>" accept="image/jpeg, image/png, image/webp">
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