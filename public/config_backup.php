<?php
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/Database.php';
require_once __DIR__ . '/../app/helpers/ActivityLogger.php';

Session::requireLogin('Administrador');

$db = Database::getInstance()->getConnection();

$payload = [
    'exported_at' => date('c'),
    'categorias' => $db->query('SELECT * FROM categorias ORDER BY nombre ASC')->fetchAll() ?: [],
    'proveedores' => $db->query('SELECT * FROM proveedores ORDER BY nombre ASC')->fetchAll() ?: [],
    'unidades_medida' => $db->query('SELECT * FROM unidades_medida ORDER BY nombre ASC')->fetchAll() ?: [],
    'almacenes' => $db->query('SELECT * FROM almacenes ORDER BY nombre ASC')->fetchAll() ?: [],
    'usuarios_activos' => $db->query('SELECT id, nombre_completo, username, role FROM usuarios WHERE activo = 1 ORDER BY nombre_completo ASC')->fetchAll() ?: [],
];

ActivityLogger::log('config_backup', 'Respaldo de configuración descargado');

$filename = 'respaldo_configuracion_' . date('Ymd_His') . '.json';
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename=' . $filename);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit();
