-- Script de la Base de Datos: inventario_db
-- Creado para: Sistema de Gestión de Inventario y Facturación

CREATE DATABASE IF NOT EXISTS inventario_db;
USE inventario_db;

-- 1. Tabla de Roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Insertar roles por defecto
INSERT IGNORE INTO roles (id, nombre) VALUES (1, 'Administrador'), (2, 'Vendedor');

-- 2. Tabla de Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Hasheado (bcrypt)
    role_id INT NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- Insertar usuario admin por defecto (Contraseña: admin123)
-- El hash generado es de 'admin123' usando PASSWORD_BCRYPT
INSERT IGNORE INTO usuarios (id, username, password, role_id) VALUES 
(1, 'admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 1),
(2, 'vendedor1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 2);

-- 3. Tabla de Productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    costo DECIMAL(10,2) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    estado BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar algunos productos de prueba
INSERT IGNORE INTO productos (id, codigo, nombre, costo, precio, stock, stock_minimo) VALUES
(1, 'P001', 'Laptop Dell XPS 13', 800.00, 1200.00, 10, 3),
(2, 'P002', 'Mouse Inalámbrico Logitech', 10.00, 25.00, 50, 10),
(3, 'P003', 'Teclado Mecánico', 30.00, 60.00, 15, 5),
(4, 'P004', 'Monitor Samsung 24"', 100.00, 180.00, 8, 4),
(5, 'P005', 'Cable HDMI 2m', 2.00, 10.00, 100, 20);

-- 4. Tabla de Clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    documento VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100),
    telefono VARCHAR(20),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO clientes (id, documento, nombre, correo, telefono) VALUES
(1, '8-123-4567', 'Cliente Público en General', 'info@empresa.com', '1234-5678');

-- 5. Tabla de Ventas
CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    usuario_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    itbms DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- 6. Tabla de Detalle de Ventas
CREATE TABLE IF NOT EXISTS detalle_ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
);

-- ==========================================================
-- PROCEDIMIENTOS ALMACENADOS
-- ==========================================================
DELIMITER //

-- SP para buscar usuario en el Login
CREATE PROCEDURE IF NOT EXISTS sp_login_usuario(IN p_username VARCHAR(50))
BEGIN
    SELECT u.id, u.username, u.password, u.role_id, r.nombre AS rol
    FROM usuarios u
    JOIN roles r ON u.role_id = r.id
    WHERE u.username = p_username AND u.estado = 1;
END //

-- SP para Registrar Nueva Venta (Cabecera) y retorna el ID generado
CREATE PROCEDURE IF NOT EXISTS sp_crear_venta(
    IN p_cliente_id INT,
    IN p_usuario_id INT,
    IN p_subtotal DECIMAL(10,2),
    IN p_itbms DECIMAL(10,2),
    IN p_descuento DECIMAL(10,2),
    IN p_total DECIMAL(10,2),
    OUT p_venta_id INT
)
BEGIN
    INSERT INTO ventas (cliente_id, usuario_id, subtotal, itbms, descuento, total)
    VALUES (p_cliente_id, p_usuario_id, p_subtotal, p_itbms, p_descuento, p_total);
    
    SET p_venta_id = LAST_INSERT_ID();
END //

-- SP para Registrar Detalle de Venta y Descontar Stock
CREATE PROCEDURE IF NOT EXISTS sp_crear_detalle_venta(
    IN p_venta_id INT,
    IN p_producto_id INT,
    IN p_cantidad INT,
    IN p_precio_unitario DECIMAL(10,2),
    IN p_subtotal DECIMAL(10,2)
)
BEGIN
    DECLARE v_stock_actual INT;
    
    -- Obtener stock actual
    SELECT stock INTO v_stock_actual FROM productos WHERE id = p_producto_id;
    
    -- Validar si hay stock suficiente
    IF v_stock_actual < p_cantidad THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock insuficiente para el producto';
    ELSE
        -- Insertar el detalle
        INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal)
        VALUES (p_venta_id, p_producto_id, p_cantidad, p_precio_unitario, p_subtotal);
        
        -- Actualizar el stock
        UPDATE productos SET stock = stock - p_cantidad WHERE id = p_producto_id;
    END IF;
END //

-- SP para Listar Productos Activos
CREATE PROCEDURE IF NOT EXISTS sp_obtener_productos()
BEGIN
    SELECT id, codigo, nombre, descripcion, costo, precio, stock, stock_minimo
    FROM productos
    WHERE estado = 1;
END //

