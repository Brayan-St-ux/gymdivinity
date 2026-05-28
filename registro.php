<?php
require_once 'config.php';

if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] == 1) header('Location: admin_dashboard.php');
    elseif ($_SESSION['rol_id'] == 2) header('Location: profe_dashboard.php');
    else header('Location: cliente_dashboard.php');
    exit;
}

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $email = mysqli_real_escape_string($conexion, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $tipo_registro = $_POST['tipo_registro']; // 'atleta' o 'entrenador'

    if (!empty($nombre) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $mensaje = "LAS CONTRASEÑAS NO COINCIDEN.";
            $tipo_alerta = "error";
        } else {
            $comprobar_email = "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1";
            $resultado_email = mysqli_query($conexion, $comprobar_email);

            if (mysqli_num_rows($resultado_email) > 0) {
                $mensaje = "EL CORREO YA SE ENCUENTRA REGISTRADO.";
                $tipo_alerta = "error";
            } else {
                $password_encriptada = password_hash($password, PASSWORD_BCRYPT);
                
                if ($tipo_registro === 'entrenador') {
                    $biografia = mysqli_real_escape_string($conexion, trim($_POST['biografia']));
                    // Rol 2 (Instructor), pero con estado_aprobado = 0 (Bloqueado/Pendiente)
                    $insertar_usuario = "INSERT INTO usuarios (nombre, email, password, rol_id, color_tema, biografia, estado_aprobado) 
                                         VALUES ('$nombre', '$email', '$password_encriptada', 2, '#00ffff', '$biografia', 0)";
                } else {
                    // Registro normal de atleta: Rol 3 y aprobado por defecto (1)
                    $insertar_usuario = "INSERT INTO usuarios (nombre, email, password, rol_id, color_tema, estado_aprobado) 
                                         VALUES ('$nombre', '$email', '$password_encriptada', 3, '#ff0000', 1)";
                }
                
                if (mysqli_query($conexion, $insertar_usuario)) {
                    if ($tipo_registro === 'entrenador') {
                        $mensaje = "¡PÁCTO ENVIADO! TU SOLICITUD ESTÁ EN EVALUACIÓN DE LA ADMINISTRACIÓN SUPREMA.";
                        $tipo_alerta = "exito";
                    } else {
                        $mensaje = "¡REGISTRO EXITOSO, GUERRERO! YA PUEDES INICIAR SESIÓN.";
                        $tipo_alerta = "exito";
                    }
                } else {
                    $mensaje = "ERROR EN EL SISTEMA: No se pudo completar el registro.";
                    $tipo_alerta = "error";
                }
            }
        }
    } else {
        $mensaje = "TODOS LOS CAMPOS SON OBLIGATORIOS.";
        $tipo_alerta = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Unirse al Templo</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login-registro.css">
    <link rel="stylesheet" href="assets/css/intercambio_login.css">
</head>
<body>

    <div class="contenedor-login">
        <div class="logo-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo Gymdivinity" class="logo-hardcore">
        </div>

        <h1 class="titulo-gótico">UNIRSE AL TEMPLO</h1>
        <p class="subtitulo" id="subtitulo-registro">EMPIEZA TU TRANSFORMACIÓN</p>

        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo $tipo_alerta == 'error' ? 'alerta-error' : 'alerta-exito'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form action="registro.php" method="POST" class="formulario-gótico animacion-fade" id="form-atleta">
            <input type="hidden" name="tipo_registro" value="atleta">
            
            <div class="grupo-input">
                <label>NOMBRE COMPLETO</label>
                <input type="text" name="nombre" placeholder="Tu nombre y apellido" required autocomplete="off">
            </div>

            <div class="grupo-input">
                <label>CORREO ELECTRÓNICO</label>
                <input type="email" name="email" placeholder="ejemplo@gym.com" required autocomplete="off">
            </div>

            <div class="grupo-input">
                <label>CONTRASEÑA</label>
                <input type="password" class="clase-password-evaluar" name="password" placeholder="Mínimo 6 caracteres" required>
                <div class="barra-fuerza-dinamica" style="height: 3px; width: 0%; margin-top: 5px; transition: all 0.3s ease; border-radius: 2px;"></div>
                <small class="texto-fuerza-dinamico" style="font-size: 0.7rem; color: #b3b3b3; display: block; margin-top: 4px;"></small>
            </div>

            <div class="grupo-input">
                <label>CONFIRMAR CONTRASEÑA</label>
                <input type="password" name="confirm_password" placeholder="Repite tu contraseña" required>
            </div>

            <button type="submit" class="boton-neon">CREAR CUENTA DE ATLETA</button>
            
            <div class="enlaces-pie">
                <p>¿Quieres postularte como instructor? <a href="#" id="ir-a-entrenador" class="link-neon" style="color:var(--neon-dorado);">Enviar Solicitud aquí</a></p>
            </div>
        </form>

        <form action="registro.php" method="POST" class="formulario-gótico oculto-bloque" id="form-entrenador">
            <input type="hidden" name="tipo_registro" value="entrenador">
            
            <div class="grupo-input">
                <label>NOMBRE COMPLETO DEL ASPIRANTE</label>
                <input type="text" name="nombre" placeholder="Nombre y apellido del instructor" required autocomplete="off">
            </div>

            <div class="grupo-input">
                <label>CORREO DE CONTACTO</label>
                <input type="email" name="email" placeholder="instructor@gym.com" required autocomplete="off">
            </div>

            <div class="grupo-input">
                <label>CONOCIMIENTOS Y EXPERIENCIA (¿QUÉ SABES HACER?)</label>
                <textarea name="biografia" placeholder="Describe tus especialidades, certificaciones y disciplinas que dominas..." required></textarea>
            </div>

            <div class="grupo-input">
                <label>CONTRASEÑA DE ACCESO</label>
                <input type="password" class="clase-password-evaluar" name="password" placeholder="Mínimo 6 caracteres" required>
                <div class="barra-fuerza-dinamica" style="height: 3px; width: 0%; margin-top: 5px; transition: all 0.3s ease; border-radius: 2px;"></div>
                <small class="texto-fuerza-dinamico" style="font-size: 0.7rem; color: #b3b3b3; display: block; margin-top: 4px;"></small>
            </div>

            <div class="grupo-input">
                <label>CONFIRMAR CONTRASEÑA</label>
                <input type="password" name="confirm_password" placeholder="Repite tu contraseña" required>
            </div>

            <button type="submit" class="boton-neon" style="border-color: var(--neon-dorado); text-shadow: 0 0 5px rgba(255,215,0,0.5);">ENVIAR PACTO DE ENTRENADOR</button>
            
            <div class="enlaces-pie">
                <p>¿Quieres registrarte como cliente? <a href="#" id="ir-a-atleta" class="link-neon">Regresar a Atleta</a></p>
            </div>
        </form>

        <div class="enlaces-pie" style="margin-top: 15px;">
            <p>¿Ya tienes una cuenta activa? <a href="login.php" class="link-neon">Inicia sesión aquí</a></p>
        </div>
    </div>

    <script src="assets/js/validaciones.js"></script>
    <script src="assets/js/intercambio_registro.js"></script>
</body>
</html>