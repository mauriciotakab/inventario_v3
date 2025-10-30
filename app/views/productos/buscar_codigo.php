<?php
$role = $_SESSION['role'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
$codigo = trim($codigo ?? '');
$error = $error ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar codigo de barras | TAKAB</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/config.css">
    <link rel="stylesheet" href="/assets/css/productos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .barcode-form {
            max-width: 520px;
            margin: 0 auto;
            padding: 32px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(15,41,90,0.08);
            text-align: center;
        }
        .barcode-form input[type="text"] {
            font-size: 1.2rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .barcode-form .help {
            font-size: 0.9rem;
            color: #6c7a99;
            margin-top: 8px;
        }
    </style>
</head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="content-area">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>

        <main class="dashboard-main productos-main">
            <div class="productos-header">
                <div>
                    <h1>Buscar por codigo de barras</h1>
                    <p class="productos-header-desc">Escanea o escribe manualmente el codigo para ubicar un producto.</p>
                </div>
                <div class="productos-header-actions">
                    <a class="btn-secondary" href="productos.php"><i class="fa fa-boxes-stacked"></i> Volver a productos</a>
                </div>
            </div>

            <section>
                <div class="barcode-form">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                        <div class="form-group">
                            <label for="codigo_barras">Codigo de barras</label>
                            <input type="text" id="codigo_barras" name="codigo_barras" value="<?= htmlspecialchars($codigo) ?>" autofocus required>
                        </div>
                        <p class="help">Puedes usar un lector USB: solo coloca el cursor en la caja y escanea.</p>
                        <button type="submit" class="btn-main"><i class="fa fa-search"></i> Buscar</button>
                    </form>
                </div>
            </section>
        </main>
    </div>
</div>
<?php include __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>

