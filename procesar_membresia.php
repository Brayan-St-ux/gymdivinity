<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Inicie sesión."]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$accion = $_POST['accion'] ?? '';
$plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;

if ($accion === 'cancelar') {
    // Quitar la membresía asignando NULL
    $sql = "UPDATE usuarios SET membresia_id = NULL WHERE id = $usuario_id";
    if (mysqli_query($conexion, $sql)) {
        echo json_encode(["status" => "success", "message" => "Membresía cancelada con éxito. Tu altar ha quedado libre."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No se pudo procesar la cancelación."]);
    }
    exit;
    
} else if ($accion === 'reemplazar' || $accion === 'adquirir') {
    if ($plan_id <= 0) {
        echo json_encode(["status" => "error", "message" => "ID de plan inválido."]);
        exit;
    }
    
    // Validar primero que el plan exista en el catálogo para evitar inconsistencias
    $verificar_plan = mysqli_query($conexion, "SELECT id FROM membresias WHERE id = $plan_id");
    if (mysqli_num_rows($verificar_plan) === 0) {
        echo json_encode(["status" => "error", "message" => "El plan divino seleccionado no existe."]);
        exit;
    }

    // Actualizar directamente la membresía del usuario
    $sql = "UPDATE usuarios SET membresia_id = $plan_id WHERE id = $usuario_id";
    if (mysqli_query($conexion, $sql)) {
        $msg = ($accion === 'reemplazar') ? "¡Membresía mutada con éxito!" : "¡Membresía adquirida con éxito!";
        echo json_encode(["status" => "success", "message" => $msg]);
    } else {
        echo json_encode(["status" => "error", "message" => "Fallo de actualización interna en el sistema."]);
    }
    exit;
}

echo json_encode(["status" => "error", "message" => "Acción no reconocida."]);
?>