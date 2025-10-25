<?php
require_once __DIR__ . '/../helpers/Database.php';

class OrdenCompra
{
    public static function crear(array $orden, array $items): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO ordenes_compra
            (proveedor_id, usuario_id, almacen_id, rfc, numero_factura, fecha, estado, total, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $orden['proveedor_id'],
            $orden['usuario_id'] ?? null,
            $orden['almacen_id'],
            $orden['rfc'] ?? null,
            $orden['numero_factura'] ?? null,
            $orden['fecha'] ?? date('Y-m-d'),
            $orden['estado'] ?? 'Pendiente',
            $orden['total'] ?? 0,
            $orden['observaciones'] ?? null,
        ]);
        $ordenId = (int) $db->lastInsertId();

        $sqlDetalle = "INSERT INTO detalle_ordenes (orden_id, producto_id, cantidad, precio_unitario, descripcion)
                       VALUES (?, ?, ?, ?, ?)";
        $stmtDetalle = $db->prepare($sqlDetalle);
        foreach ($items as $item) {
            $stmtDetalle->execute([
                $ordenId,
                $item['producto_id'],
                $item['cantidad'],
                $item['precio_unitario'],
                $item['descripcion'] ?? null,
            ]);
        }

        return $ordenId;
    }

    public static function actualizarEstado(int $id, string $estado, ?int $usuarioId = null): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE ordenes_compra SET estado=?, usuario_recibe_id=?, fecha_recibido = CASE WHEN ? = 'Recibida' THEN NOW() ELSE fecha_recibido END WHERE id=?");
        return $stmt->execute([$estado, $usuarioId, $estado, $id]);
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT oc.*, pr.nombre AS proveedor, pr.contacto, pr.telefono, a.nombre AS almacen
                FROM ordenes_compra oc
                LEFT JOIN proveedores pr ON oc.proveedor_id = pr.id
                LEFT JOIN almacenes a ON oc.almacen_id = a.id
                WHERE oc.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    public static function items(int $ordenId): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT do.*, p.nombre AS producto, p.codigo
                FROM detalle_ordenes do
                LEFT JOIN productos p ON do.producto_id = p.id
                WHERE do.orden_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$ordenId]);
        return $stmt->fetchAll() ?: [];
    }

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
                       oc.fecha,
                       oc.estado,
                       oc.total,
                       oc.rfc,
                       oc.numero_factura,
                       oc.almacen_id,
                       a.nombre AS almacen,
                       pr.nombre AS proveedor,
                       pr.contacto,
                       COALESCE(SUM(do.cantidad),0) AS total_items,
                       COALESCE(SUM(do.cantidad * do.precio_unitario),0) AS subtotal
                FROM ordenes_compra oc
                LEFT JOIN proveedores pr ON oc.proveedor_id = pr.id
                LEFT JOIN almacenes a ON oc.almacen_id = a.id
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
            if ($ids) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $detSql = "SELECT do.orden_id,
                                  do.producto_id,
                                  do.cantidad,
                                  do.precio_unitario,
                                  do.descripcion,
                                  p.nombre AS producto
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
}
