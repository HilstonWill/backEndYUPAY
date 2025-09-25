<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

header("Content-Type: application/json");
require_once __DIR__ . "/transacciones.php";

$transaccion = new Transaccion();

// Leer datos del body (JSON)
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "No se recibieron datos"]);
    exit;
}

$resultado = $transaccion->crear(
    $data["dni_usuario"],
    $data["categoria_id"],
    $data["monto"],
    $data["tipo"],
    $data["descripcion"] ?? null,
    $data["fecha"] ?? null
);

echo json_encode($resultado);
