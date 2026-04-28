<?php
// =====================================================
// ARCHIVO: admin/nueva-reserva.php
// Descripción: Permite al administrador crear una
//              reserva manualmente para cualquier
//              cliente registrado o con nombre libre.
//              Acceso restringido: solo administradores.
// =====================================================

// Incluimos configuración, conexión y funciones
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// -- Control de sesión --
// Si no hay sesión de admin activa redirige al login
requiere_admin();

$titulo_pagina = 'Nueva reserva';

// -- Obtenemos todos los servicios disponibles --
$servicios = $conexion->query("SELECT * FROM servicios ORDER BY precio ASC");

// -- Obtenemos todos los clientes registrados --
$clientes = $conexion->query(
    "SELECT id, nombre, apellidos, telefono FROM usuarios ORDER BY nombre ASC"
);

// -- Horas disponibles de 9:00 a 19:30 cada 30 minutos --
$horas_disponibles = [];
for ($h = 9; $h < 20; $h++) {
    $horas_disponibles[] = sprintf('%02d:00', $h);
    $horas_disponibles[] = sprintf('%02d:30', $h);
}

$error  = '';
$exito  = '';

// -------------------------------------------------------
// Procesamos el formulario cuando se envía (método POST)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recogemos y limpiamos los datos del formulario
    $usuario_id  = intval($_POST['usuario_id']  ?? 0);
    $servicio_id = intval($_POST['servicio_id'] ?? 0);
    $fecha       = trim($_POST['fecha']         ?? '');
    $hora        = trim($_POST['hora']          ?? '');
    $notas       = trim($_POST['notas']         ?? '');

    // -- Validaciones --
    if (empty($usuario_id) || empty($servicio_id) || empty($fecha) || empty($hora)) {
        $error = 'Por favor, rellena todos los campos obligatorios.';

    // La fecha no puede ser anterior a hoy
    } elseif ($fecha < date('Y-m-d')) {
        $error = 'La fecha no puede ser anterior a hoy.';

    // No se puede reservar en domingo
    } elseif (date('w', strtotime($fecha)) == 0) {
        $error = 'Los domingos estamos cerrados.';

    } else {

        // Comprobamos que no haya ya una reserva en esa fecha y hora
        $stmt = $conexion->prepare(
            "SELECT id FROM reservas
             WHERE fecha = ? AND hora = ?
             AND estado != 'cancelada'"
        );
        $stmt->bind_param('ss', $fecha, $hora);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Ese horario ya está ocupado. Elige otra hora.';
            $stmt->close();

        } else {
            $stmt->close();

            // Insertamos la reserva directamente como confirmada
            // ya que la crea el propio administrador
            $stmt = $conexion->prepare(
                "INSERT INTO reservas
                 (usuario_id, servicio_id, fecha, hora, estado, notas)
                 VALUES (?, ?, ?, ?, 'confirmada', ?)"
            );
            $stmt->bind_param('iisss', $usuario_id, $servicio_id, $fecha, $hora, $notas);

            if ($stmt->execute()) {
                $exito = ' Reserva creada correctamente para el ' .
                         date('d/m/Y', strtotime($fecha)) . ' a las ' . $hora . '.';
            } else {
                $error = 'Error al crear la reserva. Inténtalo de nuevo.';
            }

            $stmt->close();
        }
    }
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
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="gestionar.php">Reservas</a></li>
            <li>
                <a href="nueva-reserva.php" class="activo">
                    Nueva reserva
                </a>
            </li>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>

    </aside>

    <!-- ============================================
         CONTENIDO PRINCIPAL
         ============================================ -->
    <main class="admin-contenido">

        <h1>Nueva reserva</h1>
        <div class="linea-deco"></div>

        <!-- Mensaje de éxito -->
        <?php if (!empty($exito)): ?>
            <div class="aviso aviso-exito"><?= limpiar($exito) ?></div>
        <?php endif; ?>

        <!-- Mensaje de error -->
        <?php if (!empty($error)): ?>
            <div class="aviso aviso-error"><?= limpiar($error) ?></div>
        <?php endif; ?>

        <!-- Formulario de nueva reserva -->
        <div class="formulario-contenedor" style="max-width:560px; margin:0;">
            <form method="POST" action="nueva-reserva.php">

                <!-- Cliente registrado -->
                <div class="campo-grupo">
                    <label for="usuario_id">Cliente *</label>
                    <select id="usuario_id" name="usuario_id" required>
                        <option value="">— Selecciona un cliente —</option>
                        <?php
                        // Rellenamos el select con los clientes registrados
                        if ($clientes && $clientes->num_rows > 0):
                            while ($c = $clientes->fetch_assoc()):
                        ?>
                        <option value="<?= $c['id'] ?>"
                            <?= (isset($_POST['usuario_id']) && $_POST['usuario_id'] == $c['id']) ? 'selected' : '' ?>>
                            <?= limpiar($c['nombre']) ?>
                            <?= limpiar($c['apellidos']) ?>
                            — <?= limpiar($c['telefono']) ?>
                        </option>
                        <?php
                            endwhile;
                        endif;
                        ?>
                    </select>
                </div>

                <!-- Servicio -->
                <div class="campo-grupo">
                    <label for="servicio_id">Servicio *</label>
                    <select id="servicio_id" name="servicio_id" required>
                        <option value="">— Selecciona un servicio —</option>
                        <?php
                        // Rellenamos el select con los servicios
                        if ($servicios && $servicios->num_rows > 0):
                            while ($s = $servicios->fetch_assoc()):
                        ?>
                        <option value="<?= $s['id'] ?>"
                            <?= (isset($_POST['servicio_id']) && $_POST['servicio_id'] == $s['id']) ? 'selected' : '' ?>>
                            <?= limpiar($s['nombre']) ?> —
                            <?= number_format($s['precio'], 2) ?>€
                            (<?= $s['duracion'] ?> min)
                        </option>
                        <?php
                            endwhile;
                        endif;
                        ?>
                    </select>
                </div>

                <!-- Fecha -->
                <div class="campo-grupo">
                    <label for="fecha">Fecha *</label>
                    <input type="date" id="fecha" name="fecha"
                           min="<?= date('Y-m-d') ?>"
                           value="<?= limpiar($_POST['fecha'] ?? '') ?>"
                           required>
                </div>

                <!-- Hora -->
                <div class="campo-grupo">
                    <label for="hora">Hora *</label>
                    <select id="hora" name="hora" required>
                        <option value="">— Selecciona una hora —</option>
                        <?php foreach ($horas_disponibles as $hora_opcion): ?>
                        <option value="<?= $hora_opcion ?>"
                            <?= (isset($_POST['hora']) && $_POST['hora'] === $hora_opcion) ? 'selected' : '' ?>>
                            <?= $hora_opcion ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Notas opcionales -->
                <div class="campo-grupo">
                    <label for="notas">Notas (opcional)</label>
                    <textarea id="notas" name="notas"
                              rows="3"
                              placeholder="Indicaciones especiales..."
                              style="resize:vertical;"><?= limpiar($_POST['notas'] ?? '') ?></textarea>
                </div>

                <!-- Info: la reserva se crea como confirmada -->
                <p style="font-size:10px; letter-spacing:2px;
                          color:var(--blanco-suave); margin-bottom:20px;
                          text-transform:uppercase;">
                     Las reservas creadas por el admin se confirman automáticamente
                </p>

                <!-- Botones -->
                <button type="submit" class="btn-form">
                    Crear reserva
                </button>

                <div style="text-align:center; margin-top:15px;">
                    <a href="gestionar.php"
                       style="font-size:10px; letter-spacing:2px;
                              color:var(--blanco-suave);">
                        ← Volver a reservas
                    </a>
                </div>

            </form>
        </div>

    </main>

</div>

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>