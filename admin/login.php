<?php
// =====================================================
// ARCHIVO: admin/login.php
// Descripción: Login privado del panel de administración.
//              Completamente independiente del login
//              de clientes. Doble capa de seguridad:
//              1. URL no enlazada desde la web pública
//              2. Usuario y contraseña propios del admin
// =====================================================

// Incluimos configuración y funciones
// Usamos dirname para subir un nivel desde /admin a /includes
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// Título de la pestaña
$titulo_pagina = 'Administración — Acceso';

// Iniciamos la sesión
iniciar_sesion();

// Si el admin ya está logueado lo mandamos al dashboard
if (es_admin()) {
    redirigir('../admin/dashboard.php');
}

$error = '';

// -------------------------------------------------------
// Procesamos el formulario cuando se envía (método POST)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recogemos los datos del formulario
    $usuario  = trim($_POST['usuario']  ?? '');
    $password = trim($_POST['password'] ?? '');

    // -- Validaciones básicas --
    if (empty($usuario) || empty($password)) {
        $error = 'Por favor, rellena todos los campos.';

    } else {

        // Buscamos al administrador por su usuario en la BD
        // Usamos la tabla 'administradores', no 'usuarios'
        $stmt = $conexion->prepare(
            "SELECT id, nombre, password FROM administradores WHERE usuario = ?"
        );
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            // No existe ningún admin con ese usuario
            $error = 'Usuario o contraseña incorrectos.';

        } else {
            // Obtenemos los datos del admin encontrado
            $stmt->bind_result($id, $nombre, $password_bd);
            $stmt->fetch();

            // Verificamos la contraseña con bcrypt
            if (!password_verify($password, $password_bd)) {
                $error = 'Usuario o contraseña incorrectos.';

            } else {
                // Credenciales correctas: creamos la sesión del admin
                // Usamos ADMIN_SESSION_KEY, independiente de la sesión de cliente
                $_SESSION[ADMIN_SESSION_KEY] = [
                    'id'     => $id,
                    'nombre' => $nombre
                ];

                $stmt->close();

                // Redirigimos al panel principal del admin
                redirigir('../admin/dashboard.php');
            }
        }

        $stmt->close();
    }
}

// Incluimos la cabecera común

// Incluimos la cabecera común
require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- ================================================
     FORMULARIO DE LOGIN DEL ADMINISTRADOR
     ================================================ -->
<div class="formulario-contenedor">

    <h2>Administración</h2>
    <div class="linea-deco"></div>

    <!-- Aviso de acceso denegado si intentó entrar sin login -->
    <?php if (isset($_GET['aviso']) && $_GET['aviso'] === 'acceso_denegado'): ?>
        <div class="aviso aviso-error">
            Acceso restringido. Identifícate para continuar.
        </div>
    <?php endif; ?>

    <!-- Mensaje de error si las credenciales son incorrectas -->
    <?php if (!empty($error)): ?>
        <div class="aviso aviso-error"><?= limpiar($error) ?></div>
    <?php endif; ?>

    <!-- Formulario de login del administrador -->
    <form method="POST" action="login.php">

        <!-- Usuario -->
        <div class="campo-grupo">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario"
                   value="<?= limpiar($_POST['usuario'] ?? '') ?>"
                   placeholder="Tu usuario de administrador"
                   autocomplete="off" required>
        </div>

        <!-- Contraseña -->
        <div class="campo-grupo">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password"
                   placeholder="Tu contraseña"
                   autocomplete="off" required>
        </div>

        <!-- Botón de acceso -->
        <button type="submit" class="btn-form">Acceder al panel</button>

    </form>

    <!-- Enlace para volver a la web pública -->
    <p class="formulario-enlace">
        <a href="../public/index.php">← Volver a la web</a>
    </p>

</div>


<?php
$conexion->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>