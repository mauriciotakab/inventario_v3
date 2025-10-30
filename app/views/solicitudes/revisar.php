<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::requireLogin(['Administrador', 'Almacen']);
$breadcrumbs = [['label' => 'Revisión de solicitudes']];
$role = $_SESSION['role'] ?? 'Empleado';
$nombre = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes por Revisar / Entregar | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/solicitudes-revisar.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="main-layout">
            <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="content-area">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>


<div class="revisar-main">
    <div class="revisar-title">
        <i class="fa-solid fa-clipboard-check"></i>
        Solicitudes por Revisar / Entregar
    </div>
    <table class="takab-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Solicitante</th>
                <th>Tipo de Solicitud</th>
                <th>Motivo/Destino</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($solicitudes as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['fecha_solicitud']) ?></td>
                    <td><?= htmlspecialchars($s['usuario']) ?></td>
                    <td><?= htmlspecialchars($s['tipo_solicitud']) ?></td>
                    <td><?= htmlspecialchars($s['comentario']) ?></td>
                    <td>
                        <?php
                            $estado = strtolower($s['estado']);
                            $clase = match($estado) {
                                'pendiente' => 'badge-pendiente',
                                'aprobada' => 'badge-aprobada',
                                'entregada' => 'badge-entregada',
                                'cancelada' => 'badge-cancelada',
                                'rechazada' => 'badge-rechazada',
                                default => 'badge'
                            };
                            echo "<span class='badge $clase'>" . ucfirst(htmlspecialchars($s['estado'])) . "</span>";
                        ?>
                    </td>
                    <td>
                        <?php if ($s['estado'] == 'pendiente'): ?>
                            <a href="solicitud_aprobar.php?id=<?= $s['id'] ?>" class="btn-accion aprobar">
                                <i class="fa fa-gavel"></i> Aprobar / Rechazar
                            </a>
                        <?php elseif ($s['estado'] == 'aprobada'): ?>
                            <a href="solicitud_entregar.php?id=<?= $s['id'] ?>" class="btn-accion entregar">
                                <i class="fa fa-truck"></i> Entregar
                            </a>
                        <?php else: ?>
                            <span class="badge badge-gray">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
    </div>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>
