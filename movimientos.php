
<?php

require_once 'config.php';

// ===============================
// PROTEGER PÁGINA
// ===============================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$mensaje = '';

// ===============================
// ELIMINAR MOVIMIENTO
// ===============================

if (isset($_GET['eliminar'])) {

    $movimientoId = (int) $_GET['eliminar'];

    if ($movimientoId > 0) {

        $respuesta = supabaseRequest(
            'movimientos',
            'DELETE',
            null,
            'id=eq.' . $movimientoId .
            '&usuario_id=eq.' . $usuarioId
        );
    }

    header('Location: movimientos.php');
    exit;
}

// ===============================
// AGREGAR MOVIMIENTO
// ===============================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tipo = $_POST['tipo'] ?? 'gasto';

    $descripcion = trim(
        $_POST['descripcion'] ?? ''
    );

    $monto = (float) (
        $_POST['monto'] ?? 0
    );

    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $categoriaId = $_POST['categoria_id'] ?? '';

    // Validar tipo
    if (!in_array($tipo, ['gasto', 'ingreso'], true)) {
        $tipo = 'gasto';
    }

    if ($descripcion === '') {

        $mensaje = 'Debes ingresar una descripción.';

    } elseif ($monto <= 0) {

        $mensaje = 'El monto debe ser mayor a 0.';

    } else {

        $datos = [
            'usuario_id' => (int) $usuarioId,
            'categoria_id' =>
                $categoriaId !== ''
                    ? (int) $categoriaId
                    : null,
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'monto' => $monto,
            'fecha' => $fecha
        ];

        $respuesta = supabaseRequest(
            'movimientos',
            'POST',
            $datos
        );

        if (
            $respuesta['status'] >= 200 &&
            $respuesta['status'] < 300
        ) {

            header('Location: movimientos.php');
            exit;

        } else {

            $mensaje = 'No se pudo guardar el movimiento.';

            if (!empty($respuesta['data']['message'])) {
                $mensaje .= ' ' . $respuesta['data']['message'];
            }
        }
    }
}

// ===============================
// OBTENER CATEGORÍAS
// ===============================

$respuestaCategorias = supabaseRequest(
    'categorias',
    'GET',
    null,
    'select=id,nombre&order=nombre.asc'
);

$categorias = $respuestaCategorias['data'] ?? [];

// ===============================
// OBTENER MOVIMIENTOS
// ===============================

$respuestaMovimientos = supabaseRequest(
    'movimientos',
    'GET',
    null,
    'select=id,usuario_id,categoria_id,tipo,descripcion,monto,fecha,categorias(nombre)' .
    '&usuario_id=eq.' . $usuarioId .
    '&order=fecha.desc,id.desc'
);

$movimientos = $respuestaMovimientos['data'] ?? [];

// ===============================
// FORMATO DINERO
// ===============================

