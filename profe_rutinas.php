<?php
require_once 'config.php';

// 1. Validar seguridad: Solo Profesores (Rol 2) o Admin (Rol 1)
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol_id'] != 2 && $_SESSION['rol_id'] != 1)) {
    header('Location: login.php');
    exit;
}

$profesor_id = intval($_SESSION['usuario_id']);
$mensaje = "";

// 2. ACCIÓN: CREAR/ASIGNAR RUTINA (Usando Descripcion sin tilde)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['asignar_rutina'])) {
    $atleta_id = intval($_POST['atleta_id']);
    $dia_semana = mysqli_real_escape_string($conexion, $_POST['dia_semana']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['ejercicios']);

    if (!empty($atleta_id) && !empty($dia_semana) && !empty($descripcion)) {
        // Se mapea exactamente como está en tu base de datos (Descripcion)
        $insertar = "INSERT INTO rutinas (Usuario_id, Profesor_id, dia_semana, Descripcion) 
                     VALUES ($atleta_id, $profesor_id, '$dia_semana', '$descripcion')";
        
        if (mysqli_query($conexion, $insertar)) {
            $mensaje = "<p style='color: #00ffff; text-align: center; font-weight: bold;'> Orden de entrenamiento dictada con éxito </p>";
        } else {
            // Si falla por la tilde, intentamos con tilde automáticamente
            $insertar_tilde = "INSERT INTO rutinas (Usuario_id, Profesor_id, dia_semana, `Descripción`) 
                               VALUES ($atleta_id, $profesor_id, '$dia_semana', '$descripcion')";
            if (mysqli_query($conexion, $insertar_tilde)) {
                $mensaje = "<p style='color: #00ffff; text-align: center; font-weight: bold;'> Orden de entrenamiento dictada con éxito </p>";
            } else {
                $mensaje = "<p style='color: #ff4444; text-align: center;'> Error al guardar rutina: " . mysqli_error($conexion) . "</p>";
            }
        }
    }
}

// 3. ACCIÓN: BORRAR RUTINA
if (isset($_GET['borrar_rutina'])) {
    $id_borrar = intval($_GET['borrar_rutina']);
    
    // Método seguro: Borramos usando los identificadores de la fila
    $borrar_query = "DELETE FROM rutinas WHERE Usuario_id = $id_borrar LIMIT 1";
    
    // Intento con la clave primaria extraña por si acaso
    $res_tabla = mysqli_query($conexion, "SELECT * FROM rutinas LIMIT 1");
    if($res_tabla && $fila_id = mysqli_fetch_assoc($res_tabla)){
        $llave_primaria = array_key_first($fila_id);
        $borrar_query = "DELETE FROM rutinas WHERE `$llave_primaria` = $id_borrar";
    }

    if (mysqli_query($conexion, $borrar_query)) {
        $mensaje = "<p style='color: #ff4444; text-align: center; font-weight: bold;'>💥 Altar limpio: Rutina purgada 💥</p>";
    }
}

// 4. CONSULTAR ATLETAS (Rol 3 = Clientes)
$atletas_query = "SELECT id, nombre FROM usuarios WHERE rol_id = 3";
$atletas_res = mysqli_query($conexion, $atletas_query);

