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

// Filtros adicionales: rango rápido, fechas concretas y búsqueda por cliente/teléfono/servicio
$rango  = trim($_GET['rango'] ?? 'proximas');
$desde  = trim($_GET['desde'] ?? '');
$hasta  = trim($_GET['hasta'] ?? '');
$buscar = trim($_GET['buscar'] ?? '');

$rangos_validos = ['todas', 'proximas', 'hoy', 'semana', 'mes'];
if (!in_array($rango, $rangos_validos, true)) {
    $rango = 'proximas';
}

// Validamos formato YYYY-MM-DD para evitar valores incorrectos
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
    $desde = '';
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
    $hasta = '';
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
function redirigir_gestionar($mensaje, $tipo, $params_actuales = []) {
    $params = [
        'msg'  => $mensaje,
        'tipo' => $tipo
    ];

    foreach (['filtro', 'rango', 'desde', 'hasta', 'buscar'] as $clave) {
        if (!empty($params_actuales[$clave])) {
            $params[$clave] = $params_actuales[$clave];
        }
    }

    header('Location: gestionar.php?' . http_build_query($params));
    exit;
}

$filtros_actuales = [
    'filtro' => $filtro,
    'rango'  => $rango,
    'desde'  => $desde,
    'hasta'  => $hasta,
    'buscar' => $buscar
];

// -------------------------------------------------------
// Procesamos la acción recibida por GET (confirmar/cancelar)
// -------------------------------------------------------
if (isset($_GET['accion'], $_GET['id'])) {

    // Recogemos y validamos los parámetros
    $accion = trim($_GET['accion'] ?? '');
    $id     = intval($_GET['id'] ?? 0);

    // El id debe ser un número positivo
    if ($id <= 0) {
        redirigir_gestionar('ID de reserva no válido.', 'error', $filtros_actuales);
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
                redirigir_gestionar('Reserva confirmada correctamente.', 'exito', $filtros_actuales);
            } else {
                $stmt->close();
                redirigir_gestionar('La reserva ya estaba confirmada o no existe.', 'error', $filtros_actuales);
            }
        } else {
            $stmt->close();
            redirigir_gestionar('No se pudo confirmar la reserva.', 'error', $filtros_actuales);
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

                redirigir_gestionar('Reserva cancelada correctamente.', 'exito', $filtros_actuales);
            } else {
                $stmt->close();
                redirigir_gestionar('La reserva ya estaba cancelada o no existe.', 'error', $filtros_actuales);
            }
        } else {
            $stmt->close();
            redirigir_gestionar('No se pudo cancelar la reserva.', 'error', $filtros_actuales);
        }

    } else {
        // Acción no reconocida
        redirigir_gestionar('Acción no válida.', 'error', $filtros_actuales);
    }
}

// -------------------------------------------------------
// Consulta de reservas con filtros
// -------------------------------------------------------
$where  = [];
$params = [];
$types  = '';

if (!empty($filtro)) {
    $where[]  = 'r.estado = ?';
    $params[] = $filtro;
    $types   .= 's';
}

// Si no se indican fechas manuales, usamos el rango rápido seleccionado
if (empty($desde) && empty($hasta)) {
    if ($rango === 'proximas') {
        $where[] = "r.fecha >= CURDATE()";
        $where[] = "r.estado != 'cancelada'";
    } elseif ($rango === 'hoy') {
        $where[] = "r.fecha = CURDATE()";
    } elseif ($rango === 'semana') {
        $where[] = "r.fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $where[] = "r.estado != 'cancelada'";
    } elseif ($rango === 'mes') {
        $where[] = "r.fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
        $where[] = "r.estado != 'cancelada'";
    }
}

if (!empty($desde)) {
    $where[]  = 'r.fecha >= ?';
    $params[] = $desde;
    $types   .= 's';
}

if (!empty($hasta)) {
    $where[]  = 'r.fecha <= ?';
    $params[] = $hasta;
    $types   .= 's';
}

