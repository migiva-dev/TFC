<?php
// =====================================================
// ARCHIVO: public/servicios.php
// Descripción: Página pública con todos los servicios
//              y precios de la peluquería.
//              No requiere estar logueado para verla.
// =====================================================

// Incluimos configuración, conexión y funciones
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/funciones.php';

// Título de la pestaña del navegador
$titulo_pagina = 'Servicios';

// -- Consulta: obtenemos todos los servicios de la BD --
// Ordenados por precio de menor a mayor
$resultado = $conexion->query("SELECT * FROM servicios ORDER BY precio ASC");

// Incluimos la cabecera común
require_once '../includes/header.php';
?>

<!-- ================================================
     CABECERA DE LA PÁGINA
     ================================================ -->
<div style="padding: 80px 40px 40px; text-align:center;">

    <!-- Línea decorativa plateada -->
    <div class="hero-linea"></div>

    <!-- Título de la página -->
    <h1 style="font-size: clamp(32px, 5vw, 56px); letter-spacing:10px; text-transform:uppercase;">
        Servicios
    </h1>

    <!-- Subtítulo -->
    <p style="color:var(--blanco-suave); font-size:11px; letter-spacing:3px; margin-top:15px;">
        Calidad y precisión en cada corte
    </p>

</div>

<!-- Contenido de servicios irá aquí -->

<?php
// Cerramos la conexión a la BD
$conexion->close();

// Incluimos el pie de página común
require_once '../includes/footer.php';
?>