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
    <!-- Logo en el pie -->
    <div class="footer-logo">Dioni</div>

    <!-- Información de contacto de la peluquería -->
    <div class="footer-info">
        <p>Calle Ejemplo, 00 — Valencia</p>
        <p>Tel: 600 000 000</p>
        <p>Lunes a Sábado: 9:00 — 20:00</p>
    </div>

    <!-- Copyright con año automático -->
    <p class="footer-copy">
        &copy; <?= date('Y') ?> <?= SITIO_NOMBRE ?> — Todos los derechos reservados
    </p>
</footer>

<!-- JavaScript principal -->
<script src="<?= SITIO_URL ?>/../assets/js/main.js"></script>

</body>
</html>