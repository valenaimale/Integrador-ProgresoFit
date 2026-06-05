<!DOCTYPE html>
<html lang="es">


<head>
    <title>Perfil del entrenador</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/perfil.css">

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
            <section class="info-lista">
                <dl>
                    <dt>Nombre</dt>
                    <dd><?= htmlspecialchars($userData['nombre'] ?? 'Sin nombre') ?></dd>

                    <dt>Email</dt>
                    <dd><a href="mailto:<?= htmlspecialchars($userData['email'] ?? '') ?>"><?= htmlspecialchars($userData['email'] ?? '-') ?></a></dd>

                    <dt>Especialidad</dt>
                    <dd><?= htmlspecialchars($userData['especialidad'] ?? 'No especificada') ?></dd>

                    <dt>Descripción</dt>
                    <dd><?= htmlspecialchars($userData['descripcion'] ?? '-') ?></dd>

                    <dt>Horario</dt>
                    <dd><?= htmlspecialchars($userData['horario'] ?? '-') ?></dd>

                    <dt>Gimnasio</dt>
                    <dd><?= htmlspecialchars($userData['gimnasio_nombre'] ?? '-') ?></dd>

                    <dt>Miembro desde</dt>
                    <dd><?= !empty($userData['created_at']) ? date('d/m/Y', strtotime($userData['created_at'])) : '-' ?></dd>
                </dl>
                <a href="/editar-perfil" class="btn btn-primary">Editar perfil</a>
            </section>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>

</body>

</html>