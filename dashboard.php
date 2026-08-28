<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Obtener datos del usuario
$usuarioResp = supabaseRequest(
    'usuarios',
    'GET',
    null,
    'id=eq.' . $usuarioId
);

$presupuesto = 0;

if (!empty($usuarioResp['data'])) {
    $presupuesto = (float) $usuarioResp['data'][0]['presupuesto_mensual'];
}

// Fechas del mes actual
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');

// Obtener movimientos del mes actual
$queryMovimientos =
    'usuario_id=eq.' . $usuarioId .
    '&fecha=gte.' . $inicioMes .
    '&fecha=lte.' . $finMes .
    '&order=fecha.desc';

$movimientosResp = supabaseRequest(
    'movimientos',
    'GET',
    null,
    $queryMovimientos
);

$movimientos = $movimientosResp['data'] ?? [];

$totalGastos = 0;
$totalIngresos = 0;

foreach ($movimientos as $movimiento) {

    $monto = (float) $movimiento['monto'];

    if ($movimiento['tipo'] === 'gasto') {
        $totalGastos += $monto;
    }

    if ($movimiento['tipo'] === 'ingreso') {
        $totalIngresos += $monto;
    }
}

// Dinero disponible
$disponible = $presupuesto - $totalGastos;

// Días restantes del mes
$hoy = new DateTime();
$ultimoDiaMes = new DateTime(date('Y-m-t'));

$diasRestantes = $hoy->diff($ultimoDiaMes)->days + 1;

if ($diasRestantes < 1) {
    $diasRestantes = 1;
}

// Máximo sugerido diario
$gastoDiario = 0;

if ($disponible > 0) {
    $gastoDiario = $disponible / $diasRestantes;
}

// Porcentaje usado
$porcentajeUsado = 0;

if ($presupuesto > 0) {
    $porcentajeUsado = ($totalGastos / $presupuesto) * 100;
}

if ($porcentajeUsado > 100) {
    $porcentajeUsado = 100;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Gestor de Gastos</title>

    <link rel="stylesheet" href="assets/style.css">

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f6fa;
        }

        .navbar {
            background: #ffffff;
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

        .dashboard {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .bienvenida {
            margin-bottom: 30px;
        }

        .bienvenida h1 {
            margin-bottom: 5px;
        }

        .tarjetas {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .tarjeta {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .tarjeta h3 {
            margin-top: 0;
            color: #666;
            font-size: 15px;
        }

        .tarjeta .valor {
            font-size: 26px;
            font-weight: bold;
        }

        .gasto {
            color: #d9534f;
        }

        .ingreso {
            color: #28a745;
        }

        .disponible {
            color: #007bff;
        }

        .barra-contenedor {
            background: #ddd;
            height: 15px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 10px;
        }

        .barra {
            height: 100%;
            background: #007bff;
        }

        .acciones {
            margin: 30px 0;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .acciones a {
            display: inline-block;
            padding: 12px 20px;
            background: #007bff;
            color: white;
            border-radius: 8px;
            text-decoration: none;
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .tabla th,
        .tabla td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .tabla th {
            background: #f0f0f0;
        }

        @media (max-width: 900px) {

            .tarjetas {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .tarjetas {
                grid-template-columns: 1fr;
            }

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
        <a href="dashboard.php">Inicio</a>
        <a href="movimientos.php">Movimientos</a>
        <a href="presupuesto.php">Presupuesto</a>
        <a href="logout.php">Cerrar sesión</a>
    </div>

</div>

<div class="dashboard">

    <div class="bienvenida">

        <h1>
            Hola, <?= htmlspecialchars($nombre) ?>
        </h1>

        <p>
            Este es el resumen de tus finanzas de este mes.
        </p>

    </div>


    <div class="tarjetas">

        <div class="tarjeta">

            <h3>Presupuesto mensual</h3>

            <div class="valor">
                <?= formatoDinero($presupuesto) ?>
            </div>

        </div>


        <div class="tarjeta">

            <h3>Gastos del mes</h3>

            <div class="valor gasto">
                <?= formatoDinero($totalGastos) ?>
            </div>

        </div>


        <div class="tarjeta">

            <h3>Ingresos del mes</h3>

            <div class="valor ingreso">
                <?= formatoDinero($totalIngresos) ?>
            </div>

        </div>


        <div class="tarjeta">

            <h3>Dinero disponible</h3>

            <div class="valor disponible">
                <?= formatoDinero($disponible) ?>
            </div>

        </div>

    </div>


    <div class="tarjeta">

        <h3>Control del presupuesto</h3>

        <p>
            Has usado aproximadamente
            <strong>
                <?= round($porcentajeUsado, 1) ?>%
            </strong>
            de tu presupuesto.
        </p>

        <div class="barra-contenedor">

            <div
                class="barra"
                style="width: <?= $porcentajeUsado ?>%">
            </div>

        </div>

        <br>

        <p>
            Puedes gastar aproximadamente:
        </p>

        <div class="valor disponible">

            <?= formatoDinero($gastoDiario) ?>

            <small>por día</small>

        </div>

        <p>
            Quedan <?= $diasRestantes ?> días del mes.
        </p>

    </div>


    <div class="acciones">

        <a href="movimientos.php">
            + Agregar movimiento
        </a>

        <a href="presupuesto.php">
            Configurar presupuesto
        </a>

    </div>


    <h2>Últimos movimientos</h2>

    <table class="tabla">

        <thead>

        <tr>
            <th>Fecha</th>
            <th>Descripción</th>
            <th>Tipo</th>
            <th>Monto</th>
        </tr>

        </thead>

        <tbody>

        <?php if (empty($movimientos)): ?>

            <tr>

                <td colspan="4">
                    Todavía no tienes movimientos este mes.
                </td>

            </tr>

        <?php else: ?>

            <?php foreach (array_slice($movimientos, 0, 10) as $movimiento): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($movimiento['fecha']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($movimiento['descripcion']) ?>
                    </td>

                    <td>
                        <?= ucfirst(htmlspecialchars($movimiento['tipo'])) ?>
                    </td>

                    <td>

                        <?php if ($movimiento['tipo'] === 'gasto'): ?>

                            <span class="gasto">
                                -<?= formatoDinero($movimiento['monto']) ?>
                            </span>

                        <?php else: ?>

                            <span class="ingreso">
                                +<?= formatoDinero($movimiento['monto']) ?>
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</div>

</body>

</html>