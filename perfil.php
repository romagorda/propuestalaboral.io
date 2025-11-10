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
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
            width: 100%;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
            background: #fff;
            overflow-x: hidden;
        }
        
        .sidebar {
            width: 280px;
            background: #fff;
            border-right: 1px solid #e5e7eb;
            padding: 30px 20px;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .profile {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%);
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(124,92,252,0.2);
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
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.3);
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .profile h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }
        
        .profile p {
            margin: 0;
            font-size: 13px;
            opacity: 0.9;
        }
        
        .menu {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .menu a {
            color: #6b7280;
            text-decoration: none;
            padding: 14px 18px;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            font-size: 15px;
        }
        
        .menu a:hover {
            background: #f3f4f6;
            color: #7C5CFC;
            transform: translateX(4px);
        }
        
        .menu a i {
            width: 20px;
            font-size: 16px;
        }
        
        .perfil-page {
            flex: 1;
            background: #f9fafb;
            overflow: hidden;
            max-width: calc(100% - 280px);
            min-width: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        .perfil-banner {
            height: 240px;
            background: linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%);
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .perfil-banner:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.08) 0%, transparent 50%);
        }
        
        .perfil-header {
            position: relative;
            background: white;
            margin: -60px 40px 0;
            border-radius: 24px;
            padding: 40px;
            padding-top: 60px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            display: flex;
            align-items: flex-start;
            gap: 30px;
            max-width: calc(100% - 80px);
            flex-shrink: 0;
        }
        
        .perfil-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 6px solid white;
            box-shadow: 0 12px 40px rgba(124,92,252,0.25);
            object-fit: cover;
            margin-top: -120px;
            flex-shrink: 0;
        }
        
        .perfil-meta {
            flex: 1;
            padding-top: 10px;
            min-width: 0;
            overflow-wrap: break-word;
        }
        
        .perfil-meta h1 {
            margin: 0 0 8px 0;
            font-size: 32px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
            word-break: break-word;
        }
        
        .perfil-handle {
            color: #7C5CFC;
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .perfil-handle i {
            font-size: 16px;
        }
        
        .perfil-actions {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 26px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            font-size: 15px;
            cursor: pointer;
        }
        
        .btn-outline {
            background: #f3f4f6;
            color: #374151;
        }
        
        .btn-outline:hover {
            background: linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124,92,252,0.3);
        }
        
        .perfil-stats {
            display: flex;
            gap: 40px;
        }
        
        .perfil-stats .stat {
            text-align: center;
        }
        
        .perfil-stats .stat strong {
            display: block;
            font-size: 28px;
            font-weight: 800;
            color: #7C5CFC;
            margin-bottom: 4px;
        }
        
        .perfil-stats .stat span {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }
        
        .perfil-tabs {
            display: flex;
            background: white;
            margin: 30px 40px 20px;
            border-radius: 16px;
            padding: 6px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            gap: 6px;
            flex-shrink: 0;
        }
        
        .perfil-tabs button {
            flex: 1;
            padding: 14px 24px;
            border: none;
            background: transparent;
            color: #6b7280;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
            cursor: pointer;
            font-size: 15px;
        }
        
        .perfil-tabs button:hover {
            background: #f3f4f6;
            color: #374151;
        }
        
        .perfil-tabs button.active {
            background: linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(124,92,252,0.35);
            transform: scale(1.02);
        }
        
        .perfil-props {
            padding: 0 40px 40px;
            overflow-y: auto;
            flex: 1;
            max-height: calc(100vh - 450px);
        }
        
        .perfil-props h3 {
            color: #111827;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .perfil-props h3 i {
            font-size: 22px;
            color: #7C5CFC;
        }
        
        .card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
            transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1);
            position: relative;
            overflow: hidden;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, #7C5CFC 0%, #9b6dff 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(124,92,252,0.18);
            border-color: #7C5CFC;
        }
        
        .card:hover:before {
            opacity: 1;
        }
        
        .card h4 {
            margin: 0 0 16px 0;
            color: #111827;
            font-size: 22px;
            font-weight: 700;
        }
        
        .categoria {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
            color: #7C5CFC;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .categoria i {
            font-size: 12px;
        }
        
        .ubicacion {
            color: #6b7280;
            font-size: 15px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .ubicacion i {
            color: #7C5CFC;
            font-size: 14px;
        }
        
        .card p:last-child {
            margin-bottom: 0;
            line-height: 1.7;
            color: #4b5563;
            font-size: 15px;
        }
        
        footer {
            text-align: center;
            padding: 30px;
            color: #9ca3af;
            font-size: 14px;
            margin-top: auto;
            background: white;
            border-top: 1px solid #e5e7eb;
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
                margin: -40px 20px 0;
                padding: 50px 24px 30px;
            }
            
            .perfil-avatar {
                margin-top: -80px;
            }
            
            .perfil-stats {
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .perfil-tabs,
            .perfil-props {
                margin: 20px 20px;
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
                <div>
                    <h3><?= htmlspecialchars($usuario['nombre_usuario']) ?></h3>
                    <p><?= htmlspecialchars($usuario['rol']) ?></p>
                </div>
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
                <div class="perfil-handle">
                    <i class="fas fa-at"></i>
                    <?= htmlspecialchars(strtolower($usuario['nombre_usuario'])) ?>
                </div>
                <div class="perfil-actions">
                    <a href="editar_perfil.php" class="btn btn-outline">
                        <i class="fas fa-edit"></i>
                        Editar perfil
                    </a>
                </div>
                <div class="perfil-stats">
                    <div class="stat">
                        <strong><?= $posts_count ?></strong>
                        <span>Propuestas</span>
                    </div>
                    <div class="stat">
                        <strong><?= $fav_count ?></strong>
                        <span>Favoritos</span>
                    </div>
                    <div class="stat">
                        <strong><?= htmlspecialchars($usuario['rol']) ?></strong>
                        <span>Rol</span>
                    </div>
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
                        <p class="categoria">
                            <i class="fas fa-tag"></i>
                            <?= htmlspecialchars($p['categoria']) ?>
                        </p>
                        <p class="ubicacion">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($p['localidad']) ?>
                        </p>
                        <p><?= htmlspecialchars($p['descripcion']) ?></p>
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px; display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-calendar" style="color: #7C5CFC;"></i>
                                Publicado: <?= date('d/m/Y', strtotime($p['fecha_creacion'] ?? 'now')) ?>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="button" onclick="openEditarModalPerfil(<?= $p['id'] ?>, '<?= htmlspecialchars($p['propuesta'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['localidad'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['categoria'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['horario'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($p['salario'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($p['descripcion'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['contacto'] ?? '', ENT_QUOTES) ?>')" style="color: #7C5CFC; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #f3f4f6; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                                    <i class="fas fa-pencil-alt"></i> Editar
                                </button>
                                <a href="eliminar.php?id=<?= $p['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar esta propuesta?')" style="color: #ef4444; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #fef2f2; border-radius: 8px; transition: all 0.3s ease;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 50px;">
                    <i class="fas fa-inbox" style="font-size: 64px; color: #e5e7eb; margin-bottom: 24px;"></i>
                    <h4 style="color: #6b7280; margin-bottom: 12px;">No hay propuestas publicadas</h4>
                    <p style="color: #9ca3af; margin-bottom: 24px;">Aún no has creado ninguna propuesta laboral.</p>
                    <a href="index.php" class="btn btn-outline">
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
                        <p class="categoria">
                            <i class="fas fa-tag"></i>
                            <?= htmlspecialchars($f['categoria']) ?>
                        </p>
                        <p class="ubicacion">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($f['localidad']) ?>
                        </p>
                        <p><?= htmlspecialchars($f['descripcion']) ?></p>
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                            <a href="favorito.php?id=<?= $f['id'] ?>" style="color: #ef4444; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease;">
                                <i class="fas fa-heart-broken"></i> Quitar de favoritos
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 50px;">
                    <i class="fas fa-heart" style="font-size: 64px; color: #e5e7eb; margin-bottom: 24px;"></i>
                    <h4 style="color: #6b7280; margin-bottom: 12px;">No hay favoritos guardados</h4>
                    <p style="color: #9ca3af; margin-bottom: 24px;">Aún no has marcado ninguna propuesta como favorita.</p>
                    <a href="index.php" class="btn btn-outline">
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

<!-- Modal de Editar Propuesta -->
<div id="editarPropuestaModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;justify-content:center;align-items:center;">
    <div class="modal-content" style="max-width:600px;width:90%;border-radius:20px;padding:0;overflow:hidden;background:#fff;max-height:90vh;overflow-y:auto;">
        <!-- Header con gradiente -->
        <div style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:32px 32px 24px 32px;position:relative;">
            <button class="modal-close" onclick="closeEditarModalPerfil()" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.2);border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;font-size:24px;color:#fff;transition:all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;</button>
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:8px;">
                <div style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:16px;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(10px);">
                    <i class="fas fa-pencil-alt" style="font-size:28px;color:#fff;"></i>
                </div>
                <div>
                    <h3 style="margin:0;font-size:28px;font-weight:700;color:#fff;text-shadow:0 2px 8px rgba(0,0,0,0.1);">Editar Propuesta</h3>
                    <p style="margin:4px 0 0 0;color:rgba(255,255,255,0.9);font-size:14px;">Actualiza los detalles de tu oferta laboral</p>
                </div>
            </div>
        </div>
        
        <!-- Formulario -->
        <form id="editarFormPerfil" style="padding:32px;">
            <input type="hidden" id="editar_id_perfil" name="id">
            
            <!-- Título -->
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                    <i class="fas fa-briefcase" style="color:#667eea;margin-right:8px;"></i>Título del puesto
                </label>
                <input type="text" id="editar_propuesta_perfil" name="propuesta" placeholder="ej: Desarrollador Full Stack" required
                    style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                    onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                    onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
            </div>
            
            <!-- Grid 2 columnas -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                        <i class="fas fa-map-marker-alt" style="color:#667eea;margin-right:8px;"></i>Ubicación
                    </label>
                    <input type="text" id="editar_localidad_perfil" name="localidad" placeholder="Ciudad, País"
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                        <i class="fas fa-tag" style="color:#667eea;margin-right:8px;"></i>Categoría
                    </label>
                    <select id="editar_categoria_perfil" name="categoria" required
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;background:#f9fafb;cursor:pointer;outline:none;transition:all 0.3s;"
                        onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
                        <option value="">Seleccionar...</option>
                        <option value="Tecnología">Tecnología</option>
                        <option value="Diseño">Diseño</option>
                        <option value="Finanzas">Finanzas</option>
                        <option value="Salud">Salud</option>
                        <option value="Educación">Educación</option>
                        <option value="Administración">Administración</option>
                        <option value="Ventas">Ventas</option>
                        <option value="Legal">Legal</option>
                        <option value="Recursos Humanos">Recursos Humanos</option>
                        <option value="Construcción">Construcción</option>
                    </select>
                </div>
            </div>
            
            <!-- Grid 2 columnas para horario y salario -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                        <i class="fas fa-clock" style="color:#667eea;margin-right:8px;"></i>Horario
                    </label>
                    <input type="text" id="editar_horario_perfil" name="horario" placeholder="ej: 9 a 17hs" required
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                        <i class="fas fa-dollar-sign" style="color:#667eea;margin-right:8px;"></i>Salario
                    </label>
                    <input type="text" id="editar_salario_perfil" name="salario" placeholder="ej: $50k - $70k o 'A negociar'"
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
                </div>
            </div>
            
            <!-- Descripción -->
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                    <i class="fas fa-align-left" style="color:#667eea;margin-right:8px;"></i>Descripción
                </label>
                <textarea id="editar_descripcion_perfil" name="descripcion" placeholder="Describe el puesto, requisitos y beneficios..." rows="4"
                    style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;resize:vertical;font-family:inherit;"
                    onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                    onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'"></textarea>
            </div>
            
            <!-- Contacto -->
            <div style="margin-bottom:28px;">
                <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                    <i class="fas fa-envelope" style="color:#667eea;margin-right:8px;"></i>Contacto
                </label>
                <input type="text" id="editar_contacto_perfil" name="contacto" placeholder="email@empresa.com"
                    style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                    onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                    onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
            </div>
            
            <!-- Botones -->
            <div style="display:flex;gap:12px;padding-top:20px;border-top:2px solid #f3f4f6;">
                <button type="button" onclick="closeEditarModalPerfil()" 
                    style="flex:1;background:#f3f4f6;color:#6b7280;padding:14px 24px;border:none;border-radius:12px;font-weight:600;font-size:15px;cursor:pointer;transition:all 0.3s;"
                    onmouseover="this.style.background='#e5e7eb';this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.background='#f3f4f6';this.style.transform='translateY(0)'">
                    <i class="fas fa-times" style="margin-right:8px;"></i>Cancelar
                </button>
                <button type="button" onclick="submitEditarFormPerfil()" 
                    style="flex:1;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);color:#fff;padding:14px 24px;border:none;border-radius:12px;font-weight:600;font-size:15px;cursor:pointer;transition:all 0.3s;box-shadow:0 4px 12px rgba(102,126,234,0.4);"
                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(102,126,234,0.5)'"
                    onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(102,126,234,0.4)'">
                    <i class="fas fa-check" style="margin-right:8px;"></i>Actualizar Propuesta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditarModalPerfil(id, titulo, localidad, categoria, horario, salario, descripcion, contacto) {
    document.getElementById('editar_id_perfil').value = id;
    document.getElementById('editar_propuesta_perfil').value = titulo;
    document.getElementById('editar_localidad_perfil').value = localidad;
    document.getElementById('editar_categoria_perfil').value = categoria;
    document.getElementById('editar_horario_perfil').value = horario;
    document.getElementById('editar_salario_perfil').value = salario;
    document.getElementById('editar_descripcion_perfil').value = descripcion;
    document.getElementById('editar_contacto_perfil').value = contacto;
    
    document.getElementById('editarPropuestaModal').style.display = 'flex';
}

function closeEditarModalPerfil() {
    document.getElementById('editarPropuestaModal').style.display = 'none';
}

function submitEditarFormPerfil() {
    const formData = new FormData(document.getElementById('editarFormPerfil'));
    
    fetch('editar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Propuesta actualizada correctamente');
            closeEditarModalPerfil();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo actualizar la propuesta'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la propuesta');
    });
}

// Cerrar modal al hacer clic fuera
document.getElementById('editarPropuestaModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditarModalPerfil();
    }
});
</script>

</body>
</html>