<?php
// Incluimos la conexión y control de sesiones
require_once 'config.php';

// CONTROL DE ACCESO: Solo administradores autorizados
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: login.php');
    exit;
}

$mensaje = "";
$tipo_alerta = "";

// Variables para el modo edición (Modificar)
$modo_edicion = false;
$id_editar = "";
$nombre_editar = "";
$descripcion_editar = "";
$tipo_requisito_editar = "";
$ejercicio_editar = "";
$peso_objetivo_editar = 0;
$reps_objetivo_editar = 0;
$km_objetivo_editar = 0;
$metros_objetivo_editar = 0;
$dias_objetivo_editar = 0;

// 1. ACCIÓN: ELIMINAR LOGRO
if (isset($_GET['borrar_id'])) {
    $id_borrar = intval($_GET['borrar_id']);
    $query_borrar = "DELETE FROM medallas WHERE id = $id_borrar";
    if (mysqli_query($conexion, $query_borrar)) {
        $mensaje = "MEDALLA DESTRUIDA Y REMOVIDA DEL TEMPLO.";
        $tipo_alerta = "error";
    } else {
        $mensaje = "ERROR AL INTENTAR ELIMINAR LA MEDALLA.";
        $tipo_alerta = "error";
    }
}

// 2. ACCIÓN: CARGAR DATOS PARA MODIFICAR
if (isset($_GET['editar_id'])) {
    $id_editar = intval($_GET['editar_id']);
    $query_buscar = "SELECT * FROM medallas WHERE id = $id_editar";
    $res_buscar = mysqli_query($conexion, $query_buscar);
    
    if ($logro_edit = mysqli_fetch_assoc($res_buscar)) {
        $modo_edicion = true;
        $nombre_editar = $logro_edit['nombre'];
        $descripcion_editar = $logro_edit['descripcion'];
        $tipo_requisito_editar = $logro_edit['tipo_requisito'];
        $ejercicio_editar = $logro_edit['ejercicio'];
        $peso_objetivo_editar = $logro_edit['peso_objetivo'];
        $reps_objetivo_editar = $logro_edit['reps_objetivo'];
        $km_objetivo_editar = $logro_edit['km_objetivo'];
        $metros_objetivo_editar = $logro_edit['metros_objetivo'];
        $dias_objetivo_editar = $logro_edit['dias_objetivo'];
    }
}

// 3. ACCIÓN: PROCESAR CREACIÓN O MODIFICACIÓN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_logro'])) {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
    $tipo_requisito = mysqli_real_escape_string($conexion, $_POST['tipo_requisito']);

    // Inicializar variables por defecto
    $ejercicio = "NULL";
    $peso_objetivo = 0;
    $reps_objetivo = 0;
    $km_objetivo = 0;
    $metros_objetivo = 0;
    $dias_objetivo = 0;

    if ($tipo_requisito == 'fuerza') {
        $ejercicio = "'" . mysqli_real_escape_string($conexion, $_POST['ejercicio_fuerza']) . "'";
        $peso_objetivo = !empty($_POST['peso_objetivo']) ? floatval($_POST['peso_objetivo']) : 0;
        $reps_objetivo = !empty($_POST['reps_objetivo']) ? intval($_POST['reps_objetivo']) : 0;
    } elseif ($tipo_requisito == 'cardio') {
        $ejercicio = "'" . mysqli_real_escape_string($conexion, $_POST['ejercicio_cardio']) . "'";
        // CORREGIDO: km_objetivo procesa correctamente los datos del formulario
        $km_objetivo = !empty($_POST['km_objetivo']) ? intval($_POST['km_objetivo']) : 0;
        $metros_objetivo = !empty($_POST['metros_objetivo']) ? intval($_POST['metros_objetivo']) : 0;
    } elseif ($tipo_requisito == 'asistencia') {
        $dias_objetivo = !empty($_POST['dias_objetivo']) ? intval($_POST['dias_objetivo']) : 0;
    }

    if (!empty($nombre) && !empty($descripcion)) {
        if (isset($_POST['id_actualizar']) && !empty($_POST['id_actualizar'])) {
            // MODO ACTUALIZAR
            $id_act = intval($_POST['id_actualizar']);
            $query_update = "UPDATE medallas SET 
                                nombre='$nombre', 
                                descripcion='$descripcion', 
                                tipo_requisito='$tipo_requisito', 
                                ejercicio=$ejercicio, 
                                peso_objetivo=$peso_objetivo, 
                                reps_objetivo=$reps_objetivo, 
                                km_objetivo=$km_objetivo, 
                                metros_objetivo=$metros_objetivo, 
                                dias_objetivo=$dias_objetivo 
                             WHERE id = $id_act";
            if (mysqli_query($conexion, $query_update)) {
                $mensaje = "MEDALLA REFORJADA Y ACTUALIZADA CON ÉXITO.";
                $tipo_alerta = "exito";
            } else {
                $mensaje = "ERROR AL ACTUALIZAR LOS PARÁMETROS EN EL TEMPLO.";
                $tipo_alerta = "error";
            }
        } else {
            // MODO CREAR NUEVO
            $query_insertar = "INSERT INTO medallas (nombre, descripcion, tipo_requisito, ejercicio, peso_objetivo, reps_objetivo, km_objetivo, metros_objetivo, dias_objetivo) 
                               VALUES ('$nombre', '$descripcion', '$tipo_requisito', $ejercicio, $peso_objetivo, $reps_objetivo, $km_objetivo, $metros_objetivo, $dias_objetivo)";
            if (mysqli_query($conexion, $query_insertar)) {
                $mensaje = "MEDALLA FORJADA CON ÉXITO EN EL TEMPLO.";
                $tipo_alerta = "exito";
            } else {
                $mensaje = "ERROR AL CREAR LA MEDALLA EN LA BASE DE DATOS.";
                $tipo_alerta = "error";
            }
        }
        
        if ($tipo_alerta == "exito") {
            header("Refresh:1; url=admin_logros.php");
        }
    } else {
        $mensaje = "LOS CAMPOS GENERALES SON OBLIGATORIOS.";
        $tipo_alerta = "error";
    }
}

