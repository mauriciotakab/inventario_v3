<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador', 'Almacen']);

$role = $_SESSION['role'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proveedores | TAKAB</title>
    <link rel="stylesheet" href="../public/assets/css/proveedores.css">
    <link rel="stylesheet" href="../public/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
     <aside class="sidebar">
        <div class="sidebar-header">
            <div class="login-logo"><img src="../public/assets/images/icono_takab.png" alt="logo_TAKAB" width="90" height="55"></div>
            <div>
                <div class="sidebar-title">TAKAB</div>
                <div class="sidebar-desc">Compras</div>
            </div>
        </div>
        <nav class="sidebar-nav">     
            <?php if ($role === 'Administrador'): ?>
                <a href="ordenes_compra.php"><i class="fa-solid fa-file-invoice-dollar"></i> Ordenes de compra</a>
                <a href="facturas.php"><i class="fa-solid fa-file-circle-check"></i> Facturas de compra</a>
                <a href="ordenes_compra_crear.php"><i class="fa-solid fa-plus"></i> Nueva Orden</a>
                <a href="proveedores.php"><i class="fa-solid fa-address-book"></i> Proveedores</a>
                <a href="compras_proveedor.php"><i class="fa-solid fa-shopping-cart"></i> Compras por proveedor</a>
            <?php elseif ($role === 'Almacen'): ?>
                <a href="ordenes_compra.php"><i class="fa-solid fa-file-invoice-dollar"></i> Ordenes de compra</a>
                <a href="facturas.php"><i class="fa-solid fa-file-circle-check"></i> Facturas de compra</a>
                <a href="ordenes_compra_crear.php"><i class="fa-solid fa-plus"></i> Nueva Orden</a>
                <a href="proveedores.php"><i class="fa-solid fa-address-book"></i> Proveedores</a>
                <a href="compras_proveedor.php"><i class="fa-solid fa-shopping-cart"></i> Compras por proveedor</a>
            <?php elseif ($role === 'Compras'): ?>
                <a href="ordenes_compra.php"><i class="fa-solid fa-file-invoice-dollar"></i> Ordenes de compra</a>
                <a href="facturas.php"><i class="fa-solid fa-file-circle-check"></i> Facturas de compra</a>
                <a href="ordenes_compra_crear.php"><i class="fa-solid fa-plus"></i> Nueva Orden</a>
                <a href="proveedores.php"><i class="fa-solid fa-address-book"></i> Proveedores</a>
                <a href="compras_proveedor.php"><i class="fa-solid fa-shopping-cart"></i> Compras por proveedor</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión</a>
        </nav>
    </aside>
    <!--?php include __DIR__ . '/../partials/sidebar.php'; ?-->
    <div class="content-area">
    <?php include __DIR__ . '/../partials/topbar.php'; ?>
    <div class="main-content">
        <div class="prov-header">
            <div>
                <div class="prov-title"><i class="fa-solid fa-truck"></i> Proveedores</div>
                <div class="prov-desc">Listado y gestión de proveedores</div>
            </div>

            <!-- mover boton a submodulo proveedores -->
            <a href="proveedores_create.php" class="btn-principal"><i class="fa fa-plus"></i> Nuevo Proveedor</a>
        </div>
        <div class="prov-table-card">
            <table class="prov-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Condiciones de Pago</th>
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
                                <a href="proveedores_edit.php?id=<?= $prov['id'] ?>" class="btn-secundario" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="proveedores_delete.php?id=<?= $prov['id'] ?>" 
                                   onclick="return confirm('¿Seguro que deseas eliminar este proveedor?')" 
                                   class="btn-eliminar" title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </a>
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
</body>
</html>
