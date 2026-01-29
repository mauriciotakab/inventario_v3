<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Empleado', 'Almacen']);

$role = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud general de material | TAKAB</title>
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
            <div class="solicitud-header">
                <div>
                    <h1><i class="fa-solid fa-cart-plus"></i> Solicitud general de material</h1>
                    <p class="solicitud-header-desc">Registra materiales o herramientas no disponibles en inventario para que sean adquiridos.</p>
                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-success" style="margin-top:12px;"><i class="fa fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <section class="solicitud-card" style="margin-bottom:24px;">
                <h2><i class="fa-solid fa-list-ul"></i> Material a solicitar</h2>
                <div class="solicitud-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
                    <div class="solicitud-field">
                        <label for="extra_nombre">Descripción *</label>
                        <input type="text" id="extra_nombre" placeholder="Nombre o descripción del material/herramienta">
                    </div>
                    <div class="solicitud-field">
                        <label for="extra_cantidad">Cantidad *</label>
                        <input type="number" id="extra_cantidad" min="1" placeholder="Cantidad">
                    </div>
                    <div class="solicitud-field">
                        <label for="extra_observacion">Observación (opcional)</label>
                        <input type="text" id="extra_observacion" placeholder="Observaciones, proveedor sugerido, etc.">
                    </div>
                </div>
                <div class="solicitud-buttons">
                    <button type="button" class="btn-secondary" onclick="agregarMaterialExtra()"><i class="fa fa-plus"></i> Agregar a la lista</button>
                </div>
            </section>

            <section class="solicitud-summary">
                <h3><i class="fa-solid fa-clipboard-list"></i> Lista de materiales a comprar</h3>
                <div class="table-wrapper">
                    <table class="solicitud-items-table" id="tabla_materiales">
                        <thead>
                            <tr>
                                <th>Nombre / descripción</th>
                                <th>Cantidad</th>
                                <th>Observación</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="solicitud-empty"><td colspan="4"><i class="fa-solid fa-box-open"></i>Aún no agregas materiales.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <form method="post" id="solicitudForm">
                <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="material" id="input_materiales">
                <div class="solicitud-grid">
                    <section class="solicitud-card">
                        <h2><i class="fa-solid fa-circle-info"></i> Información adicional</h2>
                        <div class="solicitud-field">
                            <label for="comentario">Motivo de la solicitud *</label>
                            <input type="text" id="comentario" name="comentario" required placeholder="Ej. Proyecto especial, mantenimiento, etc.">
                        </div>
                        <div class="solicitud-field">
                            <label for="observacion">Observaciones generales (opcional)</label>
                            <textarea id="observacion" name="observacion" placeholder="Detalles relevantes para compras"></textarea>
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

function agregarMaterialExtra() {
    const nombre = document.getElementById('extra_nombre').value.trim();
    const cantidad = document.getElementById('extra_cantidad').value;
    const observacion = document.getElementById('extra_observacion').value;
    if (!nombre || !cantidad || cantidad < 1) {
        alert('Escribe nombre y una cantidad válida.');
        return;
    }
    materiales.push({ producto_nombre: nombre, cantidad, observacion });
    actualizarTabla();
    document.getElementById('extra_nombre').value = '';
    document.getElementById('extra_cantidad').value = '';
    document.getElementById('extra_observacion').value = '';
}

function actualizarTabla() {
    const tbody = document.querySelector('#tabla_materiales tbody');
    tbody.innerHTML = '';
    if (materiales.length === 0) {
        tbody.innerHTML = '<tr class="solicitud-empty"><td colspan="4"><i class="fa-solid fa-box-open"></i>Aún no agregas materiales.</td></tr>';
    } else {
        materiales.forEach((item, idx) => {
            tbody.innerHTML += `<tr>
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

document.getElementById('solicitudForm').addEventListener('submit', function (event) {
    if (materiales.length === 0) {
        alert('Agrega al menos un material u herramienta a la lista.');
        event.preventDefault();
    } else {
        document.getElementById('input_materiales').value = JSON.stringify(materiales);
    }
});
</script>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>
