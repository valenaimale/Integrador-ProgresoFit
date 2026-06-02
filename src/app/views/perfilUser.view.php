<!DOCTYPE html>
<html lang="es">

<head>
    <title>Mi Perfil - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/perfil.css">
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <div class="perfil-grid">
            <section class="perfil-hero">
                <figure>
                    <img src="/assets/img/imagenUser.jpg" alt="Foto del usuario">
                </figure>
            </section>

            <section class="info-lista">
                <div class="info-header">
                    <h2>Mis datos</h2>
                    <button type="button" id="btn-editar">Editar</button>
                </div>

                <dl>
                    <dt>Nombre</dt>
                    <dd><?= htmlspecialchars($userData['nombre'] ?? 'Sin nombre') ?></dd>

                    <dt>Email</dt>
                    <dd><a href="mailto:<?= htmlspecialchars($userData['email'] ?? '') ?>"><?= htmlspecialchars($userData['email'] ?? '') ?></a></dd>

                    <dt>Rol</dt>
                    <dd><?= htmlspecialchars($userData['rol'] ?? 'ALUMNO') ?></dd>

                    <dt>Miembro desde</dt>
                    <dd><?= !empty($userData['created_at']) ? date('d/m/Y', strtotime($userData['created_at'])) : '-' ?></dd>
                </dl>

                <?php if (!empty($error)): ?>
                    <p class="error-msg"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form method="POST" action="/perfil-user" class="perfil-form"
                      id="form-editar" <?= empty($error) ? 'hidden' : '' ?>>
                    <h3>Editar perfil</h3>

                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre"
                           value="<?= htmlspecialchars($userData['nombre'] ?? '') ?>"
                           placeholder="Tu nombre">

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($userData['email'] ?? '') ?>"
                           placeholder="Tu email">

                    <label for="password">Nueva contraseña</label>
                    <input type="password" id="password" name="password"
                           placeholder="Dejá vacío para no cambiarla">

                    <label for="password_confirm">Confirmar contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm"
                           placeholder="Repetí la nueva contraseña">

                    <p id="pass-error" class="error-msg" hidden>Las contraseñas no coinciden</p>

                    <div class="form-acciones">
                        <button type="submit">Guardar cambios</button>
                        <button type="button" id="btn-cancelar">Cancelar</button>
                    </div>
                </form>
            </section>

            <script>
                const btnEditar   = document.getElementById('btn-editar');
                const btnCancelar = document.getElementById('btn-cancelar');
                const form        = document.getElementById('form-editar');

                btnEditar.addEventListener('click', () => {
                    form.removeAttribute('hidden');
                    btnEditar.setAttribute('hidden', '');
                });

                btnCancelar.addEventListener('click', () => {
                    form.setAttribute('hidden', '');
                    btnEditar.removeAttribute('hidden');
                });

                form.addEventListener('submit', (e) => {
                    const pass        = document.getElementById('password').value;
                    const passConfirm = document.getElementById('password_confirm').value;
                    const passError   = document.getElementById('pass-error');

                    if (pass !== passConfirm) {
                        e.preventDefault();
                        passError.removeAttribute('hidden');
                    } else {
                        passError.setAttribute('hidden', '');
                    }
                });
            </script>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>
</body>

</html>
