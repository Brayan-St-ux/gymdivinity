<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: login.php');
    exit;
}

$mensaje = "";
$tipo_alerta = "";

$HORA_APERTURA = "06:00:00";
$HORA_CIERRE   = "22:00:00";
$CUPO_MAX_GLOBAL = 15;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion_admin_crear'])) {
        $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
        $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
        $instructor_id = intval($_POST['instructor_id']);
        $hora_inicio = $_POST['hora_inicio'] . ":00";
        $duracion = intval($_POST['duracion']);
        $tipo = $_POST['tipo'];
        $cupo = intval($_POST['cupo_maximo']);

        if ($hora_inicio < $HORA_APERTURA || $hora_inicio > $HORA_CIERRE) {
            $mensaje = "EL HORARIO ESTÁ FUERA DE LOS LÍMITES CONFIGURADOS.";
            $tipo_alerta = "error";
        } else {
            $comprobar_hora = mysqli_query($conexion, "SELECT id FROM clases WHERE instructor_id = $instructor_id AND hora_inicio = '$hora_inicio' LIMIT 1");
            if (mysqli_num_rows($comprobar_hora) > 0) {
                $mensaje = "EL ENTRENADOR SELECCIONADO YA TIENE UNA CLASE A ESA HORA.";
                $tipo_alerta = "error";
            } else {
                $query = "INSERT INTO clases (nombre, descripcion, instructor_id, hora_inicio, duracion_horas, tipo, cupo_maximo) 
                          VALUES ('$nombre', '$descripcion', $instructor_id, '$hora_inicio', $duracion, '$tipo', $cupo)";
                if (mysqli_query($conexion, $query)) {
                    $mensaje = "CLASE IMPUESTA Y ASIGNADA EXITOSAMENTE.";
                    $tipo_alerta = "exito";
                }
            }
        }
    }
}

if (isset($_GET['eliminar_clase_id'])) {
    $clase_id = intval($_GET['eliminar_clase_id']);
    if (mysqli_query($conexion, "DELETE FROM clases WHERE id = $clase_id")) {
        $mensaje = "CLASE PURGADA DEL SISTEMA CORRECTAMENTE.";
        $tipo_alerta = "error";
    }
}

$entrenadores = mysqli_query($conexion, "SELECT id, nombre FROM usuarios WHERE rol_id = 2 ORDER BY nombre ASC");

$clases_gratis = mysqli_query($conexion, "SELECT c.*, u.nombre AS profesor_nombre, (SELECT COUNT(*) FROM inscripciones_clases WHERE horario_id = c.id) AS total_inscritos FROM clases c JOIN usuarios u ON c.instructor_id = u.id WHERE c.tipo = 'gratis' ORDER BY c.hora_inicio ASC");

