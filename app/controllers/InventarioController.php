<?php
    require_once __DIR__ . '/../models/MovimientoInventario.php';
    require_once __DIR__ . '/../models/Producto.php';
    require_once __DIR__ . '/../models/Almacen.php';
    require_once __DIR__ . '/../helpers/Session.php';
    require_once __DIR__ . '/../helpers/ActivityLogger.php';
    require_once __DIR__ . '/../helpers/Database.php';

    class InventarioController
    {
        public function entrada()
        {
            Session::requireLogin(['Administrador', 'Almacen']);

            $productos = Producto::all();
            $almacenes = Almacen::all();
            $msg       = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
                    $msg = 'Token CSRF invalido.';
                } else {
                    $productoId = $_POST['producto_id'] ?? null;
                    $almacenId  = $_POST['almacen_id'] ?? null;
                    $cantidad   = isset($_POST['cantidad']) ? (float) $_POST['cantidad'] : 0;

                    if ($productoId && $almacenId && $cantidad > 0) {
                        $data = [
                            'producto_id'        => $productoId,
                            'tipo'               => 'Entrada',
                            'cantidad'           => $cantidad,
                            'usuario_id'         => $_SESSION['user_id'],
                            'almacen_destino_id' => $almacenId,
                            'observaciones'      => trim($_POST['observaciones'] ?? ''),
                        ];

                        MovimientoInventario::registrar($data);
                        Producto::sumarStock($data['producto_id'], $data['cantidad'], (int) $almacenId);
                        $msg = "Entrada registrada correctamente.";
                        ActivityLogger::log('inventario_entrada', 'Entrada de inventario registrada', [
                            'producto_id' => $productoId,
                            'almacen_id'  => $almacenId,
                            'cantidad'    => $cantidad,
                        ]);
                    } else {
                        $msg = "Por favor completa los campos obligatorios.";
                    }
                }
            }

            $movimientosRecientes = MovimientoInventario::ultimos('Entrada', 6);

            include __DIR__ . '/../views/inventario/entrada.php';
        }

        public function salida()
        {
            Session::requireLogin(['Administrador', 'Almacen']);

            $productos = Producto::all();
            $almacenes = Almacen::all();
            $msg       = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
                    $msg = 'Token CSRF invalido.';
                } else {
                    $productoId = $_POST['producto_id'] ?? null;
                    $almacenId  = $_POST['almacen_id'] ?? null;
                    $cantidad   = isset($_POST['cantidad']) ? (float) $_POST['cantidad'] : 0;

                    if ($productoId && $almacenId && $cantidad > 0) {
                        $data = [
                            'producto_id'       => $productoId,
                            'tipo'              => 'Salida',
                            'cantidad'          => $cantidad,
                            'usuario_id'        => $_SESSION['user_id'],
                            'almacen_origen_id' => $almacenId,
                            'observaciones'     => trim($_POST['observaciones'] ?? ''),
                        ];

                        MovimientoInventario::registrar($data);
                        Producto::restarStock($data['producto_id'], $data['cantidad'], (int) $almacenId);
                        $msg = "Salida registrada correctamente.";
                        ActivityLogger::log('inventario_salida', 'Salida de inventario registrada', [
                            'producto_id' => $productoId,
                            'almacen_id'  => $almacenId,
                            'cantidad'    => $cantidad,
                        ]);
                    } else {
                        $msg = "Por favor completa los campos obligatorios.";
                    }
                }
            }

            $movimientosRecientes = MovimientoInventario::ultimos('Salida', 6);

            include __DIR__ . '/../views/inventario/salida.php';
        }

        public function transferencia()
        {
            Session::requireLogin(["Administrador", "Almacen"]);

            $productos = Producto::all();
            $almacenes = Almacen::all();
            $msg       = '';
            $error     = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
                    $error = 'Token CSRF invalido.';
                } else {
                    $productoId    = isset($_POST['producto_id']) ? (int) $_POST['producto_id'] : 0;
                    $origenId      = isset($_POST['almacen_origen_id']) ? (int) $_POST['almacen_origen_id'] : 0;
                    $destinoId     = isset($_POST['almacen_destino_id']) ? (int) $_POST['almacen_destino_id'] : 0;
                    $cantidad      = isset($_POST['cantidad']) ? (float) $_POST['cantidad'] : 0;
                    $observaciones = trim($_POST['observaciones'] ?? '');

                    $producto = $productoId ? Producto::find($productoId) : null;

                    if ($productoId <= 0 || $origenId <= 0 || $destinoId <= 0 || ! $producto) {
                        $error = "Selecciona un producto y almacenes validos.";
                    } elseif ($origenId === $destinoId) {
                        $error = "El almacen de origen y destino deben ser diferentes.";
                    } elseif ($cantidad <= 0) {
                        $error = "Indica una cantidad mayor a cero.";
                    } else {
                        $disponible = Producto::stockEnAlmacen($productoId, $origenId);
                        if ($cantidad > $disponible) {
                            $error = "La cantidad supera el inventario disponible en el almacen de origen.";
                        } else {
                            $data = [
                                'producto_id'        => $productoId,
                                'tipo'               => 'Transferencia',
                                'cantidad'           => $cantidad,
                                'usuario_id'         => $_SESSION['user_id'],
                                'almacen_origen_id'  => $origenId,
                                'almacen_destino_id' => $destinoId,
                                'observaciones'      => $observaciones,
                            ];

                            if (MovimientoInventario::registrar($data) && Producto::moverStock($productoId, $origenId, $destinoId, $cantidad)) {
                                $restante = Producto::stockEnAlmacen($productoId, $origenId);
                                if ((int) ($producto['almacen_id'] ?? 0) === $origenId && $restante <= 0.0) {
                                    Producto::actualizarAlmacen($productoId, $destinoId);
                                }
                                $msg = "Transferencia registrada correctamente.";
                                ActivityLogger::log('inventario_transferencia', 'Transferencia entre almacenes', [
                                    'producto_id' => $productoId,
                                    'origen'      => $origenId,
                                    'destino'     => $destinoId,
                                    'cantidad'    => $cantidad,
                                ]);
                            } else {
                                $error = "No fue posible registrar la transferencia. Intenta nuevamente.";
                            }
                        }
                    }
                }
            }

            $movimientosRecientes = MovimientoInventario::ultimos('Transferencia', 6);

            include __DIR__ . '/../views/inventario/transferencia.php';
        }

        public function actual()
        {
            Session::requireLogin();

            $role = $_SESSION['role'] ?? 'Empleado';

            $filtros = [
                'buscar'           => trim($_GET['buscar'] ?? ($_GET['q'] ?? '')),
                'categoria_id'     => $_GET['categoria_id'] ?? '',
                'almacen_id'       => $_GET['almacen_id'] ?? '',
                'proveedor_id'     => $_GET['proveedor_id'] ?? '',
                'tipo'             => $_GET['tipo'] ?? '',
                'estado'           => $_GET['estado'] ?? '',
                'activo_id'        => $_GET['activo_id'] ?? '',
                'stock_flag'       => $_GET['stock_flag'] ?? '',
                'valor_min'        => $_GET['valor_min'] ?? '',
                'valor_max'        => $_GET['valor_max'] ?? '',
                'fecha_desde'      => $_GET['fecha_desde'] ?? '',
                'fecha_hasta'      => $_GET['fecha_hasta'] ?? '',
                'unidad_medida_id' => $_GET['unidad_medida_id'] ?? '',
                'codigo_barras'    => trim($_GET['codigo_barras'] ?? ''),
            ];

            if (! empty($_GET['cat']) && empty($filtros['categoria_id'])) {
                $filtros['categoria'] = $_GET['cat'];
            }

            $page           = max(1, (int) ($_GET['page'] ?? 1));
            $perPageOptions = [10, 15, 25, 50, 100];
            $perPage        = (int) ($_GET['per_page'] ?? 15);
            if (! in_array($perPage, $perPageOptions, true)) {
                $perPage = 15;
            }
            $offset = ($page - 1) * $perPage;

            $resultado      = Producto::inventarioListado($filtros, $perPage, $offset);
            $productos      = $resultado['items'];
            $stats          = $resultado['stats'];
            $totalRegistros = $resultado['total'];
            $totalPaginas   = max(1, (int) ceil($totalRegistros / $perPage));

            if ($page > $totalPaginas) {
                $page      = $totalPaginas;
                $offset    = ($page - 1) * $perPage;
                $resultado = Producto::inventarioListado($filtros, $perPage, $offset);
                $productos = $resultado['items'];
                $stats     = $resultado['stats'];
            }

            $db          = Database::getInstance()->getConnection();
            $categorias  = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
            $almacenes   = $db->query("SELECT id, nombre FROM almacenes ORDER BY nombre ASC")->fetchAll();
            $proveedores = $db->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll();
            $unidades    = $db->query("SELECT id, nombre, abreviacion FROM unidades_medida ORDER BY nombre ASC")->fetchAll();

            $tiposProducto   = Producto::tiposDisponibles();
            $estadosProducto = Producto::estadosDisponibles();
            $estadosActivos  = Producto::estadosActivos();

            $hayFiltros = false;
            foreach ($filtros as $valor) {
                if ($valor !== '' && $valor !== null) {
                    $hayFiltros = true;
                    break;
                }
            }

            include __DIR__ . '/../views/inventario/actual.php';
        }
}
