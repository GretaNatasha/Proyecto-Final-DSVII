<?php
require_once 'models/Usuario.php';
require_once 'config/Database.php';

class UserController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
        if ($_SESSION['rol'] !== 'Administrador') {
            // Guardar mensaje de error en sesión
            $_SESSION['error'] = "Acceso denegado. Se requieren permisos de Administrador.";
            header("Location: index.php?action=dashboard");
            exit;
        }
    }

    public function index() {
        $this->checkAuth();
        
        $db = (new Database())->getConnection();
        $usuarioModel = new Usuario($db);
        
        $usuarios = $usuarioModel->obtenerTodos();
        $roles = $usuarioModel->obtenerRoles();
        
        require_once 'views/usuarios/index.php';
    }

    public function store() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;
            $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

            // Validación estricta del lado del servidor (Robustez)
            if (empty($username)) {
                $_SESSION['error'] = "El nombre de usuario es obligatorio.";
                header("Location: index.php?action=usuarios");
                exit;
            }

            if ($role_id <= 0) {
                $_SESSION['error'] = "Debe seleccionar un rol válido.";
                header("Location: index.php?action=usuarios");
                exit;
            }

            $db = (new Database())->getConnection();
            $usuarioModel = new Usuario($db);

            if (empty($id)) {
                // Modo: Crear
                if (empty($password) || strlen($password) < 4) {
                    $_SESSION['error'] = "La contraseña es obligatoria y debe tener al menos 4 caracteres.";
                    header("Location: index.php?action=usuarios");
                    exit;
                }

                if ($usuarioModel->existeUsername($username)) {
                    $_SESSION['error'] = "El nombre de usuario '$username' ya está registrado.";
                    header("Location: index.php?action=usuarios");
                    exit;
                }

                if ($usuarioModel->crear($username, $password, $role_id)) {
                    $_SESSION['success'] = "Usuario creado exitosamente.";
                } else {
                    $_SESSION['error'] = "Error al crear el usuario.";
                }
            } else {
                // Modo: Editar
                if ($usuarioModel->existeUsername($username, $id)) {
                    $_SESSION['error'] = "El nombre de usuario '$username' ya está registrado por otro usuario.";
                    header("Location: index.php?action=usuarios");
                    exit;
                }

                // Evitar que el administrador se desactive a sí mismo
                if ($id == $_SESSION['user_id'] && $estado == 0) {
                    $_SESSION['error'] = "No puedes desactivar tu propia cuenta.";
                    header("Location: index.php?action=usuarios");
                    exit;
                }

                if (!empty($password) && strlen($password) < 4) {
                    $_SESSION['error'] = "La nueva contraseña debe tener al menos 4 caracteres.";
                    header("Location: index.php?action=usuarios");
                    exit;
                }

                if ($usuarioModel->actualizar($id, $username, $password, $role_id, $estado)) {
                    $_SESSION['success'] = "Usuario actualizado exitosamente.";
                } else {
                    $_SESSION['error'] = "Error al actualizar el usuario.";
                }
            }
        }
        
        header("Location: index.php?action=usuarios");
        exit;
    }

    public function delete() {
        $this->checkAuth();
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID de usuario inválido.";
            header("Location: index.php?action=usuarios");
            exit;
        }

        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "No puedes desactivar tu propia cuenta.";
            header("Location: index.php?action=usuarios");
            exit;
        }

        $db = (new Database())->getConnection();
        $usuarioModel = new Usuario($db);
        
        if ($usuarioModel->eliminar($id)) {
            $_SESSION['success'] = "Usuario desactivado exitosamente.";
        } else {
            $_SESSION['error'] = "Error al desactivar el usuario.";
        }
        
        header("Location: index.php?action=usuarios");
        exit;
    }
}
?>
