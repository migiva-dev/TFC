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

// Lógica de acciones irá aquí

// Consulta de reservas irá aquí

// Incluimos la cabecera común
require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- Contenido irá aquí -->

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>