<?php
$role = $_SESSION['role'] ?? 'Almacen';
$nombre = $_SESSION['nombre'] ?? '';
$mostrarCostos = $role === 'Administrador';
$filters = $filters ?? [];

$fechaInicio = htmlspecialchars($filters['from'] ?? date('Y-m-01'), ENT_QUOTES, 'UTF-8');
$fechaFin = htmlspecialchars($filters['to'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8');
$movTipo = htmlspecialchars($filters['mov_tipo'] ?? '', ENT_QUOTES, 'UTF-8');
$movAlmacenSeleccionado = htmlspecialchars($filters['mov_almacen_id'] ?? '', ENT_QUOTES, 'UTF-8');
$invAlmacenSeleccionado = htmlspecialchars($filters['inv_almacen_id'] ?? '', ENT_QUOTES, 'UTF-8');
$invCategoriaSeleccionada = htmlspecialchars($filters['inv_categoria_id'] ?? '', ENT_QUOTES, 'UTF-8');
$proveedorSeleccionado = htmlspecialchars($filters['proveedor_id'] ?? '', ENT_QUOTES, 'UTF-8');
$topTipoSeleccionado = htmlspecialchars($filters['top_tipo'] ?? '', ENT_QUOTES, 'UTF-8');
$topAlmacenSeleccionado = htmlspecialchars($filters['top_almacen_id'] ?? '', ENT_QUOTES, 'UTF-8');

$proveedores = is_array($proveedores ?? null) ? $proveedores : [];
$almacenes = is_array($almacenes ?? null) ? $almacenes : [];
$categorias = is_array($categorias ?? null) ? $categorias : [];
$tiposProducto = is_array($tiposProducto ?? null) ? $tiposProducto : [];
$comprasListado = is_array($comprasListado ?? null) ? $comprasListado : [];
$comprasResumen = is_array($comprasResumen ?? null) ? $comprasResumen : ['total' => 0, 'proveedores' => []];

$buildQuery = function(array $overrides = []) {
    $params = array_merge($_GET, $overrides);
    foreach ($params as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        }
    }
    return $params ? ('?' . http_build_query($params)) : '?';
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/reportes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="content-area">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>

        <main class="dashboard-main reportes-main">
            <div class="reportes-header">
                <div>
                    <h1>Centro de reportes</h1>
                    <p class="reportes-desc">Analiza el estado del inventario, movimientos y prestamos. Exporta la informacion a CSV o PDF para respaldo o analisis.</p>
                </div>
                <div class="reportes-actions">
                    <a class="btn-secondary" href="<?= $buildQuery(['export' => 'csv', 'section' => 'movimientos']) ?>"><i class="fa-solid fa-file-csv"></i> Movimientos CSV</a>
                    <a class="btn-secondary" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'movimientos']) ?>"><i class="fa-solid fa-file-pdf"></i> Movimientos PDF</a>
                </div>
            </div>

            <section class="reportes-summary-grid">
                <div class="reportes-summary-card primary">
                    <span class="label">Productos registrados</span>
                    <span class="value"><?= number_format($inventarioResumen['total']) ?></span>
                    <span class="foot">Activos: <?= number_format($inventarioResumen['activos']) ?> | Inactivos: <?= number_format($inventarioResumen['inactivos']) ?></span>
                </div>
                <div class="reportes-summary-card warning">
                    <span class="label">Stock bajo</span>
                    <span class="value"><?= number_format($inventarioResumen['stock_bajo']) ?></span>
                    <span class="foot">Sin stock: <?= number_format($inventarioResumen['sin_stock']) ?></span>
                </div>
                <div class="reportes-summary-card sky">
                    <span class="label">Herramientas prestadas</span>
                    <span class="value"><?= number_format($inventarioResumen['prestamos_pendientes']) ?></span>
                    <span class="foot">Vencidas: <?= number_format($inventarioResumen['prestamos_vencidos']) ?></span>
                </div>
                <?php if ($mostrarCostos): ?>
                    <div class="reportes-summary-card success">
                        <span class="label">Valor estimado del inventario</span>
                        <span class="value">$<?= number_format($inventarioResumen['valor_total'], 2) ?></span>
                        <span class="foot">Considera stock actual * costo compra</span>
                    </div>
                <?php endif; ?>
            </section>

            <?php if ($mostrarCostos): ?>
            <section class="reportes-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fa fa-warehouse"></i> Valor por almacen</h2>
                        <span class="section-sub">Inventario valorizado por almacen con totales de unidades.</span>
                    </div>
                    <div class="section-actions">
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'valor_almacen']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'valor_almacen']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <?php if (empty($valorPorAlmacen)): ?>
                    <div class="reportes-empty">
                        <i class="fa fa-info-circle"></i>
                        <p>No hay datos para calcular el valor por almacen.</p>
                    </div>
                <?php else: ?>
                    <div class="reportes-table-wrapper">
                        <table class="reportes-table">
                            <thead>
                                <tr>
                                    <th>Almacen</th>
                                    <th>Productos</th>
                                    <th>Unidades</th>
                                    <th>Valor total (MXN)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($valorPorAlmacen as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['almacen'] ?? '-') ?></td>
                                        <td><?= number_format((int) ($row['productos'] ?? 0)) ?></td>
                                        <td><?= number_format((float) ($row['unidades'] ?? 0), 2) ?></td>
                                        <td>$<?= number_format((float) ($row['valor_total'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if ($mostrarCostos): ?>
            <section class="reportes-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fa fa-file-invoice-dollar"></i> Compras por proveedor</h2>
                        <span class="section-sub">Órdenes registradas entre <?= $fechaInicio ?> y <?= $fechaFin ?>.</span>
                    </div>
                    <div class="section-actions">
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'compras_proveedor']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'compras_proveedor']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <?php if (empty($comprasListado)): ?>
                    <div class="reportes-empty">
                        <i class="fa fa-info-circle"></i>
                        <p>No se registraron compras en el periodo seleccionado.</p>
                    </div>
                <?php else: ?>
                    <div class="reportes-summary-inline">
                        <div class="reportes-summary-card success">
                            <span class="label">Total periodo</span>
                            <span class="value">$<?= number_format((float) ($comprasResumen['total'] ?? 0), 2) ?></span>
                        </div>
                        <?php foreach (array_slice($comprasResumen['proveedores'] ?? [], 0, 3) as $resProveedor): ?>
                            <div class="reportes-summary-card sky">
                                <span class="label"><?= htmlspecialchars($resProveedor['proveedor']) ?></span>
                                <span class="value">$<?= number_format((float) $resProveedor['importe'], 2) ?></span>
                                <span class="foot">Órdenes: <?= number_format((int) $resProveedor['ordenes']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="reportes-table-wrapper">
                        <table class="reportes-table">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>RFC proveedor</th>
                                    <th>RFC orden</th>
                                    <th>Factura</th>
                                    <th>Estado</th>
                                    <th>Almacen</th>
                                    <th>Productos</th>
                                    <th>Importe detalle</th>
                                    <th>Importe total</th>
                                    <th>Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comprasListado as $compra): ?>
                                    <tr>
                                        <td>#<?= (int) $compra['orden_id'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($compra['fecha'])) ?></td>
                                        <td><?= htmlspecialchars($compra['proveedor']) ?></td>
                                        <td><?= htmlspecialchars($compra['proveedor_rfc'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($compra['orden_rfc'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($compra['numero_factura'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($compra['estado']) ?></td>
                                        <td><?= htmlspecialchars($compra['almacen']) ?></td>
                                        <td><?= number_format((float) $compra['total_items'], 2) ?></td>
                                        <td>$<?= number_format((float) $compra['importe_detalle'], 2) ?></td>
                                        <td>$<?= number_format((float) $compra['importe_total'], 2) ?></td>
                                        <td><?= htmlspecialchars($compra['creado_por'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php
                $consumiblesListado = $productosPorTipo['consumibles'] ?? [];
                $herramientasListado = $productosPorTipo['herramientas'] ?? [];
                $consumiblesStock = array_sum(array_map(fn($row) => (float) ($row['stock_actual'] ?? 0), $consumiblesListado));
                $herramientasStock = array_sum(array_map(fn($row) => (float) ($row['stock_actual'] ?? 0), $herramientasListado));
            ?>
            <section class="reportes-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fa fa-boxes-stacked"></i> Productos por tipo</h2>
                        <span class="section-sub">Descarga el catalogo completo separado en consumibles y herramientas.</span>
                    </div>
                    <div class="section-actions">
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'productos_consumibles']) ?>"><i class="fa-solid fa-file-csv"></i> Consumibles CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'productos_consumibles']) ?>"><i class="fa-solid fa-file-pdf"></i> Consumibles PDF</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'productos_herramientas']) ?>"><i class="fa-solid fa-file-csv"></i> Herramientas CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'productos_herramientas']) ?>"><i class="fa-solid fa-file-pdf"></i> Herramientas PDF</a>
                    </div>
                </div>
                <div class="reportes-table-wrapper">
                    <table class="reportes-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Productos</th>
                                <th>Stock total</th>
                                <th>Exportar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-tipo consumible">Consumibles</span></td>
                                <td><?= number_format(count($consumiblesListado)) ?></td>
                                <td><?= number_format($consumiblesStock, 2) ?></td>
                                <td class="reportes-actions-inline">
                                    <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'productos_consumibles']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                                    <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'productos_consumibles']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-tipo herramienta">Herramientas</span></td>
                                <td><?= number_format(count($herramientasListado)) ?></td>
                                <td><?= number_format($herramientasStock, 2) ?></td>
                                <td class="reportes-actions-inline">
                                    <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'productos_herramientas']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                                    <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'productos_herramientas']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="reportes-filter-card">
                <form method="get" class="reportes-filter-form">
                    <div class="filter-row">
                        <div class="filter-field">
                            <label for="from">Desde</label>
                            <input type="date" id="from" name="from" value="<?= $fechaInicio ?>">
                        </div>
                        <div class="filter-field">
                            <label for="to">Hasta</label>
                            <input type="date" id="to" name="to" value="<?= $fechaFin ?>">
                        </div>
                        <div class="filter-field">
                            <label for="mov_tipo">Tipo de movimiento</label>
                            <select id="mov_tipo" name="mov_tipo">
                                <option value="">Todos</option>
                                <option value="Entrada" <?= $movTipo === 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                                <option value="Salida" <?= $movTipo === 'Salida' ? 'selected' : '' ?>>Salida</option>
                                <option value="Transferencia" <?= $movTipo === 'Transferencia' ? 'selected' : '' ?>>Transferencia</option>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="mov_almacen_id">Movimientos (almacen)</label>
                            <select id="mov_almacen_id" name="mov_almacen_id">
                                <option value="">Todos</option>
                                <?php foreach ($almacenes as $almacen): ?>
                                    <option value="<?= (int) $almacen['id'] ?>" <?= $movAlmacenSeleccionado === (string) $almacen['id'] ? 'selected' : '' ?>><?= htmlspecialchars($almacen['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn-main"><i class="fa fa-filter"></i> Actualizar</button>
                            <a class="btn-ghost" href="reportes.php"><i class="fa fa-eraser"></i> Limpiar</a>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-field">
                            <label for="proveedor_id">Proveedor (compras)</label>
                            <select id="proveedor_id" name="proveedor_id">
                                <option value="">Todos</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <option value="<?= (int) $proveedor['id'] ?>" <?= $proveedorSeleccionado === (string) $proveedor['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($proveedor['nombre']) ?><?= !empty($proveedor['rfc']) ? ' - ' . htmlspecialchars($proveedor['rfc']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="inv_almacen_id">Inventario bajo (almacen)</label>
                            <select id="inv_almacen_id" name="inv_almacen_id">
                                <option value="">Todos</option>
                                <?php foreach ($almacenes as $almacen): ?>
                                    <option value="<?= (int) $almacen['id'] ?>" <?= $invAlmacenSeleccionado === (string) $almacen['id'] ? 'selected' : '' ?>><?= htmlspecialchars($almacen['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="inv_categoria_id">Inventario bajo (categoria)</label>
                            <select id="inv_categoria_id" name="inv_categoria_id">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= (int) $categoria['id'] ?>" <?= $invCategoriaSeleccionada === (string) $categoria['id'] ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="top_tipo">Top salidas (tipo)</label>
                            <select id="top_tipo" name="top_tipo">
                                <option value="">Todos</option>
                                <?php foreach ($tiposProducto as $tipoProducto): ?>
                                    <option value="<?= htmlspecialchars($tipoProducto) ?>" <?= $topTipoSeleccionado === $tipoProducto ? 'selected' : '' ?>><?= htmlspecialchars($tipoProducto) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="top_almacen_id">Top salidas (almacen)</label>
                            <select id="top_almacen_id" name="top_almacen_id">
                                <option value="">Todos</option>
                                <?php foreach ($almacenes as $almacen): ?>
                                    <option value="<?= (int) $almacen['id'] ?>" <?= $topAlmacenSeleccionado === (string) $almacen['id'] ? 'selected' : '' ?>><?= htmlspecialchars($almacen['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </section>

            <section class="reportes-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fa fa-triangle-exclamation"></i> Inventario bajo</h2>
                        <span class="section-sub">Productos con existencias por debajo del minimo definido.</span>
                    </div>
                    <div class="section-actions">
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'inventario_bajo']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'inventario_bajo']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <?php if (empty($inventarioBajo)): ?>
                    <div class="reportes-empty">
                        <i class="fa fa-check-circle"></i>
                        <p>No hay alertas de stock bajo en este momento.</p>
                    </div>
                <?php else: ?>
                    <div class="reportes-table-wrapper">
                        <table class="reportes-table">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Categoria</th>
                                    <th>Almacen</th>
                                    <th>Stock actual</th>
                                    <th>Stock minimo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventarioBajo as $row): ?>
                                    <tr>
                                        <td><span class="mono"><?= htmlspecialchars($row['codigo']) ?></span></td>
                                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                                        <td><span class="badge badge-tipo <?= strtolower($row['tipo']) ?>"><?= htmlspecialchars($row['tipo']) ?></span></td>
                                        <td><?= htmlspecialchars($row['categoria']) ?></td>
                                        <td><?= htmlspecialchars($row['almacen']) ?></td>
                                        <td><?= number_format((float) $row['stock_actual'], 2) ?> <?= htmlspecialchars($row['unidad']) ?></td>
                                        <td><?= number_format((float) $row['stock_minimo'], 2) ?> <?= htmlspecialchars($row['unidad']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <?php if ($mostrarCostos): ?>
            <section class="reportes-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fa fa-warehouse"></i> Valor del inventario por almacen</h2>
                        <span class="section-sub">Suma del stock actual multiplicado por el costo de compra.</span>
                    </div>
                    <div class="section-actions">
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'valor_almacen']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'valor_almacen']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <div class="reportes-table-wrapper">
                    <table class="reportes-table">
                        <thead>
                            <tr>
                                <th>Almacen</th>
                                <th>Productos</th>
                                <th>Unidades</th>
                                <th>Valor estimado (MXN)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($valorPorAlmacen as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['almacen']) ?></td>
                                    <td><?= number_format((int) $row['productos']) ?></td>
                                    <td><?= number_format((float) $row['unidades'], 2) ?></td>
                                    <td>$<?= number_format((float) $row['valor_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <section class="reportes-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fa fa-exchange-alt"></i> Movimientos de inventario</h2>
                        <span class="section-sub">Entradas, salidas y transferencias registradas entre <?= $fechaInicio ?> y <?= $fechaFin ?>.</span>
                    </div>
                    <div class="section-actions">
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'movimientos']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'movimientos']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <?php if (empty($movimientos)): ?>
                    <div class="reportes-empty">
                        <i class="fa fa-info-circle"></i>
                        <p>No se registraron movimientos en el periodo seleccionado.</p>
                    </div>
                <?php else: ?>
                    <div class="reportes-table-wrapper">
                        <table class="reportes-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Codigo</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Almacen origen</th>
                                    <th>Almacen destino</th>
                                    <th>Usuario</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimientos as $mov): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($mov['fecha'])) ?></td>
                                        <td><span class="badge badge-tipo <?= strtolower($mov['tipo']) ?>"><?= htmlspecialchars($mov['tipo']) ?></span></td>
                                        <td><span class="mono"><?= htmlspecialchars($mov['codigo']) ?></span></td>
                                        <td><?= htmlspecialchars($mov['producto']) ?></td>
                                        <td><?= number_format((float) $mov['cantidad'], 2) ?></td>
                                        <td><?= htmlspecialchars($mov['almacen_origen'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($mov['almacen_destino'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($mov['usuario'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($mov['observaciones'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="reportes-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fa fa-toolbox"></i> Herramientas prestadas</h2>
                        <span class="section-sub">Prestamos activos dentro del rango seleccionado.</span>
                    </div>
                    <div class="section-actions">
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'prestamos_abiertos']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'prestamos_abiertos']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <?php if (empty($prestamosAbiertos)): ?>
                    <div class="reportes-empty">
                        <i class="fa fa-info-circle"></i>
                        <p>No hay prestamos activos en el periodo.</p>
                    </div>
                <?php else: ?>
                    <div class="reportes-table-wrapper">
                        <table class="reportes-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha prestamo</th>
                                    <th>Fecha estimada</th>
                                    <th>Producto</th>
                                    <th>Empleado</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prestamosAbiertos as $prestamo): ?>
                                    <tr>
                                        <td class="mono">#<?= (int) $prestamo['id'] ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($prestamo['fecha_prestamo'])) ?></td>
                                        <td><?= $prestamo['fecha_estimada_devolucion'] ? date('d/m/Y', strtotime($prestamo['fecha_estimada_devolucion'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($prestamo['producto']) ?> <span class="mono">(<?= htmlspecialchars($prestamo['codigo']) ?>)</span></td>
                                        <td><?= htmlspecialchars($prestamo['empleado']) ?></td>
                                        <td><?= htmlspecialchars($prestamo['observaciones'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="reportes-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fa fa-circle-exclamation"></i> Prestamos vencidos</h2>
                        <span class="section-sub">Herramientas que superaron la fecha estimada de devolucion.</span>
                    </div>
                    <div class="section-actions">
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'prestamos_vencidos']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'prestamos_vencidos']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <?php if (empty($prestamosVencidos)): ?>
                    <div class="reportes-empty">
                        <i class="fa fa-check-circle"></i>
                        <p>No se encontraron prestamos vencidos.</p>
                    </div>
                <?php else: ?>
                    <div class="reportes-table-wrapper">
                        <table class="reportes-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha prestamo</th>
                                    <th>Fecha estimada</th>
                                    <th>Dias vencidos</th>
                                    <th>Producto</th>
                                    <th>Empleado</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prestamosVencidos as $prestamo): ?>
                                    <tr>
                                        <td class="mono">#<?= (int) $prestamo['id'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($prestamo['fecha_prestamo'])) ?></td>
                                        <td><?= date('d/m/Y', strtotime($prestamo['fecha_estimada_devolucion'])) ?></td>
                                        <td><span class="badge badge-warning">+<?= (int) $prestamo['dias_vencidos'] ?> dias</span></td>
                                        <td><?= htmlspecialchars($prestamo['producto']) ?> <span class="mono">(<?= htmlspecialchars($prestamo['codigo']) ?>)</span></td>
                                        <td><?= htmlspecialchars($prestamo['empleado']) ?></td>
                                        <td><?= htmlspecialchars($prestamo['observaciones'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="reportes-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fa fa-chart-bar"></i> Productos con mayor salida</h2>
                        <span class="section-sub">Top 10 articulos con mas movimientos de salida en el periodo.</span>
                    </div>
                    <div class="section-actions">
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'top_salidas']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'top_salidas']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <?php if (empty($topSalidas)): ?>
                    <div class="reportes-empty">
                        <i class="fa fa-info-circle"></i>
                        <p>Aun no se registran salidas en el periodo.</p>
                    </div>
                <?php else: ?>
                    <div class="reportes-table-wrapper">
                        <table class="reportes-table">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Almacen</th>
                                    <th>Cantidad salida</th>
                                    <?php if ($mostrarCostos): ?><th>Costo estimado (MXN)</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topSalidas as $row): ?>
                                    <tr>
                                        <td><span class="mono"><?= htmlspecialchars($row['codigo']) ?></span></td>
                                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                                        <td><?= htmlspecialchars($row['tipo'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['almacen'] ?? '-') ?></td>
                                        <td><?= number_format((float) $row['total_salidas'], 2) ?></td>
                                        <?php if ($mostrarCostos): ?><td>$<?= number_format((float) $row['costo_estimado'], 2) ?></td><?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="reportes-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fa fa-layer-group"></i> Estado de inventario</h2>
                        <span class="section-sub">Distribucion de productos por estado fisico.</span>
                    </div>
                    <div class="section-actions">
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'csv', 'section' => 'estado_inventario']) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <a class="btn-ghost" href="<?= $buildQuery(['export' => 'pdf', 'section' => 'estado_inventario']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <div class="reportes-table-wrapper">
                    <table class="reportes-table">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Productos</th>
                                <th>Unidades</th>
                                <?php if ($mostrarCostos): ?><th>Valor estimado (MXN)</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estadoInventario as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['estado']) ?></td>
                                    <td><?= number_format((int) $row['cantidad']) ?></td>
                                    <td><?= number_format((float) $row['unidades'], 2) ?></td>
                                    <?php if ($mostrarCostos): ?><td>$<?= number_format((float) $row['valor'], 2) ?></td><?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>


