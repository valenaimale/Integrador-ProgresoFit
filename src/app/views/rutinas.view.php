<!DOCTYPE html>
<html lang="es">

<head>
    <title>Rutinas</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/rutinas.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    
    <main>
        <section class="seccion-rutinas">
            <h2>Elige tu programa según tu objetivo</h2>
            <?php if (empty($rutinas)): ?>
                <p class="sin-resultados">No hay rutinas disponibles.</p>
            <?php else: ?>
                <ul class="grid-rutinas">
                    <?php foreach ($rutinas as $rutina): ?>
                        <li>
                            <a href="/rutina?id=<?= $rutina['id'] ?>" class="card-rutina">
                                <article>
                                    <h3><?= htmlspecialchars($rutina['titulo']) ?></h3>
                                    <div class="card-info">
                                        <span class="card-autor"><?= htmlspecialchars($rutina['entrenador_nombre'] ?? 'Entrenador') ?></span>
                                        <?php if (!empty($rutina['descripcion'])): ?>
                                            <p class="card-descripcion"><?= htmlspecialchars($rutina['descripcion']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($rutina['objetivo'])): ?>
                                            <span class="card-objetivo">Objetivo: <?= htmlspecialchars($rutina['objetivo']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="seccion-especial">
            <h2>Programas especiales de 28 días</h2>
            <ul class="grid-programas">
                <li>
                    <a href="/autocuidado" class="card-programa">
                        <article>
                            <h3>Autocuidado</h3>
                            <p>Las mejores rutinas para el cuidado personal</p>
                        </article> 
                    </a>
                </li>
                <li>
                    <a href="/principiante" class="card-programa">
                        <article>
                            <h3>Principiante</h3>
                            <p>Tenés que ver esto si estás comenzando a entrenarte</p>
                        </article> 
                    </a>
                </li>
            </ul>
        </section>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
