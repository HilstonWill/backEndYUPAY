<?php
// check_db.php - borrar después de probar
require_once __DIR__ . '/bd.php';

try {
    $db = Database::getInstance();
    $mysqli = $db->getConnection();

    $res = $mysqli->query("SELECT 1 AS ok");
    if ($res) {
        $row = $res->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'result' => $row]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Query failed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    // Si estás en DEBUG muestra el mensaje, si no muestra genérico
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al conectar a la BD']);
    }
}
