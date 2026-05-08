<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>

        <?php
        if (isset($_GET['error'])) {
            echo '<div class="alerta error">Usuario o contraseña incorrectos.</div>';
        }
        if (isset($_GET['cerrada'])) {
            echo '<div class="alerta exito">Sesión cerrada correctamente.</div>';
        }
        if (isset($_GET['error']) && $_GET['error'] === 'sesion_invalida') {
            echo '<div class="alerta error">Tu sesión ya no es válida. Inicia sesión nuevamente.</div>';
        }
        ?>

        <form action="validar_login.php" method="POST">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-entrar">Entrar</button>
        </form>


    </div>
</body>
</html>
