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
            <a href="solicitudes_crear.php" class="active"><i class="fa-solid fa-comments"></i> Solicitar Material para Servicio</a>
            <a href="solicitar_material_general.php"><i class="fa-solid fa-cart-plus"></i> Solicitud general</a>
            <a href="mis_solicitudes.php"><i class="fa-solid fa-clipboard-list"></i> Mis Solicitudes</a>
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
                        <select id="select_consumible">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($productos_consumibles as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="solicitud-buttons">
                        <input type="number" id="cantidad_consumible" placeholder="Cantidad" min="1">
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
                        <select id="select_herramienta">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($productos_herramientas as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="solicitud-buttons">
                        <input type="number" id="cantidad_herramienta" placeholder="Cantidad" min="1">
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
                        <input type="number" id="extra_cantidad" min="1" placeholder="Cantidad">
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
let materiales = [];

function agregarMaterial(tipo) {
    let select, cantidad, observacion;
    if (tipo === 'Consumible') {
        select = document.getElementById('select_consumible');
        cantidad = document.getElementById('cantidad_consumible').value;
        observacion = document.getElementById('obs_consumible').value;
    } else {
        select = document.getElementById('select_herramienta');
        cantidad = document.getElementById('cantidad_herramienta').value;
        observacion = document.getElementById('obs_herramienta').value;
    }
    const producto_id = select.value;
    const producto_nombre = select.options[select.selectedIndex]?.text || '';
    if (!producto_id || !cantidad || cantidad < 1) {
        alert('Selecciona un producto y una cantidad válida.');
        return;
    }
    materiales.push({ tipo, producto_id, producto_nombre, cantidad, observacion });
    actualizarTabla();
}

function agregarMaterialExtra() {
    const nombre = document.getElementById('extra_nombre').value.trim();
    const cantidad = document.getElementById('extra_cantidad').value;
    const observacion = document.getElementById('extra_observacion').value;
    if (!nombre || !cantidad || cantidad < 1) {
        alert('Escribe nombre y cantidad válida para el material extra.');
        return;
    }
    materiales.push({ tipo: 'Extra', producto_id: null, producto_nombre: nombre, cantidad, observacion });
    actualizarTabla();
    document.getElementById('extra_nombre').value = '';
    document.getElementById('extra_cantidad').value = '';
    document.getElementById('extra_observacion').value = '';
}

function actualizarTabla() {
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
}

function eliminarMaterial(idx) {
    materiales.splice(idx, 1);
    actualizarTabla();
}

function filtrarOpciones(tipo) {
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
}

document.getElementById('solicitudForm').addEventListener('submit', function (event) {
    if (materiales.length === 0) {
        alert('Agrega al menos un material o herramienta a la lista.');
        event.preventDefault();
    } else {
        document.getElementById('input_materiales').value = JSON.stringify(materiales);
    }
});
</script>
</body>
</html>
