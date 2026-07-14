<?php
require_once 'models/Cliente.php';
require_once 'config/Database.php';

class ClientController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
    }

    private function checkAdmin() {
        $this->checkAuth();
        if ($_SESSION['rol'] !== 'Administrador') {
            $_SESSION['error'] = "Operación no permitida. Se requieren permisos de Administrador.";
            header("Location: index.php?action=clientes");
            exit;
        }
    }

    public function index() {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        $clienteModel = new Cliente($db);
        $clientes = $clienteModel->obtenerTodos();
        require_once 'views/clientes/index.php';
    }

    public function store() {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            $documento = isset($_POST['documento']) ? trim($_POST['documento']) : '';
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
            $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';

            // Validaciones del servidor (Robustez)
            if (empty($documento) || empty($nombre)) {
                $_SESSION['error'] = "El documento y el nombre del cliente son obligatorios.";
                header("Location: index.php?action=clientes");
                exit;
            }

            if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "El correo electrónico '$correo' no tiene un formato válido.";
                header("Location: index.php?action=clientes");
                exit;
            }

            $db = (new Database())->getConnection();
            $clienteModel = new Cliente($db);

            if (empty($id)) {
                // Crear Cliente
                if ($clienteModel->existeDocumento($documento)) {
                    $_SESSION['error'] = "El documento de cliente '$documento' ya está registrado.";
                    header("Location: index.php?action=clientes");
                    exit;
                }

                if ($clienteModel->crear($documento, $nombre, $correo, $telefono)) {
                    $_SESSION['success'] = "Cliente creado exitosamente.";
                } else {
                    $_SESSION['error'] = "Error al registrar el cliente.";
                }
            } else {
                // Actualizar Cliente
                if ($clienteModel->existeDocumento($documento, $id)) {
                    $_SESSION['error'] = "El documento de cliente '$documento' ya está registrado por otro cliente.";
                    header("Location: index.php?action=clientes");
                    exit;
                }

                if ($clienteModel->actualizar($id, $documento, $nombre, $correo, $telefono)) {
                    $_SESSION['success'] = "Cliente actualizado exitosamente.";
                } else {
                    $_SESSION['error'] = "Error al actualizar el cliente.";
                }
            }
        }

        header("Location: index.php?action=clientes");
        exit;
    }

    public function delete() {
        $this->checkAdmin();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['error'] = "ID de cliente inválido.";
            header("Location: index.php?action=clientes");
            exit;
        }

        $db = (new Database())->getConnection();
        $clienteModel = new Cliente($db);

        try {
            if ($clienteModel->eliminar($id)) {
                $_SESSION['success'] = "Cliente eliminado exitosamente.";
            } else {
                $_SESSION['error'] = "Error al eliminar el cliente.";
            }
        } catch (PDOException $e) {
            // Manejo estructurado de excepciones por restricción de clave foránea
            if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
                $_SESSION['error'] = "No es posible eliminar este cliente porque tiene ventas asociadas en el historial.";
            } else {
                $_SESSION['error'] = "Error de base de datos al eliminar: " . $e->getMessage();
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al eliminar: " . $e->getMessage();
        }

        header("Location: index.php?action=clientes");
        exit;
    }
}
?>
