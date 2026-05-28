document.addEventListener("DOMContentLoaded", function() {
    
    // --- LÓGICA MODAL MODIFICAR ---
    const modalModificar = document.getElementById("modal-modificar");
    const inputId = document.getElementById("modal-edit-id");
    const inputNombre = document.getElementById("modal-edit-nombre");
    const inputEmail = document.getElementById("modal-edit-email");

    document.querySelectorAll(".btn-abrir-modificar").forEach(boton => {
        boton.addEventListener("click", function(e) {
            e.preventDefault();
            inputId.value = this.getAttribute("data-id");
            inputNombre.value = this.getAttribute("data-nombre");
            inputEmail.value = this.getAttribute("data-email");
            modalModificar.classList.add("activo");
        });
    });

    // --- LÓGICA MODAL ELIMINAR ---
    const modalEliminar = document.getElementById("modal-eliminar");
    const btnConfirmarBorrar = document.getElementById("btn-confirmar-borrar");
    const textoNombreBorrar = document.getElementById("nombre-atleta-borrar");

    document.querySelectorAll(".btn-abrir-borrar").forEach(boton => {
        boton.addEventListener("click", function(e) {
            e.preventDefault();
            const id = this.getAttribute("data-id");
            const nombre = this.getAttribute("data-nombre");

            textoNombreBorrar.textContent = nombre.toUpperCase();
            btnConfirmarBorrar.href = "admin_clientes.php?borrar_id=" + id;
            modalEliminar.classList.add("activo");
        });
    });

    // --- LÓGICA DE CIERRE OPTIMIZADA ---
    // Escucha directamente a los botones que tengan la clase 'btn-cerrar-modal'
    document.querySelectorAll(".btn-cerrar-modal").forEach(boton => {
        boton.addEventListener("click", function(e) {
            e.preventDefault(); // Evita cualquier acción por defecto
            if(modalModificar) modalModificar.classList.remove("activo");
            if(modalEliminar) modalEliminar.classList.remove("activo");
        });
    });

    // Cerrar también si hacen click en el fondo negro exterior
    document.querySelectorAll(".modal-overlay").forEach(overlay => {
        overlay.addEventListener("click", function(e) {
            if (e.target === this) {
                if(modalModificar) modalModificar.classList.remove("activo");
                if(modalEliminar) modalEliminar.classList.remove("activo");
            }
        });
    });
});