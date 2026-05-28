<?php
// Incluimos la conexión y control de sesiones
require_once 'config.php';

// CONTROL DE ACCESO: Solo clientes autorizados
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Consulta avanzada: Trae todas las medallas y une el progreso del usuario si existe
$query_logros = "SELECT m.*, 
                        um.conquistada, 
                        um.progreso_actual_fuerza, 
                        um.progreso_actual_km, 
                        um.progreso_actual_metros, 
                        um.progreso_actual_asistencia,
                        um.fecha_desbloqueo
                 FROM medallas m
                 LEFT JOIN usuario_medallas um ON m.id = um.medalla_id AND um.usuario_id = $usuario_id
                 ORDER BY m.id ASC";

$resultado_logros = mysqli_query($conexion, $query_logros);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Mis Medallas</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght=700&family=Poppins:wght=400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <style>
        /* Estilos específicos para la Sala de Trofeos Gótica */
        .grid-logros {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        .tarjeta-medalla {
            background: rgba(10, 10, 10, 0.85);
            border: 1px solid #222;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        /* Estado cuando el atleta ya ganó la medalla */
        .tarjeta-medalla.conquistada {
            border: 1px solid var(--neon-dorado, #cc9933);
            box-shadow: 0 0 15px rgba(204, 153, 51, 0.2);
            background: linear-gradient(135deg, rgba(20, 18, 10, 0.9) 0%, rgba(5, 5, 5, 0.95) 100%);
        }
        .medalla-encabezado {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .icono-tipo {
            font-size: 1.8rem;
        }
        .status-badge {
            font-family: 'Cinzel', serif;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-badge.bloqueado {
            background: rgba(255, 255, 255, 0.05);
            color: #666;
            border: 1px solid #333;
        }
        .status-badge.ganado {
            background: rgba(204, 153, 51, 0.2);
            color: var(--neon-dorado, #cc9933);
            border: 1px solid var(--neon-dorado, #cc9933);
            text-shadow: 0 0 5px rgba(204, 153, 51, 0.5);
        }
        .medalla-info h3 {
            font-family: 'Cinzel', serif;
            color: #fff;
            margin: 0 0 8px 0;
            font-size: 1.2rem;
            letter-spacing: 1px;
        }
        .tarjeta-medalla.conquistada .medalla-info h3 {
            color: var(--neon-dorado, #cc9933);
        }
        .medalla-info p {
            font-family: 'Poppins', sans-serif;
            color: #aaa;
            font-size: 0.85rem;
            margin: 0 0 15px 0;
            line-height: 1.4;
        }
        .requisito-meta {
            background: rgba(0,0,0,0.4);
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-family: 'Poppins', sans-serif;
            color: #eee;
            margin-bottom: 15px;
            border-left: 3px solid #444;
        }
        .tarjeta-medalla.conquistada .requisito-meta {
            border-left: 3px solid var(--neon-dorado, #cc9933);
        }
        /* Barra de Progreso */
        .progreso-contenedor {
            margin-top: auto;
        }
        .progreso-texto {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            font-family: 'Poppins', sans-serif;
            color: #888;
            margin-bottom: 5px;
        }
        .barra-fondo {
            background: #111;
            height: 6px;
            border-radius: 3px;
            width: 100%;
            overflow: hidden;
            border: 1px solid #222;
        }
        .barra-relleno {
            height: 100%;
            border-radius: 3px;
            width: 0%;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body class="fondo-atletas-dashboard">

    <!-- Sidebar idéntica para mantener diseño estructural -->
    <nav class="sidebar-gotica">
        <div class="brand-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
            <h2>DIVINITY CLUB</h2>
        </div>
        <ul class="menu-enlaces">
            <li><a href="cliente_dashboard.php">Inicio</a></li>
            <li><a href="cliente_rutina.php">Mi Rutina</a></li>
            <li><a href="cliente_clases.php">Reservar Clase</a></li>
            <li><a href="cliente_logros.php" class="activo">Sala de Trofeos</a></li>
            <li><a href="perfil.php">Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link">Salir del Templo</a></li>
        </ul>
    </nav>

    <main class="contenido-principal">
        <header class="encabezado-dashboard">
            <div class="saludo">
                <h1>SALA DE DECRETOS Y RECOMPENSAS</h1>
                <p>MIDE TU RENDIMIENTO EVOLUTIVO Y CONQUISTA LAS MEDALLAS DE LOS DIOSES</p>
            </div>
            <div class="rango-badge">RANGO: ATLETA</div>
        </header>

        <div class="grid-logros">
            <?php if (mysqli_num_rows($resultado_logros) > 0): ?>
                <?php while ($logro = mysqli_fetch_assoc($resultado_logros)): 
                    
                    // LÓGICA MATEMÁTICA DE PORCENTAJES
                    $porcentaje = 0;
                    $es_ganada = ($logro['conquistada'] == 1);
                    $datos_actuales = "";
                    $datos_objetivo = "";
                    $color_barra = "#666"; // Por defecto

                    if ($logro['tipo_requisito'] == 'fuerza') {
                        $color_barra = "#cc9933"; // Dorado neón
                        $actual = floatval($logro['progreso_actual_fuerza']);
                        $objetivo = floatval($logro['peso_objetivo']);
                        $datos_actuales = $actual . " kg";
                        $datos_objetivo = $objetivo . " kg";
                        if ($objetivo > 0) {
                            $porcentaje = ($actual / $objetivo) * 100;
                        }
                        $icono = "";
                        $meta_txt = "Meta: PR en " . $logro['ejercicio'] . " de " . $objetivo . " kg";
                        
                    } elseif ($logro['tipo_requisito'] == 'cardio') {
                        $color_barra = "#ff4d4d"; // Rojo carrera
                        // Convertimos todo a metros para calcular el porcentaje exacto
                        $objetivo_metros = ($logro['km_objetivo'] * 1000) + $logro['metros_objetivo'];
                        $actual_metros = ($logro['progreso_actual_km'] * 1000) + $logro['progreso_actual_metros'];
                        
                        $datos_actuales = ($logro['progreso_actual_km'] > 0 ? $logro['progreso_actual_km']."km " : "") . ($logro['progreso_actual_metros'] > 0 ? $logro['progreso_actual_metros']."m" : "");
                        if(empty($datos_actuales)) $datos_actuales = "0 m";
                        
                        $datos_objetivo = ($logro['km_objetivo'] > 0 ? $logro['km_objetivo']."km " : "") . ($logro['metros_objetivo'] > 0 ? $logro['metros_objetivo']."m" : "");

                        if ($objetivo_metros > 0) {
                            $porcentaje = ($actual_metros / $objetivo_metros) * 100;
                        }
                        $icono = "";
                        $meta_txt = "Meta: Recorrer " . $datos_objetivo . " en " . $logro['ejercicio'];
                        
                    } elseif ($logro['tipo_requisito'] == 'asistencia') {
                        $color_barra = "#33ccff"; // Celeste inmortal
                        $actual = intval($logro['progreso_actual_asistencia']);
                        $objetivo = intval($logro['dias_objetivo']);
                        $datos_actuales = $actual . " días";
                        $datos_objetivo = $objetivo . " días";
                        if ($objetivo > 0) {
                            $porcentaje = ($actual / $objetivo) * 100;
                        }
                        $icono = "";
                        $meta_txt = "Meta: Asistir " . $objetivo . " días al templo";
                    }

                    // Asegurar topes de porcentaje entre 0 y 100
                    if ($porcentaje > 100 || $es_ganada) $porcentaje = 100;
                    if ($porcentaje < 0) $porcentaje = 0;
                ?>
                    
                    <div class="tarjeta-medalla <?php echo $es_ganada ? 'conquistada' : ''; ?>">
                        <div>
                            <div class="medalla-encabezado">
                                <span class="icono-tipo"><?php echo $icono; ?></span>
                                <span class="status-badge <?php echo $es_ganada ? 'ganado' : 'bloqueado'; ?>">
                                    <?php echo $es_ganada ? 'CONQUISTADO' : 'EN PROGRESO'; ?>
                                </span>
                            </div>

                            <div class="medalla-info">
                                <h3><?php echo strtoupper($logro['nombre']); ?></h3>
                                <p><?php echo $logro['descripcion']; ?></p>
                            </div>

                            <div class="requisito-meta">
                                <?php echo $meta_txt; ?>
                            </div>
                        </div>

                        <div class="progreso-contenedor">
                            <div class="progreso-texto">
                                <span>Progreso: <?php echo $datos_actuales; ?></span>
                                <strong><?php echo round($porcentaje); ?>%</strong>
                            </div>
                            <div class="barra-fondo">
                                <div class="barra-relleno" style="width: <?php echo $porcentaje; ?>%; background-color: <?php echo $color_barra; ?>; box-shadow: 0 0 8px <?php echo $color_barra; ?>;"></div>
                            </div>
                            <?php if($es_ganada && !empty($logro['fecha_desbloqueo'])): ?>
                                <div style="font-size: 0.65rem; color: #666; margin-top: 5px; text-align: right; font-style: italic;">
                                    Desbloqueado el: <?php echo date('d/m/Y', strtotime($logro['fecha_desbloqueo'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #555; font-family: 'Poppins';">
                    El Templo de los Logros no tiene desafíos forjados aún.
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>