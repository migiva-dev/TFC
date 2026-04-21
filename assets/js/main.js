// =====================================================
// ARCHIVO: assets/js/main.js
// Descripción: JavaScript general de la web.
//              Funciones básicas de interacción
//              y mejoras de usabilidad.
// =====================================================

// -------------------------------------------------------
// Esperamos a que el DOM esté completamente cargado
// antes de ejecutar cualquier función
// -------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {

    // ---------------------------------------------------
    // FECHA MÍNIMA EN EL FORMULARIO DE RESERVA
    // Evita que el usuario seleccione una fecha pasada
    // ---------------------------------------------------
    const inputFecha = document.getElementById('fecha');
    if (inputFecha) {
        // Obtenemos la fecha de hoy en formato YYYY-MM-DD
        const hoy = new Date().toISOString().split('T')[0];
        inputFecha.setAttribute('min', hoy);
    }

    // ---------------------------------------------------
    // CONFIRMACIÓN ANTES DE CANCELAR UNA RESERVA
    // Muestra un diálogo de confirmación al admin
    // antes de cancelar para evitar cancelaciones
    // accidentales
    // ---------------------------------------------------
    const botonesCancelar = document.querySelectorAll('a[href*="accion=cancelar"]');
    botonesCancelar.forEach(function (boton) {
        boton.addEventListener('click', function (e) {
            // Si el usuario pulsa Cancelar en el diálogo, no hacemos nada
            if (!confirm('¿Seguro que quieres cancelar esta reserva?')) {
                e.preventDefault();
            }
        });
    });

    // ---------------------------------------------------
    // CONFIRMACIÓN ANTES DE CONFIRMAR UNA RESERVA
    // Evita confirmaciones accidentales en el panel admin
    // ---------------------------------------------------
    const botonesConfirmar = document.querySelectorAll('a[href*="accion=confirmar"]');
    botonesConfirmar.forEach(function (boton) {
        boton.addEventListener('click', function (e) {
            if (!confirm('¿Confirmar esta reserva?')) {
                e.preventDefault();
            }
        });
    });

    // ---------------------------------------------------
    // OCULTAR MENSAJES DE AVISO AUTOMÁTICAMENTE
    // Los mensajes de éxito desaparecen solos
    // después de 4 segundos
    // ---------------------------------------------------
    const avisos = document.querySelectorAll('.aviso-exito');
    avisos.forEach(function (aviso) {
        setTimeout(function () {
            // Añadimos transición suave antes de ocultar
            aviso.style.transition = 'opacity 0.8s ease';
            aviso.style.opacity    = '0';

            // Eliminamos el elemento del DOM tras la transición
            setTimeout(function () {
                aviso.remove();
            }, 800);
        }, 4000);
    });

});