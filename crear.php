<?php
// ---------------------------
// Inicio de sesión y conexión
// - Inicia la sesión PHP
// - Incluye la conexión a la base de datos
// ---------------------------
session_start();
include 'conexion.php';

// ---------------------------
// Protección de ruta: solo usuarios logueados pueden crear propuestas
// Si no hay `usuario_id` en la sesión, redirige a `login.php`
// ---------------------------
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// ---------------------------
// Manejo del POST: se procesa el formulario de creación
// - Toma los campos enviados por POST
// - Inserta la propuesta asociada al usuario logueado (usuario_id)
// - Redirige a `index.php` tras guardar
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propuesta = $_POST['propuesta'];
    $localidad = $_POST['localidad'];
    $descripcion = $_POST['descripcion'];
    $contacto = $_POST['contacto'];
    $categoria = $_POST['categoria'] ?? '';

    // Forzamos el uso de usuario_id de la sesión
    $usuario_id = $_SESSION['usuario_id'];
    // Nota: la columna `usuario_id` debe existir en la tabla `propuestas`.
    $stmt = $conn->prepare("INSERT INTO propuestas (propuesta, localidad, descripcion, contacto, categoria, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $propuesta, $localidad, $descripcion, $contacto, $categoria, $usuario_id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Propuesta</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<body class="propuesta-container">
    <lottie-player 
        src="json/Get in touch with us  Online managers.json"
        style="position: fixed; top: 0; left: 0; width: 50%; height: 100%; z-index: -1; background: transparent;"
        loop="true"
        autoplay="true"
        speed="1">
    </lottie-player>

    <div class="propuesta-box">
        <!-- Formulario para crear una nueva propuesta -->
        <h2>Crear Propuesta Laboral</h2>
        <form method="POST">
            <!-- Título de la propuesta (requerido) -->
            <input type="text" name="propuesta" placeholder="Título" required>
            <!-- Localidad -->
            <input type="text" name="localidad" placeholder="Localidad">

            <!-- Descripción -->
            <textarea name="descripcion" placeholder="Descripción"></textarea>
            <!-- Contacto -->
            <input type="text" name="contacto" placeholder="Contacto">
            <!-- Categoría (select) -->
            <select name="categoria" required>
                <option value="">Selecciona una categoría</option>
                <option value="Tecnología">Tecnología</option>
                <option value="Diseño">Diseño</option>
                <option value="Finanzas">Finanzas</option>
                <option value="Salud">Salud</option>
                <option value="Educación">Educación</option>
                <option value="Administración">Administración</option>
                <option value="Ventas">Ventas</option>
                <option value="Legal">Legal</option>
                <option value="Recursos Humanos">Recursos Humanos</option>
                <option value="Construcción">Construcción</option>
            </select>
            <button type="submit">Guardar</button>
        </form>
    </div>
</body>
</html>
