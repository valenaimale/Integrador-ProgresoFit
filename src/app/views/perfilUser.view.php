<!DOCTYPE html>
<html lang="es">

<head>
    <title>Mi Perfil - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css?v=2">
    <link rel="stylesheet" href="/assets/css/perfil.css?v=2">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <div class="perfil-container">
            <!-- Header de Perfil Compacto -->
            <header class="perfil-header-compacto">
                <figure class="perfil-foto-circular">
                    <img src="/assets/img/imagenUser.jpg" alt="Foto de <?= htmlspecialchars($userData['nombre'] ?? 'Usuario') ?>">
                </figure>
                <div class="perfil-titulos">
                    <h2><?= htmlspecialchars($userData['nombre'] ?? 'Sin nombre') ?></h2>
                    <span class="badge-rol"><?= htmlspecialchars($userData['rol'] ?? 'ALUMNO') ?></span>
                </div>
            </header>

            <div class="perfil-grid-cards">
                <!-- Card Información Personal -->
                <section class="info-card">
                    <div class="card-header-icon">
                        <i class="icon-user"></i>
                        <h3>Información Personal</h3>
                    </div>
                    <dl>
                        <dt>Email</dt>
                        <dd><a href="mailto:<?= htmlspecialchars($userData['email'] ?? '') ?>"><?= htmlspecialchars($userData['email'] ?? '') ?></a></dd>

                        <dt>Miembro desde</dt>
                        <dd><?= !empty($userData['created_at']) ? date('d/m/Y', strtotime($userData['created_at'])) : '-' ?></dd>
                    </dl>
                </section>

                <!-- Card Mi Suscripción -->
                <?php if ($userData['rol'] === 'ALUMNO'): ?>
                <section class="info-card suscripcion-card">
                    <div class="card-header-icon">
                        <i class="icon-star"></i>
                        <h3>Mi Suscripción</h3>
                    </div>
                    <?php if ($suscripcionActiva): ?>
                        <div class="sub-status activa">
                            <p class="plan-nombre"><?= strtoupper(htmlspecialchars($suscripcionActiva['plan'])) ?></p>
                            <div class="sub-fechas">
                                <span><strong>Inicio:</strong> <?= date('d/m/Y', strtotime($suscripcionActiva['fecha_inicio'])) ?></span>
                                <span><strong>Vence:</strong> <?= date('d/m/Y', strtotime($suscripcionActiva['fecha_fin'])) ?></span>
                            </div>
                            <p class="precio-tag">$<?= number_format($suscripcionActiva['precio'], 2, ',', '.') ?></p>
                            <span class="badge-status">Activa</span>
                        </div>
                    <?php else: ?>
                        <div class="sub-status vacia">
                            <p>No tenés una suscripción activa.</p>
                            <a href="/pago-suscripcion" class="btn-suscribirse">Suscribirse ahora</a>
                        </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
