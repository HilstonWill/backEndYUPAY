<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

header("Content-Type: application/json");
require_once __DIR__ . '/usuarios.php';

$dni = $_POST['dni'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($dni) || empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios"]);
    exit;
}

$usuarios = new Usuarios();
$resultado = $usuarios->crearUsuario($dni, $nombre, $email, $password);

echo json_encode($resultado);

$usuarios->close();
