<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador', 'Almacen']);
$role   = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/config.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="content-area">

        <?php include __DIR__ . '/../partials/topbar.php'; ?>

        <main class="dashboard-main">
            <div class="main-table-card">
                <div class="config-section-header">
                    <div>
                        <div class="config-section-title"><i class="fa fa-building"></i> Clientes</div>
                        <p class="config-section-desc">Administra las razones sociales y contactos frecuentes para tus operaciones.</p>
                    </div>
                    <div class="config-section-actions">
                        <a href="clientes_create.php" class="btn-main"><i class="fa fa-plus"></i> Nuevo cliente</a>
                    </div>
                </div>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'csrf'): ?>
                    <div class="alert alert-danger"><i class="fa fa-triangle-exclamation"></i> No pudimos completar la acción solicitada. Inténtalo de nuevo.</div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success"><i class="fa fa-check-circle"></i> Cliente eliminado correctamente.</div>
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
                                <td class="table-actions">
                                    <a href="clientes_edit.php?id=<?= (int) $c['id'] ?>"><i class="fa fa-pen"></i> Editar</a>
                                    <form method="post" action="clientes_delete.php" style="display:inline-block" data-confirm="¿Eliminar este cliente?">
                                        <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                        <button type="submit" class="btn-danger"><i class="fa fa-trash"></i> Eliminar</button>
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
        </main>

    </div>
</div>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>
