<?php
// Incluimos la conexión y el control de sesiones
require_once 'config.php';

// CONTROL DE ACCESO: Solo administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: login.php');
    exit;
}

// Variables para el control de edición en el formulario
$modo_edicion = false;
$id_editar = "";
$nombre_editar = "";
$precio_editar = "";
$ciclo_editar = "";

// 1. CAPTURA DE DATOS PARA EDICIÓN
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $query_buscar = "SELECT * FROM membresias WHERE id = $id_editar";
    $res_buscar = mysqli_query($conexion, $query_buscar);
    
    if ($res_buscar && mysqli_num_rows($res_buscar) > 0) {
        $plan = mysqli_fetch_assoc($res_buscar);
        $modo_edicion = true;
        $nombre_editar = $plan['nombre'];
        $precio_editar = $plan['precio'];
        $ciclo_editar = $plan['duracion_dias']; 
    }
}

// 2. PROCESAMIENTO DEL FORMULARIO (Crear o Actualizar)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_plan']);
    $precio = floatval($_POST['precio_plan']);
    $ciclo = intval($_POST['ciclo_plan']);

    if (isset($_POST['id_membresia_edicion']) && !empty($_POST['id_membresia_edicion'])) {
        $id_update = intval($_POST['id_membresia_edicion']);
        $query_procesar = "UPDATE membresias SET nombre='$nombre', precio=$precio, duracion_dias=$ciclo WHERE id=$id_update";
    } else {
        $query_procesar = "INSERT INTO membresias (nombre, precio, duracion_dias) VALUES ('$nombre', $precio, $ciclo)";
    }
    
    if (mysqli_query($conexion, $query_procesar)) {
        header('Location: admin_membresias.php');
        exit;
    }
}

// 3. PROCESAMIENTO DE ELIMINACIÓN
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $query_eliminar = "DELETE FROM membresias WHERE id = $id_eliminar";
    if (mysqli_query($conexion, $query_eliminar)) {
        header('Location: admin_membresias.php');
        exit;
    }
}

// 4. CONSULTA DE PLANES PARA LA TABLA
$query_planes = "SELECT * FROM membresias ORDER BY id DESC";
$resultado_planes = mysqli_query($conexion, $query_planes);

// 5. EXTRACCIÓN DE DATOS PARA LAS GRÁFICAS (Semanales y Mensuales)
// Ganancias de los últimos 7 días con ventas reales
$query_semanal = "SELECT DATE_FORMAT(fecha_pago, '%d-%b') as dia, SUM(monto_pagado) as total 
                  FROM usuario_membresias 
                  WHERE fecha_pago >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  GROUP BY DATE(fecha_pago) 
                  ORDER BY fecha_pago ASC";
$res_semanal = mysqli_query($conexion, $query_semanal);
$labels_semana = [];
$datos_semana = [];
while ($r = mysqli_fetch_assoc($res_semanal)) {
    $labels_semana[] = $r['dia'];
    $datos_semana[] = $r['total'];
}

// Ganancias de los últimos 6 meses con ventas reales
$query_mensual = "SELECT DATE_FORMAT(fecha_pago, '%b-%Y') as mes, SUM(monto_pagado) as total 
                  FROM usuario_membresias 
                  WHERE fecha_pago >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                  GROUP BY MONTH(fecha_pago), YEAR(fecha_pago) 
                  ORDER BY fecha_pago ASC";
