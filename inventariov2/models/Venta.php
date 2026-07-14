<?php
class Venta {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrarVenta($cliente_id, $usuario_id, $subtotal, $itbms, $descuento, $total, $detalles) {
        try {
            // Iniciar Transacción (Robustez y atomicidad)
            $this->conn->beginTransaction();

            // 1. Crear Venta Cabecera
            $query = "CALL sp_crear_venta(:cliente_id, :usuario_id, :subtotal, :itbms, :descuento, :total, @p_venta_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cliente_id", $cliente_id);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->bindParam(":subtotal", $subtotal);
            $stmt->bindParam(":itbms", $itbms);
            $stmt->bindParam(":descuento", $descuento);
            $stmt->bindParam(":total", $total);
            $stmt->execute();

            // Obtener el ID de venta generado
            $stmt_id = $this->conn->query("SELECT @p_venta_id AS venta_id");
            $row = $stmt_id->fetch(PDO::FETCH_ASSOC);
            $venta_id = $row['venta_id'];

            if (!$venta_id) {
                throw new Exception("Error al obtener el ID de la venta generada.");
            }

            // 2. Insertar Detalles de Venta
            $query_detalle = "CALL sp_crear_detalle_venta(:venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
            $stmt_det = $this->conn->prepare($query_detalle);

            foreach ($detalles as $det) {
                $stmt_det->bindParam(":venta_id", $venta_id);
                $stmt_det->bindParam(":producto_id", $det['producto_id']);
                $stmt_det->bindParam(":cantidad", $det['cantidad']);
                $stmt_det->bindParam(":precio_unitario", $det['precio']);
                $stmt_det->bindParam(":subtotal", $det['subtotal']);
                
                // execute podría lanzar una excepción si falla el SP (por ejemplo, si no hay stock)
                $stmt_det->execute();
            }

            // Confirmar Transacción
            $this->conn->commit();
            return $venta_id;

        } catch (Exception $e) {
            // Revertir en caso de error para que no descuente stock si la venta falla
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function reporte() {
        $query = "CALL sp_reporte_ventas()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function detalleFactura($venta_id) {
        // 1. Obtener la cabecera de la factura
        $query_cabecera = "SELECT 
            v.id AS venta_id,
            c.nombre AS cliente_nombre,
            c.documento AS cliente_documento,
            c.telefono AS cliente_telefono,
            u.username AS vendedor,
            v.subtotal,
            v.itbms,
            v.descuento,
            v.total,
            v.fecha
        FROM ventas v
        JOIN clientes c ON v.cliente_id = c.id
        JOIN usuarios u ON v.usuario_id = u.id
        WHERE v.id = :venta_id";
        
        $stmt_c = $this->conn->prepare($query_cabecera);
        $stmt_c->bindParam(":venta_id", $venta_id, PDO::PARAM_INT);
        $stmt_c->execute();
        $cabecera = $stmt_c->fetch(PDO::FETCH_ASSOC);
        
        if (!$cabecera) {
            return null;
        }
        
        // 2. Obtener los detalles de la factura (productos)
        $query_detalles = "CALL sp_detalle_factura(:venta_id)";
        $stmt_d = $this->conn->prepare($query_detalles);
        $stmt_d->bindParam(":venta_id", $venta_id, PDO::PARAM_INT);
        $stmt_d->execute();
        // Cerrar cursor para evitar conflictos de conexiones abiertas
        $detalles = $stmt_d->fetchAll(PDO::FETCH_ASSOC);
        $stmt_d->closeCursor();
        
        return [
            "venta_id" => $cabecera['venta_id'],
            "cliente" => [
                "nombre" => $cabecera['cliente_nombre'],
                "documento" => $cabecera['cliente_documento'],
                "telefono" => $cabecera['cliente_telefono']
            ],
            "vendedor" => $cabecera['vendedor'],
            "subtotal" => $cabecera['subtotal'],
            "itbms" => $cabecera['itbms'],
            "descuento" => $cabecera['descuento'],
            "total" => $cabecera['total'],
            "fecha" => $cabecera['fecha'],
            "detalles" => $detalles
        ];
    }
}
?>