-- SP para Buscar un Producto por ID
CREATE PROCEDURE IF NOT EXISTS sp_obtener_producto_por_id(IN p_id INT)
BEGIN
    SELECT * FROM productos WHERE id = p_id;
END //

-- SP para Obtener Productos con Alerta de Stock Mínimo (RETO)
CREATE PROCEDURE IF NOT EXISTS sp_obtener_alertas_stock()
BEGIN
    SELECT id, codigo, nombre, stock, stock_minimo 
    FROM productos 
    WHERE stock <= stock_minimo AND estado = 1;
END //

-- SP para Obtener Reporte de Ventas (Para API REST)
CREATE PROCEDURE IF NOT EXISTS sp_reporte_ventas()
BEGIN
    SELECT 
        v.id AS venta_id,
        c.nombre AS cliente,
        u.username AS vendedor,
        v.subtotal,
        v.itbms,
        v.descuento,
        v.total,
        v.fecha
    FROM ventas v
    JOIN clientes c ON v.cliente_id = c.id
    JOIN usuarios u ON v.usuario_id = u.id
    ORDER BY v.fecha DESC;
END //

-- SP para Obtener Detalle de una Factura
CREATE PROCEDURE IF NOT EXISTS sp_detalle_factura(IN p_venta_id INT)
BEGIN
    SELECT 
        d.id,
        p.codigo,
        p.nombre AS producto,
        d.cantidad,
        d.precio_unitario,
        d.subtotal
    FROM detalle_ventas d
    JOIN productos p ON d.producto_id = p.id
    WHERE d.venta_id = p_venta_id;
END //

-- SP para Obtener Todos los Usuarios
CREATE PROCEDURE IF NOT EXISTS sp_obtener_usuarios()
BEGIN
    SELECT u.id, u.username, u.role_id, r.nombre AS rol, u.estado
    FROM usuarios u
    JOIN roles r ON u.role_id = r.id
    ORDER BY u.username ASC;
END //

-- SP para Obtener Usuario por ID
CREATE PROCEDURE IF NOT EXISTS sp_obtener_usuario_por_id(IN p_id INT)
BEGIN
    SELECT u.id, u.username, u.role_id, r.nombre AS rol, u.estado
    FROM usuarios u
    JOIN roles r ON u.role_id = r.id
    WHERE u.id = p_id;
END //

-- SP para Crear Usuario
CREATE PROCEDURE IF NOT EXISTS sp_crear_usuario(
    IN p_username VARCHAR(50),
    IN p_password VARCHAR(255),
    IN p_role_id INT
)
BEGIN
    INSERT INTO usuarios (username, password, role_id, estado)
    VALUES (p_username, p_password, p_role_id, TRUE);
END //

-- SP para Actualizar Usuario
CREATE PROCEDURE IF NOT EXISTS sp_actualizar_usuario(
    IN p_id INT,
    IN p_username VARCHAR(50),
    IN p_password VARCHAR(255),
    IN p_role_id INT,
    IN p_estado BOOLEAN
)
BEGIN
    IF p_password IS NOT NULL AND p_password != '' THEN
        UPDATE usuarios 
        SET username = p_username, password = p_password, role_id = p_role_id, estado = p_estado
        WHERE id = p_id;
    ELSE
        UPDATE usuarios 
        SET username = p_username, role_id = p_role_id, estado = p_estado
        WHERE id = p_id;
    END IF;
END //

-- SP para Eliminar (Desactivar) Usuario
CREATE PROCEDURE IF NOT EXISTS sp_eliminar_usuario(IN p_id INT)
BEGIN
    UPDATE usuarios SET estado = 0 WHERE id = p_id;
END //

-- SP para Verificar si existe un username
CREATE PROCEDURE IF NOT EXISTS sp_existe_username(IN p_username VARCHAR(50), IN p_exclude_id INT)
BEGIN
    SELECT COUNT(*) as total 
    FROM usuarios 
    WHERE username = p_username AND (p_exclude_id = 0 OR id != p_exclude_id);
END //

-- SP para Obtener Roles
CREATE PROCEDURE IF NOT EXISTS sp_obtener_roles()
BEGIN
    SELECT id, nombre FROM roles ORDER BY nombre ASC;
END //

-- SP para Crear Producto
CREATE PROCEDURE IF NOT EXISTS sp_crear_producto(
    IN p_codigo VARCHAR(50),
    IN p_nombre VARCHAR(150),
    IN p_descripcion TEXT,
    IN p_costo DECIMAL(10,2),
    IN p_precio DECIMAL(10,2),
    IN p_stock INT,
    IN p_stock_minimo INT
)
BEGIN
    INSERT INTO productos (codigo, nombre, descripcion, costo, precio, stock, stock_minimo, estado)
    VALUES (p_codigo, p_nombre, p_descripcion, p_costo, p_precio, p_stock, p_stock_minimo, TRUE);
