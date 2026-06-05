document.addEventListener("DOMContentLoaded", () => {//cargar script crea el script y lo pone en el head del .html
    ConstructorElementos.cargarScript("FormValidation", "/assets/js/components/formValidation.js", () => {
        new FormValidation("#form-inicio-sesion", {
            contra:{
                validar: (input) => input.value.trim().length > 0,
                mensaje: "La contraseña es obligatoria."
            },
            email:{
                validar: (input) => input.value.trim().length > 0,
                mensaje: "El email es obligatorio."
            }     
        })});
    });