
<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre_usuario'];

// Obtener todas las propuestas
$result = $conn->query("SELECT * FROM propuestas ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bolsa de Trabajo</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
    <!-- Sidebar izquierdo -->
    <aside class="sidebar">
        <div class="profile">
            <img src="perfil.png" alt="Perfil">
            <span><?= htmlspecialchars($nombre_usuario) ?></span>
        </div>
        <nav class="menu">
            <a href="#">Inicio</a>
            <a href="#">Búsqueda</a>
            <a href="#">Configuración</a>
            <a href="logout.php">Cerrar sesión</a>
        </nav>
        <footer>job inc-</footer>
    </aside>

    <!-- Main content -->
    <main class="main">
        <header class="header">
            <h2>PROPUESTAS LABORALES</h2>
            <a href="crear.php" class="btn">CREAR PROPUESTA</a>
        </header>

        <!-- Propuestas -->
        <section class="propuestas">
            <?php while($p = $result->fetch_assoc()):
                // Verificar si la propuesta es favorita del usuario
                $fav_stmt = $conn->prepare("SELECT * FROM favoritos WHERE usuario_id=? AND propuesta_id=?");
                $fav_stmt->bind_param("ii", $usuario_id, $p['id']);
                $fav_stmt->execute();
                $fav_result = $fav_stmt->get_result();
                $es_favorito = $fav_result->num_rows > 0;
            ?>
            <div class="card">
                <h3><?= htmlspecialchars($p['propuesta']) ?></h3>
                <p class="ubicacion"><?= htmlspecialchars($p['localidad']) ?></p>
                <p><?= htmlspecialchars($p['descripcion']) ?></p>
                <div class="acciones">
                    <a href="editar.php?id=<?= $p['id'] ?>" class="ver-mas">EDITAR</a>
                    <a href="eliminar.php?id=<?= $p['id'] ?>" class="ver-mas" onclick="return confirm('¿Seguro que querés eliminar esta propuesta?')">ELIMINAR</a>
                    <a href="favorito.php?id=<?= $p['id'] ?>" class="estrella"><?= $es_favorito ? "⭐" : "☆" ?></a>
                </div>
            </div>
            <?php endwhile; ?>
        </section>
    </main>

    <!-- Sidebar derecho favoritos -->
    <aside class="favoritos">
        <h3>Favoritos</h3>
        <?php
        $fav_stmt = $conn->prepare("SELECT p.* FROM propuestas p 
                                    INNER JOIN favoritos f ON p.id=f.propuesta_id
                                    WHERE f.usuario_id=?");
        $fav_stmt->bind_param("i", $usuario_id);
        $fav_stmt->execute();
        $fav_result = $fav_stmt->get_result();

        while($f = $fav_result->fetch_assoc()):
        ?>
        <div class="card small">
            <h4><?= htmlspecialchars($f['propuesta']) ?></h4>
            <p><?= htmlspecialchars($f['localidad']) ?></p>
            <a href="favorito.php?id=<?= $f['id'] ?>">⭐</a>
        </div>
        <?php endwhile; ?>
    </aside>
</div>
</body>
</html>
