<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/categorias.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria_id = $_POST['categoria_id'] ?? null;

    if (!$categoria_id) {
        echo json_encode(["success" => false, "error" => "Falta el ID de la categoría"]);
        exit;
    }

    $datos = [];
    if (isset($_POST['nombre'])) $datos['nombre'] = $_POST['nombre'];
    if (isset($_POST['color'])) $datos['color'] = $_POST['color'];
    if (isset($_POST['icono'])) $datos['icono'] = $_POST['icono'];

    $categoria = new Categoria();
    $resultado = $categoria->modificar($categoria_id, $datos);

    echo json_encode($resultado);
    exit;
} else {
    echo json_encode(["success" => false, "error" => "Método no permitido"]);
    exit;
}
?>