<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Incluir la clase Transaccion
require_once __DIR__ . '/transacciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar que llegue el dni_usuario
    if (!isset($_POST['dni_usuario']) || empty($_POST['dni_usuario'])) {
        echo json_encode(["error" => "Falta el parámetro dni_usuario"]);
        exit;
    }

    $dni_usuario = $_POST['dni_usuario'];

    try {
        $transaccion = new Transaccion();
        $resultado = $transaccion->getBalanceUltimos4Meses($dni_usuario);

        echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Método no permitido. Usa POST."]);
}
