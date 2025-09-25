<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/categorias.php';

// Leer JSON del body
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["categoria_id"])) {
    $categoria_id = intval($data["categoria_id"]);

    $categoria = new Categoria();
    $resultado = $categoria->eliminar($categoria_id);

    echo json_encode($resultado);
} else {
    echo json_encode(["error" => "Falta el parámetro categoria_id"]);
}
?>
