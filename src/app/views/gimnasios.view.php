<!DOCTYPE html>
<html lang="es">

<head>
    <title>Gimnasios - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/rutinas.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>

    <main>
        <section class="seccion-rutinas">
            <h2>Gimnasios disponibles</h2>

            <?php if (empty($gimnasios)): ?>
                <p class="text-muted">No hay gimnasios disponibles en este momento.</p>
            <?php else: ?>
                <ul class="grid-rutinas">
                    <?php foreach ($gimnasios as $gym): ?>
                        <li>
                            <a href="/perfil-gimnasio?id=<?= (int) $gym['id'] ?>" class="card-rutina">
                                <article>
                                    <h3><?= htmlspecialchars($gym['nombre']) ?></h3>
                                    <div class="card-info">
                                        <?php if (!empty($gym['direccion'])): ?>
                                            <span class="card-autor"><?= htmlspecialchars($gym['direccion']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($gym['horarios'])): ?>
                                            <span class="card-objetivo"><?= htmlspecialchars($gym['horarios']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>

    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
