<?php
// 1. INICIALIZACIÓN Y CONFIGURACIÓN
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CONTROL DE ACCESO
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header('Location: login.php');
    exit;
}

// Forzamos un entero limpio basado EXACTAMENTE en la sesión activa
$usuario_id = intval($_SESSION['usuario_id']);
$fecha_actual = new DateTime();

// 2. VERIFICAR TIEMPO DE REGISTRO
$query_usuario = "SELECT fecha_registro FROM usuarios WHERE id = $usuario_id";
$res_usuario = mysqli_query($conexion, $query_usuario);
$datos_user = mysqli_fetch_assoc($res_usuario);

$dias_registrado = 0;
if ($datos_user && isset($datos_user['fecha_registro'])) {
    $fecha_registro = new DateTime($datos_user['fecha_registro']);
    $diferencia = $fecha_registro->diff($fecha_actual);
    $dias_registrado = $diferencia->days;
}

// 3. COMPROBAR SI EL USUARIO TIENE UNA MEMBRESÍA ACTIVA (EXTRACCIÓN LIMPIA POR FECHA VIGENTE)
$query_verificar_compra = "SELECT * FROM usuario_membresias WHERE usuario_id = $usuario_id ORDER BY id DESC LIMIT 1";
$res_verificar = mysqli_query($conexion, $query_verificar_compra);

$tiene_membresia = false;
$membresia_actual = null;

if ($res_verificar && mysqli_num_rows($res_verificar) > 0) {
    $datos_relacion = mysqli_fetch_assoc($res_verificar);
    $m_id = intval($datos_relacion['membresia_id']);
    
    // Verificamos si la fecha de vencimiento es posterior a la actual
    $fecha_vence_check = new DateTime($datos_relacion['fecha_vencimiento']);
    if ($fecha_vence_check > $fecha_actual) {
        $tiene_membresia = true; 
    }
    
    // Buscamos los datos estéticos en el catálogo
    $query_catalogo = "SELECT nombre, precio, duracion_dias FROM membresias WHERE id = $m_id LIMIT 1";
    $res_catalogo = mysqli_query($conexion, $query_catalogo);
    
    if ($res_catalogo && mysqli_num_rows($res_catalogo) > 0) {
        $datos_cat = mysqli_fetch_assoc($res_catalogo);
        $membresia_actual = array_merge($datos_relacion, $datos_cat);
    } else {
        $membresia_actual = $datos_relacion;
        $membresia_actual['nombre'] = "Plan Dios del Olimpo";
        $membresia_actual['precio'] = $datos_relacion['monto_pagado'];
        $membresia_actual['duracion_dias'] = 30;
    }
}

// CONTROL DE BLOQUEO CRUCIAL
$bloqueado = false;
if (!$tiene_membresia && $dias_registrado >= 1) {
    $bloqueado = true;
}

// 4. PROCESAR LA COMPRA (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comprar_plan_id'])) {
    $nueva_membresia_id = intval($_POST['comprar_plan_id']);
    
    $q_plan = "SELECT * FROM membresias WHERE id = $nueva_membresia_id";
    $r_plan = mysqli_query($conexion, $q_plan);
    $nuevo_plan = mysqli_fetch_assoc($r_plan);
    
    $precio_nuevo_plan = $nuevo_plan ? floatval($nuevo_plan['precio']) : 50000.00;
    $duracion_nuevos_dias = $nuevo_plan ? intval($nuevo_plan['duracion_dias']) : 30;
    
    $monto_a_pagar = $precio_nuevo_plan; 

    if ($membresia_actual !== null) {
        $fecha_vencimiento_actual = new DateTime($membresia_actual['fecha_vencimiento']);
        if ($fecha_vencimiento_actual > $fecha_actual) {
            $intervalo_restante = $fecha_actual->diff($fecha_vencimiento_actual);
            $dias_restantes = $intervalo_restante->days;
            
            $duracion_anterior = intval($membresia_actual['duracion_dias']) > 0 ? intval($membresia_actual['duracion_dias']) : 30;
            $valor_por_dia_anterior = floatval($membresia_actual['monto_pagado']) / $duracion_anterior;
            $saldo_a_favor = $dias_restantes * $valor_por_dia_anterior;
            
            $monto_a_pagar = $precio_nuevo_plan - $saldo_a_favor;
            if ($monto_a_pagar < 0) { $monto_a_pagar = 0; }
        }
    }
    
    // CORRECCIÓN CRUCIAL: Desactivamos las membresías previas de manera controlada pasándolas a 'vencido'
    $query_desactivar = "UPDATE usuario_membresias SET estado = 'vencido' WHERE usuario_id = $usuario_id";
    mysqli_query($conexion, $query_desactivar);

    $fecha_vence_nueva = date('Y-m-d H:i:s', strtotime("+$duracion_nuevos_dias days"));

    // Guardar el nuevo registro con estado 'activo' explícito
    $query_comprar = "INSERT INTO usuario_membresias (usuario_id, membresia_id, monto_pagado, fecha_vencimiento, estado) 
                      VALUES ($usuario_id, $nueva_membresia_id, $monto_a_pagar, '$fecha_vence_nueva', 'activo')";
    
    if (mysqli_query($conexion, $query_comprar)) {
        // Redirección directa hacia el altar para comprobar los cambios de forma instantánea
        header('Location: cliente_dashboard.php');
        exit;
    } else {
        die("Error crítico: " . mysqli_error($conexion));
    }
}

