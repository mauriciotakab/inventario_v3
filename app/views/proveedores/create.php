<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador', 'Almacen']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar proveedor | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/proveedores.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <div class="main-content">
        <div class="form-card">
            <div class="form-title"><i class="fa fa-plus"></i> Registrar proveedor</div>
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
                <div class="form-row">
                    <label>Nombre:<input type="text" name="nombre" value="<?= htmlspecialchars($data['nombre'] ?? '') ?>" required></label>
                    <label>Contacto:<input type="text" name="contacto" value="<?= htmlspecialchars($data['contacto'] ?? '') ?>" required></label>
                </div>
                <div class="form-row">
                    <label>RFC:<input type="text" name="rfc" value="<?= htmlspecialchars($data['rfc'] ?? '') ?>"></label>
                    <label>Telefono:<input type="text" name="telefono" value="<?= htmlspecialchars($data['telefono'] ?? '') ?>"></label>
                </div>
                <label>Email:<input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>"></label>
                <label>Direccion:<input type="text" name="direccion" value="<?= htmlspecialchars($data['direccion'] ?? '') ?>"></label>
                <label>Condiciones de pago:<input type="text" name="condiciones_pago" value="<?= htmlspecialchars($data['condiciones_pago'] ?? '') ?>"></label>
                <div class="form-actions">
                    <button type="submit" class="btn-principal">Registrar</button>
                    <a href="proveedores.php" class="btn-secundario">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>





