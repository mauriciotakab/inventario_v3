<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Database.php';
require_once __DIR__ . '/../helpers/ActivityLogger.php';
require_once __DIR__ . '/../models/OrdenCompra.php';
require_once __DIR__ . '/../models/Producto.php';

class OrdenCompraController
{
    public function index(): void
    {
        Session::requireLogin(['Administrador', 'Compras', 'Almacen']);
        $db = Database::getInstance()->getConnection();

        $filtros = [
            'proveedor_id' => $_GET['proveedor_id'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'desde' => $_GET['desde'] ?? '',
            'hasta' => $_GET['hasta'] ?? '',
        ];

        $historial = OrdenCompra::historial([
            'proveedor_id' => $filtros['proveedor_id'] ?: null,
            'desde' => $filtros['desde'] ?: null,
            'hasta' => $filtros['hasta'] ?: null,
        ]);

        if (!empty($filtros['estado'])) {
            $estadoFiltro = strtolower($filtros['estado']);
            $historial['ordenes'] = array_values(array_filter(
                $historial['ordenes'],
                fn($orden) => strtolower($orden['estado'] ?? '') === $estadoFiltro
            ));
        }

        $proveedores = $db->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll();
        $estados = ['Pendiente', 'Enviada', 'Recibida', 'Cancelada'];

        include __DIR__ . '/../views/ordenes/index.php';
    }

    public function crear(): void
    {
        Session::requireLogin(['Administrador', 'Compras']);
        $db = Database::getInstance()->getConnection();

        $proveedores = $db->query("SELECT id, nombre, rfc FROM proveedores ORDER BY nombre ASC")->fetchAll();
        $almacenes = $db->query("SELECT id, nombre FROM almacenes ORDER BY nombre ASC")->fetchAll();
        $productos = $db->query("SELECT id, codigo, nombre, tipo FROM productos ORDER BY nombre ASC")->fetchAll();
        $categorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
        $unidades = $db->query("SELECT id, nombre, abreviacion FROM unidades_medida ORDER BY nombre ASC")->fetchAll();

        $errors = [];
        $msg = '';

        if (\['REQUEST_METHOD'] === 'POST' && !Session::checkCsrf(\['csrf'] ?? '')) {
            \[] = 'Token CSRF inválido.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cabecera = $this->buildCabeceraFromRequest($_POST);
            $items = $this->buildDetallesFromRequest($_POST, $cabecera, $errors);

            if (empty($items)) {
                $errors[] = 'Debes agregar al menos un producto a la orden.';
            }

            if (empty($errors)) {
                try {
                    $cabecera['usuario_id'] = $_SESSION['user_id'] ?? null;
                    $ordenId = OrdenCompra::crear($cabecera, $items);
                    ActivityLogger::log('orden_compra_creada', 'Orden de compra creada', [
                        'orden_id' => $ordenId,
                        'proveedor_id' => $cabecera['proveedor_id'],
                    ]);
                    header('Location: ordenes_compra_detalle.php?id=' . $ordenId . '&created=1');
                    exit();
                } catch (\Throwable $e) {
                    $errors[] = 'Error al registrar la orden: ' . $e->getMessage();
                }
            }
        }

        include __DIR__ . '/../views/ordenes/form.php';
    }

    public function editar(int $id): void
    {
        Session::requireLogin(['Administrador', 'Compras']);
        $orden = OrdenCompra::find($id);
        if (!$orden) {
            header('Location: ordenes_compra.php?not_found=1');
            exit();
        }

        if (strtolower($orden['estado']) === 'recibida' || strtolower($orden['estado']) === 'cancelada') {
            header('Location: ordenes_compra_detalle.php?id=' . $id . '&locked=1');
            exit();
        }

        $db = Database::getInstance()->getConnection();
        $proveedores = $db->query("SELECT id, nombre, rfc FROM proveedores ORDER BY nombre ASC")->fetchAll();
        $almacenes = $db->query("SELECT id, nombre FROM almacenes ORDER BY nombre ASC")->fetchAll();
        $productos = $db->query("SELECT id, codigo, nombre, tipo FROM productos ORDER BY nombre ASC")->fetchAll();
        $categorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
        $unidades = $db->query("SELECT id, nombre, abreviacion FROM unidades_medida ORDER BY nombre ASC")->fetchAll();

        $errors = [];
        $msg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cabecera = $this->buildCabeceraFromRequest($_POST);
            $items = $this->buildDetallesFromRequest($_POST, $cabecera, $errors);

            if (empty($items)) {
                $errors[] = 'Debes agregar al menos un producto a la orden.';
            }

            if (empty($errors)) {
                try {
                    $cabecera['usuario_id'] = $_SESSION['user_id'] ?? ($orden['usuario_id'] ?? null);
                    OrdenCompra::actualizar($id, $cabecera, $items);
                    ActivityLogger::log('orden_compra_editada', 'Orden de compra actualizada', [
                        'orden_id' => $id,
                        'estado' => $cabecera['estado'] ?? 'Pendiente',
                    ]);
                    header('Location: ordenes_compra_detalle.php?id=' . $id . '&updated=1');
                    exit();
                } catch (\Throwable $e) {
                    $errors[] = 'No fue posible actualizar la orden: ' . $e->getMessage();
                }
            }

            $orden = array_merge($orden, $cabecera);
            $orden['detalles'] = $items;
        }

        include __DIR__ . '/../views/ordenes/form.php';
    }

