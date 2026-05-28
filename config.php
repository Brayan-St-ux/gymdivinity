<?php
// Evitar duplicación de sesiones y optimizar carga de memoria en el servidor
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Credenciales base de datos (XAMPP local)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gym_divinity');

$conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conexion) {
    die("Error crítico de infraestructura: No se pudo conectar a la base de datos de Gymdivinity: " . mysqli_connect_error());
}

// Codificación para soporte total de caracteres, tildes y Ñ
mysqli_set_charset($conexion, "utf8mb4");

/**
 * Función global para extraer el color neón personalizado de la cuenta actual.
 * Si el usuario no ha iniciado sesión o no tiene color, retorna el rojo neón gótico por defecto.
 */
function obtenerColorTema() {
    global $conexion;
    if (isset($_SESSION['usuario_id'])) {
        $id = intval($_SESSION['usuario_id']);
        $query = "SELECT color_tema FROM usuarios WHERE id = $id";
        $resultado = mysqli_query($conexion, $query);
        if ($resultado && $fila = mysqli_fetch_assoc($resultado)) {
            return $fila['color_tema'];
        }
    }
    return '#ff0000'; // Rojo neón base si no hay sesión activa
}
?>