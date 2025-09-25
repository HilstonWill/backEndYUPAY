<?php

require_once __DIR__ . '/config.php';
class Database {
    private $conn;
    
    public function __construct() {
        // Crear la conexión
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        // Verificar la conexión
        if ($this->conn->connect_error) {
            die("Error de conexión a la base de datos: " . $this->conn->connect_error);
        }
        
        // Establecer el conjunto de caracteres
        $this->conn->set_charset("utf8mb4");
    }
    
    // Método para obtener la conexión
    public function getConnection() {
        return $this->conn;
    }
    
    // Método para preparar consultas SQL
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    // Método para ejecutar consultas directas
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    // Método para cerrar la conexión
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>