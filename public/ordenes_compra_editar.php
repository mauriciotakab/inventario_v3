<?php
require_once __DIR__ . '/../app/controllers/OrdenCompraController.php';
$controller = new OrdenCompraController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$controller->editar($id);
