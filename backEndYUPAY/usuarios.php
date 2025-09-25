<?php
require_once __DIR__ . '/bd.php';

class Usuarios {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obtener todos los usuarios
     */
    public function getAllUsuarios() {
        $sql = "SELECT dni, nombre, email, fecha_creacion FROM Usuarios";
        $result = $this->db->query($sql);

        $usuarios = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
        }
        return $usuarios;
    }

    /**
     * Comprobar si un usuario existe por su DNI
     */
    public function usuarioExiste($dni) {
        $sql = "SELECT dni FROM Usuarios WHERE dni = ?";
        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $dni);
            $stmt->execute();
            $stmt->store_result();
            $existe = $stmt->num_rows > 0;
            $stmt->close();
            return $existe;
        }
        return false;
    }

    /**
     * Comprobar si el usuario corresponde con la contraseña
     */
    
    public function validarUsuario($dni, $password) {
        $sql = "SELECT password_hash, nombre FROM Usuarios WHERE dni = ?";
        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $dni);
            $stmt->execute();
            $stmt->bind_result($hash, $nombre);
            
            if ($stmt->fetch()) {
                $stmt->close();
                if (password_verify($password, $hash)) {
                    return $nombre; // Devuelvo el nombre si la contraseña es correcta
                }
            }
            $stmt->close();
        }
        return false;
    }


/**
     * Crear nuevo usuario
     */
    public function crearUsuario($dni, $nombre, $email, $password) {
        // Verificar si el usuario ya existe
        if ($this->usuarioExiste($dni)) {
            return ["success" => false, "message" => "El DNI ya está registrado"];
        }

        // Verificar si el email ya existe
        $sql = "SELECT email FROM Usuarios WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->close();
                return ["success" => false, "message" => "El email ya está registrado"];
            }
            $stmt->close();
        }

        // Hashear contraseña
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Insertar nuevo usuario
        $sql = "INSERT INTO Usuarios (dni, nombre, email, password_hash) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $dni, $nombre, $email, $password_hash);
            if ($stmt->execute()) {
                $stmt->close();
                return ["success" => true, "message" => "Usuario registrado con éxito"];
            } else {
                $stmt->close();
                return ["success" => false, "message" => "Error al registrar usuario"];
            }
        }

        return ["success" => false, "message" => "Error en la base de datos"];
    }

    /**
     * Cerrar conexión manualmente
     */
    public function close() {
        $this->db->close();
    }
}
?>
