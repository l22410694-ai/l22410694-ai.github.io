<?php
define('DB_PATH', __DIR__ . '/usuarios.db');
define('LOG_FILE', __DIR__ . '/auditoria.log');

function conectarDB(): PDO {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        tipo TEXT NOT NULL DEFAULT 'Usuario',
        email TEXT UNIQUE NOT NULL
    )");

    // Si la tabla está vacía, insertar usuarios por defecto
    $count = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    if ($count == 0) {
        $stmt = $db->prepare("INSERT INTO usuarios (usuario, password, tipo, email) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Emmanuel', '12345678', 'Admin',   'emmanuel@temp.com']);
        $stmt->execute(['chispa',   '12345678', 'Usuario', 'chispa@temp.com']);
        $stmt->execute(['nuevo',    '12345678', 'Usuario', 'nuevo@temp.com']);
    }

    return $db;
}

function registrarLog(string $accion, string $detalles): void {
    $fecha = date('Y-m-d H:i:s');
    $usuarioSesion = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Sistema';
    $linea = "[$fecha] Usuario: $usuarioSesion | Acción: $accion | $detalles" . PHP_EOL;
    file_put_contents(LOG_FILE, $linea, FILE_APPEND | LOCK_EX);
}

function leerUsuarios(): array {
    $db = conectarDB();
    $stmt = $db->query("SELECT usuario, tipo, email FROM usuarios ORDER BY usuario");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerUsuarioPorNombre(string $usuario): array|false {
    $db = conectarDB();
    $stmt = $db->prepare("SELECT usuario, tipo, email FROM usuarios WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);
    return $datos ?: false;
}

function verificarCredenciales(string $usuario, string $password): array|false {
    $db = conectarDB();
    $stmt = $db->prepare("SELECT usuario, tipo FROM usuarios WHERE usuario = :usuario AND password = :password");
    $stmt->execute([':usuario' => $usuario, ':password' => $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: false;
}

function usuarioExiste(string $usuario): bool {
    $db = conectarDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    return $stmt->fetchColumn() > 0;
}

function agregarUsuario(string $usuario, string $password, string $tipo, string $email): bool|string {
    if (usuarioExiste($usuario)) {
        return "El usuario ya existe.";
    }
    $db = conectarDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        return "El correo electrónico ya está registrado.";
    }
    $stmt = $db->prepare("INSERT INTO usuarios (usuario, password, tipo, email) VALUES (:usuario, :password, :tipo, :email)");
    $stmt->execute([
        ':usuario'  => $usuario,
        ':password' => $password,
        ':tipo'     => $tipo,
        ':email'    => $email
    ]);
    registrarLog('AGREGAR', "Usuario: $usuario, Tipo: $tipo, Email: $email");
    return true;
}

function modificarUsuario(string $usuarioOriginal, string $nuevoUsuario, string $tipo, string $email, ?string $nuevaPassword = null): bool|string {
    $db = conectarDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :original");
    $stmt->execute([':original' => $usuarioOriginal]);
    if ($stmt->fetchColumn() == 0) {
        return "Usuario no encontrado.";
    }
    if ($nuevoUsuario !== $usuarioOriginal && usuarioExiste($nuevoUsuario)) {
        return "El nuevo nombre de usuario ya existe.";
    }
    $stmt = $db->prepare("SELECT usuario FROM usuarios WHERE email = :email AND usuario != :original");
    $stmt->execute([':email' => $email, ':original' => $usuarioOriginal]);
    if ($stmt->fetchColumn()) {
        return "El correo electrónico ya está registrado por otro usuario.";
    }
    $sql = "UPDATE usuarios SET usuario = :nuevo_usuario, tipo = :tipo, email = :email";
    $params = [
        ':nuevo_usuario' => $nuevoUsuario,
        ':tipo'          => $tipo,
        ':email'         => $email,
        ':original'      => $usuarioOriginal
    ];
    if ($nuevaPassword !== null) {
        $sql .= ", password = :password";
        $params[':password'] = $nuevaPassword;
    }
    $sql .= " WHERE usuario = :original";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    registrarLog('MODIFICAR', "Usuario original: $usuarioOriginal → Nuevo: $nuevoUsuario, Tipo: $tipo, Email: $email" . ($nuevaPassword ? ", Contraseña cambiada" : ""));
    return true;
}

function eliminarUsuario(string $usuario): bool|string {
    if (!usuarioExiste($usuario)) {
        return "Usuario no encontrado.";
    }
    $db = conectarDB();
    $stmt = $db->prepare("SELECT tipo, email FROM usuarios WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $db->prepare("DELETE FROM usuarios WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    registrarLog('ELIMINAR', "Usuario: $usuario, Tipo: {$datos['tipo']}, Email: {$datos['email']}");
    return true;
}

function obtenerUsuariosPaginado(int $pagina = 1, int $porPagina = 5): array {
    $db = conectarDB();
    $total = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $totalPaginas = (int)ceil($total / $porPagina);
    $pagina = max(1, min($pagina, $totalPaginas ?: 1));
    $offset = ($pagina - 1) * $porPagina;
    $stmt = $db->prepare("SELECT usuario, tipo, email FROM usuarios ORDER BY usuario LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return [
        'usuarios'        => $usuarios,
        'total_paginas'   => $totalPaginas,
        'pagina_actual'   => $pagina,
        'total_registros' => $total
    ];
}
