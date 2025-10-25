<?php
require_once __DIR__ . '/../app/controllers/CompraController.php';
$controller = new CompraController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: compras_proveedor.php');
    exit();
}
$controller->detalle($id);
