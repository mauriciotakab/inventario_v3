<?php
require_once __DIR__ . '/../app/controllers/CategoriaController.php';
$controller = new CategoriaController();
$id = $_POST['id'] ?? 0;
$controller->delete($id);
