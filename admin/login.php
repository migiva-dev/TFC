<?php
// =====================================================
// ARCHIVO: admin/login.php
// Descripción: Login privado del panel de administración.
//              Completamente independiente del login
//              de clientes. Doble capa de seguridad:
//              1. URL no enlazada desde la web pública
//              2. Usuario y contraseña propios del admin
// =====================================================

// Incluimos configuración y funciones
// Usamos dirname para subir un nivel desde /admin a /includes
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// Título de la pestaña
$titulo_pagina = 'Administración — Acceso';

// Iniciamos la sesión
iniciar_sesion();

// Si el admin ya está logueado lo mandamos al dashboard
if (es_admin()) {
    redirigir('../admin/dashboard.php');
}

$error = '';

// Lógica de autenticación irá aquí

// Incluimos la cabecera común
require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- Contenido irá aquí -->

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>