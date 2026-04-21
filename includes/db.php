<?php
// =====================================================
// ARCHIVO: includes/db.php
// Descripción: Establece la conexión con la base de
//              datos MySQL de AwardSpace.
//              Se incluye en todas las páginas que
//              necesiten acceder a datos.
// =====================================================

// Cargamos el archivo de configuración con los datos de conexión
require_once __DIR__ . '/config.php';

// Intentamos conectar con la base de datos
$conexion = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NOMBRE);

// Si hay error de conexión, mostramos mensaje genérico
// (nunca mostramos el error técnico al visitante por seguridad)
if ($conexion->connect_error) {
    die('Error al conectar con la base de datos. Contacte con el administrador.');
}

// Establecemos UTF-8 para que los acentos funcionen correctamente
$conexion->set_charset('utf8mb4');
?>