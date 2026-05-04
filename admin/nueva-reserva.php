<?php
// =====================================================
// ARCHIVO: admin/nueva-reserva.php
// Descripción: Permite al administrador crear una
//              reserva manualmente para cualquier
//              cliente registrado.
//              Acceso restringido: solo administradores.
// =====================================================

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

requiere_admin();

$titulo_pagina = 'Nueva reserva';

// -- Servicios y clientes --
$servicios = $conexion->query("SELECT * FROM servicios ORDER BY precio ASC");
$clientes  = $conexion->query(
    "SELECT id, nombre, apellidos, telefono FROM usuarios ORDER BY nombre ASC"
);

// -- Horas disponibles de 9:00 a 19:30 cada 30 minutos --
$horas_disponibles = [];
for ($h = 9; $h < 20; $h++) {
    $horas_disponibles[] = sprintf('%02d:00', $h);
    $horas_disponibles[] = sprintf('%02d:30', $h);
}

$error = '';
$exito = '';

// -------------------------------------------------------
// Disponibilidad por día del mes para el calendario
// -------------------------------------------------------
$total_horas_dia = 22;
$mes_actual      = intval($_GET['mes']  ?? date('n'));
$anio_actual     = intval($_GET['anio'] ?? date('Y'));

if ($mes_actual < 1)  { $mes_actual = 12; $anio_actual--; }
if ($mes_actual > 12) { $mes_actual = 1;  $anio_actual++; }

$stmt = $conexion->prepare(
    "SELECT fecha, COUNT(*) as total
     FROM reservas
     WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
     AND estado != 'cancelada'
     GROUP BY fecha"
);
$stmt->bind_param('ii', $mes_actual, $anio_actual);
$stmt->execute();
$resultado_mes = $stmt->get_result();

$reservas_por_dia = [];
while ($fila = $resultado_mes->fetch_assoc()) {
    $dia = intval(date('j', strtotime($fila['fecha'])));
    $reservas_por_dia[$dia] = intval($fila['total']);
}
$stmt->close();

// -- Horas ocupadas para la fecha seleccionada --
$fecha_consulta = $_POST['fecha'] ?? $_GET['fecha'] ?? date('Y-m-d');
$horas_ocupadas = [];

$stmt = $conexion->prepare(
    "SELECT hora FROM reservas
     WHERE fecha = ? AND estado != 'cancelada'"
);
$stmt->bind_param('s', $fecha_consulta);
$stmt->execute();
$resultado_horas = $stmt->get_result();
while ($fila = $resultado_horas->fetch_assoc()) {
    $horas_ocupadas[] = substr($fila['hora'], 0, 5);
}
$stmt->close();

