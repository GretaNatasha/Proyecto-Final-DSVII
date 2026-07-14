<?php
class Producto {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodos() {
        $query = "CALL sp_obtener_productos()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerPorId($id) {
        $query = "CALL sp_obtener_producto_por_id(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerAlertasStock() {
        $query = "CALL sp_obtener_alertas_stock()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($codigo, $nombre, $descripcion, $costo, $precio, $stock, $stock_minimo) {
        $query = "CALL sp_crear_producto(:codigo, :nombre, :descripcion, :costo, :precio, :stock, :stock_minimo)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":costo", $costo);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":stock", $stock, PDO::PARAM_INT);
        $stmt->bindParam(":stock_minimo", $stock_minimo, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function actualizar($id, $codigo, $nombre, $descripcion, $costo, $precio, $stock, $stock_minimo, $estado) {
        $query = "CALL sp_actualizar_producto(:id, :codigo, :nombre, :descripcion, :costo, :precio, :stock, :stock_minimo, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":costo", $costo);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":stock", $stock, PDO::PARAM_INT);
        $stmt->bindParam(":stock_minimo", $stock_minimo, PDO::PARAM_INT);
        $stmt->bindParam(":estado", $estado, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "CALL sp_eliminar_producto(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function existeCodigo($codigo, $excludeId = null) {
        $exclude_id = $excludeId !== null ? (int)$excludeId : 0;
        $query = "CALL sp_existe_codigo_producto(:codigo, :exclude_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->bindParam(":exclude_id", $exclude_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ((int)$row['total']) > 0;
    }

    public function obtenerTodosParaCatalogo() {
        $query = "CALL sp_obtener_todos_los_productos()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleEstado($id) {
        $query = "CALL sp_toggle_estado_producto(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
