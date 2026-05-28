<?php
session_start();
require_once 'config.php';

// Validar sesión del cliente
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// 1. Consultar cuál membresía tiene activa el usuario actualmente
$query_usuario = "SELECT membresia_id FROM usuarios WHERE id = $usuario_id";
$res_usuario = mysqli_query($conexion, $query_usuario);
$user_data = mysqli_fetch_assoc($res_usuario);
$membresia_actual_id = $user_data['membresia_id'];

// 2. Traer todos los planes de la tabla membresías
$query_planes = "SELECT * FROM membresias ORDER BY precio ASC";
$resultado_planes = mysqli_query($conexion, $query_planes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Membresías | Divinity Atleta</title>
    <link rel="stylesheet" href="assets/css/estilos.css?v=7.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="fondo-templo">

    <div class="interfaz-contenedor">
        
        <nav class="sidebar-gotica">
        <div class="brand-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
            <h2>DIVINITY ATLETA</h2>
        </div>
        <ul class="menu-enlaces">
            <li><a href="cliente_dashboard.php"> Mi Altar</a></li>
            <li><a href="cliente_membresias.php" class="activo"> Membresías</a></li>
            <li><a href="cliente_prs.php"> Mis Marcas (PRs)</a></li>
            <li><a href="cliente_clases.php"> Clases</a></li>
            <li><a href="cliente_logros.php"> Logros</a></li>
            <li><a href="perfil.php"> Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
        </ul>
    </nav>

        <main class="contenido-principal">
            <header class="encabezado-seccion">
                <h2>GESTIÓN DE MEMBRESÍAS</h2>
                <p class="subtitulo-oro">SINTONIZA TU ACCESO CON LOS PLANES DEL ALTAR</p>
            </header>

            <div class="cuadrula-planes">
                <?php while ($plan = mysqli_fetch_assoc($resultado_planes)): 
                    $es_activa = ($plan['id'] == $membresia_actual_id);
                ?>
                    <div class="cuadro-plan <?php echo $es_activa ? 'plan-activo-borde' : ''; ?>">
                        
                        <?php if ($es_activa): ?>
                            <span class="etiqueta-estado">ACTIVO</span>
                        <?php endif; ?>

                        <h3 class="nombre-plan"><?php echo htmlspecialchars($plan['nombre']); ?></h3>
                        
                        <div class="precio-caja">
                            <span class="moneda">$</span>
                            <span class="monto"><?php echo number_format($plan['precio'], 0, ',', '.'); ?></span>
                            <span class="periodo">/mes</span>
                        </div>

                        <p class="descripcion-texto">
                            <?php echo htmlspecialchars($plan['descripcion'] ?? 'Acceso ilimitado a las instalaciones del templo gótico.'); ?>
                        </p>

                        <div class="acciones-plan">
                            <?php if ($es_activa): ?>
                                <button class="btn-pagar" onclick="accionMembresia('pagar', <?php echo $plan['id']; ?>)">PAGAR PLAN</button>
                                <button class="btn-cancelar" onclick="accionMembresia('cancelar', <?php echo $plan['id']; ?>)">CANCELAR</button>
                            <?php else: ?>
                                <button class="btn-seleccionar" onclick="accionMembresia('cambiar', <?php echo $plan['id']; ?>)">SELECCIONAR</button>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>
        </main>

    </div>

    <script src="assets/js/membresias_cliente.js?v=7.0"></script>
</body>
</html>