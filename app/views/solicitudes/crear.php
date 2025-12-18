<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin('Empleado');

$role = $_SESSION['role'] ?? '';
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
    <style>\n        .preview-wrapper {margin-top:8px;}\n        .preview-card {display:flex; gap:14px; border:1px solid #e4e8f3; border-radius:12px; padding:12px; background:#f9fbff; box-shadow:0 2px 8px rgba(20,41,89,0.05);}\n        .preview-card img {width:76px; height:76px; object-fit:cover; border-radius:12px; border:1px solid #dfe6f7; background:#fff;}\n        .preview-meta {display:grid; gap:6px;}\n        .preview-label {font-size:0.85rem; color:#5a6a94; text-transform:uppercase; letter-spacing:.5px;}\n        .preview-value {font-weight:700; color:#122c57;}\n    </style></head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="content-area">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>

        <main class="dashboard-main solicitud-main">
            <div class="solicitud-header">
                <div>
                    <h1><i class="fa-solid fa-toolbox"></i> Solicitud de material y herramientas</h1>
                    <p class="solicitud-header-desc">Selecciona los consumibles u herramientas que necesitas para tu proyecto o servicio.</p>
                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-success" style="margin-top:12px;"><i class="fa fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="solicitud-grid">
                <section class="solicitud-card">
                    <h2><i class="fa-solid fa-box"></i> Consumibles</h2>
                    <div class="solicitud-field">
                        <label for="busqueda_consumible">Buscar consumible</label>
                        <input type="search" id="busqueda_consumible" placeholder="Buscar consumible..." onkeyup="filtrarOpciones('consumible')">
                    </div>
                    <div class="solicitud-field">
                        <label for="select_consumible">Selecciona un consumible *</label>
                        <select id="select_consumible" onchange="mostrarPreview('consumible')">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($productos_consumibles as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                        data-tipo="Consumible"
                                        data-stock="<?= htmlspecialchars($p['stock_actual'] ?? 0) ?>"
                                        data-marca="<?= htmlspecialchars($p['marca'] ?? '-') ?>"
                                        data-nombre="<?= htmlspecialchars($p['nombre'] ?? '') ?>"
                                        data-img="<?= htmlspecialchars($p['imagen_url'] ?? '') ?>">
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="preview-wrapper"><div id="preview_consumible" class="preview-card" style="display:none;"></div></div>
                    <div class="solicitud-buttons">
                        <input type="number" step="0.01" id="cantidad_consumible" placeholder="Cantidad" min="0.01">
                        <input type="text" id="obs_consumible" placeholder="Observación (opcional)">
                        <button type="button" class="btn-secondary" onclick="agregarMaterial('Consumible')"><i class="fa fa-plus"></i> Agregar</button>
                    </div>
                </section>

                <section class="solicitud-card">
                    <h2><i class="fa-solid fa-screwdriver-wrench"></i> Herramientas</h2>
                    <div class="solicitud-field">
                        <label for="busqueda_herramienta">Buscar herramienta</label>
                        <input type="search" id="busqueda_herramienta" placeholder="Buscar herramienta..." onkeyup="filtrarOpciones('herramienta')">
                    </div>
                    <div class="solicitud-field">
                        <label for="select_herramienta">Selecciona una herramienta *</label>
                        <select id="select_herramienta" onchange="mostrarPreview('herramienta')">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($productos_herramientas as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                        data-tipo="Herramienta"
                                        data-stock="<?= htmlspecialchars($p['stock_actual'] ?? 0) ?>"
                                        data-marca="<?= htmlspecialchars($p['marca'] ?? '-') ?>"
                                        data-nombre="<?= htmlspecialchars($p['nombre'] ?? '') ?>"
                                        data-img="<?= htmlspecialchars($p['imagen_url'] ?? '') ?>">
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="preview-wrapper"><div id="preview_herramienta" class="preview-card" style="display:none;"></div></div>
                    <div class="solicitud-buttons">
                        <input type="number" step="0.01" id="cantidad_herramienta" placeholder="Cantidad" min="0.01">
                        <input type="text" id="obs_herramienta" placeholder="Observación (opcional)">
                        <button type="button" class="btn-secondary" onclick="agregarMaterial('Herramienta')"><i class="fa fa-plus"></i> Agregar</button>
                    </div>
                </section>

                <section class="solicitud-card">
                    <h2><i class="fa-solid fa-cart-plus"></i> Material extra (opcional)</h2>
                    <div class="solicitud-field">
                        <label for="extra_nombre">Descripción</label>
                        <input type="text" id="extra_nombre" placeholder="Nombre o descripción del material extra">
                    </div>
                    <div class="solicitud-buttons">
                        <input type="number" id="extra_cantidad" min="0.01" step="0.01" placeholder="Cantidad">
                        <input type="text" id="extra_observacion" placeholder="Observación (opcional)">
                        <button type="button" class="btn-secondary" onclick="agregarMaterialExtra()"><i class="fa fa-plus"></i> Agregar extra</button>
                    </div>
                    <p class="solicitud-header-desc">Utiliza esta sección para registrar materiales no contemplados en inventario y que deban comprarse.</p>
                </section>
            </div>

            <section class="solicitud-summary">
                <h3><i class="fa-solid fa-list-check"></i> Materiales / herramientas solicitadas</h3>
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
                            <tr class="solicitud-empty" id="tabla_vacia"><td colspan="5"><i class="fa-solid fa-box-open"></i>No has agregado materiales.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <form method="post" id="solicitudForm">
                <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="material" id="input_materiales">
                <div class="solicitud-grid">
                    <section class="solicitud-card">
                        <h2><i class="fa-solid fa-location-dot"></i> Información de la solicitud</h2>
                        <div class="solicitud-field">
                            <label for="comentario">Proyecto o destino *</label>
                            <input type="text" id="comentario" name="comentario" required placeholder="Ej. Instalación en planta norte">
                        </div>
                        <div class="solicitud-field">
                            <label for="observacion">Observaciones generales (opcional)</label>
                            <textarea id="observacion" name="observacion" placeholder="Notas para almacén, horarios, etc."></textarea>
                        </div>
                    </section>
                </div>
                <div class="solicitud-submit">
                    <button type="submit" class="btn-main"><i class="fa fa-paper-plane"></i> Enviar solicitud</button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
(function(){
let materiales = [];

function obtenerPreviewData(select){
    const option = select.options[select.selectedIndex];
    if (!option || !option.value) return null;
    return {
        nombre: option.getAttribute('data-nombre') || option.textContent,
        stock: parseFloat(option.getAttribute('data-stock') || '0'),
        marca: option.getAttribute('data-marca') || '-',
        tipo: option.getAttribute('data-tipo') || '',
        img: option.getAttribute('data-img') || ''
    };
}

window.mostrarPreview = function(tipo){
    const select = tipo === 'consumible' ? document.getElementById('select_consumible') : document.getElementById('select_herramienta');
    const target = document.getElementById('preview_' + tipo);
    const data = obtenerPreviewData(select);
    if (!data) { target.style.display = 'none'; target.innerHTML=''; return; }
    const img = data.img ? data.img : '/assets/images/placeholder.png';
    target.style.display = 'flex';
    target.innerHTML = `<img src="${img}" alt="${data.nombre}" onerror="this.onerror=null;this.src='/assets/images/placeholder.png';">
        <div class="preview-meta">
            <span class="preview-label">Producto</span>
            <span class="preview-value">${data.nombre}</span>
            <span class="preview-label">Marca / Stock</span>
            <span class="preview-value">${data.marca} ? ${data.stock} disponibles</span>
            <span class="preview-label">Tipo</span>
            <span class="preview-value">${data.tipo}</span>
        </div>`;
};

window.agregarMaterial = function(tipo){
    let select, cantidad, observacion;
    if (tipo === 'Consumible') {
        select = document.getElementById('select_consumible');
        cantidad = parseFloat(document.getElementById('cantidad_consumible').value);
        observacion = document.getElementById('obs_consumible').value;
    } else {
        select = document.getElementById('select_herramienta');
        cantidad = parseFloat(document.getElementById('cantidad_herramienta').value);
        observacion = document.getElementById('obs_herramienta').value;
    }
    const producto_id = select.value;
    const producto_nombre = select.options[select.selectedIndex]?.text || '';
    const stockDisp = parseFloat(select.options[select.selectedIndex]?.getAttribute('data-stock') || '0');
    if (!producto_id || !cantidad || cantidad <= 0) {
        alert('Selecciona un producto y una cantidad valida.');
        return;
    }
    if (stockDisp && cantidad > stockDisp) {
        const seguir = confirm(`Estas solicitando ${cantidad} y solo hay ${stockDisp} en stock. ?Deseas continuar?`);
        if (!seguir) return;
    }
    materiales.push({ tipo, producto_id, producto_nombre, cantidad, observacion });
    actualizarTabla();
};

window.agregarMaterialExtra = function() {
    const nombre = document.getElementById('extra_nombre').value.trim();
    const cantidad = parseFloat(document.getElementById('extra_cantidad').value);
    const observacion = document.getElementById('extra_observacion').value;
    if (!nombre || !cantidad || cantidad <= 0) {
        alert('Escribe nombre y cantidad valida para el material extra.');
        return;
    }
    materiales.push({ tipo: 'Extra', producto_id: null, producto_nombre: nombre, cantidad, observacion });
    actualizarTabla();
    document.getElementById('extra_nombre').value = '';
    document.getElementById('extra_cantidad').value = '';
    document.getElementById('extra_observacion').value = '';
};

window.actualizarTabla = function() {
    const tbody = document.querySelector('#tabla_materiales tbody');
    tbody.innerHTML = '';
    if (materiales.length === 0) {
        tbody.innerHTML = '<tr class="solicitud-empty"><td colspan="5"><i class="fa-solid fa-box-open"></i>No has agregado materiales.</td></tr>';
    } else {
        materiales.forEach((item, idx) => {
            tbody.innerHTML += `<tr>
                <td>${item.tipo}</td>
                <td>${item.producto_nombre}</td>
                <td>${item.cantidad}</td>
                <td>${item.observacion || ''}</td>
                <td><button type="button" class="btn-ghost" onclick="eliminarMaterial(${idx})"><i class="fa fa-trash"></i> Quitar</button></td>
            </tr>`;
        });
    }
    document.getElementById('input_materiales').value = JSON.stringify(materiales);
};

window.eliminarMaterial = function(idx) {
    materiales.splice(idx, 1);
    actualizarTabla();
};

window.filtrarOpciones = function(tipo) {
    let input, select;
    if (tipo === 'consumible') {
        input = document.getElementById('busqueda_consumible').value.toLowerCase();
        select = document.getElementById('select_consumible');
    } else {
        input = document.getElementById('busqueda_herramienta').value.toLowerCase();
        select = document.getElementById('select_herramienta');
    }
    Array.from(select.options).forEach((opt, idx) => {
        if (idx === 0) return;
        opt.style.display = opt.text.toLowerCase().includes(input) ? '' : 'none';
    });
};

document.getElementById('solicitudForm').addEventListener('submit', function (event) {
    if (materiales.length === 0) {
        alert('Agrega al menos un material o herramienta a la lista.');
        event.preventDefault();
    } else {
        document.getElementById('input_materiales').value = JSON.stringify(materiales);
    }
});
})();
</script>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>
