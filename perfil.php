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
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
            max-width: 1400px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 0 50px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #7d5be6 0%, #6b4dd6 100%);
            color: white;
            padding: 30px 20px;
            flex-shrink: 0;
        }
        
        .profile {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .profile-link {
            text-decoration: none;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        
        .profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.3);
            object-fit: cover;
        }
        
        .menu {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .menu a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 15px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .menu a:before {
            content: '';
            width: 4px;
            height: 4px;
            background: rgba(255,255,255,0.5);
            border-radius: 50%;
        }
        
        .perfil-page {
            flex: 1;
            background: #f8f9fa;
            overflow-y: auto;
        }
        
        .perfil-banner {
            height: 200px;
            background: linear-gradient(135deg, #7d5be6 0%, #6b4dd6 100%);
            position: relative;
            overflow: hidden;
        }
        
        .perfil-banner:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="2" fill="white" opacity="0.1"/><circle cx="80" cy="40" r="1" fill="white" opacity="0.1"/><circle cx="40" cy="80" r="1.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        
        .perfil-header {
            position: relative;
            background: white;
            margin: -50px 30px 0;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            display: flex;
            align-items: flex-start;
            gap: 25px;
        }
        
        .perfil-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            object-fit: cover;
            margin-top: -80px;
        }
        
        .perfil-meta {
            flex: 1;
            padding-top: 20px;
        }
        
        .perfil-meta h1 {
            margin: 0 0 5px 0;
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .perfil-handle {
            color: #7d5be6;
            font-size: 1.1rem;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .perfil-actions {
            margin-bottom: 25px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .btn-outline {
            background: transparent;
            border-color: #7d5be6;
            color: #7d5be6;
        }
        
        .btn-outline:hover {
            background: #7d5be6;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(125, 91, 230, 0.3);
        }
        
        .perfil-stats {
            display: flex;
            gap: 30px;
        }
        
        .perfil-stats span {
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        .perfil-stats strong {
            color: #2c3e50;
            font-size: 1.2rem;
        }
        
        .perfil-tabs {
            display: flex;
            background: white;
            margin: 30px;
            border-radius: 15px;
            padding: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        
        .perfil-tabs button {
            flex: 1;
            padding: 15px 25px;
            border: none;
            background: transparent;
            color: #6c757d;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .perfil-tabs button.active {
            background: linear-gradient(135deg, #7d5be6 0%, #6b4dd6 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(125, 91, 230, 0.3);
        }
        
        .perfil-props {
            padding: 0 30px 30px;
        }
        
        .perfil-props h3 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .perfil-props h3:before {
            content: '';
            width: 4px;
            height: 30px;
            background: linear-gradient(135deg, #7d5be6 0%, #6b4dd6 100%);
            border-radius: 2px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #7d5be6 0%, #6b4dd6 100%);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 35px rgba(0,0,0,0.12);
        }
        
        .card h4 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .categoria {
            display: inline-block;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #7d5be6;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 10px;
            border: 1px solid #e9ecef;
        }
        
        .ubicacion {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .ubicacion:before {
            content: '\f3c5';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #7d5be6;
        }
        
        .card p:last-child {
            margin-bottom: 0;
            line-height: 1.6;
            color: #495057;
        }
        
        footer {
            text-align: center;
            padding: 20px;
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            margin-top: auto;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: 2;
            }
            
            .perfil-page {
                order: 1;
            }
            
            .perfil-header {
                flex-direction: column;
                text-align: center;
                margin: -30px 15px 0;
                padding: 40px 20px 30px;
            }
            
            .perfil-avatar {
                margin-top: -60px;
            }
            
            .perfil-stats {
                justify-content: center;
            }
            
            .perfil-tabs,
            .perfil-props {
                margin: 20px 15px;
            }
        }
    </style>
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
                <span><?= htmlspecialchars($usuario['nombre_usuario']) ?></span>
                <small style="opacity: 0.8;"><?= htmlspecialchars($usuario['rol']) ?></small>
            </a>
        </div>
        <nav class="menu">
            <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
            <a href="#" onclick="document.getElementById('tab-posts').click()"><i class="fas fa-briefcase"></i> Mis Propuestas</a>
            <a href="editar_perfil.php"><i class="fas fa-cog"></i> Configuración</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </nav>
        <footer>
            <i class="fas fa-briefcase"></i> job inc
        </footer>
    </aside>

    <!-- Main perfil -->
    <main class="perfil-page">
        <!-- Banner grande -->
        <div class="perfil-banner"></div>

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
                    <a href="editar_perfil.php" class="btn btn-outline">
                        <i class="fas fa-edit"></i>
                        Editar perfil
                    </a>
                </div>
                <div class="perfil-stats">
                    <span><strong><?= $posts_count ?></strong> Posts</span>
                    <span><strong><?= $fav_count ?></strong> Favoritos recibidos</span>
                    <span><strong><?= htmlspecialchars($usuario['rol']) ?></strong> Rol</span>
                </div>
            </div>
        </div>

        <!-- Tabs funcionales -->
        <nav class="perfil-tabs">
            <button id="tab-posts" class="active" type="button">
                <i class="fas fa-briefcase"></i> Mis Propuestas
            </button>
            <button id="tab-favs" type="button">
                <i class="fas fa-heart"></i> Mis Favoritos
            </button>
        </nav>

        <section id="posts-section" class="perfil-props">
            <h3><i class="fas fa-briefcase"></i> Propuestas publicadas</h3>
            <?php if ($props->num_rows > 0): ?>
                <?php while($p = $props->fetch_assoc()): ?>
                    <div class="card">
                        <h4><?= htmlspecialchars($p['propuesta']) ?></h4>
                        <p class="categoria"><?= htmlspecialchars($p['categoria']) ?></p>
                        <p class="ubicacion"><?= htmlspecialchars($p['localidad']) ?></p>
                        <p><?= htmlspecialchars($p['descripcion']) ?></p>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 0.8rem;">
                            <i class="fas fa-calendar"></i> Publicado: <?= date('d/m/Y', strtotime($p['fecha_creacion'] ?? 'now')) ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 40px;">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: #dee2e6; margin-bottom: 20px;"></i>
                    <h4 style="color: #6c757d;">No hay propuestas publicadas</h4>
                    <p style="color: #6c757d;">Aún no has creado ninguna propuesta laboral.</p>
                    <a href="crear.php" class="btn btn-outline" style="margin-top: 15px;">
                        <i class="fas fa-plus"></i> Crear primera propuesta
                    </a>
                </div>
            <?php endif; ?>
        </section>
        
        <section id="favs-section" class="perfil-props" style="display:none;">
            <h3><i class="fas fa-heart"></i> Favoritos guardados</h3>
            <?php if ($favs->num_rows > 0): ?>
                <?php while($f = $favs->fetch_assoc()): ?>
                    <div class="card">
                        <h4><?= htmlspecialchars($f['propuesta']) ?></h4>
                        <p class="categoria"><?= htmlspecialchars($f['categoria']) ?></p>
                        <p class="ubicacion"><?= htmlspecialchars($f['localidad']) ?></p>
                        <p><?= htmlspecialchars($f['descripcion']) ?></p>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                            <a href="favorito.php?id=<?= $f['id'] ?>" style="color: #dc3545; text-decoration: none;">
                                <i class="fas fa-heart-broken"></i> Quitar de favoritos
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 40px;">
                    <i class="fas fa-heart" style="font-size: 3rem; color: #dee2e6; margin-bottom: 20px;"></i>
                    <h4 style="color: #6c757d;">No hay favoritos guardados</h4>
                    <p style="color: #6c757d;">Aún no has marcado ninguna propuesta como favorita.</p>
                    <a href="index.php" class="btn btn-outline" style="margin-top: 15px;">
                        <i class="fas fa-search"></i> Explorar propuestas
                    </a>
                </div>
            <?php endif; ?>
        </section>
        <script>
        // Tabs funcionales con JS mejorado
        function switchTab(activeTabId, activeSectionId) {
            // Remover active de todos los tabs
            document.querySelectorAll('.perfil-tabs button').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Ocultar todas las secciones
            document.querySelectorAll('.perfil-props').forEach(section => {
                section.style.display = 'none';
            });
            
            // Activar tab y sección seleccionados
            document.getElementById(activeTabId).classList.add('active');
            document.getElementById(activeSectionId).style.display = 'block';
        }
        
        document.getElementById('tab-posts').onclick = function() {
            switchTab('tab-posts', 'posts-section');
        };
        
        document.getElementById('tab-favs').onclick = function() {
            switchTab('tab-favs', 'favs-section');
        };
        
        // Animación suave para las cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        </script>
    </main>
</div>
</body>
</html>