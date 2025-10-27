<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $propuesta_id = $_POST['propuesta_id'] ?? 0;
    $motivo = trim($_POST['motivo'] ?? '');

    if ($propuesta_id && $motivo) {
        $stmt = $conn->prepare("INSERT INTO reportes (propuesta_id, usuario_id, motivo) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $propuesta_id, $usuario_id, $motivo);
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Reporte enviado correctamente.";
        } else {
            $_SESSION['msg'] = "Error al enviar el reporte.";
        }
    }
}

header("Location: index.php");
exit;
?>
