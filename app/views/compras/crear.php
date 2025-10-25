<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador','Compras']);
$role = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva orden de compra | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/reportes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .orden-main { padding: 32px 32px 48px; }
        .orden-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(260px,1fr)); gap:18px; }
        .orden-card { background:#fff; border-radius:16px; border:1px solid #e4e8f3; box-shadow:0 2px 16px rgba(23,44,87,0.05); padding:22px 24px; }
        .orden-card h2 { margin:0 0 14px; font-size:1.3rem; color:#12305f; display:flex; gap:8px; align-items:center; }
        .orden-field { display:flex; flex-direction:column; gap:8px; margin-bottom:14px; }
        .orden-field label { font-weight:600; color:#3a4a7a; }
        .orden-field input, .orden-field select, .orden-field textarea { padding:10px 12px; border:1px solid #d6dbea; border-radius:9px; background:#fafbff; font-size:0.95rem; }
        .orden-items-table { width:100%; border-collapse:collapse; }
        .orden-items-table th, .orden-items-table td { padding:10px 12px; border-bottom:1px solid #edf0f6; text-align:left; }
        .orden-items-table th { background:#f3f6fc; text-transform:uppercase; letter-spacing:.4px; color:#5a6a94; font-size:0.88rem; }
        .orden-actions { display:flex; gap:10px; flex-wrap:wrap; }
        .alert ul { margin:8px 0 0 16px; }
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
                    <h1>Registrar nueva orden de compra</h1>
                    <p class="reportes-desc">Captura los datos de la factura y los artículos adquiridos. Si la orden se marca como recibida, el inventario se actualizará automáticamente.</p>
                </div>
            </div>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-circle-exclamation"></i> Se encontraron problemas al registrar la orden.
                    <ul>
                        <?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif (!empty($mensaje)): ?>
                <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form method="post" id="ordenForm">
                <div class="orden-grid">
                    <section class="orden-card">
                        <h2><i class="fa-solid fa-circle-info"></i> Información general</h2>
                        <div class="orden-field">
                            <label for="proveedor_id">Proveedor *</label>
                            <select id="proveedor_id" name="proveedor_id" required>
                                <option value="">Selecciona un proveedor</option>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="orden-field">
                            <label for="almacen_id">Almacén destino *</label>
                            <select id="almacen_id" name="almacen_id" required>
                                <option value="">Selecciona un almacén</option>
                                <?php foreach ($almacenes as $almacen): ?>
                                    <option value="<?= $almacen['id'] ?>"><?= htmlspecialchars($almacen['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="orden-field">
                            <label for="fecha">Fecha</label>
                            <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="orden-field">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado">
                                <?php foreach (['Pendiente','Enviada','Recibida','Cancelada'] as $estado): ?>
                                    <option value="<?= $estado ?>"><?= $estado ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </section>

                    <section class="orden-card">
                        <h2><i class="fa-solid fa-file-invoice"></i> Factura</h2>
                        <div class="orden-field">
                            <label for="rfc">RFC de la compra</label>
                            <input type="text" id="rfc" name="rfc" maxlength="13" placeholder="RFC del emisor">
                        </div>
                        <div class="orden-field">
                            <label for="numero_factura">Número de factura</label>
                            <input type="text" id="numero_factura" name="numero_factura" placeholder="Folio o serie">
                        </div>
                        <div class="orden-field">
                            <label for="observaciones">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="4" placeholder="Notas adicionales"></textarea>
                        </div>
                    </section>
                </div>

                <section class="orden-card" style="margin-top:22px;">
                    <h2><i class="fa-solid fa-box"></i> Agregar artículos existentes</h2>
                    <div class="orden-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                        <div class="orden-field">
                            <label for="producto_existente">Producto</label>
                            <select id="producto_existente">
                                <option value="">Selecciona un producto</option>
                                <?php foreach ($productos as $prod): ?>
                                    <option value="<?= $prod['id'] ?>" data-nombre="<?= htmlspecialchars($prod['nombre']) ?>"><?= htmlspecialchars($prod['nombre']) ?> (<?= htmlspecialchars($prod['codigo']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="orden-field">
                            <label for="cantidad_existente">Cantidad</label>
                            <input type="number" id="cantidad_existente" min="0" step="0.01">
                        </div>
                        <div class="orden-field">
                            <label for="precio_existente">Costo unitario</label>
                            <input type="number" id="precio_existente" min="0" step="0.01">
                        </div>
                        <div class="orden-field">
                            <label for="desc_existente">Descripción</label>
                            <input type="text" id="desc_existente" placeholder="Lote, serie, etc.">
                        </div>
                    </div>
                    <div class="orden-actions">
                        <button type="button" class="btn-secondary" onclick="agregarExistente()"><i class="fa fa-plus"></i> Agregar artículo</button>
                    </div>
                </section>

                <section class="orden-card" style="margin-top:22px;">
                    <h2><i class="fa-solid fa-plus"></i> Registrar producto nuevo</h2>
                    <div class="orden-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                        <div class="orden-field">
                            <label for="nuevo_codigo">Código *</label>
                            <input type="text" id="nuevo_codigo">
                        </div>
                        <div class="orden-field">
                            <label for="nuevo_nombre">Nombre *</label>
                            <input type="text" id="nuevo_nombre">
                        </div>
                        <div class="orden-field">
                            <label for="nuevo_tipo">Tipo *</label>
                            <select id="nuevo_tipo">
                                <?php foreach ($tiposProducto as $tipo): ?>
                                    <option value="<?= $tipo ?>"><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="orden-field">
                            <label for="nuevo_unidad">Unidad *</label>
                            <select id="nuevo_unidad">
                                <option value="">Selecciona</option>
                                <?php foreach ($unidades as $unidad): ?>
                                    <option value="<?= $unidad['id'] ?>"><?= htmlspecialchars($unidad['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="orden-field">
                            <label for="nuevo_categoria">Categoría</label>
                            <select id="nuevo_categoria">
                                <option value="">Sin categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="orden-field">
                            <label for="nuevo_stock_min">Stock mínimo</label>
                            <input type="number" id="nuevo_stock_min" min="0" step="0.01" value="0">
                        </div>
                        <div class="orden-field">
                            <label for="nuevo_cantidad">Cantidad comprada *</label>
                            <input type="number" id="nuevo_cantidad" min="0" step="0.01">
                        </div>
                        <div class="orden-field">
                            <label for="nuevo_precio">Costo unitario *</label>
                            <input type="number" id="nuevo_precio" min="0" step="0.01">
                        </div>
                        <div class="orden-field">
                            <label for="nuevo_descripcion">Descripción</label>
                            <textarea id="nuevo_descripcion" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="orden-actions">
                        <button type="button" class="btn-secondary" onclick="agregarNuevo()"><i class="fa fa-plus"></i> Agregar producto nuevo</button>
                    </div>
                </section>

                <section class="orden-card" style="margin-top:22px;">
                    <h2><i class="fa-solid fa-list"></i> Resumen de la orden</h2>
                    <div class="table-wrapper">
                        <table class="orden-items-table" id="tabla_items">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Costo unitario</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="solicitud-empty"><td colspan="6" style="text-align:center; padding:18px; color:#7d8bb0;">Sin artículos.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <input type="hidden" name="items" id="items_input">
                <div class="solicitud-submit" style="margin-top:22px;">
                    <button type="submit" class="btn-main"><i class="fa fa-save"></i> Guardar orden</button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
const items = [];
const itemsInput = document.getElementById('items_input');
const tablaItems = document.querySelector('#tabla_items tbody');

function agregarExistente() {
    const select = document.getElementById('producto_existente');
    const productoId = select.value;
    const productoNombre = select.options[select.selectedIndex]?.dataset?.nombre || select.options[select.selectedIndex]?.text;
    const cantidad = parseFloat(document.getElementById('cantidad_existente').value || '0');
    const precio = parseFloat(document.getElementById('precio_existente').value || '0');
    const descripcion = document.getElementById('desc_existente').value;
    if (!productoId || cantidad <= 0) {
        alert('Selecciona un producto y captura una cantidad válida.');
        return;
    }
    items.push({ producto_id: productoId, nombre: productoNombre, cantidad, precio_unitario: precio, descripcion, es_nuevo: false, tipo: 'Existente' });
    renderItems();
    document.getElementById('cantidad_existente').value = '';
    document.getElementById('precio_existente').value = '';
    document.getElementById('desc_existente').value = '';
}

function agregarNuevo() {
    const codigo = document.getElementById('nuevo_codigo').value.trim();
    const nombre = document.getElementById('nuevo_nombre').value.trim();
    const tipo = document.getElementById('nuevo_tipo').value;
    const unidad = document.getElementById('nuevo_unidad').value;
    const categoria = document.getElementById('nuevo_categoria').value;
    const stockMin = parseFloat(document.getElementById('nuevo_stock_min').value || '0');
    const cantidad = parseFloat(document.getElementById('nuevo_cantidad').value || '0');
    const precio = parseFloat(document.getElementById('nuevo_precio').value || '0');
    const descripcion = document.getElementById('nuevo_descripcion').value;
    if (!codigo || !nombre || cantidad <= 0) {
        alert('Completa los datos del producto nuevo y la cantidad.');
        return;
    }
    items.push({
        es_nuevo: true,
        codigo,
        nombre,
        tipo,
        unidad_medida_id: unidad,
        categoria_id: categoria,
        stock_minimo: stockMin,
        cantidad,
        precio_unitario: precio,
        descripcion,
    });
    renderItems();
    document.getElementById('nuevo_codigo').value = '';
    document.getElementById('nuevo_nombre').value = '';
    document.getElementById('nuevo_stock_min').value = '0';
    document.getElementById('nuevo_cantidad').value = '';
    document.getElementById('nuevo_precio').value = '';
    document.getElementById('nuevo_descripcion').value = '';
}

function eliminarItem(index) {
    items.splice(index, 1);
    renderItems();
}

function renderItems() {
    if (items.length === 0) {
        tablaItems.innerHTML = '<tr class="solicitud-empty"><td colspan="6" style="text-align:center; padding:18px; color:#7d8bb0;">Sin artículos.</td></tr>';
    } else {
        tablaItems.innerHTML = '';
        items.forEach((item, idx) => {
            const total = (item.cantidad || 0) * (item.precio_unitario || 0);
            tablaItems.innerHTML += `<tr>
                <td>${item.nombre || item.codigo}</td>
                <td>${item.es_nuevo ? 'Nuevo' : 'Inventario'}</td>
                <td>${Number(item.cantidad || 0).toFixed(2)}</td>
                <td>$${Number(item.precio_unitario || 0).toFixed(2)}</td>
                <td>$${total.toFixed(2)}</td>
                <td><button type="button" class="btn-ghost" onclick="eliminarItem(${idx})"><i class="fa fa-trash"></i></button></td>
            </tr>`;
        });
    }
    itemsInput.value = JSON.stringify(items);
}

document.getElementById('ordenForm').addEventListener('submit', function (e) {
    if (items.length === 0) {
        alert('Agrega al menos un artículo a la orden.');
        e.preventDefault();
    } else {
        itemsInput.value = JSON.stringify(items);
    }
});
</script>
</body>
</html>
