<?php
// =====================================================
// ARCHIVO: includes/footer.php
// Descripción: Pie de página común a todas las páginas.
//              Se incluye al final de cada página pública.
// =====================================================
?>

<!-- ================================================
     PIE DE PÁGINA
     ================================================ -->
<footer>

    <!-- Grid de 3 columnas -->
    <div class="footer-grid">

        <!-- Columna 1: Logo e info -->
        <div class="footer-col">
            <a href="<?= SITIO_URL ?>/index.php" style="display:inline-block; margin-bottom:15px;">
                <img src="https://tfc-peluqueria.atwebpages.com/assets/img/logo.png"
                     alt="Dioni Peluqueros"
                     style="height:45px; width:auto;">
            </a>
            <p>Barbería & Estilismo</p>
            <p>Valencia</p>
        </div>

        <!-- Columna 2: Horario -->
        <div class="footer-col">
            <h4>Horario</h4>
            <p>Lunes a Viernes: 9:00 — 20:00</p>
            <p>Sábado: 9:00 — 14:00</p>
            <p>Domingo: Cerrado</p>
        </div>

        <!-- Columna 3: Contacto y enlaces -->
        <div class="footer-col">
            <h4>Contacto</h4>
            <p>Calle Ejemplo, 00 — Valencia</p>
            <p>Tel: 600 000 000</p>
            <a href="<?= SITIO_URL ?>/servicios.php">Servicios</a>
            <a href="<?= SITIO_URL ?>/reservar.php">Reservar cita</a>
        </div>

    </div>

    <!-- Línea separadora -->
    <div class="footer-divider"></div>

    <!-- Copyright con año automático -->
    <p class="footer-copy">
        &copy; <?= date('Y') ?> <?= SITIO_NOMBRE ?> — Todos los derechos reservados
    </p>

</footer>

<!-- JavaScript principal con HTTPS -->
<script src="https://tfc-peluqueria.atwebpages.com/assets/js/main.js"></script>

</body>
</html>