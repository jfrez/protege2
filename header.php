<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROTEGE</title>
    <!-- Incluye Font Awesome si es necesario -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Incluye Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Estilos personalizados para los colores UDP -->
    <style>
        /* Definir los colores institucionales */
        :root {
            --udp-red: #d50032;
            --udp-black: #000000;
            --udp-white: #ffffff;
        }
        /* Estilos para la barra de navegación */
        .navbar-udp {
            background-color: var(--udp-red);
        }
        .navbar-udp .navbar-brand,
        .navbar-udp .navbar-text,
        .navbar-udp .nav-link,
        .navbar-udp .btn {
            color: var(--udp-white) !important;
        }
        .navbar-udp .btn-outline-light {
            border-color: var(--udp-white);
            color: var(--udp-white);
        }
        .navbar-udp .btn-outline-light:hover {
            background-color: var(--udp-white);
            color: var(--udp-red);
        }
    </style>
</head>
<body>

<?php
if (isset($_SESSION['userid'])) {
?>
<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-udp">
    <div class="container d-flex align-items-center">
        <a class="navbar-brand font-weight-bold" href="index.php" style="margin-right: auto;">PROTEGE</a>
        <span class="navbar-text mr-2" style="white-space: nowrap;">
            <?php echo htmlspecialchars($_SESSION['name']); ?>
        </span>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a class="nav-link" href="user_management.php" style="color: var(--udp-white);">Gestión Usuarios</a>
            <a class="nav-link" href="evaluation_report.php" style="color: var(--udp-white);">Reporte Evaluaciones</a>
        <?php endif; ?>
        <a class="nav-link" href="change_password.php" style="color: var(--udp-white);">Cambiar Clave</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
    </div>
</nav>
<?php } ?>

<!-- Contenido de la página -->

<!-- Incluir Bootstrap JS y dependencias -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Bootstrap JS Bundle (incluye Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
