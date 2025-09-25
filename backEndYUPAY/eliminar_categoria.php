<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");


require_once __DIR__ . '/categorias.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria_id = $_POST['categoria_id'] ?? null;

    if (!$categoria_id) {
        echo json_encode(["error" => "Falta el ID de la categoría"]);
        exit;
    }

    $categoria = new Categoria();
    $resultado = $categoria->eliminar($categoria_id);

    echo json_encode($resultado);
} else {
    echo json_encode(["error" => "Método no permitido"]);
}
?>
