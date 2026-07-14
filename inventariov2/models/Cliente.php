<?php
class Cliente {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodos() {
        $query = "CALL sp_obtener_clientes()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "CALL sp_obtener_cliente_por_id(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($documento, $nombre, $correo, $telefono) {
        $query = "CALL sp_crear_cliente(:documento, :nombre, :correo, :telefono)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":documento", $documento);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":telefono", $telefono);
        return $stmt->execute();
    }

    public function actualizar($id, $documento, $nombre, $correo, $telefono) {
        $query = "CALL sp_actualizar_cliente(:id, :documento, :nombre, :correo, :telefono)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":documento", $documento);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":telefono", $telefono);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "CALL sp_eliminar_cliente(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function existeDocumento($documento, $excludeId = null) {
        $exclude_id = $excludeId !== null ? (int)$excludeId : 0;
        $query = "CALL sp_existe_documento_cliente(:documento, :exclude_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":documento", $documento);
        $stmt->bindParam(":exclude_id", $exclude_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ((int)$row['total']) > 0;
    }
}
?>
