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
                } else {
                    $error = 'Error al guardar la reserva. Inténtalo de nuevo.';
                }

                $stmt->close();
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

        <!-- Fecha con calendario visual -->
        <div class="campo-grupo">
            <label for="fecha">Fecha *</label>
            <input type="date" id="fecha" name="fecha"
                   min="<?= date('Y-m-d') ?>"
                   value="<?= limpiar($_POST['fecha'] ?? '') ?>"
                   style="cursor:pointer;"
                   required>
            <!-- Aviso de días cerrados -->
            <p style="font-size:10px; color:var(--blanco-suave);
                      letter-spacing:1px; margin-top:6px;">
                🗓 Abrimos de lunes a sábado
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