// Consultar todas las medallas existentes
$resultado_medallas = mysqli_query($conexion, "SELECT * FROM medallas ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Gestionar Logros</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght=700&family=Poppins:wght=400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="assets/css/tablas.css">
</head>
<body class="fondo-staff-admin">

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
            <li><a href="admin_clases.php"> Cronograma</a></li>
            <li><a href="admin_logros.php" class="activo"> Crear Logros</a></li>
            <li><a href="perfil.php"> Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
        </ul>
    </nav>

    <main class="contenido-principal">
        <header class="encabezado-dashboard">
            <div class="saludo">
                <h1>FORJA DE LOGROS</h1>
                <p>CREA Y MODIFICA NUEVAS MEDALLAS PARA LA GAMIFICACIÓN DEL REGISTRO</p>
            </div>
            <div class="rango-badge">MÓDULO DE RECOMPENSAS</div>
        </header>

        <?php if (!empty($mensaje)): ?>
            <div style="margin-bottom: 20px; padding: 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; text-align: center; <?php echo $tipo_alerta == 'error' ? 'background: rgba(255,0,0,0.1); border: 1px solid #ff0000; color: #ff4d4d;' : 'background: rgba(255,215,0,0.08); border: 1px solid var(--neon-dorado); color: var(--neon-dorado);'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="layout-bloques">
            
            <!-- FORMULARIO DE CREACIÓN / EDICIÓN -->
            <div class="bloque-formulario">
                <h2><?php echo $modo_edicion ? "REFORJAR MEDALLA #".$id_editar : "NUEVA MEDALLA / INSIGNIA"; ?></h2>
                <form action="admin_logros.php" method="POST" class="formulario-interno">
                    
                    <input type="hidden" name="id_actualizar" value="<?php echo $id_editar; ?>">
                    
                    <div class="campo-grupo">
                        <label for="nombre">NOMBRE DE LA INICIATIVA</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: Rompe Récords, Inmortal..." value="<?php echo htmlspecialchars($nombre_editar); ?>" required autocomplete="off">
                    </div>
                    
                    <div class="campo-grupo">
                        <label for="descripcion">DESCRIPCIÓN DEL RETO</label>
                        <textarea id="descripcion" name="descripcion" placeholder="Ej: Levanta 120kg en Sentadilla o recorre 5km en trotadora." rows="3" required><?php echo htmlspecialchars($descripcion_editar); ?></textarea>
                    </div>

                    <div class="campo-grupo">
                        <label for="tipo_requisito">TIPO DE REQUISITO SAGRADO</label>
                        <select id="tipo_requisito" name="tipo_requisito" onchange="alternarCamposLogro()" style="width:100%; background:rgba(0,0,0,0.6); border:1px solid #333; padding:10px; color:#fff;" required>
                            <option value="fuerza" <?php echo ($tipo_requisito_editar == 'fuerza') ? 'selected' : ''; ?>>Fuerza Absoluta (PR de Fuerza)</option>
                            <option value="cardio" <?php echo ($tipo_requisito_editar == 'cardio' || $tipo_requisito_editar == '') ? 'selected' : ''; ?>>Resistencia Terrenal (Distancia de Cardio)</option>
                            <option value="asistencia" <?php echo ($tipo_requisito_editar == 'asistencia') ? 'selected' : ''; ?>>Constancia Inmortal (Días de Asistencia)</option>
                        </select>
                    </div>

                    <!-- SECCIÓN: FUERZA -->
                    <div id="wrapper-fuerza" style="display:none; border-left: 2px solid var(--neon-dorado); padding-left: 10px; margin-bottom: 15px;">
                        <div class="campo-grupo" style="margin-bottom: 10px;">
                            <label>EJERCICIO DE FUERZA</label>
                            <select name="ejercicio_fuerza" style="width:100%; background:#111; color:#fff; padding:8px; border:1px solid #333;">
                                <option value="Sentadilla Libre" <?php echo ($ejercicio_editar == 'Sentadilla Libre') ? 'selected' : ''; ?>>Sentadilla Libre</option>
                                <option value="Press de Banca" <?php echo ($ejercicio_editar == 'Press de Banca') ? 'selected' : ''; ?>>Press de Banca</option>
                                <option value="Peso Muerto" <?php echo ($ejercicio_editar == 'Peso Muerto') ? 'selected' : ''; ?>>Peso Muerto</option>
                                <option value="Press Militar" <?php echo ($ejercicio_editar == 'Press Militar') ? 'selected' : ''; ?>>Press Militar</option>
                            </select>
                        </div>
                        <div class="campo-grupo" style="margin-bottom: 10px;">
                            <label>PESO OBJETIVO (KG)</label>
                            <input type="number" name="peso_objetivo" placeholder="Ej: 100" min="0" step="0.5" value="<?php echo $peso_objetivo_editar; ?>" style="width:100%; box-sizing:border-box;">
                        </div>
                        <div class="campo-grupo">
                            <label>REPETICIONES MÍNIMAS</label>
                            <input type="number" name="reps_objetivo" placeholder="Ej: 1" min="0" value="<?php echo $reps_objetivo_editar; ?>" style="width:100%; box-sizing:border-box;">
                        </div>
                    </div>

                    <!-- SECCIÓN: CARDIO -->
                    <div id="wrapper-cardio" style="display:none; border-left: 2px solid #ff4d4d; padding-left: 10px; margin-bottom: 15px;">
                        <div class="campo-grupo" style="margin-bottom: 10px;">
                            <label>DISCIPLINA AERÓBICA</label>
                            <select name="ejercicio_cardio" style="width:100%; background:#111; color:#fff; padding:8px; border:1px solid #333;">
                                <option value="Trote / Carrera" <?php echo ($ejercicio_editar == 'Trote / Carrera') ? 'selected' : ''; ?>>Trote / Carrera</option>
                                <option value="Ciclismo" <?php echo ($ejercicio_editar == 'Ciclismo') ? 'selected' : ''; ?>>Ciclismo</option>
                                <option value="Natación" <?php echo ($ejercicio_editar == 'Natación') ? 'selected' : ''; ?>>Natación</option>
                                <option value="Remo" <?php echo ($ejercicio_editar == 'Remo') ? 'selected' : ''; ?>>Remo</option>
                            </select>
                        </div>
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <div class="campo-grupo" style="flex: 1;">
                                <label>KILÓMETROS (KM)</label>
                                <input type="number" name="km_objetivo" placeholder="0" min="0" value="<?php echo $km_objetivo_editar; ?>" style="width:100%; box-sizing:border-box;">
                            </div>
                            <div class="campo-grupo" style="flex: 1;">
                                <label>METROS (M)</label>
                                <input type="number" name="metros_objetivo" placeholder="Ej: 500" min="0" max="999" value="<?php echo $metros_objetivo_editar; ?>" style="width:100%; box-sizing:border-box;">
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: ASISTENCIA -->
                    <div id="wrapper-asistencia" style="display:none; border-left: 2px solid #33ccff; padding-left: 10px; margin-bottom: 15px;">
                        <div class="campo-grupo">
                            <label>CANTIDAD DE DÍAS REQUERIDOS</label>
                            <input type="number" name="dias_objetivo" placeholder="Ej: 15" min="0" value="<?php echo $dias_objetivo_editar; ?>" style="width:100%; box-sizing:border-box;">
                        </div>
                    </div>

                    <button type="submit" name="guardar_logro" class="boton-formulario-neon" style="margin-top:10px;">
                        <?php echo $modo_edicion ? "GUARDAR CAMBIOS" : "FORJAR MEDALLA"; ?>
                    </button>
                    
                    <?php if($modo_edicion): ?>
                        <a href="admin_logros.php" style="display:block; text-align:center; color:#aaa; margin-top:10px; font-family:'Poppins'; font-size:0.85rem; text-decoration:none;">Cancelar Edición</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- TABLA DE MEDALLAS ACTIVAS (CON NUMERACIÓN CONSECUTIVA CORREGIDA) -->
            <div class="bloque-tabla">
                <h2>MEDALLAS ACTIVAS EN EL SISTEMA</h2>
                <div class="tabla-contenedor">
                    <table class="tabla-gotica">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>LOGRO / MEDALLA</th>
                                <th>REQUISITO DE OBTENCIÓN MÁTECO</th>
                                <th style="text-align: right;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_filas = mysqli_num_rows($resultado_medallas);
                            if ($total_filas > 0): 
                                // El contador inicia con el total y va reduciendo por fila (Descendente)
                                $contador_visual = $total_filas; 
                                while ($logro = mysqli_fetch_assoc($resultado_medallas)): 
                            ?>
                                    <tr>
                                        <td><code>#<?php echo $contador_visual; ?></code></td>
                                        <td class="resaltado-dorado">
                                            <strong><?php echo strtoupper($logro['nombre']); ?></strong>
                                            <br><small style="color:#aaa; font-style:italic; font-family:'Poppins';"><?php echo $logro['descripcion']; ?></small>
                                        </td>
                                        <td style="font-family:'Poppins'; font-size:0.85rem;">
                                            <?php 
                                            if ($logro['tipo_requisito'] == 'fuerza') {
                                                echo " Fuerza: <span style='color:var(--neon-dorado);'>". $logro['ejercicio'] . "</span> - " . $logro['peso_objetivo'] . " kg x " . $logro['reps_objetivo'] . " Reps";
                                            } elseif ($logro['tipo_requisito'] == 'cardio') {
                                                $texto_distancia = "";
                                                if($logro['km_objetivo'] > 0){
                                                    $texto_distancia .= $logro['km_objetivo'] . " km ";
                                                }
                                                if($logro['metros_objetivo'] > 0){
                                                    $texto_distancia .= $logro['metros_objetivo'] . " m";
                                                }
                                                if($logro['km_objetivo'] == 0 && $logro['metros_objetivo'] == 0){
                                                    $texto_distancia = "0 m";
                                                }
                                                echo " Cardio: <span style='color:#ff4d4d;'>". $logro['ejercicio'] . "</span> - Recorrer " . $texto_distancia;
                                            } elseif ($logro['tipo_requisito'] == 'asistencia') {
                                                echo " Asistencia: <span style='color:#33ccff;'>" . $logro['dias_objetivo'] . " días</span> totales";
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <a href="admin_logros.php?editar_id=<?php echo $logro['id']; ?>" style="color:#ffcc00; text-decoration:none; font-family:'Poppins'; font-weight:600; font-size:0.8rem; margin-right:10px;">Modificar</a>
                                            <a href="admin_logros.php?borrar_id=<?php echo $logro['id']; ?>" onclick="return confirm('¿Destruir de forma permanente esta medalla del sistema?');" style="color:#ff4d4d; text-decoration:none; font-family:'Poppins'; font-weight:600; font-size:0.8rem;">Eliminar</a>
                                        </td>
                                    </tr>
                            <?php 
                                $contador_visual--;
                                endwhile; 
                            ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #666;">No hay medallas creadas en este momento.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script>
    function alternarCamposLogro() {
        const seleccion = document.getElementById('tipo_requisito').value;
        
        const fza = document.getElementById('wrapper-fuerza');
        const crd = document.getElementById('wrapper-cardio');
        const ast = document.getElementById('wrapper-asistencia');

        fza.style.display = "none";
        crd.style.display = "none";
        ast.style.display = "none";

        deshabilitarInputs(fza);
        deshabilitarInputs(crd);
        deshabilitarInputs(ast);

        if (seleccion === 'fuerza') {
            fza.style.display = "block";
            habilitarInputs(fza);
        } else if (seleccion === 'cardio') {
            crd.style.display = "block";
            habilitarInputs(crd);
        } else if (seleccion === 'asistencia') {
            ast.style.display = "block";
            habilitarInputs(ast);
        }
    }

    function deshabilitarInputs(container) {
        const inputs = container.querySelectorAll('input, select');
        inputs.forEach(i => i.removeAttribute('required'));
    }

    function habilitarInputs(container) {
        const inputs = container.querySelectorAll('select, input[type="number"]');
        if(inputs.length > 0) {
            inputs[0].setAttribute('required', 'required');
        }
    }

    window.onload = function() {
        alternarCamposLogro();
    };
    </script>
</body>
</html>