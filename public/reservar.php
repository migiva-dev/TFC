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

// Lógica del formulario y HTML irán aquí

// Incluimos la cabecera común
require_once '../includes/header.php';
?>

<!-- Contenido irá aquí -->

<?php
$conexion->close();
require_once '../includes/footer.php';
?>