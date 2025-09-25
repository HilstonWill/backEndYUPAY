<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/usuarios.php'; // Importamos la clase Usuarios

header('Content-Type: application/json; charset=utf-8');

// Leer datos del POST
$dni = $_POST['dni'] ?? '';
$password = $_POST['password'] ?? '';

$response = ["success" => false, "message" => ""];

// Validar credenciales
$usuarios = new Usuarios();

if ($usuarios->usuarioExiste($dni)) {
    $nombre = $usuarios->validarUsuario($dni, $password); // ahora devuelve el nombre si es correcto
    if ($nombre) {
        $response["success"] = true;
        $response["message"] = "✅ Login correcto";
        $response["dni"] = $dni;
        $response["nombre"] = $nombre; // añadimos el nombre al JSON
    } else {
        $response["message"] = "❌ Contraseña incorrecta";
    }
} else {
    $response["message"] = "❌ El usuario no existe";
}

$usuarios->close();

// Devolver respuesta en JSON
echo json_encode($response);
?>
