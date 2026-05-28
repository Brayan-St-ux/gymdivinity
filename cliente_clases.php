<?php
require_once 'config.php';

// Seguridad: Si no es Cliente (Rol 3), rebota al login
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header('Location: login.php');
    exit;
}

$alumno_id = $_SESSION['usuario_id'];
$mensaje = "";
$tipo_alerta = "";

// 1. PROCESAR SELECCIÓN / INSCRIPCIÓN A UNA CLASE (Adaptado a usuario_id y horario_id)
if (isset($_GET['inscribir_clase_id'])) {
    $clase_id = intval($_GET['inscribir_clase_id']);
    
    // 1. Buscamos primero si esa clase tiene cupo disponible
    $comprobar_cupo = mysqli_query($conexion, "SELECT cupo_maximo, (SELECT COUNT(*) FROM inscripciones_clases WHERE horario_id = clases.id) AS inscritos FROM clases WHERE id = $clase_id");
    $datos_clase = mysqli_fetch_assoc($comprobar_cupo);
    
    if ($datos_clase) {
        if ($datos_clase['inscritos'] >= $datos_clase['cupo_maximo']) {
            $mensaje = "EL CUPO PARA ESTA CLASE ESTÁ COMPLETAMENTE LLENO.";
            $tipo_alerta = "error";
        } else {
            
            // 2. CORRECCIÓN CRÍTICA: Aseguramos si el ID de la clase coincide directamente con un ID en la tabla 'horarios'
            // Insertamos el registro respetando la clave foránea vinculada
            $query = "INSERT INTO inscripciones_clases (usuario_id, horario_id) VALUES ($alumno_id, $clase_id)";
            
            if (mysqli_query($conexion, $query)) {
                $mensaje = "CLASE SELECCIONADA. TU ALMA HA SIDO VINCULADA AL HORARIO.";
                $tipo_alerta = "exito";
            } else {
                // Si falla por otra razón, capturamos el duplicado o el error de llave
                $mensaje = "YA TIENES ESTA CLASE SELECCIONADA O LA SESIÓN NO EXISTE EN HORARIOS.";
                $tipo_alerta = "error";
            }
        }
    }
}

// 2. PROCESAR BAJA / BORRAR UNA CLASE (Adaptado a usuario_id y horario_id)
if (isset($_GET['borrar_inscripcion_id'])) {
    $clase_id = intval($_GET['borrar_inscripcion_id']);
    $query = "DELETE FROM inscripciones_clases WHERE usuario_id = $alumno_id AND horario_id = $clase_id";
    if (mysqli_query($conexion, $query)) {
        $mensaje = "CLASE REMOVIDA DE TU AGENDA CORRECTAMENTE.";
        $tipo_alerta = "error";
    }
}

// 3. CONSULTAS PARA LAS TRES LISTAS
// Lista 1: Clases Gratuitas
$clases_gratuitas = mysqli_query($conexion, "SELECT c.*, u.nombre AS profesor_nombre FROM clases c JOIN usuarios u ON c.instructor_id = u.id WHERE c.tipo = 'gratis' ORDER BY c.hora_inicio ASC");

// Lista 2: Clases Privadas / Pago
$clases_privadas = mysqli_query($conexion, "SELECT c.*, u.nombre AS profesor_nombre FROM clases c JOIN usuarios u ON c.instructor_id = u.id WHERE c.tipo = 'pago' ORDER BY c.hora_inicio ASC");

