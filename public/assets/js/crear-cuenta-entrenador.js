document.addEventListener("DOMContentLoaded", () => {//cargar script crea el script y lo pone en el head del .html
    ConstructorElementos.cargarScript("DragDrop", "/assets/js/components/dragDrop.js", () => {
        new DragDrop("#fotodeperfil");
    });
    ConstructorElementos.cargarScript("FormValidation", "/assets/js/components/formValidation.js", () => {
        new FormValidation("#form-crear-cuenta-entrenador", {
            contra_nueva_repetida:{
                validar: (input) => {
                    if (input.value.trim().length === 0) return false;  // vacío → error
                        return input.value === document.querySelector("#contra_nueva").value;  // coincide → verde
                },
                mensaje: "Las contraseñas es obligatoria y debe coincidir con la ingresada."
            },
            contra_nueva:{
                validar: (input) => input.value.trim().length > 0,
                mensaje: "La contraseña es obligatoria."
            },
            nombre:{
                validar: (input) => input.value.trim().length > 0,
                mensaje: "El nombre es obligatorio."
            },
            fotodeperfil:{
                validar: (input) => {
                    if (input.files.length === 0) return null;  // vacío → neutro
                    const tiposValidos = ['image/jpeg', 'image/png', 'image/webp'];
                    return tiposValidos.includes(input.files[0].type); 
                },
                mensaje: "La foto de perfil debe ser una imagen JPG, PNG o WEBP."
            },
            email:{
                validar: (input) => input.value.trim().length > 0,
                mensaje: "El email es obligatorio."
            }
        })});
    });