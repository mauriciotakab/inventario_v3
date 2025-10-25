<?php
$role = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
$ordenes = $historial['ordenes'];
$detalles = $historial['detalles'];

$importeTotal = array_sum(array_map(fn($row) => (float) ($row['subtotal'] ?? 0), $ordenes));
$buildQuery = function(array $overrides = []) {
    $params = array_merge($_GET, $overrides);
    foreach ($params as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        }
    }
    return $params ? '?' . http_build_query($params) : '?';
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de compras por proveedor | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/reportes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .compras-main { padding: 32px 32px 48px; }
        .compras-header { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:26px; }
        .compras-header h1 { margin:0; font-size:2rem; color:#12305f; }
        .compras-summary { display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap:18px; margin-bottom:26px; }
        .compras-card { background:#fff; border-radius:14px; padding:22px 24px; border:1px solid #e4e8f3; box-shadow:0 2px 16px rgba(23,44,87,0.05); display:flex; flex-direction:column; gap:6px; }
        .compras-card .label { font-size:0.9rem; color:#6575a1; text-transform:uppercase; letter-spacing:.6px; }
        .compras-card .value { font-size:1.8rem; font-weight:800; color:#142b55; }
        .compras-card .foot { font-size:0.92rem; color:#7b8ab4; }
        .compras-table { width:100%; border-collapse:collapse; min-width:940px; }
        .compras-table th, .compras-table td { padding:12px 14px; border-bottom:1px solid #edf0f6; text-align:left; font-size:0.95rem; color:#1a2c51; }
        .compras-table th { background:#f3f6fc; text-transform:uppercase; letter-spacing:.4px; color:#5a6a94; font-size:0.88rem; }
        .compras-detalle { background:#f9fbff; border-left:3px solid #d6e3ff; }
        .badge-estado { display:inline-block; padding:4px 12px; border-radius:999px; font-weight:600; font-size:0.82rem; }
        .badge-pendiente { background:#fff6e5; color:#c17a12; }
        .badge-enviada { background:#e9f4ff; color:#1f6bb0; }
        .badge-recibida { background:#e6f7ed; color:#1a7a4b; }
        .badge-cancelada { background:#ffe8e8; color:#c44545; }
        .compras-filters { background:#fff; border-radius:16px; padding:24px 26px; border:1px solid #e4e8f3; box-shadow:0 2px 16px rgba(23,44,87,0.05); margin-bottom:26px; }
        .compras-filters form { display:grid; gap:18px; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); }
        .compras-filters label { font-weight:600; color:#3a4a7a; margin-bottom:6px; display:block; }
        .compras-filters input, .compras-filters select { padding:10px 12px; border-radius:9px; border:1px solid #d6dbea; background:#fafbff; color:#1a2c51; }
        .logs-filter-actions { display:flex; gap:10px; align-items:center; }
        @media (max-width:768px) {
            .compras-main { padding:22px 18px 36px; }
            .compras-header { flex-direction:column; align-items:flex-start; }
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
            <?php if ($role === 'Administrador'): ?>
                <a href="usuarios.php"><i class="fa-solid fa-users-cog"></i> Gestión de Usuarios</a>
            <?php endif; ?>
            <a href="productos.php"><i class="fa-solid fa-boxes-stacked"></i> Gestión de Productos</a>
            <a href="inventario_actual.php"><i class="fa-solid fa-list-check"></i> Inventario</a>
            <a href="compras_proveedor.php" class="active"><i class="fa-solid fa-file-invoice"></i> Compras por proveedor</a>
            <a href="compras_nueva.php"><i class="fa-solid fa-plus"></i> Nueva orden</a>
            <a href="reportes_rotacion.php"><i class="fa-solid fa-arrows-rotate"></i> Rotación de inventario</a>
            <a href="reportes.php"><i class="fa-solid fa-chart-line"></i> Reportes</a>
            <?php if ($role === 'Administrador'): ?>
                <a href="logs.php"><i class="fa-solid fa-clipboard-list"></i> Bitácora</a>
                <a href="configuracion.php"><i class="fa-solid fa-gear"></i> Configuración</a>
            <?php endif; ?>
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

        <main class="dashboard-main compras-main">
            <div class="compras-header">
                <div>
                    <h1>Historial de compras</h1>
                    <p class="reportes-desc">Consulta las órdenes de compra registradas por proveedor y periodo.</p>
                </div>
                <div class="section-actions">
                    <?php if (in_array($role, ['Administrador','Compras'], true)): ?>
                        <a class="btn-main" href="compras_nueva.php"><i class="fa-solid fa-plus"></i> Nueva orden</a>
                    <?php endif; ?>
                    <a class="btn-secondary" href="<?= $buildQuery(['export' => 'csv', 'section' => 'compras']) ?>"><i class="fa-solid fa-file-csv"></i> Exportar CSV</a>
                </div>
            </div>

            <section class="compras-filters">
                <form method="get">
                    <div>
                        <label for="proveedor_id">Proveedor</label>
                        <select id="proveedor_id" name="proveedor_id">
                            <option value="">Todos</option>
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?= $prov['id'] ?>" <?= $filtros['proveedor_id'] == $prov['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prov['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="desde">Desde</label>
                        <input type="date" id="desde" name="desde" value="<?= htmlspecialchars($filtros['desde']) ?>">
                    </div>
                    <div>
                        <label for="hasta">Hasta</label>
                        <input type="date" id="hasta" name="hasta" value="<?= htmlspecialchars($filtros['hasta']) ?>">
                    </div>
                    <div class="logs-filter-actions">
                        <button type="submit" class="btn-main"><i class="fa fa-filter"></i> Filtrar</button>
                        <a class="btn-ghost" href="compras_proveedor.php"><i class="fa fa-eraser"></i> Limpiar</a>
                    </div>
                </form>
            </section>

            <section class="compras-summary">
                <div class="compras-card">
                    <span class="label">Órdenes encontradas</span>
                    <span class="value"><?= number_format(count($ordenes)) ?></span>
                </div>
                <div class="compras-card">
                    <span class="label">Importe estimado</span>
                    <span class="value">$<?= number_format($importeTotal, 2) ?></span>
                    <span class="foot">Suma de subtotal por orden (detalle)</span>
                </div>
            </section>

            <section class="reportes-section">
                <div class="reportes-table-wrapper">
                        <table class="compras-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>RFC</th>
                                    <th>Factura</th>
                                    <th>Estado</th>
                                    <th>Productos</th>
                                    <th>Importe detalle</th>
                                    <th>Total registrado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($ordenes)): ?>
                                <tr><td colspan="6" style="text-align:center; padding:24px; color:#7d8bb0;">Sin órdenes en el periodo seleccionado.</td></tr>
                            <?php else: ?>
                                <?php foreach ($ordenes as $orden): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($orden['fecha'])) ?></td>
                                        <td><?= htmlspecialchars($orden['proveedor'] ?? 'Desconocido') ?></td>
                                        <td><?= htmlspecialchars($orden['rfc'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($orden['numero_factura'] ?? '-') ?></td>
                                        <td>
                                            <?php
                                                $estado = strtolower($orden['estado'] ?? 'Pendiente');
                                                $badge = match($estado) {
                                                    'enviada' => 'badge-enviada',
                                                    'recibida' => 'badge-recibida',
                                                    'cancelada' => 'badge-cancelada',
                                                    default => 'badge-pendiente'
                                                };
                                            ?>
                                            <span class="badge-estado <?= $badge ?>"><?= htmlspecialchars($orden['estado']) ?></span>
                                        </td>
                                        <td><?= number_format((float) ($orden['total_items'] ?? 0), 2) ?></td>
                                        <td>$<?= number_format((float) ($orden['subtotal'] ?? 0), 2) ?></td>
                                        <td>$<?= number_format((float) ($orden['total'] ?? $orden['subtotal'] ?? 0), 2) ?></td>
                                        <td><a class="btn-ghost" href="compras_detalle.php?id=<?= $orden['id'] ?>"><i class="fa fa-eye"></i> Ver</a></td>
                                    </tr>
                                    <?php if (!empty($detalles[$orden['id']])): ?>
                                        <tr class="compras-detalle">
                                            <td colspan="6">
                                                <strong>Detalle:</strong>
                                                <ul>
                                                    <?php foreach ($detalles[$orden['id']] as $detalle): ?>
                                                        <li>
                                                            <?= htmlspecialchars($detalle['producto'] ?? ('Producto #' . $detalle['producto_id'])) ?> ·
                                                            Cantidad: <?= number_format((float) $detalle['cantidad'], 2) ?> ·
                                                            Precio: $<?= number_format((float) $detalle['precio_unitario'], 2) ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>
