<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$propuesta_id = $_GET['id'] ?? null;
if (!$propuesta_id) exit;

// Verificar si ya es favorito
$stmt = $conn->prepare("SELECT * FROM favoritos WHERE usuario_id=? AND propuesta_id=?");
$stmt->bind_param("ii", $usuario_id, $propuesta_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Quitar favorito
    $del = $conn->prepare("DELETE FROM favoritos WHERE usuario_id=? AND propuesta_id=?");
    $del->bind_param("ii", $usuario_id, $propuesta_id);
    $del->execute();
} else {
    // Agregar favorito
    $ins = $conn->prepare("INSERT INTO favoritos (usuario_id, propuesta_id) VALUES (?, ?)");
    $ins->bind_param("ii", $usuario_id, $propuesta_id);
    $ins->execute();
}

header("Location: index.php");
exit;
