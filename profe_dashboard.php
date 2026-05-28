<?php
// Incluimos la conexión y el control de sesiones centralizado
require_once 'config.php';

// CONTROL DE ACCESO SEGURO: Si no es Instructor (rol 2), rebote inmediato al login
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: login.php');
    exit;
}

// LÓGICA DEL BUSCADOR INTELIGENTE EN VIVO
$busqueda = "";
if (isset($_GET['buscar'])) {
    $busqueda = mysqli_real_escape_string($conexion, trim($_GET['buscar']));
}

// Consulta indexada: Buscamos usuarios que sean clientes (rol 3) filtrados por el buscador si existe
$query_alumnos = "SELECT u.id, u.nombre, u.email, m.nombre as plan 
                  FROM usuarios u 
                  LEFT JOIN membresias m ON u.membresia_id = m.id 
                  WHERE u.rol_id = 3";

if (!empty($busqueda)) {
    $query_alumnos .= " AND u.nombre LIKE '%$busqueda%'";
}
$query_alumnos .= " ORDER BY u.nombre ASC";
$resultado_alumnos = mysqli_query($conexion, $query_alumnos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Panel del Profesor</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="assets/css/tablas.css">
</head>
<body class="fondo-staff-profe">

    <nav class="sidebar-gotica">
        <div class="brand-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
            <h2>DIVINITY ENTRENADOR</h2>
        </div>
        <ul class="menu-enlaces">
            <li><a href="profe_dashboard.php" class="activo"> Inicio</a></li>
            <li><a href="profe_rutinas.php"> Tabla de rutinas</a></li>
            <li><a href="profe_clases.php"> Crear clase</a></li>
            <li><a href="perfil.php"> Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
        </ul>
    </nav>

    <main class="contenido-principal">
        <header class="encabezado-dashboard">
            <div class="saludo">
                <h1>PANEL DEL ENTRENADOR</h1>
                <p style="color: #00ffff; letter-spacing: 2px;">SEGUIMIENTO DE ATLETAS Y ASIGNACIÓN DE CARGAS</p>
            </div>
            <div class="rango-badge" style="border-color: #00ffff; color: #00ffff; background: rgba(0, 255, 255, 0.05); text-shadow: 0 0 5px rgba(0, 255, 255, 0.3);">INSTRUCTOR OFICIAL</div>
        </header>

        <section class="bloque-formulario" style="border-top-color: #00ffff; margin-bottom: 30px;">
            <h2>BUSCADOR DE ATLETAS</h2>
            <form action="profe_dashboard.php" method="GET" style="display: flex; gap: 15px;">
                <div class="campo-grupo" style="flex: 1; margin-bottom: 0;">
                    <input type="text" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Escribe el nombre del guerrero a buscar..." autocomplete="off" style="border-radius: 4px;">
                </div>
                <button type="submit" class="boton-formulario-neon" style="border-color: #00ffff; color: #ffffff; width: auto; padding: 0 30px; text-shadow: 0 0 5px rgba(0, 255, 255, 0.4);">FILTRAR</button>
                <?php if (!empty($busqueda)): ?>
                    <a href="profe_dashboard.php" style="color: #ff4d4d; border: 1px solid #ff4d4d; padding: 12px 20px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.3s; text-shadow: 0 0 5px rgba(255,0,0,0.3);" onmouseover="this.style.background='rgba(255,0,0,0.1)'" onmouseout="this.style.background='transparent'">LIMPIAR</a>
                <?php endif; ?>
            </form>
        </section>

        <div class="bloque-tabla" style="width: 100%;">
            <h2>LISTADO DE ALUMNOS</h2>
            <div class="tabla-contenedor">
                <table class="tabla-gotica">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE COMPLETO</th>
                            <th>CORREO ELECTRÓNICO</th>
                            <th>ESTADO MENSUALIDAD</th>
                            <th>ACCIONES DE MONITOREO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($resultado_alumnos) > 0): ?>
                            <?php while ($alumno = mysqli_fetch_assoc($resultado_alumnos)): ?>
                                <tr>
                                    <td><code>#<?php echo $alumno['id']; ?></code></td>
                                    <td style="color: #ffffff; font-weight: 600;"><?php echo strtoupper($alumno['nombre']); ?></td>
                                    <td><?php echo $alumno['email']; ?></td>
                                    <td>
                                        <?php if ($alumno['plan']): ?>
                                            <span style="color: #4df44d; font-weight: 600;"> ACTIVO (<?php echo $alumno['plan']; ?>)</span>
                                        <?php else: ?>
                                            <span style="color: #ff4d4d; font-weight: 600;"> SIN PLAN ASIGNADO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 10px;">
                                            <a href="profe_rutinas.php?alumno_id=<?php echo $alumno['id']; ?>" style="border: 1px solid #00ffff; color: #00ffff; padding: 6px 12px; font-size: 0.75rem; font-weight: 600; text-decoration: none; border-radius: 2px; transition: 0.3s;" onmouseover="this.style.background='rgba(0, 255, 255, 0.1)'" onmouseout="this.style.background='transparent'"> ASIGNAR RUTINA</a>
                                            <a href="profe_seguimiento.php?alumno_id=<?php echo $alumno['id']; ?>" style="border: 1px solid #ffd700; color: #ffd700; padding: 6px 12px; font-size: 0.75rem; font-weight: 600; text-decoration: none; border-radius: 2px; transition: 0.3s;" onmouseover="this.style.background='rgba(255, 215, 0, 0.1)'" onmouseout="this.style.background='transparent'"> MEDIDAS Y PRs</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #666; padding: 30px;">No se encontraron atletas registrados con ese criterio de búsqueda.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>