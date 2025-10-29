<?php
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/controllers/ProductoController.php';

Session::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: productos.php');
    exit();
}

$controller = new ProductoController();
$controller->delete($_POST['id'] ?? 0);
