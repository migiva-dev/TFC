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
   
    <!-- Logo imagen en el pie enlazado a la página principal -->
    <a href="<?= SITIO_URL ?>/index.php" style="display:block;">
        <img src="http://tfc-peluqueria.atwebpages.com/assets/img/logo.png"
             alt="Dioni Peluqueros"
             style="height:55px; width:auto; margin-bottom:20px;">
    </a>

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