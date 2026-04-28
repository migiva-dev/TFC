<?php
// =====================================================
// ARCHIVO: admin/nueva-reserva.php
// Descripción: Permite al administrador crear una
//              reserva manualmente para cualquier
//              cliente registrado o con nombre libre.
//              Acceso restringido: solo administradores.
// =====================================================

// Incluimos configuración, conexión y funciones
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// -- Control de sesión --
// Si no hay sesión de admin activa redirige al login
requiere_admin();

$titulo_pagina = 'Nueva reserva';

// -- Obtenemos todos los servicios disponibles --
$servicios = $conexion->query("SELECT * FROM servicios ORDER BY precio ASC");

// -- Obtenemos todos los clientes registrados --
$clientes = $conexion->query(
    "SELECT id, nombre, apellidos, telefono FROM usuarios ORDER BY nombre ASC"
);

// -- Horas disponibles de 9:00 a 19:30 cada 30 minutos --
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
    $usuario_id  = intval($_POST['usuario_id']  ?? 0);
    $servicio_id = intval($_POST['servicio_id'] ?? 0);
    $fecha       = trim($_POST['fecha']         ?? '');
    $hora        = trim($_POST['hora']          ?? '');
    $notas       = trim($_POST['notas']         ?? '');

    // -- Validaciones --
    if (empty($usuario_id) || empty($servicio_id) || empty($fecha) || empty($hora)) {
        $error = 'Por favor, rellena todos los campos obligatorios.';

    // La fecha no puede ser anterior a hoy
    } elseif ($fecha < date('Y-m-d')) {
        $error = 'La fecha no puede ser anterior a hoy.';

    // No se puede reservar en domingo
    } elseif (date('w', strtotime($fecha)) == 0) {
        $error = 'Los domingos estamos cerrados.';

    } else {

        // Comprobamos que no haya ya una reserva en esa fecha y hora
        $stmt = $conexion->prepare(
            "SELECT id FROM reservas
             WHERE fecha = ? AND hora = ?
             AND estado != 'cancelada'"
        );
        $stmt->bind_param('ss', $fecha, $hora);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Ese horario ya está ocupado. Elige otra hora.';
            $stmt->close();

        } else {
            $stmt->close();

            // Insertamos la reserva directamente como confirmada
            // ya que la crea el propio administrador
            $stmt = $conexion->prepare(
                "INSERT INTO reservas
                 (usuario_id, servicio_id, fecha, hora, estado, notas)
                 VALUES (?, ?, ?, ?, 'confirmada', ?)"
            );
            $stmt->bind_param('iisss', $usuario_id, $servicio_id, $fecha, $hora, $notas);

            if ($stmt->execute()) {
                $exito = ' Reserva creada correctamente para el ' .
                         date('d/m/Y', strtotime($fecha)) . ' a las ' . $hora . '.';
            } else {
                $error = 'Error al crear la reserva. Inténtalo de nuevo.';
            }

            $stmt->close();
        }
    }
}

// Incluimos la cabecera común
require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- Contenido irá aquí -->

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>