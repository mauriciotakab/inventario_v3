<?php
session_start();
// Si ya hay sesión, saltar directo al menú de módulos.
if (isset($_SESSION['user_id'])) {
    header('Location: menu.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>INICIO TAKAB</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/config.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body, html {
            color: var(--white);
            background: url('assets/images/edificios20.jpg') center center / cover no-repeat fixed;
          
        } 
    </style>
</head>
<body>
    <div class="content-area">
        
        <!-- Topbar -->
        <!--<header class="top-header">
            
            <div class="top-header-user">
                
                <span><?= htmlspecialchars($_SESSION['nombre'] ?? 'TAKAB'); ?></span>
                <i class="fa-solid fa-user-circle"></i>
                <a href="logout.php" class="logout-btn" title="Cerrar sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </header>-->

         <!--<main class="error-main">
            
            
           <div class="error-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="error-title">404 | Página no encontrada</div>
            <div class="error-desc">
                Lo sentimos, la página que buscas no existe o ha sido movida.<br>
                Si crees que es un error, contacta al administrador.
            </div> 
            <a href="dashboard.php" class="error-btn"><i class="fa fa-home"></i> Ir al inicio</a> -->

        <main class="inicio-main">
            
            
            <div class="login-container" >
            <div class="inicio-title">Bienvenido al 
                <br> ERP TAKAB
            </div>
            <div class="inicio-desc">Inicia sesión para acceder al sistema.</div> 
            <a href="login.php?next=menu.php" class="inicio-btn"><i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión</a> 
            <footer>
                <div>
                    <br>
                    <img src="assets/images/LogoTakab2.webp" alt="logo Takab" align-item="center justify-content-center" width="320px">
                </div>
            </footer>        
        </main>
      
    </div>
</body>
</html>
