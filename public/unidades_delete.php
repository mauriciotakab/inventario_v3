<?php
require_once __DIR__ . '/../app/controllers/UnidadMedidaController.php';
$controller = new UnidadMedidaController();
$id = $_POST['id'] ?? 0;
$controller->delete($id);
