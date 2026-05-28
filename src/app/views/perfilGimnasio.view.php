<!DOCTYPE html>
<html lang="es">

<head>
    <title><?= htmlspecialchars($gimnasio['nombre'] ?? 'Perfil del Gimnasio') ?> - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/perfil.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <?php if ($gimnasio === null): ?>
            <p class="text-muted">No se encontró el gimnasio.</p>
        <?php else: ?>
            <div class="perfil-grid">
                <section class="perfil-hero">
                    <figure>
                        <img src="/assets/img/imagenUser.jpg" alt="Foto del gimnasio">
                    </figure>
                </section>

                <section class="info-lista">
                    <dl>
                        <dt>Nombre</dt>
                        <dd><?= htmlspecialchars($gimnasio['nombre']) ?></dd>

                        <?php if (!empty($gimnasio['direccion'])): ?>
                            <dt>Dirección</dt>
                            <dd><?= htmlspecialchars($gimnasio['direccion']) ?></dd>
                        <?php endif; ?>

                        <?php if (!empty($gimnasio['horarios'])): ?>
                            <dt>Horarios</dt>
                            <dd><?= htmlspecialchars($gimnasio['horarios']) ?></dd>
                        <?php endif; ?>
                    </dl>
                    <a href="/gimnasios" class="btn btn-outline">← Volver a gimnasios</a>
                </section>
            </div>
        <?php endif; ?>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