// 5. OBTENER CATÁLOGO PARA LAS TARJETAS VISUALES
$query_todos = "SELECT * FROM membresias ORDER BY precio ASC";
$resultado_planes = mysqli_query($conexion, $query_todos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymdivinity - Mis Membresías</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght=700&family=Poppins:wght=400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <style>
        html { scroll-behavior: smooth; }
        .boton-pacto-seleccionar:hover {
            background: rgba(204,153,51,0.1) !important;
            box-shadow: 0 0 10px rgba(204,153,51,0.3);
        }
    </style>
</head>
<body class="fondo-dashboard-atleta" style="background-color: #050505; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; display: flex; min-height: 100vh; overflow-x: hidden;">

    <nav class="sidebar-gotica" style="width: 250px; background-color: #0a0a0a; border-right: 1px solid #111; min-height: 100vh; padding: 20px; box-sizing: border-box; position: fixed; left: 0; top: 0; z-index: 1001;">
        <div class="brand-zona" style="text-align: center; margin-bottom: 40px;">
            <img src="assets/img/logos/logo.jpg" alt="Logo" class="logo-nav" style="width: 60px; height: auto; border-radius: 50%;">
            <h2 style="font-family: 'Cinzel', serif; color: #fff; font-size: 1.1rem; letter-spacing: 1px; margin-top: 10px;">DIVINITY ATLETA</h2>
        </div>
        <ul class="menu-enlaces" style="list-style: none; padding: 0; margin: 0;">
            <li style="margin-bottom: 20px;"><a href="cliente_dashboard.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Mi Altar</a></li>
            <li style="margin-bottom: 20px;"><a href="cliente_membresias.php" class="activo" style="color: #fff; text-decoration: none; font-size: 0.95rem; font-weight: 600; border: 1px solid #cc9933; padding: 8px 12px; display: block; border-radius: 4px; background: rgba(204,153,51,0.05);">Membresías</a></li>
            <li style="margin-bottom: 20px;"><a href="cliente_prs.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Mis Marcas (PRs)</a></li>
            <li style="margin-bottom: 20px;"><a href="cliente_clases.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Clases</a></li>
            <li style="margin-bottom: 20px;"><a href="cliente_logros.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Logros</a></li>
            <li style="margin-bottom: 40px;"><a href="perfil.php" style="color: #aaa; text-decoration: none; font-size: 0.95rem;">Mi Perfil</a></li>
            <li class="separador-logout" style="margin-top: 50px;"><a href="procesar/auth/logout.php" class="logout-link" style="color: #ff4d4d; text-decoration: none; font-size: 0.95rem; border: 1px solid #ff4d4d; padding: 8px 12px; display: block; border-radius: 4px; text-align: center;">Cerrar Templo</a></li>
        </ul>
    </nav>

    <main style="flex: 1; margin-left: 250px; padding: 40px; box-sizing: border-box; position: relative; min-height: 100vh;">
        
        <?php if ($bloqueado): ?>
            <div id="pantalla-bloqueo" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(5,5,5,0.98); z-index: 999; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; padding-top: 15vh; text-align: center; box-sizing: border-box;">
                <h2 style="font-family: 'Cinzel', serif; color: #ff4d4d; font-size: 2.2rem; margin-bottom: 15px; letter-spacing: 1px; text-shadow: 0 0 15px rgba(255,77,77,0.3);">TU TIEMPO DE PRUEBA HA EXPIRADO</h2>
                <p style="color: #aaa; max-width: 550px; font-size: 0.95rem; line-height: 1.7; margin-bottom: 35px; font-family: 'Poppins'; padding: 0 20px;">Tu periodo de gracia de 24 horas ha terminado. Para desbloquear el acceso al templo gótico y continuar tus crónicas, debes infundir poder seleccionando uno de los pactos sagrados de abajo.</p>
                <button onclick="revelarPactos()" style="background: #cc9933; border: none; color: #000; font-family: 'Cinzel', serif; font-weight: bold; padding: 14px 35px; border-radius: 4px; font-size: 0.9rem; letter-spacing: 1px; cursor: pointer; box-shadow: 0 4px 15px rgba(204,153,51,0.3);">VER PACTOS DISPONIBLES</button>
            </div>
        <?php endif; ?>

        <header style="margin-bottom: 40px;">
            <h1 style="font-family: 'Cinzel', serif; font-size: 2rem; margin: 0; letter-spacing: 1px;">GESTIÓN DE MEMBRESÍAS</h1>
            <p style="color: #777; font-size: 0.85rem; margin: 5px 0 0 0;">SINTONIZA TU ACCESO CON LOS PLANES DEL ALTAR</p>
        </header>

        <div style="margin-bottom: 50px;">
            <h2 style="font-family: 'Cinzel', serif; font-size: 1.1rem; color: #cc9933; letter-spacing: 1px; margin-bottom: 20px; text-transform: uppercase;">Tu Estado Actual</h2>
            <?php if ($tiene_membresia && $membresia_actual !== null): ?>
                <div style="background: #0a0a0a; border: 1px solid #cc9933; border-radius: 6px; padding: 25px; max-width: 450px; position: relative; box-shadow: 0 0 15px rgba(204,153,51,0.15);">
                    <span style="position: absolute; top: 20px; right: 20px; background: #00ff66; color: #000; font-size: 0.7rem; font-weight: bold; padding: 4px 10px; border-radius: 4px; text-transform: uppercase;">Activo</span>
                    <h3 style="font-family: 'Cinzel', serif; margin: 0; font-size: 1.4rem; color: #fff;"><?php echo strtoupper($membresia_actual['nombre']); ?></h3>
                    <p style="color: #ffd700; font-size: 1.2rem; font-weight: bold; margin: 10px 0;">$<?php echo number_format($membresia_actual['precio'], 0, ',', '.'); ?> COP</p>
                    <p style="color: #666; font-size: 0.8rem; margin: 0;">Expira el: <span style="color: #aaa;"><?php echo date('d-m-Y', strtotime($membresia_actual['fecha_vencimiento'])); ?></span></p>
                </div>
            <?php else: ?>
                <div style="background: #0a0a0a; border: 1px solid #1c1c1c; border-radius: 6px; padding: 25px; max-width: 450px; color: #ff4d4d; font-size: 0.9rem; font-weight: 600;">
                    ⚠️ No portas ningún pacto activo en este momento. Selecciona uno para activar tu cuenta.
                </div>
            <?php endif; ?>
        </div>

        <div id="planes-ancla" style="margin-top: 20px;">
            <h2 style="font-family: 'Cinzel', serif; font-size: 1.1rem; color: #fff; letter-spacing: 1px; margin-bottom: 20px; text-transform: uppercase;">Pactos Disponibles en el Altar</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-bottom: 40px;">
                <?php 
                if ($resultado_planes):
                    while ($plan = mysqli_fetch_assoc($resultado_planes)): 
                        if ($tiene_membresia && isset($membresia_actual['membresia_id']) && $plan['id'] == $membresia_actual['membresia_id']) continue;
                        
                        $mensaje_cambio = "Costo total del pacto sagrado.";
                        $precio_final_estimado = floatval($plan['precio']);

                        if ($membresia_actual !== null && isset($membresia_actual['fecha_vencimiento'])) {
                            $fecha_vence_act = new DateTime($membresia_actual['fecha_vencimiento']);
                            if ($fecha_vence_act > $fecha_actual) {
                                $diff_dias = $fecha_actual->diff($fecha_vence_act)->days;
                                $duracion_ant = intval($membresia_actual['duracion_dias']) > 0 ? intval($membresia_actual['duracion_dias']) : 30;
                                $val_dia = floatval($membresia_actual['monto_pagado']) / $duracion_ant;
                                $credito = $diff_dias * $val_dia;
                                
                                $precio_final_estimado = floatval($plan['precio']) - $credito;
                                if ($precio_final_estimado < 0) { $precio_final_estimado = 0; }
                                
                                $mensaje_cambio = (floatval($plan['precio']) > floatval($membresia_actual['precio'])) ? "Upgrade: Diferencia proporcional calculada." : "Downgrade: Ajuste aplicado por saldo a favor.";
                            }
                        }
                    ?>
                        <div style="background: #0a0a0a; border: 1px solid #1c1c1c; border-radius: 6px; padding: 25px; display: flex; flex-direction: column; justify-content: space-between; position: relative; z-index: 1;">
                            <div>
                                <h3 style="font-family: 'Cinzel', serif; margin: 0 0 10px 0; font-size: 1.2rem; color: #fff;"><?php echo strtoupper($plan['nombre']); ?></h3>
                                <p style="color: #ffd700; font-size: 1.4rem; font-weight: bold; margin: 0 0 5px 0;">$<?php echo number_format($plan['precio'], 0, ',', '.'); ?> <span style="font-size: 0.8rem; color: #444;">/ mes</span></p>
                                <p style="color: #666; font-size: 0.8rem; margin: 0 0 15px 0;">Ciclo: <?php echo $plan['duracion_dias']; ?> días</p>
                                <p style="color: #aaa; font-size: 0.85rem; line-height: 1.5; margin: 0 0 20px 0; min-height: 45px;">Acceso ilimitado a las instalaciones del templo gótico.</p>
                            </div>

                            <button class="boton-pacto-seleccionar" onclick="abrirModalPacto(<?php echo $plan['id']; ?>, '<?php echo $plan['nombre']; ?>', '<?php echo number_format($plan['precio'], 0, ',', '.'); ?>', '<?php echo number_format($precio_final_estimado, 0, ',', '.'); ?>', '<?php echo $mensaje_cambio; ?>')" style="width: 100%; background: none; border: 1px solid #cc9933; color: #cc9933; font-family: 'Poppins'; font-weight: 600; padding: 11px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; text-transform: uppercase; transition: all 0.3s;">
                                Seleccionar
                            </button>
                        </div>
                    <?php 
                    endwhile; 
                endif;
                ?>
            </div>
        </div>
    </main>

    <div id="modalConfirmacion" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box;">
        <div style="background: #0a0a0a; border: 1px solid #cc9933; width: 100%; max-width: 440px; padding: 30px; border-radius: 6px; text-align: center; box-shadow: 0 10px 35px rgba(0,0,0,0.9);">
            <h3 style="font-family: 'Cinzel', serif; color: #fff; font-size: 1.3rem; margin-top: 0; margin-bottom: 15px;">CONFIRMACIÓN DE OFRENDA</h3>
            <p style="color: #aaa; font-size: 0.9rem; margin-bottom: 10px; line-height: 1.6;">
                Vas a vincularte al <span id="modalNombrePlan" style="color: #ffd700; font-weight: bold;"></span> (Valor base: $<span id="modalPrecioBase"></span> COP).
            </p>
            <div style="background: rgba(0, 255, 102, 0.04); border: 1px dashed #00ff66; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
                <span style="color: #aaa; font-size: 0.8rem; display: block; text-transform: uppercase;">Total Neto a Pagar Hoy:</span>
                <span style="color: #00ff66; font-size: 1.6rem; font-weight: bold; font-family: 'Cinzel';">$<span id="modalPrecioFinal"></span> COP</span>
            </div>
            <p id="modalMensajeAccion" style="color: #cc9933; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 25px; letter-spacing: 0.5px;"></p>
            <form action="cliente_membresias.php" method="POST" style="display: flex; gap: 15px; justify-content: center;">
                <input type="hidden" name="comprar_plan_id" id="inputHiddenPlanId">
                <button type="button" onclick="cerrarModalPacto()" style="flex: 1; background: #111; border: 1px solid #222; color: #888; padding: 12px; font-family: 'Poppins'; font-size: 0.85rem; border-radius: 4px; cursor: pointer; text-transform: uppercase; font-weight: 600;">Modificar</button>
                <button type="submit" style="flex: 1; background: #cc9933; border: none; color: #000; font-family: 'Cinzel', serif; font-weight: bold; padding: 12px; font-size: 0.85rem; border-radius: 4px; cursor: pointer; letter-spacing: 0.5px;">CONCLUIR PAGO</button>
            </form>
        </div>
    </div>

    <script>
        function revelarPactos() {
            const capa = document.getElementById('pantalla-bloqueo');
            if(capa) { capa.style.display = 'none'; }
            const ancla = document.getElementById('planes-ancla');
            if(ancla) { ancla.scrollIntoView({ behavior: 'smooth' }); }
        }
        function abrirModalPacto(id, nombre, precioBase, precioFinal, mensaje) {
            document.getElementById('inputHiddenPlanId').value = id;
            document.getElementById('modalNombrePlan').innerText = nombre.toUpperCase();
            document.getElementById('modalPrecioBase').innerText = precioBase;
            document.getElementById('modalPrecioFinal').innerText = precioFinal;
            document.getElementById('modalMensajeAccion').innerText = mensaje;
            document.getElementById('modalConfirmacion').style.display = 'flex';
        }
        function cerrarModalPacto() {
            document.getElementById('modalConfirmacion').style.display = 'none';
        }
    </script>
</body>
</html>