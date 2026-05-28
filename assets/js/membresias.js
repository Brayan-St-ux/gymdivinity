function procesarAccion(accion, planId) {
    let mensajeConfirmacion = "¿Estás seguro de realizar esta acción?";
    
    if (accion === 'cancelar') {
        mensajeConfirmacion = "⚠️ ATENCIÓN: ¿Seguro que deseas cancelar tu membresía actual? Perderás acceso al templo.";
    } else if (accion === 'reemplazar') {
        mensajeConfirmacion = "🔄 ¿Deseas reemplazar tu membresía actual por este nuevo plan divino? Los cambios se aplicarán de inmediato.";
    }

    if (!confirm(mensajeConfirmacion)) return;

    // Petición Fetch hacia el procesador backend puro
    fetch('procesar_membresia.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `accion=${accion}&plan_id=${planId}`
    })
    .then(res => {
        if (!res.ok) throw new Error("Error en la señal del altar.");
        return res.json();
    })
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload(); // Recarga la interfaz en tiempo real para ver los cambios
        } else {
            alert("Fallo: " + data.message);
        }
    })
    .catch(err => {
        alert("Error crítico de comunicación con el procesador de membresías.");
    });
}