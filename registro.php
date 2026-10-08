
<?php
require_once 'config.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nombre === '' || $email === '' || $password === '') {
        $mensaje = 'Completa todos los campos.';
    } else {

        // Buscar si el correo ya existe
        $consulta = supabaseRequest(
            'usuarios',
            'GET',
            null,
            'email=eq.' . urlencode($email)
        );

        if (!empty($consulta['data'])) {
            $mensaje = 'Ese correo ya está registrado.';
        } else {

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $nuevoUsuario = [
                'nombre' => $nombre,
                'email' => $email,
                'password' => $passwordHash,
                'presupuesto_mensual' => 0
            ];

            $respuesta = supabaseRequest(
                'usuarios',
                'POST',
                $nuevoUsuario
            );

            if ($respuesta['status'] >= 200 && $respuesta['status'] < 300) {

                header('Location: login.php?registro=ok');
                exit;

            } else {

                $mensaje = 'No se pudo crear la cuenta.';

                if (!empty($respuesta['data']['message'])) {
                    $mensaje .= ' ' . $respuesta['data']['message'];
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear cuenta</title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="contenedor-auth">

    <div class="tarjeta-auth">

        <h1>Crear cuenta</h1>

        <p>
            Regístrate para comenzar a controlar tus gastos.
        </p>

        <?php if ($mensaje !== ''): ?>

            <div class="mensaje-error">
                <?= htmlspecialchars($mensaje) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <!-- NOMBRE CORREGIDO -->

            <div class="campo">

                <label for="nombre">
                    Nombre
                </label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Tu nombre"
                    required
                >

            </div>


            <!-- CORREO CORREGIDO -->

            <div class="campo">

                <label for="email">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="correo@ejemplo.com"
                    required
                >

            </div>


            <!-- CONTRASEÑA CORREGIDA -->

            <div class="campo">

                <label for="password">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Contraseña"
                    minlength="6"
                    required
                >

            </div>


            <button type="submit">
                Crear cuenta
            </button>

        </form>


        <p class="enlace-auth">

            ¿Ya tienes cuenta?

            <a href="login.php">
                Iniciar sesión
            </a>

        </p>

    </div>

</div>

</body>

</html>
