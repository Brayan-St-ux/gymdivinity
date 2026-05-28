<?php
// Incluimos la conexión y control de sesiones
require_once 'config.php';

// CONTROL DE ACCESO SEGURO: Si no ha iniciado sesión o no es administrador, rebote inmediato
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$query_user = "SELECT nombre FROM usuarios WHERE id = $usuario_id";
$res_user = mysqli_query($conexion, $query_user);
$user_data = mysqli_fetch_assoc($res_user);
$nombre_admin = $user_data['nombre'];

// Extraemos métricas en vivo mediante consultas de alto rendimiento
$total_clientes = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuarios WHERE rol_id = 3"))['total'];
$total_profesores = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuarios WHERE rol_id = 2"))['total'];
$total_clases = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM horarios"))['total'];
$total_logros = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM medallas"))['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Panel de Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body class="fondo-staff-admin">

    <nav class="sidebar-gotica">
        <div class="brand-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
            <h2>DIVINITY ADMIN</h2>
        </div>
        <ul class="menu-enlaces">
            <li><a href="admin_dashboard.php" class="activo"> Inicio</a></li>
            <li><a href="admin_clientes.php"> Atletas</a></li>
            <li><a href="admin_profesores.php"> Entrenadores</a></li>
            <li><a href="admin_membresias.php"> Membresías</a></li>
            <li><a href="admin_clases.php"> Cronograma</a></li>
            <li><a href="admin_logros.php"> Crear Logros</a></li>
            <li><a href="perfil.php"> Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
        </ul>
    </nav>

    <main class="contenido-principal">
        <header class="encabezado-dashboard">
            <div class="saludo">
                <h1>BIENVENIDO, <?php echo strtoupper($nombre_admin); ?></h1>
                <p>ESTADO DE LA INFRAESTRUCTURA MÁXIMA</p>
            </div>
            <div class="rango-badge">ADMINISTRADOR SUPREMO</div>
        </header>
        <section class="grilla-kpi">
            <div class="tarjeta-kpi">
                <div class="icono-kpi"></div>
                <div class="datos-kpi">
                    <h3>ATLETAS REGISTRADOS</h3>
                    <p class="numero-kpi"><?php echo $total_clientes; ?></p>
                </div>
            </div>

            <div class="tarjeta-kpi">
                <div class="icono-kpi"></div>
                <div class="datos-kpi">
                    <h3> ENTRENADOR</h3>
                    <p class="numero-kpi"><?php echo $total_profesores; ?></p>
                </div>
            </div>

            <div class="tarjeta-kpi">
                <div class="icono-kpi"></div>
                <div class="datos-kpi">
                    <h3>CLASES ACTIVAS</h3>
                    <p class="numero-kpi"><?php echo $total_clases; ?></p>
                </div>
            </div>

            <div class="tarjeta-kpi">
                <div class="icono-kpi"></div>
                <div class="datos-kpi">
                    <h3>MEDALLAS CREADAS</h3>
                    <p class="numero-kpi"><?php echo $total_logros; ?></p>
                </div>
            </div>
        </section>

        <section class="muro-noticias">
            <h2>CENTRAL DE OPERACIONES</h2>
            <p>Desde este módulo maestro tienes el poder absoluto para auditar las membresías vencidas de los clientes, asignar nuevos instructores a los salones de pesas y forjar nuevos logros para gamificar el progreso de la comunidad.</p>
        </section>
    </main>

</body>
</html>