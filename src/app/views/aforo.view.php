<!DOCTYPE html>
<html lang="es">

<head>
    <title>Aforo del gimnasio - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Auto-refresh cada 30 segundos para mantener el aforo actualizado -->
    <meta http-equiv="refresh" content="30">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/aforo.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <div class="aforo-container">
            <h2>Aforo en tiempo real</h2>

            <?php if (!empty($error)): ?>
                <p class="aforo-error"><?= htmlspecialchars($error) ?></p>

            <?php elseif (!empty($aforo)): ?>
                <?php
                    $ocupacion  = (int) $aforo['ocupacion_actual'];
                    $capacidad  = (int) $aforo['capacidad_maxima'];
                    $porcentaje = $capacidad > 0 ? round($ocupacion / $capacidad * 100) : 0;

                    if ($porcentaje >= 90)      $estado = 'lleno';
                    elseif ($porcentaje >= 60)  $estado = 'moderado';
                    else                         $estado = 'libre';
                ?>

                <div class="aforo-card aforo-<?= $estado ?>">
                    <div class="aforo-numero">
                        <span class="aforo-actual"><?= $ocupacion ?></span>
                        <span class="aforo-sep">/</span>
                        <span class="aforo-max"><?= $capacidad ?></span>
                    </div>
                    <p class="aforo-etiqueta">personas en el gimnasio</p>

                    <div class="aforo-barra-wrap">
                        <div class="aforo-barra" style="width: <?= $porcentaje ?>%"></div>
                    </div>
                    <p class="aforo-porcentaje"><?= $porcentaje ?>% de ocupación</p>

                    <p class="aforo-estado-texto">
                        <?php if ($estado === 'lleno'): ?>
                            Gimnasio al límite de capacidad
                        <?php elseif ($estado === 'moderado'): ?>
                            Gimnasio con alta concurrencia
                        <?php else: ?>
                            Gimnasio con buena disponibilidad
                        <?php endif; ?>
                    </p>
                </div>

                <p class="aforo-actualiza">Se actualiza automáticamente cada 30 segundos.</p>
            <?php endif; ?>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
