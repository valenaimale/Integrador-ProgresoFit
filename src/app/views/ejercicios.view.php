<!DOCTYPE html>
<html lang="es">

<head>
    <title>Ejercicios - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/ejercicios.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <section class="seccion-ejercicios">
            <h2>Catálogo de ejercicios</h2>

            <?php if (empty($ejercicios)): ?>
                <p class="sin-resultados">No se pudieron cargar los ejercicios.</p>
            <?php else: ?>
                <ul class="grid-ejercicios">
                    <?php foreach ($ejercicios as $ejercicio): ?>
                        <li>
                            <a href="/ejercicio?id=<?= $ejercicio['api_id'] ?>" class="card-ejercicio">
                                <article>
                                    <h3><?= htmlspecialchars($ejercicio['nombre']) ?></h3>
                                    <div class="card-ejercicio-info">
                                        <?php if (!empty($ejercicio['musculos'])): ?>
                                            <span class="badge-musculo"><?= htmlspecialchars($ejercicio['musculos']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($ejercicio['dificultad'])): ?>
                                            <span class="badge-dificultad badge-dificultad--<?= htmlspecialchars($ejercicio['dificultad']) ?>">
                                                <?= htmlspecialchars($ejercicio['dificultad']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($ejercicio['equipamiento'])): ?>
                                            <span class="card-equipamiento"><?= htmlspecialchars($ejercicio['equipamiento']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <nav class="paginacion">
                    <?php $limit = 20; ?>
                    <?php if ($offset > 0): ?>
                        <a href="/ejercicios?offset=<?= $offset - $limit ?>" class="btn-pagina">← Anterior</a>
                    <?php endif; ?>
                    <span class="paginacion-info">
                        <?= $offset + 1 ?>–<?= min($offset + $limit, $total) ?> de <?= $total ?>
                    </span>
                    <?php if ($offset + $limit < $total): ?>
                        <a href="/ejercicios?offset=<?= $offset + $limit ?>" class="btn-pagina">Siguiente →</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </section>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
