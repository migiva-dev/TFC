<?php
// =====================================================
// ARCHIVO: public/registro.php
// Descripción: Página de registro de nuevos clientes.
//              El usuario introduce sus datos y se crea
//              una cuenta en la base de datos.
// =====================================================

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/funciones.php';

iniciar_sesion();

// Si el usuario ya está logueado no necesita registrarse
// lo mandamos directamente a reservar
if (esta_logueado()) {
    redirigir(SITIO_URL . '/reservar.php');
}

$titulo_pagina = 'Registro';
$error   = '';  // Mensaje de error si algo falla
$exito   = '';  // Mensaje de éxito al registrarse

// -------------------------------------------------------
// Procesamos el formulario cuando se envía (método POST)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recogemos y limpiamos los datos del formulario
    $nombre    = trim($_POST['nombre']    ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = trim($_POST['password']  ?? '');
    $repetir   = trim($_POST['repetir']   ?? '');

    // -- Validaciones básicas --

    // Campos obligatorios
    if (empty($nombre) || empty($apellidos) || empty($telefono) || empty($password)) {
        $error = 'Por favor, rellena todos los campos obligatorios.';

    // Las contraseñas deben coincidir
    } elseif ($password !== $repetir) {
        $error = 'Las contraseñas no coinciden.';

    // La contraseña debe tener al menos 6 caracteres
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';

    } else {

        // Comprobamos si el email ya está registrado (si se ha indicado)
        if (!empty($email)) {
            $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'Este correo electrónico ya está registrado.';
                $stmt->close();
            } else {
                $stmt->close();
            }
        }

        // Si no hay error, insertamos el nuevo usuario
        if (empty($error)) {

            // Ciframos la contraseña con bcrypt (nunca se guarda en texto plano)
            $password_cifrada = password_hash($password, PASSWORD_BCRYPT);

            // Preparamos la consulta de inserción
            $stmt = $conexion->prepare(
                "INSERT INTO usuarios (nombre, apellidos, telefono, email, password)
                 VALUES (?, ?, ?, ?, ?)"
            );

            // Usamos NULL si el email está vacío
            $email_bd = !empty($email) ? $email : null;

            $stmt->bind_param('sssss', $nombre, $apellidos, $telefono, $email_bd, $password_cifrada);

            if ($stmt->execute()) {
                // Registro exitoso: iniciamos sesión automáticamente
                $_SESSION[USER_SESSION_KEY] = [
                    'id'      => $stmt->insert_id,
                    'nombre'  => $nombre,
                    'apellidos' => $apellidos
                ];
                $stmt->close();

                // Redirigimos a reservar directamente
                redirigir(SITIO_URL . '/reservar.php?bienvenido=1');

            } else {
                $error = 'Error al crear la cuenta. Inténtalo de nuevo.';
                $stmt->close();
            }
        }
    }
}

require_once '../includes/header.php';
?>

<!-- ================================================
     FORMULARIO DE REGISTRO
     ================================================ -->
<div class="formulario-contenedor">

    <h2>Crear cuenta</h2>
    <div class="linea-deco"></div>

    <!-- Mensaje de error si lo hay -->
    <?php if (!empty($error)): ?>
        <div class="aviso aviso-error"><?= limpiar($error) ?></div>
    <?php endif; ?>

    <!-- Formulario de registro -->
    <form method="POST" action="registro.php">

        <!-- Nombre (obligatorio) -->
        <div class="campo-grupo">
            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre"
                   value="<?= limpiar($_POST['nombre'] ?? '') ?>"
                   placeholder="Tu nombre" required>
        </div>

        <!-- Apellidos (obligatorio) -->
        <div class="campo-grupo">
            <label for="apellidos">Apellidos *</label>
            <input type="text" id="apellidos" name="apellidos"
                   value="<?= limpiar($_POST['apellidos'] ?? '') ?>"
                   placeholder="Tus apellidos" required>
        </div>

        <!-- Teléfono (obligatorio) -->
        <div class="campo-grupo">
            <label for="telefono">Teléfono *</label>
            <input type="tel" id="telefono" name="telefono"
                   value="<?= limpiar($_POST['telefono'] ?? '') ?>"
                   placeholder="600 000 000" required>
        </div>

        <!-- Email (opcional) -->
        <div class="campo-grupo">
            <label for="email">Correo electrónico (opcional)</label>
            <input type="email" id="email" name="email"
                   value="<?= limpiar($_POST['email'] ?? '') ?>"
                   placeholder="tu@email.com">
        </div>

        <!-- Contraseña (obligatorio) -->
        <div class="campo-grupo">
            <label for="password">Contraseña *</label>
            <input type="password" id="password" name="password"
                   placeholder="Mínimo 6 caracteres" required>
        </div>

        <!-- Repetir contraseña -->
        <div class="campo-grupo">
            <label for="repetir">Repetir contraseña *</label>
            <input type="password" id="repetir" name="repetir"
                   placeholder="Repite tu contraseña" required>
        </div>

        <!-- Botón de envío -->
        <button type="submit" class="btn-form">Crear cuenta</button>

    </form>

    <!-- Enlace para ir al login si ya tiene cuenta -->
    <p class="formulario-enlace">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
    </p>

</div>

<?php
$conexion->close();
require_once '../includes/footer.php';
?>