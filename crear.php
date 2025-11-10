<?php

session_start();
include 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propuesta = $_POST['propuesta'];
    $localidad = $_POST['localidad'];
    $descripcion = $_POST['descripcion'];
    $contacto = $_POST['contacto'];
    $categoria = $_POST['categoria'] ?? '';
    $horario = $_POST['horario'] ?? '';
    $salario = $_POST['salario'] ?? '';
    $usuario_id = $_SESSION['usuario_id'];

    $stmt = $conn->prepare("INSERT INTO propuestas (propuesta, localidad, descripcion, contacto, categoria, horario, salario, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssi", $propuesta, $localidad, $descripcion, $contacto, $categoria, $horario, $salario, $usuario_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la propuesta']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}