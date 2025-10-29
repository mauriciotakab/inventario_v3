<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::start();
$error = $error ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - TAKAB</title>
    <link rel="stylesheet" href="/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="login-bg">
        <div class="login-container">
            <div class="login-logo">
                <img src="/assets/images/icono_takab.png" alt="logo TAKAB" width="90" height="70">
            </div>
            <h1 class="login-title">TAKAB</h1>
            <p class="login-subtitle">Sistema de Inventario</p>
            <div class="login-form-area">
                <h2><i class="fa-solid fa-right-to-bracket"></i> Iniciar sesi&oacute;n</h2>
                <p class="login-instructions">Ingresa tus credenciales para acceder al sistema</p>
                <?php if (!empty($error)): ?>
                    <p class="login-error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required autofocus placeholder="Ingresa tu usuario">

                    <label for="password">Contrase&ntilde;a</label>
                    <input type="password" id="password" name="password" required placeholder="Ingresa tu contrase&ntilde;a">

                    <button type="submit">Iniciar sesi&oacute;n</button>
                    <a class="forgot-link" href="forgot.php">Olvid&eacute; mi contrase&ntilde;a</a>
                </form>
                <div class="login-test-users">
                    <span>Usuarios de prueba:</span>
                    <ul>
                        <li><strong>Admin:</strong> admin / 123456</li>
                        <li><strong>Almac&eacute;n:</strong> almacen / 123456</li>
                        <li><strong>Empleado:</strong> empleado / 123456</li>
                        <li><strong>Empleado Prueba:</strong> mau / 123456</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/8b82fe7e0b.js" crossorigin="anonymous"></script>
</body>
</html>
