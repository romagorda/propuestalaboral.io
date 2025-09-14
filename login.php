<?php
session_start();
include 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gmail = $_POST['gmail'];
    $contraseña = $_POST['contraseña'];

    // Buscar usuario por Gmail
    $stmt = $conn->prepare("SELECT * FROM login WHERE gmail=?");
    $stmt->bind_param("s", $gmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
        // Login exitoso
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
        header("Location: index.php");
        exit;
    } else {
        $mensaje = "Gmail o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<h2>Iniciar Sesión</h2>
<form method="POST">
    <input type="email" name="gmail" placeholder="Gmail" required><br>
    <input type="password" name="contraseña" placeholder="Contraseña" required><br>
    <button type="submit">Ingresar</button>
</form>
<p style="color:red;"><?= $mensaje ?></p>
<p>¿No tenés cuenta? <a href="registro.php">Registrate aquí</a></p>
</body>
</html>
