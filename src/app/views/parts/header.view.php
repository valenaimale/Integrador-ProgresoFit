<header>
    <img src="Logo.JPG" alt="Logo ProgresoFit">
    <h1><a href="/">progresoFit</a></h1>
    <nav>
        <ul>
            <li><a href="/">Inicio</a></li>
            <li><a href="/rutinas">Rutinas</a></li>
            <li><a href="/gimnasios">Gimnasios</a></li>
        </ul>

        <div class="nav-acciones">
            <?php if (!empty($_SESSION['user'])): ?>
                <a href="/perfil-user"><?= htmlspecialchars($_SESSION['user']['nombre'] ?? 'Mi perfil') ?></a>
                <a href="/cerrar-sesion">Cerrar sesión</a>
            <?php else: ?>
                <a href="/crearCuenta">Registro</a>
                <a href="/inicio-sesion">Iniciar sesión</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
