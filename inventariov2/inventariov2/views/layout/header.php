<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Inventario</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo-details">
            <i class='bx bx-store-alt'></i>
            <span class="logo_name">MiniEmpresa</span>
        </div>
        <ul class="nav-links">
            <?php $action = $_GET['action'] ?? 'dashboard'; ?>
            <li><a href="index.php?action=dashboard" class="<?= $action == 'dashboard' ? 'active' : '' ?>"><i class='bx bx-grid-alt'></i><span class="links_name">Dashboard</span></a></li>
            <li><a href="index.php?action=pos" class="<?= $action == 'pos' ? 'active' : '' ?>"><i class='bx bx-cart'></i><span class="links_name">Punto de Venta</span></a></li>
            <li><a href="index.php?action=productos" class="<?= $action == 'productos' ? 'active' : '' ?>"><i class='bx bx-box'></i><span class="links_name">Productos</span></a></li>
            <li><a href="index.php?action=clientes" class="<?= $action == 'clientes' ? 'active' : '' ?>"><i class='bx bx-user'></i><span class="links_name">Clientes</span></a></li>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador'): ?>
            <li><a href="index.php?action=usuarios" class="<?= $action == 'usuarios' ? 'active' : '' ?>"><i class='bx bx-group'></i><span class="links_name">Usuarios</span></a></li>
            <li><a href="index.php?action=reportes" class="<?= $action == 'reportes' ? 'active' : '' ?>"><i class='bx bx-bar-chart-alt-2'></i><span class="links_name">Reportes</span></a></li>
            <?php endif; ?>
            <li class="log_out"><a href="index.php?action=logout"><i class='bx bx-log-out'></i><span class="links_name">Cerrar Sesión</span></a></li>
        </ul>
    </div>
    <section class="home-section">
        <nav>
            <div class="sidebar-button" style="display: flex; align-items: center; gap: 15px;">
                <i class='bx bx-menu sidebarBtn' style="font-size: 26px; cursor: pointer; color: var(--text-main); padding: 8px; border-radius: 50%; background: #f8fafc; border: 1px solid #e2e8f0; transition: var(--transition); display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);"></i>
                <span class="dashboard">Panel Principal</span>
            </div>
            <div class="profile-details">
                <span class="admin_name"><?= htmlspecialchars($_SESSION['username'] ?? '') ?> (<?= htmlspecialchars($_SESSION['rol'] ?? '') ?>)</span>
            </div>
        </nav>
        
        <!-- Reto: Contenedor para Alertas de Stock Mínimo -->
        <div id="stock-alerts-container" class="stock-alerts-container"></div>
        
        <div class="main-content">
