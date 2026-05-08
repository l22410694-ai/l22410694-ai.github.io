<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'Admin') {
    header("Location: login.php?error=2");
    exit();
}

require_once 'funciones.php';

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$titulo = '';
$datos  = null;

if ($accion === 'modificar' && isset($_GET['usuario'])) {
    $usuario_modificar = $_GET['usuario'];
    // Fix #8: consulta directa en lugar de iterar todos los usuarios
    $datos = obtenerUsuarioPorNombre($usuario_modificar);
    if (!$datos) {
        header("Location: usuarios.php?error=" . urlencode("Usuario no encontrado."));
        exit();
    }
    $titulo = "Modificar Usuario";
} elseif ($accion === 'agregar') {
    $titulo = "Agregar Usuario";
} else {
    header("Location: usuarios.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($titulo); ?></h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alerta error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="usuarios_a.php" method="POST">
            <?php if ($accion === 'modificar'): ?>
                <input type="hidden" name="accion" value="modificar">
                <input type="hidden" name="usuario_original" value="<?php echo htmlspecialchars($datos['usuario']); ?>">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario"
                           value="<?php echo htmlspecialchars($datos['usuario']); ?>" required
                           pattern="[a-zA-Z0-9_]{3,30}" title="Solo letras, números y guion bajo (3-30 caracteres)">
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($datos['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" required>
                        <option value="Admin"   <?php if ($datos['tipo'] === 'Admin')   echo 'selected'; ?>>Admin</option>
                        <option value="Usuario" <?php if ($datos['tipo'] === 'Usuario') echo 'selected'; ?>>Usuario</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nueva_password">Nueva contraseña (opcional, mínimo 6 caracteres)</label>
                    <input type="password" id="nueva_password" name="nueva_password" minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirmar_password">Confirmar contraseña</label>
                    <input type="password" id="confirmar_password" name="confirmar_password">
                </div>
            <?php else: ?>
                <input type="hidden" name="accion" value="agregar">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" required
                           pattern="[a-zA-Z0-9_]{3,30}" title="Solo letras, números y guion bajo (3-30 caracteres)">
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña (mínimo 6 caracteres)</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirmar_password">Confirmar contraseña</label>
                    <input type="password" id="confirmar_password" name="confirmar_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" required>
                        <option value="Admin">Admin</option>
                        <option value="Usuario" selected>Usuario</option>
                    </select>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn">
                <?php echo ($accion === 'modificar') ? 'Guardar cambios' : 'Agregar usuario'; ?>
            </button>
            <a href="usuarios.php" class="btn btn-cancelar">Cancelar</a>
        </form>
    </div>
</body>
</html>
