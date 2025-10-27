<?php
require_once __DIR__ . '/../helpers/Database.php';

class OrdenCompra
{
    public static function historial(array $filtros = []): array
    {
        $db = Database::getInstance()->getConnection();
        $where = [];
        $params = [];

        if (!empty($filtros['proveedor_id'])) {
            $where[] = 'oc.proveedor_id = ?';
            $params[] = (int) $filtros['proveedor_id'];
        }
        if (!empty($filtros['desde'])) {
            $where[] = 'DATE(oc.fecha) >= ?';
            $params[] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $where[] = 'DATE(oc.fecha) <= ?';
            $params[] = $filtros['hasta'];
        }

        $sql = "SELECT oc.id,
                       oc.proveedor_id,
                       oc.usuario_id,
                       oc.solicitud_id,
                       oc.rfc,
                       oc.numero_factura,
                       oc.fecha,
                       oc.estado,
                       oc.total,
                       oc.almacen_destino_id,
                       pr.nombre AS proveedor,
                       pr.contacto,
                       al.nombre AS almacen_destino,
                       u.nombre_completo AS creado_por,
                       COALESCE(SUM(do.cantidad), 0) AS total_items,
                       COALESCE(SUM(do.cantidad * do.precio_unitario), 0) AS subtotal
                FROM ordenes_compra oc
                LEFT JOIN proveedores pr ON oc.proveedor_id = pr.id
                LEFT JOIN almacenes al ON oc.almacen_destino_id = al.id
                LEFT JOIN usuarios u ON oc.usuario_id = u.id
                LEFT JOIN detalle_ordenes do ON do.orden_id = oc.id";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' GROUP BY oc.id ORDER BY oc.fecha DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $ordenes = $stmt->fetchAll() ?: [];

        $detalles = [];
        if (!empty($ordenes)) {
            $ids = array_column($ordenes, 'id');
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $detSql = "SELECT do.orden_id,
                                  do.producto_id,
                                  do.cantidad,
                                  do.precio_unitario,
                                  p.nombre AS producto,
                                  p.codigo AS codigo_producto,
                                  p.tipo AS tipo_producto
                           FROM detalle_ordenes do
                           LEFT JOIN productos p ON do.producto_id = p.id
                           WHERE do.orden_id IN ($placeholders)";
                $stmtDet = $db->prepare($detSql);
                $stmtDet->execute($ids);
                foreach ($stmtDet->fetchAll() as $row) {
                    $detalles[$row['orden_id']][] = $row;
                }
            }
        }

