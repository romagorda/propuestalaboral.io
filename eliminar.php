<?php
session_start();
include 'conexion.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    die("Debes iniciar sesión");
}

$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'] ?? 'usuario';

$id = $_GET['id'] ?? null;
if ($id) {
    // Verificar que el usuario es el dueño de la propuesta o es admin
    $stmt_check = $conn->prepare("SELECT usuario_id FROM propuestas WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $propuesta_data = $result_check->fetch_assoc();
    
    if (!$propuesta_data || ($propuesta_data['usuario_id'] != $usuario_id && $rol !== 'admin')) {
        die("No tienes permiso para eliminar esta propuesta");
    }
    
    // Primero eliminar registros relacionados en otras tablas
    
    // Eliminar favoritos asociados
    $stmt_fav = $conn->prepare("DELETE FROM favoritos WHERE propuesta_id=?");
    $stmt_fav->bind_param("i", $id);
    $stmt_fav->execute();
    
    // Eliminar tags asociados (si existe la tabla propuesta_tag)
    $stmt_tags = $conn->prepare("DELETE FROM propuesta_tag WHERE propuesta_id=?");
    $stmt_tags->bind_param("i", $id);
    $stmt_tags->execute();
    
    // Eliminar reportes asociados (si existe)
    $stmt_rep = $conn->prepare("DELETE FROM reportes WHERE propuesta_id=?");
    $stmt_rep->bind_param("i", $id);
    $stmt_rep->execute();
    
    // Finalmente eliminar la propuesta
    $stmt = $conn->prepare("DELETE FROM propuestas WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php");
exit;
