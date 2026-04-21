<?php
// =====================================================
// ARCHIVO: admin/gestionar.php
// Descripción: Página de gestión completa de reservas.
//              Permite al administrador confirmar,
//              cancelar y filtrar todas las reservas.
//              Acceso restringido: solo administradores.
// =====================================================

// Incluimos configuración, conexión y funciones
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// -- Control de sesión --
// Si no hay sesión de admin activa redirige al login
requiere_admin();

$titulo_pagina = 'Gestión de reservas';

// Mensaje de confirmación o error tras una acción
$mensaje = '';
$tipo    = '';  // 'exito' o 'error'

// -------------------------------------------------------
// Procesamos la acción recibida por GET (confirmar/cancelar)
// -------------------------------------------------------
if (isset($_GET['accion']) && isset($_GET['id'])) {

    // Recogemos y validamos los parámetros
    $accion = trim($_GET['accion'] ?? '');
    $id     = intval($_GET['id']   ?? 0);

    // El id debe ser un número positivo
    if ($id > 0) {

        // Comprobamos qué acción se quiere realizar
        if ($accion === 'confirmar') {

            // Cambiamos el estado de la reserva a 'confirmada'
            $stmt = $conexion->prepare(
                "UPDATE reservas SET estado = 'confirmada' WHERE id = ?"
            );
            $stmt->bind_param('i', $id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $mensaje = 'Reserva confirmada correctamente.';
                $tipo    = 'exito';
            } else {
                $mensaje = 'No se pudo confirmar la reserva.';
                $tipo    = 'error';
            }

            $stmt->close();

        } elseif ($accion === 'cancelar') {

            // Cambiamos el estado de la reserva a 'cancelada'
            $stmt = $conexion->prepare(
                "UPDATE reservas SET estado = 'cancelada' WHERE id = ?"
            );
            $stmt->bind_param('i', $id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $mensaje = 'Reserva cancelada correctamente.';
                $tipo    = 'exito';
            } else {
                $mensaje = 'No se pudo cancelar la reserva.';
                $tipo    = 'error';
            }

            $stmt->close();

        } else {
            // Acción no reconocida
            $mensaje = 'Acción no válida.';
            $tipo    = 'error';
        }
    }
}

// -------------------------------------------------------
// Consulta de reservas con filtro de estado opcional
// -------------------------------------------------------

// Recogemos el filtro de estado si se ha seleccionado
$filtro = trim($_GET['filtro'] ?? '');

