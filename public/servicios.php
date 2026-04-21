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

 <!-- ================================================
     GRID DE SERVICIOS
     ================================================ -->
<div class="seccion" style="padding-top:20px;">
    <div class="servicios-grid">
        <?php
        // Comprobamos que la consulta devolvió resultados
        if ($resultado && $resultado->num_rows > 0):
            // Recorremos cada servicio y lo mostramos
            while ($servicio = $resultado->fetch_assoc()):
        ?>
        <div class="servicio-card">

            <!-- Nombre del servicio -->
            <h3><?= limpiar($servicio['nombre']) ?></h3>

            <!-- Descripción del servicio -->
            <p><?= limpiar($servicio['descripcion']) ?></p>

            <!-- Precio destacado en plateado -->
            <div class="servicio-precio">
                <?= number_format($servicio['precio'], 2) ?>€
            </div>

            <!-- Duración en minutos -->
            <div class="servicio-duracion">
                <?= limpiar($servicio['duracion']) ?> min
            </div>

        </div>
        <?php
            endwhile;
        else:
        ?>
            <!-- Mensaje si no hay servicios en la BD -->
            <p style="text-align:center; color:var(--blanco-suave); grid-column:1/-1;">
                No hay servicios disponibles en este momento.
            </p>
        <?php endif; ?>
    </div>
</div>

<!-- Aviso de pago irá aquí -->

<?php
// Cerramos la conexión a la BD
$conexion->close();

// Incluimos el pie de página común
require_once '../includes/footer.php';
?>