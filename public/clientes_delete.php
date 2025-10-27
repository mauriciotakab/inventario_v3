<?php
require_once __DIR__ . '/../app/controllers/ClienteController.php';
$controller = new ClienteController();
$id = $_POST['id'] ?? 0;
$controller->delete($id);
