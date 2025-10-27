<?php
$role = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
$ordenId = (int) ($orden['id'] ?? 0);
$estado = strtolower($orden['estado'] ?? 'pendiente');
$puedeEditar = in_array($role, ['Administrador', 'Compras'], true) && !in_array($estado, ['recibida', 'cancelada'], true);
$puedeRecibir = in_array($role, ['Administrador', 'Compras', 'Almacen'], true) && !in_array($estado, ['recibida', 'cancelada'], true);
$totalDetalle = array_sum(array_map(fn($item) => (float) ($item['cantidad'] ?? 0) * (float) ($item['precio_unitario'] ?? 0), $orden['detalles'] ?? []));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de orden #<?= $ordenId ?> | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/reportes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .detalle-main { padding:32px; }
        .summary-grid { display:grid; gap:18px; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); margin-bottom:26px; }
        .summary-card { background:#fff; border:1px solid #e4e8f3; border-radius:14px; padding:20px 22px; box-shadow:0 2px 16px rgba(23,44,87,0.05); }
        .summary-card .label { color:#6373a1; text-transform:uppercase; letter-spacing:.6px; font-size:0.82rem; margin-bottom:6px; display:block; }
        .summary-card .value { font-size:1.6rem; font-weight:800; color:#122c57; }
        .detalle-table { width:100%; border-collapse:collapse; margin-bottom:28px; }
        .detalle-table th, .detalle-table td { border:1px solid #e1e5f2; padding:12px; font-size:0.95rem; text-align:left; }
        .detalle-table th { background:#eef2ff; color:#20356f; text-transform:uppercase; font-size:0.82rem; }
        .badge-estado { display:inline-block; padding:6px 14px; border-radius:999px; font-weight:600; font-size:0.85rem; }
        .badge-pendiente { background:#fff6e5; color:#c17a12; }
        .badge-enviada { background:#e9f4ff; color:#1f6bb0; }
        .badge-recibida { background:#e6f7ed; color:#1a7a4b; }
        .badge-cancelada { background:#ffe8e8; color:#c44545; }
        .alert-success { background:#e7f7ee; border:1px solid #b8e0c1; color:#1b6d3b; padding:14px 18px; border-radius:10px; margin-bottom:20px; }
        .alert-error { background:#ffe8e8; border:1px solid #f5c2c7; color:#8a1c1c; padding:14px 18px; border-radius:10px; margin-bottom:20px; }
        .actions { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:28px; }
        .btn-secondary { background:#e3e9ff; border-radius:8px; padding:10px 18px; color:#213c7a; text-decoration:none; font-weight:600; }
        .btn-secondary:hover { background:#d2dcff; }
        .btn-action { background:#2563eb; color:#fff; border:none; border-radius:10px; padding:11px 20px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
        .btn-action:hover { background:#1e4dc2; }
        .form-recepcion { background:#fff; border:1px solid #e4e8f3; border-radius:16px; padding:24px; box-shadow:0 2px 16px rgba(23,44,87,0.05); margin-top:16px; }
        .form-recepcion h2 { margin:0 0 18px; color:#1b2f58; }
        .form-grid { display:grid; gap:18px; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); margin-bottom:18px; }
        .form-grid label { font-weight:600; color:#2f3f6d; margin-bottom:6px; display:block; }
        .form-grid input, .form-grid select { width:100%; padding:10px 12px; border-radius:10px; border:1px solid #d8dfee; background:#f7f9ff; color:#1b2d56; }
        .btn-danger { background:#d94a4a; color:#fff; border:none; border-radius:10px; padding:11px 20px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
        .btn-danger:hover { background:#b93c3c; }
        @media (max-width:768px) {
            .detalle-main { padding:22px 18px; }
            .detalle-table th, .detalle-table td { font-size:0.85rem; }
        }
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
            <a href="ordenes_compra.php" class="active"><i class="fa-solid fa-file-invoice-dollar"></i> Órdenes de compra</a>
            <a href="compras_proveedor.php"><i class="fa-solid fa-chart-pie"></i> Historial de compras</a>
            <a href="productos.php"><i class="fa-solid fa-boxes-stacked"></i> Productos</a>
            <a href="inventario_actual.php"><i class="fa-solid fa-list-check"></i> Inventario</a>
            <a href="reportes.php"><i class="fa-solid fa-chart-line"></i> Reportes</a>
            <a href="configuracion.php"><i class="fa-solid fa-gear"></i> Configuración</a>
            <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión</a>
        </nav>
    </aside>
    <div class="content-area">
        <header class="top-header">
            <div class="top-header-user">
                <span><?= htmlspecialchars($nombre ?: 'Usuario') ?></span>
                <i class="fa-solid fa-user-circle"></i>
                <a href="logout.php" class="logout-btn" title="Cerrar sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </header>
        <main class="detalle-main">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
                <div>
                    <h1 style="margin:0; font-size:2rem; color:#12305f;">Orden #<?= $ordenId ?></h1>
                    <p style="margin:6px 0 0; color:#61729f;">Proveedor: <?= htmlspecialchars($orden['proveedor'] ?? 'Desconocido') ?></p>
                </div>
                <div class="actions">
                    <a href="ordenes_compra.php" class="btn-secondary"><i class="fa-solid fa-arrow-left"></i> Volver</a>
                    <?php if ($puedeEditar): ?>
                        <a href="ordenes_compra_editar.php?id=<?= $ordenId ?>" class="btn-secondary"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($_GET['created'])): ?>
                <div class="alert-success"><i class="fa-solid fa-circle-check"></i> Orden registrada correctamente.</div>
            <?php endif; ?>
            <?php if (!empty($_GET['updated'])): ?>
                <div class="alert-success"><i class="fa-solid fa-circle-check"></i> Orden actualizada correctamente.</div>
            <?php endif; ?>
            <?php if (!empty($_GET['received'])): ?>
                <div class="alert-success"><i class="fa-solid fa-box-open"></i> Se registró la recepción y se actualizó el inventario.</div>
            <?php endif; ?>
            <?php if (!empty($_GET['cancelled'])): ?>
                <div class="alert-success"><i class="fa-solid fa-ban"></i> Orden cancelada correctamente.</div>
            <?php endif; ?>
            <?php if (!empty($_GET['locked'])): ?>
                <div class="alert-error"><i class="fa-solid fa-circle-info"></i> La orden no puede editarse porque está cerrada.</div>
            <?php endif; ?>
            <?php if (!empty($msg)): ?>
                <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <section class="summary-grid">
                <div class="summary-card">
                    <span class="label">Estado</span>
                    <?php
                        $badge = match($estado) {
                            'enviada' => 'badge-enviada',
                            'recibida' => 'badge-recibida',
                            'cancelada' => 'badge-cancelada',
                            default => 'badge-pendiente'
                        };
                    ?>
                    <span class="value"><span class="badge-estado <?= $badge ?>"><?= htmlspecialchars($orden['estado'] ?? 'Pendiente') ?></span></span>
                </div>
                <div class="summary-card">
                    <span class="label">Fecha</span>
                    <span class="value"><?= date('d/m/Y H:i', strtotime($orden['fecha'])) ?></span>
                </div>
                <div class="summary-card">
                    <span class="label">Total</span>
                    <span class="value">$<?= number_format((float) ($orden['total'] ?? $totalDetalle), 2) ?></span>
                </div>
                <div class="summary-card">
                    <span class="label">Almacén destino</span>
                    <span class="value" style="font-size:1.1rem;"><?= htmlspecialchars($orden['almacen_destino'] ?? '-') ?></span>
                </div>
                <div class="summary-card">
                    <span class="label">RFC</span>
                    <span class="value" style="font-size:1.1rem;"><?= htmlspecialchars($orden['rfc'] ?? '-') ?></span>
                </div>
                <div class="summary-card">
                    <span class="label">Factura</span>
                    <span class="value" style="font-size:1.1rem;"><?= htmlspecialchars($orden['numero_factura'] ?? '-') ?></span>
                </div>
            </section>

            <section>
                <h2 style="margin:0 0 14px; color:#1b2f58;">Detalle de productos</h2>
                <div class="reportes-table-wrapper">
                    <table class="detalle-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Costo unitario</th>
                            <th>Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($orden['detalles'])): ?>
                            <tr><td colspan="7" style="text-align:center; padding:22px; color:#7a88b2;">Sin productos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orden['detalles'] as $idx => $detalle): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= htmlspecialchars($detalle['codigo_producto'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($detalle['producto'] ?? 'Producto #' . $detalle['producto_id']) ?></td>
                                    <td><?= htmlspecialchars($detalle['tipo_producto'] ?? '-') ?></td>
                                    <td><?= number_format((float) ($detalle['cantidad'] ?? 0), 2) ?></td>
                                    <td>$<?= number_format((float) ($detalle['precio_unitario'] ?? 0), 2) ?></td>
                                    <td>$<?= number_format((float) ($detalle['cantidad'] ?? 0) * (float) ($detalle['precio_unitario'] ?? 0), 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="6" style="text-align:right;">Total</th>
                            <th>$<?= number_format($totalDetalle, 2) ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <?php if ($puedeRecibir): ?>
                <section id="recepcion" class="form-recepcion">
                    <h2><i class="fa-solid fa-box-open"></i> Registrar recepción</h2>
                    <form method="post">
                        <input type="hidden" name="accion" value="recibir">
                        <div class="form-grid">
                            <div>
                                <label for="numero_factura">Número de factura</label>
                                <input type="text" name="numero_factura" id="numero_factura" value="<?= htmlspecialchars($orden['numero_factura'] ?? '') ?>">
                            </div>
                            <div>
                                <label for="rfc">RFC de la compra</label>
                                <input type="text" name="rfc" id="rfc" maxlength="13" value="<?= htmlspecialchars($orden['rfc'] ?? '') ?>">
                            </div>
                            <div>
                                <label for="almacen_destino_id">Almacén que recibe *</label>
                                <select name="almacen_destino_id" id="almacen_destino_id" required>
                                    <option value="">Selecciona</option>
                                    <?php foreach ($almacenes as $almacen): ?>
                                        <option value="<?= (int) $almacen['id'] ?>" <?= (int) ($orden['almacen_destino_id'] ?? 0) === (int) $almacen['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($almacen['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn-action"><i class="fa-solid fa-box"></i> Confirmar recepción</button>
                    </form>
                </section>
            <?php endif; ?>

            <?php if (in_array($role, ['Administrador', 'Compras'], true) && $estado === 'pendiente'): ?>
                <section style="margin-top:24px;">
                    <form method="post" onsubmit="return confirm('¿Cancelar la orden? Esta acción no afecta el inventario.');">
                        <input type="hidden" name="accion" value="cancelar">
                        <button type="submit" class="btn-danger"><i class="fa-solid fa-ban"></i> Cancelar orden</button>
                    </form>
                </section>
            <?php endif; ?>
        </main>
    </div>
</div>
</body>
</html>
