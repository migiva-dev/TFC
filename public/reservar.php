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
// Procesamos el formulario cuando se envía (método POST)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
}

// Incluimos la cabecera común

// Incluimos la cabecera común
require_once '../includes/header.php';
?>

<!-- Contenido irá aquí -->

<?php
$conexion->close();
require_once '../includes/footer.php';
?>