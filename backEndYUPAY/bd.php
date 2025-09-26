<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $conn;
    private $connected = false;

    // Parámetros de reconexión
    private $maxRetries = 2;
    private $retryDelaySeconds = 1;

    // Constructor privado para singleton
    private function __construct() {
        $this->connect();
    }

    // Obtener la instancia (singleton)
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Crear la conexión (con reintentos simples)
    private function connect() {
        $retries = 0;
        $host = DB_SERVER;
        $port = DB_PORT;
        $user = DB_USER;
        $pass = DB_PASS;
        $db   = DB_NAME;

        // Soporte para conexiones persistentes si se desea: 'p:host'
        // $host = 'p:' . $host; // descomenta si quieres persistente

        while ($retries <= $this->maxRetries) {
            $this->conn = @new mysqli($host, $user, $pass, $db, $port);

            if ($this->conn && !$this->conn->connect_error) {
                $this->connected = true;
                // Charset
                $this->conn->set_charset('utf8mb4');
                return;
            }

            // Si hay fallo, guardar error y reintentar
            $retries++;
            if ($retries <= $this->maxRetries) {
                sleep($this->retryDelaySeconds);
            } else {
                $err = $this->conn ? $this->conn->connect_error : 'Unknown error creating mysqli object';
                // Lanzar excepción para que el caller la maneje (mejor que die())
                if (defined('APP_DEBUG') && APP_DEBUG) {
                    throw new Exception("MySQL connection error after {$retries} attempts: " . $err);
                } else {
                    // Mensaje genérico en producción
                    throw new Exception("Error al conectar a la base de datos.");
                }
            }
        }
    }

    // Obtener la conexión mysqli (reconectar si necesario)
    public function getConnection(): mysqli {
        if (!$this->connected || !$this->conn || $this->conn->ping() === false) {
            // Intentar reconectar
            $this->connect();
        }
        return $this->conn;
    }

    // Atajos para prepare y query
    public function prepare(string $sql) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error . " — SQL: " . $sql);
            }
            return false;
        }
        return $stmt;
    }

    public function query(string $sql) {
        $conn = $this->getConnection();
        $result = $conn->query($sql);
        if ($result === false && defined('APP_DEBUG') && APP_DEBUG) {
            throw new Exception("Query failed: (" . $conn->errno . ") " . $conn->error . " — SQL: " . $sql);
        }
        return $result;
    }

    // Helpers para transacciones
    public function beginTransaction() {
        $this->getConnection()->begin_transaction();
    }

    public function commit() {
        $this->getConnection()->commit();
    }

    public function rollback() {
        $this->getConnection()->rollback();
    }

    // Cerrar conexión
    public function close() {
        if ($this->conn) {
            @$this->conn->close();
            $this->connected = false;
        }
    }

    // Destructor: cerrar si queda abierta
    public function __destruct() {
        $this->close();
    }
}
