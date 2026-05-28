<?php
require_once 'config.php';

// Seguridad: Si no es Profesor (Rol 2), rebota al login
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: login.php');
    exit;
}

$instructor_id = $_SESSION['usuario_id'];
$mensaje = "";
$tipo_alerta = "";

// Configuración global del Templo (Se puede cambiar después desde el Admin)
$HORA_APERTURA = "06:00:00";
$HORA_CIERRE   = "22:00:00";
$CUPO_PREDETERMINADO = 15; // Cupo por defecto

// PROCESAR CREACIÓN DE CLASE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_crear_clase'])) {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
    $hora_inicio = $_POST['hora_inicio'] . ":00"; // Asegurar formato HH:MM:SS
    $duracion = intval($_POST['duracion']);
    $tipo = $_POST['tipo']; // 'gratis' o 'pago'

    // VALIDACIÓN 3: Solo dentro del rango de tiempo permitido
    if ($hora_inicio < $HORA_APERTURA || $hora_inicio > $HORA_CIERRE) {
        $mensaje = "EL HORARIO ESTÁ FUERA DE LOS LÍMITES PERMITIDOS ($HORA_APERTURA A $HORA_CIERRE).";
        $tipo_alerta = "error";
    } else {
        // VALIDACIÓN 1: El profesor no puede poner más de una clase a la misma hora
        $comprobar_hora = mysqli_query($conexion, "SELECT id FROM clases WHERE instructor_id = $instructor_id AND hora_inicio = '$hora_inicio' LIMIT 1");
        
        if (mysqli_num_rows($comprobar_hora) > 0) {
            $mensaje = "YA TIENES UNA CLASE ASIGNADA A ESA MISMA HORA.";
            $tipo_alerta = "error";
        } else {
            // Guardar clase en la base de datos (VALIDACIÓN 2: El cupo se asigna automáticamente al límite del templo)
            $query = "INSERT INTO clases (nombre, descripcion, instructor_id, hora_inicio, duracion_horas, tipo, cupo_maximo) 
                      VALUES ('$nombre', '$descripcion', $instructor_id, '$hora_inicio', $duracion, '$tipo', $CUPO_PREDETERMINADO)";
            
            if (mysqli_query($conexion, $query)) {
                $mensaje = "NUEVA CLASE EN SINTONÍA CON EL CRONOGRAMA.";
                $tipo_alerta = "exito";
            } else {
                $mensaje = "ERROR AL INFUNDIR PODER A LA CLASE.";
                $tipo_alerta = "error";
            }
        }
    }
}

// PROCESAR ELIMINACIÓN DE CLASE
if (isset($_GET['borrar_clase_id'])) {
    $clase_id = intval($_GET['borrar_clase_id']);
    // Asegurar que solo el profesor dueño pueda borrarla
    $query = "DELETE FROM clases WHERE id = $clase_id AND instructor_id = $instructor_id";
    if (mysqli_query($conexion, $query)) {
        $mensaje = "CLASE DISUELTA CORRECTAMENTE.";
        $tipo_alerta = "error";
    }
}

