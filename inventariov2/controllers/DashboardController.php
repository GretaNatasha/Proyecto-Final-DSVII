<?php
require_once 'config/Database.php';
require_once 'models/Producto.php';
require_once 'models/Cliente.php';

class DashboardController {
    public function index() {
        if(!isset($_SESSION['user_id'])){
            header("Location: index.php?action=login");
            exit;
        }

        $db = (new Database())->getConnection();

        // Obtener cantidad de productos activos
        $productoModel = new Producto($db);
        $productos = $productoModel->obtenerTodosParaCatalogo();
        $totalProductos = count(array_filter($productos, function($p) {
            return (int)$p['estado'] === 1;
        }));

        // Obtener cantidad de clientes
        $clienteModel = new Cliente($db);
        $totalClientes = count($clienteModel->obtenerTodos());

        require_once 'views/dashboard/index.php';
    }
}
?>
