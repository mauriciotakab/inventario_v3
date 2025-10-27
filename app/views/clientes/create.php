<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador', 'Almacen']);
$role = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar cliente | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
<div class="main-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="login-logo"><img src="/assets/images/icono_takab.png" alt="logo_TAKAB" width="90" height="55"></div>
            <div>
                <div class="sidebar-title">TAKAB</div>
                <div class="sidebar-desc">Dashboard</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="clientes.php" class="active"><i class="fa fa-building"></i> Clientes</a>
            <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión</a>
        </nav>
    </aside>

    <div class="main-content">
        <h2><i class="fa fa-plus"></i> Registrar cliente</h2>
        <?php if (!empty($msg)): ?>
            <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" class="form-takab">
            <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
            <label for="nombre">Nombre / Razón social:
                <input id="nombre" type="text" name="nombre" value="<?= htmlspecialchars($data['nombre'] ?? '') ?>" required>
            </label>
            <label for="contacto">Contacto:
                <input id="contacto" type="text" name="contacto" value="<?= htmlspecialchars($data['contacto'] ?? '') ?>">
            </label>
            <label for="telefono">Teléfono:
                <input id="telefono" type="text" name="telefono" value="<?= htmlspecialchars($data['telefono'] ?? '') ?>">
            </label>
            <label for="email">Email:
                <input id="email" type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
            </label>
            <label for="direccion">Dirección:
                <input id="direccion" type="text" name="direccion" value="<?= htmlspecialchars($data['direccion'] ?? '') ?>">
            </label>
            <div class="form-actions">
                <button type="submit" class="btn-principal">Registrar</button>
                <a href="clientes.php" class="btn-secundario">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
