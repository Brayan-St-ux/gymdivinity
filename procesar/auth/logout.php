<?php
// Inicializamos el entorno de sesión para poder destruirlo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Limpiamos todas las variables globales de la sesión actual
$_SESSION = array();

// Si se desea destruir la cookie de sesión del navegador, la borramos
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destruimos la sesión en el servidor de forma definitiva
session_destroy();

// Redirección limpia e inmediata a la pantalla de acceso gótica
header("Location: ../../login.php");
exit;
?>