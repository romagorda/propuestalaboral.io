<?php
include 'conexion.php';

$id = $_GET['id'] ?? null;
if (!$id) exit("ID no válido");

$stmt = $conn->prepare("SELECT * FROM propuestas WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$propuesta = $result->fetch_assoc();
if (!$propuesta) exit("Propuesta no encontrada");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE propuestas SET propuesta=?, localidad=?, descripcion=?, contacto=? WHERE id=?");
    $stmt->bind_param("ssssi", $_POST['propuesta'], $_POST['localidad'], $_POST['descripcion'], $_POST['contacto'], $id);
    $stmt->execute();
    header("Location: index.php");
    exit;
}
?>
<h2>Editar Propuesta</h2>
<form method="POST">
    <input type="text" name="propuesta" value="<?= htmlspecialchars($propuesta['propuesta']) ?>" required><br>
    <input type="text" name="localidad" value="<?= htmlspecialchars($propuesta['localidad']) ?>"><br>
    <textarea name="descripcion"><?= htmlspecialchars($propuesta['descripcion']) ?></textarea><br>
    <input type="text" name="contacto" value="<?= htmlspecialchars($propuesta['contacto']) ?>"><br>
    <button type="submit">Actualizar</button>
</form>
