<?php

session_start();


// ========================================
// CONFIGURACIÓN LOCAL
// ========================================

$configLocal = __DIR__ . '/config.local.php';

if (!file_exists($configLocal)) {
    die('Falta el archivo config.local.php');
}

require_once $configLocal;


// ========================================
// PETICIONES A SUPABASE
// ========================================

function supabaseRequest($tabla, $metodo = 'GET', $datos = null, $query = '')
{
    $url = SUPABASE_URL . '/rest/v1/' . $tabla;

    if (!empty($query)) {
        $url .= '?' . $query;
    }

    $curl = curl_init($url);

    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Accept: application/json',
        'Prefer: return=representation'
    ];

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $metodo);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    if ($datos !== null) {
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            json_encode($datos)
        );
    }

    $respuesta = curl_exec($curl);

    $codigo = curl_getinfo(
        $curl,
        CURLINFO_HTTP_CODE
    );

    if (curl_errno($curl)) {
        die(
            'Error de conexión: '
            . curl_error($curl)
        );
    }

    curl_close($curl);

    return [
        'status' => $codigo,
        'data' => json_decode($respuesta, true)
        
    ];
}
