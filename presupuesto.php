
<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$mensaje = '';

// Obtener presupuesto actual
$usuarioResp = supabaseRequest(
    'usuarios',
    'GET',
    null,
    'id=eq.' . $usuarioId
);

$presupuestoActual = 0;

if (!empty($usuarioResp['data'])) {
    $presupuestoActual = (float) $usuarioResp['data'][0]['presupuesto_mensual'];
}

// Guardar nuevo presupuesto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $presupuesto = $_POST['presupuesto'] ?? '';

    if ($presupuesto === '' || !is_numeric($presupuesto)) {

        $mensaje = 'Ingresa un monto válido.';

    } elseif ($presupuesto < 0) {

        $mensaje = 'El presupuesto no puede ser negativo.';

    } else {

        $datos = [
            'presupuesto_mensual' => (float) $presupuesto
        ];

        $respuesta = supabaseRequest(
            'usuarios',
            'PATCH',
            $datos,
            'id=eq.' . $usuarioId
        );

        if ($respuesta['status'] >= 200 && $respuesta['status'] < 300) {

            $presupuestoActual = (float) $presupuesto;

            $mensaje = 'Presupuesto actualizado correctamente.';

        } else {

            $mensaje = 'No se pudo actualizar el presupuesto.';

            if (!empty($respuesta['data']['message'])) {
                $mensaje .= ' ' . $respuesta['data']['message'];
            }
        }
    }
}

function formatoDinero($valor)
{
    return '$' . number_format($valor, 0, ',', '.');
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

    <title>Presupuesto</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f6fa;
        }

        .navbar {
            background: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        .navbar h2 {
            margin: 0;
        }

        .navbar a {
            text-decoration: none;
            color: #333;
            margin-left: 20px;
        }

        .contenedor {
            max-width: 600px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .tarjeta {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        .presupuesto-actual {
            margin: 25px 0;
            padding: 20px;
            background: #f1f5f9;
            border-radius: 10px;
        }

        .presupuesto-actual span {
            display: block;
            font-size: 28px;
            font-weight: bold;
            margin-top: 8px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #007bff;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            opacity: .9;
        }

        .mensaje {
            background: #e8f5e9;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .volver {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
        }

        @media (max-width: 600px) {

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

        }

    </style>

</head>

<body>

<div class="navbar">

    <h2>Mi Presupuesto</h2>

    <div>

        <a href="dashboard.php">
            Inicio
        </a>

        <a href="movimientos.php">
            Movimientos
        </a>

        <a href="presupuesto.php">
            Presupuesto
        </a>

        <a href="logout.php">
            Cerrar sesión
        </a>

    </div>

</div>

<div class="contenedor">

    <div class="tarjeta">

        <h1>Presupuesto mensual</h1>

        <p>
            Define cuánto dinero quieres gastar como máximo durante este mes.
        </p>

        <?php if ($mensaje !== ''): ?>

            <div class="mensaje">
                <?= htmlspecialchars($mensaje) ?>
            </div>

        <?php endif; ?>

        <div class="presupuesto-actual">

            Tu presupuesto actual:

            <span>
                <?= formatoDinero($presupuestoActual) ?>
            </span>

        </div>

        <form method="POST">

            <!-- CAMPO CORREGIDO -->

            <label for="presupuesto">
                Nuevo presupuesto
            </label>

            <input
                type="number"
                id="presupuesto"
                name="presupuesto"
                min="0"
                step="1"
                placeholder="Ej: 500000"
                required
            >

            <button type="submit">
                Guardar presupuesto
            </button>

        </form>

        <a
            class="volver"
            href="dashboard.php"
        >
            ← Volver al dashboard
        </a>

    </div>

</div>

</body>

</html>
