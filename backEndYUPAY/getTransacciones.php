<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/transacciones.php';

// Si es OPTIONS (preflight), respondemos vacío
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// Solo permitimos POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Método no permitido
    echo json_encode(["error" => "Método no permitido. Usa POST."]);
    exit;
}

// Leer el cuerpo de la petición
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["dni_usuario"])) {
    $dni_usuario = $data["dni_usuario"];

    $transaccion = new Transaccion();
    // 👉 aquí puedes usar getPorUsuarioConCategoria si quieres ya con nombre de categoría
    $resultado = $transaccion->getPorUsuarioConCategoria($dni_usuario);

    echo json_encode($resultado);
} else {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Falta el parámetro dni_usuario"]);
}
