class DragDrop{
    input;
    div;//el div es lo que el usuario va a ver, dentro de el va a ver un span que va a 
    //tener el texto de "arrastra aqui o selecciona un archivo" o el nombre del archivo
    //insertado por el usuario, va a ir variando segun se inserten/saquen archivos 
    //ademas dentro del div va a estar tambien el input original del html
    span;
    constructor(pInput){
        this.input=document.querySelector(pInput);
        if(!this.input || this.input.type !== "file")
            console.error("DragDrop: el elemento debe ser un input de tipo file");
        else{
            this.div=ConstructorElementos.nuevoElemento("div", "", {class: "drag-drop-area"});
            this.span=ConstructorElementos.nuevoElemento("span", "Arrastrá una imagen acá o hacé click para seleccionar");
            this.input.parentNode.insertBefore(this.div, this.input);//this.input.parentNode selecciona el elemento
            //padre del input (en este caso el fieldset) y .insertBefore(this.div, this.input) 
            //inserta el div justo antes del input 
            this.div.appendChild(this.input);//se pone el input dentro del div, el usuario ve un div no ve un input
            this.div.appendChild(this.span);
            this.div.addEventListener("click", ()=>this.input.click());//cuando el usuario
            //hace click sobre el div, simulamos un click sobre el input que esta dentro de el
            //que es el input el que abre el sistema de archivos del so
            this.div.addEventListener("dragover",(e)=>e.preventDefault());//si el usuario 
            //arrastra el archivo por encima del div, se detecta y se hace un e.preventDefault()
            //para habilitar el drop. e.preventDefault() anula el comportamiento predeterminado 
            //del navegador que en este caso para el dragover es rechazar el drop, entonces de esa
            //manera habilitamos el drop
            this.input.addEventListener("click", (e) => e.stopPropagation());

            this.div.addEventListener("drop", (e)=>{
                e.preventDefault();
                let archivo=e.dataTransfer.files[0];//obtengo el archivo dropeado
                let dataTransf=new DataTransfer();//crep un nuevo dataTransfer
                dataTransf.items.add(archivo);//le agrego el archivo
                this.input.files=dataTransf.files;//le asigno el archivo al input encapsulado dentro del div
                //de esta manera el formValidation puede validar del input si es el tipo de archivo correcto
                this.span.textContent=this.input.files[0].name;
                this.input.dispatchEvent(new Event("blur"));

            });//por que no se le asigna e.dataTransfer.files[0] directamente a input.files?
            //no se puede asignar una lista de archivos (FileList), es una limitacion de 
            //los navegadores pero si se puede asignar un nuevo DataTransfer con el archivo dropeado
            this.input.addEventListener("change", ()=>{//si el input cambia, ya sea 
            //porque se agrego un archivo o se elimino el archivo, se cambia el contenido del div
                if(this.input.files.length>0){
                    this.span.textContent =this.input.files[0].name;
                    this.input.dispatchEvent(new Event("blur"));
                }
                else{ 
                    this.span.textContent= "Arrastrá una imagen acá o hacé click para seleccionar";
                    this.input.dispatchEvent(new Event("blur"));
                }
            });
        }
    }
}