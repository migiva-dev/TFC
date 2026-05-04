<?php
// =====================================================
// ARCHIVO: public/reservar.php
// Descripción: Página de reserva de citas.
//              Solo accesible para usuarios logueados.
//              Permite elegir servicio, fecha y hora.
// =====================================================

// Incluimos configuración, conexión y funciones
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/funciones.php';


// -- Control de sesión --
// Si el usuario no está logueado lo mandamos al login
requiere_usuario();

$titulo_pagina = 'Reservar cita';

// -- Datos del usuario logueado --
// Los cogemos de la sesión para usarlos en la página
$usuario_id     = $_SESSION[USER_SESSION_KEY]['id'];
$usuario_nombre = $_SESSION[USER_SESSION_KEY]['nombre'];

// -- Consulta: obtenemos todos los servicios disponibles --
$servicios = $conexion->query("SELECT * FROM servicios ORDER BY precio ASC");

// -- Horas disponibles para reservar --
// De 9:00 a 19:30 cada 30 minutos
$horas_disponibles = [];
for ($h = 9; $h < 20; $h++) {
    $horas_disponibles[] = sprintf('%02d:00', $h);
    $horas_disponibles[] = sprintf('%02d:30', $h);
}

$error  = '';
$exito  = '';

// -------------------------------------------------------
// Calculamos la disponibilidad de cada día del mes
// para mostrarla en el calendario con colores
// -------------------------------------------------------

// Total de horas disponibles por día (de 9:00 a 19:30 cada 30min)
$total_horas_dia = 22; // 22 franjas horarias disponibles

// Mes y año que se está viendo en el calendario
// Por defecto el mes actual
$mes_actual  = intval($_GET['mes']  ?? date('n'));
$anio_actual = intval($_GET['anio'] ?? date('Y'));

// Nos aseguramos de que el mes esté entre 1 y 12
if ($mes_actual < 1)  { $mes_actual = 12; $anio_actual--; }
if ($mes_actual > 12) { $mes_actual = 1;  $anio_actual++; }

// Consultamos cuántas reservas hay por día en ese mes
$stmt = $conexion->prepare(
    "SELECT fecha, COUNT(*) as total
     FROM reservas
     WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
     AND estado != 'cancelada'
     GROUP BY fecha"
);
$stmt->bind_param('ii', $mes_actual, $anio_actual);
$stmt->execute();
$resultado_mes = $stmt->get_result();

// Guardamos las reservas por día en un array
// clave = día del mes, valor = número de reservas
$reservas_por_dia = [];
while ($fila = $resultado_mes->fetch_assoc()) {
    $dia = intval(date('j', strtotime($fila['fecha'])));
    $reservas_por_dia[$dia] = intval($fila['total']);
}
$stmt->close();


// -- Obtenemos las horas ocupadas para la fecha seleccionada --
// Se usa para deshabilitar horas ya reservadas en el selector
$fecha_consulta = $_POST['fecha'] ?? date('Y-m-d');
$horas_ocupadas = [];

$stmt = $conexion->prepare(
    "SELECT hora FROM reservas
     WHERE fecha = ? AND estado != 'cancelada'"
);
$stmt->bind_param('s', $fecha_consulta);
$stmt->execute();
$resultado_horas = $stmt->get_result();
while ($fila = $resultado_horas->fetch_assoc()) {
    // Guardamos solo HH:MM para comparar con el selector
    $horas_ocupadas[] = substr($fila['hora'], 0, 5);
}
$stmt->close();

