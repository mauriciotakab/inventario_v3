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
    <title>Editar cliente | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-content">
        <h2><i class="fa fa-pen"></i> Editar cliente</h2>
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
                <input id="nombre" type="text" name="nombre" value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>" required>
            </label>
            <label for="contacto">Contacto:
                <input id="contacto" type="text" name="contacto" value="<?= htmlspecialchars($cliente['contacto'] ?? '') ?>">
            </label>
            <label for="telefono">Teléfono:
                <input id="telefono" type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>">
            </label>
            <label for="email">Email:
                <input id="email" type="email" name="email" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
            </label>
            <label for="direccion">Dirección:
                <input id="direccion" type="text" name="direccion" value="<?= htmlspecialchars($cliente['direccion'] ?? '') ?>">
            </label>
            <div class="form-actions">
                <button type="submit" class="btn-principal">Guardar</button>
                <a href="clientes.php" class="btn-secundario">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>
