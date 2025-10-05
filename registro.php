<?php
session_start();
include 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_usuario'];
    $gmail = $_POST['gmail'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id FROM login WHERE gmail=?");
    $stmt->bind_param("s", $gmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mensaje = "El Gmail ya está registrado.";
    } else {
        $stmt = $conn->prepare("INSERT INTO login (nombre_usuario, gmail, contraseña, rol) VALUES (?, ?, ?, 'usuario')");
        $stmt->bind_param("sss", $nombre, $gmail, $contraseña);
        $stmt->execute();

        $_SESSION['usuario_id'] = $conn->insert_id;
        $_SESSION['nombre_usuario'] = $nombre;
        $_SESSION['rol'] = 'usuario';
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
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<body class="auth-container">

<lottie-player 
    src="json/job hunting.json"
    style="position: fixed; top: -10; left: 0; width: 140%; height: 100%; z-index: -1; background: transparent;"
    loop
    autoplay
    speed="1">
</lottie-player>

    <div class="auth-box">
        <h2>Registro de Usuario</h2>
        <form method="POST">
            <input type="text" name="nombre_usuario" placeholder="Nombre de usuario" required>
            <input type="email" name="gmail" placeholder="Gmail" required>
            <input type="password" name="contraseña" placeholder="Contraseña" required>
            <button type="submit">Registrarse</button>
        </form>
        <p class="error"><?= $mensaje ?></p>
        <p>¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a></p>
    </div>
</body>
</html>
