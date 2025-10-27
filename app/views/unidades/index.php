<?php
Session::requireLogin(['Administrador']);

$role = $_SESSION['role'];
$nombre = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Unidades de Medida - TAKAB</title>
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
            <div class="main-table-card">
                <div class="main-table-header-row">
                    <div class="main-table-title">Unidades de Medida</div>
                    <a class="btn-main" href="unidades_create.php"><i class="fa fa-plus"></i> Agregar unidad</a>
                </div>
                <?php if (isset($_GET['success'])): ?>
                    <div class="form-success">Operación realizada correctamente.</div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="form-error">Unidad eliminada.</div>
                <?php endif; ?>
                <?php if (isset($_GET['error']) && $_GET['error'] === 'csrf'): ?>
                    <div class="form-error">No pudimos completar la acción por motivos de seguridad.</div>
                <?php endif; ?>
                <table class="takab-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Abreviación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unidades as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['abreviacion']) ?></td>
                            <td class="table-actions">
                                <a href="unidades_edit.php?id=<?= (int) $u['id'] ?>"><i class="fa fa-pen"></i> Editar</a>
                                <form method="post" action="unidades_delete.php" style="display:inline-block" onsubmit="return confirm('¿Eliminar unidad?');">
                                    <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" class="btn-danger"><i class="fa fa-trash"></i> Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($unidades)): ?>
                        <tr>
                            <td colspan="3" style="text-align:center;">No hay unidades registradas.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
</body>
</html>
