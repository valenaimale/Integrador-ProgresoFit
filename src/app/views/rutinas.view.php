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
            <h2><?= in_array($user['rol'], ['ENTRENADOR', 'ADMIN']) ? 'Gestión de Rutinas' : 'Mis Rutinas Asignadas' ?></h2>
            
            <?php if (isset($_GET['mensaje'])): ?>
                <p class="exito"><?= htmlspecialchars($_GET['mensaje']) ?></p>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
            <?php endif; ?>

            <?php if (empty($rutinas)): ?>
                <p>No hay rutinas para mostrar en este momento.</p>
            <?php else: ?>
                <ul class="grid-rutinas">
                    <?php foreach ($rutinas as $rutina): ?>
                        <li class="card-rutina-container">
                            <a href="/rutina?id=<?= $rutina['id'] ?>" class="card-rutina">
                                <article>
                                    <h3><?= htmlspecialchars($rutina['titulo']) ?></h3>
                                    <div class="card-info">
                                        <span class="card-autor"><?= htmlspecialchars($rutina['entrenador_nombre'] ?? 'Sistema') ?></span>
                                        <span class="card-objetivo">Objetivo: <?= htmlspecialchars($rutina['objetivo'] ?? 'General') ?></span>
                                    </div>
                                </article>
                            </a>
                            
                            <?php if (in_array($user['rol'], ['ENTRENADOR', 'ADMIN'])): ?>
                                <div class="admin-actions">
                                    <form action="/rutinas/asignar" method="post" class="form-asignar">
                                        <input type="hidden" name="rutina_id" value="<?= $rutina['id'] ?>">
                                        <select name="alumno_id" required>
                                            <option value="">Asignar a alumno...</option>
                                            <?php foreach ($alumnos as $alumno): ?>
                                                <option value="<?= $alumno['id'] ?>"><?= htmlspecialchars($alumno['nombre']) ?> (<?= htmlspecialchars($alumno['email']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-asignar">Asignar</button>
                                    </form>
                                </div>
                            <?php endif; ?>
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
