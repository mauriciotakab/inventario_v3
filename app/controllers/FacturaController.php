<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Database.php';
require_once __DIR__ . '/../helpers/ActivityLogger.php';
require_once __DIR__ . '/../models/Factura.php';

class FacturaController
{
    public function index(): void
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);

        $filters = [
            'proveedor_id' => $_GET['proveedor_id'] ?? '',
            'almacen_id'   => $_GET['almacen_id'] ?? '',
            'desde'        => $_GET['desde'] ?? '',
            'hasta'        => $_GET['hasta'] ?? '',
        ];

        $facturas = Factura::all($filters);
        $db = Database::getInstance()->getConnection();
        $proveedores = $db->query('SELECT id, nombre FROM proveedores ORDER BY nombre ASC')->fetchAll();
        $almacenes   = $db->query('SELECT id, nombre FROM almacenes ORDER BY nombre ASC')->fetchAll();

        include __DIR__ . '/../views/facturas/index.php';
    }

    public function create(): void
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);
        $db = Database::getInstance()->getConnection();

        $proveedores = $db->query('SELECT id, nombre FROM proveedores ORDER BY nombre ASC')->fetchAll();
        $almacenes   = $db->query('SELECT id, nombre FROM almacenes ORDER BY nombre ASC')->fetchAll();
        $ordenes     = $db->query("
            SELECT oc.id,
                   oc.proveedor_id,
                   oc.fecha,
                   oc.estado,
                   oc.numero_factura,
                   oc.almacen_destino_id,
                   pr.nombre AS proveedor
            FROM ordenes_compra oc
            LEFT JOIN proveedores pr ON pr.id = oc.proveedor_id
            ORDER BY oc.fecha DESC
            LIMIT 200
        ")->fetchAll();
        $productos   = $db->query('SELECT id, codigo, nombre FROM productos ORDER BY nombre ASC')->fetchAll();

        $errors = [];
        $msg    = '';
        $defaultItems = [[
            'producto_id'   => '',
            'cantidad'      => '',
            'costo_unitario'=> '',
            'impuesto'      => '',
        ]];

        $facturaData = [
            'numero_factura' => trim($_GET['numero'] ?? ''),
            'proveedor_id'   => $_GET['proveedor_id'] ?? '',
            'orden_id'       => $_GET['orden_id'] ?? '',
            'almacen_id'     => '',
            'fecha'          => date('Y-m-d'),
            'notas'          => '',
            'items'          => $defaultItems,
        ];

        $ordenSeleccionada = null;
        if (!empty($facturaData['orden_id'])) {
            $facturaData = $this->hydrateOrdenDefaults($facturaData, $ordenSeleccionada);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
                $errors[] = 'La sesion expiro. Intenta nuevamente.';
            } else {
                $facturaData['numero_factura'] = trim($_POST['numero_factura'] ?? '');
                $facturaData['proveedor_id']   = (int) ($_POST['proveedor_id'] ?? 0);
                $facturaData['orden_id']       = !empty($_POST['orden_id']) ? (int) $_POST['orden_id'] : null;
                $facturaData['almacen_id']     = (int) ($_POST['almacen_id'] ?? 0);
                $facturaData['fecha']          = $_POST['fecha'] ?? date('Y-m-d');
                $facturaData['notas']          = trim($_POST['notas'] ?? '');

                $itemsValidos = $this->parseItems($_POST);
                $rawRows      = $this->rebuildItemRows($_POST);
                if (!empty($rawRows)) {
                    $facturaData['items'] = $rawRows;
                }

                $facturaData = $this->hydrateOrdenDefaults($facturaData, $ordenSeleccionada);

                if (!empty($facturaData['orden_id']) && ! $ordenSeleccionada) {
                    $errors[] = 'La orden seleccionada no existe.';
                }
                if ($ordenSeleccionada && $facturaData['proveedor_id'] > 0 &&
                    (int) $ordenSeleccionada['proveedor_id'] !== (int) $facturaData['proveedor_id']) {
                    $errors[] = 'El proveedor no coincide con la orden seleccionada.';
                }
                if ($facturaData['proveedor_id'] <= 0) {
                    $errors[] = 'Selecciona un proveedor valido.';
                }
                if ($facturaData['almacen_id'] <= 0) {
                    $errors[] = 'Selecciona el almacen que recibe.';
                }
                if (empty($itemsValidos)) {
                    $errors[] = 'Debes agregar al menos un producto.';
                }

                if (empty($errors)) {
                    try {
                        $facturaId = Factura::create([
                            'numero_factura' => $facturaData['numero_factura'],
                            'proveedor_id'   => $facturaData['proveedor_id'],
                            'orden_id'       => $facturaData['orden_id'],
                            'almacen_id'     => $facturaData['almacen_id'],
                            'fecha'          => $facturaData['fecha'],
                            'notas'          => $facturaData['notas'],
                            'usuario_id'     => $_SESSION['user_id'] ?? null,
                        ], $itemsValidos);

                        header('Location: facturas_detalle.php?id=' . $facturaId . '&created=1');
                        return;
                    } catch (\Throwable $e) {
                        $errors[] = 'No fue posible registrar la factura: ' . $e->getMessage();
                    }
                }
            }
        }

        include __DIR__ . '/../views/facturas/create.php';
    }

    public function detalle(int $id): void
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);
        $factura = Factura::find($id);
        if (! $factura) {
            die('Factura no encontrada.');
        }
        include __DIR__ . '/../views/facturas/detalle.php';
    }

    private function parseItems(array $payload): array
    {
        $productoIds = $payload['item_producto_id'] ?? [];
        $cantidades  = $payload['item_cantidad'] ?? [];
        $costos      = $payload['item_costo'] ?? [];
        $impuestos   = $payload['item_impuesto'] ?? [];

        $items = [];
        foreach ($productoIds as $index => $productoId) {
            $pid = (int) $productoId;
            $cantidad = isset($cantidades[$index]) ? (float) $cantidades[$index] : 0;
            $costo = isset($costos[$index]) ? (float) $costos[$index] : 0;
            if ($pid <= 0 || $cantidad <= 0 || $costo < 0) {
                continue;
            }
            $items[] = [
                'producto_id'    => $pid,
                'cantidad'       => $cantidad,
                'costo_unitario' => $costo,
                'impuesto'       => isset($impuestos[$index]) ? (float) $impuestos[$index] : 0,
            ];
        }
        return $items;
    }

    private function rebuildItemRows(array $payload): array
    {
        $productoIds = $payload['item_producto_id'] ?? [];
        $cantidades  = $payload['item_cantidad'] ?? [];
        $costos      = $payload['item_costo'] ?? [];
        $impuestos   = $payload['item_impuesto'] ?? [];

        $rows = [];
        $max = max(count($productoIds), count($cantidades), count($costos), count($impuestos), 0);
        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                'producto_id'    => $productoIds[$i] ?? '',
                'cantidad'       => $cantidades[$i] ?? '',
                'costo_unitario' => $costos[$i] ?? '',
                'impuesto'       => $impuestos[$i] ?? '',
            ];
        }
        return $rows;
    }

    private function hydrateOrdenDefaults(array $facturaData, ?array &$ordenSeleccionada): array
    {
        $ordenSeleccionada = null;
        if (empty($facturaData['orden_id'])) {
            return $facturaData;
        }
        $ordenSeleccionada = $this->fetchOrdenResumen((int) $facturaData['orden_id']);
        if (! $ordenSeleccionada) {
            return $facturaData;
        }
        if (empty($facturaData['proveedor_id'])) {
            $facturaData['proveedor_id'] = (int) $ordenSeleccionada['proveedor_id'];
        }
        if (empty($facturaData['almacen_id']) && !empty($ordenSeleccionada['almacen_destino_id'])) {
            $facturaData['almacen_id'] = (int) $ordenSeleccionada['almacen_destino_id'];
        }
        return $facturaData;
    }

    private function fetchOrdenResumen(int $ordenId): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT oc.id,
                   oc.proveedor_id,
                   oc.almacen_destino_id,
                   oc.numero_factura,
                   oc.estado,
                   oc.fecha,
                   pr.nombre AS proveedor,
                   al.nombre AS almacen
            FROM ordenes_compra oc
            LEFT JOIN proveedores pr ON pr.id = oc.proveedor_id
            LEFT JOIN almacenes al ON al.id = oc.almacen_destino_id
            WHERE oc.id = ?
        ");
        $stmt->execute([$ordenId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
