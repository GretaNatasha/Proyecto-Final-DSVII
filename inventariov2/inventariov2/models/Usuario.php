<?php
class Usuario {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username) {
        $query = "CALL sp_login_usuario(:username)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTodos() {
        $query = "CALL sp_obtener_usuarios()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "CALL sp_obtener_usuario_por_id(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($username, $password, $role_id) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query = "CALL sp_crear_usuario(:username, :password, :role_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":role_id", $role_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function actualizar($id, $username, $password, $role_id, $estado) {
        $hashed_password = "";
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        }
        $query = "CALL sp_actualizar_usuario(:id, :username, :password, :role_id, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":role_id", $role_id, PDO::PARAM_INT);
        $stmt->bindParam(":estado", $estado, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "CALL sp_eliminar_usuario(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function obtenerRoles() {
        $query = "CALL sp_obtener_roles()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeUsername($username, $excludeId = null) {
        $exclude_id = $excludeId !== null ? (int)$excludeId : 0;
        $query = "CALL sp_existe_username(:username, :exclude_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":exclude_id", $exclude_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ((int)$row['total']) > 0;
    }
}
?>
