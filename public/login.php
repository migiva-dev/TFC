<?php
// =====================================================
// ARCHIVO: public/login.php
// Descripción: Página de inicio de sesión de clientes.
//              Comprueba las credenciales contra la BD
//              y crea la sesión si son correctas.
// =====================================================

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/funciones.php';

iniciar_sesion();

// Si ya está logueado lo mandamos a reservar
if (esta_logueado()) {
    redirigir(SITIO_URL . '/reservar.php');
}

$titulo_pagina = 'Iniciar sesión';
$error = '';

// -------------------------------------------------------
// Procesamos el formulario cuando se envía (método POST)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recogemos los datos del formulario
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // -- Validaciones básicas --
    if (empty($email) || empty($password)) {
        $error = 'Por favor, rellena todos los campos.';

    } else {

        // Buscamos al usuario por su email en la BD
        $stmt = $conexion->prepare(
            "SELECT id, nombre, apellidos, password FROM usuarios WHERE email = ?"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            // No existe ningún usuario con ese email
            $error = 'Correo o contraseña incorrectos.';

        } else {
            // Obtenemos los datos del usuario encontrado
            $stmt->bind_result($id, $nombre, $apellidos, $password_bd);
            $stmt->fetch();

            // Verificamos la contraseña con bcrypt
            // password_verify compara la contraseña introducida
            // con el hash guardado en la BD
            if (!password_verify($password, $password_bd)) {
                $error = 'Correo o contraseña incorrectos.';

            } else {
                // Credenciales correctas: creamos la sesión del usuario
                $_SESSION[USER_SESSION_KEY] = [
                    'id'        => $id,
                    'nombre'    => $nombre,
                    'apellidos' => $apellidos
                ];

                $stmt->close();

                // Si venía de una página que requería login,
                // comprobamos si hay una URL guardada para redirigir
                redirigir(SITIO_URL . '/reservar.php');
            }
        }

        $stmt->close();
    }
}

require_once '../includes/header.php';
?>

<!-- ================================================
     FORMULARIO DE LOGIN
     ================================================ -->
<div class="formulario-contenedor">

    <h2>Bienvenido</h2>
    <div class="linea-deco"></div>

    <!-- Aviso si viene de una página que requería login -->
    <?php if (isset($_GET['aviso']) && $_GET['aviso'] === 'debes_iniciar_sesion'): ?>
        <div class="aviso aviso-error">
            Debes iniciar sesión para reservar una cita.
        </div>
    <?php endif; ?>

    <!-- Mensaje de error si las credenciales son incorrectas -->
    <?php if (!empty($error)): ?>
        <div class="aviso aviso-error"><?= limpiar($error) ?></div>
    <?php endif; ?>

    <!-- Formulario de inicio de sesión -->
    <form method="POST" action="login.php">

        <!-- Email -->
        <div class="campo-grupo">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email"
                   value="<?= limpiar($_POST['email'] ?? '') ?>"
                   placeholder="tu@email.com" required>
        </div>

        <!-- Contraseña -->
        <div class="campo-grupo">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password"
                   placeholder="Tu contraseña" required>
        </div>

        <!-- Botón de envío -->
        <button type="submit" class="btn-form">Iniciar sesión</button>

    </form>

    <!-- Enlace para registrarse si no tiene cuenta -->
    <p class="formulario-enlace">
        ¿No tienes cuenta? <a href="registro.php">Regístrate gratis</a>
    </p>

</div>

<?php
$conexion->close();
require_once '../includes/footer.php';
?>