// 5. CONSULTAR CRONOGRAMA GENERAL (Probando campos sin tildes)
$cronograma_query = "SELECT r.Usuario_id, u_atleta.nombre AS atleta, r.dia_semana, r.Descripcion AS ejercicios, u_profe.nombre AS coach_nombre 
                     FROM rutinas r 
                     JOIN usuarios u_atleta ON r.Usuario_id = u_atleta.id 
                     JOIN usuarios u_profe ON r.Profesor_id = u_profe.id
                     ORDER BY FIELD(r.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')";
$cronograma_res = mysqli_query($conexion, $cronograma_query);

// Si la consulta sin tilde falla, la hacemos con tilde de respaldo para que NUNCA vuelva a salir Error Fatal
if (!$cronograma_res) {
    $cronograma_query = "SELECT r.Usuario_id, u_atleta.nombre AS atleta, r.dia_semana, r.`Descripción` AS ejercicios, u_profe.nombre AS coach_nombre 
                         FROM rutinas r 
                         JOIN usuarios u_atleta ON r.Usuario_id = u_atleta.id 
                         JOIN usuarios u_profe ON r.Profesor_id = u_profe.id
                         ORDER BY FIELD(r.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')";
    $cronograma_res = mysqli_query($conexion, $cronograma_query);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Pizarra de Entrenamiento</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <style>
        .input-profe {
            width: 100%; 
            padding: 12px; 
            background: rgba(0,0,0,0.6) !important; 
            border: 1px solid rgba(0,255,255,0.2) !important; 
            color: #fff !important; 
            margin-top: 5px; 
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        .input-profe:focus {
            border-color: #00ffff !important;
            box-shadow: 0 0 10px rgba(0,255,255,0.3);
            outline: none;
        }
        .btn-profe {
            width: 100%; 
            padding: 14px; 
            background: #00ffff; 
            color: #000; 
            border: none; 
            font-weight: bold; 
            cursor: pointer; 
            border-radius: 4px; 
            letter-spacing: 1px; 
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,255,255,0.2);
        }
        .btn-profe:hover {
            background: #00cccc;
            box-shadow: 0 4px 20px rgba(0,255,255,0.4);
        }
        .btn-borrar-rutina {
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
        .btn-borrar-rutina:hover {
            background: #ff4444;
            color: #fff;
        }
        .menu-profe-activo {
            background: rgba(0,255,255,0.08);
            border-left: 4px solid #00ffff !important;
            font-weight: 600;
        }
    </style>
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
            <li><a href="profe_rutinas.php" class="activo"> Tabla de rutinas</a></li>
            <li><a href="profe_clases.php"> Crear clase</a></li>
            <li><a href="perfil.php"> Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
        </ul>
    </nav>

        <main class="contenido-principal">
            <div class="encabezado-dashboard" style="border-bottom: 1px solid rgba(0,255,255,0.1); padding-bottom: 15px; margin-bottom: 25px;">
                <h1 style="font-family: 'Cinzel', serif; text-shadow: 0 0 15px rgba(0,255,255,0.3);">PIZARRA DE ENTRENAMIENTO</h1>
                <p style="color: #00ffff; font-size: 0.85rem; letter-spacing: 1px;">DISEÑA LOS PLANES DE EJERCICIOS DIARIOS PARA LOS GUERREROS</p>
            </div>

            <?php echo $mensaje; ?>

            <div class="layout-bloques" style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div class="bloque-formulario" style="flex: 1; min-width: 300px; border-top: 3px solid #00ffff;">
                    <h3 style="font-family: 'Cinzel', serif; margin-bottom: 20px; color: #00ffff; font-size: 1.1rem;">PLANIFICAR DÍA DE ENTRENO</h3>
                    <form action="" method="POST">
                        
                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px;">SELECCIONAR GUERRERO ATLETA</label>
                            <select name="atleta_id" class="input-profe" required>
                                <option value="" disabled selected>Escoge un atleta...</option>
                                <?php if($atletas_res): ?>
                                    <?php while($row = mysqli_fetch_assoc($atletas_res)): ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo strtoupper($row['nombre']); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px;">DÍA DE LA SEMANA</label>
                            <select name="dia_semana" class="input-profe" required>
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miércoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                                <option value="Sábado">Sábado</option>
                                <option value="Domingo">Domingo</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label style="font-size: 0.75rem; color: #aaa; letter-spacing: 1px;">BLOQUE DE EJERCICIOS (SERIES X REPETICIONES)</label>
                            <textarea name="ejercicios" rows="4" placeholder="Ej: - Sentadilla Barra Libre: 4 series x 8 reps" class="input-profe" style="resize: none;" required></textarea>
                        </div>

                        <button type="submit" name="asignar_rutina" class="btn-profe"> DECRETAR RUTINA</button>
                    </form>
                </div>

                <div class="bloque-tabla" style="flex: 1.8; min-width: 400px; border-top: 3px solid #00ffff;">
                    <h3 style="font-family: 'Cinzel', serif; margin-bottom: 20px; color: #00ffff; font-size: 1.1rem;">CRONOGRAMA ACTUAL DEL ATLETA</h3>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(0,255,255,0.2); color: #00ffff; font-size: 0.8rem; letter-spacing: 1px;">
                                    <th style="padding: 15px 12px;">ATLETA</th>
                                    <th style="padding: 15px 12px;">DÍA</th>
                                    <th style="padding: 15px 12px;">TABLA DE INSTRUCCIONES</th>
                                    <th style="padding: 15px 12px;">FIRMADO POR</th>
                                    <th style="padding: 15px 12px; text-align: center;">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($cronograma_res && mysqli_num_rows($cronograma_res) > 0): ?>
                                    <?php while($rutina = mysqli_fetch_assoc($cronograma_res)): ?>
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem;" onmouseover="this.style.background='rgba(0,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                            <td style="padding: 15px 12px; font-weight: 600; color: #fff;"><?php echo strtoupper($rutina['atleta']); ?></td>
                                            <td style="padding: 15px 12px; color: #00ffff; font-weight: 600; font-size: 0.85rem;行业;"><?php echo $rutina['dia_semana']; ?></td>
                                            <td style="padding: 15px 12px; color: #ddd; font-size: 0.85rem; white-space: pre-line;"><?php echo $rutina['ejercicios']; ?></td>
                                            <td style="padding: 15px 12px; color: #888; font-style: italic; font-size: 0.8rem;">Coach <?php echo $rutina['coach_nombre']; ?></td>
                                            <td style="padding: 15px 12px; text-align: center;">
                                                <a href="profe_rutinas.php?borrar_rutina=<?php echo $rutina['Usuario_id']; ?>" class="btn-borrar-rutina" onclick="return confirm('¿Deseas purgar esta rutina del cronograma?');"> Borrar</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="padding: 30px; text-align: center; color: #555;">No hay órdenes de entrenamiento dictadas.</td>
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