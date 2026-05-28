<?php
// Incluimos la conexión y el control de sesiones
require_once 'config.php';

// CONTROL DE ACCESO: Solo administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: login.php');
    exit;
}

$mensaje = "";
$tipo_alerta = "";

// ACCIÓN 1: PROCESAR ELIMINACIÓN DESDE EL MODAL FLOTANTE
if (isset($_GET['borrar_id'])) {
    $id_borrar = intval($_GET['borrar_id']);
    $query_borrar = "DELETE FROM usuarios WHERE id = $id_borrar AND rol_id = 3";
    if (mysqli_query($conexion, $query_borrar)) {
        $mensaje = "ATLETA PURGADO Y ELIMINADO CORRECTAMENTE DEL TEMPLO.";
        $tipo_alerta = "exito";
    } else {
        $mensaje = "ERROR AL INTENTAR ELIMINAR EL REGISTRO DEL ATLETA.";
        $tipo_alerta = "error";
    }
}

// ACCIÓN 2: PROCESAR ACTUALIZACIÓN DESDE EL MODAL FLOTANTE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['procesar_edicion_modal'])) {
    $id_editar = intval($_POST['atleta_id']);
    $nombre_nuevo = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email_nuevo = mysqli_real_escape_string($conexion, $_POST['email']);

    if (!empty($nombre_nuevo) && !empty($email_nuevo)) {
        $query_update = "UPDATE usuarios SET nombre = '$nombre_nuevo', email = '$email_nuevo' WHERE id = $id_editar AND rol_id = 3";
        if (mysqli_query($conexion, $query_update)) {
            $mensaje = "DATOS DEL ATLETA ACTUALIZADOS EN EL TEMPLO.";
            $tipo_alerta = "exito";
        } else {
            $mensaje = "ERROR AL ACTUALIZAR LOS REGISTROS EN LA BASE DE DATOS.";
            $tipo_alerta = "error";
        }
    } else {
        $mensaje = "CAMPOS VACÍOS DETECTADOS. OPERACIÓN RECHAZADA.";
        $tipo_alerta = "error";
    }
}

// Consultamos ÚNICAMENTE a los usuarios con rol_id = 3 (Atletas)
$query_usuarios = "SELECT u.id, u.nombre, u.email, u.rol_id, u.color_tema, m.nombre as plan 
                   FROM usuarios u 
                   LEFT JOIN membresias m ON u.membresia_id = m.id 
                   WHERE u.rol_id = 3
                   ORDER BY u.id DESC";
