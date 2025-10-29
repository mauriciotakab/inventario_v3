<?php
$role = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';

$mensajeExito = null;
if (!empty($alerta['success'])) {
    $mensajeExito = $alerta['success'] == 2
        ? 'Producto actualizado correctamente.'
        : 'Producto registrado correctamente.';
}
$mensajeEliminado = !empty($alerta['deleted']) ? 'Producto eliminado correctamente.' : null;
$importResultado = $importAlert ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>GestiA3n de Productos | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/config.css">
    <link rel="stylesheet" href="/assets/css/productos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Ensures the Stock column is a bit wider and numbers don't wrap/cut */
        .productos-table .col-stock { width: 140px; min-width: 120px; text-align: center; }
        .productos-table td.col-stock { white-space: nowrap; }
        .productos-table td.col-stock .badge { display: inline-block; }
        .productos-table td.col-stock small { display: block; font-size: 0.75rem; color: #666; }
        @media (max-width: 700px) {
            .productos-table .col-stock { width: auto; min-width: 0; }
            .productos-table td.col-stock { white-space: normal; }
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
                <a href="usuarios.php"><i class="fa-solid fa-users-cog"></i> Gestion de Usuarios</a>
            <?php endif; ?>
            <a href="productos.php" class="active"><i class="fa-solid fa-boxes-stacked"></i> Productos</a>
            <?php if (in_array($role, ['Administrador','Compras','Almacen'], true)): ?>
                <a href="ordenes_compra.php"><i class="fa-solid fa-file-invoice-dollar"></i> Ordenes de compra</a>
            <?php endif; ?>
            <?php if (in_array($role, ['Administrador','Compras'], true)): ?>
                <a href="ordenes_compra_crear.php"><i class="fa-solid fa-plus"></i> Registrar orden</a>
            <?php endif; ?>
            <a href="inventario_actual.php"><i class="fa-solid fa-list-check"></i> Inventario</a>
            <a href="compras_proveedor.php"><i class="fa-solid fa-file-invoice"></i> Compras por proveedor</a>
            <a href="reportes_rotacion.php"><i class="fa-solid fa-arrows-rotate"></i> Rotacion de inventario</a>
            <?php if ($role === 'Administrador' || $role === 'Almacen'): ?>
                <a href="revisar_solicitudes.php"><i class="fa-solid fa-inbox"></i> Solicitudes de Material</a>
            <?php endif; ?>
            <a href="reportes.php"><i class="fa-solid fa-chart-line"></i> Reportes</a>
            <?php if ($role === 'Administrador'): ?>
                <a href="logs.php"><i class="fa-solid fa-clipboard-list"></i> Bitacora</a>
            <?php endif; ?>
            <a href="configuracion.php"><i class="fa-solid fa-gear"></i> Configuracion</a>
            <a href="documentacion.php"><i class="fa-solid fa-book"></i> Documentacion</a>
            <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesion</a>
        </nav>
    </aside>

    <div class="content-area">
        <header class="top-header">
            <div></div>
            <div class="top-header-user">
                <span><?= htmlspecialchars($nombre) ?> (<?= htmlspecialchars($role) ?>)</span>
                <i class="fa-solid fa-user-circle"></i>
                <a href="logout.php" class="logout-btn" title="Cerrar sesiA3n"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </header>

        <main class="dashboard-main productos-main">
            <?php if ($mensajeExito): ?>
                <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= htmlspecialchars($mensajeExito) ?></div>
            <?php endif; ?>
            <?php if ($mensajeEliminado): ?>
                <div class="alert alert-danger"><i class="fa fa-trash"></i> <?= htmlspecialchars($mensajeEliminado) ?></div>
            <?php endif; ?>
            <?php if (!empty($importResultado)): ?>
                <?php
                    $importSuccess = (int) ($importResultado['success'] ?? 0);
                    $importProcessed = (int) ($importResultado['processed'] ?? 0);
                    $importSkipped = (int) ($importResultado['skipped'] ?? 0);
                    $importErrors = $importResultado['errors'] ?? [];
                    $hayErroresImport = !empty($importErrors);
                ?>
                <div class="alert <?= $hayErroresImport ? 'alert-danger' : 'alert-success' ?>">
                    <i class="fa <?= $hayErroresImport ? 'fa-circle-exclamation' : 'fa-check-circle' ?>"></i>
                    Se procesaron <?= $importProcessed ?> filas. Importados correctamente: <?= $importSuccess ?><?= $importSkipped > 0 ? " A Saltados: {$importSkipped}" : '' ?>.
                    <?php if ($hayErroresImport): ?>
                        <div class="alert-detail">
                            <strong>Observaciones:</strong>
                            <ul>
                                <?php foreach (array_slice($importErrors, 0, 8) as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                                <?php if (count($importErrors) > 8): ?>
                                    <li>Se omitieron <?= count($importErrors) - 8 ?> mensajes adicionales.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="productos-header">
                <div>
                    <h1>GestiA3n de Productos</h1>
                    <p class="productos-header-desc">Administra el catAlogo de materiales y herramientas de TAKAB.</p>
                    <p class="productos-import-note desktop-only">Usa la plantilla para cargar mAoltiples productos. Los valores deben corresponder con los IDs de catAlogos ya registrados (categorAas, proveedores, almacenes, unidades).</p>
                </div>
                <div class="productos-header-actions">
                    <a class="btn-secondary" href="productos_template.php"><i class="fa-solid fa-download"></i> Descargar plantilla</a>
                    <form class="productos-import-form" action="productos_import.php" method="post" enctype="multipart/form-data">
                        <label class="btn-secondary btn-file">
                            <i class="fa-solid fa-file-csv"></i> Seleccionar CSV
                            <input type="file" name="productos_archivo" accept=".csv,text/csv" required>
                        </label>
                        <button type="submit" class="btn-main"><i class="fa-solid fa-upload"></i> Importar productos</button>
                    </form>
                    <a class="btn-main" href="productos_create.php"><i class="fa fa-plus"></i> Nuevo producto</a>
                </div>
                <p class="productos-import-note mobile-only">Usa la plantilla para cargar mAoltiples productos. Los valores deben corresponder con los IDs de catAlogos ya registrados (categorAas, proveedores, almacenes, unidades).</p>
            </div>

            <section class="productos-stats-grid">
                <div class="productos-stat-card primary">
                    <span class="stat-label">Productos totales</span>
                    <span class="stat-value"><?= number_format($stats['total']) ?></span>
                    <span class="stat-foot">Activos: <?= number_format($stats['activos']) ?> A Inactivos: <?= number_format($stats['inactivos']) ?></span>
                </div>
                <div class="productos-stat-card sky">
                    <span class="stat-label">Consumibles</span>
                    <span class="stat-value"><?= number_format($stats['consumibles']) ?></span>
                    <span class="stat-foot">Herramientas: <?= number_format($stats['herramientas']) ?></span>
                </div>
                <div class="productos-stat-card warning">
                    <span class="stat-label">Stock bajo</span>
                    <span class="stat-value"><?= number_format($stats['stock_bajo']) ?></span>
                    <span class="stat-foot">Sin stock: <?= number_format($stats['sin_stock']) ?></span>
                </div>
                <div class="productos-stat-card success">
                    <span class="stat-label">Valor inventario</span>
                    <span class="stat-value">$<?= number_format($stats['valor_total'], 2) ?></span>
                    <span class="stat-foot">Costo estimado total</span>
                </div>
            </section>

            <section class="productos-filters-card">
                <form method="get" class="productos-filters-form">
                    <div class="filter-row">
                        <div class="filter-field">
                            <label for="buscar">BAosqueda global</label>
                            <div class="filter-input-icon">
                                <i class="fa fa-search"></i>
                                <input type="text" id="buscar" name="buscar" placeholder="Nombre, cA3digo, descripciA3n o tags" value="<?= htmlspecialchars($filtros['buscar']) ?>">
                            </div>
                        </div>
                        <div class="filter-field">
                            <label for="codigo">CA3digo interno</label>
                            <input type="text" id="codigo" name="codigo" value="<?= htmlspecialchars($filtros['codigo']) ?>" placeholder="Ej. H001">
                        </div>
                        <div class="filter-field">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($filtros['nombre']) ?>" placeholder="Buscar por nombre exacto">
                        </div>
                        <div class="filter-field">
                            <label for="tipo">Tipo</label>
                            <select id="tipo" name="tipo">
                                <option value="">Todos</option>
                                <?php foreach ($tiposProducto as $tipo): ?>
                                    <option value="<?= $tipo ?>" <?= $filtros['tipo'] === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="filter-field">
                            <label for="categoria_id">CategorAa</label>
                            <select id="categoria_id" name="categoria_id">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= $categoria['id'] ?>" <?= $filtros['categoria_id'] == $categoria['id'] ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="almacen_id">AlmacAn</label>
                            <select id="almacen_id" name="almacen_id">
                                <option value="">Todos</option>
                                <?php foreach ($almacenes as $almacen): ?>
                                    <option value="<?= $almacen['id'] ?>" <?= $filtros['almacen_id'] == $almacen['id'] ? 'selected' : '' ?>><?= htmlspecialchars($almacen['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="proveedor_id">Proveedor</label>
                            <select id="proveedor_id" name="proveedor_id">
                                <option value="">Todos</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <option value="<?= $proveedor['id'] ?>" <?= $filtros['proveedor_id'] == $proveedor['id'] ? 'selected' : '' ?>><?= htmlspecialchars($proveedor['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="estado">Estado fAsico</label>
                            <select id="estado" name="estado">
                                <option value="">Todos</option>
                                <?php foreach ($estadosProducto as $estado): ?>
                                    <option value="<?= $estado ?>" <?= $filtros['estado'] === $estado ? 'selected' : '' ?>><?= $estado ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="filter-field">
                            <label for="activo_id">Disponibilidad</label>
                            <select id="activo_id" name="activo_id">
                                <option value="">Todas</option>
                                <?php foreach ($estadosActivos as $estadoActivo): ?>
                                    <option value="<?= $estadoActivo['id'] ?>" <?= $filtros['activo_id'] == $estadoActivo['id'] ? 'selected' : '' ?>><?= htmlspecialchars($estadoActivo['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="stock_flag">Estado de stock</label>
                            <select id="stock_flag" name="stock_flag">
                                <option value="">Todos</option>
                                <option value="bajo" <?= $filtros['stock_flag'] === 'bajo' ? 'selected' : '' ?>>Stock bajo</option>
                                <option value="sin" <?= $filtros['stock_flag'] === 'sin' ? 'selected' : '' ?>>Sin stock</option>
                                <option value="suficiente" <?= $filtros['stock_flag'] === 'suficiente' ? 'selected' : '' ?>>Stock suficiente</option>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="unidad_medida_id">Unidad</label>
                            <select id="unidad_medida_id" name="unidad_medida_id">
                                <option value="">Todas</option>
                                <?php foreach ($unidades as $unidad): ?>
                                    <option value="<?= $unidad['id'] ?>" <?= $filtros['unidad_medida_id'] == $unidad['id'] ? 'selected' : '' ?>><?= htmlspecialchars($unidad['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="tags">Tags</label>
                            <input type="text" id="tags" name="tags" value="<?= htmlspecialchars($filtros['tags']) ?>" placeholder="Palabras clave">
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="filter-field">
                            <label for="fecha_desde">Fecha de alta (desde)</label>
                            <input type="date" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($filtros['fecha_desde']) ?>">
                        </div>
                        <div class="filter-field">
                            <label for="fecha_hasta">Fecha de alta (hasta)</label>
                            <input type="date" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>">
                        </div>
                        <div class="filter-field">
                            <label for="valor_min">Valor inventario mAnimo</label>
                            <input type="number" step="0.01" id="valor_min" name="valor_min" value="<?= htmlspecialchars($filtros['valor_min']) ?>" placeholder="Ej. 1000">
                        </div>
                        <div class="filter-field">
                            <label for="valor_max">Valor inventario mAximo</label>
                            <input type="number" step="0.01" id="valor_max" name="valor_max" value="<?= htmlspecialchars($filtros['valor_max']) ?>" placeholder="Ej. 5000">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-main"><i class="fa fa-filter"></i> Aplicar filtros</button>
                        <?php if ($hayFiltros): ?>
                            <a class="btn-ghost" href="productos.php"><i class="fa fa-eraser"></i> Limpiar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="productos-table-card">
                <div class="productos-table-header">
                    <h2><i class="fa-solid fa-cubes"></i> CatAlogo (<?= number_format($stats['total']) ?>)</h2>
                    <span class="productos-table-sub">Resultados segAon filtros aplicados</span>
                </div>
                <div class="productos-table-wrapper">
                    <?php if (empty($productos)): ?>
                        <div class="productos-empty">
                            <i class="fa fa-inbox"></i>
                            <p>No se encontraron productos con los criterios seleccionados.</p>
                        </div>
                    <?php else: ?>
                        <table class="productos-table">
                            <thead>
                            <tr>
                                <th>CA3digo</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>CategorAa</th>
                                <th class="col-stock">Stock</th>
                                <th>Estado</th>
                                <th>Disponibilidad</th>
                                <th>AlmacAn</th>
                                <th>Proveedor</th>
                                <th>Valor</th>
                                <th class="col-actions">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <?php
                                $stockActual = (int) ($producto['stock_actual'] ?? 0);
                                $stockMinimo = (int) ($producto['stock_minimo'] ?? 0);
                                $valorInventario = (float) ($producto['costo_compra'] ?? 0) * $stockActual;
                                $badgeStock = 'ok';
                                if ($stockActual <= 0) {
                                    $badgeStock = 'sin';
                                } elseif ($stockActual < $stockMinimo) {
                                    $badgeStock = 'bajo';
                                }
                                ?>
                                <tr>
                                    <td><span class="mono"><?= htmlspecialchars($producto['codigo']) ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($producto['nombre']) ?></strong>
                                        <?php if (!empty($producto['tags'])): ?>
                                            <div class="producto-tags"><?= htmlspecialchars($producto['tags']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge badge-tipo <?= strtolower($producto['tipo'] ?? '') ?>"><?= htmlspecialchars($producto['tipo']) ?></span></td>
                                    <td><?= htmlspecialchars($producto['categoria'] ?? 'Sin categorAa') ?></td>
                                    <td class="col-stock">
                                        <span class="badge badge-stock <?= $badgeStock ?>">
                                            <?= number_format($stockActual) ?> <?= htmlspecialchars($producto['unidad_abreviacion'] ?? '') ?>
                                        </span>
                                        <small>MAn: <?= number_format($stockMinimo) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($producto['estado'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge badge-activo <?= (int)($producto['activo_id'] ?? 1) === 1 ? 'activo' : 'inactivo' ?>">
                                            <?= htmlspecialchars($producto['estado_activo'] ?? 'Activo') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($producto['almacen'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($producto['proveedor'] ?? '-') ?></td>
                                    <td>$<?= number_format($valorInventario, 2) ?></td>
                                    <td class="col-actions">
                                        <a class="btn-table" title="Ver detalle" href="productos_view.php?id=<?= $producto['id'] ?>"><i class="fa fa-eye"></i></a>
                                        <a class="btn-table" title="Editar" href="productos_edit.php?id=<?= $producto['id'] ?>"><i class="fa fa-pen"></i></a>
                                        <form method="post" action="productos_setactive.php" class="inline-form" style="display:inline-block">
                                            <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                                            <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">
                                            <input type="hidden" name="active" value="<?= (int)($producto['activo_id'] ?? 1) === 1 ? 0 : 1 ?>">
                                            <button type="submit" class="btn-table" title="<?= (int)($producto['activo_id'] ?? 1) === 1 ? 'Desactivar' : 'Activar' ?>" onclick="return confirm('<?= (int)($producto['activo_id'] ?? 1) === 1 ? 'ADesactivar este producto?' : 'AActivar este producto?' ?>');">
                                                <i class="fa <?= (int)($producto['activo_id'] ?? 1) === 1 ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="post" action="productos_delete.php" class="inline-form" style="display:inline-block" onsubmit="return confirm('AEliminar el producto seleccionado? Esta acciA3n no se puede deshacer.');">
                                            <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                                            <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">
                                            <button type="submit" class="btn-table btn-danger" title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>

