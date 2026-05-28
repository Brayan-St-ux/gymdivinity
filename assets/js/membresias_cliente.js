function accionMembresia(tipo, planId) {
    let confirmacionTexto = "";

    if (tipo === 'cancelar') {
        confirmacionTexto = "⚠️ ¿Seguro que deseas cancelar tu membresía actual? Perderás los privilegios de atleta.";
    } else if (tipo === 'cambiar') {
        confirmacionTexto = "🔄 ¿Deseas reemplazar tu membresía actual por este nuevo plan? El cambio se procesará de inmediato.";
    } else if (tipo === 'pagar') {
        confirmacionTexto = "💳 ¿Proceder al portal de pago para renovar este plan?";
    }

    if (!confirm(confirmacionTexto)) return;

    // Si es solo simular el pago
    if (tipo === 'pagar') {
        alert("Redireccionando a la pasarela de pago Divinity... (Simulación)");
        return;
    }

    // Petición asíncrona al backend para cambiar o cancelar
    fetch('procesar_cambio_membresia.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `accion=${tipo}&plan_id=${planId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload(); // Recarga en tiempo real para ver el nuevo cuadro activo
        } else {
            alert("Error del sistema: " + data.message);
        }
    })
    .catch(error => {
        alert("Error de comunicación con el altar de membresías.");
    });
}