END //

-- SP para Actualizar Producto
CREATE PROCEDURE IF NOT EXISTS sp_actualizar_producto(
    IN p_id INT,
    IN p_codigo VARCHAR(50),
    IN p_nombre VARCHAR(150),
    IN p_descripcion TEXT,
    IN p_costo DECIMAL(10,2),
    IN p_precio DECIMAL(10,2),
    IN p_stock INT,
    IN p_stock_minimo INT,
    IN p_estado BOOLEAN
)
BEGIN
    UPDATE productos
    SET codigo = p_codigo,
        nombre = p_nombre,
        descripcion = p_descripcion,
        costo = p_costo,
        precio = p_precio,
        stock = p_stock,
        stock_minimo = p_stock_minimo,
        estado = p_estado
    WHERE id = p_id;
END //

-- SP para Desactivar Producto (Borrado lógico)
CREATE PROCEDURE IF NOT EXISTS sp_eliminar_producto(IN p_id INT)
BEGIN
    UPDATE productos SET estado = FALSE WHERE id = p_id;
END //

-- SP para Verificar Código de Producto
CREATE PROCEDURE IF NOT EXISTS sp_existe_codigo_producto(IN p_codigo VARCHAR(50), IN p_exclude_id INT)
BEGIN
    SELECT COUNT(*) as total 
    FROM productos 
    WHERE codigo = p_codigo AND (p_exclude_id = 0 OR id != p_exclude_id);
END //

-- SP para Obtener Todos los Clientes
CREATE PROCEDURE IF NOT EXISTS sp_obtener_clientes()
BEGIN
    SELECT id, documento, nombre, correo, telefono, fecha_registro
    FROM clientes
    ORDER BY nombre ASC;
END //

-- SP para Obtener Cliente por ID
CREATE PROCEDURE IF NOT EXISTS sp_obtener_cliente_por_id(IN p_id INT)
BEGIN
    SELECT id, documento, nombre, correo, telefono, fecha_registro
    FROM clientes
    WHERE id = p_id;
END //

-- SP para Crear Cliente
CREATE PROCEDURE IF NOT EXISTS sp_crear_cliente(
    IN p_documento VARCHAR(20),
    IN p_nombre VARCHAR(100),
    IN p_correo VARCHAR(100),
    IN p_telefono VARCHAR(20)
)
BEGIN
    INSERT INTO clientes (documento, nombre, correo, telefono)
    VALUES (p_documento, p_nombre, p_correo, p_telefono);
END //

-- SP para Actualizar Cliente
CREATE PROCEDURE IF NOT EXISTS sp_actualizar_cliente(
    IN p_id INT,
    IN p_documento VARCHAR(20),
    IN p_nombre VARCHAR(100),
    IN p_correo VARCHAR(100),
    IN p_telefono VARCHAR(20)
)
BEGIN
    UPDATE clientes
    SET documento = p_documento,
        nombre = p_nombre,
        correo = p_correo,
        telefono = p_telefono
    WHERE id = p_id;
END //

-- SP para Eliminar Cliente
CREATE PROCEDURE IF NOT EXISTS sp_eliminar_cliente(IN p_id INT)
BEGIN
    DELETE FROM clientes WHERE id = p_id;
END //

-- SP para Verificar Documento de Cliente
CREATE PROCEDURE IF NOT EXISTS sp_existe_documento_cliente(IN p_documento VARCHAR(20), IN p_exclude_id INT)
BEGIN
    SELECT COUNT(*) as total 
    FROM clientes 
    WHERE documento = p_documento AND (p_exclude_id = 0 OR id != p_exclude_id);
END //

-- SP para Obtener Todos los Productos (incluye inactivos)
CREATE PROCEDURE IF NOT EXISTS sp_obtener_todos_los_productos()
BEGIN
    SELECT id, codigo, nombre, descripcion, costo, precio, stock, stock_minimo, estado
    FROM productos
    ORDER BY nombre ASC;
END //

-- SP para Alternar el Estado (Activo/Inactivo) de un Producto
CREATE PROCEDURE IF NOT EXISTS sp_toggle_estado_producto(IN p_id INT)
BEGIN
    UPDATE productos SET estado = NOT estado WHERE id = p_id;
END //

DELIMITER ;


