document.addEventListener("DOMContentLoaded", () => {//cargar script crea el script y lo pone en el head del .html
    ConstructorElementos.cargarScript("FormValidation", "/assets/js/components/formValidation.js", () => {
        new FormValidation("#form-editar-perfil-entrenador", {
            contra_nueva_repetida:{
                validar: (input) => {
                    if (input.value.trim() === "") return false;
                    return input.value === document.querySelector("#contra_nueva").value;
                },
                mensaje: "Las contraseñas no coinciden."
            },
        })});
    });