<?php
require_once 'config.php';

// Seguridad: Si no es Admin (Rol 1), rebota al login
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: login.php');
    exit;
}

$mensaje = "";
$tipo_alerta = "";

// 1. PROCESAR AGREGAR ENTRENADOR DIRECTO (Formulario Izquierdo)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_registrar'])) {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $email = mysqli_real_escape_string($conexion, trim($_POST['email']));
    $biografia = mysqli_real_escape_string($conexion, trim($_POST['biografia']));
    $password = $_POST['password'];

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        $comprobar = mysqli_query($conexion, "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1");
        if (mysqli_num_rows($comprobar) > 0) {
            $mensaje = "EL CORREO YA ESTÁ REGISTRADO EN EL TEMPLO.";
            $tipo_alerta = "error";
        } else {
            $password_encriptada = password_hash($password, PASSWORD_BCRYPT);
            // Rol 2 (Instructor), aprobado directamente (estado_aprobado = 1)
            $query = "INSERT INTO usuarios (nombre, email, password, rol_id, color_tema, biografia, estado_aprobado) 
                      VALUES ('$nombre', '$email', '$password_encriptada', 2, '#00ffff', '$biografia', 1)";
            if (mysqli_query($conexion, $query)) {
                $mensaje = "ENTRENADOR ADMITIDO EN LAS FILAS CON ÉXITO.";
                $tipo_alerta = "exito";
            }
        }
    }
}

// 2. PROCESAR ACCIONES DE SOLICITUDES (Aceptar / Rechazar)
if (isset($_GET['solicitud_id']) && isset($_GET['estado'])) {
    $solicitud_id = intval($_GET['solicitud_id']);
    $estado = $_GET['estado'];

    if ($estado === 'aceptar') {
        $query = "UPDATE usuarios SET estado_aprobado = 1 WHERE id = $solicitud_id AND rol_id = 2";
        if (mysqli_query($conexion, $query)) {
            $mensaje = "PACTO ACEPTADO. EL INSTRUCTOR HA SIDO INCORPORADO.";
            $tipo_alerta = "exito";
        }
    } elseif ($estado === 'rechazar') {
        $query = "DELETE FROM usuarios WHERE id = $solicitud_id AND rol_id = 2 AND estado_aprobado = 0";
        if (mysqli_query($conexion, $query)) {
            $mensaje = "SOLICITUD RECHAZADA Y PURGADA DEL SISTEMA.";
            $tipo_alerta = "error";
        }
    }
}

// 3. PROCESAR ELIMINACIÓN DE ENTRENADOR ACTIVO
if (isset($_GET['borrar_id'])) {
    $borrar_id = intval($_GET['borrar_id']);
    $query = "DELETE FROM usuarios WHERE id = $borrar_id AND rol_id = 2";
    if (mysqli_query($conexion, $query)) {
        $mensaje = "ENTRENADOR ELIMINADO CORRECTAMENTE.";
        $tipo_alerta = "error";
    }
}

// 4. PROCESAR EDICIÓN DESDE MODAL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['procesar_edicion_modal'])) {
    $id_edit = intval($_POST['id_edit']);
    $nombre_edit = mysqli_real_escape_string($conexion, trim($_POST['nombre_edit']));
    $email_edit = mysqli_real_escape_string($conexion, trim($_POST['email_edit']));
    $biografia_edit = mysqli_real_escape_string($conexion, trim($_POST['biografia_edit']));

    $query = "UPDATE usuarios SET nombre = '$nombre_edit', email = '$email_edit', biografia = '$biografia_edit' WHERE id = $id_edit AND rol_id = 2";
    if (mysqli_query($conexion, $query)) {
        $mensaje = "DATOS DEL INSTRUCTOR ACTUALIZADOS.";
        $tipo_alerta = "exito";
    }
}

