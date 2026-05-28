<?php
session_start();
require_once 'config.php';

// Seguridad: Si no es Cliente (Rol 3), rebota al login
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header('Location: login.php');
    exit;
}

$alumno_id = $_SESSION['usuario_id'];
$mensaje = "";
$tipo_alerta = "";

// 1. PROCESAR LOG / CREACIÓN DE LA MARCA O ACTIVIDAD
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_pr'])) {
    $ejercicio = mysqli_real_escape_string($conexion, $_POST['ejercicio']);
    $peso = !empty($_POST['peso']) ? floatval($_POST['peso']) : 0;
    $reps = !empty($_POST['reps']) ? intval($_POST['reps']) : 0;
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    
    // Procesar el tiempo opcional
    $tiempo = !empty($_POST['tiempo']) ? "'" . mysqli_real_escape_string($conexion, $_POST['tiempo']) . "'" : "NULL";

    if (!empty($ejercicio)) {
        $query = "INSERT INTO marcas_prs (usuario_id, ejercicio, peso, repeticiones, tiempo, fecha) 
                  VALUES ($alumno_id, '$ejercicio', $peso, $reps, $tiempo, '$fecha')";
        
        if (mysqli_query($conexion, $query)) {
            $mensaje = "REGISTRO DECRETADO CON ÉXITO EN TU HISTORIAL.";
            $tipo_alerta = "exito";
        } else {
            $mensaje = "ERROR AL VINCULAR TU RECORD EN LA BASE DE DATOS SAGRADA.";
            $tipo_alerta = "error";
        }
    }
}

// 2. PROCESAR ELIMINACIÓN DE UNA MARCA
if (isset($_GET['borrar_pr_id'])) {
    $pr_id = intval($_GET['borrar_pr_id']);
    $query = "DELETE FROM marcas_prs WHERE id = $pr_id AND usuario_id = $alumno_id";
    if (mysqli_query($conexion, $query)) {
        $mensaje = "REGISTRO REMOVIDO DE TU HISTORIAL DE MARCAS.";
        $tipo_alerta = "error";
    }
}

