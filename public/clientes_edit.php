<?php
require_once __DIR__ . '/../app/controllers/ClienteController.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$controller = new ClienteController();
$controller->edit($id);
