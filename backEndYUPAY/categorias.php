<?php
require_once __DIR__ . '/bd.php';

class Categoria {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Crear una categoría nueva
    public function crear($nombre, $color, $icono, $dni_usuario) {
        $sql = "INSERT INTO categorias (nombre, color, icono, dni_usuario, activo) VALUES (?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }
        $stmt->bind_param("ssss", $nombre, $color, $icono, $dni_usuario);
        if ($stmt->execute()) {
            return ["success" => true, "id" => $stmt->insert_id];
        } else {
            return ["error" => $stmt->error];
        }
    }

    // Obtener todas las categorías activas de un usuario
    public function getPorUsuario($dni_usuario) {
        $sql = "SELECT * FROM categorias WHERE dni_usuario = ? AND activo = 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }
        $stmt->bind_param("s", $dni_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener todas las categorías (de todos los usuarios)
    public function getTodas() {
        $sql = "SELECT * FROM categorias";
        $result = $this->db->query($sql);
        if (!$result) {
            return ["error" => $this->db->error];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // "Eliminar" una categoría → ponerla inactiva
    public function eliminar($categoria_id) {
        $sql = "UPDATE categorias SET activo = 0 WHERE categoria_id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }
        $stmt->bind_param("i", $categoria_id);
        if ($stmt->execute()) {
            return ["success" => true];
        } else {
            return ["error" => $stmt->error];
        }
    }

    // Modificar una categoría (nombre, color o icono)
    public function modificar($categoria_id, $datos) {
        $campos = [];
        $valores = [];
        $tipos = "";

        if (isset($datos['nombre'])) {
            $campos[] = "nombre = ?";
            $valores[] = $datos['nombre'];
            $tipos .= "s";
        }
        if (isset($datos['color'])) {
            $campos[] = "color = ?";
            $valores[] = $datos['color'];
            $tipos .= "s";
        }
        if (isset($datos['icono'])) {
            $campos[] = "icono = ?";
            $valores[] = $datos['icono'];
            $tipos .= "s";
        }

        if (empty($campos)) {
            return ["error" => "No se especificaron campos para modificar"];
        }

        $sql = "UPDATE categorias SET " . implode(", ", $campos) . " WHERE categoria_id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }

        $tipos .= "i"; // para categoria_id
        $valores[] = $categoria_id;

        $stmt->bind_param($tipos, ...$valores);

        if ($stmt->execute()) {
            return ["success" => true];
        } else {
            return ["error" => $stmt->error];
        }
    }
}
?>
