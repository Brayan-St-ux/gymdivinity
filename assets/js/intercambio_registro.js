document.addEventListener("DOMContentLoaded", function() {
    const linkEntrenador = document.getElementById("ir-a-entrenador");
    const linkAtleta = document.getElementById("ir-a-atleta");
    const formAtleta = document.getElementById("form-atleta");
    const formEntrenador = document.getElementById("form-entrenador");
    const subtitulo = document.getElementById("subtitulo-registro");

    if (linkEntrenador && linkAtleta && formAtleta && formEntrenador) {
        
        // Cambiar a vista Entrenador
        linkEntrenador.addEventListener("click", function(e) {
            e.preventDefault();
            formAtleta.classList.add("oculto-bloque");
            formAtleta.classList.remove("animacion-fade");
            
            formEntrenador.classList.remove("oculto-bloque");
            formEntrenador.classList.add("animacion-fade");
            subtitulo.textContent = "CONVOCATORIA DE INSTRUCTORES";
            subtitulo.style.color = "#ffd700";
        });

        // Cambiar a vista Atleta
        linkAtleta.addEventListener("click", function(e) {
            e.preventDefault();
            formEntrenador.classList.add("oculto-bloque");
            formEntrenador.classList.remove("animacion-fade");
            
            formAtleta.classList.remove("oculto-bloque");
            formAtleta.classList.add("animacion-fade");
            subtitulo.textContent = "EMPIEZA TU TRANSFORMACIÓN";
            subtitulo.style.color = "#ffd700";
        });
    }
});