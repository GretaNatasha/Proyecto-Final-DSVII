<?php require_once 'views/layout/header.php'; ?>

<div class="card p-20">
    <div class="flex-between">
        <h2>Lista de Clientes</h2>
        <button class="btn btn-primary" id="btn-new-client"><i class='bx bx-plus'></i> Nuevo Cliente</button>
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

    <table class="table mt-20">
        <thead>
            <tr>
                <th>Documento</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($clientes as $c): ?>
            <tr>
                <td><code><?= htmlspecialchars($c['documento']) ?></code></td>
                <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($c['correo'] ?: 'N/A') ?></td>
                <td><?= htmlspecialchars($c['telefono'] ?: 'N/A') ?></td>
                <td>
                    <button class="btn btn-edit" onclick='openEditClientModal(<?= json_encode($c) ?>)'>
                        <i class='bx bx-edit-alt'></i> Editar
                    </button>
                    <?php if ($_SESSION['rol'] === 'Administrador'): ?>
                        <a href="index.php?action=cliente_delete&id=<?= $c['id'] ?>" class="btn btn-deactivate" onclick="return confirm('¿Está seguro de que desea eliminar este cliente?');">
                            <i class='bx bx-trash'></i> Eliminar
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Crear/Editar Cliente -->
<div id="clienteModal" class="modal">
    <div class="modal-content" style="width: 480px; max-width: 90%;">
        <div class="modal-header info" id="modalClientHeader">
            <h2 id="modalClientTitle"><i class='bx bx-user-plus'></i> Nuevo Cliente</h2>
            <span class="close-modal close-client-modal">&times;</span>
        </div>
        <form action="index.php?action=cliente_store" method="POST" id="clienteForm">
            <div class="modal-body">
                <input type="hidden" name="id" id="cli_id">
                
                <div class="form-group">
                    <label for="cli_documento">Documento de Identidad / RUC:</label>
                    <input type="text" name="documento" id="cli_documento" class="form-control" required placeholder="Ej. 8-999-9999">
                </div>
                
                <div class="form-group">
                    <label for="cli_nombre">Nombre Completo:</label>
                    <input type="text" name="nombre" id="cli_nombre" class="form-control" required placeholder="Ej. Juan Pérez">
                </div>
                
                <div class="form-group">
                    <label for="cli_correo">Correo Electrónico:</label>
                    <input type="email" name="correo" id="cli_correo" class="form-control" placeholder="Ej. juan.perez@correo.com">
                </div>
                
                <div class="form-group">
                    <label for="cli_telefono">Teléfono / Celular:</label>
                    <input type="text" name="telefono" id="cli_telefono" class="form-control" placeholder="Ej. 6666-8888">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-client-modal-btn">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("clienteModal");
    const closeBtns = document.querySelectorAll(".close-client-modal, .close-client-modal-btn");
    const form = document.getElementById("clienteForm");
    
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
    const btnNew = document.getElementById("btn-new-client");
    if(btnNew) {
        btnNew.addEventListener("click", () => {
            form.reset();
            document.getElementById("cli_id").value = "";
            document.getElementById("modalClientTitle").innerHTML = "<i class='bx bx-user-plus'></i> Nuevo Cliente";
            modal.style.display = "block";
        });
    }

    // Abrir para editar
    window.openEditClientModal = function(cli) {
        form.reset();
        document.getElementById("cli_id").value = cli.id;
        document.getElementById("cli_documento").value = cli.documento;
        document.getElementById("cli_nombre").value = cli.nombre;
        document.getElementById("cli_correo").value = cli.correo || "";
        document.getElementById("cli_telefono").value = cli.telefono || "";
        
        document.getElementById("modalClientTitle").innerHTML = "<i class='bx bx-edit-alt'></i> Editar Cliente";
        modal.style.display = "block";
    };
});
</script>

<?php require_once 'views/layout/footer.php'; ?>
