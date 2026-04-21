<?php
// =====================================================
// ARCHIVO: admin/logout.php
// Descripción: Cierra la sesión del administrador.
//              Destruye solo la sesión del admin,
//              no la del cliente si la hubiera.
//              Redirige al login del admin.
// =====================================================

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// Iniciamos la sesión para poder destruirla
iniciar_sesion();

// Eliminamos solo la variable de sesión del admin
unset($_SESSION[ADMIN_SESSION_KEY]);

// Si no queda ninguna sesión activa destruimos todo
if (empty($_SESSION)) {
    session_destroy();
}

// Redirigimos al login del administrador
redirigir('../admin/login.php');
?>