$resultado_usuarios = mysqli_query($conexion, $query_usuarios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Gestión de Atletas</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="assets/css/tablas.css">
    <link rel="stylesheet" href="assets/css/modales.css">
</head>
<body class="fondo-staff-admin">

    <nav class="sidebar-gotica">
        <div class="brand-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
            <h2>DIVINITY ADMIN</h2>
        </div>
        <ul class="menu-enlaces">
            <li><a href="admin_dashboard.php"> Inicio</a></li>
            <li><a href="admin_clientes.php" class="activo"> Atletas</a></li>
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
                <h1>COMUNIDAD DE ATLETAS</h1>
                <p>AUDITORÍA DE ROLES, COLORES NEÓN Y PLANES ADQUIRIDOS</p>
            </div>
            <div class="rango-badge">CONTROL DE CUENTAS</div>
        </header>

        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo $tipo_alerta == 'error' ? 'alerta-error-sistema' : 'alerta-exito-sistema'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="bloque-tabla" style="width: 100%;">
            <h2>USUARIOS DENTRO DEL SISTEMA</h2>
            <div class="tabla-contenedor">
                <table class="tabla-gotica">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE COMPLETO</th>
                            <th>CORREO ELECTRÓNICO</th>
                            <th>ROL ASIGNADO</th>
                            <th>NEÓN ACTIVO</th>
                            <th>PLAN ACTUAL</th>
                            <th style="text-align: center;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($resultado_usuarios) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($resultado_usuarios)): ?>
                                <tr>
                                    <td><code>#<?php echo $user['id']; ?></code></td>
                                    <td class="resaltado-dorado"><?php echo strtoupper($user['nombre']); ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><span style="color:#ff0000; font-weight:600;">CLIENTE</span></td>
                                    <td>
                                        <div style="width: 15px; height: 15px; background: <?php echo $user['color_tema']; ?>; border-radius: 50%; display: inline-block; box-shadow: 0 0 8px <?php echo $user['color_tema']; ?>; vertical-align: middle; margin-right: 5px;"></div>
                                        <?php 
                                            if (strtoupper($user['color_tema']) === '#FFD700') {
                                                echo '<span style="color:#ffd700; font-weight:600; font-size:0.8rem;">DORADO</span>';
                                            } else {
                                                echo '<span style="font-size:0.8rem; color:#aaa;">PERSONALIZADO</span>';
                                            }
                                        ?>
                                    </td>
                                    <td><em><?php echo $user['plan'] ? $user['plan'] : 'Inactivo'; ?></em></td>
                                    <td>
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <a href="#" class="btn-abrir-modificar" 
                                               style="color: #ecc94b; text-decoration: none; border: 1px solid #ecc94b; padding: 4px 10px; font-size: 0.75rem; font-weight: 600;"
                                               data-id="<?php echo $user['id']; ?>"
                                               data-nombre="<?php echo $user['nombre']; ?>"
                                               data-email="<?php echo $user['email']; ?>">Modificar</a>
                                               
                                            <a href="#" class="btn-abrir-borrar" 
                                               style="color: #ff4444; text-decoration: none; border: 1px solid #ff4444; padding: 4px 10px; font-size: 0.75rem; font-weight: 600;"
                                               data-id="<?php echo $user['id']; ?>"
                                               data-nombre="<?php echo $user['nombre']; ?>">Borrar</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #666; font-style: italic;">No hay atletas registrados en este momento.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>


    <div class="modal-overlay" id="modal-modificar">
        <div class="modal-ventana" style="border-top: 3px solid #ecc94b;">
            <h3>ALTERAR ALMA</h3>
            <p>MODIFICACIÓN DE ATLETA EN LOS REGISTROS</p>
            
            <form id="form-modal-modificar" action="admin_clientes.php" method="POST">
                <input type="hidden" name="atleta_id" id="modal-edit-id">
                
                <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px; font-weight: 600;">NOMBRE COMPLETO</label>
                <input type="text" name="nombre" id="modal-edit-nombre" class="modal-input" required>

                <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px; font-weight: 600;">CORREO ELECTRÓNICO</label>
                <input type="email" name="email" id="modal-edit-email" class="modal-input" required>

                <div class="modal-botones-zona">
                    <button type="button" class="btn-modal btn-modal-cancelar btn-cerrar-modal">CANCELAR</button>
                    <button type="submit" name="procesar_edicion_modal" class="btn-modal btn-modal-modificar">MODIFICAR</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-eliminar">
        <div class="modal-ventana" style="border-top: 3px solid #ff4444;">
            <h3 style="color: #ff4444;">¿ESTÁS SEGURO?</h3>
            <p>Se va a purgar permanentemente al guerrero: <br><strong id="nombre-atleta-borrar" style="color: #fff; font-size: 1rem; display: block; margin-top: 10px;"></strong></p>
            
            <div class="modal-botones-zona">
                <button type="button" class="btn-modal btn-modal-cancelar btn-cerrar-modal">CANCELAR</button>
                <a href="#" id="btn-confirmar-borrar" class="btn-modal btn-modal-borrar">BORRAR ATLETA</a>
            </div>
        </div>
    </div>

    <script src="assets/js/gestion_modales.js"></script>
</body>
</html>