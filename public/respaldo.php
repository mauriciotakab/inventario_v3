<?php
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/Database.php';
require_once __DIR__ . '/../app/helpers/ActivityLogger.php';

Session::requireLogin('Administrador');

$role   = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
        $error = 'La sesión expiró. Inténtalo nuevamente.';
    } else {
        $db = Database::getInstance()->getConnection();

        $payload = [
            'exported_at'      => date('c'),
            'categorias'       => $db->query('SELECT * FROM categorias ORDER BY nombre ASC')->fetchAll() ?: [],
            'proveedores'      => $db->query('SELECT * FROM proveedores ORDER BY nombre ASC')->fetchAll() ?: [],
            'unidades_medida'  => $db->query('SELECT * FROM unidades_medida ORDER BY nombre ASC')->fetchAll() ?: [],
            'almacenes'        => $db->query('SELECT * FROM almacenes ORDER BY nombre ASC')->fetchAll() ?: [],
            'usuarios_activos' => $db->query('SELECT id, nombre_completo, username, role FROM usuarios WHERE activo = 1 ORDER BY nombre_completo ASC')->fetchAll() ?: [],
        ];

        ActivityLogger::log('config_backup', 'Respaldo de configuración descargado');

        $filename = 'respaldo_configuracion_' . date('Ymd_His') . '.json';
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Respaldos de configuración | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/config.css">
    <link rel="stylesheet" href="/assets/css/config-pages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/../app/views/partials/sidebar.php'; ?>

    <div class="content-area">
        <?php include __DIR__ . '/../app/views/partials/topbar.php'; ?>

        <main class="dashboard-main config-page">
            <div class="main-table-card">
                <div class="config-section-header">
                    <div>
                        <div class="config-section-title">
                            <span class="config-icon gradient-gray"><i class="fa-solid fa-database"></i></span>
                            Respaldos de configuración
                        </div>
                        <p class="config-section-desc">Descarga un archivo JSON con catálogos clave (categorías, almacenes, unidades, proveedores y usuarios activos).</p>
                    </div>
                </div>
                <div class="config-section-actions" style="margin-bottom:18px;">
                    <a class="btn-secondary-ghost" href="ajustes.php"><i class="fa fa-arrow-left"></i> Ajustes</a>
                </div>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><i class="fa fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <p style="margin-bottom:18px;color:#5f6b8a;">Puedes conservar este archivo para restaurar la configuración base del sistema en una instalación nueva.</p>

                <ul style="margin:0 0 20px 18px;color:#2d3257;">
                    <li>Categorías, proveedores y unidades de medida.</li>
                    <li>Almacenes registrados con su responsable.</li>
                    <li>Usuarios activos (sin contraseñas).</li>
                </ul>

                <form method="post">
                    <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                    <button type="submit" class="btn-main"><i class="fa-solid fa-download"></i> Descargar respaldo</button>
                </form>
            </div>
        </main>

    </div>
</div>
<?php include __DIR__ . '/../app/views/partials/scripts.php'; ?>
</body>
</html>
