<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propuesta = $_POST['propuesta'];
    $localidad = $_POST['localidad'];
    $descripcion = $_POST['descripcion'];
    $contacto = $_POST['contacto'];

    $stmt = $conn->prepare("INSERT INTO propuestas (propuesta, localidad, descripcion, contacto) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $propuesta, $localidad, $descripcion, $contacto);
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
</head>
<body>
<h2>Crear Propuesta Laboral</h2>
<form method="POST">
    <input type="text" name="propuesta" placeholder="Título" required><br>
    <input type="text" name="localidad" placeholder="Localidad"><br>
    <textarea name="descripcion" placeholder="Descripción"></textarea><br>
    <input type="text" name="contacto" placeholder="Contacto"><br>
    <button type="submit">Guardar</button>
</form>
</body>
</html>
