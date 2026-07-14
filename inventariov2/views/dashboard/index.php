<?php require_once 'views/layout/header.php'; ?>

<!-- Mensaje de error si el usuario intentó acceder a una zona restringida -->
<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger" style="margin-bottom: 25px;">
        <i class='bx bx-error-circle' style="font-size: 20px;"></i> <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Banner de Bienvenida Premium -->
<div class="card p-20" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-purple) 100%); color: white; border: none; margin-bottom: 30px; position: relative; overflow: hidden; min-height: 180px; display: flex; align-items: center;">
    <div style="position: relative; z-index: 2; padding: 10px;">
        <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">¡Hola de nuevo, <?= htmlspecialchars($_SESSION['username']) ?>! 👋</h1>
        <p style="font-size: 15.5px; opacity: 0.9; max-width: 650px; line-height: 1.5;">
            Te damos la bienvenida al panel de administración de tu miniempresa. A continuación, tienes las principales métricas del negocio y una guía interactiva con los pasos para operar el sistema.
        </p>
    </div>
    <div style="position: absolute; right: 20px; bottom: -30px; font-size: 160px; opacity: 0.12; transform: rotate(-15deg); pointer-events: none;">
        <i class='bx bx-rocket'></i>
    </div>
</div>

<!-- Tarjetas de Métricas (KPIs) -->
<div class="dashboard-cards">
    <div class="card">
        <div class="card-content">
            <div class="number"><?= $totalProductos ?></div>
            <div class="card-name">Productos Activos</div>
        </div>
        <div class="icon-box" style="background: rgba(99, 102, 241, 0.08); color: var(--primary-color);">
            <i class='bx bx-box'></i>
        </div>
    </div>
    <div class="card">
        <div class="card-content">
            <div class="number"><?= $totalClientes ?></div>
            <div class="card-name">Clientes Registrados</div>
        </div>
        <div class="icon-box" style="background: rgba(16, 185, 129, 0.08); color: var(--secondary-color);">
            <i class='bx bx-user'></i>
        </div>
    </div>
    <div class="card">
        <div class="card-content">
            <div class="number" style="font-size: 20px; padding-top: 5px;"><?= htmlspecialchars($_SESSION['rol']) ?></div>
            <div class="card-name">Rol del Usuario</div>
        </div>
        <div class="icon-box" style="background: rgba(245, 158, 11, 0.08); color: #f59e0b;">
            <i class='bx bx-shield-quarter'></i>
        </div>
    </div>
</div>

<!-- Título de Sección -->
<h3 style="margin-top: 35px; margin-bottom: 20px; font-size: 18px; color: #1e293b; display: flex; align-items: center; gap: 8px; font-weight: 700;">
    <i class='bx bx-map-alt' style="font-size: 22px; color: var(--primary-color);"></i> Guía del Flujo del Sistema (Paso a Paso)
</h3>

