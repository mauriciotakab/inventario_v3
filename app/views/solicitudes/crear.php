<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Empleado', 'Almacen']);

$role   = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitar material y herramientas | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/solicitudes_form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="content-area">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>

        <main class="dashboard-main solicitud-main">
            <div class="solicitud-hero">
                <div class="hero-info">
                    <p class="hero-kicker">Solicitudes</p>
                    <h1>Planifica tus pedidos del día</h1>
                    <p class="solicitud-header-desc">
                        Selecciona consumibles o herramientas, revisa el resumen y envía tu requisición en pocos pasos.
                        La vista está optimizada para pantallas móviles, ideal para campo.
                    </p>
                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-success hero-alert">
                            <i class="fa fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="hero-meta">
                    <span class="hero-user"><i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($nombre) ?: 'Empleado' ?></span>
                    <span class="hero-role badge">Rol: <?= htmlspecialchars($role ?: 'Empleado') ?></span>
                    <a class="btn-ghost hero-link" href="/mis_solicitudes.php">
                        <i class="fa-solid fa-clipboard-list"></i> Mis solicitudes
                    </a>
                </div>
            </div>

            <ul class="solicitud-steps">
                <li class="step-item active"><span>1</span>Elige productos</li>
                <li class="step-item"><span>2</span>Confirma cantidades</li>
                <li class="step-item"><span>3</span>Envía tu solicitud</li>
            </ul>

            <div class="solicitud-layout">
                <div class="solicitud-left">
                    <div class="solicitud-grid cards-grid">
                        <section class="solicitud-card">
                            <div class="card-head">
                                <h2><i class="fa-solid fa-box"></i> Consumibles</h2>
                                <span class="card-hint"><i class="fa-solid fa-eye"></i> Vista detallada en el panel derecho</span>
                            </div>
                            <div class="solicitud-field input-icon">
                                <label for="busqueda_consumible">Buscar consumible</label>
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="search" id="busqueda_consumible" placeholder="Filtra por nombre o código" onkeyup="filtrarOpciones('consumible')">
                            </div>
                            <div class="solicitud-field">
                                <label for="select_consumible">Selecciona un consumible *</label>
                                <select id="select_consumible" onchange="mostrarPreview('consumible')">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($productos_consumibles as $p): ?>
                                        <?php
                                            $imgPath   = $p['imagen_url'] ?? '';
                                            $imgUrl    = $imgPath ? '/' . ltrim(str_replace('\\', '/', $imgPath), '/') : '/assets/images/placeholder.png';
                                            $unidad    = $p['unidad_abreviacion'] ?? $p['unidad_medida_nombre'] ?? '';
                                            $desc      = htmlspecialchars(trim((string) ($p['descripcion'] ?? '')), ENT_QUOTES, 'UTF-8');
                                            $categoria = htmlspecialchars($p['categoria'] ?? '-', ENT_QUOTES, 'UTF-8');
                                            $almacen   = htmlspecialchars($p['almacen'] ?? '-', ENT_QUOTES, 'UTF-8');
                                            $codigo    = htmlspecialchars($p['codigo'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $marca     = htmlspecialchars($p['marca'] ?? '-', ENT_QUOTES, 'UTF-8');
                                            $nombreOpt = htmlspecialchars($p['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <option value="<?= $p['id'] ?>"
                                                data-tipo="Consumible"
                                                data-stock="<?= htmlspecialchars($p['stock_actual'] ?? 0) ?>"
                                                data-stockmin="<?= htmlspecialchars($p['stock_minimo'] ?? 0) ?>"
                                                data-marca="<?= $marca ?>"
                                                data-nombre="<?= $nombreOpt ?>"
                                                data-img="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                                                data-categoria="<?= $categoria ?>"
                                                data-unidad="<?= htmlspecialchars($unidad, ENT_QUOTES, 'UTF-8') ?>"
                                                data-descripcion="<?= $desc ?>"
                                                data-codigo="<?= $codigo ?>"
                                                data-almacen="<?= $almacen ?>"
                                                data-estado="<?= htmlspecialchars($p['estado'] ?? '-', ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($p['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="solicitud-buttons">
                                <input type="number" step="0.01" id="cantidad_consumible" placeholder="Cantidad" min="0.01">
                                <input type="text" id="obs_consumible" placeholder="Observación (opcional)">
                                <button type="button" class="btn-secondary" onclick="agregarMaterial('Consumible')"><i class="fa fa-plus"></i> Agregar</button>
                            </div>
                        </section>

                        <section class="solicitud-card">
                            <div class="card-head">
                                <h2><i class="fa-solid fa-screwdriver-wrench"></i> Herramientas</h2>
                                <span class="card-hint"><i class="fa-solid fa-clock"></i> Requerirá fecha de devolución</span>
                            </div>
                            <div class="solicitud-field input-icon">
                                <label for="busqueda_herramienta">Buscar herramienta</label>
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="search" id="busqueda_herramienta" placeholder="Busca por nombre o código" onkeyup="filtrarOpciones('herramienta')">
                            </div>
                            <div class="solicitud-field">
                                <label for="select_herramienta">Selecciona una herramienta *</label>
                                <select id="select_herramienta" onchange="mostrarPreview('herramienta')">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($productos_herramientas as $p): ?>
                                        <?php
                                            $imgPath   = $p['imagen_url'] ?? '';
                                            $imgUrl    = $imgPath ? '/' . ltrim(str_replace('\\', '/', $imgPath), '/') : '/assets/images/placeholder.png';
                                            $unidad    = $p['unidad_abreviacion'] ?? $p['unidad_medida_nombre'] ?? '';
                                            $desc      = htmlspecialchars(trim((string) ($p['descripcion'] ?? '')), ENT_QUOTES, 'UTF-8');
                                            $categoria = htmlspecialchars($p['categoria'] ?? '-', ENT_QUOTES, 'UTF-8');
                                            $almacen   = htmlspecialchars($p['almacen'] ?? '-', ENT_QUOTES, 'UTF-8');
                                            $codigo    = htmlspecialchars($p['codigo'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $marca     = htmlspecialchars($p['marca'] ?? '-', ENT_QUOTES, 'UTF-8');
                                            $nombreOpt = htmlspecialchars($p['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <option value="<?= $p['id'] ?>"
                                                data-tipo="Herramienta"
                                                data-stock="<?= htmlspecialchars($p['stock_actual'] ?? 0) ?>"
                                                data-stockmin="<?= htmlspecialchars($p['stock_minimo'] ?? 0) ?>"
                                                data-marca="<?= $marca ?>"
                                                data-nombre="<?= $nombreOpt ?>"
                                                data-img="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                                                data-categoria="<?= $categoria ?>"
                                                data-unidad="<?= htmlspecialchars($unidad, ENT_QUOTES, 'UTF-8') ?>"
                                                data-descripcion="<?= $desc ?>"
                                                data-codigo="<?= $codigo ?>"
                                                data-almacen="<?= $almacen ?>"
                                                data-estado="<?= htmlspecialchars($p['estado'] ?? '-', ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($p['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="solicitud-buttons">
                                <input type="number" step="0.01" id="cantidad_herramienta" placeholder="Cantidad" min="0.01">
                                <input type="text" id="obs_herramienta" placeholder="Observación (opcional)">
                                <button type="button" class="btn-secondary" onclick="agregarMaterial('Herramienta')"><i class="fa fa-plus"></i> Agregar</button>
                            </div>
                        </section>
                    </div>

                    <section class="solicitud-card solicitud-extra">
                        <div class="card-head">
                            <h2><i class="fa-solid fa-cart-plus"></i> Material extra</h2>
                            <span class="card-hint"><i class="fa-solid fa-store"></i> Para compras o reposiciones especiales</span>
                        </div>
                        <div class="solicitud-field">
                            <label for="extra_nombre">Descripción</label>
                            <input type="text" id="extra_nombre" placeholder="Nombre o descripción del material extra">
                        </div>
                        <div class="solicitud-buttons wrap">
                            <input type="number" id="extra_cantidad" min="0.01" step="0.01" placeholder="Cantidad">
                            <input type="text" id="extra_observacion" placeholder="Observación (opcional)">
                            <button type="button" class="btn-secondary" onclick="agregarMaterialExtra()"><i class="fa fa-plus"></i> Agregar extra</button>
                        </div>
                        <p class="solicitud-header-desc small">Registra aquí lo que no esté en inventario; se notificará al área de compras.</p>
                    </section>

                    <section class="solicitud-summary">
                        <h3><i class="fa-solid fa-list-check"></i> Materiales y herramientas agregados</h3>
                        <div class="table-wrapper">
                            <table class="solicitud-items-table" id="tabla_materiales">
                                <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Observación</th>
                                    <th>Acción</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="solicitud-empty" id="tabla_vacia">
                                    <td colspan="5"><i class="fa-solid fa-box-open"></i>No has agregado materiales.</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <form method="post" id="solicitudForm" class="solicitud-card solicitud-form">
                        <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                        <input type="hidden" name="material" id="input_materiales">
                        <h2><i class="fa-solid fa-location-dot"></i> Información de la solicitud</h2>
                        <div class="solicitud-field">
                            <label for="comentario">Proyecto o destino *</label>
                            <input type="text" id="comentario" name="comentario" required placeholder="Ej. Instalación en planta norte">
                        </div>
                        <div class="solicitud-field">
                            <label for="observacion">Observaciones generales (opcional)</label>
                            <textarea id="observacion" name="observacion" placeholder="Notas para almacén, horarios, prioridad, etc."></textarea>
                        </div>
                        <div class="mobile-panel-hint">
                            <i class="fa-solid fa-circle-info"></i> Antes de enviar, revisa el resumen lateral o la tabla superior.
                        </div>
                        <div class="solicitud-submit">
                            <button type="submit" class="btn-main"><i class="fa fa-paper-plane"></i> Enviar solicitud</button>
                        </div>
                    </form>
                </div>

                <aside class="solicitud-aside">
                    <div class="solicitud-panel product-panel">
                        <div class="panel-title"><i class="fa-solid fa-eye"></i> Vista previa del producto</div>
                        <div class="product-preview-body" id="product_preview_body">
                            <div class="panel-empty" id="product_preview_empty">
                                Selecciona un consumible o herramienta para ver su foto, marca y disponibilidad.
                            </div>
                        </div>
                    </div>

                    <div class="solicitud-panel metrics-panel">
                        <div class="panel-title"><i class="fa-solid fa-clipboard-check"></i> Resumen rápido</div>
                        <div class="metrics-grid">
                            <div class="metric-card">
                                <span>Total ítems</span>
                                <strong id="summary_total_items">0</strong>
                            </div>
                            <div class="metric-card">
                                <span>Consumibles</span>
                                <strong id="summary_consumibles">0</strong>
                            </div>
                            <div class="metric-card">
                                <span>Herramientas</span>
                                <strong id="summary_herramientas">0</strong>
                            </div>
                            <div class="metric-card">
                                <span>Extras</span>
                                <strong id="summary_extras">0</strong>
                            </div>
                        </div>
                    </div>


                </aside>
            </div>

            <div id="stock_modal" class="stock-modal" aria-hidden="true">
                <div class="stock-modal-card">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <h4>Stock insuficiente</h4>
                    <p id="stock_modal_text">Estas solicitando más piezas de las disponibles.</p>
                    <div class="stock-modal-actions">
                        <button type="button" class="btn-ghost" data-stock-cancel>Cancelar</button>
                        <button type="button" class="btn-main" data-stock-continue>Continuar de todos modos</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
(function () {
    let materiales = [];
    let pendingStockAction = null;

    const previewBody = document.getElementById('product_preview_body');
    const previewEmpty = document.getElementById('product_preview_empty');
    const summaryTotal = document.getElementById('summary_total_items');
    const summaryConsumibles = document.getElementById('summary_consumibles');
    const summaryHerramientas = document.getElementById('summary_herramientas');
    const summaryExtras = document.getElementById('summary_extras');
    const stockModal = document.getElementById('stock_modal');
    const stockModalText = document.getElementById('stock_modal_text');
    const stockModalCancel = stockModal?.querySelector('[data-stock-cancel]');
    const stockModalContinue = stockModal?.querySelector('[data-stock-continue]');

    function obtenerPreviewData(select) {
        if (!select) return null;
        const option = select.options[select.selectedIndex];
        if (!option || !option.value) return null;
        const get = (attr) => option.getAttribute(attr) || '';
        return {
            nombre: get('data-nombre') || option.textContent,
            stock: parseFloat(get('data-stock') || '0'),
            stockMinimo: parseFloat(get('data-stockmin') || '0'),
            marca: get('data-marca') || '-',
            tipo: get('data-tipo') || '',
            img: get('data-img') || '',
            categoria: get('data-categoria') || '-',
            unidad: get('data-unidad') || '',
            descripcion: get('data-descripcion') || '',
            codigo: get('data-codigo') || '',
            almacen: get('data-almacen') || '-',
            estado: get('data-estado') || '-'
        };
    }

    function stockBadge(stock, min) {
        if (stock <= 0) return { label: 'Sin stock', css: 'danger' };
        if (min > 0 && stock < min) return { label: 'Bajo', css: 'warning' };
        return { label: 'Disponible', css: 'success' };
    }

    function renderPreview(tipoLabel, data) {
        if (!previewBody || !previewEmpty) return;
        if (!data) {
            previewBody.innerHTML = '';
            previewEmpty.style.display = 'block';
            return;
        }
        previewEmpty.style.display = 'none';
        const badge = stockBadge(data.stock, data.stockMinimo);
        const unidad = data.unidad ? ` ${data.unidad}` : '';
        previewBody.innerHTML = `
            <div class="product-preview-card">
                <div class="preview-header">
                    <img src="${data.img || '/assets/images/placeholder.png'}" alt="${data.nombre}"
                         onerror="this.onerror=null;this.src='/assets/images/placeholder.png';">
                    <div>
                        <p class="preview-type">${tipoLabel}</p>
                        <h4>${data.nombre}</h4>
                        <span class="stock-badge ${badge.css}">${badge.label}</span>
                    </div>
                </div>
                <div class="preview-meta">
                    <div>
                        <span class="preview-label">Código</span>
                        <p>${data.codigo || 'N/A'}</p>
                    </div>
                    <div>
                        <span class="preview-label">Categoría</span>
                        <p>${data.categoria || '-'}</p>
                    </div>
                    <div>
                        <span class="preview-label">Marca</span>
                        <p>${data.marca || '-'}</p>
                    </div>
                    <div>
                        <span class="preview-label">Stock disponible</span>
                        <p>${(data.stock ?? 0)}${unidad}</p>
                    </div>
                    <div>
                        <span class="preview-label">Almacén</span>
                        <p>${data.almacen || '-'}</p>
                    </div>
                    <div>
                        <span class="preview-label">Estado</span>
                        <p>${data.estado || '-'}</p>
                    </div>
                </div>
                ${data.descripcion ? `<p class="preview-description">${data.descripcion}</p>` : ''}
            </div>
        `;
    }

    window.mostrarPreview = function (tipo) {
        const select = tipo === 'consumible'
            ? document.getElementById('select_consumible')
            : document.getElementById('select_herramienta');
        const data = obtenerPreviewData(select);
        const label = tipo === 'consumible' ? 'Consumible' : 'Herramienta';
        renderPreview(label, data);
    };

    function resetCampos(tipo) {
        if (tipo === 'Consumible') {
            document.getElementById('select_consumible').value = '';
            document.getElementById('cantidad_consumible').value = '';
            document.getElementById('obs_consumible').value = '';
        } else if (tipo === 'Herramienta') {
            document.getElementById('select_herramienta').value = '';
            document.getElementById('cantidad_herramienta').value = '';
            document.getElementById('obs_herramienta').value = '';
        }
        renderPreview('', null);
    }

    function agregarItem(item) {
        materiales.push(item);
        actualizarTabla();
        actualizarResumen();
        resetCampos(item.tipo);
    }

    function mostrarModalStock(nombre, cantidad, stockDisp, callback) {
        if (!stockModal) {
            if (window.confirm(`Estás solicitando ${cantidad} y solo hay ${stockDisp} disponibles. ¿Deseas continuar?`)) {
                callback();
            }
            return;
        }
        stockModalText.textContent = `Solicitas ${cantidad} unidades de "${nombre}" pero solo hay ${stockDisp} disponibles. ¿Deseas continuar igualmente?`;
        stockModal.classList.add('active');
        stockModal.setAttribute('aria-hidden', 'false');
        pendingStockAction = callback;
    }

    function cerrarModalStock() {
        if (!stockModal) return;
        stockModal.classList.remove('active');
        stockModal.setAttribute('aria-hidden', 'true');
        pendingStockAction = null;
    }

    window.agregarMaterial = function (tipo) {
        const isConsumible = tipo === 'Consumible';
        const select = document.getElementById(isConsumible ? 'select_consumible' : 'select_herramienta');
        const cantidadInput = document.getElementById(isConsumible ? 'cantidad_consumible' : 'cantidad_herramienta');
        const obsInput = document.getElementById(isConsumible ? 'obs_consumible' : 'obs_herramienta');

        const cantidad = parseFloat(cantidadInput.value);
        const observacion = obsInput.value.trim();
        const option = select.options[select.selectedIndex];

        if (!option || !option.value || !cantidad || cantidad <= 0) {
            alert('Selecciona un producto válido y una cantidad mayor a cero.');
            return;
        }

        const producto_id = option.value;
        const producto_nombre = option.textContent;
        const stockDisp = parseFloat(option.getAttribute('data-stock') || '0');

        const item = { tipo, producto_id, producto_nombre, cantidad, observacion };

        if (stockDisp >= 0 && cantidad > stockDisp) {
            mostrarModalStock(producto_nombre, cantidad, stockDisp, () => agregarItem(item));
            return;
        }

        agregarItem(item);
    };

    window.agregarMaterialExtra = function () {
        const nombre = document.getElementById('extra_nombre').value.trim();
        const cantidad = parseFloat(document.getElementById('extra_cantidad').value);
        const observacion = document.getElementById('extra_observacion').value.trim();
        if (!nombre || !cantidad || cantidad <= 0) {
            alert('Escribe un nombre y una cantidad válida para el material extra.');
            return;
        }
        materiales.push({ tipo: 'Extra', producto_id: null, producto_nombre: nombre, cantidad, observacion });
        actualizarTabla();
        actualizarResumen();
        document.getElementById('extra_nombre').value = '';
        document.getElementById('extra_cantidad').value = '';
        document.getElementById('extra_observacion').value = '';
    };

    window.actualizarTabla = function () {
        const tbody = document.querySelector('#tabla_materiales tbody');
        tbody.innerHTML = '';
        if (materiales.length === 0) {
            tbody.innerHTML = '<tr class="solicitud-empty"><td colspan="5"><i class="fa-solid fa-box-open"></i>No has agregado materiales.</td></tr>';
        } else {
            materiales.forEach((item, idx) => {
                tbody.innerHTML += `
                    <tr>
                        <td>${item.tipo}</td>
                        <td>${item.producto_nombre || 'N/A'}</td>
                        <td>${item.cantidad}</td>
                        <td>${item.observacion || ''}</td>
                        <td><button type="button" class="btn-ghost" onclick="eliminarMaterial(${idx})"><i class="fa fa-trash"></i> Quitar</button></td>
                    </tr>`;
            });
        }
        document.getElementById('input_materiales').value = JSON.stringify(materiales);
    };

    function actualizarResumen() {
        if (!summaryTotal) return;
        summaryTotal.textContent = materiales.length;
        summaryConsumibles.textContent = materiales.filter(m => m.tipo === 'Consumible').length;
        summaryHerramientas.textContent = materiales.filter(m => m.tipo === 'Herramienta').length;
        summaryExtras.textContent = materiales.filter(m => m.tipo === 'Extra').length;
    }

    window.eliminarMaterial = function (idx) {
        materiales.splice(idx, 1);
        actualizarTabla();
        actualizarResumen();
    };

    window.filtrarOpciones = function (tipo) {
        const isConsumible = tipo === 'consumible';
        const input = document.getElementById(isConsumible ? 'busqueda_consumible' : 'busqueda_herramienta');
        const select = document.getElementById(isConsumible ? 'select_consumible' : 'select_herramienta');
        const filtro = input.value.toLowerCase();

        Array.from(select.options).forEach((opt, idx) => {
            if (idx === 0) return;
            const match = opt.textContent.toLowerCase().includes(filtro);
            opt.style.display = match ? '' : 'none';
        });
    };

    document.getElementById('solicitudForm').addEventListener('submit', function (event) {
        if (materiales.length === 0) {
            alert('Agrega al menos un material, herramienta o extra a la lista antes de enviar.');
            event.preventDefault();
        } else {
            document.getElementById('input_materiales').value = JSON.stringify(materiales);
        }
    });

    if (stockModalCancel) {
        stockModalCancel.addEventListener('click', cerrarModalStock);
    }
    if (stockModalContinue) {
        stockModalContinue.addEventListener('click', function () {
            if (pendingStockAction) {
                pendingStockAction();
            }
            cerrarModalStock();
        });
    }
    if (stockModal) {
        stockModal.addEventListener('click', function (e) {
            if (e.target === stockModal) {
                cerrarModalStock();
            }
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarModalStock();
        }
    });
})();
</script>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>
