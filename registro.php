<?php
session_start();
include 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_usuario'];
    $gmail = $_POST['gmail'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);

    // Verificar si el gmail ya existe
    $stmt = $conn->prepare("SELECT id FROM login WHERE gmail=?");
    $stmt->bind_param("s", $gmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mensaje = "El Gmail ya está registrado.";
    } else {
        $stmt = $conn->prepare("INSERT INTO login (nombre_usuario, gmail, contraseña) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $gmail, $contraseña);
        $stmt->execute();

        $_SESSION['usuario_id'] = $conn->insert_id;
        $_SESSION['nombre_usuario'] = $nombre;
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<h2>Registro de Usuario</h2>
<form method="POST">
    <input type="text" name="nombre_usuario" placeholder="Nombre de usuario" required><br>
    <input type="email" name="gmail" placeholder="Gmail" required><br>
    <input type="password" name="contraseña" placeholder="Contraseña" required><br>
    <button type="submit">Registrarse</button>
</form>
<p style="color:red;"><?= $mensaje ?></p>
<p>¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a></p>
</body>
</html>
