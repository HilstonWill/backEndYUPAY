<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/transacciones.php';

// 🔹 Decodificar JSON del body
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['dni_usuario'])) {
    echo json_encode(["error" => "Falta el parámetro dni_usuario"]);
    exit;
}

$dni_usuario = $input['dni_usuario'];

$transaccion = new Transaccion();
$resultado = $transaccion->getGastosMesActual($dni_usuario);

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
