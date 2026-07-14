<?php
require_once '../config/Database.php';
require_once '../models/Producto.php';
require_once '../models/Venta.php';

// Cabeceras para API RESTful
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$method = $_SERVER['REQUEST_METHOD'];
// Endpoint path parsing (e.g. /api/inventario/alertas)
// This will be called from an .htaccess or direct include, let's parse standard query ?endpoint=...
$endpoint = $_GET['endpoint'] ?? '';

$db = (new Database())->getConnection();

if ($method === 'GET') {
    if ($endpoint === 'inventario/alertas') {
        // RETO: Endpoint para alertas de stock
        $productoModel = new Producto($db);
        $alertas = $productoModel->obtenerAlertasStock();
        
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "count" => count($alertas),
            "data" => $alertas
        ]);
        exit;
    }
    
    if ($endpoint === 'ventas/reportes') {
        $ventaModel = new Venta($db);
        $reportes = $ventaModel->reporte();
        
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "data" => $reportes
        ]);
        exit;
    }

    if (preg_match('/facturas\/(\d+)/', $endpoint, $matches)) {
        $venta_id = $matches[1];
        $ventaModel = new Venta($db);
        $detalle = $ventaModel->detalleFactura($venta_id);
        
        if($detalle) {
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "venta_id" => $venta_id,
                "data" => $detalle
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["success" => false, "mensaje" => "Factura no encontrada."]);
        }
        exit;
    }

    // Ruta no encontrada
    http_response_code(404);
    echo json_encode(["error" => "Endpoint no encontrado."]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Método HTTP no permitido."]);
?>
