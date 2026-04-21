<?php
// =====================================================
// ARCHIVO: admin/dashboard.php
// Descripción: Panel principal del administrador.
//              Muestra estadísticas generales y las
//              reservas más recientes.
//              Acceso restringido: solo administradores.
// =====================================================

// Incluimos configuración, conexión y funciones
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// -- Control de sesión --
// Si no hay sesión de admin activa redirige al login del admin
requiere_admin();

$titulo_pagina = 'Panel de administración';

// Nombre del admin logueado para mostrarlo en el panel
$admin_nombre = $_SESSION[ADMIN_SESSION_KEY]['nombre'];

// -------------------------------------------------------
// Consultas a la BD para obtener las estadísticas
// -------------------------------------------------------

// Total de reservas pendientes
$total_pendientes = $conexion->query(
    "SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'"
)->fetch_assoc()['total'];

// Total de reservas confirmadas
$total_confirmadas = $conexion->query(
    "SELECT COUNT(*) as total FROM reservas WHERE estado = 'confirmada'"
)->fetch_assoc()['total'];

// Total de clientes registrados
$total_clientes = $conexion->query(
    "SELECT COUNT(*) as total FROM usuarios"
)->fetch_assoc()['total'];

// Total de reservas de hoy
$total_hoy = $conexion->query(
    "SELECT COUNT(*) as total FROM reservas
     WHERE fecha = CURDATE()
     AND estado != 'cancelada'"
)->fetch_assoc()['total'];

// Últimas 10 reservas con datos del cliente y servicio
// Usamos JOIN para unir las tres tablas
$reservas = $conexion->query(
    "SELECT r.id, r.fecha, r.hora, r.estado, r.notas,
            u.nombre, u.apellidos, u.telefono,
            s.nombre AS servicio, s.precio
     FROM reservas r
     JOIN usuarios  u ON r.usuario_id  = u.id
     JOIN servicios s ON r.servicio_id = s.id
     ORDER BY r.fecha ASC, r.hora ASC
     LIMIT 10"
);

// Incluimos la cabecera común
require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- Contenido del dashboard irá aquí -->

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>