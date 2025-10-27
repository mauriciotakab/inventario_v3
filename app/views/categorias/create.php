<?php
Session::requireLogin([
    'Administrador'
]);

$role = $_SESSION['role'];
$nombre = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva categoría - TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/config.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="login-logo"><img src="/assets/images/icono_takab.png" alt="logo_TAKAB" width="90" height="55"></div>
            <div>
                <div class="sidebar-title">TAKAB</div>
                <div class="sidebar-desc">Dashboard</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="usuarios.php"><i class="fa-solid fa-users-cog"></i> Gestión de Usuarios</a>
            <a href="productos.php"><i class="fa-solid fa-boxes-stacked"></i> Gestión de Productos</a>
            <a href="inventario_actual.php"><i class="fa-solid fa-list-check"></i> Inventario</a>
            <a href="revisar_solicitudes.php"><i class="fa-solid fa-comment-medical"></i> Solicitudes de Material</a>
            <a href="reportes.php"><i class="fa-solid fa-chart-line"></i> Reportes</a>
            <a href="configuracion.php" class="active"><i class="fa-solid fa-gear"></i> Configuración</a>
            <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión</a>
        </nav>
    </aside>
    <div class="content-area">
        <header class="top-header">
            <div></div>
            <div class="top-header-user">
                <span><?= htmlspecialchars($nombre ?? 'Usuario') ?></span>
                <i class="fa-solid fa-user-circle"></i>
                <a href="logout.php" class="logout-btn" title="Cerrar sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </header>
        <main class="dashboard-main">
            <div class="form-card">
                <div class="form-title">Nueva categoría</div>
                <?php if (!empty($error)): ?>
                    <div class="form-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form class="takab-form" method="post" action="">
                    <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                    <label for="nombre">Nombre:</label>
                    <input id="nombre" type="text" name="nombre" value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>" required>

                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>

                    <button type="submit">Guardar</button>
                </form>
                <a class="form-link" href="categorias.php"><i class="fa fa-arrow-left"></i> Volver</a>
            </div>
        </main>
    </div>
</div>
</body>
</html>
