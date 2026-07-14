<?php require_once 'views/layout/header.php'; ?>

<div class="card p-20">
    <div class="flex-between">
        <h2>Gestión de Usuarios</h2>
        <button class="btn btn-primary" id="btn-new-user"><i class='bx bx-user-plus'></i> Nuevo Usuario</button>
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
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($usuarios as $u): ?>
            <tr>
                <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                <td><?= htmlspecialchars($u['rol']) ?></td>
                <td>
                    <?php if($u['estado']): ?>
                        <span class="badge badge-success">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-edit" onclick='openEditUserModal(<?= json_encode($u) ?>)'>
                        <i class='bx bx-edit-alt'></i> Editar
                    </button>
                    <?php if($u['id'] != $_SESSION['user_id'] && $u['estado']): ?>
                        <a href="index.php?action=usuario_delete&id=<?= $u['id'] ?>" class="btn btn-deactivate" onclick="return confirm('¿Está seguro de que desea desactivar este usuario?');">
                            <i class='bx bx-power-off'></i> Desactivar
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Crear/Editar Usuario -->
<div id="usuarioModal" class="modal">
    <div class="modal-content" style="width: 480px; max-width: 90%;">
        <div class="modal-header info" id="modalUserHeader">
            <h2 id="modalUserTitle"><i class='bx bx-user-plus'></i> Nuevo Usuario</h2>
            <span class="close-modal close-user-modal">&times;</span>
        </div>
        <form action="index.php?action=usuario_store" method="POST" id="usuarioForm">
            <div class="modal-body">
                <input type="hidden" name="id" id="user_id">
                
                <div class="form-group">
                    <label for="user_username">Nombre de Usuario:</label>
                    <input type="text" name="username" id="user_username" class="form-control" required minlength="3" placeholder="Ej. jperez">
                </div>
                
                <div class="form-group">
                    <label for="user_password" id="label_password">Contraseña:</label>
                    <input type="password" name="password" id="user_password" class="form-control" minlength="4">
                    <small style="color: #6b7280; display: block; margin-top: 4px;" id="password_help">Mínimo 4 caracteres.</small>
                </div>
                
                <div class="form-group">
                    <label for="user_role_id">Rol asignado:</label>
                    <select name="role_id" id="user_role_id" class="form-control" required>
                        <option value="">-- Seleccionar Rol --</option>
                        <?php foreach($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" id="group_estado" style="display:none;">
                    <label for="user_estado">Estado del Usuario:</label>
                    <select name="estado" id="user_estado" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-user-modal-btn">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("usuarioModal");
    const closeBtns = document.querySelectorAll(".close-user-modal, .close-user-modal-btn");
    const form = document.getElementById("usuarioForm");
    
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
    document.getElementById("btn-new-user").addEventListener("click", () => {
        form.reset();
        document.getElementById("user_id").value = "";
        document.getElementById("modalUserTitle").innerHTML = "<i class='bx bx-user-plus'></i> Nuevo Usuario";
        document.getElementById("label_password").textContent = "Contraseña:";
        document.getElementById("user_password").required = true;
        document.getElementById("password_help").textContent = "Mínimo 4 caracteres.";
        document.getElementById("group_estado").style.display = "none";
        modal.style.display = "block";
    });

    // Abrir para editar
    window.openEditUserModal = function(user) {
        form.reset();
        document.getElementById("user_id").value = user.id;
        document.getElementById("user_username").value = user.username;
        document.getElementById("user_role_id").value = user.role_id;
        document.getElementById("user_estado").value = user.estado;
        
        document.getElementById("modalUserTitle").innerHTML = "<i class='bx bx-edit-alt'></i> Editar Usuario";
        document.getElementById("label_password").textContent = "Nueva Contraseña:";
        document.getElementById("user_password").required = false;
        document.getElementById("password_help").textContent = "Dejar en blanco para conservar la actual.";
        document.getElementById("group_estado").style.display = "block";
        modal.style.display = "block";
    };
});
</script>

<?php require_once 'views/layout/footer.php'; ?>