// -------------------------------------------------------
// Procesamos el formulario cuando se envía (método POST)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Si solo es una consulta de horas al cambiar la fecha
    // no procesamos la reserva, solo actualizamos las horas
    if (isset($_POST['consultar_horas'])) {

        // La fecha ya fue consultada arriba, no hacemos nada más
        // El formulario se mostrará con las horas actualizadas

    } else {

        // Recogemos y limpiamos los datos del formulario
        $servicio_id = intval($_POST['servicio_id'] ?? 0);
        $fecha       = trim($_POST['fecha']         ?? '');
        $hora        = trim($_POST['hora']          ?? '');
        $notas       = trim($_POST['notas']         ?? '');

        // -- Validaciones --

        // Todos los campos obligatorios deben estar rellenos
        if (empty($servicio_id) || empty($fecha) || empty($hora)) {
            $error = 'Por favor, rellena todos los campos obligatorios.';

        // La fecha no puede ser anterior a hoy
        } elseif ($fecha < date('Y-m-d')) {
            $error = 'La fecha no puede ser anterior a hoy.';

        // No se puede reservar en domingo (0 = domingo en PHP)
        } elseif (date('w', strtotime($fecha)) == 0) {
            $error = 'Lo sentimos, los domingos estamos cerrados.';

        // Comprobamos que la hora no esté ya ocupada
        // Seguridad extra: aunque el select esté deshabilitado
        // alguien podría enviar el formulario manualmente
        } elseif (in_array($hora, $horas_ocupadas)) {
            $error = 'Esa hora ya está ocupada. Por favor elige otra.';

        } else {

            // Comprobamos que no haya ya una reserva para esa fecha y hora
            $stmt = $conexion->prepare(
                "SELECT id FROM reservas
                 WHERE fecha = ? AND hora = ?
                 AND estado != 'cancelada'"
            );
            $stmt->bind_param('ss', $fecha, $hora);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Ya hay una reserva en ese horario
                $error = 'Ese horario ya está ocupado. Por favor elige otro.';
                $stmt->close();

            } else {
                $stmt->close();

                // Insertamos la reserva en la BD con estado 'pendiente'
                $stmt = $conexion->prepare(
                    "INSERT INTO reservas (usuario_id, servicio_id, fecha, hora, notas)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('iisss', $usuario_id, $servicio_id, $fecha, $hora, $notas);

               if ($stmt->execute()) {
                
               $exito = '¡Reserva realizada con éxito! Te esperamos el ' .
                         date('d/m/Y', strtotime($fecha)) . ' a las ' . $hora . '.';
                $stmt->close();
                
                // Obtenemos los datos del cliente para el evento
                $stmt2 = $conexion->prepare(
                    "SELECT nombre, apellidos, telefono FROM usuarios WHERE id = ?"
                );
                $stmt2->bind_param('i', $usuario_id);
                $stmt2->execute();
                $stmt2->bind_result($cli_nombre, $cli_apellidos, $cli_telefono);
                $stmt2->fetch();
                $stmt2->close();

                // Obtenemos los datos del servicio para el evento
                $stmt3 = $conexion->prepare(
                    "SELECT nombre, duracion, precio FROM servicios WHERE id = ?"
                );
                $stmt3->bind_param('i', $servicio_id);
                $stmt3->execute();
                $stmt3->bind_result($srv_nombre, $srv_duracion, $srv_precio);
                $stmt3->fetch();
                $stmt3->close();

                // Creamos el evento en Google Calendar
                $cliente  = [
                    'nombre'    => $cli_nombre,
                    'apellidos' => $cli_apellidos,
                    'telefono'  => $cli_telefono
                ];
                $servicio_data = [
                    'nombre'   => $srv_nombre,
                    'duracion' => $srv_duracion,
                    'precio'   => $srv_precio
                ];

                $google_event_id = false;
                /*
                $google_event_id = google_crear_evento(
                    $cliente,
                    $servicio_data,
                    $fecha,
                    $hora,
                    $notas
                );
                */

                // Si se creó el evento guardamos su ID en la BD
                if ($google_event_id) {
                    $stmt4 = $conexion->prepare(
                        "UPDATE reservas SET google_event_id = ? WHERE id = ?"
                    );
                    $stmt4->bind_param('si', $google_event_id, $reserva_id);
                    $stmt4->execute();
                    $stmt4->close();
                }

                $exito = '¡Reserva realizada con éxito! Te esperamos el ' .
                         date('d/m/Y', strtotime($fecha)) . ' a las ' . $hora . '.';

            } else {
                $error = 'Error al guardar la reserva. Inténtalo de nuevo.';
                $stmt->close();
            } 
            }
        }

    } // fin else consultar_horas
}

// Incluimos la cabecera común
require_once '../includes/header.php';
?>

<!-- ================================================
     FORMULARIO DE RESERVA
     ================================================ -->
