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
require_once dirname(__DIR__) . '/includes/google-calendar.php';

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

// -- Obtenemos los eventos de Google Calendar de esta semana --
$eventos_semana = google_obtener_eventos_semana();

// Incluimos la cabecera común
require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- ================================================
     LAYOUT DEL PANEL DE ADMINISTRACIÓN
     ================================================ -->
<div class="admin-layout">

    <!-- ============================================
         SIDEBAR — Menú lateral izquierdo
         ============================================ -->
    <aside class="admin-sidebar">

        <!-- Logo en el sidebar -->
        <div class="logo-admin">Dioni</div>

        <!-- Menú de navegación del panel -->
        <ul>
            <li>
                <a href="dashboard.php" class="activo">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="gestionar.php">
                    Reservas
                </a>
            </li>
            <li>
                <!-- Enlace para crear una nueva reserva manualmente -->
                <a href="nueva-reserva.php">
                    Nueva reserva
                </a>
            </li>
            <li>
                <!-- Cerrar sesión del administrador -->
                <a href="logout.php">
                    Cerrar sesión
                </a>
            </li>
        </ul>

    </aside>

    <!-- ============================================
         CONTENIDO PRINCIPAL DEL DASHBOARD
         ============================================ -->
    <main class="admin-contenido">

        <!-- Saludo con el nombre del admin -->
        <h1>Bienvenido, <?= limpiar($admin_nombre) ?></h1>
        <div class="linea-deco"></div>

        <!-- ----------------------------------------
             TARJETAS DE ESTADÍSTICAS
             ---------------------------------------- -->
        <div class="stats-grid">

            <!-- Reservas pendientes -->
            <div class="stat-card">
                <div class="stat-numero"><?= $total_pendientes ?></div>
                <div class="stat-label">Pendientes</div>
            </div>

            <!-- Reservas confirmadas -->
            <div class="stat-card">
                <div class="stat-numero"><?= $total_confirmadas ?></div>
                <div class="stat-label">Confirmadas</div>
            </div>

            <!-- Citas de hoy -->
            <div class="stat-card">
                <div class="stat-numero"><?= $total_hoy ?></div>
                <div class="stat-label">Citas hoy</div>
            </div>

            <!-- Total de clientes registrados -->
            <div class="stat-card">
                <div class="stat-numero"><?= $total_clientes ?></div>
                <div class="stat-label">Clientes</div>
            </div>

        </div>

        <!-- ----------------------------------------
             TABLA DE RESERVAS RECIENTES
             ---------------------------------------- -->
        <h2 style="font-size:20px; letter-spacing:4px;
                   text-transform:uppercase; margin-bottom:10px;">
            Próximas reservas
        </h2>
        <div class="linea-deco" style="margin-bottom:30px;"></div>

        <?php if ($reservas && $reservas->num_rows > 0): ?>
        <div style="overflow-x:auto;">
            <table class="tabla-reservas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = $reservas->fetch_assoc()): ?>
                    <tr>
                        <!-- ID de la reserva -->
                        <td><?= $r['id'] ?></td>

                        <!-- Nombre completo del cliente -->
                        <td><?= limpiar($r['nombre']) . ' ' . limpiar($r['apellidos']) ?></td>

                        <!-- Teléfono del cliente -->
                        <td><?= limpiar($r['telefono']) ?></td>

                        <!-- Servicio reservado -->
                        <td><?= limpiar($r['servicio']) ?></td>

                        <!-- Fecha formateada en español -->
                        <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>

                        <!-- Hora -->
                        <td><?= limpiar($r['hora']) ?></td>

                        <!-- Badge de estado coloreado -->
                        <td>
                            <span class="badge badge-<?= $r['estado'] ?>">
                                <?= ucfirst($r['estado']) ?>
                            </span>
                        </td>

                        <!-- Botones de acción -->
                        <td>
                            <a href="gestionar.php?accion=confirmar&id=<?= $r['id'] ?>"
                               class="btn-principal"
                               style="font-size:9px; padding:6px 14px; margin-right:5px;">
                                Confirmar
                            </a>
                            <a href="gestionar.php?accion=cancelar&id=<?= $r['id'] ?>"
                               class="btn-form"
                               style="font-size:9px; padding:6px 14px;
                                      display:inline-block; width:auto;
                                      color:#ff6b6b; border-color:#ff6b6b;">
                                Cancelar
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Enlace para ver y gestionar todas las reservas -->
        <div style="text-align:right; margin-top:20px;">
            <a href="gestionar.php" class="btn-principal"
               style="font-size:9px; padding:10px 24px;">
                Ver todas las reservas
            </a>
        </div>

        <?php else: ?>
            <!-- Mensaje si no hay reservas todavía -->
            <p style="color:var(--blanco-suave); font-size:13px; letter-spacing:1px;">
                No hay reservas registradas todavía.
            </p>
        <?php endif; ?>

    </main>

</div>

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>