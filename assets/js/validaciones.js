document.addEventListener("DOMContentLoaded", function() {
    // Buscamos todas las contraseñas que tengan la clase evaluadora
    document.querySelectorAll(".clase-password-evaluar").forEach(input => {
        // Encontramos la barra y texto hermanos dentro de su propio contenedor
        const contenedor = input.parentElement;
        const barraFuerza = contenedor.querySelector(".barra-fuerza-dinamica");
        const textoFuerza = contenedor.querySelector(".texto-fuerza-dinamico");

        if (barraFuerza && textoFuerza) {
            input.addEventListener("input", function() {
                const clave = input.value;
                let fuerza = 0;

                if (clave.length >= 6) fuerza += 30;
                if (/[A-Z]/.test(clave)) fuerza += 35;
                if (/[0-9]/.test(clave)) fuerza += 35;

                if (clave.length === 0) {
                    barraFuerza.style.width = "0%";
                    textoFuerza.textContent = "";
                } else if (fuerza < 65) {
                    barraFuerza.style.width = "35%";
                    barraFuerza.style.backgroundColor = "#ff0000";
                    textoFuerza.textContent = "SEGURIDAD DE LA CLAVE: DÉBIL 💀";
                    textoFuerza.style.color = "#ff4d4d";
                } else if (fuerza < 100) {
                    barraFuerza.style.width = "65%";
                    barraFuerza.style.backgroundColor = "#ffd700";
                    textoFuerza.textContent = "SEGURIDAD DE LA CLAVE: ACEPTABLE 🛡️";
                    textoFuerza.style.color = "#ffd700";
                } else {
                    barraFuerza.style.width = "100%";
                    barraFuerza.style.backgroundColor = "#00ff00";
                    textoFuerza.textContent = "SEGURIDAD DE LA CLAVE: INQUEBRANTABLE 🔥";
                    textoFuerza.style.color = "#4df44d";
                }
            });
        }
    });
});