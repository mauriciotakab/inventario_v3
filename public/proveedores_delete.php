<?php
require_once __DIR__ . '/../app/controllers/ProveedorController.php';
$controller = new ProveedorController();
$id = $_POST['id'] ?? 0;
$controller->delete($id);
