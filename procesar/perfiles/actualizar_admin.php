<?php
// Incluimos la conexión centralizada
require_once '../../config.php';

// CONTROL DE SEGURIDAD EXTREMA: Si no es admin, bloqueo absoluto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_id = intval($_POST['usuario_id']);
    $nuevo_role = intval($_POST['nuevo_rol']);

    // NUEVA PROTECCIÓN: Si el ID a editar es el mismo del administrador logueado, rebotamos por seguridad
    if ($usuario_id == $_SESSION['usuario_id']) {
        header('Location: ../../admin_clientes.php?status=error');
        exit;
    }

    if ($usuario_id > 0 && ($nuevo_role >= 1 && $nuevo_role <= 3)) {
        $query_actualizar = "UPDATE usuarios SET rol_id = $nuevo_role WHERE id = $usuario_id";
        
        if (mysqli_query($conexion, $query_actualizar)) {
            header('Location: ../../admin_clientes.php?status=success');
            exit;
        }
    }
}

header('Location: ../../admin_clientes.php?status=error');
exit;
?>