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
    target.innerHTML = `<img src="${img}" alt="${data.nombre}">
        <div class="preview-meta">
            <span class="preview-label">Producto</span>
            <span class="preview-value">${data.nombre}</span>
            <span class="preview-label">Marca / Stock</span>
            <span class="preview-value">${data.marca} · ${data.stock} disponibles</span>
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
        const seguir = confirm(`Estas solicitando ${cantidad} y solo hay ${stockDisp} en stock. ¿Deseas continuar?`);
        if (!seguir) return;
    }
    materiales.push({ tipo, producto_id, producto_nombre, cantidad, observacion });
    actualizarTabla();
};

window.agregarMaterialExtra = function(){
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

window.actualizarTabla = function(){
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

window.eliminarMaterial = function(idx){
    materiales.splice(idx, 1);
    actualizarTabla();
};

window.filtrarOpciones = function(tipo){
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
