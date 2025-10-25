<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador','Compras','Almacen']);
$role = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de orden #<?= $orden['id'] ?> | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/reportes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .orden-main { padding: 32px 32px 48px; }
        .orden-detail-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(260px,1fr)); gap:18px; }
        .orden-card { background:#fff; border-radius:16px; border:1px solid #e4e8f3; box-shadow:0 2px 16px rgba(23,44,87,0.05); padding:22px 24px; }
        .orden-card h2 { margin:0 0 14px; font-size:1.3rem; color:#12305f; display:flex; gap:8px; align-items:center; }
        .orden-card p { margin:4px 0; color:#4a5a85; }
        .orden-items { width:100%; border-collapse:collapse; margin-top:16px; }
        .orden-items th, .orden-items td { padding:11px 12px; border-bottom:1px solid #edf0f6; text-align:left; }
        .orden-items th { background:#f3f6fc; text-transform:uppercase; font-size:0.88rem; color:#5a6a94; }
        .orden-actions { margin-top:20px; display:flex; gap:12px; }
    </style>
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
            <?php if ($role === 'Administrador'): ?><a href="usuarios.php"><i class="fa-solid fa-users-cog"></i> Gestión de Usuarios</a><?php endif; ?>
            <a href="productos.php"><i class="fa-solid fa-boxes-stacked"></i> Gestión de Productos</a>
            <a href="compras_proveedor.php" class="active"><i class="fa-solid fa-file-invoice"></i> Compras</a>
            <a href="reportes_rotacion.php"><i class="fa-solid fa-arrows-rotate"></i> Rotación</a>
            <a href="reportes.php"><i class="fa-solid fa-chart-line"></i> Reportes</a>
            <?php if ($role === 'Administrador'): ?><a href="logs.php"><i class="fa-solid fa-clipboard-list"></i> Bitácora</a><?php endif; ?>
            <a href="documentacion.php"><i class="fa-solid fa-book"></i> Documentación</a>
            <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión</a>
        </nav>
    </aside>

    <div class="content-area">
        <header class="top-header">
            <div></div>
            <div class="top-header-user">
                <span><?= htmlspecialchars($nombre) ?> (<?= htmlspecialchars($role) ?>)</span>
                <i class="fa-solid fa-user-circle"></i>
                <a href="logout.php" class="logout-btn" title="Cerrar sesión"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </header>

        <main class="dashboard-main orden-main">
            <div class="reportes-header">
                <div>
                    <h1>Orden de compra #<?= $orden['id'] ?></h1>
                    <p class="reportes-desc">Proveedor: <?= htmlspecialchars($orden['proveedor'] ?? '-') ?> · Estado: <?= htmlspecialchars($orden['estado']) ?></p>
                    <?php if (!empty($mensaje)): ?><div class="alert alert-success" style="margin-top:12px;"><i class="fa fa-check-circle"></i> <?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger" style="margin-top:12px;">
                            <i class="fa fa-circle-exclamation"></i> No se pudo actualizar la orden.
                            <ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="section-actions">
                    <a class="btn-secondary" href="compras_proveedor.php"><i class="fa fa-arrow-left"></i> Volver</a>
                </div>
            </div>

            <div class="orden-detail-grid">
                <section class="orden-card">
                    <h2><i class="fa-solid fa-circle-info"></i> Información</h2>
                    <p><strong>Proveedor:</strong> <?= htmlspecialchars($orden['proveedor'] ?? '-') ?></p>
                    <p><strong>Contacto:</strong> <?= htmlspecialchars($orden['contacto'] ?? '-') ?></p>
                    <p><strong>Almacén destino:</strong> <?= htmlspecialchars($orden['almacen'] ?? '-') ?></p>
                    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($orden['fecha'])) ?></p>
                    <p><strong>Estado:</strong> <?= htmlspecialchars($orden['estado']) ?></p>
                </section>
                <section class="orden-card">
                    <h2><i class="fa-solid fa-file-invoice"></i> Factura</h2>
                    <p><strong>RFC:</strong> <?= htmlspecialchars($orden['rfc'] ?? '-') ?></p>
                    <p><strong>Factura:</strong> <?= htmlspecialchars($orden['numero_factura'] ?? '-') ?></p>
                    <p><strong>Total registrado:</strong> $<?= number_format((float) ($orden['total'] ?? 0), 2) ?></p>
                    <p><strong>Observaciones:</strong> <?= htmlspecialchars($orden['observaciones'] ?? '-') ?></p>
                </section>
            </div>

            <section class="orden-card" style="margin-top:24px;">
                <h2><i class="fa-solid fa-list"></i> Productos</h2>
                <div class="table-wrapper">
                    <table class="orden-items" id="tabla_items">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Costo unitario</th>
                                <th>Total</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['producto'] ?? ('Producto #' . $item['producto_id'])) ?></td>
                                    <td><?= number_format((float) $item['cantidad'], 2) ?></td>
                                    <td>$<?= number_format((float) $item['precio_unitario'], 2) ?></td>
                                    <td>$<?= number_format((float) $item['cantidad'] * (float) $item['precio_unitario'], 2) ?></td>
                                    <td><?= htmlspecialchars($item['descripcion'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <?php if (in_array($role, ['Administrador','Compras'], true) && strtolower($orden['estado']) !== 'recibida'): ?>
                <form method="post" class="orden-actions">
                    <button type="submit" name="accion" value="recibir" class="btn-main"><i class="fa fa-box"></i> Marcar como recibida</button>
                </form>
            <?php endif; ?>
        </main>
    </div>
</div>
</body>
</html>