$res_mensual = mysqli_query($conexion, $query_mensual);
$labels_mes = [];
$datos_mes = [];
while ($r = mysqli_fetch_assoc($res_mensual)) {
    $labels_mes[] = $r['mes'];
    $datos_mes[] = $r['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Forja de Membresías</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght=700&family=Poppins:wght=400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="assets/css/tablas.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="fondo-dashboard-admin" style="background-color: #050505; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; display: flex;">

    <nav class="sidebar-gotica" style="width: 250px; background-color: #0a0a0a; border-right: 1px solid #111; min-height: 100vh; padding: 20px; box-sizing: border-box;">
        <div class="brand-zona" style="text-align: center; margin-bottom: 40px;">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav" style="width: 60px; height: auto; border-radius: 50%;">
            <h2 style="font-family: 'Cinzel', serif; color: #fff; font-size: 1.1rem; letter-spacing: 1px; margin-top: 10px;">DIVINITY ADMIN</h2>
        </div>
        <ul class="menu-enlaces" style="list-style: none; padding: 0; margin: 0;">
            <li style="margin-bottom: 20px;"><a href="admin_dashboard.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Inicio</a></li>
            <li style="margin-bottom: 20px;"><a href="admin_clientes.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Atletas</a></li>
            <li style="margin-bottom: 20px;"><a href="admin_profesores.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Entrenadores</a></li>
            <li style="margin-bottom: 20px;"><a href="admin_membresias.php" class="activo" style="color: #fff; text-decoration: none; font-size: 0.95rem; font-weight: 600; border: 1px solid #cc9933; padding: 8px 12px; display: block; border-radius: 4px; background: rgba(204,153,51,0.05);">Membresías</a></li>
            <li style="margin-bottom: 20px;"><a href="admin_clases.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Cronograma</a></li>
            <li style="margin-bottom: 20px;"><a href="admin_logros.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Crear Logros</a></li>
            <li style="margin-bottom: 40px;"><a href="perfil.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Mi Perfil</a></li>
            <li class="separador-logout" style="margin-top: 50px;"><a href="procesar/auth/logout.php" class="logout-link" style="color: #ff4d4d; text-decoration: none; font-size: 0.95rem; border: 1px solid #ff4d4d; padding: 8px 12px; display: block; border-radius: 4px; text-align: center;">Cerrar Templo</a></li>
        </ul>
    </nav>

    <main class="contenido-principal" style="flex: 1; padding: 40px; box-sizing: border-box;">
        
        <header class="encabezado-dashboard" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h1 style="font-family: 'Cinzel', serif; font-size: 2.2rem; color: #fff; margin: 0; letter-spacing: 2px; text-shadow: 0 0 10px rgba(255,255,255,0.1);">FORJA DE MEMBRESÍAS</h1>
            <p style="color: #cc9933; font-size: 0.8rem; font-weight: 600; letter-spacing: 1px; margin: 0; text-transform: uppercase;">DECRETA LOS PACTOS Y PRECIOS DE ACCESO AL INFRAMUNDO</p>
        </header>

        <div class="contenedor-grid-membresias" style="display: grid; grid-template-columns: 1fr 1.3fr; gap: 30px; align-items: start;">
            
            <section class="tarjeta-formulario" style="background-color: #0a0a0a; border: 1px solid #1c1c1c; padding: 30px; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                <h2 style="font-family: 'Cinzel', serif; color: #fff; font-size: 1.2rem; margin-top: 0; margin-bottom: 25px; letter-spacing: 1px;">
                    <?php echo $modo_edicion ? "MODIFICAR DECRETOS" : "FORJAR NUEVO PACTO"; ?>
                </h2>

                <form action="admin_membresias.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                    <input type="hidden" name="id_membresia_edicion" value="<?php echo $id_editar; ?>">

                    <div class="campo-grupo">
                        <label style="color: #777; font-size: 0.75rem; display: block; margin-bottom: 8px; letter-spacing: 1px; text-transform: uppercase;">NOMBRE DE LA MEMBRESÍA</label>
                        <input type="text" name="nombre_plan" value="<?php echo htmlspecialchars($nombre_editar); ?>" required placeholder="Ej: Plan Semidios" style="width: 100%; background-color: #111; border: 1px solid #222; color: #fff; padding: 12px; border-radius: 4px; box-sizing: border-box; font-family: 'Poppins'; font-size: 0.9rem;">
                    </div>

                    <div class="campo-grupo">
                        <label style="color: #777; font-size: 0.75rem; display: block; margin-bottom: 8px; letter-spacing: 1px; text-transform: uppercase;">PRECIO DE OFRENDA (COP)</label>
                        <input type="number" name="precio_plan" value="<?php echo $precio_editar; ?>" required placeholder="Ej: 60000" style="width: 100%; background-color: #111; border: 1px solid #222; color: #fff; padding: 12px; border-radius: 4px; box-sizing: border-box; font-family: 'Poppins'; font-size: 0.9rem;">
                    </div>

                    <div class="campo-grupo">
                        <label style="color: #777; font-size: 0.75rem; display: block; margin-bottom: 8px; letter-spacing: 1px; text-transform: uppercase;">TIEMPO DE ACCESO (DÍAS)</label>
                        <input type="number" name="ciclo_plan" value="<?php echo $ciclo_editar; ?>" required placeholder="Ej: 30" style="width: 100%; background-color: #111; border: 1px solid #222; color: #fff; padding: 12px; border-radius: 4px; box-sizing: border-box; font-family: 'Poppins'; font-size: 0.9rem;">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                        <button type="submit" style="width: 100%; background-color: #ffd700; color: #000; font-family: 'Cinzel', serif; font-weight: bold; border: none; padding: 14px; border-radius: 4px; cursor: pointer; letter-spacing: 1px; font-size: 0.95rem; transition: background 0.3s; text-transform: uppercase;">
                            <?php echo $modo_edicion ? "GUARDAR CAMBIOS" : "INFUNDIR PODER AL PLAN"; ?>
                        </button>
                        
                        <?php if($modo_edicion): ?>
                            <a href="admin_membresias.php" style="width: 100%; background-color: #1a1a1a; color: #aaa; font-family: 'Poppins'; text-decoration: none; padding: 12px; border-radius: 4px; border: 1px solid #222; font-size: 0.85rem; text-align: center; box-sizing: border-box;">Cancelación de Edición</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="tarjeta-tabla" style="background-color: #0a0a0a; border: 1px solid #1c1c1c; padding: 30px; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h2 id="tituloSeccionDinamica" style="font-family: 'Cinzel', serif; color: #fff; font-size: 1.2rem; margin: 0; letter-spacing: 1px;">PACTOS VIGENTES</h2>
                    <button id="btnAlternarVista" onclick="alternarPantallaVista()" style="background: rgba(204,153,51,0.1); border: 1px solid #cc9933; color: #cc9933; padding: 6px 12px; font-family: 'Poppins'; font-size: 0.75rem; border-radius: 4px; cursor: pointer; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase;">ESTADÍSTICAS</button>
                </div>

                <div id="vistaTablaMembresias">
                    <table class="tabla-oscura" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid #222;">
                                <th style="padding: 12px 8px; font-family: 'Cinzel'; font-size: 0.8rem; color: #ffd700; width: 40px;">ID</th>
                                <th style="padding: 12px 8px; font-family: 'Cinzel'; font-size: 0.8rem; color: #ffd700;">NOMBRE DEL PACTO</th>
                                <th style="padding: 12px 8px; font-family: 'Cinzel'; font-size: 0.8rem; color: #ffd700; text-align: right;">VALOR</th>
                                <th style="padding: 12px 8px; font-family: 'Cinzel'; font-size: 0.8rem; color: #ffd700; text-align: center; width: 90px;">CICLO</th>
                                <th style="padding: 12px 8px; font-family: 'Cinzel'; font-size: 0.8rem; color: #ffd700; text-align: center; width: 160px;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($resultado_planes) > 0): ?>
                                <?php while($fila = mysqli_fetch_assoc($resultado_planes)): ?>
                                    <tr style="border-bottom: 1px solid #111;">
                                        <td style="padding: 16px 8px; color: #444; font-size: 0.9rem;"><?php echo $fila['id']; ?></td>
                                        <td style="padding: 16px 8px; font-weight: 600; color: #fff; font-size: 0.9rem; text-transform: uppercase;"><?php echo htmlspecialchars($fila['nombre']); ?></td>
                                        <td style="padding: 16px 8px; text-align: right; color: #00ff66; font-weight: 600; font-size: 0.9rem;">$<?php echo number_format($fila['precio'], 0, ',', '.'); ?></td>
                                        <td style="padding: 16px 8px; text-align: center; color: #aaa; font-size: 0.85rem;"><?php echo $fila['duracion_dias']; ?> Días</td>
                                        <td style="padding: 16px 8px; display: flex; align-items: center; justify-content: center; gap: 12px; height: 100%;">
                                            <a href="admin_membresias.php?editar=<?php echo $fila['id']; ?>" style="color: #cc9933; text-decoration: none; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid rgba(204,153,51,0.3); padding: 6px 10px; border-radius: 4px; background: rgba(204,153,51,0.02);">Modificar</a>
                                            <a href="admin_membresias.php?eliminar=<?php echo $fila['id']; ?>" onclick="return confirm('¿Deseas revocar este pacto de membresía?');" style="color: #ff4d4d; border: 1px solid #ff4d4d; background: rgba(255,77,77,0.02); text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="padding: 30px; text-align: center; color: #444; font-size: 0.9rem;">No hay pactos vigentes en el inframundo.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div id="vistaEstadisticasMembresias" style="display: none; flex-direction: column; gap: 30px;">
                    <div>
                        <p style="color: #cc9933; font-size: 0.75rem; font-weight: 600; margin: 0 0 10px 0; letter-spacing: 1px; text-transform: uppercase;">Rendimiento de Ingresos Semanales (COP)</p>
                        <canvas id="graficaSemanalCanvas" style="max-height: 180px; width: 100%;"></all>
                    </div>
                    <div style="border-top: 1px solid #1c1c1c; padding-top: 20px;">
                        <p style="color: #cc9933; font-size: 0.75rem; font-weight: 600; margin: 0 0 10px 0; letter-spacing: 1px; text-transform: uppercase;">Rendimiento de Ingresos Mensuales (COP)</p>
                        <canvas id="graficaMensualCanvas" style="max-height: 180px; width: 100%;"></canvas>
                    </div>
                </div>

            </section>

        </div>
    </main>

    <script>
        // Variables de control de renderizado de gráficos
        let graficaSemanalInstance = null;
        let graficaMensualInstance = null;

        function alternarPantallaVista() {
            const tabla = document.getElementById('vistaTablaMembresias');
            const estadisticas = document.getElementById('vistaEstadisticasMembresias');
            const boton = document.getElementById('btnAlternarVista');
            const titulo = document.getElementById('tituloSeccionDinamica');

            if (tabla.style.display !== 'none') {
                // Cambiar a la vista de Estadísticas
                tabla.style.display = 'none';
                estadisticas.style.display = 'flex';
                boton.innerText = 'VER TABLA';
                titulo.innerText = 'RENDIMIENTO FINANCIERO';
                inicializarGraficos();
            } else {
                // Volver a la vista de la Tabla tradicional
                tabla.style.display = 'block';
                estadisticas.style.display = 'none';
                boton.innerText = 'ESTADÍSTICAS';
                titulo.innerText = 'PACTOS VIGENTES';
            }
        }

        function inicializarGraficos() {
            // Verificar si los gráficos ya fueron dibujados previamente para no duplicarlos
            if (graficaSemanalInstance !== null && graficaMensualInstance !== null) {
                return;
            }

            // Configuración del Gráfico Semanal
            const ctxSemanal = document.getElementById('graficaSemanalCanvas').getContext('2d');
            graficaSemanalInstance = new Chart(ctxSemanal, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($labels_semana); ?>,
                    datasets: [{
                        label: 'Ingresos Diarios',
                        data: <?php echo json_encode($datos_semana); ?>,
                        borderColor: '#ffd700',
                        backgroundColor: 'rgba(255, 215, 0, 0.05)',
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: '#111' }, ticks: { color: '#666', font: { family: 'Poppins', size: 10 } } },
                        y: { grid: { color: '#111' }, ticks: { color: '#666', font: { family: 'Poppins', size: 10 } } }
                    }
                }
            });

            // Configuración del Gráfico Mensual
            const ctxMensual = document.getElementById('graficaMensualCanvas').getContext('2d');
            graficaMensualInstance = new Chart(ctxMensual, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels_mes); ?>,
                    datasets: [{
                        label: 'Ingresos Mensuales',
                        data: <?php echo json_encode($datos_mes); ?>,
                        backgroundColor: 'rgba(204, 153, 51, 0.7)',
                        borderColor: '#cc9933',
                        borderWidth: 1,
                        borderRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: '#111' }, ticks: { color: '#666', font: { family: 'Poppins', size: 10 } } },
                        y: { grid: { color: '#111' }, ticks: { color: '#666', font: { family: 'Poppins', size: 10 } } }
                    }
                }
            });
        }
    </script>

</body>
</html>