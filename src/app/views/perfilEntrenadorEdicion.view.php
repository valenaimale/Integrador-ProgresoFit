<!DOCTYPE html>
<html lang="es">


<head>
    <title>Perfil del entrenador</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/perfil.css">
    <link rel="stylesheet" href="/assets/css/formularios.css">

</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <div class="perfil-grid">
            <!-- Foto principal del entrenador -->
            <section class="perfil-hero">
                <figure>
                    <img src="foto-entrenador.jpg" alt="Foto del entrenador">
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

            <form method="POST" action="/editar-perfil" class="info-lista" novalidate>
                <fieldset>
                    <label for="nombre">Nombre y apellido</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($userData['nombre'] ?? '') ?>" placeholder="ej.: Maria Perez">
                    <label for="especialidad">Especialidad</label>
                    <input type="text" id="especialidad" name="especialidad" value="<?= htmlspecialchars($userData['especialidad'] ?? '') ?>" placeholder="ej.: Tren superior">
                    <label for="descripcion">Descripcion</label>
                    <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($userData['descripcion'] ?? '') ?></textarea>
                    <label for="horario">Horario</label>
                    <input type="text" id="horario" name="horario" value="<?= htmlspecialchars($userData['horario'] ?? '') ?>" placeholder="ej.: 9hs a 12hs">
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