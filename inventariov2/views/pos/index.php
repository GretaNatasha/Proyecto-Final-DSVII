<?php require_once 'views/layout/header.php'; ?>

<!-- Barra de Guía de Pasos en la parte superior -->
<div class="pos-top-guide">
    <div class="guide-item active-step">
        <span class="guide-number step-1-bg">1</span>
        <div class="guide-text">
            <strong>Paso 1: Cliente</strong>
            <p>Busca y selecciona el cliente receptor.</p>
        </div>
    </div>
    <div class="guide-item active-step">
        <span class="guide-number step-2-bg">2</span>
        <div class="guide-text">
            <strong>Paso 2: Productos</strong>
            <p>Busca en el catálogo y haz clic en (+) para añadir.</p>
        </div>
    </div>
    <div class="guide-item active-step">
        <span class="guide-number step-3-bg">3</span>
        <div class="guide-text">
            <strong>Paso 3: Cantidad</strong>
            <p>Ajusta unidades con (+/-) en el detalle.</p>
        </div>
    </div>
    <div class="guide-item active-step">
        <span class="guide-number step-4-bg">4</span>
        <div class="guide-text">
            <strong>Paso 4: Cobro</strong>
            <p>Ingresa descuento y procesa la venta.</p>
        </div>
    </div>
</div>

<div class="pos-wrapper">
    <!-- Panel Izquierdo: Selección (Cliente y Catálogo) -->
    <div class="pos-left-panel">
        <!-- Paso 1: Selección de Cliente -->
        <div class="billing-card client-section step-1-highlight">
            <h3><span class="badge-step-inline step-1">1</span> Cliente</h3>
            <div style="display: flex; gap: 15px; align-items: center;">
                <input type="text" id="buscar_cliente" class="form-control client-search" placeholder="🔍 Buscar cliente por nombre o documento..." style="flex: 1; margin: 0;">
                <select id="cliente_id" class="form-control" style="flex: 1; margin: 0;">
                    <?php foreach($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?> (<?= htmlspecialchars($c['documento']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Paso 2: Catálogo de Productos Compacto -->
        <div class="pos-catalog-panel step-2-highlight">
            <div class="panel-header">
                <h2><span class="badge-step-inline step-2">2</span> Catálogo de Productos</h2>
                <div class="search-box-wrapper">
                    <i class='bx bx-search search-icon'></i>
                    <input type="text" id="buscar_producto" class="form-control search-input" placeholder="Buscar por nombre o código...">
                </div>
            </div>
            
            <div class="table-responsive-wrapper">
                <table class="table catalog-list-table">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Código</th>
                            <th>Descripción</th>
                            <th style="width: 100px; text-align: right;">Precio</th>
                            <th style="width: 100px; text-align: center;">Stock</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($productos as $p): ?>
                            <?php if ($p['stock'] > 0): ?>
                                <tr class="catalog-item-row" 
                                    data-id="<?= $p['id'] ?>"
                                    data-stock="<?= $p['stock'] ?>"
                                    data-nombre="<?= htmlspecialchars(strtolower($p['nombre'])) ?>"
                                    data-codigo="<?= htmlspecialchars(strtolower($p['codigo'])) ?>">
                                    <td class="font-code"><?= htmlspecialchars($p['codigo']) ?></td>
                                    <td class="font-bold"><?= htmlspecialchars($p['nombre']) ?></td>
                                    <td style="text-align: right; font-weight: 600; color: var(--primary-color);">$<?= number_format($p['precio'], 2) ?></td>
                                    <td style="text-align: center;">
                                        <span class="stock-badge <?= $p['stock'] <= $p['stock_minimo'] ? 'warning' : 'success' ?>">
                                            <?= $p['stock'] ?> disp.
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-add-to-cart-quick" 
                                                onclick="addProductFromCard('<?= $p['id'] ?>', '<?= htmlspecialchars($p['nombre']) ?>', <?= $p['precio'] ?>, <?= $p['stock'] ?>)">
                                            <i class='bx bx-plus'></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Panel Derecho: Carrito de Compras y Totales -->
    <div class="pos-right-panel">
        <!-- Paso 3: Detalle de Venta (Carrito) -->
        <div class="billing-card cart-section step-3-highlight">
            <h3><span class="badge-step-inline step-3">3</span> Detalle de la Venta</h3>
            <div class="cart-table-wrapper">
                <table class="table pos-cart-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="width: 90px; text-align: center;">Cant.</th>
                            <th style="text-align: right;">Total</th>
                            <th style="width: 35px;"></th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <!-- Items del carrito -->
                    </tbody>
                </table>
                <div id="cart-empty-message" class="cart-empty-msg">
                    <i class='bx bx-cart-alt'></i>
                    <p>El carrito está vacío</p>
                </div>
            </div>
        </div>

        <!-- Paso 4: Totales y Cobro -->
        <div class="billing-card summary-section step-4-highlight">
            <div class="summary-line-step-header">
                <span><span class="badge-step-inline step-4">4</span> Totales y Caja</span>
            </div>
            <div class="summary-line">
                <span>Subtotal:</span>
                <span id="res_subtotal">$0.00</span>
            </div>
            <div class="summary-line inline-discount">
                <span>Descuento ($):</span>
                <input type="number" id="input_descuento" class="form-control-sm" value="0" min="0" step="0.01">
            </div>
            <div class="summary-line">
                <span>ITBMS (7%):</span>
                <span id="res_itbms">$0.00</span>
            </div>
            <div class="summary-line total-to-pay">
                <span>Total Neto:</span>
                <span id="res_total">$0.00</span>
            </div>
            <button id="btn-process-sale" class="btn btn-primary btn-process-checkout">
                <i class='bx bx-check-double'></i> Procesar Venta
            </button>
        </div>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
