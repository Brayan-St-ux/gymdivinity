<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "message" => "Sesión no válida"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$accion = $_POST['accion'] ?? '';
$plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;

if ($accion === 'cancelar') {
    $sql = "UPDATE usuarios SET membresia_id = NULL WHERE id = $usuario_id";
    if (mysqli_query($conexion, $sql)) {
        echo json_encode(["status" => "success", "message" => "Membresía cancelada. Tu altar quedó libre."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No se pudo cancelar el plan."]);
    }
    exit;
} 

if ($accion === 'cambiar') {
    // Validar que el plan de reemplazo exista
    $check = mysqli_query($conexion, "SELECT id FROM membresias WHERE id = $plan_id");
    if (mysqli_num_rows($check) == 0) {
        echo json_encode(["status" => "error", "message" => "El plan seleccionado no existe."]);
        exit;
    }

    // Cambiar la membresía antigua por la nueva de un solo golpe
    $sql = "UPDATE usuarios SET membresia_id = $plan_id WHERE id = $usuario_id";
    if (mysqli_query($conexion, $sql)) {
        echo json_encode(["status" => "success", "message" => "¡Membresía reemplazada con éxito! Disfruta tu nuevo rango."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error interno al actualizar la membresía."]);
    }
    exit;
}

echo json_encode(["status" => "error", "message" => "Acción inválida"]);
?>