    public function detalle(int $id): void
    {
        Session::requireLogin(['Administrador', 'Compras', 'Almacen']);
        $orden = OrdenCompra::find($id);
        if (!$orden) {
            header('Location: ordenes_compra.php?not_found=1');
            exit();
        }

        $db = Database::getInstance()->getConnection();
        $almacenes = $db->query("SELECT id, nombre FROM almacenes ORDER BY nombre ASC")->fetchAll();

        $msg = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'recibir' && strtolower($orden['estado']) !== 'recibida') {
                $extras = [
                    'numero_factura' => trim($_POST['numero_factura'] ?? ''),
                    'rfc' => trim($_POST['rfc'] ?? ''),
                    'almacen_destino_id' => $_POST['almacen_destino_id'] ?? $orden['almacen_destino_id'] ?? null,
                ];
                $extras['almacen_destino_id'] = $extras['almacen_destino_id'] ? (int) $extras['almacen_destino_id'] : null;
                if (empty($extras['almacen_destino_id'])) {
                    $error = 'Debes seleccionar el almacÃ©n destino para registrar la recepciÃ³n.';
                } else {
                    try {
                        OrdenCompra::actualizarEstado($id, 'Recibida', $extras);
                        ActivityLogger::log('orden_compra_recibida', 'Orden de compra recibida', [
                            'orden_id' => $id,
                            'numero_factura' => $extras['numero_factura'] ?: null,
                        ]);
                        header('Location: ordenes_compra_detalle.php?id=' . $id . '&received=1');
                        exit();
                    } catch (\Throwable $e) {
                        $error = 'No fue posible registrar la recepciÃ³n: ' . $e->getMessage();
                    }
                }
            } elseif ($accion === 'cancelar' && strtolower($orden['estado']) !== 'cancelada') {
                try {
                    OrdenCompra::actualizarEstado($id, 'Cancelada');
                    ActivityLogger::log('orden_compra_cancelada', 'Orden de compra cancelada', [
                        'orden_id' => $id,
                    ]);
                    header('Location: ordenes_compra_detalle.php?id=' . $id . '&cancelled=1');
                    exit();
                } catch (\Throwable $e) {
                    $error = 'No fue posible cancelar la orden: ' . $e->getMessage();
                }
            }
            $orden = OrdenCompra::find($id) ?: $orden;
        }

        include __DIR__ . '/../views/ordenes/detalle.php';
    }

    private function buildCabeceraFromRequest(array $input): array
    {
        return [
            'proveedor_id' => (int) ($input['proveedor_id'] ?? 0),
            'solicitud_id' => !empty($input['solicitud_id']) ? (int) $input['solicitud_id'] : null,
            'rfc' => trim($input['rfc'] ?? '') ?: null,
            'numero_factura' => trim($input['numero_factura'] ?? '') ?: null,
            'fecha' => !empty($input['fecha']) ? $input['fecha'] . (strlen($input['fecha']) === 10 ? ' 00:00:00' : '') : date('Y-m-d H:i:s'),
            'estado' => $input['estado'] ?? 'Pendiente',
            'almacen_destino_id' => !empty($input['almacen_destino_id']) ? (int) $input['almacen_destino_id'] : null,
        ];
    }

    private function buildDetallesFromRequest(array $input, array $cabecera, array &$errors): array
    {
        $tipos = $input['item_tipo'] ?? [];
        $cantidad = $input['item_cantidad'] ?? [];
        $productoIds = $input['item_producto_id'] ?? [];
        $costos = $input['item_costo'] ?? [];
        $preciosVenta = $input['item_precio_venta'] ?? [];

        $codigos = $input['item_codigo'] ?? [];
        $nombres = $input['item_nombre'] ?? [];
        $tiposProducto = $input['item_tipo_producto'] ?? [];
        $unidades = $input['item_unidad'] ?? [];
        $categorias = $input['item_categoria'] ?? [];
        $stockMinimo = $input['item_stock_minimo'] ?? [];

        $detalles = [];

        foreach ($tipos as $index => $tipo) {
            $tipo = strtolower($tipo);
            $cant = isset($cantidad[$index]) ? (float) str_replace(',', '.', $cantidad[$index]) : 0;
            $costo = isset($costos[$index]) ? (float) str_replace(',', '.', $costos[$index]) : 0;
            $precioVenta = isset($preciosVenta[$index]) ? (float) str_replace(',', '.', $preciosVenta[$index]) : 0;

            if ($cant <= 0) {
                $errors[] = "La cantidad debe ser mayor a cero (fila " . ($index + 1) . ").";
                continue;
            }
            if ($costo < 0) {
                $errors[] = "El costo unitario no puede ser negativo (fila " . ($index + 1) . ").";
                continue;
            }

            if ($tipo === 'existente') {
                $productoId = isset($productoIds[$index]) ? (int) $productoIds[$index] : 0;
                if ($productoId <= 0) {
                    $errors[] = "Debes seleccionar un producto existente (fila " . ($index + 1) . ").";
                    continue;
                }
                $detalles[] = [
                    'producto_id' => $productoId,
                    'cantidad' => $cant,
                    'precio_unitario' => $costo,
                ];
            } else {
                $codigo = trim($codigos[$index] ?? '');
                $nombre = trim($nombres[$index] ?? '');
                $tipoProducto = trim($tiposProducto[$index] ?? '');
                $unidadId = isset($unidades[$index]) ? (int) $unidades[$index] : null;
                $categoriaId = isset($categorias[$index]) ? (int) $categorias[$index] : null;
                $stockMin = isset($stockMinimo[$index]) ? (float) str_replace(',', '.', $stockMinimo[$index]) : 0;

                if ($nombre === '') {
                    $errors[] = "El nombre del nuevo producto es obligatorio (fila " . ($index + 1) . ").";
                    continue;
                }
                if ($codigo === '') {
                    $errors[] = "El cÃ³digo del nuevo producto es obligatorio (fila " . ($index + 1) . ").";
                    continue;
                }
                if (!in_array($tipoProducto, Producto::tiposDisponibles(), true)) {
                    $errors[] = "El tipo del nuevo producto no es vÃ¡lido (fila " . ($index + 1) . ").";
                    continue;
                }

                try {
                    $productoId = $this->crearProductoDesdeOrden([
                        'codigo' => $codigo,
                        'nombre' => $nombre,
                        'tipo' => $tipoProducto,
                        'unidad_medida_id' => $unidadId,
                        'categoria_id' => $categoriaId,
                        'costo_compra' => $costo,
                        'precio_venta' => $precioVenta,
                        'stock_minimo' => $stockMin,
                        'almacen_id' => $cabecera['almacen_destino_id'] ?? null,
                        'proveedor_id' => $cabecera['proveedor_id'] ?? null,
                    ]);
                } catch (\Throwable $e) {
                    $errors[] = "No fue posible registrar el nuevo producto (fila " . ($index + 1) . "): " . $e->getMessage();
                    continue;
                }

                if ($productoId) {
                    $detalles[] = [
                        'producto_id' => $productoId,
                        'cantidad' => $cant,
                        'precio_unitario' => $costo,
                    ];
                }
            }
        }

        return $detalles;
    }

    private function crearProductoDesdeOrden(array $data): int
    {
        $existente = Producto::findByCodigo($data['codigo']);
        if ($existente) {
            return (int) $existente['id'];
        }

        $payload = [
            'codigo' => $data['codigo'],
            'nombre' => $data['nombre'],
            'descripcion' => '',
            'proveedor_id' => $data['proveedor_id'] ?? null,
            'categoria_id' => $data['categoria_id'] ?? null,
            'peso' => null,
            'ancho' => null,
            'alto' => null,
            'profundidad' => null,
            'unidad_medida_id' => $data['unidad_medida_id'] ?? null,
            'clase_categoria' => null,
            'marca' => null,
            'color' => null,
            'forma' => null,
            'especificaciones_tecnicas' => null,
            'origen' => null,
            'costo_compra' => $data['costo_compra'] ?? 0,
            'precio_venta' => $data['precio_venta'] ?? 0,
            'stock_minimo' => $data['stock_minimo'] ?? 0,
            'stock_actual' => 0,
            'almacen_id' => $data['almacen_id'] ?? null,
            'estado' => 'Nuevo',
            'tipo' => $data['tipo'],
            'imagen_url' => null,
            'last_requested_by_user_id' => null,
            'last_request_date' => null,
            'tags' => null,
        ];

        Producto::create($payload);
        $db = Database::getInstance()->getConnection();
        return (int) $db->lastInsertId();
    }
}


