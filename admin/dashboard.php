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

// Consultas a la BD irán aquí

// Incluimos la cabecera común
require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- Contenido del dashboard irá aquí -->

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>