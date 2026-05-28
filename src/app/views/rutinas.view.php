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
            <ul class="grid-rutinas">
                <li>
                    <a href="/rutina1" class="card-rutina">
                        <article>
                            <h3>Rutina #1</h3>
                            <div class="card-info">
                                <span class="card-autor">Entrenador</span>
                                <time datetime="2026-05-01/2026-06-01" class="card-fechas">01 May - 01 Jun</time>
                                <span class="card-objetivo">Objetivo: Hipertrofia</span>
                            </div>
                        </article>
                    </a>
                </li>
                <li>
                    <a href="/rutina2" class="card-rutina">
                        <article>
                            <h3>Rutina #2</h3>
                            <div class="card-info">
                                <span class="card-autor">Entrenador</span>
                                <time datetime="2026-05-15/2026-06-15" class="card-fechas">15 May - 15 Jun</time>
                                <span class="card-objetivo">Objetivo: Resistencia</span>
                            </div>
                        </article>
                    </a>
                </li>
                <li>
                    <a href="/rutina3" class="card-rutina">
                        <article>
                            <h3>Rutina #3</h3>
                            <div class="card-info">
                                <span class="card-autor">Entrenador</span>
                                <time datetime="2026-06-01/2026-07-01" class="card-fechas">01 Jun - 01 Jul</time>
                                <span class="card-objetivo">Objetivo: Fuerza</span>
                            </div>
                        </article>
                    </a>
                </li>
            </ul>
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
