document.addEventListener("DOMContentLoaded", function () {
    // Intercambio de pestañas (Gratuito / Pago)
    const tabG = document.getElementById('tab-gratis');
    const tabP = document.getElementById('tab-pago');
    const panG = document.getElementById('panel-gratis');
    const panP = document.getElementById('panel-pago');

    if (tabG && tabP) {
        tabG.addEventListener('click', () => {
            tabG.classList.add('activo');
            tabP.classList.remove('activo');
            panG.classList.remove('oculto-inicial');
            panP.classList.add('oculto-inicial');
        });

        tabP.addEventListener('click', () => {
            tabP.classList.add('activo');
            tabG.classList.remove('activo');
            panP.classList.remove('oculto-inicial');
            panG.classList.add('oculto-inicial');
        });
    }

    // Modal de Monitoreo de Alumnos en vivo
    const modal = document.getElementById('modal-guerreros');
    const contenedorLista = document.getElementById('lista-guerreros-ajax');
    const btnCerrar = document.getElementById('btn-cerrar-admin');
    
    if (modal && contenedorLista) {
        document.querySelectorAll('.btn-ver-guerreros').forEach(btn => {
            btn.addEventListener('click', () => {
                const claseId = btn.getAttribute('data-id');
                contenedorLista.innerHTML = "<p class='texto-carga-altar'>Consultando el altar...</p>";
                modal.classList.add('activo');

                // Petición al backend
                fetch(`get_alumnos_clase.php?clase_id=${claseId}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Error en respuesta de red');
                        return res.json();
                    })
                    .then(data => {
                        if (data.error) {
                            contenedorLista.innerHTML = `<p class='texto-error-altar'>${data.error}</p>`;
                            return;
                        }

                        if (data.length === 0) {
                            contenedorLista.innerHTML = "<p class='texto-vacio-altar'>Ningún guerrero ha seleccionado este horario aún.</p>";
                        } else {
                            let html = "<ul>";
                            data.forEach(alumno => {
                                html += `<li>⚡ ${alumno.nombre} <span>(${alumno.correo})</span></li>`;
                            });
                            html += "</ul>";
                            contenedorLista.innerHTML = html;
                        }
                    })
                    .catch(err => {
                        contenedorLista.innerHTML = "<p class='texto-error-altar'>Error de conexión con el altar.</p>";
                    });
            });
        });

        if (btnCerrar) {
            btnCerrar.addEventListener('click', () => {
                modal.classList.remove('activo');
            });
        }
    }
});