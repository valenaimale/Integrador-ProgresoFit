<!DOCTYPE html>
<html lang="es">

<head>
    <title>Mi Perfil - ProgresoFit</title>
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
                    <img src="/assets/img/imagenUser.jpg" alt="Foto del usuario">
                </figure>
            </section>

            <section class="info-lista">
                <dl>
                    <dt>Nombre</dt>
                    <dd><?= htmlspecialchars($userData['nombre'] ?? 'Sin nombre') ?></dd>

                    <dt>Email</dt>
                    <dd><a href="mailto:<?= htmlspecialchars($userData['email'] ?? '') ?>"><?= htmlspecialchars($userData['email'] ?? '') ?></a></dd>

                    <dt>Rol</dt>
                    <dd><?= htmlspecialchars($userData['rol'] ?? 'ALUMNO') ?></dd>

                    <dt>Miembro desde</dt>
                    <dd><?= !empty($userData['created_at']) ? date('d/m/Y', strtotime($userData['created_at'])) : '-' ?></dd>
                </dl>
            </section>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
