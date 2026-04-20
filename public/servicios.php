<?php
require_once __DIR__ . '/../app/helpers/Session.php';
Session::requireLogin();

$moduleTitle = 'Servicios';
$nombre = $_SESSION['nombre'] ?? 'TAKAB';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($moduleTitle); ?> - TAKAB</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/config.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body, html { background-color: #f5f7fa; }
        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background-image: url('assets/images/edificios13.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .card {
            background: #ffffffc7;
            border-radius: 20px;
            box-shadow: 0 6px 30px 0 rgba(16, 24, 40, 0.30);
            width: min(860px, 100%);
            padding: 28px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 12px;
        }
        .title { margin: 0; font-size: 1.7rem; font-weight: 900; color: #223264; }
        .sub { margin: 6px 0 0 0; color: #364e96; }
        .user { color: #6476a8; font-weight: 700; }
        .actions { margin-top: 18px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 800;
            background: #2563eb;
            color: #fff;
        }
        .btn.secondary { background: #223264; }
        .note {
            margin-top: 14px;
            background: #2563eb14;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 14px;
            color: #223264;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="header">
                <div>
                    <h1 class="title"><?= htmlspecialchars($moduleTitle); ?></h1>
                    <p class="sub">Panel del módulo (placeholder).</p>
                    <div class="user">Sesión: <?= htmlspecialchars($nombre); ?><?= $role ? ' • ' . htmlspecialchars($role) : ''; ?></div>
                </div>
                <img src="assets/images/LogoTakab2.webp" alt="Logo Takab" style="height:44px;width:auto;">
            </div>

            <div class="note">
                <b>En construcción.</b> Este módulo aún no está implementado.
                Aquí se integrará: órdenes de servicio, tickets, checklists, evidencias (fotos) y reportes.
            </div>

            <div class="actions">
                <a class="btn" href="dashboard.php"><i class="fa-solid fa-boxes-stacked"></i> Ir a Inventarios</a>
                <a class="btn secondary" href="menu.php"><i class="fa-solid fa-layer-group"></i> Cambiar módulo</a>
                <a class="btn" style="background:#d32323" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión</a>
            </div>
        </div>
    </div>
</body>
</html>