<?php
// ---------------------------
// Inicio y autorización
// - Inicia sesión y conecta a la BD
// - Solo usuarios con rol 'admin' pueden acceder a esta página
// ---------------------------
session_start();
include 'conexion.php';

// Verificar rol admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado");
}

// ---------------------------
// Cargar propuesta a editar
// - Obtiene el id por GET
// - Recupera la fila correspondiente de la tabla `propuestas`
// ---------------------------
$id = $_GET['id'] ?? null;
if (!$id) exit("ID no válido");

$stmt = $conn->prepare("SELECT * FROM propuestas WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$propuesta = $result->fetch_assoc();
if (!$propuesta) exit("Propuesta no encontrada");

// ---------------------------
// Manejo del POST: actualizar propuesta
// - Actualiza los campos y redirige a `index.php`
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE propuestas SET propuesta=?, localidad=?, descripcion=?, contacto=?, categoria=? WHERE id=?");
    $stmt->bind_param("sssssi", $_POST['propuesta'], $_POST['localidad'], $_POST['descripcion'], $_POST['contacto'], $_POST['categoria'], $id);
    $stmt->execute();
    header("Location: index.php");
    exit;
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
