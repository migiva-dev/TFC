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

    // ---------------------------------------------------
    // MENÚ HAMBURGUESA PARA MÓVIL
    // Muestra y oculta el menú en pantallas pequeñas
    // ---------------------------------------------------
    const hamburguesa = document.querySelector('.menu-hamburguesa');
    const navLinks    = document.querySelector('.nav-links');

    if (hamburguesa && navLinks) {
        hamburguesa.addEventListener('click', function () {
            // Alternamos la clase 'abierto' para mostrar/ocultar el menú
            navLinks.classList.toggle('abierto');
        });

        // Cerramos el menú al pulsar cualquier enlace
        navLinks.querySelectorAll('a').forEach(function (enlace) {
            enlace.addEventListener('click', function () {
                navLinks.classList.remove('abierto');
            });
        });
    }

    // ---------------------------------------------------
    // ACTUALIZAR HORAS OCUPADAS AL CAMBIAR LA FECHA
    // Cuando el usuario cambia la fecha recargamos
    // el formulario para actualizar las horas disponibles
    // ---------------------------------------------------
    const inputFechaReserva = document.getElementById('fecha');
    if (inputFechaReserva) {
        inputFechaReserva.addEventListener('change', function () {
            // Buscamos el formulario padre
            const form = this.closest('form');
            if (form) {
                // Añadimos un campo oculto para indicar que es solo
                // una consulta de horas, no una reserva real
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'consultar_horas';
                input.value = '1';
                form.appendChild(input);
                form.submit();
            }
        });
    }

    // ---------------------------------------------------
    // EFECTO TRANSPARENTE EN EL HEADER AL HACER SCROLL
    // Cuando estamos arriba del todo el header es
    // transparente. Al bajar se vuelve blanco con blur.
    // ---------------------------------------------------
    const headerEl = document.querySelector('header');

    if (headerEl) {

        // Función que actualiza el estado del header
        function actualizarHeader() {
            const scrollY = window.scrollY;

            if (scrollY < 50) {
                // Estamos arriba del todo: header transparente
                headerEl.classList.add('en-top');
                headerEl.classList.remove('transparente');
            } else {
                // Hemos bajado: header blanco con efecto blur
                headerEl.classList.remove('en-top');
                headerEl.classList.add('transparente');
            }
        }

        // Ejecutamos al cargar la página para el estado inicial
        actualizarHeader();

        // Ejecutamos cada vez que el usuario hace scroll
        window.addEventListener('scroll', actualizarHeader);
    }

    // ---------------------------------------------------
    // SELECCIÓN DE FECHA EN EL CALENDARIO VISUAL
    // Al pulsar un día del calendario actualiza el
    // campo oculto de fecha y recarga las horas
    // ---------------------------------------------------
    window.seleccionarFecha = function(fecha) {

        // Actualizamos el campo oculto con la fecha seleccionada
        const inputFechaOculto = document.getElementById('fecha');
        if (inputFechaOculto) {
            inputFechaOculto.value = fecha;
        }

        // Actualizamos el texto que muestra la fecha seleccionada
        const texto = document.getElementById('fecha-seleccionada-texto');
        if (texto) {
            // Formateamos la fecha de YYYY-MM-DD a DD/MM/YYYY
            const partes = fecha.split('-');
            texto.textContent = '📅 ' + partes[2] + '/' + partes[1] + '/' + partes[0];
        }

        // Quitamos la clase seleccionado de todos los días
        document.querySelectorAll('.calendario-dia').forEach(function(dia) {
            dia.classList.remove('seleccionado');
        });

        // Añadimos la clase seleccionado al día pulsado
        const diaSeleccionado = document.querySelector('[data-fecha="' + fecha + '"]');
        if (diaSeleccionado) {
            diaSeleccionado.classList.add('seleccionado');
        }

        // Enviamos el formulario para actualizar las horas disponibles
        const form = document.querySelector('form');
        if (form) {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'consultar_horas';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    };

});