        return ['ordenes' => $ordenes, 'detalles' => $detalles];
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT oc.*,
                       pr.nombre AS proveedor,
                       pr.contacto,
                       pr.telefono,
                       al.nombre AS almacen_destino,
                       u.nombre_completo AS creado_por
                FROM ordenes_compra oc
                LEFT JOIN proveedores pr ON oc.proveedor_id = pr.id
                LEFT JOIN almacenes al ON oc.almacen_destino_id = al.id
                LEFT JOIN usuarios u ON oc.usuario_id = u.id
                WHERE oc.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $orden = $stmt->fetch();
        if (!$orden) {
            return null;
        }
        $orden['detalles'] = self::detalles($id);
        return $orden;
    }

    public static function detalles(int $ordenId): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT do.*,
                       p.nombre AS producto,
                       p.codigo AS codigo_producto,
                       p.tipo AS tipo_producto,
                       p.unidad_medida_id
                FROM detalle_ordenes do
                LEFT JOIN productos p ON do.producto_id = p.id
                WHERE do.orden_id = ?
                ORDER BY do.id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$ordenId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function crear(array $cabecera, array $detalles): int
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $fecha = $cabecera['fecha'] ?? date('Y-m-d H:i:s');
            $estado = $cabecera['estado'] ?? 'Pendiente';

            $insert = $db->prepare(
                "INSERT INTO ordenes_compra
                 (proveedor_id, usuario_id, solicitud_id, rfc, numero_factura, fecha, estado, total, almacen_destino_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $total = self::calcularTotal($detalles);
            $insert->execute([
                (int) $cabecera['proveedor_id'],
                $cabecera['usuario_id'] ?? null,
                $cabecera['solicitud_id'] ?? null,
                $cabecera['rfc'] ?? null,
                $cabecera['numero_factura'] ?? null,
                $fecha,
                $estado,
                $total,
                $cabecera['almacen_destino_id'] ?? null,
            ]);

            $ordenId = (int) $db->lastInsertId();
            self::guardarDetalles($db, $ordenId, $detalles);

            if ($estado === 'Recibida') {
                self::registrarRecepcionInventario($db, $ordenId, $detalles, $cabecera);
            }

            $db->commit();
            return $ordenId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function actualizar(int $id, array $cabecera, array $detalles): bool
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $total = self::calcularTotal($detalles);
            $update = $db->prepare(
                "UPDATE ordenes_compra
                 SET proveedor_id = ?, usuario_id = ?, solicitud_id = ?, rfc = ?, numero_factura = ?,
                     fecha = ?, estado = ?, total = ?, almacen_destino_id = ?
                 WHERE id = ?"
            );
            $update->execute([
                (int) $cabecera['proveedor_id'],
                $cabecera['usuario_id'] ?? null,
                $cabecera['solicitud_id'] ?? null,
                $cabecera['rfc'] ?? null,
                $cabecera['numero_factura'] ?? null,
                $cabecera['fecha'] ?? date('Y-m-d H:i:s'),
                $cabecera['estado'] ?? 'Pendiente',
                $total,
                $cabecera['almacen_destino_id'] ?? null,
                $id,
            ]);

            self::eliminarDetalles($db, $id);
            self::guardarDetalles($db, $id, $detalles);

            if (($cabecera['estado'] ?? 'Pendiente') === 'Recibida') {
                self::registrarRecepcionInventario($db, $id, $detalles, $cabecera);
            }

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function actualizarEstado(int $id, string $estado, array $extras = []): bool
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $sql = "UPDATE ordenes_compra
                    SET estado = ?, numero_factura = COALESCE(?, numero_factura),
                        rfc = COALESCE(?, rfc), almacen_destino_id = COALESCE(?, almacen_destino_id)
                    WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $estado,
                $extras['numero_factura'] ?? null,
                $extras['rfc'] ?? null,
                $extras['almacen_destino_id'] ?? null,
                $id,
            ]);

            if ($estado === 'Recibida') {
                $detalles = self::detalles($id);
                $cabecera = self::find($id) ?: [];
                self::registrarRecepcionInventario($db, $id, $detalles, $cabecera);
            }

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private static function guardarDetalles(\PDO $db, int $ordenId, array $detalles): void
    {
        if (empty($detalles)) {
            return;
        }

        $insert = $db->prepare(
            "INSERT INTO detalle_ordenes (orden_id, producto_id, cantidad, precio_unitario)
             VALUES (?, ?, ?, ?)"
        );

        foreach ($detalles as $detalle) {
            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            $precio = (float) ($detalle['precio_unitario'] ?? 0);
            if ($cantidad <= 0) {
                continue;
            }
            $insert->execute([
                $ordenId,
                (int) $detalle['producto_id'],
                $cantidad,
                $precio,
            ]);
        }
    }

    private static function eliminarDetalles(\PDO $db, int $ordenId): void
    {
        $stmt = $db->prepare("DELETE FROM detalle_ordenes WHERE orden_id = ?");
        $stmt->execute([$ordenId]);
    }

    private static function calcularTotal(array $detalles): float
    {
        $total = 0.0;
        foreach ($detalles as $detalle) {
            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            $precio = (float) ($detalle['precio_unitario'] ?? 0);
            if ($cantidad > 0 && $precio >= 0) {
                $total += $cantidad * $precio;
            }
        }
        return round($total, 2);
    }

    private static function registrarRecepcionInventario(\PDO $db, int $ordenId, array $detalles, array $cabecera): void
    {
        if (empty($detalles)) {
            return;
        }

        require_once __DIR__ . '/Producto.php';
        require_once __DIR__ . '/MovimientoInventario.php';
        require_once __DIR__ . '/Usuario.php';

        $usuarioId = $cabecera['usuario_id'] ?? null;
        $almacenId = $cabecera['almacen_destino_id'] ?? null;

        foreach ($detalles as $detalle) {
            $productoId = (int) ($detalle['producto_id'] ?? 0);
            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            if ($productoId <= 0 || $cantidad <= 0) {
                continue;
            }

            Producto::sumarStock($productoId, $cantidad);
            if ($almacenId) {
                Producto::actualizarAlmacen($productoId, (int) $almacenId);
            }

            MovimientoInventario::registrar([
                'producto_id' => $productoId,
                'tipo' => 'Entrada',
                'cantidad' => $cantidad,
                'usuario_id' => $usuarioId,
                'almacen_destino_id' => $almacenId,
                'observaciones' => 'Recepción OC #' . $ordenId,
            ]);
        }
    }
}
