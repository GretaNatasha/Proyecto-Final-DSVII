<?php
require_once 'models/Venta.php';
require_once 'models/Producto.php';
require_once 'models/Cliente.php';
require_once 'config/Database.php';

class SaleController {
    public function index() {
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
        // Redirect to create sale logic for POS
        header("Location: index.php?action=pos");
        exit;
    }

    public function create() {
        if(!isset($_SESSION['user_id'])){
            header("Location: index.php?action=login");
            exit;
        }
        $db = (new Database())->getConnection();
        $productoModel = new Producto($db);
        $clienteModel = new Cliente($db);

        // Fetching list for POS view
        $productos = $productoModel->obtenerTodos();
        $clientes = $clienteModel->obtenerTodos();

        require_once 'views/pos/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            try {
                // Obtener datos JSON de fetch
                $data = json_decode(file_get_contents("php://input"), true);
                
                if(!isset($_SESSION['user_id'])) {
                    throw new Exception("Usuario no autenticado.");
                }

                $db = (new Database())->getConnection();
                $ventaModel = new Venta($db);

                $cliente_id = $data['cliente_id'];
                $usuario_id = $_SESSION['user_id'];
                $subtotal = $data['subtotal'];
                $itbms = $data['itbms'];
                $descuento = $data['descuento'] ?? 0;
                $total = $data['total'];
                $detalles = $data['detalles'];

                if (empty($detalles)) {
                    throw new Exception("La venta debe contener al menos un producto.");
                }

                // Ejecución transaccional mediante PDO dentro del modelo
                $venta_id = $ventaModel->registrarVenta($cliente_id, $usuario_id, $subtotal, $itbms, $descuento, $total, $detalles);

                echo json_encode(['success' => true, 'mensaje' => 'Venta registrada exitosamente', 'venta_id' => $venta_id]);

            } catch (Exception $e) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }

    public function reports() {
        if(!isset($_SESSION['user_id'])){
            header("Location: index.php?action=login");
            exit;
        }
        if ($_SESSION['rol'] !== 'Administrador') {
            $_SESSION['error'] = "Acceso denegado. Se requieren permisos de Administrador.";
            header("Location: index.php?action=dashboard");
            exit;
        }
        require_once 'views/reportes/index.php';
    }
}
?>
