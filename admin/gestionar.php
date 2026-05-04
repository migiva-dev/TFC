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

// Estados permitidos para evitar valores no válidos
$estados_validos = ['pendiente', 'confirmada', 'cancelada'];

// Recogemos el filtro de estado si se ha seleccionado
$filtro = trim($_GET['filtro'] ?? '');
if (!in_array($filtro, $estados_validos, true)) {
    $filtro = '';
}

// Mensaje de confirmación o error tras una acción
$mensaje = trim($_GET['msg'] ?? '');
$tipo    = trim($_GET['tipo'] ?? '');

if (!in_array($tipo, ['exito', 'error'], true)) {
    $tipo = '';
}

// -------------------------------------------------------
// Función auxiliar para redirigir después de una acción
// Así evitamos que al refrescar la página se repita la acción
// -------------------------------------------------------
function redirigir_gestionar($mensaje, $tipo, $filtro = '') {
    $params = [
        'msg'  => $mensaje,
        'tipo' => $tipo
    ];

    if (!empty($filtro)) {
        $params['filtro'] = $filtro;
    }

    header('Location: gestionar.php?' . http_build_query($params));
    exit;
}

// -------------------------------------------------------
// Procesamos la acción recibida por GET (confirmar/cancelar)
// -------------------------------------------------------
if (isset($_GET['accion'], $_GET['id'])) {

    // Recogemos y validamos los parámetros
    $accion = trim($_GET['accion'] ?? '');
    $id     = intval($_GET['id'] ?? 0);

    // El id debe ser un número positivo
    if ($id <= 0) {
        redirigir_gestionar('ID de reserva no válido.', 'error', $filtro);
    }

    if ($accion === 'confirmar') {

        // Cambiamos el estado de la reserva a 'confirmada'
        $stmt = $conexion->prepare(
            "UPDATE reservas
             SET estado = 'confirmada'
             WHERE id = ? AND estado != 'confirmada'"
        );
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                redirigir_gestionar('Reserva confirmada correctamente.', 'exito', $filtro);
            } else {
                $stmt->close();
                redirigir_gestionar('La reserva ya estaba confirmada o no existe.', 'error', $filtro);
            }
        } else {
            $stmt->close();
            redirigir_gestionar('No se pudo confirmar la reserva.', 'error', $filtro);
        }

    } elseif ($accion === 'cancelar') {

        // Antes de cancelar, intentamos obtener el ID del evento de Google Calendar si existe la columna
        // Esto no rompe la página si no tienes configurada la función google_cancelar_evento().
        $google_event_id = null;

        $stmt_evento = $conexion->prepare(
            "SELECT google_event_id FROM reservas WHERE id = ? LIMIT 1"
        );

        if ($stmt_evento) {
            $stmt_evento->bind_param('i', $id);
            $stmt_evento->execute();
            $stmt_evento->bind_result($google_event_id);
            $stmt_evento->fetch();
            $stmt_evento->close();
        }

        // Cambiamos el estado de la reserva a 'cancelada'
        $stmt = $conexion->prepare(
            "UPDATE reservas
             SET estado = 'cancelada'
             WHERE id = ? AND estado != 'cancelada'"
        );
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();

                // Si existe la función y hay evento asociado, intentamos cancelarlo también en Google Calendar
                if (!empty($google_event_id) && function_exists('google_cancelar_evento')) {
                    google_cancelar_evento($google_event_id);
                }

                redirigir_gestionar('Reserva cancelada correctamente.', 'exito', $filtro);
            } else {
                $stmt->close();
                redirigir_gestionar('La reserva ya estaba cancelada o no existe.', 'error', $filtro);
            }
        } else {
            $stmt->close();
            redirigir_gestionar('No se pudo cancelar la reserva.', 'error', $filtro);
        }

    } else {
        // Acción no reconocida
        redirigir_gestionar('Acción no válida.', 'error', $filtro);
    }
}

// -------------------------------------------------------
// Consulta de reservas con filtro de estado opcional
// -------------------------------------------------------

// Construimos la consulta según el filtro
if (!empty($filtro)) {
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
                <!-- Enlace para crear una nueva reserva manualmente -->
                <a href="nueva-reserva.php">Nueva reserva</a>
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
                        <td><?= intval($r['id']) ?></td>

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
                        <td><?= limpiar(substr($r['hora'], 0, 5)) ?></td>

                        <!-- Notas del cliente -->
                        <td style="max-width:150px; font-size:11px;">
                            <?= !empty($r['notas']) ? limpiar($r['notas']) : '—' ?>
                        </td>

                        <!-- Badge de estado -->
                        <td>
                            <span class="badge badge-<?= limpiar($r['estado']) ?>">
                                <?= ucfirst(limpiar($r['estado'])) ?>
                            </span>
                        </td>

                        <!-- Botones de acción -->
                        <td style="white-space:nowrap;">

                            <?php if ($r['estado'] !== 'confirmada'): ?>
                            <!-- Botón confirmar (solo si no está ya confirmada) -->
                            <a href="gestionar.php?accion=confirmar&id=<?= intval($r['id']) ?>&filtro=<?= urlencode($filtro) ?>"
                               class="btn-principal"
                               style="font-size:9px; padding:6px 12px; margin-right:4px;">
                                Confirmar
                            </a>
                            <?php endif; ?>

                            <?php if ($r['estado'] !== 'cancelada'): ?>
                            <!-- Botón cancelar (solo si no está ya cancelada) -->
                            <a href="gestionar.php?accion=cancelar&id=<?= intval($r['id']) ?>&filtro=<?= urlencode($filtro) ?>"
                               class="btn-principal"
                               style="font-size:9px; padding:6px 12px;
                                      color:#ff6b6b; border-color:#ff6b6b;"
                               onclick="return confirm('¿Seguro que quieres cancelar esta reserva?');">
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
                No hay reservas <?= !empty($filtro) ? limpiar($filtro) . 's' : '' ?> registradas.
            </p>
        <?php endif; ?>

    </main>

</div>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>
