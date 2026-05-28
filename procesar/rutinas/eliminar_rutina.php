<?php
// Incluimos la conexión centralizada
require_once '../../config.php';

// CONTROL DE SEGURIDAD: Solo entrenadores autorizados (rol 2) pueden purgar rutinas
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ../../login.php');
    exit;
}

// Validamos que los parámetros ID de rutina y ID de alumno existan en la URL
if (isset($_GET['id']) && isset($_GET['alumno_id'])) {
    $id_rutina = intval($_GET['id']);
    $alumno_id = intval($_GET['alumno_id']);

    if ($id_rutina > 0) {
        // Ejecutamos la consulta DELETE indexada
        $query_eliminar = "DELETE FROM rutinas WHERE id = $id_rutina";
        
        if (mysqli_query($conexion, $query_eliminar)) {
            // Éxito: Regresa a la pizarra manteniendo al alumno en pantalla
            header("Location: ../../profe_rutinas.php?alumno_id=$alumno_id");
            exit;
        }
    }
}

// Si hubo alteración de datos, rebota sin cambios
header("Location: ../../profe_rutinas.php");
exit;
?>