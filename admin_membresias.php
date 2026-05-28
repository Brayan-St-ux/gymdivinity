
<?php
require_once 'config.php';

// 1. Validar seguridad
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: login.php');
    exit;
}

$mensaje = "";

// 2. ACCIÓN: ELIMINAR PLAN (D de CRUD)
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    
    // Evitar que borren el plan 1 si es el que tiene amarrado tu cliente de prueba
    if ($id_eliminar == 1) {
        $mensaje = "<p style='color: #ffcc00; text-align: center;'> El Plan Base del Olimpo (ID 1) está protegido y no puede ser destruido.</p>";
    } else {
        $eliminar_query = "DELETE FROM membresias WHERE id = $id_eliminar";
        if (mysqli_query($conexion, $eliminar_query)) {
            $mensaje = "<p style='color: #ff3333; text-align: center;'> Plan destruido y purgado del templo </p>";
        } else {
            $mensaje = "<p style='color: #ff0000; text-align: center;'> No se pudo borrar (puede estar asignado a un usuario).</p>";
        }
    }
}

// 3. ACCIÓN: CREAR PLAN (C de CRUD)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_plan'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $precio = mysqli_real_escape_string($conexion, $_POST['precio']);
    $duracion = mysqli_real_escape_string($conexion, $_POST['duracion_dias']);

    if (!empty($nombre) && !empty($precio) && !empty($duracion)) {
        $insertar = "INSERT INTO membresias (nombre, precio, duracion_dias) VALUES ('$nombre', '$precio', '$duracion')";
        if (mysqli_query($conexion, $insertar)) {
            $mensaje = "<p style='color: #00ff00; text-align: center;'> Plan forjado con éxito en el Olimpo </p>";
        } else {
            $mensaje = "<p style='color: #ff0000; text-align: center;'> Error al forjar el plan: " . mysqli_error($conexion) . "</p>";
        }
    }
}

// 4. CONSULTAR PLANES (R de CRUD)
$query = "SELECT * FROM membresias ORDER BY id DESC";
$resultado = mysqli_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Forja de Membresías</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <style>
        /* Estilos góticos premium para inputs dentro de este módulo */
        .input-gotico {
            width: 100%; 
            padding: 12px; 
            background: rgba(0,0,0,0.6) !important; 
            border: 1px solid rgba(255,215,0,0.2) !important; 
            color: #fff !important; 
            margin-top: 5px; 
            border-radius: 4px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        .input-gotico:focus {
            border-color: #ffd700 !important;
            box-shadow: 0 0 10px rgba(255,215,0,0.3);
            outline: none;
        }
        /* Botón estilizado de eliminación */
        .btn-eliminar {
            background: rgba(255,68,68,0.1);
            color: #ff4444;
            padding: 6px 12px;
            border: 1px solid rgba(255,68,68,0.4);
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-eliminar:hover {
            background: #ff4444;
            color: #fff;
            box-shadow: 0 0 10px rgba(255,68,68,0.5);
        }
        /* Resaltado del menú activo */
        .menu-activo {
            background: rgba(255,215,0,0.08);
            border-left: 4px solid #ffd700 !important;
            font-weight: 600;
        }
    </style>
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
            <li><a href="admin_membresias.php" class="activo"> Membresías</a></li>
            <li><a href="admin_clases.php"> Cronograma</a></li>
            <li><a href="admin_logros.php"> Crear Logros</a></li>
            <li><a href="perfil.php"> Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
        </ul>
    </nav>

        <main class="contenido-principal">
            <div class="encabezado-dashboard" style="border-bottom: 1px solid rgba(255,215,0,0.1); padding-bottom: 15px; margin-bottom: 25px;">
                <h1 style="font-family: 'Cinzel', serif; text-shadow: 0 0 15px rgba(255,215,0,0.3);">FORJA DE MEMBRESÍAS</h1>
                <p style="color: #ffd700; font-size: 0.85rem; letter-spacing: 1px;">DECRETA LOS PACTOS Y PRECIOS DE ACCESO AL INFRAMUNDO</p>
            </div>

            <?php echo $mensaje; ?>

            <div class="layout-bloques" style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div class="bloque-formulario" style="flex: 1; min-width: 300px; border-top: 3px solid #ffd700;">
                    <h3 style="font-family: 'Cinzel', serif; margin-bottom: 20px; color: #ffd700; font-size: 1.1rem; letter-spacing: 1px;">FORJAR NUEVO PACTO</h3>
                    <form action="" method="POST">
                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px;">NOMBRE DE LA MEMBRESÍA</label>
                            <input type="text" name="nombre" placeholder="Ej: Plan Semidios" class="input-gotico" required>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px;">PRECIO DE OFRENDA (COP)</label>
                            <input type="number" name="precio" placeholder="Ej: 60000" class="input-gotico" required>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px;">TIEMPO DE ACCESO (DÍAS)</label>
                            <input type="number" name="duracion_dias" placeholder="Ej: 30" class="input-gotico" required>
                        </div>

                        <button type="submit" name="crear_plan" style="width: 100%; padding: 14px; background: #ffd700; color: #000; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; letter-spacing: 1px; font-family: 'Poppins', sans-serif; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(255,215,0,0.2);"> INFUNDIR PODER AL PLAN</button>
                    </form>
                </div>

                <div class="bloque-tabla" style="flex: 1.8; min-width: 400px; border-top: 3px solid #ffd700;">
                    <h3 style="font-family: 'Cinzel', serif; margin-bottom: 20px; color: #ffd700; font-size: 1.1rem; letter-spacing: 1px;">PACTOS VIGENTES</h3>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(255,215,0,0.2); color: #ffd700; font-size: 0.8rem; letter-spacing: 1px;">
                                    <th style="padding: 15px 12px;">ID</th>
                                    <th style="padding: 15px 12px;">NOMBRE DEL PACTO</th>
                                    <th style="padding: 15px 12px;">VALOR</th>
                                    <th style="padding: 15px 12px;">CICLO</th>
                                    <th style="padding: 15px 12px; text-align: center;">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($resultado) > 0): ?>
                                    <?php while($plan = mysqli_fetch_assoc($resultado)): ?>
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,215,0,0.02)'" onmouseout="this.style.background='transparent'">
                                            <td style="padding: 15px 12px; color: #666; font-size: 0.8rem;"><?php echo $plan['id']; ?></td>
                                            <td style="padding: 15px 12px; font-weight: 600; letter-spacing: 0.5px;"><?php echo strtoupper($plan['nombre']); ?></td>
                                            <td style="padding: 15px 12px; color: #00ff00; font-weight: 600;">$<?php echo number_format($plan['precio'], 0, ',', '.'); ?></td>
                                            <td style="padding: 15px 12px; color: #aaa;"><?php echo $plan['duracion_dias']; ?> Días</td>
                                            <td style="padding: 15px 12px; text-align: center;">
                                                <a href="admin_membresias.php?eliminar=<?php echo $plan['id']; ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas romper este pacto sagrado?');"> Eliminar</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="padding: 30px; text-align: center; color: #555;">El templo está vacío. No hay membresías forjadas.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>