﻿<?php
Session::requireLogin();

$role = $_SESSION['role'];
$nombre = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= isset($almacen) ? 'Editar' : 'Agregar' ?> Almacén - TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/config.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-layout">
    <!-- Sidebar -->
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
            <?php if ($role === 'Administrador'): ?>
                <a href="usuarios.php"><i class="fa-solid fa-users-cog"></i> Gestión de Usuarios</a>
                <a href="productos.php"><i class="fa-solid fa-boxes-stacked"></i> Gestión de Productos</a>
                <a href="inventario_actual.php"><i class="fa-solid fa-list-check"></i> Inventario</a>
                <a href="revisar_solicitudes.php"><i class="fa-solid fa-comment-medical"></i> Solicitudes de Material</a>
                <a href="reportes.php"><i class="fa-solid fa-chart-line"></i> Reportes</a>
                <a href="configuracion.php" class="active"><i class="fa-solid fa-gear"></i> Configuración</a>
            <?php elseif ($role === 'Almacen'): ?>
                <a href="productos.php"><i class="fa-solid fa-boxes-stacked"></i> Gestión de Productos</a>
                <a href="solicitudes.php"><i class="fa-solid fa-inbox"></i> Gestionar Solicitudes</a>
                <a href="revisar_solicitudes.php"><i class="fa-solid fa-comments"></i> Solicitudes de Material</a>
                <a href="configuracion_almacen.php"><i class="fa-solid fa-gear"></i> Configuración</a>
            <?php elseif ($role === 'Empleado'): ?>
                <a href="solicitudes_crear.php"><i class="fa-solid fa-plus-square"></i> Solicitar Material</a>
                <a href="mis_solicitudes.php"><i class="fa-solid fa-clipboard-list"></i> Mis Solicitudes</a>
                <a href="solicitar_material_general.php"><i class="fa-solid fa-toolbox"></i> Solicitud general</a>
            <?php endif; ?>
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
                <div class="form-title">Agregar Almacén</div>
                <?php if (!empty($error)): ?>
                    <div class="form-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form class="takab-form" method="post" action="">
                    <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                    <label for="nombre">Nombre:</label>
                    <input id="nombre" type="text" name="nombre" value="<?= htmlspecialchars($almacen['nombre'] ?? '') ?>" required>

                    <label for="ubicacion">Ubicación:</label>
                    <input id="ubicacion" type="text" name="ubicacion" value="<?= htmlspecialchars($almacen['ubicacion'] ?? '') ?>">

                    <label for="responsable_id">Responsable:</label>
                    <select id="responsable_id" name="responsable_id">
                        <option value="">Ninguno</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= (int) $u['id'] ?>" <?= (isset($almacen) && (int) $almacen['responsable_id'] === (int) $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="check-label">
                        <input type="checkbox" name="es_principal" value="1" <?= !empty($almacen['es_principal']) ? 'checked' : '' ?>>
                        ¿Es principal?
                    </label>

                    <button type="submit">Guardar</button>
                </form>
                <a class="form-link" href="almacenes.php"><i class="fa fa-arrow-left"></i> Volver</a>
            </div>
        </main>
    </div>
</div>
</body>
</html>
