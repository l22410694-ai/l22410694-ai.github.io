<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'Admin') {
    header("Location: login.php?error=2");
    exit();
}

require_once 'funciones.php';

// Parámetro de paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = 5;
$datosPaginado = obtenerUsuariosPaginado($pagina, $porPagina);
$usuarios = $datosPaginado['usuarios'];
$totalPaginas = $datosPaginado['total_paginas'];
$paginaActual = $datosPaginado['pagina_actual'];
$totalRegistros = $datosPaginado['total_registros'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de usuarios</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="container">
        <h2>Listado de usuarios</h2>

        <?php
        if (isset($_GET['exito'])) {
            echo '<div class="alerta exito">Operación realizada correctamente.</div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="alerta error">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>

        <p>Total de usuarios: <?php echo $totalRegistros; ?></p>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $contador = ($paginaActual - 1) * $porPagina + 1; ?>
                <?php foreach ($usuarios as $user): ?>
                <tr>
                    <td><?php echo $contador++; ?></td>
                    <td><?php echo htmlspecialchars($user['usuario']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['tipo']); ?></td>
                    <td>
                        <a href="usuarios_f.php?accion=modificar&usuario=<?php echo urlencode($user['usuario']); ?>" class="btn">Modificar</a>
                        <a href="usuarios_a.php?accion=eliminar&usuario=<?php echo urlencode($user['usuario']); ?>" 
                           class="btn btn-eliminar" 
                           onclick="return confirm('¿Estás seguro de eliminar a <?php echo htmlspecialchars($user['usuario']); ?>?');">Borrar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="5">No hay usuarios registrados.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <div class="paginacion">
            <?php if ($paginaActual > 1): ?>
                <a href="?pagina=<?php echo $paginaActual - 1; ?>" class="btn">&laquo; Anterior</a>
            <?php endif; ?>
            <span>Página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?></span>
            <?php if ($paginaActual < $totalPaginas): ?>
                <a href="?pagina=<?php echo $paginaActual + 1; ?>" class="btn">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="acciones">
            <a href="usuarios_f.php?accion=agregar" class="btn">Agregar Usuario</a>
            <a href="index.php" class="btn">Volver</a>
        </div>
    </div>
</body>
</html>