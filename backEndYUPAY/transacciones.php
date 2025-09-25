<?php
require_once __DIR__ . '/bd.php';

class Transaccion {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Crear una transacción
    public function crear($dni_usuario, $categoria_id, $monto, $tipo, $descripcion = null, $fecha = null) {
        $sql = "INSERT INTO transacciones (dni_usuario, categoria_id, monto, tipo, descripcion, fecha) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }

        if ($fecha === null) {
            $fecha = date("Y-m-d H:i:s");
        }

        $stmt->bind_param("sidsss", $dni_usuario, $categoria_id, $monto, $tipo, $descripcion, $fecha);

        if ($stmt->execute()) {
            return ["success" => true, "id" => $stmt->insert_id];
        } else {
            return ["error" => $stmt->error];
        }
    }

    // Obtener todas las transacciones
    public function getTodas() {
        $sql = "SELECT * FROM transacciones ORDER BY fecha DESC";
        $result = $this->db->query($sql);
        if (!$result) {
            return ["error" => $this->db->error];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener transacciones de un usuario
    public function getPorUsuario($dni_usuario) {
        $sql = "SELECT * FROM transacciones WHERE dni_usuario = ? ORDER BY fecha DESC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }

        $stmt->bind_param("s", $dni_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Eliminar una transacción
    public function eliminar($transaccion_id) {
        $sql = "DELETE FROM transacciones WHERE transaccion_id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }

        $stmt->bind_param("i", $transaccion_id);
        if ($stmt->execute()) {
            return ["success" => true];
        } else {
            return ["error" => $stmt->error];
        }
    }

    // Obtener transacciones de un usuario con nombre de categoría
    public function getPorUsuarioConCategoria($dni_usuario) {
        $sql = "SELECT t.transaccion_id, t.monto, t.tipo, t.fecha, t.descripcion, 
                    c.nombre AS categoria_nombre, c.color, c.icono
                FROM transacciones t
                INNER JOIN categorias c ON t.categoria_id = c.categoria_id
                WHERE t.dni_usuario = ?
                ORDER BY t.fecha DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }

        $stmt->bind_param("s", $dni_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }



    // Obtener gastos del mes actual agrupados por categoría
    public function getGastosMesActual($dni_usuario) {
        $sql = "SELECT c.nombre, c.icono, c.color, SUM(t.monto) AS total_gastado
                FROM transacciones t
                INNER JOIN categorias c ON t.categoria_id = c.categoria_id
                WHERE t.dni_usuario = ?
                AND t.tipo = 'gasto'
                AND MONTH(t.fecha) = MONTH(CURRENT_DATE())
                AND YEAR(t.fecha) = YEAR(CURRENT_DATE())
                GROUP BY c.categoria_id, c.nombre, c.icono
                ORDER BY total_gastado DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }

        $stmt->bind_param("s", $dni_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }


    // Obtener balance (ingresos - gastos) de los últimos 4 meses
    public function getBalanceUltimos4Meses($dni_usuario) {
        $sql = "SELECT 
                    DATE_FORMAT(t.fecha, '%Y-%m') AS mes,
                    SUM(CASE WHEN t.tipo = 'ingreso' THEN t.monto ELSE 0 END) AS total_ingresos,
                    SUM(CASE WHEN t.tipo = 'gasto' THEN t.monto ELSE 0 END) AS total_gastos,
                    SUM(CASE WHEN t.tipo = 'ingreso' THEN t.monto ELSE 0 END) - 
                    SUM(CASE WHEN t.tipo = 'gasto' THEN t.monto ELSE 0 END) AS balance
                FROM transacciones t
                WHERE t.dni_usuario = ?
                AND t.fecha >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01')
                GROUP BY DATE_FORMAT(t.fecha, '%Y-%m')
                ORDER BY mes DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }

        $stmt->bind_param("s", $dni_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getIngresosPorCategoria($dni_usuario) {
        $sql = "SELECT categoria_id, SUM(monto) AS total 
                FROM transacciones 
                WHERE dni_usuario = ? AND tipo = 'ingreso' 
                GROUP BY categoria_id";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }

        $stmt->bind_param("s", $dni_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getGastosPorCategoria($dni_usuario) {
        $sql = "SELECT categoria_id, SUM(monto) AS total 
                FROM transacciones 
                WHERE dni_usuario = ? AND tipo = 'gasto' 
                GROUP BY categoria_id";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ["error" => $this->db->error];
        }

        $stmt->bind_param("s", $dni_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }




}
?>

