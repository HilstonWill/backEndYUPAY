<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/categorias.php';

$categoria = new Categoria();

// 🔹 Leer datos (soporta JSON y x-www-form-urlencoded)
$input = json_decode(file_get_contents("php://input"), true);
if ($input) {
    $nombre = $input['nombre'] ?? '';
    $color = $input['color'] ?? '';
    $icono = $input['icono'] ?? '';
    $dni_usuario = $input['dni_usuario'] ?? '';
} else {
    $nombre = $_POST['nombre'] ?? '';
    $color = $_POST['color'] ?? '';
    $icono = $_POST['icono'] ?? '';
    $dni_usuario = $_POST['dni_usuario'] ?? '';
}

// Validar datos mínimos
if (!$nombre || !$dni_usuario) {
    echo json_encode([
        "success" => false,
        "message" => "Faltan parámetros obligatorios (nombre, dni_usuario)"
    ]);
    exit;
}

// Crear categoría
$response = $categoria->crear($nombre, $color, $icono, $dni_usuario);

// Devolver JSON
echo json_encode($response);
