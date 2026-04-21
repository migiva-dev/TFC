<?php
// =====================================================
// ARCHIVO: admin/dashboard.php
// Descripción: Panel principal del administrador.
//              Muestra estadísticas generales y las
//              reservas más recientes.
//              Acceso restringido: solo administradores.
// =====================================================

// Incluimos configuración, conexión y funciones
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// -- Control de sesión --
// Si no hay sesión de admin activa redirige al login del admin
requiere_admin();

$titulo_pagina = 'Panel de administración';

// Nombre del admin logueado para mostrarlo en el panel
$admin_nombre = $_SESSION[ADMIN_SESSION_KEY]['nombre'];

// -------------------------------------------------------
// Consultas a la BD para obtener las estadísticas
// -------------------------------------------------------

// Total de reservas pendientes
$total_pendientes = $conexion->query(
    "SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'"
)->fetch_assoc()['total'];

// Total de reservas confirmadas
$total_confirmadas = $conexion->query(
    "SELECT COUNT(*) as total FROM reservas WHERE estado = 'confirmada'"
)->fetch_assoc()['total'];

// Total de clientes registrados
$total_clientes = $conexion->query(
    "SELECT COUNT(*) as total FROM usuarios"
)->fetch_assoc()['total'];

// Total de reservas de hoy
$total_hoy = $conexion->query(
    "SELECT COUNT(*) as total FROM reservas
     WHERE fecha = CURDATE()
     AND estado != 'cancelada'"
)->fetch_assoc()['total'];

// Últimas 10 reservas con datos del cliente y servicio
// Usamos JOIN para unir las tres tablas
$reservas = $conexion->query(
    "SELECT r.id, r.fecha, r.hora, r.estado, r.notas,
            u.nombre, u.apellidos, u.telefono,
            s.nombre AS servicio, s.precio
     FROM reservas r
     JOIN usuarios  u ON r.usuario_id  = u.id
     JOIN servicios s ON r.servicio_id = s.id
     ORDER BY r.fecha ASC, r.hora ASC
     LIMIT 10"
);

// Incluimos la cabecera común
require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- ================================================
     PANEL PRINCIPAL DE ADMINISTRACIÓN
     ================================================ -->
<div class="admin-layout">

    <!-- Barra lateral del panel -->
    <aside class="admin-sidebar">
        <div class="logo-admin">Dioni</div>

        <ul>
            <li><a href="dashboard.php" class="activo">Panel</a></li>
            <li><a href="gestionar.php">Reservas</a></li>
            <li><a href="../public/index.php">Ver web</a></li>
        </ul>
    </aside>

    <!-- Contenido principal -->
    <main class="admin-contenido">

        <h1>Panel de administración</h1>
        <div class="linea-deco"></div>

        <p style="color: var(--blanco-suave); margin-bottom: 35px;">
            Bienvenido/a, <?= limpiar($admin_nombre) ?>. Este es el resumen general de reservas.
        </p>

        <!-- Tarjetas de estadísticas -->
        <section class="stats-grid">

            <article class="stat-card">
                <div class="stat-numero"><?= (int) $total_pendientes ?></div>
                <div class="stat-label">Pendientes</div>
            </article>

            <article class="stat-card">
                <div class="stat-numero"><?= (int) $total_confirmadas ?></div>
                <div class="stat-label">Confirmadas</div>
            </article>

            <article class="stat-card">
                <div class="stat-numero"><?= (int) $total_clientes ?></div>
                <div class="stat-label">Clientes</div>
            </article>

            <article class="stat-card">
                <div class="stat-numero"><?= (int) $total_hoy ?></div>
                <div class="stat-label">Reservas hoy</div>
            </article>

        </section>

        <!-- Tabla de reservas recientes -->
        <section>
            <h2 style="font-size: 22px; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 20px;">
                Últimas reservas
            </h2>

            <?php if ($reservas && $reservas->num_rows > 0): ?>
                <table class="tabla-reservas">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Precio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($reserva = $reservas->fetch_assoc()): ?>
                            <?php
                                $estado = strtolower($reserva['estado']);
                                $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
                                $hora_formateada = date('H:i', strtotime($reserva['hora']));
                            ?>
                            <tr>
                                <td>
                                    <?= limpiar($reserva['nombre'] . ' ' . $reserva['apellidos']) ?>
                                </td>
                                <td><?= limpiar($reserva['telefono']) ?></td>
                                <td><?= limpiar($reserva['servicio']) ?></td>
                                <td><?= limpiar($fecha_formateada) ?></td>
                                <td><?= limpiar($hora_formateada) ?></td>
                                <td><?= number_format((float) $reserva['precio'], 2, ',', '.') ?> €</td>
                                <td>
                                    <span class="badge badge-<?= limpiar($estado) ?>">
                                        <?= limpiar($estado) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="aviso aviso-exito">
                    Todavía no hay reservas registradas.
                </div>
            <?php endif; ?>
        </section>

    </main>

</div>

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>