<?php
// Incluimos la conexión centralizada y el control de sesiones
require_once 'config.php';

// CONTROL DE ACCESO: Exclusivo para Clientes (rol 3)
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header('Location: login.php');
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);
$fecha_actual = new DateTime();

// 1. CONSULTA DE PERFIL (Tabla usuarios)
$query_usuario = "SELECT nombre, color_tema FROM usuarios WHERE id = $usuario_id";
$resultado_usuario = mysqli_query($conexion, $query_usuario);
$datos_atleta = mysqli_fetch_assoc($resultado_usuario);

// 2. CONSULTA DE MEMBRESÍA (Trae la última registrada en el historial del usuario)
$query_membresia = "SELECT um.fecha_vencimiento, um.estado, m.nombre as plan 
                    FROM usuario_membresias um 
                    JOIN membresias m ON um.membresia_id = m.id 
                    WHERE um.usuario_id = $usuario_id 
                    ORDER BY um.id DESC LIMIT 1";
$resultado_membresia = mysqli_query($conexion, $query_membresia);
$datos_membresia = mysqli_fetch_assoc($resultado_membresia);

// Lógica del contador de días restantes dinámico
$nombre_plan = 'SIN PLAN ACTIVO';
$dias_restantes = 0;
$estado_membresia = "INACTIVO";
$color_estado = '#ff4d4d'; // Rojo por defecto

if ($datos_membresia) {
    // Validamos si la fecha de vencimiento aún es vigente
    if (!empty($datos_membresia['fecha_vencimiento'])) {
        $fecha_vence = new DateTime($datos_membresia['fecha_vencimiento']);
        
        if ($fecha_vence > $fecha_actual) {
            $intervalo = $fecha_actual->diff($fecha_vence);
            $dias_restantes = $intervalo->days;
            
            // Si tiene fecha vigente, forzamos el estado a ACTIVO
            $nombre_plan = strtoupper($datos_membresia['plan']);
            $estado_membresia = "ACTIVO";
            $color_estado = '#4df44d'; // Verde brillante
        } else {
            $nombre_plan = strtoupper($datos_membresia['plan']);
            $estado_membresia = "VENCIDO";
            $color_estado = '#ff9900'; // Naranja
        }
    }
}

// 3. CAPTURAR EL DÍA DE HOY EN ESPAÑOL PARA LA RUTINA EN VIVO
$dias_semana_es = [
    'Monday'    => 'Lunes',
    'Tuesday'   => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday'  => 'Jueves',
    'Friday'    => 'Viernes',
    'Saturday'  => 'Sábado',
    'Sunday'    => 'Domingo'
];
$dia_actual_ingles = date('l');
$dia_actual_es = $dias_semana_es[$dia_actual_ingles];

// 4. CONSULTA DE LA RUTINA DEL DÍA
$query_rutina = "SELECT r.descripcion, u.nombre as profesor 
                 FROM rutinas r 
                 JOIN usuarios u ON r.profesor_id = u.id 
                 WHERE r.usuario_id = $usuario_id AND r.dia_semana = '$dia_actual_es' 
                 LIMIT 1";
