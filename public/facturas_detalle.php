<?php
require_once __DIR__ . '/../app/controllers/FacturaController.php';
$controller = new FacturaController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die('Factura no encontrada.');
}
$controller->detalle($id);
