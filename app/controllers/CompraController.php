<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Database.php';
require_once __DIR__ . '/../helpers/ActivityLogger.php';
require_once __DIR__ . '/../models/OrdenCompra.php';

class CompraController
{
    public function historial(): void
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);

        $filtros = [
            'proveedor_id' => $_GET['proveedor_id'] ?? '',
            'desde' => $_GET['desde'] ?? date('Y-m-01'),
            'hasta' => $_GET['hasta'] ?? date('Y-m-d'),
        ];

        if ($filtros['desde'] > $filtros['hasta']) {
            [$filtros['desde'], $filtros['hasta']] = [$filtros['hasta'], $filtros['desde']];
        }

        $db = Database::getInstance()->getConnection();
        $proveedores = $db->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll();

        $historial = OrdenCompra::historial($filtros);

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            ActivityLogger::log('compras_export', 'Descarga de historial de compras', [
                'proveedor_id' => $filtros['proveedor_id'] ?: null,
                'desde' => $filtros['desde'],
                'hasta' => $filtros['hasta'],
            ]);
            $filename = 'compras_proveedor_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            $out = fopen('php://output', 'w');
            fputs($out, chr(239) . chr(187) . chr(191));
            fputcsv($out, ['Orden', 'Fecha', 'Proveedor', 'Estado', 'Productos', 'Importe detalle', 'Importe total']);
            foreach ($historial['ordenes'] as $orden) {
                fputcsv($out, [
                    $orden['id'],
                    $orden['fecha'],
                    $orden['proveedor'],
                    $orden['estado'],
                    number_format((float) ($orden['total_items'] ?? 0), 2, '.', ''),
                    number_format((float) ($orden['subtotal'] ?? 0), 2, '.', ''),
                    number_format((float) ($orden['total'] ?? $orden['subtotal'] ?? 0), 2, '.', ''),
                ]);
            }
            fclose($out);
            return;
        }

        include __DIR__ . '/../views/compras/historial.php';
    }

    public function crear(): void
    {
        Session::requireLogin(['Administrador', 'Compras']);
        $db = Database::getInstance()->getConnection();
        $proveedores = $db->query('SELECT id, nombre, rfc FROM proveedores ORDER BY nombre ASC')->fetchAll();
        $productos = Producto::all();
        $almacenes = $db->query('SELECT id, nombre FROM almacenes ORDER BY nombre ASC')->fetchAll();
        $unidades = $db->query('SELECT id, nombre, abreviacion FROM unidades_medida ORDER BY nombre ASC')->fetchAll();
        $categorias = $db->query('SELECT id, nombre FROM categorias ORDER BY nombre ASC')->fetchAll();
        $tiposProducto = Producto::tiposDisponibles();
        $mensaje = '';
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $itemsJson = $_POST['items'] ?? '[]';
            $items = json_decode($itemsJson, true);
            if (!is_array($items) || empty($items)) {
                $errores[] = 'Debes agregar al menos un producto a la orden.';
            }

            if (empty($errores)) {
                try {
                    $db->beginTransaction();

                    $proveedorId = (int) ($_POST['proveedor_id'] ?? 0);
                    $almacenId = (int) ($_POST['almacen_id'] ?? 0);
                    $estado = $_POST['estado'] ?? 'Pendiente';
                    $fecha = $_POST['fecha'] ?? date('Y-m-d');
                    $rfc = trim($_POST['rfc'] ?? '');
                    $factura = trim($_POST['numero_factura'] ?? '');
                    $observaciones = trim($_POST['observaciones'] ?? '');

                    $detalle = [];
                    $total = 0;
                    foreach ($items as $item) {
                        $cantidad = (float) ($item['cantidad'] ?? 0);
                        $precio = (float) ($item['precio_unitario'] ?? 0);
                        if ($cantidad <= 0 || $precio < 0) {
                            throw new RuntimeException('Cantidad o precio inválido en uno de los artículos.');
                        }

                        if (!empty($item['es_nuevo'])) {
                            $productoId = $this->crearProductoDesdeOrden($item, $almacenId, $proveedorId);
                        } else {
                            $productoId = (int) ($item['producto_id'] ?? 0);
                            if ($productoId <= 0) {
                                throw new RuntimeException('Selecciona un producto válido.');
                            }
                        }

                        $detalle[] = [
                            'producto_id' => $productoId,
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precio,
                            'descripcion' => trim($item['descripcion'] ?? ''),
                        ];
                        $total += $cantidad * $precio;
                    }

                    $ordenId = OrdenCompra::crear([
                        'proveedor_id' => $proveedorId,
                        'usuario_id' => $_SESSION['user_id'],
                        'almacen_id' => $almacenId,
                        'rfc' => $rfc,
                        'numero_factura' => $factura,
                        'fecha' => $fecha,
                        'estado' => $estado,
                        'total' => $total,
                        'observaciones' => $observaciones,
                    ], $detalle);

                    if ($estado === 'Recibida') {
                        $this->aplicarInventario($ordenId, $_SESSION['user_id']);
                    }

                    $db->commit();
                    ActivityLogger::log('orden_compra_creada', 'Se registró una orden de compra', ['orden_id' => $ordenId]);
                    header('Location: compras_detalle.php?id=' . $ordenId . '&created=1');
                    return;
                } catch (Throwable $e) {
                    $db->rollBack();
                    $errores[] = 'Error al registrar la orden: ' . $e->getMessage();
                }
            }
        }

        include __DIR__ . '/../views/compras/crear.php';
    }

    public function detalle(int $id): void
    {
        Session::requireLogin(['Administrador', 'Compras', 'Almacen']);
        $orden = OrdenCompra::find($id);
        if (!$orden) {
            header('Location: compras_proveedor.php');
            return;
        }
        $items = OrdenCompra::items($id);
        $mensaje = '';
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'recibir') {
            if ($orden['estado'] === 'Recibida') {
                $errores[] = 'La orden ya fue marcada como recibida.';
            } else {
                try {
                    $this->aplicarInventario($id, $_SESSION['user_id']);
                    OrdenCompra::actualizarEstado($id, 'Recibida', $_SESSION['user_id']);
                    ActivityLogger::log('orden_compra_recibida', 'Orden marcada como recibida', ['orden_id' => $id]);
                    $orden = OrdenCompra::find($id);
                    $mensaje = 'La orden fue marcada como recibida y el inventario se actualizó.';
                } catch (Throwable $e) {
                    $errores[] = 'No se pudo marcar como recibida: ' . $e->getMessage();
                }
            }
        }

        include __DIR__ . '/../views/compras/detalle.php';
    }

    private function crearProductoDesdeOrden(array $item, int $almacenId, int $proveedorId): int
    {
        $codigo = trim($item['codigo'] ?? '');
        $nombre = trim($item['nombre'] ?? '');
        $tipo = trim($item['tipo'] ?? '');
        $unidadMedidaId = (int) ($item['unidad_medida_id'] ?? 0);
        $categoriaId = (int) ($item['categoria_id'] ?? null);
        $stockMinimo = (float) ($item['stock_minimo'] ?? 0);

        if ($codigo === '' || $nombre === '' || !in_array($tipo, Producto::tiposDisponibles(), true)) {
            throw new RuntimeException('Datos insuficientes para crear el producto nuevo.');
        }

        $payload = [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => trim($item['descripcion'] ?? ''),
            'proveedor_id' => $proveedorId,
            'categoria_id' => $categoriaId ?: null,
            'peso' => null,
            'ancho' => null,
            'alto' => null,
            'profundidad' => null,
            'unidad_medida_id' => $unidadMedidaId ?: null,
            'clase_categoria' => null,
            'marca' => trim($item['marca'] ?? ''),
            'color' => trim($item['color'] ?? ''),
            'forma' => null,
            'especificaciones_tecnicas' => null,
            'origen' => trim($item['origen'] ?? ''),
            'costo_compra' => (float) ($item['precio_unitario'] ?? 0),
            'precio_venta' => (float) ($item['precio_venta'] ?? 0),
            'stock_minimo' => $stockMinimo,
            'stock_actual' => 0,
            'almacen_id' => $almacenId,
            'estado' => 'Nuevo',
            'tipo' => $tipo,
            'imagen_url' => null,
            'last_requested_by_user_id' => null,
            'last_request_date' => null,
            'tags' => trim($item['tags'] ?? ''),
        ];

        Producto::create($payload);
        $nuevo = Producto::findByCodigo($codigo);
        if (!$nuevo) {
            throw new RuntimeException('No se pudo registrar el producto nuevo.');
        }
        return (int) $nuevo['id'];
    }

    private function aplicarInventario(int $ordenId, int $usuarioId): void
    {
        $orden = OrdenCompra::find($ordenId);
        if (!$orden) {
            throw new RuntimeException('Orden no encontrada.');
        }
        $items = OrdenCompra::items($ordenId);
        foreach ($items as $item) {
            Producto::sumarStock($item['producto_id'], $item['cantidad']);
            MovimientoInventario::registrar([
                'producto_id' => $item['producto_id'],
                'tipo' => 'Entrada',
                'cantidad' => $item['cantidad'],
                'usuario_id' => $usuarioId,
                'almacen_destino_id' => $orden['almacen_id'],
                'observaciones' => 'OC #' . $ordenId,
            ]);
        }
    }
}
