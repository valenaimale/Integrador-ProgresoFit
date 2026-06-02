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
        <?php if (!$rutina): ?>
            <p class="sin-resultados">Rutina no encontrada.</p>
        <?php else: ?>
            <div class="rutina-header">
                <h2><?= htmlspecialchars($rutina['titulo']) ?></h2>
                <div class="rutina-meta">
                    <span class="rutina-autor"><?= htmlspecialchars($rutina['entrenador_nombre'] ?? 'Entrenador') ?></span>
                    <?php if (!empty($rutina['objetivo'])): ?>
                        <span class="rutina-objetivo">Objetivo: <?= htmlspecialchars($rutina['objetivo']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($rutina['descripcion'])): ?>
                    <p class="rutina-descripcion"><?= htmlspecialchars($rutina['descripcion']) ?></p>
                <?php endif; ?>
            </div>

            <section class="grid-entrenamientos">
                <?php if (empty($rutina['dias'])): ?>
                    <p class="sin-resultados">Esta rutina no tiene días cargados.</p>
                <?php else: ?>
                    <?php foreach ($rutina['dias'] as $dia): ?>
                        <article class="card-entrenamiento">
                            <div class="card-header">
                                <h3><?= htmlspecialchars($dia['nombre_dia']) ?></h3>
                                <div class="card-meta-badges">
                                    <?php if (!empty($dia['duracion_minutos'])): ?>
                                        <span class="badge-duracion"><?= $dia['duracion_minutos'] ?> min</span>
                                    <?php endif; ?>
                                    <?php if (!empty($dia['ejercicios'])): ?>
                                        <span class="badge-ejercicios"><?= count($dia['ejercicios']) ?> ejercicios</span>
                                    <?php endif; ?>
                                    <?php if (!empty($dia['grupo_muscular'])): ?>
                                        <span class="badge-grupo"><?= htmlspecialchars($dia['grupo_muscular']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="lista-ejercicios">
                                <?php if (empty($dia['ejercicios'])): ?>
                                    <p class="sin-resultados">Sin ejercicios cargados.</p>
                                <?php else: ?>
                                    <?php foreach ($dia['ejercicios'] as $ejercicio): ?>
                                        <div class="ejercicio-item">
                                            <div>
                                                <a href="/ejercicio-local?id=<?= $ejercicio['id'] ?>" class="ejercicio-nombre">
                                                    <?= htmlspecialchars($ejercicio['nombre']) ?>
                                                </a>
                                                <?php if (!empty($ejercicio['series_repeticiones'])): ?>
                                                    <div class="ejercicio-series"><?= htmlspecialchars($ejercicio['series_repeticiones']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ejercicio-acciones">
                                                <?php if (!empty($ejercicio['video_url'])): ?>
                                                    <a href="<?= htmlspecialchars($ejercicio['video_url']) ?>" class="btn-video" target="_blank" aria-label="Ver video">
                                                        <img src="/assets/img/logoyoutube.JPG" alt="Video">
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
    
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>