function formatoDinero($valor)
{
    return '$' . number_format(
        (float) $valor,
        0,
        ',',
        '.'
    );
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

    <title>Movimientos</title>

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

        /* NAVBAR */

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

        /* CONTENEDOR */

        .contenedor {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* TARJETAS */

        .tarjeta {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        /* FORMULARIO */

        .formulario {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .campo {
            display: flex;
            flex-direction: column;
        }

        .campo label {
            font-weight: bold;
            margin-bottom: 7px;
        }

        .campo input,
        .campo select {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        .boton-contenedor {
            display: flex;
            align-items: end;
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #007bff;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            opacity: .9;
        }

        /* MENSAJE */

        .mensaje {
            background: #ffecec;
            color: #b91c1c;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* TABLA */

        .tabla-contenedor {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f5f5f5;
        }

        .gasto {
            color: #dc3545;
            font-weight: bold;
        }

        .ingreso {
            color: #28a745;
            font-weight: bold;
        }

        .eliminar {
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
        }

        .vacio {
            text-align: center;
            padding: 30px;
            color: #777;
        }

        /* RESPONSIVE */

        @media (max-width: 700px) {

            .formulario {
                grid-template-columns: 1fr;
            }

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .navbar a {
                margin: 0 8px;
            }
        }

    </style>

</head>

<body>

<!-- ===============================
     NAVEGACIÓN
================================ -->

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

<!-- ===============================
     CONTENIDO
================================ -->

<div class="contenedor">

    <h1>Movimientos</h1>

    <p>
        Registra todos tus ingresos y gastos.
    </p>

    <?php if ($mensaje !== ''): ?>

        <div class="mensaje">
            <?= htmlspecialchars($mensaje) ?>
        </div>

    <?php endif; ?>

    <!-- ===========================
         AGREGAR MOVIMIENTO
    ============================ -->

    <div class="tarjeta">

        <h2>Agregar movimiento</h2>

        <form
            method="POST"
            class="formulario"
        >

            <!-- TIPO -->

            <div class="campo">

                <label for="tipo">
                    Tipo
                </label>

                <select
                    id="tipo"
                    name="tipo"
                    required
                >

                    <option value="gasto">
                        Gasto
                    </option>

                    <option value="ingreso">
                        Ingreso
                    </option>

                </select>

            </div>

            <!-- DESCRIPCIÓN -->

            <div class="campo">

                <label for="descripcion">
                    Descripción
                </label>

                <input
                    type="text"
                    id="descripcion"
                    name="descripcion"
                    placeholder="Ej: Supermercado"
                    required
                >

            </div>

            <!-- MONTO -->

            <div class="campo">

                <label for="monto">
                    Monto
                </label>

                <input
                    type="number"
                    id="monto"
                    name="monto"
                    min="1"
                    step="1"
                    placeholder="Ej: 25000"
                    required
                >

            </div>

            <!-- CATEGORÍA -->

            <div class="campo">

                <label for="categoria_id">
                    Categoría
                </label>

                <select
                    id="categoria_id"
                    name="categoria_id"
                >

                    <option value="">
                        Sin categoría
                    </option>

                    <?php foreach ($categorias as $categoria): ?>

                        <option
                            value="<?= (int) $categoria['id'] ?>"
                        >

                            <?= htmlspecialchars(
                                $categoria['nombre']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <!-- FECHA -->

            <div class="campo">

                <label for="fecha">
                    Fecha
                </label>

                <input
                    type="date"
                    id="fecha"
                    name="fecha"
                    value="<?= date('Y-m-d') ?>"
                    required
                >

            </div>

            <!-- BOTÓN -->

            <div class="boton-contenedor">

                <button type="submit">
                    Guardar movimiento
                </button>

            </div>

        </form>

    </div>

    <!-- ===========================
         HISTORIAL
    ============================ -->

    <div class="tarjeta">

        <h2>Historial</h2>

        <div class="tabla-contenedor">

            <table>

                <thead>

                    <tr>

                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Acción</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (empty($movimientos)): ?>

                        <tr>

                            <td
                                colspan="6"
                                class="vacio"
                            >
                                Todavía no tienes movimientos.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($movimientos as $movimiento): ?>

                            <?php

                            $categoriaNombre = 'Sin categoría';

                            if (
                                isset($movimiento['categorias']) &&
                                !empty($movimiento['categorias']['nombre'])
                            ) {

                                $categoriaNombre =
                                    $movimiento['categorias']['nombre'];
                            }

                            ?>

                            <tr>

                                <td>

                                    <?= htmlspecialchars(
                                        $movimiento['fecha']
                                    ) ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $movimiento['descripcion']
                                    ) ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $categoriaNombre
                                    ) ?>

                                </td>

                                <td>

                                    <?= ucfirst(
                                        htmlspecialchars(
                                            $movimiento['tipo']
                                        )
                                    ) ?>

                                </td>

                                <td>

                                    <?php if (
                                        $movimiento['tipo'] === 'gasto'
                                    ): ?>

                                        <span class="gasto">

                                            -<?= formatoDinero(
                                                $movimiento['monto']
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="ingreso">

                                            +<?= formatoDinero(
                                                $movimiento['monto']
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <a
                                        class="eliminar"
                                        onclick="
                                            return confirm(
                                                '¿Seguro que quieres eliminar este movimiento?'
                                            )
                                        "
                                        href="?eliminar=<?= (int) $movimiento['id'] ?>"
                                    >
                                        Eliminar
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>

</html>
