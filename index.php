<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'funciones.php';

// Verificar que el usuario de la sesión aún existe en la BD.
// Si fue renombrado o eliminado, se destruye la sesión y se redirige al login.
$datosActuales = obtenerUsuarioPorNombre($_SESSION['usuario']);
if (!$datosActuales) {
    session_destroy();
    header("Location: login.php?error=sesion_invalida");
    exit();
}

// Sincronizar el tipo en caso de que haya cambiado en la BD
$_SESSION['tipo'] = $datosActuales['tipo'];

$usuario = $_SESSION['usuario'];
$tipo    = $_SESSION['tipo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel principal</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="panel-container">
        <h2>Inicio de Sesión<br>Bienvenido, <?php echo htmlspecialchars($usuario); ?></h2>
        <p>Tipo de cuenta: <?php echo htmlspecialchars($tipo); ?></p>

        <div class="menu">
            <?php if ($tipo === 'Admin'): ?>
                <a href="usuarios.php" class="btn">Gestión de Usuarios</a>
            <?php else: ?>
                <p>No tienes privilegios de administración.</p>
            <?php endif; ?>

            <a href="logout.php" class="btn btn-salir">Cerrar sesión</a>
        </div>
    </div>
</body>
</html>
