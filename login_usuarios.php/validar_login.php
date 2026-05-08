<?php
session_start();
require_once 'funciones.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    $usuario_valido = verificarCredenciales($usuario, $password);

    if ($usuario_valido) {
        $_SESSION['usuario'] = $usuario_valido['usuario'];
        $_SESSION['tipo'] = $usuario_valido['tipo'];
        header("Location: index.php");
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}