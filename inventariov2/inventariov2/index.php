<?php
// Configuración de cookies de sesión seguras para mitigar el secuestro de sesión
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
} else {
    session_set_cookie_params(0, '/; SameSite=Strict', '', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', true);
}
session_start();

// Enrutador Principal MVC
$action = $_GET['action'] ?? 'login';

switch ($action) {
    case 'login':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;

    case 'postLogin':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->postLogin();
        break;

    case 'logout':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'dashboard':
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;

    case 'pos':
        require_once 'controllers/SaleController.php';
        $controller = new SaleController();
        $controller->create();
        break;

    case 'pos_store':
        require_once 'controllers/SaleController.php';
        $controller = new SaleController();
        $controller->store();
        break;

    case 'reportes':
        require_once 'controllers/SaleController.php';
        $controller = new SaleController();
        $controller->reports();
        break;

    case 'productos':
        require_once 'controllers/ProductController.php';
        $controller = new ProductController();
        $controller->index();
        break;

    case 'producto_store':
        require_once 'controllers/ProductController.php';
        $controller = new ProductController();
        $controller->store();
        break;

    case 'producto_delete':
        require_once 'controllers/ProductController.php';
        $controller = new ProductController();
        $controller->delete();
        break;

    case 'clientes':
        require_once 'controllers/ClientController.php';
        $controller = new ClientController();
        $controller->index();
        break;

    case 'cliente_store':
        require_once 'controllers/ClientController.php';
        $controller = new ClientController();
        $controller->store();
        break;

    case 'cliente_delete':
        require_once 'controllers/ClientController.php';
        $controller = new ClientController();
        $controller->delete();
        break;

    case 'usuarios':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        $controller->index();
        break;

    case 'usuario_store':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        $controller->store();
        break;

    case 'usuario_delete':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        $controller->delete();
        break;

    default:
        // Ruta por defecto si no existe la acción
        header("Location: index.php?action=login");
        break;
}
?>
