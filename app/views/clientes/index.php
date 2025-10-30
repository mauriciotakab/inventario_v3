<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador', 'Almacen']);
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-title">
            <i class="fa fa-building"></i> Clientes
            <a href="clientes_create.php" class="btn-principal" style="float:right;"><i class="fa fa-plus"></i> Nuevo cliente</a>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'csrf'): ?>
            <div class="alert-error">No pudimos completar la acción solicitada. Inténtalo de nuevo.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert-success">Cliente eliminado correctamente.</div>
        <?php endif; ?>

        <table class="takab-table">
            <thead>
                <tr>
                    <th>Nombre / Razón social</th>
                    <th>Contacto</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Dirección</th>
                    <th style="width:180px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nombre']) ?></td>
                        <td><?= htmlspecialchars($c['contacto']) ?></td>
                        <td><?= htmlspecialchars($c['telefono']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['direccion']) ?></td>
                        <td>
                            <a class="btn-secundario" href="clientes_edit.php?id=<?= (int) $c['id'] ?>"><i class="fa fa-edit"></i> Editar</a>
                            <form method="post" action="clientes_delete.php" style="display:inline-block" data-confirm="¿Eliminar este cliente?">
                                <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="btn-eliminar"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($clientes)): ?>
                    <tr><td colspan="6" style="text-align:center;">No hay clientes registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>
