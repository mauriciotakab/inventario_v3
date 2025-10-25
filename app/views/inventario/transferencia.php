<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador', 'Almacen','Compras']);

$role = $_SESSION['role'] ?? 'Administrador';
$nombre = $_SESSION['nombre'] ?? '';
$selectedProducto = $_POST['producto_id'] ?? '';
$selectedOrigen = $_POST['almacen_origen_id'] ?? '';
$selectedDestino = $_POST['almacen_destino_id'] ?? '';
$cantidadIngresada = $_POST['cantidad'] ?? '';
$observaciones = $_POST['observaciones'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Transferencia entre almacenes | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/productos.css">
    <link rel="stylesheet" href="/assets/css/inventario_form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="login-logo"><img src="/assets/images/icono_takab.png" alt="logo_TAKAB" width="90" height="55"></div>
            <div>
                <div class="sidebar-title">TAKAB</div>
                <div class="sidebar-desc">Panel de control</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="productos.php"><i class="fa-solid fa-boxes-stacked"></i> Gestión de Productos</a>
            <a href="inventario_actual.php"><i class="fa-solid fa-list-check"></i> Inventario</a>
            <a href="inventario_entradas.php"><i class="fa-solid fa-arrow-down-wide-short"></i> Entradas</a>
            <a href="inventario_salidas.php"><i class="fa-solid fa-arrow-up-short-wide"></i> Salidas</a>
            <a href="inventario_transferencias.php" class="active"><i class="fa-solid fa-right-left"></i> Transferencias</a>
            <a href="compras_proveedor.php"><i class="fa-solid fa-file-invoice"></i> Compras por proveedor</a>
            <a href="reportes_rotacion.php"><i class="fa-solid fa-arrows-rotate"></i> Rotación de inventario</a>
            <a href="revisar_solicitudes.php"><i class="fa-solid fa-comment-medical"></i> Solicitudes de Material</a>
            <?php if ($role === 'Administrador'): ?>
            <a href="logs.php"><i class="fa-solid fa-clipboard-list"></i> Bitácora</a>
            <?php endif; ?>
            <a href="configuracion.php"><i class="fa-solid fa-gear"></i> Configuración</a>
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

        <main class="dashboard-main inventario-form-main">
            <div class="inventario-form-header">
                <div>
                    <h1><i class="fa fa-right-left"></i> Transferir inventario entre almacenes</h1>
                    <p class="form-desc">Registra el movimiento completo de un producto desde un almacén hacia otro.</p>
                </div>
                <a class="btn-secondary" href="inventario_actual.php"><i class="fa fa-arrow-left"></i> Volver al inventario</a>
            </div>

            <?php if (!empty($msg)): ?>
                <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="inventario-form-grid">
                <section class="inventario-form-card">
                    <h2><i class="fa fa-right-left"></i> Datos de la transferencia</h2>
                    <form method="post" autocomplete="off" class="inventario-entry-form">
                        <div class="form-field">
                            <label for="producto_id">Producto *</label>
                            <select id="producto_id" name="producto_id" required>
                                <option value="">Selecciona un producto...</option>
                                <?php foreach ($productos as $producto): ?>
                                    <option value="<?= $producto['id'] ?>"
                                        data-stock="<?= (float) ($producto['stock_actual'] ?? 0) ?>"
                                        data-almacen="<?= (int) ($producto['almacen_id'] ?? 0) ?>"
                                        <?= $selectedProducto == $producto['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($producto['nombre']) ?> (<?= htmlspecialchars($producto['codigo']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="double-field">
                            <div class="form-field">
                                <label for="almacen_origen_id">Almacén origen *</label>
                                <select id="almacen_origen_id" name="almacen_origen_id" required>
                                    <option value="">Selecciona un almacén...</option>
                                    <?php foreach ($almacenes as $almacen): ?>
                                        <option value="<?= $almacen['id'] ?>"
                                            <?= $selectedOrigen == $almacen['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($almacen['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-field">
                                <label for="almacen_destino_id">Almacén destino *</label>
                                <select id="almacen_destino_id" name="almacen_destino_id" required>
                                    <option value="">Selecciona un almacén...</option>
                                    <?php foreach ($almacenes as $almacen): ?>
                                        <option value="<?= $almacen['id'] ?>"
                                            <?= $selectedDestino == $almacen['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($almacen['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="cantidad">Cantidad a transferir *</label>
                            <input type="number" step="0.01" min="0" id="cantidad" name="cantidad" required value="<?= htmlspecialchars($cantidadIngresada) ?>">
                            <span class="field-note">Actualmente solo se permite trasladar el total disponible.</span>
                        </div>

                        <div class="form-field">
                            <label for="observaciones">Notas u observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="3" placeholder="Opcional"><?= htmlspecialchars($observaciones) ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-main"><i class="fa fa-right-left"></i> Registrar transferencia</button>
                        </div>
                    </form>
                </section>

                <section class="inventario-form-card inventario-summary">
                    <h2><i class="fa fa-circle-info"></i> Resumen del producto</h2>
                    <div class="summary-placeholder" id="summary-placeholder">
                        <i class="fa fa-box-open"></i>
                        <p>Selecciona un producto para ver su stock y almacén actual.</p>
                    </div>
                    <div class="summary-content hidden" id="summary-content">
                        <div class="summary-item">
                            <span class="label">Producto</span>
                            <span class="value" id="summary-nombre">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Stock disponible</span>
                            <span class="value" id="summary-stock">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Almacén actual</span>
                            <span class="value" id="summary-almacen">-</span>
                        </div>
                    </div>
                </section>
            </div>

            <section class="inventario-form-card inventario-recents">
                <div class="recents-header">
                    <h2><i class="fa fa-clock"></i> Últimas transferencias</h2>
                    <span class="recents-sub">Consulta los movimientos recientes entre almacenes.</span>
                </div>
                <?php if (empty($movimientosRecientes)): ?>
                    <div class="inventario-empty">
                        <i class="fa fa-inbox"></i>
                        <p>Aún no se registran transferencias.</p>
                    </div>
                <?php else: ?>
                    <div class="recents-table-wrapper">
                        <table class="recents-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Registró</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimientosRecientes as $mov): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($mov['fecha'])) ?></td>
                                        <td><?= htmlspecialchars($mov['producto'] ?? '-') ?> <span class="mono">(<?= htmlspecialchars($mov['codigo_producto'] ?? '-') ?>)</span></td>
                                        <td><?= rtrim(rtrim(number_format((float) ($mov['cantidad'] ?? 0), 2), '0'), '.') ?></td>
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
        </main>
    </div>
</div>

<script>
const productosSelect = document.getElementById('producto_id');
const summaryPlaceholder = document.getElementById('summary-placeholder');
const summaryContent = document.getElementById('summary-content');
const summaryNombre = document.getElementById('summary-nombre');
const summaryStock = document.getElementById('summary-stock');
const summaryAlmacen = document.getElementById('summary-almacen');
const almacenOrigenSelect = document.getElementById('almacen_origen_id');

const almacenesMap = new Map([
    <?php foreach ($almacenes as $almacen): ?>
    [<?= (int) $almacen['id'] ?>, "<?= addslashes($almacen['nombre']) ?>"],
    <?php endforeach; ?>
]);

function actualizarResumen() {
    const option = productosSelect.options[productosSelect.selectedIndex];
    if (!option || !option.value) {
        summaryPlaceholder.classList.remove('hidden');
        summaryContent.classList.add('hidden');
        return;
    }

    const nombre = option.text;
    const stock = option.getAttribute('data-stock') || 0;
    const almacenActual = option.getAttribute('data-almacen');

    summaryNombre.textContent = nombre;
    summaryStock.textContent = stock + ' unidades';
    summaryAlmacen.textContent = almacenesMap.get(Number(almacenActual)) || 'Sin almacén asignado';

    if (!almacenOrigenSelect.value && almacenActual) {
        almacenOrigenSelect.value = almacenActual;
    }

    summaryPlaceholder.classList.add('hidden');
    summaryContent.classList.remove('hidden');
}

productosSelect.addEventListener('change', actualizarResumen);
document.addEventListener('DOMContentLoaded', actualizarResumen);
</script>
</body>
</html>
