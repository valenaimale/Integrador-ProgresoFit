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
        <header class="rutina-header">
            <h2>Press de banca con barra</h2>
            <div class="rutina-meta">
                <span>Pecho</span>
                <span>Dificultad: Media</span>
                <span>Equipamiento: Barra y banco</span>
            </div>
        </header>

        <section>
            <h2>Video tutorial</h2>
            <article class="card-entrenamiento">
                <iframe
                    src="https://www.youtube.com/embed/SCVCLChPQFY"
                    title="Video tutorial — Press de banca con barra"
                    width="100%"
                    height="360"
                    frameborder="0"
                    allowfullscreen
                    style="border-radius: var(--radio-sm); display: block;">
                </iframe>
            </article>
        </section>

        <section>
            <h2>Descripción</h2>
            <div class="info-lista">
                <dl>
                    <dt>Músculos involucrados</dt>
                    <dd>Pectoral mayor, Tríceps braquial, Deltoides anterior</dd>

                    <dt>Equipamiento necesario</dt>
                    <dd>Barra olímpica, Banco plano, Discos de peso</dd>

                    <dt>Instrucciones</dt>
                    <dd>Acostado en el banco, tomá la barra con agarre ligeramente más ancho que los hombros. Bajá la barra de forma controlada hasta rozar el pecho y empujá hacia arriba hasta extender los brazos por completo. Mantené los pies apoyados en el suelo durante todo el movimiento.</dd>
                </dl>
            </div>
        </section>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