// 3. CONSULTAR MARCAS Y ACTIVIDADES ACTUALES DEL USUARIO
$mis_marcas = mysqli_query($conexion, "SELECT * FROM marcas_prs WHERE usuario_id = $alumno_id ORDER BY fecha DESC, id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Mis Marcas Sagradas</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght=700&family=Poppins:wght=400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css?v=1.5"> 
</head>
<body style="background-image: linear-gradient(rgba(0, 0, 0, 0.85), rgba(15, 5, 5, 0.92)), url('assets/img/fondos/alas-rojas.jpg'); background-size: cover; background-position: center center; background-attachment: fixed; background-repeat: no-repeat; color: #ffffff;">

    <div class="contenedor-dashboard">
        
        <!-- SIDEBAR DE NAVEGACIÓN -->
        <nav class="sidebar-gotica">
            <div class="brand-zona">
                <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
                <h2>DIVINITY ATLETA</h2>
            </div>
            <ul class="menu-enlaces">
                <li><a href="cliente_dashboard.php"> Mi Altar</a></li>
                <li><a href="cliente_membresias.php"> Membresias</a></li>
                <li><a href="cliente_prs.php" class="activo"> Mis Marcas (PRs)</a></li>
                <li><a href="cliente_clases.php"> Clases</a></li>
                <li><a href="cliente_logros.php"> Logros</a></li>
                <li><a href="perfil.php"> Mi Perfil</a></li>
                <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
            </ul>
        </nav>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="contenido-principal">
            
            <header class="encabezado-dashboard">
                <div class="saludo">
                    <h1>PIZARRA DE RENDIMIENTO Y MARCAS</h1>
                    <p>REGISTRA TU MÁXIMO PODER EN FUERZA Y RESISTENCIA CARDIOVASCULAR</p>
                </div>
                <div class="rango-badge" style="border-color: var(--neon-rojo); color: var(--neon-rojo); background: rgba(255,0,0,0.05); text-shadow: 0 0 5px rgba(255,0,0,0.3);">ATLETA</div>
            </header>

            <!-- Alertas del sistema -->
            <?php if (!empty($mensaje)): ?>
                <div style="margin-bottom: 20px; padding: 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; text-align: center; <?php echo $tipo_alerta == 'error' ? 'background: rgba(255,0,0,0.1); border: 1px solid #ff0000; color: #ff4d4d;' : 'background: rgba(255,215,0,0.08); border: 1px solid var(--neon-dorado); color: var(--neon-dorado);'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <!-- BLOQUE DE ESTRUCTURA EN DOS COLUMNAS -->
            <div class="pizarra-contenedor">
                
                <!-- COLUMNA IZQUIERDA: CALCULADORA IMC Y FORMULARIO -->
                <div class="columna-formulario">
                    
                    <!-- Botón disparador del Modal IMC -->
                    <button type="button" id="btnIMC" class="btn-imc">CALCULAR IMC</button>

                    <!-- Tarjeta contenedora del Formulario Único Eficiente -->
                    <div class="bloque-tabla" style="width: 100%; padding: 20px; box-sizing: border-box;">
                        <h2 style="font-family: 'Cinzel', serif; font-size: 1.1rem; color: #fff; margin-bottom: 20px; text-align: center; border-bottom: 1px solid var(--borde-sutil); padding-bottom: 10px;">
                            DECRETAR ACTIVIDAD / PR
                        </h2>
                        
                        <form action="cliente_prs.php" method="POST" style="font-family: 'Poppins', sans-serif;">
                            <input type="hidden" name="registrar_pr" value="1">
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; color: var(--neon-dorado); font-size: 0.75rem; font-weight: 600; margin-bottom: 5px; letter-spacing: 1px;">EJERCICIO O DISCIPLINA</label>
                                <select name="ejercicio" style="width: 100%; background: rgba(0,0,0,0.6); border: 1px solid var(--borde-sutil); padding: 10px; color: #fff; border-radius: 4px;" required>
                                    <option value="" style="background:#111;">Escoge una opción...</option>
                                    <optgroup label="Fuerza Absoluta (PR)" style="background:#111; color:var(--neon-dorado);">
                                        <option value="Sentadilla Libre" style="background:#111; color:#fff;">Sentadilla Libre</option>
                                        <option value="Press de Banca" style="background:#111; color:#fff;">Press de Banca</option>
                                        <option value="Peso Muerto" style="background:#111; color:#fff;">Peso Muerto</option>
                                        <option value="Press Militar" style="background:#111; color:#fff;">Press Militar</option>
                                    </optgroup>
                                    <optgroup label="Resistencia Terrenal (Cardio)" style="background:#111; color:#ff4d4d;">
                                        <option value="Trote / Carrera" style="background:#111; color:#fff;">Trote / Carrera</option>
                                        <option value="Ciclismo" style="background:#111; color:#fff;">Ciclismo</option>
                                        <option value="Natación" style="background:#111; color:#fff;">Natación</option>
                                        <option value="Salto de Cuerda" style="background:#111; color:#fff;">Salto de Cuerda</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label style="display: block; color: var(--neon-dorado); font-size: 0.75rem; font-weight: 600; margin-bottom: 5px; letter-spacing: 1px;">CARGA / PESO (KG) <span style="color:#888; font-size:0.65rem;">(Opcional)</span></label>
                                <input type="number" name="peso" placeholder="Ej: 120 (Dejar vacío en cardio)" min="0" step="0.5" style="width: 100%; background: rgba(0,0,0,0.6); border: 1px solid var(--borde-sutil); padding: 10px; color: #fff; box-sizing: border-box; border-radius: 4px;">
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label style="display: block; color: var(--neon-dorado); font-size: 0.75rem; font-weight: 600; margin-bottom: 5px; letter-spacing: 1px;">REPETICIONES REALIZADAS <span style="color:#888; font-size:0.65rem;">(Opcional)</span></label>
                                <input type="number" name="reps" placeholder="Ej: 1" min="0" style="width: 100%; background: rgba(0,0,0,0.6); border: 1px solid var(--borde-sutil); padding: 10px; color: #fff; box-sizing: border-box; border-radius: 4px;">
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label style="display: block; color: var(--neon-dorado); font-size: 0.75rem; font-weight: 600; margin-bottom: 5px; letter-spacing: 1px;">TIEMPO EN PUBLICAR <span style="color:#888; font-size:0.65rem;">(Opcional - Ej: Trote)</span></label>
                                <input type="time" name="tiempo" step="1" style="width: 100%; background: rgba(0,0,0,0.6); border: 1px solid var(--borde-sutil); padding: 10px; color: #fff; box-sizing: border-box; border-radius: 4px;">
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label style="display: block; color: var(--neon-dorado); font-size: 0.75rem; font-weight: 600; margin-bottom: 5px; letter-spacing: 1px;">FECHA DE EJECUCIÓN</label>
                                <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" style="width: 100%; background: rgba(0,0,0,0.6); border: 1px solid var(--borde-sutil); padding: 10px; color: #fff; box-sizing: border-box; border-radius: 4px;" required>
                            </div>

                            <button type="submit" class="btn-registrar">CONSAGRAR REGISTRO</button>
                        </form>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: CRONOGRAMA DE RENDIMIENTO -->
                <div class="columna-tabla">
                    <div class="bloque-tabla" style="width: 100%;">
                        <h2 style="font-family: 'Cinzel', serif; font-size: 1.1rem; color: #fff; margin-bottom: 20px; padding-left: 5px;">
                            CRONOGRAMA ACTUAL DEL ATLETA
                        </h2>
                        
                        <table class="tabla-hardcore">
                            <thead>
                                <tr>
                                    <th>EJERCICIO</th>
                                    <th>PESO / CARGA</th>
                                    <th>REPETICIONES</th>
                                    <th>DURACIÓN / TIEMPO</th>
                                    <th>FECHA</th>
                                    <th style="text-align: right;">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($mis_marcas) == 0): ?>
                                    <tr>
                                        <td colspan="6" class="texto-vacio">No has decretado marcas ni trotes en tu cronograma todavía.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($row = mysqli_fetch_assoc($mis_marcas)): ?>
                                        <tr>
                                            <td style="color:#fff; font-weight:600;"><?php echo htmlspecialchars($row['ejercicio']); ?></td>
                                            
                                            <!-- Validar peso vacío -->
                                            <td style="color:var(--neon-dorado); font-weight:600;">
                                                <?php echo ($row['peso'] > 0) ? $row['peso'] . " kg" : "--"; ?>
                                            </td>
                                            
                                            <!-- Validar reps vacías -->
                                            <td style="color:#b3b3b3;">
                                                <?php echo ($row['repeticiones'] > 0) ? $row['repeticiones'] . " Rep(s)" : "--"; ?>
                                            </td>

                                            <!-- Mostrar tiempo si existe -->
                                            <td style="color:#33ccff; font-weight:600;">
                                                <?php echo (!empty($row['tiempo']) && $row['tiempo'] != '00:00:00') ? htmlspecialchars($row['tiempo']) : "--"; ?>
                                            </td>

                                            <td><?php echo date("d/m/Y", strtotime($row['fecha'])); ?></td>
                                            <td style="text-align: right;">
                                                <a href="cliente_prs.php?borrar_pr_id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('¿Eliminar de forma permanente este registro de tu cronograma?');">Borrar</a>
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

    <!-- ==========================================================================
         MODAL INTERACTIVO DE CALCULADORA IMC
         ========================================================================== -->
    <div id="modalIMC" class="modal-overlay-imc">
        <div class="modal-caja-imc">
            <span id="cerrarModalIMC" class="cerrar-modal-imc">&times;</span>
            <h2 style="font-family: 'Cinzel', serif; font-size: 1.4rem; letter-spacing: 1px; margin-bottom: 8px;">CALCULADORA IMC</h2>
            <p class="subtitulo-modal-imc">ANALIZA EL ESTADO DE TU TEMPLO CORPORAL</p>
            
            <div style="text-align: left; font-family: 'Poppins', sans-serif; margin-bottom: 15px;">
                <label style="display:block; font-size:0.75rem; color:var(--neon-dorado); font-weight:600; margin-bottom:5px;">MASA CORPORAL (KG)</label>
                <input type="number" id="imc-peso" placeholder="Ej: 80" step="0.1" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid var(--borde-sutil); padding:10px; color:#fff; box-sizing:border-box; border-radius:4px;">
            </div>
            
            <div style="text-align: left; font-family: 'Poppins', sans-serif; margin-bottom: 20px;">
                <label style="display:block; font-size:0.75rem; color:var(--neon-dorado); font-weight:600; margin-bottom:5px;">ESTATURA (METROS)</label>
                <input type="number" id="imc-altura" placeholder="Ej: 1.78" step="0.01" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid var(--borde-sutil); padding:10px; color:#fff; box-sizing:border-box; border-radius:4px;">
            </div>
            
            <button type="button" onclick="calcularIMC()" class="btn-calcular-modal">EJECUTAR ANÁLISIS</button>
            
            <div id="resultado-imc" class="resultado-imc-caja" style="display:none;">
                <p>ÍNDICE OBTENIDO: <span id="valor-imc" style="color:var(--neon-dorado); font-weight:bold;">0.0</span></p>
                <p>DIAGNÓSTICO: <span id="estado-imc">Condición</span></p>
            </div>
        </div>
    </div>

    <!-- CONTROLADORES JAVASCRIPT -->
    <script src="assets/js/imc_prs.js"></script>
</body>
</html>