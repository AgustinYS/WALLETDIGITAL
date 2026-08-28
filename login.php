<?php

require_once 'config.php';

$mensaje = '';


// Mensaje después de registrarse
if (isset($_GET['registro']) && $_GET['registro'] === 'ok') {
    $mensaje = 'Cuenta creada correctamente. Ahora inicia sesión.';
}


// Cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';


    // Validar campos
    if ($email === '' || $password === '') {

        $mensaje = 'Completa todos los campos.';

    } else {

        // Buscar usuario en Supabase
        $consulta = supabaseRequest(
            'usuarios',
            'GET',
            null,
            'select=id,nombre,email,password&email=eq.' . urlencode($email)
        );


        // Error de conexión o Supabase
        if (
            $consulta['status'] < 200 ||
            $consulta['status'] >= 300
        ) {

            $mensaje = 'Error de Supabase. Código: ' . $consulta['status'];

            if (!empty($consulta['data']['message'])) {
                $mensaje .= ' - ' . $consulta['data']['message'];
            }

        }

        // No existe el correo
        elseif (empty($consulta['data'])) {

            $mensaje = 'No existe una cuenta con ese correo.';

        }

        // Usuario encontrado
        else {

            $usuario = $consulta['data'][0];


            // Comprobar contraseña
            if (
                isset($usuario['password']) &&
                password_verify($password, $usuario['password'])
            ) {

                // Seguridad de sesión
                session_regenerate_id(true);


                // Guardar usuario en sesión
                $_SESSION['usuario_id'] = $usuario['id'];

                $_SESSION['usuario_nombre'] = $usuario['nombre'];

                $_SESSION['usuario_email'] = $usuario['email'];


                // Ir al dashboard
                header('Location: dashboard.php');
                exit;

            } else {

                $mensaje = 'La contraseña ingresada es incorrecta.';

            }

        }

    }

}

?>


<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Iniciar sesión
    </title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

</head>


<body>


<div class="contenedor-auth">


    <div class="tarjeta-auth">


        <h1>
            Iniciar sesión
        </h1>


        <p>
            Ingresa a tu cuenta para controlar tus gastos.
        </p>


        <!-- MENSAJES -->

        <?php if ($mensaje !== ''): ?>

            <div class="mensaje">

                <?= htmlspecialchars($mensaje) ?>

            </div>

        <?php endif; ?>


        <!-- FORMULARIO -->

        <form method="POST">


            <!-- CORREO -->

            <div class="campo">

                <label>
                    Correo electrónico
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="correo@ejemplo.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >

            </div>


            <!-- CONTRASEÑA -->

            <div class="campo">

                <label>
                    Contraseña
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Tu contraseña"
                    required
                >

            </div>


            <!-- BOTÓN -->

            <button type="submit">

                Iniciar sesión

            </button>


        </form>


        <!-- REGISTRO -->

        <p class="enlace-auth">

            ¿No tienes cuenta?

            <a href="registro.php">

                Crear cuenta

            </a>

        </p>


    </div>


</div>


</body>

</html>