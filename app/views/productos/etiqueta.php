<?php
$role = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
$producto = $producto ?? [];
$almacenes = $almacenes ?? [];

$unidadPredeterminada = $producto['unidad_abreviacion'] ?? ($producto['unidad_medida_nombre'] ?? '');
$loteValor = trim($_POST['lote'] ?? '');
$almacenSeleccionado = (int) ($_POST['almacen_id'] ?? ($producto['almacen_id'] ?? 0));
$cantidadValor = max(1, min(50, (int) ($_POST['cantidad'] ?? 1)));
$unidadEtiqueta = trim($_POST['unidad_etiqueta'] ?? $unidadPredeterminada);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Etiquetas de producto | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/config.css">
    <link rel="stylesheet" href="/assets/css/productos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="content-area">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>

        <main class="dashboard-main productos-main">
            <div class="productos-header">
                <div>
                    <h1>Imprimir etiquetas</h1>
                    <p class="productos-header-desc">Genera etiquetas PDF con codigo de barras para el producto seleccionado.</p>
                </div>
                <div class="productos-header-actions">
                    <a class="btn-secondary" href="productos_view.php?id=<?= (int) ($producto['id'] ?? 0) ?>"><i class="fa fa-arrow-left"></i> Volver al producto</a>
                </div>
            </div>

            <section class="productos-detail-card productos-hero">
                <div>
                    <h1><?= htmlspecialchars($producto['nombre'] ?? 'Producto') ?></h1>
                    <div class="hero-meta">
                        <span class="badge badge-stock ok">Codigo interno: <?= htmlspecialchars($producto['codigo'] ?? '-') ?></span>
                        <span class="badge badge-stock ok">Codigo barras: <?= htmlspecialchars($producto['codigo_barras'] ?? '-') ?></span>
                        <span class="badge badge-activo"><?= htmlspecialchars($producto['almacen'] ?? 'Sin almacen') ?></span>
                    </div>
                    <?php if (!empty($producto['descripcion'])): ?>
                        <p class="hero-description"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="productos-detail-card">
                <h2><i class="fa fa-print"></i> Configuracion de etiquetas</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" class="form-grid">
                    <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                    <div class="form-group">
                        <label for="almacen_id">Almacen</label>
                        <select id="almacen_id" name="almacen_id" required>
                            <option value="">Selecciona un almacen</option>
                            <?php foreach ($almacenes as $almacen): ?>
                                <option value="<?= (int) $almacen['id'] ?>" <?= (int) $almacen['id'] === $almacenSeleccionado ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($almacen['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lote">Lote o referencia</label>
                        <input type="text" id="lote" name="lote" maxlength="50" value="<?= htmlspecialchars($loteValor) ?>">
                    </div>

                    <div class="form-group">
                        <label for="unidad_etiqueta">Unidad mostrada</label>
                        <input type="text" id="unidad_etiqueta" name="unidad_etiqueta" maxlength="20" value="<?= htmlspecialchars($unidadEtiqueta) ?>">
                    </div>

                    <div class="form-group">
                        <label for="cantidad">Cantidad de etiquetas</label>
                        <input type="number" id="cantidad" name="cantidad" min="1" max="50" value="<?= (int) $cantidadValor ?>">
                        <small>Maximo 50 etiquetas por generacion.</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-main"><i class="fa fa-file-pdf"></i> Generar PDF</button>
                        <a href="productos.php" class="btn-secondary"><i class="fa fa-times"></i> Cancelar</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</div>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>

