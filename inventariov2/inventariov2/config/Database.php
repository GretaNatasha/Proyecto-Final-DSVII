<?php
class Database {
    private $host = "localhost";
    private $db_name = "inventario_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            // Configurar PDO para que lance excepciones en caso de error (Robustez)
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Configurar el juego de caracteres a utf8
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // Manejo estructurado de excepciones
            die(json_encode([
                "error" => "Error de conexión a la base de datos.",
                "detalle" => $exception->getMessage()
            ]));
        }
        return $this->conn;
    }
}
?>
