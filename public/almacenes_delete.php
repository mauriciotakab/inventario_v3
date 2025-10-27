<?php
require_once __DIR__ . '/../app/controllers/AlmacenController.php';
$controller = new AlmacenController();
$id = $_POST['id'] ?? 0;
$controller->delete($id);
