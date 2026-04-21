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

<!-- Contenido de la página irá aquí -->

<?php
// Cerramos la conexión a la BD
$conexion->close();

// Incluimos el pie de página común
require_once '../includes/footer.php';
?>