<?php
session_start();
include 'conexion.php';

// Solo administradores
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado");
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $conn->prepare("DELETE FROM propuestas WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php");
exit;