<!-- Grid de Pasos -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
    <!-- Tarjeta Paso 1 -->
    <div class="card p-20" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid var(--primary-color);">
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <span style="font-size: 11.5px; font-weight: 700; background: #e5e7eb; color: #374151; padding: 4px 10px; border-radius: 20px;">PASO 1</span>
                <i class='bx bx-category' style="font-size: 24px; color: var(--primary-color);"></i>
            </div>
            <h4 style="font-size: 15.5px; margin-bottom: 10px; font-weight: 700; color: #0f172a;">Gestionar Catálogos</h4>
            <p style="font-size: 13.5px; color: var(--text-light); line-height: 1.5; margin-bottom: 15px;">
                Registra y edita la información de tus productos (precios, costos, stock mínimo) y de tus clientes en los módulos correspondientes.
            </p>
        </div>
        <a href="index.php?action=productos" class="btn btn-secondary w-100" style="padding: 8px 12px; font-size: 13px;">
            Ir a Catálogos <i class='bx bx-right-arrow-alt'></i>
        </a>
    </div>

    <!-- Tarjeta Paso 2 -->
    <div class="card p-20" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid var(--secondary-color);">
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <span style="font-size: 11.5px; font-weight: 700; background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px;">PASO 2</span>
                <i class='bx bx-cart' style="font-size: 24px; color: var(--secondary-color);"></i>
            </div>
            <h4 style="font-size: 15.5px; margin-bottom: 10px; font-weight: 700; color: #0f172a;">Procesar Ventas (POS)</h4>
            <p style="font-size: 13.5px; color: var(--text-light); line-height: 1.5; margin-bottom: 15px;">
                Carga productos en el Punto de Venta, calcula automáticamente el impuesto ITBMS (7%), aplica descuentos y registra la venta.
            </p>
        </div>
        <a href="index.php?action=pos" class="btn btn-secondary w-100" style="padding: 8px 12px; font-size: 13px; border-color: rgba(16, 185, 129, 0.2); background: rgba(16, 185, 129, 0.05); color: var(--secondary-color);">
            Ir al POS <i class='bx bx-right-arrow-alt'></i>
        </a>
    </div>

    <!-- Tarjeta Paso 3 -->
    <div class="card p-20" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid var(--accent-purple);">
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <span style="font-size: 11.5px; font-weight: 700; background: #f3e8ff; color: #6b21a8; padding: 4px 10px; border-radius: 20px;">PASO 3</span>
                <i class='bx bx-file-blank' style="font-size: 24px; color: var(--accent-purple);"></i>
            </div>
            <h4 style="font-size: 15.5px; margin-bottom: 10px; font-weight: 700; color: #0f172a;">Visualizar Facturas JSON</h4>
            <p style="font-size: 13.5px; color: var(--text-light); line-height: 1.5; margin-bottom: 15px;">
                Una vez procesada la compra en el Punto de Venta, el sistema generará y mostrará la factura en formato puramente estructurado JSON.
            </p>
        </div>
        <button class="btn btn-secondary w-100" style="padding: 8px 12px; font-size: 13px; cursor: default; pointer-events: none; opacity: 0.7;">
            Generado tras Venta
        </button>
    </div>

    <!-- Tarjeta Paso 4 -->
    <div class="card p-20" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid #f59e0b;">
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <span style="font-size: 11.5px; font-weight: 700; background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 20px;">PASO 4</span>
                <i class='bx bx-line-chart' style="font-size: 24px; color: #f59e0b;"></i>
            </div>
            <h4 style="font-size: 15.5px; margin-bottom: 10px; font-weight: 700; color: #0f172a;">Consultar Reportes</h4>
            <p style="font-size: 13.5px; color: var(--text-light); line-height: 1.5; margin-bottom: 15px;">
                El Administrador puede ver el historial consolidado de todas las ventas del negocio consumiendo la API REST estructurada en JSON.
            </p>
        </div>
        <?php if ($_SESSION['rol'] === 'Administrador'): ?>
            <a href="index.php?action=reportes" class="btn btn-secondary w-100" style="padding: 8px 12px; font-size: 13px; border-color: rgba(245, 158, 11, 0.2); background: rgba(245, 158, 11, 0.05); color: #b45309;">
                Ver Reportes <i class='bx bx-right-arrow-alt'></i>
            </a>
        <?php else: ?>
            <span style="display: block; text-align: center; font-size: 12.5px; color: var(--text-light); font-weight: 600; padding: 8px 0; border: 1px dashed #cbd5e1; border-radius: 8px;">
                Solo Administrador
            </span>
        <?php endif; ?>
    </div>
</div>

<!-- Cuadro Informativo de Testeo (Stock Mínimo) -->
<div class="card" style="border-left: 5px solid var(--danger-color); background: #fef2f2; margin-top: 30px; border-radius: 12px; box-shadow: var(--shadow);">
    <h4 style="color: #991b1b; display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 15px; font-weight: 700;">
        <i class='bx bx-bell' style="font-size: 20px;"></i> ¿Cómo probar las Alertas de Stock Mínimo en tiempo real?
    </h4>
    <p style="color: #7f1d1d; font-size: 13.5px; line-height: 1.5;">
        Ve al <strong>Punto de Venta</strong> y realiza una venta de algún producto de modo que la cantidad restante del producto sea igual o menor a su <strong>stock mínimo</strong>. El sistema disparará de inmediato una alerta emergente (un toast en la esquina superior derecha) obteniendo los datos de la base de datos a través de la API, de manera asíncrona y sin tener que refrescar la pantalla.
    </p>
</div>

<?php require_once 'views/layout/footer.php'; ?>