if (!empty($buscar)) {
    $where[]  = "CONCAT(u.nombre, ' ', u.apellidos, ' ', u.telefono, ' ', s.nombre) LIKE ?";
    $params[] = '%' . $buscar . '%';
    $types   .= 's';
}

$sql = "SELECT r.id, r.fecha, r.hora, r.estado, r.notas,
               u.nombre, u.apellidos, u.telefono,
               s.nombre AS servicio, s.precio
        FROM reservas r
        JOIN usuarios  u ON r.usuario_id  = u.id
        JOIN servicios s ON r.servicio_id = s.id";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY r.fecha ASC, r.hora ASC';

$stmt = $conexion->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reservas = $stmt->get_result();

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
             FILTROS DE BÚSQUEDA
             ---------------------------------------- -->
        <form method="GET" action="gestionar.php" class="filtros-reservas-admin">

            <div class="campo-filtro">
                <label for="rango">Rango</label>
                <select id="rango" name="rango">
                    <option value="proximas" <?= $rango === 'proximas' ? 'selected' : '' ?>>Próximas citas</option>
                    <option value="hoy" <?= $rango === 'hoy' ? 'selected' : '' ?>>Hoy</option>
                    <option value="semana" <?= $rango === 'semana' ? 'selected' : '' ?>>Próximos 7 días</option>
                    <option value="mes" <?= $rango === 'mes' ? 'selected' : '' ?>>Próximo mes</option>
                    <option value="todas" <?= $rango === 'todas' ? 'selected' : '' ?>>Todas</option>
                </select>
            </div>

            <div class="campo-filtro">
                <label for="filtro">Estado</label>
                <select id="filtro" name="filtro">
                    <option value="" <?= empty($filtro) ? 'selected' : '' ?>>Todos</option>
                    <option value="pendiente" <?= $filtro === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                    <option value="confirmada" <?= $filtro === 'confirmada' ? 'selected' : '' ?>>Confirmadas</option>
                    <option value="cancelada" <?= $filtro === 'cancelada' ? 'selected' : '' ?>>Canceladas</option>
                </select>
            </div>

            <div class="campo-filtro">
                <label for="desde">Desde</label>
                <input type="date" id="desde" name="desde" value="<?= limpiar($desde) ?>">
            </div>

            <div class="campo-filtro">
                <label for="hasta">Hasta</label>
                <input type="date" id="hasta" name="hasta" value="<?= limpiar($hasta) ?>">
            </div>

            <div class="campo-filtro campo-filtro-buscar">
                <label for="buscar">Cliente / teléfono / servicio</label>
                <input type="text" id="buscar" name="buscar"
                       value="<?= limpiar($buscar) ?>"
                       placeholder="Ej: Miguel, 600..., corte">
            </div>

            <div class="campo-filtro campo-filtro-botones">
                <button type="submit" class="btn-secundario">Filtrar</button>
                <a href="gestionar.php?rango=proximas" class="btn-principal">Limpiar</a>
            </div>

        </form>

        <?php
        $query_filtros_array = [
            'filtro' => $filtro,
            'rango'  => $rango,
            'desde'  => $desde,
            'hasta'  => $hasta,
            'buscar' => $buscar
        ];
        $query_filtros_array = array_filter($query_filtros_array, function($valor) {
            return $valor !== '';
        });
        $query_filtros = http_build_query($query_filtros_array);
        ?>

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
                            <a href="gestionar.php?accion=confirmar&id=<?= intval($r['id']) ?>&<?= $query_filtros ?>"
                               class="btn-principal"
                               style="font-size:9px; padding:6px 12px; margin-right:4px;">
                                Confirmar
                            </a>
                            <?php endif; ?>

                            <?php if ($r['estado'] !== 'cancelada'): ?>
                            <!-- Botón cancelar (solo si no está ya cancelada) -->
                            <a href="gestionar.php?accion=cancelar&id=<?= intval($r['id']) ?>&<?= $query_filtros ?>"
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
                No hay reservas que coincidan con los filtros seleccionados.
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
