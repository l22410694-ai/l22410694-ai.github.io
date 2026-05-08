<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'Admin') {
    header("Location: login.php?error=2");
    exit();
}

require_once 'funciones.php';

/**
 * Redirige con un mensaje de error sanitizado en la URL.
 * Fix #3: nunca interpolamos cadenas externas directamente en la URL sin sanitizar.
 */
function redirigirError(string $url, string $mensaje): void {
    // strip_tags elimina HTML/JS; urlencode lo codifica para la URL
    header("Location: " . $url . "?error=" . urlencode(strip_tags($mensaje)));
    exit();
}

$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($accion === 'agregar') {
        $usuario  = trim($_POST['usuario']  ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmar = trim($_POST['confirmar_password'] ?? '');
        $tipo     = $_POST['tipo'] ?? '';
        $email    = trim($_POST['email'] ?? '');

        $errores = [];
        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $usuario)) {
            $errores[] = "El nombre de usuario solo permite letras, números y guion bajo (3-30 caracteres).";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El correo electrónico no tiene un formato válido.";
        }
        if (strlen($password) < 6) {
            $errores[] = "La contraseña debe tener al menos 6 caracteres.";
        }
        if ($password !== $confirmar) {
            $errores[] = "Las contraseñas no coinciden.";
        }
        if (empty($tipo)) {
            $errores[] = "Debe seleccionar un tipo de usuario.";
        }

        if (!empty($errores)) {
            redirigirError("usuarios_f.php?accion=agregar", implode(" ", $errores));
        }

        $resultado = agregarUsuario($usuario, $password, $tipo, $email);
        if ($resultado === true) {
            header("Location: usuarios.php?exito=1");
        } else {
            redirigirError("usuarios_f.php?accion=agregar", $resultado);
        }
        exit();

    } elseif ($accion === 'modificar') {
        $usuario_original   = trim($_POST['usuario_original'] ?? '');
        $nuevo_usuario      = trim($_POST['usuario']          ?? '');
        $tipo               = $_POST['tipo']                  ?? '';
        $email              = trim($_POST['email']            ?? '');
        $nueva_password     = trim($_POST['nueva_password']   ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');

        $errores = [];
        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $nuevo_usuario)) {
            $errores[] = "El nombre de usuario solo permite letras, números y guion bajo (3-30 caracteres).";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El correo electrónico no tiene un formato válido.";
        }
        if (empty($tipo)) {
            $errores[] = "Debe seleccionar un tipo de usuario.";
        }
        if (!empty($nueva_password)) {
            if (strlen($nueva_password) < 6) {
                $errores[] = "La nueva contraseña debe tener al menos 6 caracteres.";
            }
            if ($nueva_password !== $confirmar_password) {
                $errores[] = "Las contraseñas no coinciden.";
            }
        }

        if (!empty($errores)) {
            redirigirError(
                "usuarios_f.php?accion=modificar&usuario=" . urlencode($usuario_original),
                implode(" ", $errores)
            );
        }

        $password_a_guardar = !empty($nueva_password) ? $nueva_password : null;
        $resultado = modificarUsuario($usuario_original, $nuevo_usuario, $tipo, $email, $password_a_guardar);

        if ($resultado === true) {
            header("Location: usuarios.php?exito=1");
        } else {
            redirigirError(
                "usuarios_f.php?accion=modificar&usuario=" . urlencode($usuario_original),
                $resultado
            );
        }
        exit();
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'eliminar') {
    $usuario = isset($_GET['usuario']) ? trim($_GET['usuario']) : '';

    if (empty($usuario)) {
        redirigirError("usuarios.php", "Usuario no especificado.");
    }
    if ($usuario === $_SESSION['usuario']) {
        redirigirError("usuarios.php", "No puedes eliminar tu propio usuario.");
    }

    $resultado = eliminarUsuario($usuario);
    if ($resultado === true) {
        header("Location: usuarios.php?exito=1");
    } else {
        redirigirError("usuarios.php", $resultado);
    }
    exit();
}

header("Location: usuarios.php");
exit();
