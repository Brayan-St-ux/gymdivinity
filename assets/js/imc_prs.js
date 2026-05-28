// ==========================================================================
// CONTROLADOR DEL MODAL INTERACTIVO DE CALCULADORA IMC
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
    const modalImc = document.getElementById("modalIMC");
    const btnAbrirImc = document.getElementById("btnIMC");
    const btnCerrarImc = document.getElementById("cerrarModalIMC");

    // Abrir ventana flotante
    if (btnAbrirImc) {
        btnAbrirImc.addEventListener("click", () => {
            modalImc.classList.add("activo");
        });
    }

    // Cerrar ventana flotante mediante la X
    if (btnCerrarImc) {
        btnCerrarImc.addEventListener("click", () => {
            modalImc.classList.remove("activo");
            limpiarCamposIMC();
        });
    }

    // Cerrar si hacen click en el fondo oscuro exterior
    window.addEventListener("click", (event) => {
        if (event.target === modalImc) {
            modalImc.classList.remove("activo");
            limpiarCamposIMC();
        }
    });
});

// Resetea el formulario interno al cerrar
function limpiarCamposIMC() {
    document.getElementById('imc-peso').value = '';
    document.getElementById('imc-altura').value = '';
    document.getElementById('resultado-imc').style.display = 'none';
}

// Lógica matemática y procesamiento del IMC
function calcularIMC() {
    const peso = parseFloat(document.getElementById('imc-peso').value);
    const altura = parseFloat(document.getElementById('imc-altura').value);
    
    const contenedorResultado = document.getElementById('resultado-imc');
    const txtValor = document.getElementById('valor-imc');
    const txtEstado = document.getElementById('estado-imc');

    if (!peso || !altura || altura <= 0 || peso <= 0) {
        alert("Por favor, introduce parámetros válidos de masa corporal y estatura.");
        return;
    }

    // Operación matemática básica del IMC
    const imc = (peso / (altura * altura)).toFixed(1);
    txtValor.innerText = imc;

    let estado = "";
    let claseColor = "";

    // Clasificación de rangos
    if (imc < 18.5) {
        estado = "DÉFICIT METABÓLICO (Bajo Peso)";
        claseColor = "estado-azul";
    } else if (imc >= 18.5 && imc <= 24.9) {
        estado = "TEMPLO ÓPTIMO (Peso Saludable)";
        claseColor = "estado-verde";
    } else if (imc >= 25 && imc <= 29.9) {
        estado = "HIPERTROFIA / VOLUMEN (Sobrepeso)";
        claseColor = "estado-amarillo";
    } else {
        estado = "ALTA CARGA ADIPOSA (Obesidad)";
        claseColor = "estado-rojo";
    }

    txtEstado.innerText = estado;
    txtEstado.className = claseColor; 
    contenedorResultado.style.display = "block";
}s