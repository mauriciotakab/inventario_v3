<?php
require_once __DIR__ . '/../helpers/Database.php';
require_once __DIR__ . '/../helpers/ActivityLogger.php';
require_once __DIR__ . '/Producto.php';
require_once __DIR__ . '/MovimientoInventario.php';

class Factura
{
    private static bool $tablesReady = false;

    private static function ensureTables(): void
    {
        if (self::$tablesReady) {
            return;
        }

        $db = Database::getInstance()->getConnection();

        $db->exec("
            CREATE TABLE IF NOT EXISTS facturas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero_factura VARCHAR(50) NULL,
                proveedor_id INT NOT NULL,
                orden_id INT NULL,
                almacen_id INT NOT NULL,
                fecha DATE NOT NULL,
                subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
                impuestos DECIMAL(12,2) NOT NULL DEFAULT 0,
                total DECIMAL(12,2) NOT NULL DEFAULT 0,
                notas TEXT NULL,
                usuario_id INT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_factura_proveedor (proveedor_id),
                KEY idx_factura_orden (orden_id),
                KEY idx_factura_almacen (almacen_id),
                CONSTRAINT fk_factura_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
                CONSTRAINT fk_factura_orden FOREIGN KEY (orden_id) REFERENCES ordenes_compra(id) ON DELETE SET NULL,
                CONSTRAINT fk_factura_almacen FOREIGN KEY (almacen_id) REFERENCES almacenes(id),
                CONSTRAINT fk_factura_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS detalle_facturas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                factura_id INT NOT NULL,
                producto_id INT NOT NULL,
                cantidad DECIMAL(10,2) NOT NULL,
                costo_unitario DECIMAL(12,2) NOT NULL,
                impuesto DECIMAL(12,2) NOT NULL DEFAULT 0,
                total DECIMAL(12,2) NOT NULL,
                KEY idx_detalle_factura (factura_id),
                KEY idx_detalle_producto (producto_id),
                CONSTRAINT fk_detalle_factura FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE CASCADE,
                CONSTRAINT fk_detalle_factura_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        self::$tablesReady = true;
    }

    public static function all(array $filters = []): array
    {
        self::ensureTables();
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT f.*, pr.nombre AS proveedor, al.nombre AS almacen, oc.id AS orden_numero
                FROM facturas f
                LEFT JOIN proveedores pr ON f.proveedor_id = pr.id
                LEFT JOIN almacenes al ON f.almacen_id = al.id
                LEFT JOIN ordenes_compra oc ON f.orden_id = oc.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['proveedor_id'])) {
            $sql .= " AND f.proveedor_id = ?";
            $params[] = (int) $filters['proveedor_id'];
        }
        if (!empty($filters['almacen_id'])) {
            $sql .= " AND f.almacen_id = ?";
            $params[] = (int) $filters['almacen_id'];
        }
        if (!empty($filters['desde'])) {
            $sql .= " AND f.fecha >= ?";
            $params[] = $filters['desde'];
        }
        if (!empty($filters['hasta'])) {
            $sql .= " AND f.fecha <= ?";
            $params[] = $filters['hasta'];
        }
        $sql .= " ORDER BY f.fecha DESC, f.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public static function find(int $id): ?array
    {
        self::ensureTables();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT f.*, pr.nombre AS proveedor, al.nombre AS almacen, oc.id AS orden_numero
                              FROM facturas f
                              LEFT JOIN proveedores pr ON f.proveedor_id = pr.id
                              LEFT JOIN almacenes al ON f.almacen_id = al.id
                              LEFT JOIN ordenes_compra oc ON f.orden_id = oc.id
                              WHERE f.id = ?");
        $stmt->execute([$id]);
        $factura = $stmt->fetch();
        if (! $factura) {
            return null;
        }

        $det = $db->prepare("SELECT df.*, p.nombre AS producto, p.codigo
                             FROM detalle_facturas df
                             LEFT JOIN productos p ON df.producto_id = p.id
                             WHERE df.factura_id = ?
                             ORDER BY df.id ASC");
        $det->execute([$id]);
        $factura['detalles'] = $det->fetchAll() ?: [];

        return $factura;
    }

    public static function porOrden(int $ordenId): array
    {
        if ($ordenId <= 0) {
            return [];
        }
        self::ensureTables();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT f.*, pr.nombre AS proveedor
                              FROM facturas f
                              LEFT JOIN proveedores pr ON f.proveedor_id = pr.id
                              WHERE f.orden_id = ?
                              ORDER BY f.fecha DESC, f.id DESC");
        $stmt->execute([$ordenId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function create(array $data, array $items): int
    {
        self::ensureTables();
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            $subtotal = 0.0;
            $impuestos = 0.0;

            foreach ($items as $item) {
                $cantidad = (float) ($item['cantidad'] ?? 0);
                $costo = (float) ($item['costo_unitario'] ?? 0);
                $lineSubtotal = $cantidad * $costo;
                $lineTax = 0.0;
                if (isset($item['impuesto']) && $item['impuesto'] !== '') {
                    $lineTax = $lineSubtotal * ((float) $item['impuesto'] / 100);
                }
                $subtotal += $lineSubtotal;
                $impuestos += $lineTax;
            }
            $total = $subtotal + $impuestos;

            $insert = $db->prepare("INSERT INTO facturas
                (numero_factura, proveedor_id, orden_id, almacen_id, fecha, subtotal, impuestos, total, notas, usuario_id, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $insert->execute([
                $data['numero_factura'] ?: null,
                (int) $data['proveedor_id'],
                $data['orden_id'] ? (int) $data['orden_id'] : null,
                (int) $data['almacen_id'],
                $data['fecha'] ?? date('Y-m-d'),
                $subtotal,
                $impuestos,
                $total,
                $data['notas'] ?? null,
                $data['usuario_id'] ?? null,
            ]);

            $facturaId = (int) $db->lastInsertId();

            $detalleStmt = $db->prepare("INSERT INTO detalle_facturas
                (factura_id, producto_id, cantidad, costo_unitario, impuesto, total)
                VALUES (?, ?, ?, ?, ?, ?)");

            $almacenId = (int) $data['almacen_id'];
            $usuarioId = $data['usuario_id'] ?? null;

            foreach ($items as $item) {
                $productoId = (int) ($item['producto_id'] ?? 0);
                $cantidad = (float) ($item['cantidad'] ?? 0);
                $costo = (float) ($item['costo_unitario'] ?? 0);
                $impuestoPct = isset($item['impuesto']) ? (float) $item['impuesto'] : 0.0;
                if ($productoId <= 0 || $cantidad <= 0 || $costo < 0) {
                    continue;
                }
                $lineSubtotal = $cantidad * $costo;
                $lineImpuesto = $impuestoPct > 0 ? $lineSubtotal * ($impuestoPct / 100) : 0.0;
                $lineTotal = $lineSubtotal + $lineImpuesto;

                $detalleStmt->execute([
                    $facturaId,
                $productoId,
                    $cantidad,
                    $costo,
                    $lineImpuesto,
                    $lineTotal,
                ]);

                Producto::sumarStock($productoId, $cantidad, $almacenId ?: null);
                MovimientoInventario::registrar([
                    'producto_id' => $productoId,
                    'tipo' => 'Entrada',
                    'cantidad' => $cantidad,
                    'usuario_id' => $usuarioId,
                    'almacen_destino_id' => $almacenId ?: null,
                    'observaciones' => 'Factura #' . ($data['numero_factura'] ?: $facturaId),
                ]);
            }

            if (!empty($data['orden_id'])) {
                $stmtOrden = $db->prepare("UPDATE ordenes_compra SET estado = 'Recibida' WHERE id = ? AND estado <> 'Cancelada'");
                $stmtOrden->execute([(int) $data['orden_id']]);
            }

            $db->commit();

            ActivityLogger::log('factura_creada', 'Se registró la factura ' . ($data['numero_factura'] ?: '#' . $facturaId), [
                'factura_id' => $facturaId,
                'proveedor_id' => (int) $data['proveedor_id'],
            ]);

            return $facturaId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
