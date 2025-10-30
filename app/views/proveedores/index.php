<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador', 'Almacen']);

$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proveedores | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/proveedores.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="main-content">
        <div class="prov-header">
            <div>
                <div class="prov-title"><i class="fa-solid fa-truck"></i> Proveedores</div>
                <div class="prov-desc">Listado y gestión de proveedores</div>
            </div>
            <a href="proveedores_create.php" class="btn-principal"><i class="fa fa-plus"></i> Nuevo proveedor</a>
        </div>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'csrf'): ?>
            <div class="alert-error">No pudimos completar la acción por motivos de seguridad.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert-success">Proveedor eliminado correctamente.</div>
        <?php endif; ?>
        <div class="prov-table-card">
            <table class="prov-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Condiciones de pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $prov): ?>
                        <tr>
                            <td><?= htmlspecialchars($prov['nombre']) ?></td>
                            <td><?= htmlspecialchars($prov['contacto']) ?></td>
                            <td><?= htmlspecialchars($prov['telefono']) ?></td>
                            <td><?= htmlspecialchars($prov['email']) ?></td>
                            <td><?= htmlspecialchars($prov['direccion']) ?></td>
                            <td><?= htmlspecialchars($prov['condiciones_pago']) ?></td>
                            <td>
                                <a href="proveedores_edit.php?id=<?= (int) $prov['id'] ?>" class="btn-secundario" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form method="post" action="proveedores_delete.php" style="display:inline-block" data-confirm="¿Eliminar este proveedor?">
                                    <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= (int) $prov['id'] ?>">
                                    <button type="submit" class="btn-eliminar" title="Eliminar"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($proveedores)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; color:#9ab;">Sin proveedores registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>
