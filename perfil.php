<?php
require_once 'config.php';

// 1. Validar que esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);
$rol_id = intval($_SESSION['rol_id']);

// 2. DETECCIÓN INTELIGENTE DEL ALTAR DE REGRESO
$url_regreso = "cliente_dashboard.php"; 

if ($rol_id == 1) {
    $url_regreso = "admin_dashboard.php";  
} elseif ($rol_id == 2) {
    $url_regreso = "profe_rutinas.php";   
}

$mensaje = "";

// 3. ACCIÓN: PROCESAR LA ACTUALIZACIÓN DEL PERFIL (UPDATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_perfil'])) {
    $nuevo_nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $nuevo_email = mysqli_real_escape_string($conexion, $_POST['email']);
    $nueva_clave = $_POST['password'];

    if (!empty($nuevo_nombre) && !empty($nuevo_email)) {
        
        // Si el usuario escribió algo en el campo de contraseña, la actualizamos
        if (!empty($nueva_clave)) {
            // Encriptamos la clave para máxima protección en el Templo
            $clave_encriptada = password_hash($nueva_clave, PASSWORD_DEFAULT);
            $query_update = "UPDATE usuarios SET nombre = '$nuevo_nombre', email = '$nuevo_email', password = '$clave_encriptada' WHERE id = $usuario_id";
        } else {
            // Si lo dejó vacío, solo actualizamos nombre y correo sin tocar la contraseña actual
            $query_update = "UPDATE usuarios SET nombre = '$nuevo_nombre', email = '$nuevo_email' WHERE id = $usuario_id";
        }

        if (mysqli_query($conexion, $query_update)) {
             // AGREGA ESTA LÍNEA AQUÍ ABAJO:
            $_SESSION['usuario_nombre'] = $nuevo_nombre; 
    
            $mensaje = "<p style='color: #00ff00; text-align: center; font-size: 0.85rem; font-weight: bold; margin-bottom: 15px;'> ALMA Y CREDENCIALES ACTUALIZADAS EN EL TEMPLO </p>";
        } else {
            $mensaje = "<p style='color: #ff4444; text-align: center; font-size: 0.85rem; margin-bottom: 15px;'> Error al alterar registros: " . mysqli_error($conexion) . "</p>";
        }
    } else {
        $mensaje = "<p style='color: #ffaa00; text-align: center; font-size: 0.85rem; margin-bottom: 15px;'> No puedes dejar campos vacíos.</p>";
    }
}

// 4. Consultar datos actuales del usuario
$query = "SELECT nombre, email FROM usuarios WHERE id = $usuario_id";
$res = mysqli_query($conexion, $query);
$usuario = mysqli_fetch_assoc($res);

// 5. CONFIGURACIÓN DEL FONDO ESPECÍFICO POR ROL
$imagen_fondo = "alas-rojas.jpg"; 
$color_borde = "#ff0000"; 

if ($rol_id == 1) {
    $imagen_fondo = "alas-doradas.jpg";
    $color_borde = "#ecc94b"; 
} elseif ($rol_id == 2) {
    $imagen_fondo = "alas-cian.jpg";
    $color_borde = "#00ffff";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Mi Perfil</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        .contenedor-perfil {
            max-width: 500px;
            margin: 0 auto;
            padding: 60px 20px;
            box-sizing: border-box;
        }
        .boton-regresar {
            display: inline-block;
            margin-bottom: 25px;
            color: #aaa;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .boton-regresar:hover { 
            color: <?php echo $color_borde; ?>; 
        }
        .input-perfil {
            width: 100%;
            padding: 12px;
            background: rgba(0, 0, 0, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            margin-top: 6px;
            margin-bottom: 20px;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
        }
        .input-perfil:focus {
            border-color: <?php echo $color_borde; ?> !important;
            outline: none;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.05);
        }
        .btn-guardar-perfil {
            width: 100%;
            padding: 14px;
            background: transparent;
            color: #fff;
            border: 1px solid <?php echo $color_borde; ?>;
            font-weight: bold;
            font-family: 'Cinzel', serif;
            cursor: pointer;
            border-radius: 4px;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-guardar-perfil:hover {
            background: <?php echo $color_borde; ?>;
            color: #000;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body style="background-image: linear-gradient(rgba(0, 0, 0, 0.85), rgba(15, 5, 5, 0.92)), url('assets/img/fondos/<?php echo $imagen_fondo; ?>'); background-size: cover; background-position: center center; background-attachment: fixed; background-repeat: no-repeat; color: #ffffff;">

    <div class="contenedor-perfil">
        
        <a href="<?php echo $url_regreso; ?>" class="boton-regresar">← Volver al Altar</a>

        <div class="bloque-formulario" style="background: rgba(20, 20, 20, 0.5); border-top: 3px solid <?php echo $color_borde; ?>; padding: 35px; border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,0.7);">
            
            <h2 style="text-align: center; font-family: 'Cinzel', serif; margin-top: 0; letter-spacing: 1px;">GESTIÓN DEL ALMA</h2>
            <p style="text-align: center; color: #888; font-size: 0.8rem; margin-bottom: 30px; letter-spacing: 0.5px;">INFORMACIÓN DE TU CUENTA EN EL TEMPLO</p>
            
            <?php echo $mensaje; ?>

            <form action="" method="POST">
                
                <div>
                    <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px; font-weight: 600; text-transform: uppercase;">Nombre en el Altar</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" class="input-perfil" required>
                </div>

                <div>
                    <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px; font-weight: 600; text-transform: uppercase;">Correo Electrónico</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" class="input-perfil" required>
                </div>

                <div>
                    <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px; font-weight: 600; text-transform: uppercase;">Nueva Contraseña (Opcional)</label>
                    <input type="password" name="password" placeholder="Déjalo en blanco para conservar la actual" class="input-perfil">
                </div>

                <button type="submit" name="actualizar_perfil" class="btn-guardar-perfil">CONSERVAR CAMBIOS</button>
            </form>
            
        </div>
    </div>

</body>
</html>