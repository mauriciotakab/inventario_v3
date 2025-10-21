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
                       oc.fecha,
                       oc.estado,
                       oc.total,
                       pr.nombre AS proveedor,
                       pr.contacto,
                       COALESCE(SUM(do.cantidad), 0) AS total_items,
                       COALESCE(SUM(do.cantidad * do.precio_unitario), 0) AS subtotal
                FROM ordenes_compra oc
                LEFT JOIN proveedores pr ON oc.proveedor_id = pr.id
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
