<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::start();
$mensaje = $mensaje ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar acceso - TAKAB</title>
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
                <h2><i class="fa-solid fa-key"></i> Recuperar acceso</h2>
                <p class="login-instructions">Ingresa tu usuario; el equipo de sistemas validar&aacute; tu solicitud.</p>
                <?php if (!empty($mensaje)): ?>
                    <p class="login-info"><?= htmlspecialchars($mensaje) ?></p>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required autofocus placeholder="Ingresa tu usuario">

                    <button type="submit">Enviar solicitud</button>
                    <a class="forgot-link" href="login.php"><i class="fa-solid fa-arrow-left"></i> Volver al inicio de sesi&oacute;n</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/8b82fe7e0b.js" crossorigin="anonymous"></script>
</body>
</html>
