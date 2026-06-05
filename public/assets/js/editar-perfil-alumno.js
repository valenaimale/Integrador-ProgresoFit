document.addEventListener("DOMContentLoaded", () => {//cargar script crea el script y lo pone en el head del .html
    ConstructorElementos.cargarScript("DragDrop", "/assets/js/components/dragDrop.js", () => {
        new DragDrop("#fotodeperfil");
    });
    ConstructorElementos.cargarScript("FormValidation", "/assets/js/components/formValidation.js", () => {
        new FormValidation("#form-editar-perfil-alumno", {
            contra_nueva_repetida:{
                validar: (input) => {
                    if (input.value.trim() === "") return null;
                    return input.value === document.querySelector("#contra_nueva").value;
                },
                mensaje: "Las contraseñas no coinciden."
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
            }
        })});
    });

//si bien alumno y entrenador al validar validan unicamente la contrasenia, 
//se ponen en archivos separados con el objetivo de que si, en un futuro, por ejemplo,
//para el entrenador es obligatorio tener especialidades, simplemente se agrega esa validacion
//al archivo de entrenador y no hay que crear uno nuevo de cero ni nada por el estilo.