<!DOCTYPE html>
<html lang="es">

<head>
    <title><?= htmlspecialchars($userData['nombre'] ?? 'Perfil del Gimnasio') ?> - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/perfil.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <div class="perfil-grid">
            <section class="perfil-hero">
                <figure>
                    <img src="/assets/img/imagenUser.jpg" alt="Foto del gimnasio">
                </figure>
            </section>

            <section class="info-lista">
                <dl>
                    <dt>Nombre</dt>
                    <dd><?= htmlspecialchars($userData['nombre'] ?? 'Sin nombre') ?></dd>

                    <dt>Email</dt>
                    <dd><a href="mailto:<?= htmlspecialchars($userData['email'] ?? '') ?>"><?= htmlspecialchars($userData['email'] ?? '-') ?></a></dd>

                    <dt>Dirección</dt>
                    <dd><?= htmlspecialchars($userData['direccion'] ?? '-') ?></dd>

                    <dt>Horarios</dt>
                    <dd><?= htmlspecialchars($userData['horarios'] ?? '-') ?></dd>

                    <dt>Teléfono</dt>
                    <dd><?= htmlspecialchars($userData['telefono'] ?? '-') ?></dd>

                    <dt>Descripción</dt>
                    <dd><?= htmlspecialchars($userData['descripcion'] ?? '-') ?></dd>

                    <dt>Servicios</dt>
                    <dd><?= htmlspecialchars($userData['servicios'] ?? '-') ?></dd>
                </dl>
                <a href="/editar-perfil" class="btn btn-primary">Editar perfil</a>
            </section>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
