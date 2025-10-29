<?php
require_once __DIR__ . '/../app/controllers/ProductoController.php';

$controller = new ProductoController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: productos.php');
    exit();
}
$controller->etiqueta($id);
