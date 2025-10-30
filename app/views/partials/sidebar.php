<?php
require_once __DIR__ . '/../../helpers/Navigation.php';

$role = Navigation::normalizeRole($role ?? ($_SESSION['role'] ?? ''));
$nombre = $nombre ?? ($_SESSION['nombre'] ?? '');

echo Navigation::renderSidebar($role, $nombre);
