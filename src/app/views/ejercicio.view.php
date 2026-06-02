<!DOCTYPE html>
<html lang="es">

<head>
    <title>Ejercicio</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/rutina-ind.css">
    <link rel="stylesheet" href="/assets/css/perfil.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <?php if (!$ejercicio): ?>
            <p class="sin-resultados">Ejercicio no encontrado.</p>
        <?php else: ?>
            <div class="rutina-header">
                <h2><?= htmlspecialchars($ejercicio['nombre']) ?></h2>
                <div class="rutina-meta">
                    <?php if (!empty($ejercicio['musculos'])): ?>
                        <span><?= htmlspecialchars($ejercicio['musculos']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($ejercicio['dificultad'])): ?>
                        <span>Dificultad: <?= htmlspecialchars($ejercicio['dificultad']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($ejercicio['equipamiento'])): ?>
                        <span><?= htmlspecialchars($ejercicio['equipamiento']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($ejercicio['video_url'])): ?>
                <section>
                    <h2>Video tutorial</h2>
                    <article class="card-entrenamiento">
                        <iframe
                            src="<?= htmlspecialchars($ejercicio['video_url']) ?>"
                            title="Video tutorial — <?= htmlspecialchars($ejercicio['nombre']) ?>"
                            width="100%"
                            height="360"
                            frameborder="0"
                            allowfullscreen
                            style="border-radius: var(--radio-sm); display: block;">
                        </iframe>
                    </article>
                </section>
            <?php endif; ?>

            <section>
                <h2>Descripción</h2>
                <div class="info-lista">
                    <dl>
                        <?php if (!empty($ejercicio['musculos'])): ?>
                            <dt>Músculos involucrados</dt>
                            <dd><?= htmlspecialchars($ejercicio['musculos']) ?></dd>
                        <?php endif; ?>

                        <?php if (!empty($ejercicio['equipamiento'])): ?>
                            <dt>Equipamiento necesario</dt>
                            <dd><?= htmlspecialchars($ejercicio['equipamiento']) ?></dd>
                        <?php endif; ?>

                        <?php if (!empty($ejercicio['descripcion'])): ?>
                            <dt>Instrucciones</dt>
                            <dd><?= htmlspecialchars(strip_tags($ejercicio['descripcion'])) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </section>
        <?php endif; ?>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
