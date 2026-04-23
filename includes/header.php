<?php
// =====================================================
// ARCHIVO: includes/header.php
// Descripción: Cabecera HTML común a todas las páginas.
//              Incluye el menú de navegación principal.
//              Se incluye al inicio de cada página pública.
// =====================================================
require_once __DIR__ . '/funciones.php';
iniciar_sesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo_pagina) ? limpiar($titulo_pagina) . ' — ' . SITIO_NOMBRE : SITIO_NOMBRE ?></title>

    <!-- Ruta absoluta al CSS para que funcione en AwardSpace -->
    <link rel="stylesheet" href="http://tfc-peluqueria.atwebpages.com/assets/css/estilo.css">
</head>
<body>

<!-- ================================================
     CABECERA Y NAVEGACIÓN PRINCIPAL
     ================================================ -->
<header>
    <nav>

       <!-- Logo de la peluquería como imagen -->
        <a href="<?= SITIO_URL ?>/index.php" class="logo">
            <img src="http://tfc-peluqueria.atwebpages.com/assets/img/logo.png"
                 alt="Dioni Peluqueros"
                 style="height:45px; width:auto;">
        </a>

        <!-- Botón hamburguesa visible solo en móvil -->
        <button class="menu-hamburguesa" aria-label="Abrir menú">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Menú de navegación -->
        <ul class="nav-links">
            <li><a href="<?= SITIO_URL ?>/index.php">Inicio</a></li>
            <li><a href="<?= SITIO_URL ?>/servicios.php">Servicios</a></li>

            <?php if (esta_logueado()): ?>
                <!-- Usuario logueado: mostramos Reservar y Cerrar sesión -->
                <li><a href="<?= SITIO_URL ?>/reservar.php" class="btn-nav">Reservar</a></li>
                <li><a href="<?= SITIO_URL ?>/logout.php">Cerrar sesión</a></li>
            <?php else: ?>
                <!-- Usuario no logueado: mostramos Entrar y Reservar -->
                <li><a href="<?= SITIO_URL ?>/login.php">Entrar</a></li>
                <li><a href="<?= SITIO_URL ?>/reservar.php" class="btn-nav">Reservar</a></li>
            <?php endif; ?>
        </ul>

    </nav>
</header>