// Lista 3: Clases ya seleccionadas (Consulta corregida según tu imagen image_22d18a.png)
$clases_seleccionadas = mysqli_query($conexion, "SELECT clases.*, usuarios.nombre AS profesor_nombre FROM inscripciones_clases JOIN clases ON inscripciones_clases.horario_id = clases.id JOIN usuarios ON clases.instructor_id = usuarios.id WHERE inscripciones_clases.usuario_id = $alumno_id ORDER BY clases.hora_inicio ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Mis Clases Sagradas</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght=700&family=Poppins:wght=400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css?v=1.3"> 
</head>
<body class="fondo-templo">

    <div class="contenedor-dashboard">
        
        <nav class="sidebar-gotica">
        <div class="brand-zona">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav">
            <h2>DIVINITY ATLETA</h2>
        </div>
        <ul class="menu-enlaces">
            <li><a href="cliente_dashboard.php"> Mi Altar</a></li>
            <li><a href="cliente_membresias.php"> Membresias</a></li>
            <li><a href="cliente_prs.php"> Mis Marcas (PRs)</a></li>
            <li><a href="cliente_clases.php" class="activo"> Clases</a></li>
            <li><a href="cliente_logros.php"> Logros</a></li>
            <li><a href="perfil.php"> Mi Perfil</a></li>
            <li class="separador-logout"><a href="procesar/auth/logout.php" class="logout-link"> Cerrar Templo</a></li>
        </ul>
        </nav>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="contenido-principal">
            
            <header class="encabezado-dashboard">
                <div class="saludo">
                    <h1>RESERVA DE CLASES</h1>
                    <p>SINTONIZA TU CUERPO CON LOS HORARIOS DE ENTRENAMIENTO</p>
                </div>
                <div class="rango-badge" style="border-color: var(--neon-rojo); color: var(--neon-rojo); background: rgba(255,0,0,0.05); text-shadow: 0 0 5px rgba(255,0,0,0.3);">ATLETA</div>
            </header>

            <!-- Alertas del sistema -->
            <?php if (!empty($mensaje)): ?>
                <div style="margin-bottom: 20px; padding: 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; text-align: center; <?php echo $tipo_alerta == 'error' ? 'background: rgba(255,0,0,0.1); border: 1px solid #ff0000; color: #ff4d4d;' : 'background: rgba(255,215,0,0.08); border: 1px solid var(--neon-dorado); color: var(--neon-dorado);'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <!-- TABLA PRINCIPAL ADAPTADA A UNA SOLA COLUMNA ANCHA -->
            <div class="bloque-tabla" style="width: 100%;">
                
                <!-- BOTONES SUPERIORES PARA INTERCAMBIAR LAS 3 LISTAS -->
                <div class="zona-intercambio-botones" style="justify-content: flex-start; gap: 10px;">
                    <button type="button" id="btn-lista-gratis" class="btn-intercambio activo">CLASES GRATUITAS</button>
                    <button type="button" id="btn-lista-privadas" class="btn-intercambio">CLASES PRIVADAS</button>
                    <button type="button" id="btn-lista-mias" class="btn-intercambio" style="border-color: rgba(255,0,0,0.2);">
                        MIS SELECCIONES
                        <?php if(mysqli_num_rows($clases_seleccionadas) > 0): ?>
                            <span class="burbuja-alerta" style="background:var(--neon-rojo);"><?php echo mysqli_num_rows($clases_seleccionadas); ?></span>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- LISTA 1: CLASES GRATUITAS -->
                <div id="contenedor-gratis" class="bloque-tabla-dinamica">
                    <table class="tabla-hardcore">
                        <thead>
                            <tr>
                                <th>HORA</th>
                                <th>CLASE</th>
                                <th>PROFESOR / INSTRUCTOR</th>
                                <th>DESCRIPCIÓN</th>
                                <th style="text-align: right;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($clases_gratuitas) == 0): ?>
                                <tr><td colspan="5" class="texto-vacio">No hay clases gratuitas programadas por ahora.</td></tr>
                            <?php else: ?>
                                <?php while($row = mysqli_fetch_assoc($clases_gratuitas)): ?>
                                    <tr>
                                        <td style="color:var(--neon-dorado); font-weight:600;"><?php echo date("g:i a", strtotime($row['hora_inicio'])); ?></td>
                                        <td style="color:#fff; font-weight:600;"><?php echo htmlspecialchars($row['nombre']); ?></td>
                                        <td style="color:#ff4d4d;"><?php echo htmlspecialchars($row['profesor_nombre']); ?></td>
                                        <td class="texto-biografia-celda"><?php echo htmlspecialchars($row['descripcion']); ?></td>
                                        <td style="text-align: right;">
                                            <button type="button" class="action-btn btn-abrir-flotante" 
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                                                    data-profe="<?php echo htmlspecialchars($row['profesor_nombre']); ?>"
                                                    data-hora="<?php echo date("g:i a", strtotime($row['hora_inicio'])); ?>"
                                                    data-duracion="<?php echo $row['duracion_horas']; ?>"
                                                    data-desc="<?php echo htmlspecialchars($row['descripcion']); ?>"
                                                    data-tipo="gratis">
                                                Seleccionar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- LISTA 2: CLASES PRIVADAS -->
                <div id="contenedor-privadas" class="bloque-tabla-dinamica oculto-inicial">
                    <table class="tabla-hardcore">
                        <thead>
                            <tr>
                                <th>HORA</th>
                                <th>CLASE PRIVADA</th>
                                <th>PROFESOR / INSTRUCTOR</th>
                                <th>DESCRIPCIÓN</th>
                                <th style="text-align: right;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($clases_privadas) == 0): ?>
                                <tr><td colspan="5" class="texto-vacio">No hay clases privadas programadas por ahora.</td></tr>
                            <?php else: ?>
                                <?php while($row = mysqli_fetch_assoc($clases_privadas)): ?>
                                    <tr>
                                        <td style="color:var(--neon-dorado); font-weight:600;"><?php echo date("g:i a", strtotime($row['hora_inicio'])); ?></td>
                                        <td style="color:#fff; font-weight:600;"><?php echo htmlspecialchars($row['nombre']); ?></td>
                                        <td style="color:#ff4d4d;"><?php echo htmlspecialchars($row['profesor_nombre']); ?></td>
                                        <td class="texto-biografia-celda"><?php echo htmlspecialchars($row['descripcion']); ?></td>
                                        <td style="text-align: right;">
                                            <button type="button" class="action-btn btn-abrir-flotante" 
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                                                    data-profe="<?php echo htmlspecialchars($row['profesor_nombre']); ?>"
                                                    data-hora="<?php echo date("g:i a", strtotime($row['hora_inicio'])); ?>"
                                                    data-duracion="<?php echo $row['duracion_horas']; ?>"
                                                    data-desc="<?php echo htmlspecialchars($row['descripcion']); ?>"
                                                    data-tipo="pago">
                                                Seleccionar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- LISTA 3: CLASES SELECCIONADAS -->
                <div id="contenedor-mias" class="bloque-tabla-dinamica oculto-inicial">
                    <table class="tabla-hardcore">
                        <thead>
                            <tr>
                                <th>HORA</th>
                                <th>CLASE INSCRITA</th>
                                <th>PROFESOR</th>
                                <th>DURACIÓN</th>
                                <th style="text-align: right;">REMOCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($clases_seleccionadas) == 0): ?>
                                <tr><td colspan="5" class="texto-vacio">Aún no has incorporado clases a tu grimorio personal.</td></tr>
                            <?php else: ?>
                                <?php while($row = mysqli_fetch_assoc($clases_seleccionadas)): ?>
                                    <tr>
                                        <td style="color:var(--neon-rojo); font-weight:600;"><?php echo date("g:i a", strtotime($row['hora_inicio'])); ?></td>
                                        <td style="color:#fff; font-weight:600;"><?php echo htmlspecialchars($row['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($row['profesor_nombre']); ?></td>
                                        <td><?php echo $row['duracion_horas']; ?> Hora(s)</td>
                                        <td style="text-align: right;">
                                            <a href="cliente_clases.php?borrar_inscripcion_id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('¿Retirar esta clase de tus asignaciones actuales?');">Borrar</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <!-- VENTANA FLOTANTE (MODAL INTERACTIVO) DE CONFIRMACIÓN -->
    <div id="modal-resumen-clase" class="modal-overlay">
        <div class="modal-caja" style="border-top-color: var(--neon-rojo);">
            <h2 class="modal-titulo" style="color: #fff; font-family:'Cinzel', serif;">DETALLES DE LA SESIÓN</h2>
            
            <div style="margin-bottom: 20px; border-bottom: 1px solid var(--borde-sutil); padding-bottom: 15px; text-align: left; font-family:'Poppins', sans-serif; font-size:0.9rem;">
                <p style="margin: 8px 0;"><strong style="color: var(--neon-dorado);">CLASE:</strong> <span id="lbl-modal-nombre"></span></p>
                <p style="margin: 8px 0;"><strong style="color: var(--neon-dorado);">INSTRUCTOR:</strong> <span id="lbl-modal-profe"></span></p>
                <p style="margin: 8px 0;"><strong style="color: var(--neon-dorado);">HORARIO:</strong> <span id="lbl-modal-hora"></span> (<span id="lbl-modal-duracion"></span> hrs)</p>
                <p style="margin: 12px 0 5px 0;"><strong style="color: var(--neon-dorado);">SINOPSIS / DESCRIPCIÓN:</strong></p>
                <p id="lbl-modal-desc" style="color:#b3b3b3; font-size:0.8rem; line-height:1.4; background: rgba(0,0,0,0.4); padding:10px; border-left:2px solid var(--neon-rojo);"></p>
            </div>

            <div class="modal-botones-zona" style="justify-content: space-between;">
                <button type="button" class="btn-modal btn-modal-cancelar" id="btn-cerrar-flotante" style="width: 48%;">CANCELAR</button>
                <a href="#" id="btn-confirmar-seleccion" class="btn-modal" style="width: 48%; text-align: center; border: 1px solid #ff4d4d; color: #fff; background: rgba(255, 77, 77, 0.1); font-weight:600; line-height:2.2;">SELECCIONAR</a>
            </div>
        </div>
    </div>

    <!-- CONTROLADOR JAVASCRIPT DE INTERFAZ -->
    <script>
        // Elementos para el intercambio de las 3 listas
        const btnGratis = document.getElementById('btn-lista-gratis');
        const btnPrivadas = document.getElementById('btn-lista-privadas');
        const btnMias = document.getElementById('btn-lista-mias');

        const conGratis = document.getElementById('contenedor-gratis');
        const conPrivadas = document.getElementById('contenedor-privadas');
        const conMias = document.getElementById('contenedor-mias');

        btnGratis.addEventListener('click', () => {
            btnGratis.className = 'btn-intercambio activo'; btnPrivadas.className = 'btn-intercambio'; btnMias.className = 'btn-intercambio';
            conGratis.classList.remove('oculto-inicial'); conPrivadas.classList.add('oculto-inicial'); conMias.classList.add('oculto-inicial');
        });

        btnPrivadas.addEventListener('click', () => {
            btnGratis.className = 'btn-intercambio'; btnPrivadas.className = 'btn-intercambio activo'; btnMias.className = 'btn-intercambio';
            conGratis.classList.add('oculto-inicial'); conPrivadas.classList.remove('oculto-inicial'); conMias.classList.add('oculto-inicial');
        });

        btnMias.addEventListener('click', () => {
            btnGratis.className = 'btn-intercambio'; btnPrivadas.className = 'btn-intercambio'; btnMias.className = 'btn-intercambio activo';
            conGratis.classList.add('oculto-inicial'); conPrivadas.classList.add('oculto-inicial'); conMias.classList.remove('oculto-inicial');
        });

        // Elementos del Modal Flotante de Selección
        const modal = document.getElementById('modal-resumen-clase');
        const btnCerrar = document.getElementById('btn-cerrar-flotante');
        const btnConfirmar = document.getElementById('btn-confirmar-seleccion');

        document.querySelectorAll('.btn-abrir-flotante').forEach(boton => {
            boton.addEventListener('click', () => {
                document.getElementById('lbl-modal-nombre').innerText = boton.getAttribute('data-nombre');
                document.getElementById('lbl-modal-profe').innerText = boton.getAttribute('data-profe');
                document.getElementById('lbl-modal-hora').innerText = boton.getAttribute('data-hora');
                document.getElementById('lbl-modal-duracion').innerText = boton.getAttribute('data-duracion');
                document.getElementById('lbl-modal-desc').innerText = boton.getAttribute('data-desc');
                
                // Configurar el enlace de confirmación con el ID dinámico
                const claseId = boton.getAttribute('data-id');
                btnConfirmar.setAttribute('href', `cliente_clases.php?inscribir_clase_id=${claseId}`);
                
                // Mostrar ventana flotante
                modal.classList.add('activo');
            });
        });

        btnCerrar.addEventListener('click', () => {
            modal.classList.remove('activo');
        });
    </script>
</body>
</html>