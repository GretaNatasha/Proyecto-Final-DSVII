<?php
require_once 'models/Producto.php';
require_once 'config/Database.php';

class ProductController {
    
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
            header("Location: index.php?action=productos");
            exit;
        }
    }

    public function index() {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        $productoModel = new Producto($db);
        $productos = $productoModel->obtenerTodosParaCatalogo();
        require_once 'views/productos/index.php';
    }

    public function store() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $costo = isset($_POST['costo']) ? (float)$_POST['costo'] : 0.0;
            $precio = isset($_POST['precio']) ? (float)$_POST['precio'] : 0.0;
            $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
            $stock_minimo = isset($_POST['stock_minimo']) ? (int)$_POST['stock_minimo'] : 0;
            $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

            // Validación estricta del lado del servidor (Robustez)
            if (empty($codigo) || empty($nombre)) {
                $_SESSION['error'] = "El código y el nombre del producto son obligatorios.";
                header("Location: index.php?action=productos");
                exit;
            }

            if ($costo < 0 || $precio < 0 || $stock < 0 || $stock_minimo < 0) {
                $_SESSION['error'] = "Los valores de costo, precio, stock y stock mínimo no pueden ser negativos.";
                header("Location: index.php?action=productos");
                exit;
            }

            if ($costo > $precio) {
                $_SESSION['error'] = "El costo del producto ($costo) no puede ser mayor que su precio de venta ($precio).";
                header("Location: index.php?action=productos");
                exit;
            }

            $db = (new Database())->getConnection();
            $productoModel = new Producto($db);

            if (empty($id)) {
                // Crear Producto
                if ($productoModel->existeCodigo($codigo)) {
                    $_SESSION['error'] = "El código de producto '$codigo' ya está registrado.";
                    header("Location: index.php?action=productos");
                    exit;
                }

                if ($productoModel->crear($codigo, $nombre, $descripcion, $costo, $precio, $stock, $stock_minimo)) {
                    $_SESSION['success'] = "Producto creado exitosamente.";
                } else {
                    $_SESSION['error'] = "Error al registrar el producto.";
                }
            } else {
                // Actualizar Producto
                if ($productoModel->existeCodigo($codigo, $id)) {
                    $_SESSION['error'] = "El código de producto '$codigo' ya está registrado por otro producto.";
                    header("Location: index.php?action=productos");
                    exit;
                }

                if ($productoModel->actualizar($id, $codigo, $nombre, $descripcion, $costo, $precio, $stock, $stock_minimo, $estado)) {
                    $_SESSION['success'] = "Producto actualizado exitosamente.";
                } else {
                    $_SESSION['error'] = "Error al actualizar el producto.";
                }
            }
        }

        header("Location: index.php?action=productos");
        exit;
    }

    public function delete() {
        $this->checkAdmin();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['error'] = "ID de producto inválido.";
            header("Location: index.php?action=productos");
            exit;
        }

        $db = (new Database())->getConnection();
        $productoModel = new Producto($db);

        // Obtener el producto para saber si lo estamos activando o desactivando
        $prod = $productoModel->obtenerPorId($id);
        if ($prod) {
            $nuevoEstado = $prod['estado'] ? "desactivado" : "activado";
            if ($productoModel->toggleEstado($id)) {
                $_SESSION['success'] = "Producto " . $nuevoEstado . " exitosamente.";
            } else {
                $_SESSION['error'] = "Error al cambiar el estado del producto.";
            }
        } else {
            $_SESSION['error'] = "Producto no encontrado.";
        }

        header("Location: index.php?action=productos");
        exit;
    }
}
?>
