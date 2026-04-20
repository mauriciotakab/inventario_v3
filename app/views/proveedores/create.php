<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador', 'Almacen']);
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Proveedor | TAKAB</title>
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
        <div class="form-card">
            <div class="form-title"><i class="fa fa-plus"></i> Registrar Proveedor</div>
            <?php if (!empty($msg)) echo "<div class='alert-success'>$msg</div>"; ?>
            <form method="post" class="form-takab">
                <div class="form-row">
                    <label>Nombre:<input type="text" name="nombre" required></label>
                    <label>Contacto:<input type="text" name="contacto" required></label>
                </div>
                <div class="form-row">
                    <label>Teléfono:<input type="text" name="telefono"></label>
                    <label>Email:<input type="email" name="email"></label>
                </div>
                <label>Dirección:<input type="text" name="direccion"></label>
                <label>Condiciones de Pago:<input type="text" name="condiciones_pago"></label>
                <div class="form-actions">
                    <button type="submit" class="btn-principal">Registrar</button>
                    <a href="proveedores.php" class="btn-secundario">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
