/* El objetivo de esta clase es evitar que el usuario envíe el 
formulario con datos inválidos, mostrándole mensajes de error en 
tiempo real, campo por campo, sin depender del comportamiento 
nativo del navegador (que es diferente en Chrome, Firefox, Safari).

El flujo sería:

1)Usuario llena el campo titulo y pasa al siguiente → el 
componente valida ese campo y si está vacío muestra un span 
rojo debajo con "El título es obligatorio"
2)Si el usuario lo corrige → el span desaparece (o se pone verde)
3)Si el usuario hace click en Crear libro sin completar todo → el 
componente valida todos los campos a la vez y muestra todos los 
errores juntos, sin enviar el form al servidor
*/
class FormValidation{
    reglas;
    form;
    constructor(pFormulario, pReglas){
        //las reglas van a tener cada uno de los campos del formulario 
        //y las cosas a validar del mismo
        if(!pFormulario)
            console.error();
        else{
            
            this.form = document.querySelector(pFormulario);
            this.reglas=pReglas;
            for(const regla in this.reglas){
            let span=ConstructorElementos.nuevoElemento("span" , "", {id:"error-"+regla, class:"mensaje-error"});//creo el span vacio ("") para posibles mensajes de error futuros
            let input = this.form.querySelector("#" + regla);//obtengo el input con el id del mismo (regla)
            // si el input está dentro de un drag-drop-area, insertar el span después del div contenedor
            // para evitar que quede dentro del área de drag & drop junto al span del nombre de archivo
            const contenedor = input.closest(".drag-drop-area") ?? input;
            contenedor.insertAdjacentElement("afterend", span);
            // file inputs escuchan blur porque DragDrop despacha blur tanto al seleccionar como al arrastrar
            input.addEventListener("blur", ()=>this.validacionIndividual(regla));//le agrego el event listener a cada input 
                //"blur", cuando el usuario abandona el campo se verifica que lo que fue ingresado es correcto o no
                //al abandonar el campo se ejecuta el metodo validacion que justamente se encarga de verificar lo ingresado
            }
            this.form.addEventListener("submit", (e)=>{
                if(!this.validacionTotal()) //si el formulario es invalido
                    e.preventDefault();//cancela el envio del formulario
            });//e.preventDefault() anula el comportamiento predeterminado del navegador, en este caso
            //es enviar el formulario
        }
    }
    validacionTotal(){
        let valido=true;
        for(const regla in this.reglas){//recorre cada uno de los campos
            this.validacionIndividual(regla);//valida cada uno de ellos
            if(this.form.querySelector("#error-"+regla).textContent!=="")//si el span es distinto de "", significa que la entrada es incorrecta
                valido=false;//si la entrada para uno de ellos es incorrecta, el formulario no es valido
        }
        return valido;//retorna si el form es valido o no
    }
    validacionIndividual(id) {
        let input=this.form.querySelector("#" + id);//obtengo el input del id (por ejemplo titulo)
        let regla=this.reglas[id];//obtengo el campo del id (funcion + mensaje)
        let span=this.form.querySelector("#error-"+id);
        const resultado = regla.validar(input);//guardo el resultado para poder distinguir null de false

        if(resultado === null){
            //campo opcional vacío → estado neutro, sin color ni mensaje
            input.classList.remove("input-error");
            input.classList.remove("input-ok");
            span.textContent="";
        }
        else if(!resultado){
            //validacion fallida → rojo con mensaje de error
            input.classList.add("input-error");
            input.classList.remove("input-ok");
            span.textContent=regla.mensaje;
        }
        else{
            //validacion exitosa → verde sin mensaje
            input.classList.add("input-ok");
            input.classList.remove("input-error");
            span.textContent="";
        }
    }

}