<div class="formulario-contenedor" style="max-width:560px;">

    <h2>Reservar cita</h2>
    <div class="linea-deco"></div>

    <!-- Mensaje de bienvenida si acaba de registrarse -->
    <?php if (isset($_GET['bienvenido'])): ?>
        <div class="aviso aviso-exito" style="margin-bottom:25px;">
            ¡Bienvenido, <?= limpiar($usuario_nombre) ?>!
            Tu cuenta ha sido creada correctamente.
            Ahora puedes reservar tu cita.
        </div>
    <?php endif; ?>

    <!-- Saludo personalizado con nombre -->
    <div style="text-align:center; margin-bottom:30px;">
        <p style="color:var(--plateado); font-family:var(--fuente-titulo);
                  font-size:20px; letter-spacing:3px;">
            Hola, <?= limpiar($usuario_nombre) ?>
        </p>
        <p style="color:var(--blanco-suave); font-size:10px;
                  letter-spacing:2px; margin-top:5px; text-transform:uppercase;">
            Elige tu servicio y horario preferido
        </p>
    </div>

    <!-- Mensaje de error si lo hay -->
    <?php if (!empty($error)): ?>
        <div class="aviso aviso-error"><?= limpiar($error) ?></div>
    <?php endif; ?>

    <!-- Mensaje de éxito si la reserva se hizo correctamente -->
    <?php if (!empty($exito)): ?>
        <div class="aviso aviso-exito"><?= limpiar($exito) ?></div>
    <?php endif; ?>

    <!-- Solo mostramos el formulario si no hay reserva exitosa -->
    <?php if (empty($exito)): ?>
    <form method="POST" action="reservar.php">

        <!-- Servicio -->
        <div class="campo-grupo">
            <label for="servicio_id">Servicio *</label>
            <select id="servicio_id" name="servicio_id" required>
                <option value="">— Selecciona un servicio —</option>
                <?php
                // Rellenamos el select con los servicios de la BD
                if ($servicios && $servicios->num_rows > 0):
                    // Si viene de servicios.php con ?servicio=X lo preseleccionamos
                    $servicio_preseleccionado = intval($_GET['servicio'] ?? 0);
                    while ($s = $servicios->fetch_assoc()):
                        $selected = ($s['id'] == $servicio_preseleccionado) ? 'selected' : '';
                ?>
                <option value="<?= $s['id'] ?>" <?= $selected ?>>
                    <?= limpiar($s['nombre']) ?> —
                    <?= number_format($s['precio'], 2) ?>€
                    (<?= $s['duracion'] ?> min)
                </option>
                <?php
                    endwhile;
                endif;
                ?>
            </select>
        </div>

       <!-- Fecha con calendario visual por colores -->
        <div class="campo-grupo">
            <label>Fecha *</label>

            <!-- Calendario visual -->
            <div class="calendario">

                <!-- Cabecera con mes, año y flechas de navegación -->
                <div class="calendario-header">
                    <!-- Flecha mes anterior -->
                    <a href="reservar.php?mes=<?= $mes_actual - 1 ?>&anio=<?= $anio_actual ?>"
                       class="calendario-nav">&#8592;</a>

                    <!-- Nombre del mes y año -->
                    <h3>
                        <?php
                        // Nombres de los meses en español
                        $meses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
                            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
                            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        echo $meses[$mes_actual] . ' ' . $anio_actual;
                        ?>
                    </h3>

                    <!-- Flecha mes siguiente -->
                    <a href="reservar.php?mes=<?= $mes_actual + 1 ?>&anio=<?= $anio_actual ?>"
                       class="calendario-nav">&#8594;</a>
                </div>

                <!-- Días de la semana -->
                <div class="calendario-dias-semana">
                    <span>Lun</span>
                    <span>Mar</span>
                    <span>Mié</span>
                    <span>Jue</span>
                    <span>Vie</span>
                    <span>Sáb</span>
                    <span>Dom</span>
                </div>

                <!-- Grid de días del mes -->
                <div class="calendario-grid">
                    <?php
                    // Primer día del mes (1=lunes...7=domingo en ISO)
                    $primer_dia    = date('N', mktime(0, 0, 0, $mes_actual, 1, $anio_actual));
                    // Total de días del mes
                    $dias_en_mes   = date('t', mktime(0, 0, 0, $mes_actual, 1, $anio_actual));
                    // Fecha de hoy para comparar
                    $hoy           = date('Y-m-d');

                    // Celdas vacías antes del primer día
                    for ($i = 1; $i < $primer_dia; $i++) {
                        echo '<div class="calendario-dia vacio"></div>';
                    }

                    // Recorremos cada día del mes
                    for ($dia = 1; $dia <= $dias_en_mes; $dia++) {

                        // Fecha completa de este día
                        $fecha_dia = sprintf('%04d-%02d-%02d', $anio_actual, $mes_actual, $dia);

                        // Día de la semana (0=domingo, 6=sábado)
                        $dia_semana = date('w', strtotime($fecha_dia));

                        // Determinamos la clase CSS según disponibilidad
                        if ($fecha_dia < $hoy) {
                            // Día pasado
                            $clase = 'pasado';
                        } elseif ($dia_semana == 0) {
                            // Domingo: cerrado
                            $clase = 'domingo';
                        } else {
                            // Calculamos reservas y disponibilidad
                            $reservas_hoy = $reservas_por_dia[$dia] ?? 0;
                            $porcentaje   = ($reservas_hoy / $total_horas_dia) * 100;

                            if ($porcentaje >= 100) {
                                $clase = 'completo';       // Rojo: lleno
                            } elseif ($porcentaje >= 70) {
                                $clase = 'disponible-poco'; // Naranja: pocas horas
                            } elseif ($porcentaje >= 40) {
                                $clase = 'disponible-medio'; // Amarillo: algunas horas
                            } else {
                                $clase = 'disponible-alto';  // Verde: muchas horas
                            }
                        }

                        // Si este día está seleccionado lo marcamos
                        $fecha_seleccionada = $_POST['fecha'] ?? $_GET['fecha'] ?? '';
                        if ($fecha_dia === $fecha_seleccionada) {
                            $clase .= ' seleccionado';
                        }

                        // Pintamos el día
                        // Si se puede seleccionar añadimos data-fecha
                        if (!in_array($clase, ['pasado', 'domingo', 'completo'])) {
                            echo "<div class=\"calendario-dia {$clase}\"
                                       data-fecha=\"{$fecha_dia}\"
                                       onclick=\"seleccionarFecha('{$fecha_dia}')\">{$dia}</div>";
                        } else {
                            echo "<div class=\"calendario-dia {$clase}\">{$dia}</div>";
                        }
                    }
                    ?>
                </div>

                <!-- Leyenda de colores -->
                <div class="calendario-leyenda">
                    <div class="leyenda-item">
                        <div class="leyenda-punto verde"></div>
                        <span>Disponible</span>
                    </div>
                    <div class="leyenda-item">
                        <div class="leyenda-punto amarillo"></div>
                        <span>Algunas horas</span>
                    </div>
                    <div class="leyenda-item">
                        <div class="leyenda-punto naranja"></div>
                        <span>Pocas horas</span>
                    </div>
                    <div class="leyenda-item">
                        <div class="leyenda-punto rojo"></div>
                        <span>Completo</span>
                    </div>
                </div>

            </div>

            <!-- Campo oculto que guarda la fecha seleccionada -->
            <!-- Se rellena con JavaScript al pulsar un día -->
            <input type="hidden" id="fecha" name="fecha"
                   value="<?= limpiar($_POST['fecha'] ?? $_GET['fecha'] ?? '') ?>">

            <!-- Muestra la fecha seleccionada en texto -->
            <p id="fecha-seleccionada-texto"
               style="font-size:10px; color:var(--plateado);
                      letter-spacing:2px; margin-top:8px; text-align:center;">
                <?php
                $fecha_sel = $_POST['fecha'] ?? $_GET['fecha'] ?? '';
                if (!empty($fecha_sel)) {
                    echo '📅 ' . date('d/m/Y', strtotime($fecha_sel));
                } else {
                    echo '🗓 Selecciona un día del calendario';
                }
                ?>
            </p>
        </div>

        <!-- Hora con horas ocupadas deshabilitadas -->
        <div class="campo-grupo">
            <label for="hora">Hora *</label>
            <select id="hora" name="hora" required>
                <option value="">— Selecciona una hora —</option>
                <?php foreach ($horas_disponibles as $hora_opcion):
                    // Comprobamos si esta hora ya está ocupada
                    $ocupada = in_array($hora_opcion, $horas_ocupadas);
                ?>
                <option value="<?= $hora_opcion ?>"
                    <?= $ocupada ? 'disabled style="color:#444"' : '' ?>
                    <?= (isset($_POST['hora']) && $_POST['hora'] === $hora_opcion) ? 'selected' : '' ?>>
                    <?= $hora_opcion ?> <?= $ocupada ? '— Ocupado' : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
            <!-- Aviso de horas ocupadas -->
            <p style="font-size:10px; color:var(--blanco-suave);
                      letter-spacing:1px; margin-top:6px;">
                Las horas marcadas como ocupadas no están disponibles
            </p>
        </div>

        <!-- Notas opcionales -->
        <div class="campo-grupo">
            <label for="notas">Notas (opcional)</label>
            <textarea id="notas" name="notas"
                      rows="3"
                      placeholder="Alguna preferencia o indicación..."
                      style="resize:vertical;"><?= limpiar($_POST['notas'] ?? '') ?></textarea>
        </div>

        <!-- Aviso de pago -->
        <p style="font-size:10px; letter-spacing:2px; color:var(--blanco-suave);
                  text-align:center; margin-bottom:20px; text-transform:uppercase;">
             El pago se realiza en el establecimiento
        </p>

        <!-- Botón de envío -->
        <button type="submit" class="btn-form">Confirmar reserva</button>

    </form>
    <?php endif; ?>

    <!-- Enlace para ver todos los servicios -->
    <p class="formulario-enlace">
        <a href="servicios.php">← Ver todos los servicios</a>
    </p>

</div>

<?php
$conexion->close();
require_once '../includes/footer.php';
?>
