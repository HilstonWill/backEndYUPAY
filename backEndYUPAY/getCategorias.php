<?php
require_once __DIR__ . '/categorias.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

$input = json_decode(file_get_contents("php://input"), true);
$dni_usuario = $input['dni_usuario'] ?? null;

$categoria = new Categoria();

if ($dni_usuario) {
    $categorias = $categoria->getPorUsuario($dni_usuario);
    echo json_encode($categorias);
} else {
    echo json_encode(["error" => "dni_usuario requerido"]);
}
?>
