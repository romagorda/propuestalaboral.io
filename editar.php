<?php
// ---------------------------
// Inicio y autorización
// - Inicia sesión y conecta a la BD
// - Solo usuarios autenticados pueden acceder
// ---------------------------
session_start();
include 'conexion.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'] ?? 'usuario';

// ---------------------------
// Manejo del POST: actualizar propuesta
// - Actualiza los campos y devuelve JSON
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID no válido']);
        exit;
    }
    
    // Verificar que el usuario es el dueño de la propuesta o es admin
    $stmt_check = $conn->prepare("SELECT usuario_id FROM propuestas WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $propuesta_data = $result_check->fetch_assoc();
    
    if (!$propuesta_data || ($propuesta_data['usuario_id'] != $usuario_id && $rol !== 'admin')) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar esta propuesta']);
        exit;
    }
    
    $propuesta = $_POST['propuesta'];
    $localidad = $_POST['localidad'];
    $descripcion = $_POST['descripcion'];
    $contacto = $_POST['contacto'];
    $categoria = $_POST['categoria'];
    $horario = $_POST['horario'] ?? '';
    $salario = $_POST['salario'] ?? '';
    
    $stmt = $conn->prepare("UPDATE propuestas SET propuesta=?, localidad=?, descripcion=?, contacto=?, categoria=?, horario=?, salario=? WHERE id=?");
    $stmt->bind_param("sssssssi", $propuesta, $localidad, $descripcion, $contacto, $categoria, $horario, $salario, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la propuesta']);
    }
    exit;
}

// ---------------------------
// Manejo del GET: cargar propuesta a editar (para compatibilidad)
// ---------------------------
$id = $_GET['id'] ?? null;
if (!$id) exit("ID no válido");

// Verificar que el usuario es el dueño de la propuesta o es admin
$stmt = $conn->prepare("SELECT * FROM propuestas WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$propuesta = $result->fetch_assoc();

if (!$propuesta) {
    exit("Propuesta no encontrada");
}

if ($propuesta['usuario_id'] != $usuario_id && $rol !== 'admin') {
    exit("No tienes permiso para editar esta propuesta");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Propuesta</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <style>
        /* pequeñas adaptaciones si se necesita */
    </style>
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
        <h2>Editar Propuesta Laboral</h2>
        <form method="POST">
            <input type="text" name="propuesta" placeholder="Título" value="<?= htmlspecialchars($propuesta['propuesta']) ?>" required>
            <input type="text" name="localidad" placeholder="Localidad" value="<?= htmlspecialchars($propuesta['localidad']) ?>">

            <select name="categoria" required>
                <option value="">Selecciona una categoría</option>
                <?php
                $cats = ['Tecnología','Diseño','Finanzas','Salud','Educación','Administración','Ventas','Legal','Recursos Humanos','Construcción'];
                foreach ($cats as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= ($propuesta['categoria'] === $c) ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                <?php endforeach; ?>
            </select>

            <textarea name="descripcion" placeholder="Descripción"><?= htmlspecialchars($propuesta['descripcion']) ?></textarea>
            <input type="text" name="contacto" placeholder="Contacto" value="<?= htmlspecialchars($propuesta['contacto']) ?>">
            <button type="submit">Actualizar</button>
        </form>
    </div>
</body>
</html>