// -------------------------------------------------------
// Procesamos el formulario
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['consultar_horas'])) {
        // Solo actualizamos las horas, no procesamos reserva
    } else {

        $usuario_id  = intval($_POST['usuario_id']  ?? 0);
        $servicio_id = intval($_POST['servicio_id'] ?? 0);
        $fecha       = trim($_POST['fecha']         ?? '');
        $hora        = trim($_POST['hora']          ?? '');
        $notas       = trim($_POST['notas']         ?? '');

        if (empty($usuario_id) || empty($servicio_id) || empty($fecha) || empty($hora)) {
            $error = 'Por favor, rellena todos los campos obligatorios.';
        } elseif ($fecha < date('Y-m-d')) {
            $error = 'La fecha no puede ser anterior a hoy.';
        } elseif (date('w', strtotime($fecha)) == 0) {
            $error = 'Los domingos estamos cerrados.';
        } elseif (in_array($hora, $horas_ocupadas)) {
            $error = 'Esa hora ya está ocupada. Elige otra.';
        } else {

            $stmt = $conexion->prepare(
                "SELECT id FROM reservas
                 WHERE fecha = ? AND hora = ? AND estado != 'cancelada'"
            );
            $stmt->bind_param('ss', $fecha, $hora);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'Ese horario ya está ocupado. Elige otra hora.';
                $stmt->close();
            } else {
                $stmt->close();

                $stmt = $conexion->prepare(
                    "INSERT INTO reservas
                     (usuario_id, servicio_id, fecha, hora, estado, notas)
                     VALUES (?, ?, ?, ?, 'confirmada', ?)"
                );
                $stmt->bind_param('iisss', $usuario_id, $servicio_id, $fecha, $hora, $notas);

                if ($stmt->execute()) {
                    $exito = '✅ Reserva creada correctamente para el ' .
                             date('d/m/Y', strtotime($fecha)) . ' a las ' . $hora . '.';
                } else {
                    $error = 'Error al crear la reserva. Inténtalo de nuevo.';
                }
                $stmt->close();
            }
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="logo-admin">Dioni</div>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="gestionar.php">Reservas</a></li>
            <li><a href="nueva-reserva.php" class="activo">Nueva reserva</a></li>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="admin-contenido">

        <h1>Nueva reserva</h1>
        <div class="linea-deco"></div>

        <?php if (!empty($exito)): ?>
            <div class="aviso aviso-exito"><?= limpiar($exito) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="aviso aviso-error"><?= limpiar($error) ?></div>
        <?php endif; ?>

        <!-- Formulario centrado con dos columnas: calendario + campos -->
        <div class="nueva-reserva-grid">

            <!-- COLUMNA IZQUIERDA: Calendario visual -->
            <div class="nueva-reserva-calendario">

                <div class="calendario">

                    <!-- Cabecera del calendario -->
                    <div class="calendario-header">
                        <a href="nueva-reserva.php?mes=<?= $mes_actual - 1 ?>&anio=<?= $anio_actual ?>"
                           class="calendario-nav">&#8592;</a>
                        <h3>
                            <?php
                            $meses = [
                                1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
                                5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
                                9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
                            ];
                            echo $meses[$mes_actual] . ' ' . $anio_actual;
                            ?>
                        </h3>
                        <a href="nueva-reserva.php?mes=<?= $mes_actual + 1 ?>&anio=<?= $anio_actual ?>"
                           class="calendario-nav">&#8594;</a>
                    </div>

                    <!-- Días de la semana -->
                    <div class="calendario-dias-semana">
                        <span>Lun</span><span>Mar</span><span>Mié</span>
                        <span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                    </div>

                    <!-- Grid de días -->
                    <div class="calendario-grid">
                        <?php
                        $primer_dia  = date('N', mktime(0,0,0,$mes_actual,1,$anio_actual));
                        $dias_en_mes = date('t', mktime(0,0,0,$mes_actual,1,$anio_actual));
                        $hoy_cal     = date('Y-m-d');

                        for ($i = 1; $i < $primer_dia; $i++) {
                            echo '<div class="calendario-dia vacio"></div>';
                        }

                        for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
                            $fecha_dia  = sprintf('%04d-%02d-%02d', $anio_actual, $mes_actual, $dia);
                            $dia_semana = date('w', strtotime($fecha_dia));

                            if ($fecha_dia < $hoy_cal) {
                                $clase = 'pasado';
                            } elseif ($dia_semana == 0) {
                                $clase = 'domingo';
                            } else {
                                $reservas_hoy = $reservas_por_dia[$dia] ?? 0;
                                $pct = ($reservas_hoy / $total_horas_dia) * 100;
                                if ($pct >= 100)      $clase = 'completo';
                                elseif ($pct >= 70)   $clase = 'disponible-poco';
                                elseif ($pct >= 40)   $clase = 'disponible-medio';
                                else                  $clase = 'disponible-alto';
                            }

                            $fecha_sel = $_POST['fecha'] ?? $_GET['fecha'] ?? '';
                            if ($fecha_dia === $fecha_sel) $clase .= ' seleccionado';

                            if (!in_array(trim($clase), ['pasado','domingo','completo'])) {
                                echo "<div class=\"calendario-dia {$clase}\"
                                           data-fecha=\"{$fecha_dia}\"
                                           onclick=\"seleccionarFecha('{$fecha_dia}')\">{$dia}</div>";
                            } else {
                                echo "<div class=\"calendario-dia {$clase}\">{$dia}</div>";
                            }
                        }
                        ?>
                    </div>

                    <!-- Leyenda -->
                    <div class="calendario-leyenda">
                        <div class="leyenda-item">
                            <div class="leyenda-punto verde"></div><span>Disponible</span>
                        </div>
                        <div class="leyenda-item">
                            <div class="leyenda-punto amarillo"></div><span>Algunas horas</span>
                        </div>
                        <div class="leyenda-item">
                            <div class="leyenda-punto naranja"></div><span>Pocas horas</span>
                        </div>
                        <div class="leyenda-item">
                            <div class="leyenda-punto rojo"></div><span>Completo</span>
                        </div>
                    </div>

                </div>

            </div>

            <!-- COLUMNA DERECHA: Formulario -->
            <div class="nueva-reserva-form">
                <form method="POST" action="nueva-reserva.php?mes=<?= $mes_actual ?>&anio=<?= $anio_actual ?>">

                    <!-- Cliente -->
                    <div class="campo-grupo">
                        <label for="usuario_id">Cliente *</label>
                        <select id="usuario_id" name="usuario_id" required>
                            <option value="">— Selecciona un cliente —</option>
                            <?php
                            if ($clientes && $clientes->num_rows > 0):
                                while ($c = $clientes->fetch_assoc()):
                            ?>
                            <option value="<?= $c['id'] ?>"
                                <?= (isset($_POST['usuario_id']) && $_POST['usuario_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= limpiar($c['nombre']) ?> <?= limpiar($c['apellidos']) ?>
                                — <?= limpiar($c['telefono']) ?>
                            </option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>

                    <!-- Servicio -->
                    <div class="campo-grupo">
                        <label for="servicio_id">Servicio *</label>
                        <select id="servicio_id" name="servicio_id" required>
                            <option value="">— Selecciona un servicio —</option>
                            <?php
                            if ($servicios && $servicios->num_rows > 0):
                                while ($s = $servicios->fetch_assoc()):
                            ?>
                            <option value="<?= $s['id'] ?>"
                                <?= (isset($_POST['servicio_id']) && $_POST['servicio_id'] == $s['id']) ? 'selected' : '' ?>>
                                <?= limpiar($s['nombre']) ?> —
                                <?= number_format($s['precio'], 2) ?>€
                                (<?= $s['duracion'] ?> min)
                            </option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>

                    <!-- Fecha (campo oculto, se rellena con el calendario) -->
                    <input type="hidden" id="fecha" name="fecha"
                           value="<?= limpiar($_POST['fecha'] ?? $_GET['fecha'] ?? '') ?>">

                    <!-- Texto que muestra la fecha seleccionada -->
                    <div class="campo-grupo">
                        <label>Fecha seleccionada</label>
                        <p id="fecha-seleccionada-texto"
                           style="font-size:13px; color:var(--plateado);
                                  letter-spacing:2px; padding:12px 15px;
                                  border:1px solid var(--negro-borde);
                                  background:var(--negro);">
                            <?php
                            $fecha_sel = $_POST['fecha'] ?? $_GET['fecha'] ?? '';
                            echo !empty($fecha_sel)
                                ? ' ' . date('d/m/Y', strtotime($fecha_sel))
                                : '🗓 Selecciona un día del calendario';
                            ?>
                        </p>
                    </div>

                    <!-- Hora con horas ocupadas deshabilitadas -->
                    <div class="campo-grupo">
                        <label for="hora">Hora *</label>
                        <select id="hora" name="hora" required>
                            <option value="">— Selecciona una hora —</option>
                            <?php foreach ($horas_disponibles as $hora_opcion):
                                $ocupada = in_array($hora_opcion, $horas_ocupadas);
                            ?>
                            <option value="<?= $hora_opcion ?>"
                                <?= $ocupada ? 'disabled style="color:#444"' : '' ?>
                                <?= (isset($_POST['hora']) && $_POST['hora'] === $hora_opcion) ? 'selected' : '' ?>>
                                <?= $hora_opcion ?> <?= $ocupada ? '— Ocupado' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p style="font-size:10px; color:var(--blanco-suave);
                                  letter-spacing:1px; margin-top:6px;">
                            Las horas ocupadas no están disponibles
                        </p>
                    </div>

                    <!-- Notas -->
                    <div class="campo-grupo">
                        <label for="notas">Notas (opcional)</label>
                        <textarea id="notas" name="notas" rows="3"
                                  placeholder="Indicaciones especiales..."
                                  style="resize:vertical;"><?= limpiar($_POST['notas'] ?? '') ?></textarea>
                    </div>

                    <p style="font-size:10px; letter-spacing:2px; color:var(--blanco-suave);
                              margin-bottom:20px; text-transform:uppercase;">
                        ℹ️ Las reservas del administrador se confirman automáticamente
                    </p>

                    <button type="submit" class="btn-form">Crear reserva</button>

                    <div style="text-align:center; margin-top:15px;">
                        <a href="gestionar.php"
                           style="font-size:10px; letter-spacing:2px; color:var(--blanco-suave);">
                            ← Volver a reservas
                        </a>
                    </div>

                </form>
            </div>

        </div>

    </main>
</div>

<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>