// OBTENER DATOS PARA LAS TABLAS
$entrenadores_activos = mysqli_query($conexion, "SELECT * FROM usuarios WHERE rol_id = 2 AND estado_aprobado = 1 ORDER BY id DESC");
$solicitudes_pendientes = mysqli_query($conexion, "SELECT * FROM usuarios WHERE rol_id = 2 AND estado_aprobado = 0 ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity Admin - Forja de Entrenadores</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css?v=1.1">
</head>
<body class="fondo-staff-admin">

    <div class="contenedor-dashboard">
        
        <aside class="sidebar-gotica">
            <div class="brand-zona">
                <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
                <h2>GYMDIVINITY</h2>
            </div>
            <ul class="menu-enlaces">
                <li><a href="admin_dashboard.php">INICIO</a></li>
                <li><a href="admin_clientes.php">ATLETAS</a></li>
                <li><a href="admin_profesores.php" class="activo">ENTRENADORES</a></li>
                <li><a href="admin_membresias.php">MEMBRESÍAS</a></li>
                <li><a href="admin_clases.php">CRONOGRAMA</a></li>
                <li><a href="admin_logros.php">CREAR LOGROS</a></li>
                <li><a href="admin_perfil.php">MI PERFIL</a></li>
                <li class="separador-logout"><a href="logout.php" class="logout-link">CERRAR TEMPLO</a></li>
            </ul>
        </aside>

        <main class="contenido-principal">
            
            <header class="encabezado-dashboard">
                <div class="saludo">
                    <h1>FORJA DE ENTRENADORES</h1>
                    <p>GESTIÓN DE INSTRUCTORES Y ADMISIÓN DE ASPIRANTES</p>
                </div>
                <div class="rango-badge">ADMIN SUPREMO</div>
            </header>

            <?php if (!empty($mensaje)): ?>
                <div class="<?php echo $tipo_alerta == 'error' ? 'alerta-error' : 'alerta-exito'; ?>" style="margin-bottom: 20px; padding: 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; letter-spacing: 1px; text-align: center; <?php echo $tipo_alerta == 'error' ? 'background: rgba(255,0,0,0.1); border: 1px solid #ff0000; color: #ff4d4d;' : 'background: rgba(0,255,0,0.08); border: 1px solid #00ff00; color: #4df44d;'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="malla-forja">
                
                <div class="bloque-formulario">
                    <h3 style="font-family: 'Cinzel', serif; font-size: 1.1rem; letter-spacing: 1px; margin-bottom: 20px; border-bottom: 1px solid var(--borde-sutil); padding-bottom: 10px;">INSCRIBIR INSTRUCTOR</h3>
                    
                    <form action="admin_profesores.php" method="POST">
                        <input type="hidden" name="accion_registrar" value="1">
                        
                        <div class="campo-panel">
                            <label>NOMBRE COMPLETO</label>
                            <input type="text" name="nombre" placeholder="Ej: Brayan Gonzalez" required autocomplete="off">
                        </div>
                        <div class="campo-panel">
                            <label>CORREO ELECTRÓNICO</label>
                            <input type="email" name="email" placeholder="instructor@divinity.com" required autocomplete="off">
                        </div>
                        <div class="campo-panel">
                            <label>ESPECIALIDAD / BIOGRAFÍA</label>
                            <textarea name="biografia" placeholder="Ej: Especialista en Powerlifting, Hipertrofia..." required></textarea>
                        </div>
                        <div class="campo-panel">
                            <label>CONTRASEÑA INICIAL</label>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn-panel-neon">VINCULAR ENTRENADOR</button>
                    </form>
                </div>

                <div class="bloque-tabla">
                    
                    <div class="zona-intercambio-botones">
                        <button type="button" id="btn-vista-activos" class="btn-intercambio activo">CONVENTO ACTIVO</button>
                        <button type="button" id="btn-vista-solicitudes" class="btn-intercambio">
                            SOLICITUDES PENDIENTES 
                            <?php if(mysqli_num_rows($solicitudes_pendientes) > 0): ?>
                                <span class="burbuja-alerta"><?php echo mysqli_num_rows($solicitudes_pendientes); ?></span>
                            <?php endif; ?>
                        </button>
                    </div>

                    <div id="contenedor-tabla-activos" class="bloque-tabla-dinamica">
                        <table class="tabla-hardcore">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NOMBRE</th>
                                    <th>CORREO</th>
                                    <th>ESPECIALIDAD</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($entrenadores_activos) == 0): ?>
                                    <tr><td colspan="5" class="texto-vacio">No hay instructores activos en el Templo.</td></tr>
                                <?php else: ?>
                                    <?php while($profe = mysqli_fetch_assoc($entrenadores_activos)): ?>
                                        <tr>
                                            <td>#<?php echo $profe['id']; ?></td>
                                            <td class="resaltado-neon"><?php echo htmlspecialchars($profe['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($profe['email']); ?></td>
                                            <td class="texto-biografia-celda"><?php echo htmlspecialchars($profe['biografia']); ?></td>
                                            <td>
                                                <button type="button" class="action-btn btn-abrir-modificar" 
                                                        data-id="<?php echo $profe['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($profe['nombre']); ?>"
                                                        data-email="<?php echo htmlspecialchars($profe['email']); ?>"
                                                        data-biografia="<?php echo htmlspecialchars($profe['biografia']); ?>">
                                                    Modificar
                                                </button>
                                                <button type="button" class="action-btn delete-btn btn-abrir-borrar"
                                                        data-id="<?php echo $profe['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($profe['nombre']); ?>">
                                                    Borrar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="contenedor-tabla-solicitudes" class="bloque-tabla-dinamica oculto-inicial">
                        <table class="tabla-hardcore">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ASPIRANTE</th>
                                    <th>CORREO</th>
                                    <th>SABERES / EXPERIENCIA</th>
                                    <th>DECISIÓN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($solicitudes_pendientes) == 0): ?>
                                    <tr><td colspan="5" class="texto-vacio">Ningún alma solicita unirse al convento por ahora.</td></tr>
                                <?php else: ?>
                                    <?php while($sol = mysqli_fetch_assoc($solicitudes_pendientes)): ?>
                                        <tr>
                                            <td>#<?php echo $sol['id']; ?></td>
                                            <td style="color: var(--neon-dorado); font-weight:600;"><?php echo htmlspecialchars($sol['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($sol['email']); ?></td>
                                            <td class="texto-biografia-celda"><?php echo htmlspecialchars($sol['biografia']); ?></td>
                                            <td>
                                                <a href="admin_profesores.php?solicitud_id=<?php echo $sol['id']; ?>&estado=aceptar" class="action-btn btn-aceptar-solicitud">Aceptar</a>
                                                <a href="admin_profesores.php?solicitud_id=<?php echo $sol['id']; ?>&estado=rechazar" class="action-btn delete-btn">Rechazar</a>
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

    <div id="modal-modificar" class="modal-overlay">
        <div class="modal-caja">
            <h2 class="modal-titulo">ALTERAR PACTO INSTRUCTOR</h2>
            <form action="admin_profesores.php" method="POST">
                <input type="hidden" name="id_edit" id="modal-edit-id">
                <div class="modal-campo">
                    <label>NOMBRE DEL ENTRENADOR</label>
                    <input type="text" name="nombre_edit" id="modal-edit-nombre" required>
                </div>
                <div class="modal-campo">
                    <label>CORREO ELECTRÓNICO</label>
                    <input type="email" name="email_edit" id="modal-edit-email" required>
                </div>
                <div class="modal-campo">
                    <label>ESPECIALIDAD / EXPERIENCIA</label>
                    <textarea name="biografia_edit" id="modal-edit-biografia" required></textarea>
                </div>
                <div class="modal-botones-zona">
                    <button type="button" class="btn-modal btn-modal-cancelar btn-cerrar-modal">CANCELAR</button>
                    <button type="submit" name="procesar_edicion_modal" class="btn-modal btn-modal-modificar">MODIFICAR</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-eliminar" class="modal-overlay">
        <div class="modal-caja" style="border-top-color: var(--neon-rojo);">
            <h2 class="modal-titulo" style="color: var(--neon-rojo); text-shadow: 0 0 10px rgba(255,0,0,0.4);">EXPULSAR DE LAS FILAS</h2>
            <p class="modal-aviso">¿Estás seguro de revocar los privilegios del entrenador <span id="nombre-profesor-borrar" style="color: #00ffff; font-weight: 600;"></span> y borrarlo para siempre?</p>
            <div class="modal-botones-zona">
                <button type="button" class="btn-modal btn-modal-cancelar btn-cerrar-modal">CONSERVAR</button>
                <a href="#" id="btn-confirmar-borrar" class="btn-modal btn-modal-borrar">ELIMINAR ENTRADA</a>
            </div>
        </div>
    </div>

    <script src="assets/js/gestion_profesores.js"></script>
</body>
</html>