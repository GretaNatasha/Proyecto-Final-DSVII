<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Inventario</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <h2><i class='bx bx-lock-alt'></i> Acceso al Sistema</h2>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form action="index.php?action=postLogin" method="POST">
                <div class="input-box">
                    <label for="username">Usuario</label>
                    <input type="text" name="username" id="username" required placeholder="Escribe tu usuario">
                </div>
                <div class="input-box">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" required placeholder="Escribe tu contraseña">
                </div>
                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            </form>
            <div class="login-footer">
                <p>Usa <b>admin</b> y contraseña <b>admin123</b></p>
            </div>
        </div>
    </div>
</body>
</html>
