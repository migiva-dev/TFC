<?php
// =====================================================
// ARCHIVO: public/index.php
// Descripción: Página principal (inicio) de la web.
//              Muestra el hero, servicios destacados
//              y llamada a la acción para reservar.
// =====================================================

// Incluimos configuración, conexión y funciones
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/funciones.php';

// Título que aparecerá en la pestaña del navegador
$titulo_pagina = 'Inicio';

// -- Obtenemos los servicios de la BD --
// Traemos todos los servicios ordenados por precio ascendente
$resultado = $conexion->query("SELECT * FROM servicios ORDER BY precio ASC");

// Incluimos la cabecera común (navegación + head HTML)
require_once '../includes/header.php';
?>

<!-- ================================================
     SECCIÓN HERO — Presentación principal
     ================================================ -->
<section class="hero">
    <div class="hero-contenido">

        <!-- Línea decorativa plateada -->
        <div class="hero-linea"></div>

        <!-- Título principal con letra en plateado -->
        <h1>D<span>ioni</span></h1>

        <!-- Subtítulo -->
        <p class="hero-subtitulo">Barbería & Estilismo — Valencia</p>

        <!-- Botones de acción -->
        <a href="reservar.php" class="btn-principal">Reservar cita</a>
        &nbsp;&nbsp;
        <a href="servicios.php" class="btn-secundario">Ver servicios</a>

    </div>
</section>

<!-- Separador visual entre secciones -->
<hr style="border:none; border-top:1px solid #1a1a1a;">

<!-- ================================================
     SECCIÓN SERVICIOS DESTACADOS
     ================================================ -->
<div class="seccion">
    <div class="seccion-titulo">
        <h2>Nuestros Servicios</h2>
        <div class="linea-deco"></div>
    </div>

    <!-- Grid de tarjetas de servicios -->
    <div class="servicios-grid">
        <?php
        // Comprobamos que la consulta devolvió resultados
        if ($resultado && $resultado->num_rows > 0):
            // Recorremos cada servicio y lo pintamos
            while ($servicio = $resultado->fetch_assoc()):
        ?>
        <div class="servicio-card">
            <!-- Nombre del servicio -->
            <h3><?= limpiar($servicio['nombre']) ?></h3>

            <!-- Descripción -->
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
            <p style="text-align:center; color:var(--blanco-suave);">
                No hay servicios disponibles en este momento.
            </p>
        <?php endif; ?>
    </div>

    <!-- Botón para ir a la página completa de servicios -->
    <div style="text-align:center; margin-top:50px;">
        <a href="servicios.php" class="btn-principal">Ver todos los servicios</a>
    </div>
</div>

<!-- ================================================
     SECCIÓN LLAMADA A LA ACCIÓN — Reservar
     ================================================ -->
<div style="background-color:var(--negro-suave); border-top:1px solid #1a1a1a; border-bottom:1px solid #1a1a1a;">
    <div class="seccion" style="text-align:center;">

        <div class="hero-linea"></div>

        <h2 style="font-size:36px; letter-spacing:8px; margin-bottom:15px;">
            ¿Listo para tu cita?
        </h2>

        <p style="color:var(--blanco-suave); font-size:12px; letter-spacing:2px; margin-bottom:40px;">
            Reserva online en cualquier momento.<br>
            El pago se realiza en el establecimiento.
        </p>

        <a href="reservar.php" class="btn-principal">Reservar ahora</a>

    </div>
</div>

<?php
// Cerramos la conexión a la base de datos
$conexion->close();

// Incluimos el pie de página común
require_once '../includes/footer.php';
?>