$resultado_rutina = mysqli_query($conexion, $query_rutina);
$rutina_del_dia = mysqli_fetch_assoc($resultado_rutina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Panel de Atleta</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="assets/css/tablas.css">
    <style>
        /* Estilos específicos e inyectados para la atmósfera gótica de alas rojas */
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.88), rgba(0, 0, 0, 0.93)), url('assets/img/fondos/alas-rojas.jpg');
        }
        .sidebar-gotica {
            border-right-color: rgba(255, 0, 0, 0.15) !important;
        }
        .badge-rojo-neon {
            border: 1px solid #ff0000;
            color: #ff0000;
            background: rgba(255, 0, 0, 0.05);
            text-shadow: 0 0 8px rgba(255, 0, 0, 0.4);
            padding: 8px 15px;
            font-family: 'Cinzel', serif;
            font-size: 0.85rem;
            letter-spacing: 2px;
            border-radius: 3px;
        }
        .tarjeta-metrica {
            background: rgba(5, 5, 5, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-top: 3px solid #ff0000;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            padding: 25px;
            border-radius: 4px;
            text-align: center;
            flex: 1;
        }
        .numero-metrica {
            font-family: 'Cinzel', serif;
            font-size: 3rem;
            font-weight: 700;
            color: #ff0000;
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
            margin: 10px 0;
        }
    </style>
</head>
<body>

    <nav class="sidebar-gotica">
        <div class="brand-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
            <h2>DIVINITY ATLETA</h2>
        </div>
        <ul class="menu-enlaces">
            <li><a href="cliente_dashboard.php" class="activo"> Mi Altar</a></li>
            <li><a href="cliente_membresias.php"> Membresías</a></li>
            <li><a href="cliente_prs.php"> Mis Marcas (PRs)</a></li>
            <li><a href="cliente_clases.php"> Clases</a></li>
            <li><a href="cliente_logros.php"> Logros</a></li>
            <li><a href="perfil.php"> Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
        </ul>
    </nav>

    <main class="contenido-principal">
        <header class="encabezado-dashboard">
            <div class="saludo">
                <h1>BIENVENIDO, <?php echo strtoupper($datos_atleta['nombre']); ?></h1>
                <p style="color: #ff0000; letter-spacing: 2px;">TU CUERPO ES TU TEMPLO, LA DISCIPLINA SU FORJA</p>
            </div>
            <div class="badge-rojo-neon">ATLETA</div>
        </header>

        <section class="layout-bloques" style="margin-bottom: 30px; gap: 20px;">
            <div class="tarjeta-metrica">
                <h3>PLAN CONTRATADO</h3>
                <div class="numero-metrica" style="font-size: 1.8rem; padding: 17px 0; color: #fff;">
                    <?php echo $nombre_plan; ?>
                </div>
                <p style="font-size: 0.75rem; color: #666;">MEMBRESÍA ACTUAL EN EL SISTEMA</p>
            </div>

            <div class="tarjeta-metrica">
                <h3>TIEMPO RESTANTE</h3>
                <div class="numero-metrica">
                    <?php echo $dias_restantes; ?>
                </div>
                <p style="font-size: 0.75rem; color: #666;">DÍAS DE ACCESO ANTES DEL VENCIMIENTO</p>
            </div>

            <div class="tarjeta-metrica">
                <h3>ESTADO DE MENBRESIA</h3>
                <div class="numero-metrica" style="font-size: 1.8rem; padding: 17px 0; color: <?php echo $color_estado; ?>;">
                    <?php echo $estado_membresia; ?>
                </div>
                <p style="font-size: 0.75rem; color: #666;">ESTADO DE ENTRADA AL GIMNASIO</p>
            </div>
        </section>

        <div class="bloque-tabla" style="width: 100%; border-top: 3px solid #ff0000;">
            <h2> ORDEN DE ENTRENAMIENTO PARA HOY: <?php echo strtoupper($dia_actual_es); ?></h2>
            <div style="background: rgba(10,10,10,0.8); border: 1px solid rgba(255,255,255,0.02); padding: 30px; border-radius: 4px; margin-top: 15px;">
                <?php if ($rutina_del_dia): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,0,0,0.1); padding-bottom: 15px; margin-bottom: 20px;">
                        <span style="font-size: 0.85rem; color: #aaa; font-weight: 600;">INSTRUCCIONES DICTADAS POR:</span>
                        <span style="color: #ff0000; font-weight: 600; font-family: 'Cinzel', serif; font-size: 0.9rem;"> COACH <?php echo strtoupper($rutina_del_dia['profesor']); ?></span>
                    </div>
                    <p style="white-space: pre-line; line-height: 1.8; color: #ffffff; font-size: 0.95rem; font-family: 'Poppins', sans-serif; letter-spacing: 0.5px;">
                        <?php echo htmlspecialchars($rutina_del_dia['descripcion']); ?>
                    </p>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px 0;">
                        <p style="color: #666; font-size: 1.1rem; margin-bottom: 10px;"> Día de Descanso o Altar Vacío </p>
                        <p style="color: #444; font-size: 0.8rem;">Tu entrenador aún no ha programado ejercicios para ti en este día de la semana.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>