// Construimos la consulta según el filtro
if (!empty($filtro) && in_array($filtro, ['pendiente', 'confirmada', 'cancelada'])) {
    // Filtramos por estado concreto
    $stmt = $conexion->prepare(
        "SELECT r.id, r.fecha, r.hora, r.estado, r.notas,
                u.nombre, u.apellidos, u.telefono,
                s.nombre AS servicio, s.precio
         FROM reservas r
         JOIN usuarios  u ON r.usuario_id  = u.id
         JOIN servicios s ON r.servicio_id = s.id
         WHERE r.estado = ?
         ORDER BY r.fecha ASC, r.hora ASC"
    );
    $stmt->bind_param('s', $filtro);
    $stmt->execute();
    $reservas = $stmt->get_result();
    $stmt->close();

} else {
    // Sin filtro: traemos todas las reservas
    $reservas = $conexion->query(
        "SELECT r.id, r.fecha, r.hora, r.estado, r.notas,
                u.nombre, u.apellidos, u.telefono,
                s.nombre AS servicio, s.precio
         FROM reservas r
         JOIN usuarios  u ON r.usuario_id  = u.id
         JOIN servicios s ON r.servicio_id = s.id
         ORDER BY r.fecha ASC, r.hora ASC"
    );
}

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

        <div class="logo-admin">Dioni</div>

        <ul>
            <li>
                <a href="dashboard.php">Dashboard</a>
            </li>
            <li>
                <a href="gestionar.php" class="activo">Reservas</a>
            </li>
            <li>
                <a href="logout.php">Cerrar sesión</a>
            </li>
        </ul>

    </aside>

    <!-- ============================================
         CONTENIDO PRINCIPAL
         ============================================ -->
    <main class="admin-contenido">

        <h1>Gestión de reservas</h1>
        <div class="linea-deco"></div>

        <!-- Mensaje de éxito o error tras una acción -->
        <?php if (!empty($mensaje)): ?>
            <div class="aviso aviso-<?= $tipo === 'exito' ? 'exito' : 'error' ?>">
                <?= limpiar($mensaje) ?>
            </div>
        <?php endif; ?>

        <!-- ----------------------------------------
             FILTROS DE ESTADO
             ---------------------------------------- -->
        <div style="margin-bottom:30px; display:flex; gap:10px; flex-wrap:wrap;">

            <!-- Botón todas las reservas -->
            <a href="gestionar.php"
               class="<?= empty($filtro) ? 'btn-secundario' : 'btn-principal' ?>"
               style="font-size:9px; padding:8px 20px;">
                Todas
            </a>

            <!-- Botón filtro pendientes -->
            <a href="gestionar.php?filtro=pendiente"
               class="<?= $filtro === 'pendiente' ? 'btn-secundario' : 'btn-principal' ?>"
               style="font-size:9px; padding:8px 20px;">
                Pendientes
            </a>

            <!-- Botón filtro confirmadas -->
            <a href="gestionar.php?filtro=confirmada"
               class="<?= $filtro === 'confirmada' ? 'btn-secundario' : 'btn-principal' ?>"
               style="font-size:9px; padding:8px 20px;">
                Confirmadas
            </a>

            <!-- Botón filtro canceladas -->
            <a href="gestionar.php?filtro=cancelada"
               class="<?= $filtro === 'cancelada' ? 'btn-secundario' : 'btn-principal' ?>"
               style="font-size:9px; padding:8px 20px;">
                Canceladas
            </a>

        </div>

        <!-- ----------------------------------------
             TABLA DE TODAS LAS RESERVAS
             ---------------------------------------- -->
        <?php if ($reservas && $reservas->num_rows > 0): ?>
        <div style="overflow-x:auto;">
            <table class="tabla-reservas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Servicio</th>
                        <th>Precio</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Notas</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = $reservas->fetch_assoc()): ?>
                    <tr>
                        <!-- ID -->
                        <td><?= $r['id'] ?></td>

                        <!-- Nombre completo del cliente -->
                        <td><?= limpiar($r['nombre']) . ' ' . limpiar($r['apellidos']) ?></td>

                        <!-- Teléfono -->
                        <td><?= limpiar($r['telefono']) ?></td>

                        <!-- Servicio -->
                        <td><?= limpiar($r['servicio']) ?></td>

                        <!-- Precio -->
                        <td><?= number_format($r['precio'], 2) ?>€</td>

                        <!-- Fecha formateada -->
                        <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>

                        <!-- Hora -->
                        <td><?= limpiar($r['hora']) ?></td>

                        <!-- Notas del cliente -->
                        <td style="max-width:150px; font-size:11px;">
                            <?= !empty($r['notas']) ? limpiar($r['notas']) : '—' ?>
                        </td>

                        <!-- Badge de estado -->
                        <td>
                            <span class="badge badge-<?= $r['estado'] ?>">
                                <?= ucfirst($r['estado']) ?>
                            </span>
                        </td>

                        <!-- Botones de acción -->
                        <td style="white-space:nowrap;">

                            <?php if ($r['estado'] !== 'confirmada'): ?>
                            <!-- Botón confirmar (solo si no está ya confirmada) -->
                            <a href="gestionar.php?accion=confirmar&id=<?= $r['id'] ?>&filtro=<?= $filtro ?>"
                               class="btn-principal"
                               style="font-size:9px; padding:6px 12px; margin-right:4px;">
                                Confirmar
                            </a>
                            <?php endif; ?>

                            <?php if ($r['estado'] !== 'cancelada'): ?>
                            <!-- Botón cancelar (solo si no está ya cancelada) -->
                            <a href="gestionar.php?accion=cancelar&id=<?= $r['id'] ?>&filtro=<?= $filtro ?>"
                               class="btn-principal"
                               style="font-size:9px; padding:6px 12px;
                                      color:#ff6b6b; border-color:#ff6b6b;">
                                Cancelar
                            </a>
                            <?php endif; ?>

                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
            <!-- Mensaje si no hay reservas con ese filtro -->
            <p style="color:var(--blanco-suave); font-size:13px; letter-spacing:1px;">
                No hay reservas <?= !empty($filtro) ? $filtro . 's' : '' ?> registradas.
            </p>
        <?php endif; ?>

    </main>

</div>

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>