$clases_pago = mysqli_query($conexion, "SELECT c.*, u.nombre AS profesor_nombre, (SELECT COUNT(*) FROM inscripciones_clases WHERE horario_id = c.id) AS total_inscritos FROM clases c JOIN usuarios u ON c.instructor_id = u.id WHERE c.tipo = 'pago' ORDER BY c.hora_inicio ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Panel Maestro de Clases</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght=700&family=Poppins:wght=400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css?v=5.0">
</head>
<body class="fondo-staff-admin">

    <div class="contenedor-dashboard">
        
        <nav class="sidebar-gotica">
        <div class="brand-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
            <h2>DIVINITY ADMIN</h2>
        </div>
        <ul class="menu-enlaces">
            <li><a href="admin_dashboard.php"> Inicio</a></li>
            <li><a href="admin_clientes.php"> Atletas</a></li>
            <li><a href="admin_profesores.php"> Entrenadores</a></li>
            <li><a href="admin_membresias.php"> Membresías</a></li>
            <li><a href="admin_clases.php" class="activo"> Cronograma</a></li>
            <li><a href="admin_logros.php"> Crear Logros</a></li>
            <li><a href="perfil.php"> Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
        </ul>
    </nav>

        <main class="contenido-principal">
            
            <header class="encabezado-dashboard">
                <div class="saludo">
                    <h1>DECRETOS HORARIOS</h1>
                    <p>SUPERVISIÓN GENERAL, CONTROL DE AFOROS Y FEUDOS DE CLASES</p>
                </div>
                <div class="rango-badge-admin">DIVINITY ADMIN</div>
            </header>

            <?php if (!empty($mensaje)): ?>
                <div class="alerta-global <?php echo $tipo_alerta == 'error' ? 'alerta-admin-error' : 'alerta-admin-exito'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="malla-forja">
                
                <div class="bloque-formulario">
                    <h3 class="titulo-seccion-admin">CREACIÓN ADMINISTRATIVA</h3>
                    
                    <form action="admin_clases.php" method="POST">
                        <input type="hidden" name="accion_admin_crear" value="1">
                        
                        <div class="campo-panel">
                            <label>NOMBRE DE LA SESIÓN</label>
                            <input type="text" name="nombre" placeholder="Ej: Spartan Yoga, Elite Power" required>
                        </div>
                        <div class="campo-panel">
                            <label>ASIGNAR INSTRUCTOR (ENTRENADOR)</label>
                            <select name="instructor_id" required class="select-personalizado-admin">
                                <?php while($ent = mysqli_fetch_assoc($entrenadores)): ?>
                                    <option value="<?php echo $ent['id']; ?>"><?php echo htmlspecialchars($ent['nombre']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="malla-forja-interna">
                            <div class="campo-panel">
                                <label>INICIO</label>
                                <input type="time" name="hora_inicio" required>
                            </div>
                            <div class="campo-panel">
                                <label>DURACIÓN</label>
                                <input type="number" name="duracion" min="1" max="5" value="1" required>
                            </div>
                        </div>
                        <div class="malla-forja-interna">
                            <div class="campo-panel">
                                <label>TIPO DE ACCESO</label>
                                <select name="tipo" class="select-personalizado-admin">
                                    <option value="gratis">Gratuita</option>
                                    <option value="pago">De Pago</option>
                                </select>
                            </div>
                            <div class="campo-panel">
                                <label>CUPO MÁXIMO</label>
                                <input type="number" name="cupo_maximo" value="<?php echo $CUPO_MAX_GLOBAL; ?>" min="1" required>
                            </div>
                        </div>
                        <div class="campo-panel">
                            <label>DESCRIPCIÓN DE LA CLASE</label>
                            <textarea name="descripcion" placeholder="Instrucciones globales de la sesión..." required></textarea>
                        </div>
                        <button type="submit" class="btn-panel-neon-admin">FORZAR CREACIÓN</button>
                    </form>
                </div>

                <div class="bloque-tabla">
                    <div class="zona-intercambio-botones">
                        <button type="button" id="tab-gratis" class="btn-intercambio activo">CRONOGRAMA GRATUITO</button>
                        <button type="button" id="tab-pago" class="btn-intercambio">CRONOGRAMA DE PAGO</button>
                    </div>

                    <div id="panel-gratis" class="bloque-tabla-dinamica">
                        <table class="tabla-hardcore">
                            <thead>
                                <tr>
                                    <th>HORA</th>
                                    <th>CLASE</th>
                                    <th>INSTRUCTOR</th>
                                    <th>INSCRITOS</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($clases_gratis)): ?>
                                    <tr>
                                        <td class="hora-dorada-admin"><?php echo date("g:i a", strtotime($row['hora_inicio'])); ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                        <td class="nombre-profesor-admin"><?php echo htmlspecialchars($row['profesor_nombre']); ?></td>
                                        <td>
                                            <button type="button" class="btn-ver-guerreros" data-id="<?php echo $row['id']; ?>">
                                                👁️ <?php echo $row['total_inscritos']; ?> / <?php echo $row['cupo_maximo']; ?>
                                            </button>
                                        </td>
                                        <td>
                                            <a href="admin_clases.php?eliminar_clase_id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('¿Destruir esta clase permanentemente?');">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="panel-pago" class="bloque-tabla-dinamica oculto-inicial">
                        <table class="tabla-hardcore">
                            <thead>
                                <tr>
                                    <th>HORA</th>
                                    <th>CLASE PRIVADA</th>
                                    <th>INSTRUCTOR</th>
                                    <th>INSCRITOS</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($clases_pago)): ?>
                                    <tr>
                                        <td class="hora-dorada-admin"><?php echo date("g:i a", strtotime($row['hora_inicio'])); ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                        <td class="nombre-profesor-admin"><?php echo htmlspecialchars($row['profesor_nombre']); ?></td>
                                        <td>
                                            <button type="button" class="btn-ver-guerreros" data-id="<?php echo $row['id']; ?>">
                                                👁️ <?php echo $row['total_inscritos']; ?> / <?php echo $row['cupo_maximo']; ?>
                                            </button>
                                        </td>
                                        <td>
                                            <a href="admin_clases.php?eliminar_clase_id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('¿Destruir esta clase permanentemente?');">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <div id="modal-guerreros" class="modal-overlay">
        <div class="modal-caja-admin">
            <h2 class="modal-titulo-admin">GUERREROS INSCRITOS</h2>
            
            <div id="lista-guerreros-ajax" class="lista-guerreros-contenedor">
                <!-- Inyección asíncrona -->
            </div>

            <div class="modal-botones-zona">
                <button type="button" class="btn-modal-cancelar-admin" id="btn-cerrar-admin">CERRAR MONITOREO</button>
            </div>
        </div>
    </div>

    <!-- VINCULACIÓN DEL SCRIPT EXTERNO PURIFICADO -->
    <script src="assets/js/clases.js?v=5.0"></script>
</body>
</html>