// OBTENER CLASES DEL PROFESOR
$clases_gratuitas = mysqli_query($conexion, "SELECT * FROM clases WHERE instructor_id = $instructor_id AND tipo = 'gratis' ORDER BY hora_inicio ASC");
$clases_pago = mysqli_query($conexion, "SELECT * FROM clases WHERE instructor_id = $instructor_id AND tipo = 'pago' ORDER BY hora_inicio ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Clases del Instructor</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css?v=1.2"> 
</head>
<body class="fondo-staff-profe">

    <div class="contenedor-dashboard">
        
        <nav class="sidebar-gotica">
        <div class="brand-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
            <h2>DIVINITY ENTRENADOR</h2>
        </div>
        <ul class="menu-enlaces">
            <li><a href="profe_dashboard.php"> Inicio</a></li>
            <li><a href="profe_rutinas.php"> Tabla de rutinas</a></li>
            <li><a href="profe_clases.php" class="activo"> Crear clase</a></li>
            <li><a href="perfil.php"> Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
        </ul>
    </nav><


        <main class="contenido-principal">
            
            <header class="encabezado-dashboard">
                <div class="saludo">
                    <h1>GESTIÓN DE CLASES</h1>
                    <p>MÓDULO DE CREACIÓN Y ASIGNACIÓN DE HORARIOS</p>
                </div>
                <div class="rango-badge" style="border-color: #00ffff; color: #00ffff; background: rgba(0, 255, 255, 0.05); text-shadow: 0 0 5px rgba(0, 255, 255, 0.3);">INSTRUCTOR OFICIAL</div>
            </header>

            <?php if (!empty($mensaje)): ?>
                <div style="margin-bottom: 20px; padding: 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; text-align: center; <?php echo $tipo_alerta == 'error' ? 'background: rgba(255,0,0,0.1); border: 1px solid #ff0000; color: #ff4d4d;' : 'background: rgba(0,255,255,0.08); border: 1px solid #00ffff; color: #4df4f4;'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="malla-forja">
                
                <div class="bloque-formulario">
                    <h3 style="font-family: 'Cinzel', serif; font-size: 1.1rem; letter-spacing: 1px; margin-bottom: 20px; border-bottom: 1px solid var(--borde-sutil); padding-bottom: 10px;">DECRETAR NUEVA CLASE</h3>
                    
                    <form action="profe_clases.php" method="POST">
                        <input type="hidden" name="accion_crear_clase" value="1">
                        
                        <div class="campo-panel">
                            <label>NOMBRE DE LA CLASE</label>
                            <input type="text" name="nombre" placeholder="Ej: Spinning Infernal, Power HIIT" required autocomplete="off">
                        </div>
                        <div class="campo-panel">
                            <label>DURACIÓN (EN HORAS)</label>
                            <input type="number" name="duracion" min="1" max="4" value="1" required>
                        </div>
                        <div class="campo-panel">
                            <label>HORA DE INICIO</label>
                            <input type="time" name="hora_inicio" required>
                        </div>
                        <div class="campo-panel">
                            <label>ACCESO / TIPO DE CLASE</label>
                            <select name="tipo" style="width: 100%; background: rgba(20, 20, 20, 0.8); border: 1px solid rgba(255, 255, 255, 0.08); padding: 12px; color: var(--texto-brillante); font-family: 'Poppins', sans-serif; border-radius: 4px;">
                                <option value="gratis" style="background:#0a0a0a;">Clase Gratuita (Comunidad)</option>
                                <option value="pago" style="background:#0a0a0a;">Clase Privada (Premium)</option>
                            </select>
                        </div>
                        <div class="campo-panel">
                            <label>BREVE DESCRIPCIÓN</label>
                            <textarea name="descripcion" placeholder="Describe los objetivos y la exigencia de este entrenamiento..." required></textarea>
                        </div>
                        <button type="submit" class="btn-panel-neon" style="border-color:#00ffff; text-shadow: 0 0 5px rgba(0,255,255,0.4);">CREAR CLASE</button>
                    </form>
                </div>

                <div class="bloque-tabla">
                    
                    <div class="zona-intercambio-botones">
                        <button type="button" id="btn-clases-gratis" class="btn-intercambio activo">SESIONES GRATUITAS</button>
                        <button type="button" id="btn-clases-pago" class="btn-intercambio">SESIONES PRIVADAS</button>
                    </div>

                    <div id="tabla-gratis-contenedor" class="bloque-tabla-dinamica">
                        <table class="tabla-hardcore">
                            <thead>
                                <tr>
                                    <th>HORA</th>
                                    <th>CLASE</th>
                                    <th>DURACIÓN</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($clases_gratuitas) == 0): ?>
                                    <tr><td colspan="5" class="texto-vacio">No has decretado clases gratuitas aún.</td></tr>
                                <?php else: ?>
                                    <?php while($c_g = mysqli_fetch_assoc($clases_gratuitas)): ?>
                                        <tr>
                                            <td style="color:#00ffff; font-weight:600;"><?php echo date("g:i a", strtotime($c_g['hora_inicio'])); ?></td>
                                            <td class="resaltado-neon" style="color:#fff;"><?php echo htmlspecialchars($c_g['nombre']); ?></td>
                                            <td><?php echo $c_g['duracion_horas']; ?> Hora(s)</td>
                                            <td class="texto-biografia-celda"><?php echo htmlspecialchars($c_g['descripcion']); ?></td>
                                            <td>
                                                <a href="profe_clases.php?borrar_clase_id=<?php echo $c_g['id']; ?>" class="action-btn delete-btn" onclick="return confirm('¿Disolver esta sesión del cronograma?');">Eliminar</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="tabla-pago-contenedor" class="bloque-tabla-dinamica oculto-inicial">
                        <table class="tabla-hardcore">
                            <thead>
                                <tr>
                                    <th>HORA</th>
                                    <th>CLASE</th>
                                    <th>DURACIÓN</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($clases_pago) == 0): ?>
                                    <tr><td colspan="5" class="texto-vacio">No has decretado clases privadas aún.</td></tr>
                                <?php else: ?>
                                    <?php while($c_p = mysqli_fetch_assoc($clases_pago)): ?>
                                        <tr>
                                            <td style="color:#00ffff; font-weight:600;"><?php echo date("g:i a", strtotime($c_p['hora_inicio'])); ?></td>
                                            <td class="resaltado-neon" style="color:#fff;"><?php echo htmlspecialchars($c_p['nombre']); ?></td>
                                            <td><?php echo $c_p['duracion_horas']; ?> Hora(s)</td>
                                            <td class="texto-biografia-celda"><?php echo htmlspecialchars($c_p['descripcion']); ?></td>
                                            <td>
                                                <a href="profe_clases.php?borrar_clase_id=<?php echo $c_p['id']; ?>" class="action-btn delete-btn" onclick="return confirm('¿Disolver esta sesión del cronograma?');">Eliminar</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script>
        const btnGratis = document.getElementById('btn-clases-gratis');
        const btnPago = document.getElementById('btn-clases-pago');
        const contenedorGratis = document.getElementById('tabla-gratis-contenedor');
        const contenedorPago = document.getElementById('tabla-pago-contenedor');

        btnGratis.addEventListener('click', () => {
            btnGratis.classList.add('activo');
            btnPago.classList.remove('activo');
            contenedorGratis.classList.remove('oculto-inicial');
            contenedorPago.classList.add('oculto-inicial');
        });

        btnPago.addEventListener('click', () => {
            btnPago.classList.add('activo');
            btnGratis.classList.remove('activo');
            contenedorPago.classList.remove('oculto-inicial');
            contenedorGratis.classList.add('oculto-inicial');
        });
    </script>
</body>
</html>