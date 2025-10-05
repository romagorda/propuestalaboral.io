<?php
session_start();
include 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gmail = $_POST['gmail'];
    $contraseña = $_POST['contraseña'];

    $stmt = $conn->prepare("SELECT * FROM login WHERE gmail=?");
    $stmt->bind_param("s", $gmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
        $_SESSION['rol'] = $usuario['rol']; // 🔥 Guardamos el rol
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
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<body class="auth-container">

<lottie-player 
      src="json/Job proposal review animation.json" 
      background="transparent" 
      speed="1" 
      style="position: fixed; top: 0; left: 0; width: 50%; height: 100%; z-index: -1;" 
      loop 
      autoplay>
  </lottie-player>
  
    <div class="auth-box">
        <h2>Iniciar Sesión</h2>
        <form method="POST">
            <input type="email" name="gmail" placeholder="Gmail" required>
            <input type="password" name="contraseña" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <p class="error"><?= $mensaje ?></p>
        <p>¿No tenés cuenta? <a href="registro.php">Registrate aquí</a></p>
    </div>
</body>
</html>
