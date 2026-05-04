<?php
// =====================================================
// ARCHIVO: admin/dashboard.php
// Descripción: Panel principal del administrador.
//              Muestra estadísticas, calendario semanal
//              con las citas de la BD y reservas recientes.
//              Acceso restringido: solo administradores.
// =====================================================

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

requiere_admin();

$titulo_pagina = 'Panel de administración';
$admin_nombre  = $_SESSION[ADMIN_SESSION_KEY]['nombre'];

// -------------------------------------------------------
// Estadísticas generales
// -------------------------------------------------------
$total_pendientes = $conexion->query(
    "SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'"
)->fetch_assoc()['total'];

$total_confirmadas = $conexion->query(
    "SELECT COUNT(*) as total FROM reservas WHERE estado = 'confirmada'"
)->fetch_assoc()['total'];

$total_clientes = $conexion->query(
    "SELECT COUNT(*) as total FROM usuarios"
)->fetch_assoc()['total'];

$total_hoy = $conexion->query(
    "SELECT COUNT(*) as total FROM reservas
     WHERE fecha = CURDATE() AND estado != 'cancelada'"
)->fetch_assoc()['total'];

// -------------------------------------------------------
// Semana actual para el calendario
// Lunes a Sábado de la semana en curso
// -------------------------------------------------------
$hoy           = date('Y-m-d');
$lunes         = date('Y-m-d', strtotime('monday this week'));
$sabado        = date('Y-m-d', strtotime('saturday this week'));

// Obtenemos todas las reservas de esta semana con datos del cliente y servicio
$reservas_semana = $conexion->query(
    "SELECT r.fecha, r.hora, r.estado,
            u.nombre, u.apellidos,
            s.nombre AS servicio, s.duracion
     FROM reservas r
     JOIN usuarios  u ON r.usuario_id  = u.id
     JOIN servicios s ON r.servicio_id = s.id
     WHERE r.fecha BETWEEN '{$lunes}' AND '{$sabado}'
     AND r.estado != 'cancelada'
     ORDER BY r.fecha ASC, r.hora ASC"
);

// Agrupamos las reservas por fecha para pintarlas en el calendario
$citas_por_dia = [];
while ($c = $reservas_semana->fetch_assoc()) {
    $citas_por_dia[$c['fecha']][] = $c;
}

// -------------------------------------------------------
// Próximas 10 reservas
// -------------------------------------------------------
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

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="logo-admin">Dioni</div>
        <ul>
            <li><a href="dashboard.php" class="activo">Dashboard</a></li>
            <li><a href="gestionar.php">Reservas</a></li>
            <li><a href="nueva-reserva.php">Nueva reserva</a></li>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="admin-contenido">

        <h1>Bienvenido, <?= limpiar($admin_nombre) ?></h1>
        <div class="linea-deco"></div>

        <!-- TARJETAS DE ESTADÍSTICAS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-numero"><?= $total_pendientes ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-numero"><?= $total_confirmadas ?></div>
                <div class="stat-label">Confirmadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-numero"><?= $total_hoy ?></div>
                <div class="stat-label">Citas hoy</div>
            </div>
            <div class="stat-card">
                <div class="stat-numero"><?= $total_clientes ?></div>
                <div class="stat-label">Clientes</div>
            </div>
        </div>

        <!-- ----------------------------------------
             CALENDARIO SEMANAL
             ---------------------------------------- -->
        <h2 style="font-size:20px; letter-spacing:4px;
                   text-transform:uppercase; margin-bottom:10px;">
            Agenda esta semana
        </h2>
        <div class="linea-deco" style="margin-bottom:25px;"></div>

        <!-- Fechas de la semana -->
        <p style="font-size:10px; letter-spacing:2px; color:var(--blanco-suave);
                  text-transform:uppercase; margin-bottom:20px;">
            <?= date('d/m', strtotime($lunes)) ?> — <?= date('d/m/Y', strtotime($sabado)) ?>
        </p>

        <!-- Grid semanal: 6 columnas (Lun-Sáb) -->
        <div class="semana-grid">
            <?php
            // Nombres de los días
            $nombres_dias = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            $fecha_actual = $lunes;

            for ($i = 0; $i < 6; $i++):
                $es_hoy   = ($fecha_actual === $hoy);
                $dia_num  = date('d', strtotime($fecha_actual));
                $citas    = $citas_por_dia[$fecha_actual] ?? [];
            ?>
            <!-- Columna de día -->
            <div class="semana-dia <?= $es_hoy ? 'semana-dia-hoy' : '' ?>">

                <!-- Cabecera del día -->
                <div class="semana-dia-header">
                    <span class="semana-dia-nombre"><?= $nombres_dias[$i] ?></span>
                    <span class="semana-dia-numero <?= $es_hoy ? 'semana-numero-hoy' : '' ?>">
                        <?= $dia_num ?>
                    </span>
                </div>

                <!-- Citas del día -->
                <div class="semana-dia-citas">
                    <?php if (!empty($citas)): ?>
                        <?php foreach ($citas as $cita): ?>
                        <div class="semana-cita <?= $cita['estado'] === 'confirmada' ? 'semana-cita-confirmada' : 'semana-cita-pendiente' ?>">
                            <!-- Hora de la cita -->
                            <span class="semana-cita-hora">
                                <?= substr($cita['hora'], 0, 5) ?>
                            </span>
                            <!-- Nombre del cliente -->
                            <span class="semana-cita-cliente">
                                <?= limpiar($cita['nombre']) ?> <?= limpiar($cita['apellidos']) ?>
                            </span>
                            <!-- Servicio -->
                            <span class="semana-cita-servicio">
                                <?= limpiar($cita['servicio']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Sin citas ese día -->
                        <p class="semana-sin-citas">Sin citas</p>
                    <?php endif; ?>
                </div>

            </div>
            <?php
                $fecha_actual = date('Y-m-d', strtotime($fecha_actual . ' +1 day'));
            endfor;
            ?>
        </div>

        <!-- ----------------------------------------
             TABLA DE PRÓXIMAS RESERVAS
             ---------------------------------------- -->
        <h2 style="font-size:20px; letter-spacing:4px;
                   text-transform:uppercase; margin-bottom:10px;
                   margin-top:50px;">
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
                        <td><?= $r['id'] ?></td>
                        <td><?= limpiar($r['nombre']) . ' ' . limpiar($r['apellidos']) ?></td>
                        <td><?= limpiar($r['telefono']) ?></td>
                        <td><?= limpiar($r['servicio']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                        <td><?= limpiar($r['hora']) ?></td>
                        <td>
                            <span class="badge badge-<?= $r['estado'] ?>">
                                <?= ucfirst($r['estado']) ?>
                            </span>
                        </td>
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

        <div style="text-align:right; margin-top:20px;">
            <a href="gestionar.php" class="btn-principal"
               style="font-size:9px; padding:10px 24px;">
                Ver todas las reservas
            </a>
        </div>

        <?php else: ?>
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