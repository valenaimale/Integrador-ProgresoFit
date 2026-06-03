document.addEventListener("DOMContentLoaded", () => {//cargar script crea el script y lo pone en el head del .html
    ConstructorElementos.cargarScript("FormValidation", "/assets/js/components/formValidation.js", () => {
        new FormValidation("#form-editar-perfil-alumno", {
            contra_nueva_repetida:{
                validar: (input) => {
                    if (input.value.trim() === "") return false;
                    return input.value === document.querySelector("#contra_nueva").value;
                },
                mensaje: "Las contraseñas no coinciden."
            },
        })});
    });

//si bien alumno y entrenador al validar validan unicamente la contrasenia, 
//se ponen en archivos separados con el objetivo de que si, en un futuro, por ejemplo,
//para el entrenador es obligatorio tener especialidades, simplemente se agrega esa validacion
//al archivo de entrenador y no hay que crear uno nuevo de cero ni nada por el estilo.