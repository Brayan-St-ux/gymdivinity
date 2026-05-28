<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    echo json_encode(["error" => "Sesión inválida o no autorizado"]);
    exit;
}

if (isset($_GET['clase_id'])) {
    $clase_id = intval($_GET['clase_id']);
    
    // CONSULTA PURIFICADA: Si tu columna en la base de datos es 'email', cámbialo aquí.
    // Si no estás seguro, dejamos solo 'u.nombre' para asegurar que conecte sin romperse.
    $query = "SELECT u.nombre 
              FROM inscripciones_clases ic 
              INNER JOIN usuarios u ON ic.usuario_id = u.id 
              WHERE ic.horario_id = $clase_id 
              ORDER BY u.nombre ASC";
              
    $resultado = mysqli_query($conexion, $query);
    
    if (!$resultado) {
        echo json_encode(["error" => "Error al consultar los guerreros"]);
        exit;
    }
    
    $alumnos = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $alumnos[] = [
            'nombre' => htmlspecialchars($row['nombre'])
        ];
    }
    
    echo json_encode($alumnos);
    exit;
} else {
    echo json_encode(["error" => "Falta el ID de la clase"]);
    exit;
}
?>