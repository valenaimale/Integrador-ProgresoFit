document.addEventListener("DOMContentLoaded", () => {//cargar script crea el script y lo pone en el head del .html
    ConstructorElementos.cargarScript("FormValidation", "/assets/js/components/formValidation.js", () => {
        new FormValidation("#form-editar-perfil-gim", {
            contra_nueva_repetida:{
                validar: (input) => {
                    return input.value === document.querySelector("#contra_nueva").value;
                },
                mensaje: "Las contraseñas no coinciden."
            },
            nombre:{
                validar: (input) => input.value.trim().length > 0,
                mensaje: "El nombre es obligatorio."
            },
            telefono: {
                validar: (input) => {
                    if (input.value.trim() === "") return null;
                    return /^\d+$/.test(input.value.trim());
                },
                mensaje: "El teléfono debe contener solo números."
            }
        })});
    });

    /*<form method="POST" name="form-editar-perfil-gim" action="/editar-perfil" class="info-lista" novalidate>
                <fieldset>
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($userData['nombre'] ?? '') ?>" placeholder="ej.: Sport Club">
                    <label for="direccion">Direccion</label>
                    <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($userData['direccion'] ?? '') ?>" placeholder="ej.: Calle Falsa 1234">
                    <label for="horarios">Horario</label>
                    <input type="text" id="horarios" name="horarios" value="<?= htmlspecialchars($userData['horarios'] ?? '') ?>" placeholder="ej.: 9hs a 12hs">
                    <label for="telefono">Telefono</label>
                    <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($userData['telefono'] ?? '') ?>" placeholder="ej.: 11 3703 4068">
                    <label for="descripcion">Descripcion</label>
                    <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($userData['descripcion'] ?? '') ?></textarea>
                    <label for="servicios"> Servicios</label>
                    <input type="tel" id="servicios" name="servicios" value="<?= htmlspecialchars($userData['servicios'] ?? '') ?>" placeholder="ej.: Funcional, clases de pileta y maquinas">
                    <label for="contra_actual"> Contraseña actual</label>
                    <input type="password" id="contra_actual" name="contra_actual">
                    <label for="contra_nueva"> Nueva Contraseña</label>
                    <input type="password" id="contra_nueva" name="contra_nueva">
                    <label for="contra_nueva_repetida"> Repetir nueva Contraseña</label>
                    <input type="password" id="contra_nueva_repetida" name="contra_nueva_repetida">
                </fieldset>  */