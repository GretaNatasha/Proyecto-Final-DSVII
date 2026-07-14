<?php 
require_once 'views/layout/header.php'; 

// Filtrar productos por estado para mostrarlos en pestañas separadas
$productos_activos = array_filter($productos, function($p) {
    return (int)$p['estado'] === 1;
});
$productos_inactivos = array_filter($productos, function($p) {
    return (int)$p['estado'] === 0;
});
?>

<style>
/* Estilos premium para el sistema de pestañas */
.tab-container {
    display: flex;
    gap: 12px;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 25px;
    margin-top: 15px;
}
.tab-button {
    background: none;
    border: none;
    padding: 12px 20px;
    font-size: 15px;
    font-weight: 600;
    color: var(--text-light);
    cursor: pointer;
    position: relative;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
}
.tab-button:hover {
    color: var(--primary-color);
}
.tab-button.active {
    color: var(--primary-color);
}
.tab-button.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2.5px;
    background-color: var(--primary-color);
    border-radius: 2px;
}
.tab-badge {
    font-size: 11px;
    padding: 2px 7px;
    border-radius: 12px;
    background: #e5e7eb;
    color: var(--text-main);
    font-weight: 700;
    transition: var(--transition);
}
.tab-button.active .tab-badge {
    background: var(--primary-color);
    color: white;
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
    animation: tabSlideIn 0.3s ease-out forwards;
}
@keyframes tabSlideIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="card p-20">
    <div class="flex-between">
        <h2>Catálogo de Productos</h2>
        <?php if ($_SESSION['rol'] === 'Administrador'): ?>
            <button class="btn btn-primary" id="btn-new-product"><i class='bx bx-plus'></i> Nuevo Producto</button>
        <?php endif; ?>
    </div>

    <!-- Mensajes de Notificación -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-top: 20px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <i class='bx bx-check-circle' style="font-size: 20px;"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-top: 20px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <i class='bx bx-error-circle' style="font-size: 20px;"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Contenedor de Pestañas -->
    <div class="tab-container">
        <button class="tab-button active" onclick="switchTab('activos', this)">
            <i class='bx bx-check-circle'></i> Activos 
            <span class="tab-badge"><?= count($productos_activos) ?></span>
        </button>
        <button class="tab-button" onclick="switchTab('inactivos', this)">
            <i class='bx bx-block'></i> Inactivos 
            <span class="tab-badge" style="background-color: #fee2e2; color: #991b1b;"><?= count($productos_inactivos) ?></span>
        </button>
    </div>

    <!-- Pestaña: Productos Activos -->
    <div id="tab-activos" class="tab-content active">
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Costo</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Stock Mínimo</th>
                    <th>Estado</th>
                    <?php if ($_SESSION['rol'] === 'Administrador'): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($productos_activos)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-light); padding: 30px;">
                            <i class='bx bx-info-circle' style="font-size: 24px; vertical-align: middle; margin-right: 6px;"></i> No hay productos activos registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($productos_activos as $p): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                        <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                        <td>$<?= number_format($p['costo'], 2) ?></td>
                        <td>$<?= number_format($p['precio'], 2) ?></td>
                        <td>
                            <?php if($p['stock'] <= $p['stock_minimo']): ?>
                                <span class="badge badge-danger"><?= $p['stock'] ?></span>
                            <?php else: ?>
                                <span class="badge badge-success"><?= $p['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['stock_minimo']) ?></td>
                        <td><span class="badge badge-success">Activo</span></td>
                        <?php if ($_SESSION['rol'] === 'Administrador'): ?>
                            <td>
                                <button class="btn btn-edit" onclick='openEditProductModal(<?= json_encode($p) ?>)'>
                                    <i class='bx bx-edit-alt'></i> Editar
                                </button>
                                <a href="index.php?action=producto_delete&id=<?= $p['id'] ?>" class="btn btn-deactivate" onclick="return confirm('¿Está seguro de que desea desactivar este producto?');">
                                    <i class='bx bx-power-off'></i> Desactivar
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pestaña: Productos Inactivos -->
    <div id="tab-inactivos" class="tab-content">
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Costo</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Stock Mínimo</th>
                    <th>Estado</th>
                    <?php if ($_SESSION['rol'] === 'Administrador'): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($productos_inactivos)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-light); padding: 30px;">
                            <i class='bx bx-info-circle' style="font-size: 24px; vertical-align: middle; margin-right: 6px;"></i> No hay productos inactivos registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($productos_inactivos as $p): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                        <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                        <td>$<?= number_format($p['costo'], 2) ?></td>
                        <td>$<?= number_format($p['precio'], 2) ?></td>
                        <td>
                            <?php if($p['stock'] <= $p['stock_minimo']): ?>
                                <span class="badge badge-danger"><?= $p['stock'] ?></span>
                            <?php else: ?>
                                <span class="badge badge-success"><?= $p['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['stock_minimo']) ?></td>
                        <td><span class="badge badge-danger">Inactivo</span></td>
                        <?php if ($_SESSION['rol'] === 'Administrador'): ?>
                            <td>
                                <button class="btn btn-edit" onclick='openEditProductModal(<?= json_encode($p) ?>)'>
                                    <i class='bx bx-edit-alt'></i> Editar
                                </button>
                                <a href="index.php?action=producto_delete&id=<?= $p['id'] ?>" class="btn btn-activate" onclick="return confirm('¿Está seguro de que desea activar este producto?');">
                                    <i class='bx bx-play'></i> Activar
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($_SESSION['rol'] === 'Administrador'): ?>
<!-- Modal para Crear/Editar Producto -->
<div id="productoModal" class="modal">
    <div class="modal-content" style="width: 520px; max-width: 90%;">
        <div class="modal-header info" id="modalProductHeader">
            <h2 id="modalProductTitle"><i class='bx bx-plus'></i> Nuevo Producto</h2>
            <span class="close-modal close-product-modal">&times;</span>
        </div>
        <form action="index.php?action=producto_store" method="POST" id="productoForm">
            <div class="modal-body">
                <input type="hidden" name="id" id="prod_id">
                
                <div class="form-group">
                    <label for="prod_codigo">Código de Barras / Referencia:</label>
                    <input type="text" name="codigo" id="prod_codigo" class="form-control" required placeholder="Ej. P006">
                </div>
                
                <div class="form-group">
                    <label for="prod_nombre">Nombre del Producto:</label>
                    <input type="text" name="nombre" id="prod_nombre" class="form-control" required placeholder="Ej. Mouse Inalámbrico">
                </div>
                
                <div class="form-group">
                    <label for="prod_descripcion">Descripción corta (opcional):</label>
                    <textarea name="descripcion" id="prod_descripcion" class="form-control" rows="2" placeholder="Escribe detalles del producto..."></textarea>
                </div>
                
                <div class="row-flex" style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label for="prod_costo" style="font-weight: 500; display: block; margin-bottom: 8px;">Costo ($):</label>
                        <input type="number" name="costo" id="prod_costo" class="form-control" required min="0" step="0.01" value="0.00">
                    </div>
                    <div style="flex: 1;">
                        <label for="prod_precio" style="font-weight: 500; display: block; margin-bottom: 8px;">Precio de Venta ($):</label>
                        <input type="number" name="precio" id="prod_precio" class="form-control" required min="0" step="0.01" value="0.00">
                    </div>
                </div>
                
                <div class="row-flex" style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label for="prod_stock" style="font-weight: 500; display: block; margin-bottom: 8px;">Stock Actual:</label>
                        <input type="number" name="stock" id="prod_stock" class="form-control" required min="0" value="0">
                    </div>
                    <div style="flex: 1;">
                        <label for="prod_stock_minimo" style="font-weight: 500; display: block; margin-bottom: 8px;">Stock Mínimo:</label>
                        <input type="number" name="stock_minimo" id="prod_stock_minimo" class="form-control" required min="0" value="5">
                    </div>
                </div>
                
                <div class="form-group" id="group_estado_prod" style="display:none;">
                    <label for="prod_estado">Estado:</label>
                    <select name="estado" id="prod_estado" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-product-modal-btn">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("productoModal");
    const closeBtns = document.querySelectorAll(".close-product-modal, .close-product-modal-btn");
    const form = document.getElementById("productoForm");
    
    // Cerrar modal
    closeBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    // Abrir para crear
    const btnNew = document.getElementById("btn-new-product");
    if(btnNew) {
        btnNew.addEventListener("click", () => {
            form.reset();
            document.getElementById("prod_id").value = "";
            document.getElementById("modalProductTitle").innerHTML = "<i class='bx bx-plus'></i> Nuevo Producto";
            document.getElementById("group_estado_prod").style.display = "none";
            modal.style.display = "block";
        });
    }

    // Abrir para editar
    window.openEditProductModal = function(prod) {
        form.reset();
        document.getElementById("prod_id").value = prod.id;
        document.getElementById("prod_codigo").value = prod.codigo;
        document.getElementById("prod_nombre").value = prod.nombre;
        document.getElementById("prod_descripcion").value = prod.descripcion || "";
        document.getElementById("prod_costo").value = prod.costo;
        document.getElementById("prod_precio").value = prod.precio;
        document.getElementById("prod_stock").value = prod.stock;
        document.getElementById("prod_stock_minimo").value = prod.stock_minimo;
        document.getElementById("prod_estado").value = prod.estado !== undefined ? prod.estado : 1;
        
        document.getElementById("modalProductTitle").innerHTML = "<i class='bx bx-edit-alt'></i> Editar Producto";
        document.getElementById("group_estado_prod").style.display = "block";
        modal.style.display = "block";
    };

    // Validación estricta del lado del cliente
    form.addEventListener("submit", (e) => {
        const costo = parseFloat(document.getElementById("prod_costo").value) || 0;
        const precio = parseFloat(document.getElementById("prod_precio").value) || 0;
        
        if (costo > precio) {
            e.preventDefault();
            alert("El costo del producto ($" + costo + ") no puede ser mayor que su precio de venta ($" + precio + "). ¡Por favor verifique los montos!");
        }
    });
});
</script>
<?php endif; ?>

<script>
// Función para alternar pestañas
function switchTab(tabName, element) {
    // Desactivar todos los contenidos de pestaña y botones
    document.querySelectorAll(".tab-content").forEach(tc => tc.classList.remove("active"));
    document.querySelectorAll(".tab-button").forEach(tb => tb.classList.remove("active"));
    
    // Activar el seleccionado
    document.getElementById("tab-" + tabName).classList.add("active");
    element.classList.add("active");
    
    // Guardar pestaña activa en sessionStorage para recordar el estado al recargar la página
    sessionStorage.setItem("activeProductTab", tabName);
}

// Cargar la pestaña activa al iniciar
document.addEventListener("DOMContentLoaded", () => {
    const savedTab = sessionStorage.getItem("activeProductTab");
    if (savedTab) {
        const btn = Array.from(document.querySelectorAll(".tab-button")).find(b => 
            b.getAttribute("onclick").includes(savedTab)
        );
        if (btn) {
            btn.click();
        }
    }
});
</script>

<?php require_once 'views/layout/footer.php'; ?>
