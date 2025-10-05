<?php
session_start();
include 'conexion.php';

// Requerir sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario (incluyendo avatar_url)
$stmt = $conn->prepare("SELECT id, nombre_usuario, gmail, rol, avatar_url FROM login WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener propuestas del usuario
$prop_stmt = $conn->prepare("SELECT * FROM propuestas WHERE usuario_id = ? ORDER BY id DESC");
$prop_stmt->bind_param("i", $usuario_id);
$prop_stmt->execute();
$props = $prop_stmt->get_result();

// Obtener propuestas favoritas del usuario
$fav_stmt = $conn->prepare("SELECT p.* FROM propuestas p INNER JOIN favoritos f ON p.id = f.propuesta_id WHERE f.usuario_id = ? ORDER BY p.id DESC");
$fav_stmt->bind_param("i", $usuario_id);
$fav_stmt->execute();
$favs = $fav_stmt->get_result();
// Contadores: número de propuestas y número de favoritos (hechos por otros usuarios sobre las propuestas de este usuario)
$count_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM propuestas WHERE usuario_id = ?");
$count_stmt->bind_param("i", $usuario_id);
$count_stmt->execute();
$count_res = $count_stmt->get_result()->fetch_assoc();
$posts_count = $count_res['cnt'] ?? 0;

$fav_count_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM favoritos f INNER JOIN propuestas p ON f.propuesta_id = p.id WHERE p.usuario_id = ?");
$fav_count_stmt->bind_param("i", $usuario_id);
$fav_count_stmt->execute();
$fav_count = $fav_count_stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil - <?= htmlspecialchars($usuario['nombre_usuario'] ?? 'Usuario') ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<div class="container">
    <!-- Sidebar izquierdo -->
    <aside class="sidebar">
        <div class="profile">
            <a href="perfil.php" class="profile-link">
                <?php if (!empty($usuario['avatar_url'])): ?>
                    <img src="<?= htmlspecialchars($usuario['avatar_url']) ?>" alt="Perfil">
                <?php else: ?>
                    <img src="perfil.png" alt="Perfil">
                <?php endif; ?>
                <span><?= htmlspecialchars($usuario['nombre_usuario']) ?> (<?= htmlspecialchars($usuario['rol']) ?>)</span>
            </a>
        </div>
        <nav class="menu">
            <a href="index.php">Inicio</a>
            <a href="#">Mis Propuestas</a>
            <a href="#">Configuración</a>
            <a href="logout.php">Cerrar sesión</a>
        </nav>
        <footer>job inc-</footer>
    </aside>

    <!-- Main perfil -->
    <main class="perfil-page">
        <!-- Banner grande -->
        <div class="perfil-banner" style="background-image: url('json/Job proposal review animation.json');"></div>

        <!-- Cabecera con avatar superpuesto y botón editar -->
        <div class="perfil-header">
            <?php if (!empty($usuario['avatar_url'])): ?>
                <img src="<?= htmlspecialchars($usuario['avatar_url']) ?>" alt="Avatar" class="perfil-avatar">
            <?php else: ?>
                <img src="perfil.png" alt="Avatar" class="perfil-avatar">
            <?php endif; ?>
            <div class="perfil-meta">
                <h1><?= htmlspecialchars($usuario['nombre_usuario']) ?></h1>
                <div class="perfil-handle">@<?= htmlspecialchars(strtolower($usuario['nombre_usuario'])) ?></div>
                <div class="perfil-actions">
                    <a href="editar_perfil.php" class="btn btn-outline">Editar perfil</a>
                </div>
                <div class="perfil-stats">
                    <span><strong><?= $posts_count ?></strong> Posts</span>
                    <span><strong><?= $fav_count ?></strong> Favoritos</span>
                </div>
            </div>
        </div>

        <!-- Tabs funcionales -->
        <nav class="perfil-tabs">
            <button id="tab-posts" class="active" type="button">Posts</button>
            <button id="tab-favs" type="button">Favoritos</button>
        </nav>

        <section id="posts-section" class="perfil-props">
            <h3>Propuestas publicadas</h3>
            <?php foreach($props as $p): ?>
                <div class="card">
                    <h4><?= htmlspecialchars($p['propuesta']) ?></h4>
                    <p class="categoria"><?= htmlspecialchars($p['categoria']) ?></p>
                    <p class="ubicacion"><?= htmlspecialchars($p['localidad']) ?></p>
                    <p><?= htmlspecialchars($p['descripcion']) ?></p>
                </div>
            <?php endforeach; ?>
        </section>
        <section id="favs-section" class="perfil-props" style="display:none;">
            <h3>Favoritos guardados</h3>
            <?php foreach($favs as $f): ?>
                <div class="card">
                    <h4><?= htmlspecialchars($f['propuesta']) ?></h4>
                    <p class="categoria"><?= htmlspecialchars($f['categoria']) ?></p>
                    <p class="ubicacion"><?= htmlspecialchars($f['localidad']) ?></p>
                    <p><?= htmlspecialchars($f['descripcion']) ?></p>
                </div>
            <?php endforeach; ?>
        </section>
        <script>
        // Tabs funcionales con JS
        document.getElementById('tab-posts').onclick = function() {
            this.classList.add('active');
            document.getElementById('tab-favs').classList.remove('active');
            document.getElementById('posts-section').style.display = '';
            document.getElementById('favs-section').style.display = 'none';
        };
        document.getElementById('tab-favs').onclick = function() {
            this.classList.add('active');
            document.getElementById('tab-posts').classList.remove('active');
            document.getElementById('posts-section').style.display = 'none';
            document.getElementById('favs-section').style.display = '';
        };
        </script>
    </main>
</div>
</body>
</html>