<?php
// =====================================================
// ARCHIVO: public/logout.php
// Descripción: Cierra la sesión del cliente.
//              Destruye todos los datos de sesión
//              y redirige a la página principal.
// =====================================================

require_once '../includes/config.php';
require_once '../includes/funciones.php';

// Iniciamos la sesión para poder destruirla
iniciar_sesion();

// Eliminamos la variable de sesión del usuario
// (solo la del cliente, no la del admin)
unset($_SESSION[USER_SESSION_KEY]);

// Si no queda ninguna sesión activa, destruimos
// completamente la sesión de PHP
if (empty($_SESSION)) {
    session_destroy();
}

// Redirigimos a la página principal
redirigir(SITIO_URL . '/index.php');
?>