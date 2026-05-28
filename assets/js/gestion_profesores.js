document.addEventListener("DOMContentLoaded", function() {
    
    // --- LÓGICA DE INTERCAMBIO DE TABLAS ---
    const btnActivos = document.getElementById("btn-vista-activos");
    const btnSolicitudes = document.getElementById("btn-vista-solicitudes");
    const tablaActivos = document.getElementById("contenedor-tabla-activos");
    const tablaSolicitudes = document.getElementById("contenedor-tabla-solicitudes");

    if (btnActivos && btnSolicitudes && tablaActivos && tablaSolicitudes) {
        btnActivos.addEventListener("click", function() {
            btnSolicitudes.classList.remove("activo");
            btnActivos.classList.add("activo");
            tablaSolicitudes.classList.add("oculto-inicial");
            tablaActivos.classList.remove("oculto-inicial");
        });

        btnSolicitudes.addEventListener("click", function() {
            btnActivos.classList.remove("activo");
            btnSolicitudes.classList.add("activo");
            tablaActivos.classList.add("oculto-inicial");
            tablaSolicitudes.classList.remove("oculto-inicial");
        });
    }

    // --- LÓGICA MODAL MODIFICAR INSTRUCTOR ---
    const modalModificar = document.getElementById("modal-modificar");
    const inputId = document.getElementById("modal-edit-id");
    const inputNombre = document.getElementById("modal-edit-nombre");
    const inputEmail = document.getElementById("modal-edit-email");
    const inputBio = document.getElementById("modal-edit-biografia");

    document.querySelectorAll(".btn-abrir-modificar").forEach(boton => {
        boton.addEventListener("click", function(e) {
            e.preventDefault();
            inputId.value = this.getAttribute("data-id");
            inputNombre.value = this.getAttribute("data-nombre");
            inputEmail.value = this.getAttribute("data-email");
            inputBio.value = this.getAttribute("data-biografia");
            modalModificar.classList.add("activo");
        });
    });

    // --- LÓGICA MODAL ELIMINAR INSTRUCTOR ---
    const modalEliminar = document.getElementById("modal-eliminar");
    const btnConfirmarBorrar = document.getElementById("btn-confirmar-borrar");
    const textoNombreBorrar = document.getElementById("nombre-profesor-borrar");

    document.querySelectorAll(".btn-abrir-borrar").forEach(boton => {
        boton.addEventListener("click", function(e) {
            e.preventDefault();
            const id = this.getAttribute("data-id");
            const nombre = this.getAttribute("data-nombre");

            textoNombreBorrar.textContent = nombre.toUpperCase();
            btnConfirmarBorrar.href = "admin_profesores.php?borrar_id=" + id;
            modalEliminar.classList.add("activo");
        });
    });

    // --- LÓGICA DE CIERRE DE MODALES ---
    document.querySelectorAll(".btn-cerrar-modal").forEach(boton => {
        boton.addEventListener("click", function(e) {
            e.preventDefault();
            if(modalModificar) modalModificar.classList.remove("activo");
            if(modalEliminar) modalEliminar.classList.remove("activo");
        });
    });

    // Cerrar al golpear el fondo exterior
    document.querySelectorAll(".modal-overlay").forEach(overlay => {
        overlay.addEventListener("click", function(e) {
            if (e.target === this) {
                if(modalModificar) modalModificar.classList.remove("activo");
                if(modalEliminar) modalEliminar.classList.remove("activo");
            }
        });
    });
});