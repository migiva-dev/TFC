<?php
// =====================================================
// ARCHIVO: includes/funciones.php
// Descripción: Funciones reutilizables en toda la web.
//              Se incluye junto con db.php al inicio
//              de cada página PHP.
// =====================================================

// -----------------------------------------------------
// Inicia la sesión PHP si no está ya iniciada.
// Necesario antes de usar $_SESSION.
// -----------------------------------------------------
function iniciar_sesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// -----------------------------------------------------
// Comprueba si hay un USUARIO CLIENTE con sesión activa.
// Si no está logueado, lo redirige al login.
// Úsala al inicio de páginas que requieren login.
// -----------------------------------------------------
function requiere_usuario() {
    iniciar_sesion();
    if (!isset($_SESSION[USER_SESSION_KEY])) {
        // Guardamos la URL a la que intentaba acceder
        // para redirigirle después del login
        header('Location: ' . SITIO_URL . '/login.php?aviso=debes_iniciar_sesion');
        exit();
    }
}

// -----------------------------------------------------
// Comprueba si hay un ADMINISTRADOR con sesión activa.
// Si no está logueado, lo redirige al login del admin.
// Úsala al inicio de todas las páginas del panel admin.
// -----------------------------------------------------
function requiere_admin() {
    iniciar_sesion();
    if (!isset($_SESSION[ADMIN_SESSION_KEY])) {
        header('Location: ../admin/login.php?aviso=acceso_denegado');
        exit();
    }
}

// -----------------------------------------------------
// Limpia y escapa un valor para mostrarlo en HTML.
// Previene ataques XSS (Cross-Site Scripting).
// Úsala siempre que muestres datos del usuario en HTML.
// Ejemplo: echo limpiar($nombre);
// -----------------------------------------------------
function limpiar($valor) {
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

// -----------------------------------------------------
// Redirige al navegador a otra URL y detiene el script.
// Úsala tras procesar formularios.
// Ejemplo: redirigir('index.php');
// -----------------------------------------------------
function redirigir($url) {
    header('Location: ' . $url);
    exit();
}

// -----------------------------------------------------
// Comprueba si el usuario cliente está logueado.
// Devuelve true o false (no redirige).
// Úsala para mostrar/ocultar elementos en la web.
// Ejemplo: if (esta_logueado()) { ... }
// -----------------------------------------------------
function esta_logueado() {
    iniciar_sesion();
    return isset($_SESSION[USER_SESSION_KEY]);
}

// -----------------------------------------------------
// Comprueba si el administrador está logueado.
// Devuelve true o false (no redirige).
// -----------------------------------------------------
function es_admin() {
    iniciar_sesion();
    return isset($_SESSION[ADMIN_SESSION_KEY]);
}
?>