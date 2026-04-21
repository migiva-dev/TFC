<?php
// =====================================================
// ARCHIVO: admin/gestionar.php
// Descripción: Página de gestión completa de reservas.
//              Permite al administrador confirmar,
//              cancelar y filtrar todas las reservas.
//              Acceso restringido: solo administradores.
// =====================================================

// Incluimos configuración, conexión y funciones
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// -- Control de sesión --
// Si no hay sesión de admin activa redirige al login
requiere_admin();

$titulo_pagina = 'Gestión de reservas';

// Mensaje de confirmación o error tras una acción
$mensaje = '';
$tipo    = '';  // 'exito' o 'error'

// -------------------------------------------------------
// Procesamos la acción recibida por GET (confirmar/cancelar)
// -------------------------------------------------------
if (isset($_GET['accion']) && isset($_GET['id'])) {

    // Recogemos y validamos los parámetros
    $accion = trim($_GET['accion'] ?? '');
    $id     = intval($_GET['id']   ?? 0);

    // El id debe ser un número positivo
    if ($id > 0) {

        // Comprobamos qué acción se quiere realizar
        if ($accion === 'confirmar') {

            // Cambiamos el estado de la reserva a 'confirmada'
            $stmt = $conexion->prepare(
                "UPDATE reservas SET estado = 'confirmada' WHERE id = ?"
            );
            $stmt->bind_param('i', $id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $mensaje = 'Reserva confirmada correctamente.';
                $tipo    = 'exito';
            } else {
                $mensaje = 'No se pudo confirmar la reserva.';
                $tipo    = 'error';
            }

            $stmt->close();

        } elseif ($accion === 'cancelar') {

            // Cambiamos el estado de la reserva a 'cancelada'
            $stmt = $conexion->prepare(
                "UPDATE reservas SET estado = 'cancelada' WHERE id = ?"
            );
            $stmt->bind_param('i', $id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $mensaje = 'Reserva cancelada correctamente.';
                $tipo    = 'exito';
            } else {
                $mensaje = 'No se pudo cancelar la reserva.';
                $tipo    = 'error';
            }

            $stmt->close();

        } else {
            // Acción no reconocida
            $mensaje = 'Acción no válida.';
            $tipo    = 'error';
        }
    }
}

// -------------------------------------------------------
// Consulta de reservas con filtro de estado opcional
// -------------------------------------------------------

// Recogemos el filtro de estado si se ha seleccionado
$filtro = trim($_GET['filtro'] ?? '');

// Construimos la consulta según el filtro
if (!empty($filtro) && in_array($filtro, ['pendiente', 'confirmada', 'cancelada'])) {
    // Filtramos por estado concreto
    $stmt = $conexion->prepare(
        "SELECT r.id, r.fecha, r.hora, r.estado, r.notas,
                u.nombre, u.apellidos, u.telefono,
                s.nombre AS servicio, s.precio
         FROM reservas r
         JOIN usuarios  u ON r.usuario_id  = u.id
         JOIN servicios s ON r.servicio_id = s.id
         WHERE r.estado = ?
         ORDER BY r.fecha ASC, r.hora ASC"
    );
    $stmt->bind_param('s', $filtro);
    $stmt->execute();
    $reservas = $stmt->get_result();
    $stmt->close();

} else {
    // Sin filtro: traemos todas las reservas
    $reservas = $conexion->query(
        "SELECT r.id, r.fecha, r.hora, r.estado, r.notas,
                u.nombre, u.apellidos, u.telefono,
                s.nombre AS servicio, s.precio
         FROM reservas r
         JOIN usuarios  u ON r.usuario_id  = u.id
         JOIN servicios s ON r.servicio_id = s.id
         ORDER BY r.fecha ASC, r.hora ASC"
    );
}

// Incluimos la cabecera común
require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- Contenido irá aquí -->

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>