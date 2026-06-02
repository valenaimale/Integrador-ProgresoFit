<!DOCTYPE html>
<html lang="es">

<head>
    <title>Rutina Individual</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/rutina-ind.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    
    <main>
        <header class="rutina-header">
            <h2><?= htmlspecialchars($rutina['titulo']) ?></h2>
            <div class="rutina-meta">
                <span class="rutina-autor">Entrenador: <?= htmlspecialchars($rutina['entrenador_nombre'] ?? 'Sistema') ?></span>
                <span class="rutina-objetivo">Objetivo: <?= htmlspecialchars($rutina['objetivo'] ?? 'No especificado') ?></span>
            </div>
            <?php if (!empty($rutina['descripcion'])): ?>
                <p class="rutina-desc"><?= nl2br(htmlspecialchars($rutina['descripcion'])) ?></p>
            <?php endif; ?>
        </header>

        <section class="grid-entrenamientos">
            <?php if (empty($rutina['dias'])): ?>
                <p>Esta rutina aún no tiene ejercicios cargados.</p>
            <?php else: ?>
                <?php foreach ($rutina['dias'] as $dia): ?>
                    <article class="card-entrenamiento">
                        <div class="card-header">
                            <h3><?= htmlspecialchars($dia['nombre_dia']) ?> - <?= htmlspecialchars($dia['grupo_muscular'] ?? 'General') ?></h3>
                            <div class="card-meta-badges">
                                <span class="badge-duracion"><?= $dia['duracion_minutos'] ?? '-' ?> min</span>
                                <span class="badge-ejercicios"><?= count($dia['ejercicios']) ?> ejercicios</span>
                            </div>
                        </div>

                        <div class="lista-ejercicios">
                            <?php foreach ($dia['ejercicios'] as $ej): ?>
                                <div class="ejercicio-item">
                                    <div>
                                        <div class="ejercicio-nombre"><?= htmlspecialchars($ej['nombre']) ?></div>
                                        <div class="ejercicio-series"><?= htmlspecialchars($ej['series_repeticiones'] ?? '-') ?></div>
                                    </div>
                                    <div class="ejercicio-acciones">
                                        <!-- Enlace a la vista de ejercicio detalle si existiera, o directo a video -->
                                        <?php if (!empty($ej['video_url'])): ?>
                                            <a href="<?= htmlspecialchars($ej['video_url']) ?>" target="_blank" class="btn-video" aria-label="Ver video">
                                                <img src="/assets/img/logoyoutube.JPG" alt="Video">
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
    
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>