<?php
// Incluimos la conexión centralizada
require_once 'config.php';

// Si ya tiene una sesión activa, lo mandamos directo a su panel según su rol
if (isset($_SESSION['usuario_id']) && isset($_SESSION['rol_id'])) {
    if ($_SESSION['rol_id'] == 1) header('Location: admin_dashboard.php');
    elseif ($_SESSION['rol_id'] == 2) header('Location: profe_dashboard.php');
    else header('Location: cliente_dashboard.php');
    exit;
}

$error = "";

// Procesamos el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conexion, trim($_POST['email']));
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Consulta indexada incluyendo estado_aprobado de forma segura
        $query = "SELECT id, nombre, password, rol_id, estado_aprobado FROM usuarios WHERE email = '$email' LIMIT 1";
        $resultado = mysqli_query($conexion, $query);

        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $usuario = mysqli_fetch_assoc($resultado);
            
            // Verificamos la contraseña encriptada
            if (password_verify($password, $usuario['password'])) {
                
                // COMPROBACIÓN DE ADMISIÓN: Verificar si es entrenador (rol 2) y está bloqueado (estado 0)
                // Usamos isset() por si aún no has creado la columna en la BD, evitando que la pantalla quede en blanco
                if ($usuario['rol_id'] == 2 && isset($usuario['estado_aprobado']) && $usuario['estado_aprobado'] == 0) {
                    $error = "TU SOLICITUD ESTÁ SIENDO EVALUADA POR EL ADMINISTRADOR SUPREMO. ESPERA A QUE TU PACTO SEA ACEPTADO.";
                } else {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    $_SESSION['rol_id'] = $usuario['rol_id'];

                    // Redirección inmediata según el rol
                    if ($usuario['rol_id'] == 1) {
                        header('Location: admin_dashboard.php');
                    } elseif ($usuario['rol_id'] == 2) {
                        header('Location: profe_dashboard.php');
                    } else {
                        header('Location: cliente_dashboard.php');
                    }
                    exit;
                }
            } else {
                $error = "CONTRASEÑA INCORRECTA. INTENTA DE NUEVO, GUERRERO.";
            }
        } else {
            // CUENTA DE RESPALDO EN CALIENTE
            if ($email === 'admin@gymdivinity.com' && $password === 'admin123') {
                $password_segura = password_hash('admin123', PASSWORD_BCRYPT);
                $crear_admin = "INSERT INTO usuarios (nombre, email, password, rol_id, color_tema, estado_aprobado) 
                                VALUES ('Administrador Supremo', 'admin@gymdivinity.com', '$password_segura', 1, '#ffd700', 1)";
                mysqli_query($conexion, $crear_admin);
                
                $error = "CUENTA MAESTRA INICIALIZADA. VUELVE A DARLE CLIC A ENTRAR.";
            } else {
                $error = "EL CORREO ELECTRÓNICO NO ESTÁ REGISTRADO.";
            }
        }
    } else {
        $error = "TODOS LOS CAMPOS SON OBLIGATORIOS.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Metamorphous&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login-registro.css">
</head>
<body>

    <div class="contenedor-login">
        <div class="logo-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo Gymdivinity" class="logo-hardcore">
        </div>

        <h1 class="titulo-gótico">GYMDIVINITY</h1>
        <p class="subtitulo">DISCIPLINA • FUERZA • DEVOCIÓN</p>

        <?php if (!empty($error)): ?>
            <div class="alerta-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="formulario-gótico">
            <div class="grupo-input">
                <label for="email">CORREO ELECTRÓNICO</label>
                <input type="email" id="email" name="email" placeholder="ejemplo@gym.com" required autocomplete="off">
            </div>

            <div class="grupo-input">
                <label for="password">CONTRASEÑA</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="boton-neon">ENTRAR AL TEMPLO</button>
        </form>

        <div class="enlaces-pie">
            <p>¿Eres un nuevo atleta? <a href="registro.php" class="link-neon">Regístrate aquí</a></p>
        </div>
    </div>

</body>
</html>