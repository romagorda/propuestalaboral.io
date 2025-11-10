<?php


session_start();
include 'conexion.php';

// Capturamos mensaje de sesión si existe
$flash_msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']); // Eliminamos para que no se repita



$usuario_id = $_SESSION['usuario_id'] ?? null;
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Invitado';
$rol = $_SESSION['rol'] ?? 'usuario';

// Obtener avatar del usuario logueado
$avatar_url = 'avatars/default.png';
if ($usuario_id) {
    $stmt_avatar = $conn->prepare("SELECT avatar_url FROM login WHERE id = ?");
    $stmt_avatar->bind_param("i", $usuario_id);
    $stmt_avatar->execute();
    $result_avatar = $stmt_avatar->get_result();
    if ($row_avatar = $result_avatar->fetch_assoc()) {
        $avatar_url = $row_avatar['avatar_url'] ?: 'avatars/default.png';
    }
    $stmt_avatar->close();
}

// Procesar búsqueda y categoría
$busqueda = $_GET['busqueda'] ?? '';
$categoria = $_GET['categoria'] ?? '';

// Consulta que incluye tags y el nombre del usuario que creó la propuesta
$query = "SELECT DISTINCT p.*, l.nombre_usuario AS creador, l.id AS creador_id, l.avatar_url AS creador_avatar 
          FROM propuestas p 
          LEFT JOIN login l ON p.usuario_id = l.id 
          LEFT JOIN propuesta_tag pt ON p.id = pt.propuesta_id 
          LEFT JOIN tags t ON pt.tag_id = t.id 
          WHERE p.activo = 1";

$params = [];
$types = '';

if ($busqueda) {
    $query .= " AND (p.propuesta LIKE ? OR p.descripcion LIKE ? OR p.localidad LIKE ? OR p.contacto LIKE ? OR t.nombre LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params = array_merge($params, [$busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param]);
    $types .= 'sssss';
}

if ($categoria) {
    $query .= " AND p.categoria = ?";
    $params[] = $categoria;
    $types .= 's';
}

$query .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Propuestas Laborales</title>
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<!-- Google Fonts: Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
html, body { margin: 0; padding: 0; height: 100vh; overflow: hidden; box-sizing: border-box; }
.top-bar { flex-shrink: 0; }
.container { height: calc(100vh - 100px); display: flex; overflow: hidden; }
.sidebar { flex-shrink: 0; height: 100%; overflow-y: auto; }
.main { flex: 1; height: 100%; display: flex; flex-direction: column; overflow: hidden; }
.header { flex-shrink: 0; }
.favoritos { flex-shrink: 0; height: 100%; overflow-y: auto; width: 300px; min-width: 300px; }

/* Scrollbar personalizado */
.propuestas::-webkit-scrollbar { width: 8px; }
.propuestas::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
.propuestas::-webkit-scrollbar-thumb { background: #7d5be6; border-radius: 4px; }
.propuestas::-webkit-scrollbar-thumb:hover { background: #6b4dd6; }
.propuestas { scrollbar-width: thin; scrollbar-color: #7d5be6 #f1f1f1; flex: 1; overflow-y: auto; min-height: 0; }
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideDown {
    from {
        transform: translateY(-30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal.show {
    display: flex;
}

.modal-content {
    background: #fff;
    padding: 24px;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    position: relative;
    box-shadow: 0 4px 24px rgba(0,0,0,0.15);
    animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.preview-content {
    background: #f7f2e9;
    padding: 16px;
    border-radius: 8px;
    margin: 16px 0;
}

.modal-buttons {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

.modal-buttons button {
    flex: 1;
    padding: 10px;
}

/* Estilos para el formulario dentro del modal */
#propuestaForm input,
#propuestaForm textarea,
#propuestaForm select {
    width: 100%;
    padding: 8px;
    margin: 8px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
}

#propuestaForm textarea {
    min-height: 100px;
    resize: vertical;
}
</style>
</head>
<body>
<!-- Barra superior de búsqueda y categorías -->
<?php
$categorias_result = $conn->query("SELECT DISTINCT categoria FROM propuestas");
$categorias = [];
while ($row = $categorias_result->fetch_assoc()) {
    $categorias[] = $row['categoria'];
}
?>

<!-- Barra de búsqueda moderna -->
<div style="background:transparent;padding:10px 0;position:sticky;top:0;z-index:100;">
    <div style="max-width:1400px;margin:0 auto;padding:0 20px;">
        <form method="get" style="display:flex;align-items:center;gap:12px;max-width:900px;margin:0 auto;">
            <!-- Input de búsqueda -->
            <div style="flex:1;position:relative;">
                <i class="fas fa-search" style="position:absolute;left:20px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:16px;"></i>
                <input type="text" name="busqueda" placeholder="Buscar por título, ubicación o categoría..." 
                    style="width:100%;padding:16px 20px 16px 52px;border:none;border-radius:50px;font-size:15px;outline:none;transition:all 0.3s;background:#fff;box-shadow:0 4px 16px rgba(0,0,0,0.08);"
                    onfocus="this.style.boxShadow='0 8px 24px rgba(124,92,252,0.15)';this.style.transform='translateY(-2px)'"
                    onblur="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)';this.style.transform='translateY(0)'">
            </div>
            
            <!-- Select de categoría -->
            <select name="categoria" 
                style="padding:16px 24px;border:none;border-radius:50px;font-size:15px;background:#fff;cursor:pointer;outline:none;min-width:180px;transition:all 0.3s;box-shadow:0 4px 16px rgba(0,0,0,0.08);appearance:none;background-image:url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27currentColor%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e');background-repeat:no-repeat;background-position:right 16px center;background-size:16px;padding-right:48px;"
                onfocus="this.style.boxShadow='0 8px 24px rgba(124,92,252,0.15)';this.style.transform='translateY(-2px)'"
                onblur="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)';this.style.transform='translateY(0)'">
                <option value="">Categorías</option>
                <?php
                $extra_categorias = ['Tecnología', 'Diseño', 'Finanzas', 'Salud', 'Educación', 'Administración', 'Ventas', 'Legal', 'Recursos Humanos', 'Construcción'];
                $todas_categorias = array_unique(array_filter(array_merge($extra_categorias, $categorias)));
                foreach ($todas_categorias as $cat):
                    if (trim($cat) !== ''):
                ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($_GET['categoria']) && $_GET['categoria'] === $cat) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endif; endforeach; ?>
            </select>
            
            <!-- Botón de búsqueda -->
            <button type="submit" 
                style="background:#7C5CFC;color:#fff;padding:16px 32px;border:none;border-radius:50px;font-weight:600;font-size:15px;cursor:pointer;transition:all 0.3s;display:flex;align-items:center;gap:8px;white-space:nowrap;box-shadow:0 4px 16px rgba(124,92,252,0.25);"
                onmouseover="this.style.background='#6b4dd6';this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(124,92,252,0.35)'"
                onmouseout="this.style.background='#7C5CFC';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(124,92,252,0.25)'">
                <i class="fas fa-search"></i>
                Buscar
            </button>
            
            <!-- Botón limpiar (si hay búsqueda activa) -->
            <?php if(isset($_GET['busqueda']) || isset($_GET['categoria'])): ?>
            <a href="index.php" 
                style="padding:16px;border:none;border-radius:50%;color:#ef4444;text-decoration:none;font-weight:600;font-size:16px;transition:all 0.3s;display:flex;align-items:center;justify-content:center;background:#fff;box-shadow:0 4px 16px rgba(0,0,0,0.08);width:52px;height:52px;"
                onmouseover="this.style.background='#fef2f2';this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(239,68,68,0.2)'"
                onmouseout="this.style.background='#fff';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'">
                <i class="fas fa-times"></i>
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="container">
<!-- Sidebar izquierdo -->
<aside class="sidebar">
    <div class="sidebar-top">
        <div class="logo">
            <i class="fas fa-briefcase"></i>
            <span>PropuestaLaboral</span>
        </div>
        <div class="profile">
            <a href="#" class="profile-link">
                <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Perfil">
                <span><?= htmlspecialchars($nombre_usuario) ?></span>
            </a>
        </div>
        <nav class="menu">
            <a href="index.php" class="menu-item active">
                <i class="fas fa-th-large"></i>
                <span>Inicio</span>
            </a>
            <a href="perfil.php" class="menu-item">
                <i class="fas fa-user"></i>
                <span>Mi Perfil</span>
            </a>
            <?php if ($rol === 'admin'): ?>
            <a href="reportes.php" class="menu-item">
                <i class="fas fa-flag"></i>
                <span>Reportes</span>
            </a>
            <?php endif; ?>
            <a href="configuracion.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a>
        </nav>
    </div>
    <div class="sidebar-bottom">
        <a href="ayuda.php" class="menu-item">
            <i class="fas fa-question-circle"></i>
            <span>Ayuda</span>
        </a>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar sesión</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="menu-item">
                <i class="fas fa-sign-in-alt"></i>
                <span>Iniciar sesión</span>
            </a>
        <?php endif; ?>
    </div>
</aside>

<!-- Main content -->
<main class="main">
<header class="header">
    <!-- header vacío: el botón de crear ahora está dentro del hero -->
</header>
<!-- Dashboard hero y contenido principal -->
<?php if($flash_msg): ?>
<div class="flash-message" style="padding:10px; margin:10px 0; border-radius:8px; background:#e8ffe8; color:#183;">
    <?= htmlspecialchars($flash_msg) ?>
</div>
<?php endif; ?>

<!-- Hero banner -->
<section class="hero">
    <div class="hero-left">
        <small>ONLINE</small>
        <h1>Encuentra y publica oportunidades laborales</h1>
        <p class="hero-sub">Explora propuestas, guarda tus favoritas y conecta con empleadores.</p>
        <div class="hero-actions">
            <button onclick="openCrearModal()" class="btn btn-primary" style="background:#fff;color:#7C5CFC;padding:14px 32px;font-size:16px;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,0.15);border:none;border-radius:12px;cursor:pointer;transition:all 0.3s ease;text-transform:uppercase;letter-spacing:0.5px;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 32px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.15)'">
                <i class="fas fa-plus-circle" style="margin-right:8px;"></i>CREAR PROPUESTA
            </button>
        </div>
    </div>
    <div class="hero-right">
        <img src="img/celu sinfondo.png" alt="Mobile app preview" class="hero-character" />
    </div>
</section>

<!-- Contenido dashboard: grid de tarjetas -->
<section class="dashboard-grid">
    <div class="grid-main">
        <h2>Propuestas recientes</h2>
        <div class="cards-grid">
<?php while($p = $result->fetch_assoc()):
    $fav_stmt = $conn->prepare("SELECT * FROM favoritos WHERE usuario_id=? AND propuesta_id=?");
    $fav_stmt->bind_param("ii", $usuario_id, $p['id']);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    $es_favorito = $fav_result->num_rows > 0;
    // salary and date handling (optional fields)
    $salario = '';
    if (isset($p['salario']) && trim($p['salario']) !== '') {
        $salario = htmlspecialchars($p['salario']);
    }
    $fecha_src = $p['fecha_creacion'] ?? ($p['created_at'] ?? '');
    $when_text = '';
    if (!empty($fecha_src)) {
        $ts = strtotime($fecha_src);
        $diff = time() - $ts;
        if ($diff < 60) {
            $when_text = 'hace ' . $diff . 's';
        } elseif ($diff < 3600) {
            $when_text = 'hace ' . floor($diff/60) . 'm';
        } elseif ($diff < 86400) {
            $when_text = 'hace ' . floor($diff/3600) . 'h';
        } else {
            $when_text = 'hace ' . floor($diff/86400) . 'd';
        }
    }
?>
<div class="card"
    data-id="<?= $p['id'] ?>"
    data-title="<?= htmlspecialchars($p['propuesta'], ENT_QUOTES) ?>"
    data-localidad="<?= htmlspecialchars($p['localidad'] ?? '', ENT_QUOTES) ?>"
    data-horario="<?= htmlspecialchars($p['horario'] ?? '', ENT_QUOTES) ?>"
    data-descripcion="<?= htmlspecialchars($p['descripcion'], ENT_QUOTES) ?>"
    data-contacto="<?= htmlspecialchars($p['contacto'] ?? '', ENT_QUOTES) ?>"
    data-creador="<?= htmlspecialchars($p['creador'] ?? '', ENT_QUOTES) ?>"
    data-avatar="<?= htmlspecialchars($p['creador_avatar'] ?? '', ENT_QUOTES) ?>"
    data-categoria="<?= htmlspecialchars($p['categoria'] ?? '', ENT_QUOTES) ?>"
    data-salario="<?= $salario ?>"
    data-fecha="<?= htmlspecialchars($when_text, ENT_QUOTES) ?>">
    
    <!-- Logo circular -->
    <img src="<?= !empty($p['creador_avatar']) ? htmlspecialchars($p['creador_avatar']) : 'avatars/default.png' ?>" alt="Company logo" class="card-logo" />
    
    <!-- Contenido principal -->
    <div class="card-body">
        <!-- Header: título, empresa, ubicación -->
        <div class="card-header-row">
            <div class="card-title-company">
                <h3 class="card-title"><?= htmlspecialchars($p['propuesta']) ?></h3>
                <p class="card-company">@<?= htmlspecialchars($p['creador']) ?></p>
            </div>
            <div class="card-location">
                <i class="fas fa-map-marker-alt"></i>
                <span><?= htmlspecialchars($p['localidad'] ?? 'Remote') ?></span>
            </div>
        </div>
        
        <!-- Badges -->
        <div class="card-badges">
            <?php
            // Determinar badge según horario o categoría
            $horario = strtolower($p['horario'] ?? '');
            if(strpos($horario, 'full') !== false || strpos($horario, 'completo') !== false):
            ?>
                <span class="badge full-time">Full-time</span>
            <?php elseif(strpos($horario, 'part') !== false || strpos($horario, 'medio') !== false): ?>
                <span class="badge part-time">Part-time</span>
            <?php else: ?>
                <span class="badge full-time">Full-time</span>
            <?php endif; ?>
            
            <?php
            // Badge de nivel (ejemplo: Entry, Mid-level)
            $categoria = strtolower($p['categoria'] ?? '');
            if(strpos($categoria, 'junior') !== false || strpos($categoria, 'entry') !== false):
            ?>
                <span class="badge entry">Entry</span>
            <?php else: ?>
                <span class="badge mid-level">Mid Level</span>
            <?php endif; ?>
        </div>
        
        <!-- Descripción en bullet points -->
        <ul class="card-description">
            <?php 
            $desc = $p['descripcion'] ?? '';
            // Dividir descripción en puntos (si contiene saltos de línea o puntos)
            $bullets = preg_split('/[\r\n]+|(?<=\.)\s+/', $desc, 2, PREG_SPLIT_NO_EMPTY);
            foreach(array_slice($bullets, 0, 2) as $bullet):
                if(trim($bullet)):
            ?>
                <li><i class="fas fa-check-circle" style="color:#7C5CFC;margin-right:8px;"></i><?= htmlspecialchars(trim($bullet)) ?></li>
            <?php 
                endif;
            endforeach; 
            ?>
        </ul>
        
        <!-- Botones secundarios -->
        <div class="card-actions-secondary">
            <?php if($rol === 'admin' || $p['usuario_id'] == $usuario_id): ?>
            <button type="button" onclick="openEditarModal(<?= $p['id'] ?>)" class="card-icon-btn-small btn-edit" title="Editar">
                <i class="fas fa-pencil-alt" style="color:#9ca3af;"></i>
            </button>
            <button type="button" onclick="if(confirm('¿Eliminar esta propuesta?')) window.location.href='eliminar.php?id=<?= $p['id'] ?>'" class="card-icon-btn-small btn-delete" title="Eliminar">
                <i class="fas fa-trash" style="color:#9ca3af;"></i>
            </button>
            <?php endif; ?>
            <?php if($p['usuario_id'] != $usuario_id): ?>
            <button type="button" onclick="openReportModal(<?= $p['id'] ?>)" class="card-icon-btn-small btn-report" title="Reportar">
                <i class="fas fa-flag" style="color:#9ca3af;"></i>
            </button>
            <?php endif; ?>
            <button type="button" onclick="openFavoritoModal(<?= $p['id'] ?>, <?= $es_favorito ? 'true' : 'false' ?>)" class="card-icon-btn-small btn-favorite <?= $es_favorito ? 'active' : '' ?>" title="Favorito">
                <i class="<?= $es_favorito ? 'fas' : 'far' ?> fa-bookmark" style="color:<?= $es_favorito ? '#f59e0b' : '#9ca3af' ?>;"></i>
            </button>
        </div>
    </div>
    
    <!-- Botón Ver más -->
    <button type="button" class="card-apply-btn ver-mas-card">Ver más</button>
</div>
<?php endwhile; ?>
        </div> <!-- .cards-grid -->
    </div> <!-- .grid-main -->

</section> <!-- .dashboard-grid -->
</main>

<!-- Sidebar derecho favoritos -->
<aside class="favoritos">
    <h3>Favoritos</h3>
    <?php
    $fav_stmt = $conn->prepare("SELECT p.*, l.nombre_usuario AS creador, l.avatar_url AS creador_avatar FROM propuestas p INNER JOIN favoritos f ON p.id=f.propuesta_id LEFT JOIN login l ON p.usuario_id = l.id WHERE f.usuario_id=?");
    $fav_stmt->bind_param("i", $usuario_id);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    while($f = $fav_result->fetch_assoc()):
        // Calcular fecha relativa
        $fecha_src = $f['fecha_creacion'] ?? '';
        $when_text = '';
        if (!empty($fecha_src)) {
            $ts = strtotime($fecha_src);
            $diff = time() - $ts;
            if ($diff < 60) {
                $when_text = 'hace ' . $diff . 's';
            } elseif ($diff < 3600) {
                $when_text = 'hace ' . floor($diff/60) . 'm';
            } elseif ($diff < 86400) {
                $when_text = 'hace ' . floor($diff/3600) . 'h';
            } else {
                $when_text = 'hace ' . floor($diff/86400) . 'd';
            }
        }
    ?>
    <div class="card small"
        data-id="<?= $f['id'] ?>"
        data-title="<?= htmlspecialchars($f['propuesta'], ENT_QUOTES) ?>"
        data-localidad="<?= htmlspecialchars($f['localidad'] ?? '', ENT_QUOTES) ?>"
        data-horario="<?= htmlspecialchars($f['horario'] ?? '', ENT_QUOTES) ?>"
        data-descripcion="<?= htmlspecialchars($f['descripcion'], ENT_QUOTES) ?>"
        data-contacto="<?= htmlspecialchars($f['contacto'] ?? '', ENT_QUOTES) ?>"
        data-creador="<?= htmlspecialchars($f['creador'] ?? '', ENT_QUOTES) ?>"
        data-avatar="<?= htmlspecialchars($f['creador_avatar'] ?? '', ENT_QUOTES) ?>"
        data-categoria="<?= htmlspecialchars($f['categoria'] ?? '', ENT_QUOTES) ?>"
        data-salario="<?= htmlspecialchars($f['salario'] ?? '', ENT_QUOTES) ?>"
        data-fecha="<?= htmlspecialchars($when_text, ENT_QUOTES) ?>">
        <div style="display:flex;flex-direction:column;gap:8px;">
            <h4 style="margin:0;font-size:14px;font-weight:600;color:#333;"><?= htmlspecialchars($f['propuesta']) ?></h4>
            <p style="margin:0;font-size:12px;color:#666;display:flex;align-items:center;gap:4px;">
                <i class="fas fa-map-marker-alt" style="font-size:10px;"></i>
                <?= htmlspecialchars($f['localidad'] ?? 'No especificado') ?>
            </p>
            <a href="#" onclick="openFavoritoDetail(<?= $f['id'] ?>); return false;" style="font-size:12px;color:#7C5CFC;text-decoration:none;font-weight:600;">Ver más</a>
        </div>
    </div>
    <?php endwhile; ?>
    
</aside>
</div>

<!-- Add these modals before closing body tag -->
<!-- Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Reportar Propuesta</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="reportForm" method="POST" action="reportar.php">
            <input type="hidden" name="propuesta_id" id="reportPropuestaId">
            <textarea name="motivo" placeholder="Describe el motivo del reporte" 
                      required style="width:100%;min-height:100px;margin:12px 0;padding:8px;border-radius:8px;"></textarea>
            <button type="submit" class="btn" style="background:#e74c3c;width:100%;">Enviar Reporte</button>
        </form>
    </div>
</div>

<!-- Favorite Modal -->
<div id="favoritoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar Acción</h3>
            <button class="modal-close">&times;</button>
        </div>
        <p id="favoritoMessage"></p>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button class="btn" id="confirmFavorito" style="flex:1">Confirmar</button>
            <button class="btn" onclick="closeModal('favoritoModal')" 
                    style="flex:1;background:#666;">Cancelar</button>
        </div>
    </div>
</div>

<!-- Crear Propuesta Modal -->
<div id="crearPropuestaModal" class="modal">
    <div class="modal-content" style="max-width:600px;border-radius:20px;padding:0;overflow:hidden;">
        <!-- Header con gradiente -->
        <div style="background:linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%);padding:32px 32px 24px 32px;position:relative;">
            <button class="modal-close" onclick="closeCrearModal()" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.2);border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;font-size:24px;color:#fff;transition:all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;</button>
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:8px;">
                <div style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:16px;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(10px);">
                    <i class="fas fa-plus-circle" style="font-size:28px;color:#fff;"></i>
                </div>
                <div>
                    <h3 style="margin:0;font-size:28px;font-weight:700;color:#fff;text-shadow:0 2px 8px rgba(0,0,0,0.1);">Nueva Propuesta</h3>
                    <p style="margin:4px 0 0 0;color:rgba(255,255,255,0.9);font-size:14px;">Publica una oportunidad laboral increíble</p>
                </div>
            </div>
        </div>
        
        <!-- Formulario -->
        <form id="propuestaForm" method="POST" action="procesar_propuesta.php" style="padding:32px;">
            <!-- Título -->
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                    <i class="fas fa-briefcase" style="color:#7C5CFC;margin-right:8px;"></i>Título del puesto
                </label>
                <input type="text" id="propuesta" name="propuesta" placeholder="ej: Desarrollador Full Stack" required
                    style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                    onfocus="this.style.borderColor='#7C5CFC';this.style.background='#fff'"
                    onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
            </div>
            
            <!-- Grid 2 columnas -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                        <i class="fas fa-map-marker-alt" style="color:#7C5CFC;margin-right:8px;"></i>Ubicación
                    </label>
                    <input type="text" id="localidad" name="localidad" placeholder="Ciudad, País"
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#7C5CFC';this.style.background='#fff'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                        <i class="fas fa-tag" style="color:#7C5CFC;margin-right:8px;"></i>Categoría
                    </label>
                    <select id="categoria" name="categoria" required
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;background:#f9fafb;cursor:pointer;outline:none;transition:all 0.3s;"
                        onfocus="this.style.borderColor='#7C5CFC';this.style.background='#fff'"
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
                        <i class="fas fa-clock" style="color:#7C5CFC;margin-right:8px;"></i>Horario
                    </label>
                    <input type="text" id="horario" name="horario" placeholder="ej: 9 a 17hs" required
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#7C5CFC';this.style.background='#fff'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                        <i class="fas fa-dollar-sign" style="color:#7C5CFC;margin-right:8px;"></i>Salario
                    </label>
                    <div style="display:flex;gap:8px;margin-bottom:12px;">
                        <label style="flex:1;cursor:pointer;background:#f3f4f6;padding:10px 16px;border-radius:10px;text-align:center;transition:all 0.3s;border:2px solid transparent;display:flex;align-items:center;justify-content:center;gap:6px;" 
                            onmouseover="if(!this.querySelector('input').checked) this.style.background='#e5e7eb'" 
                            onmouseout="if(!this.querySelector('input').checked) this.style.background='#f3f4f6'">
                            <input type="radio" name="salario_tipo" value="negociable" checked onchange="toggleSalarioInput();updateSalarioRadioStyles()" style="display:none;">
                            <i class="fas fa-handshake" style="font-size:14px;color:#7C5CFC;"></i>
                            <span style="font-size:13px;color:#374151;font-weight:600;">A negociar</span>
                        </label>
                        <label style="flex:1;cursor:pointer;background:#f3f4f6;padding:10px 16px;border-radius:10px;text-align:center;transition:all 0.3s;border:2px solid transparent;display:flex;align-items:center;justify-content:center;gap:6px;" 
                            onmouseover="if(!this.querySelector('input').checked) this.style.background='#e5e7eb'" 
                            onmouseout="if(!this.querySelector('input').checked) this.style.background='#f3f4f6'">
                            <input type="radio" name="salario_tipo" value="especifico" onchange="toggleSalarioInput();updateSalarioRadioStyles()" style="display:none;">
                            <i class="fas fa-dollar-sign" style="font-size:14px;color:#7C5CFC;"></i>
                            <span style="font-size:13px;color:#374151;font-weight:600;">Especificar</span>
                        </label>
                    </div>
                    <input type="text" id="salario" name="salario" placeholder="ej: $50k - $70k" disabled
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f3f4f6;color:#9ca3af;"
                        onfocus="this.style.borderColor='#7C5CFC';this.style.background='#fff';this.style.color='#374151'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#fff';this.style.color='#374151'">
                </div>
            </div>
            
            <!-- Descripción -->
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                    <i class="fas fa-align-left" style="color:#7C5CFC;margin-right:8px;"></i>Descripción
                </label>
                <textarea id="descripcion" name="descripcion" placeholder="Describe el puesto, requisitos y beneficios..." rows="4"
                    style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;resize:vertical;font-family:inherit;"
                    onfocus="this.style.borderColor='#7C5CFC';this.style.background='#fff'"
                    onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'"></textarea>
            </div>
            
            <!-- Contacto -->
            <div style="margin-bottom:28px;">
                <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                    <i class="fas fa-envelope" style="color:#7C5CFC;margin-right:8px;"></i>Contacto
                </label>
                <input type="text" id="contacto" name="contacto" placeholder="email@empresa.com"
                    style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                    onfocus="this.style.borderColor='#7C5CFC';this.style.background='#fff'"
                    onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
            </div>
            
            <!-- Botones -->
            <div style="display:flex;gap:12px;padding-top:20px;border-top:2px solid #f3f4f6;">
                <button type="button" onclick="closeCrearModal()" 
                    style="flex:1;background:#f3f4f6;color:#6b7280;padding:14px 24px;border:none;border-radius:12px;font-weight:600;font-size:15px;cursor:pointer;transition:all 0.3s;"
                    onmouseover="this.style.background='#e5e7eb';this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.background='#f3f4f6';this.style.transform='translateY(0)'">
                    <i class="fas fa-times" style="margin-right:8px;"></i>Cancelar
                </button>
                <button type="button" onclick="showPreview()" 
                    style="flex:1;background:linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%);color:#fff;padding:14px 24px;border:none;border-radius:12px;font-weight:600;font-size:15px;cursor:pointer;transition:all 0.3s;box-shadow:0 4px 12px rgba(124,92,252,0.4);"
                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(124,92,252,0.5)'"
                    onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(124,92,252,0.4)'">
                    <i class="fas fa-eye" style="margin-right:8px;"></i>Vista Previa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Editar Propuesta -->
<div id="editarPropuestaModal" class="modal">
    <div class="modal-content" style="max-width:600px;border-radius:20px;padding:0;overflow:hidden;">
        <!-- Header con gradiente -->
        <div style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:32px 32px 24px 32px;position:relative;">
            <button class="modal-close" onclick="closeEditarModal()" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.2);border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;font-size:24px;color:#fff;transition:all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;</button>
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
        <form id="editarForm" style="padding:32px;">
            <input type="hidden" id="editar_id" name="id">
            
            <!-- Título -->
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                    <i class="fas fa-briefcase" style="color:#667eea;margin-right:8px;"></i>Título del puesto
                </label>
                <input type="text" id="editar_propuesta" name="propuesta" placeholder="ej: Desarrollador Full Stack" required
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
                    <input type="text" id="editar_localidad" name="localidad" placeholder="Ciudad, País"
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                        <i class="fas fa-tag" style="color:#667eea;margin-right:8px;"></i>Categoría
                    </label>
                    <select id="editar_categoria" name="categoria" required
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
                    <input type="text" id="editar_horario" name="horario" placeholder="ej: 9 a 17hs" required
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                        <i class="fas fa-dollar-sign" style="color:#667eea;margin-right:8px;"></i>Salario
                    </label>
                    <div style="display:flex;gap:8px;margin-bottom:12px;">
                        <label style="flex:1;cursor:pointer;background:#f3f4f6;padding:10px 16px;border-radius:10px;text-align:center;transition:all 0.3s;border:2px solid transparent;display:flex;align-items:center;justify-content:center;gap:6px;" 
                            onmouseover="if(!this.querySelector('input').checked) this.style.background='#e5e7eb'" 
                            onmouseout="if(!this.querySelector('input').checked) this.style.background='#f3f4f6'">
                            <input type="radio" name="editar_salario_tipo" value="negociable" checked onchange="toggleEditarSalarioInput();updateEditarSalarioRadioStyles()" style="display:none;">
                            <i class="fas fa-handshake" style="font-size:14px;color:#667eea;"></i>
                            <span style="font-size:13px;color:#374151;font-weight:600;">A negociar</span>
                        </label>
                        <label style="flex:1;cursor:pointer;background:#f3f4f6;padding:10px 16px;border-radius:10px;text-align:center;transition:all 0.3s;border:2px solid transparent;display:flex;align-items:center;justify-content:center;gap:6px;" 
                            onmouseover="if(!this.querySelector('input').checked) this.style.background='#e5e7eb'" 
                            onmouseout="if(!this.querySelector('input').checked) this.style.background='#f3f4f6'">
                            <input type="radio" name="editar_salario_tipo" value="especifico" onchange="toggleEditarSalarioInput();updateEditarSalarioRadioStyles()" style="display:none;">
                            <i class="fas fa-dollar-sign" style="font-size:14px;color:#667eea;"></i>
                            <span style="font-size:13px;color:#374151;font-weight:600;">Especificar</span>
                        </label>
                    </div>
                    <input type="text" id="editar_salario" name="salario" placeholder="ej: $50k - $70k" disabled
                        style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f3f4f6;color:#9ca3af;"
                        onfocus="this.style.borderColor='#667eea';this.style.background='#fff';this.style.color='#374151'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#fff';this.style.color='#374151'">
                </div>
            </div>
            
            <!-- Descripción -->
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                    <i class="fas fa-align-left" style="color:#667eea;margin-right:8px;"></i>Descripción
                </label>
                <textarea id="editar_descripcion" name="descripcion" placeholder="Describe el puesto, requisitos y beneficios..." rows="4"
                    style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;resize:vertical;font-family:inherit;"
                    onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                    onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'"></textarea>
            </div>
            
            <!-- Contacto -->
            <div style="margin-bottom:28px;">
                <label style="display:block;margin-bottom:8px;color:#374151;font-weight:600;font-size:14px;">
                    <i class="fas fa-envelope" style="color:#667eea;margin-right:8px;"></i>Contacto
                </label>
                <input type="text" id="editar_contacto" name="contacto" placeholder="email@empresa.com"
                    style="width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;transition:all 0.3s;background:#f9fafb;"
                    onfocus="this.style.borderColor='#667eea';this.style.background='#fff'"
                    onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">
            </div>
            
            <!-- Botones -->
            <div style="display:flex;gap:12px;padding-top:20px;border-top:2px solid #f3f4f6;">
                <button type="button" onclick="closeEditarModal()" 
                    style="flex:1;background:#f3f4f6;color:#6b7280;padding:14px 24px;border:none;border-radius:12px;font-weight:600;font-size:15px;cursor:pointer;transition:all 0.3s;"
                    onmouseover="this.style.background='#e5e7eb';this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.background='#f3f4f6';this.style.transform='translateY(0)'">
                    <i class="fas fa-times" style="margin-right:8px;"></i>Cancelar
                </button>
                <button type="button" onclick="submitEditarForm()" 
                    style="flex:1;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);color:#fff;padding:14px 24px;border:none;border-radius:12px;font-weight:600;font-size:15px;cursor:pointer;transition:all 0.3s;box-shadow:0 4px 12px rgba(102,126,234,0.4);"
                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(102,126,234,0.5)'"
                    onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(102,126,234,0.4)'">
                    <i class="fas fa-check" style="margin-right:8px;"></i>Actualizar Propuesta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Preview -->
<div id="previewModal" class="modal">
    <div class="modal-content" style="max-width:700px;border-radius:20px;padding:0;overflow:hidden;">
        <!-- Header -->
        <div style="background:linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%);padding:24px 32px;position:relative;">
            <button class="modal-close" onclick="closePreviewModal()" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.2);border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;font-size:24px;color:#fff;transition:all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;</button>
            <h3 style="margin:0;font-size:24px;font-weight:700;color:#fff;text-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <i class="fas fa-eye" style="margin-right:12px;"></i>Vista Previa
            </h3>
            <p style="margin:8px 0 0 0;color:rgba(255,255,255,0.9);font-size:14px;">Así se verá tu propuesta publicada</p>
        </div>
        
        <!-- Card Preview - Estilo igual a las cards del feed -->
        <div style="padding:32px;">
            <div class="card" style="padding:20px 24px 70px 24px;border:1px solid #e5e7eb;border-radius:12px;position:relative;background:#fff;display:flex;gap:20px;align-items:flex-start;">
                <!-- Logo circular -->
                <img src="<?= !empty($_SESSION['avatar_url']) ? htmlspecialchars($_SESSION['avatar_url']) : 'avatars/default.png' ?>" alt="avatar" class="card-logo" style="width:60px;height:60px;border-radius:50%;object-fit:cover;flex-shrink:0;" />
                
                <!-- Contenido principal -->
                <div class="card-body" style="flex:1;min-width:0;">
                    <!-- Header: título y empresa -->
                    <div class="card-header-row" style="display:flex;align-items:center;gap:12px;margin-bottom:8px;flex-wrap:wrap;">
                        <h3 id="previewTitulo" class="card-title" style="margin:0;font-size:18px;font-weight:700;color:#1f2937;"></h3>
                        <span class="card-company" style="color:#6b7280;font-size:14px;font-weight:500;">@<?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?></span>
                    </div>
                    
                    <!-- Badges -->
                    <div class="card-badges" style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
                        <span id="previewCategoria" class="badge" style="background:#f0f0f0;padding:4px 12px;border-radius:20px;font-size:13px;color:#666;"></span>
                        <span id="previewHorario" class="badge" style="background:#dbeafe;padding:4px 12px;border-radius:20px;font-size:13px;color:#1e40af;"></span>
                        <span id="previewLocalidad" style="color:#6b7280;font-size:13px;display:flex;align-items:center;gap:4px;">
                            <i class="fas fa-map-marker-alt" style="font-size:12px;"></i>
                            <span></span>
                        </span>
                    </div>
                    
                    <!-- Descripción -->
                    <ul class="card-description" id="previewDescripcion" style="list-style:none;padding:0;margin:0 0 8px 0;color:#4b5563;font-size:14px;line-height:1.6;">
                        <!-- Se llenará con JavaScript -->
                    </ul>
                    
                    <!-- Salario si existe -->
                    <div id="previewSalarioContainer" style="display:none;margin-top:8px;">
                        <span style="color:#10b981;font-weight:600;font-size:15px;display:flex;align-items:center;gap:6px;">
                            <i class="fas fa-dollar-sign"></i>
                            <span id="previewSalario"></span>
                        </span>
                    </div>
                </div>
                
                <!-- Botón "Ver más" (simulado) -->
                <button class="card-apply-btn" disabled style="position:absolute;bottom:20px;left:24px;background:#ef4444;color:#fff;padding:10px 24px;border:none;border-radius:8px;font-weight:600;font-size:14px;cursor:not-allowed;opacity:0.7;">
                    Ver más
                </button>
            </div>
            
            <!-- Información adicional -->
            <div style="margin-top:24px;padding:20px;background:#f9fafb;border-radius:12px;border:2px dashed #e5e7eb;">
                <h4 style="margin:0 0 12px 0;color:#374151;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-info-circle" style="color:#7C5CFC;"></i>
                    Información adicional
                </h4>
                <div style="display:grid;gap:8px;">
                    <p style="margin:0;color:#6b7280;font-size:13px;">
                        <strong>Contacto:</strong> <span id="previewContacto"></span>
                    </p>
                    <p style="margin:0;color:#6b7280;font-size:13px;">
                        <strong>Descripción completa:</strong>
                    </p>
                    <p id="previewDescripcionCompleta" style="margin:4px 0 0 0;color:#4b5563;font-size:13px;line-height:1.5;"></p>
                </div>
            </div>
        </div>
        
        <!-- Botones -->
        <div style="padding:0 32px 32px 32px;">
            <div style="display:flex;gap:12px;">
                <button onclick="closePreviewModal()" 
                    style="flex:1;background:#f3f4f6;color:#6b7280;padding:14px 24px;border:none;border-radius:12px;font-weight:600;font-size:15px;cursor:pointer;transition:all 0.3s;"
                    onmouseover="this.style.background='#e5e7eb';this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.background='#f3f4f6';this.style.transform='translateY(0)'">
                    <i class="fas fa-arrow-left" style="margin-right:8px;"></i>Volver a Editar
                </button>
                <button onclick="submitForm()" 
                    style="flex:1;background:linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%);color:#fff;padding:14px 24px;border:none;border-radius:12px;font-weight:600;font-size:15px;cursor:pointer;transition:all 0.3s;box-shadow:0 4px 12px rgba(124,92,252,0.4);"
                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(124,92,252,0.5)'"
                    onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(124,92,252,0.4)'">
                    <i class="fas fa-check-circle" style="margin-right:8px;"></i>Publicar Propuesta
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Card Detail Modal (Ver más) -->
<div id="cardDetailModal" class="modal">
    <div class="modal-content" style="max-width:700px;border-radius:20px;">
        <!-- Header con avatar y título -->
        <div style="display:flex;gap:20px;align-items:flex-start;padding-bottom:20px;border-bottom:2px solid #f0f0f0;">
            <img id="detailAvatar" src="avatars/default.png" alt="avatar" style="width:80px;height:80px;border-radius:16px;object-fit:cover;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <div style="flex:1;">
                <h3 id="detailTitle" style="margin:0 0 8px 0;font-size:24px;font-weight:700;color:#222;">Título</h3>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                    <i class="fas fa-user" style="color:#7C5CFC;font-size:14px;"></i>
                    <span id="detailCreator" style="font-weight:600;color:#555;"></span>
                </div>
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
                    <span id="detailCategory" style="background:#f0f0f0;padding:4px 12px;border-radius:20px;font-size:13px;color:#666;"></span>
                </div>
            </div>
            <button class="modal-close" style="background:#f5f5f5;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;font-size:20px;color:#666;transition:background 0.2s;" onmouseover="this.style.background='#e5e5e5'" onmouseout="this.style.background='#f5f5f5'">&times;</button>
        </div>
        
        <!-- Información principal -->
        <div style="padding:24px 0;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
                <div style="background:#f8f9ff;padding:16px;border-radius:12px;border-left:4px solid #7C5CFC;">
                    <div style="color:#7C5CFC;font-size:12px;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Ubicación</div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-map-marker-alt" style="color:#7C5CFC;"></i>
                        <span id="detailLocation" style="font-weight:600;color:#333;"></span>
                    </div>
                </div>
                <div style="background:#f8f9ff;padding:16px;border-radius:12px;border-left:4px solid #7C5CFC;">
                    <div style="color:#7C5CFC;font-size:12px;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Horario</div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-clock" style="color:#7C5CFC;"></i>
                        <span id="detailHorario" style="font-weight:600;color:#333;"></span>
                    </div>
                </div>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
                <div style="background:#f0fdf4;padding:16px;border-radius:12px;border-left:4px solid #10b981;">
                    <div style="color:#10b981;font-size:12px;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Salario</div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-dollar-sign" style="color:#10b981;"></i>
                        <span id="detailSalary" style="font-weight:700;color:#333;font-size:18px;"></span>
                    </div>
                </div>
                <div style="background:#fef3c7;padding:16px;border-radius:12px;border-left:4px solid #f59e0b;">
                    <div style="color:#f59e0b;font-size:12px;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Publicado</div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-calendar" style="color:#f59e0b;"></i>
                        <span id="detailWhen" style="font-weight:600;color:#333;"></span>
                    </div>
                </div>
            </div>
            
            <!-- Descripción -->
            <div style="background:#fafafa;padding:20px;border-radius:12px;margin-bottom:20px;">
                <h4 style="margin:0 0 12px 0;color:#222;font-size:16px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-file-alt" style="color:#7C5CFC;"></i>
                    Descripción
                </h4>
                <p id="detailDescription" style="margin:0;line-height:1.6;color:#555;"></p>
            </div>
            
            <!-- Contacto -->
            <div style="background:#fff;padding:20px;border:2px solid #f0f0f0;border-radius:12px;">
                <h4 style="margin:0 0 12px 0;color:#222;font-size:16px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-envelope" style="color:#7C5CFC;"></i>
                    Contacto
                </h4>
                <span id="detailContact" style="font-weight:600;color:#7C5CFC;font-size:16px;"></span>
            </div>
        </div>
        
        <!-- Footer con botones -->
        <div style="display:flex;gap:12px;justify-content:flex-end;padding-top:20px;border-top:2px solid #f0f0f0;">
            <button id="copiarContactoBtn" onclick="copiarContacto()" style="background:#7C5CFC;color:#fff;padding:12px 28px;border-radius:8px;font-weight:600;border:none;cursor:pointer;transition:background 0.2s;display:flex;align-items:center;gap:8px;" onmouseover="this.style.background='#6b4dd6'" onmouseout="this.style.background='#7C5CFC'">
                <i class="fas fa-copy"></i>
                <span id="copiarBtnText">Copiar Contacto</span>
            </button>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar Cierre de Sesión</h3>
            <button class="modal-close" onclick="closeLogoutModal()">&times;</button>
        </div>
        <p style="margin: 20px 0;">¿Estás seguro que deseas cerrar sesión?</p>
        <div class="modal-buttons">
            <a href="logout.php" class="btn" style="text-decoration:none;text-align:center;">Sí, cerrar sesión</a>
            <button onclick="closeLogoutModal()" class="btn" style="background:#666;">Cancelar</button>
        </div>
    </div>
</div>

<!-- Add JavaScript for modal functionality -->
<script>
// Funciones para actualizar estilos de radio buttons
function updateSalarioRadioStyles() {
    const labels = document.querySelectorAll('input[name="salario_tipo"]');
    labels.forEach(radio => {
        const label = radio.parentElement;
        if (radio.checked) {
            label.style.background = 'linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%)';
            label.style.borderColor = '#7C5CFC';
            label.style.color = '#fff';
            label.style.boxShadow = '0 4px 12px rgba(124,92,252,0.3)';
            label.querySelectorAll('i, span').forEach(el => el.style.color = '#fff');
        } else {
            label.style.background = '#f3f4f6';
            label.style.borderColor = 'transparent';
            label.style.boxShadow = 'none';
            label.querySelector('i').style.color = '#7C5CFC';
            label.querySelector('span').style.color = '#374151';
        }
    });
}

function updateEditarSalarioRadioStyles() {
    const labels = document.querySelectorAll('input[name="editar_salario_tipo"]');
    labels.forEach(radio => {
        const label = radio.parentElement;
        if (radio.checked) {
            label.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            label.style.borderColor = '#667eea';
            label.style.color = '#fff';
            label.style.boxShadow = '0 4px 12px rgba(102,126,234,0.3)';
            label.querySelectorAll('i, span').forEach(el => el.style.color = '#fff');
        } else {
            label.style.background = '#f3f4f6';
            label.style.borderColor = 'transparent';
            label.style.boxShadow = 'none';
            label.querySelector('i').style.color = '#667eea';
            label.querySelector('span').style.color = '#374151';
        }
    });
}

// Funciones para toggle de salario
function toggleSalarioInput() {
    const salarioInput = document.getElementById('salario');
    const tipoNegociable = document.querySelector('input[name="salario_tipo"][value="negociable"]').checked;
    
    if (tipoNegociable) {
        salarioInput.disabled = true;
        salarioInput.value = '';
        salarioInput.style.background = '#f3f4f6';
        salarioInput.style.color = '#9ca3af';
    } else {
        salarioInput.disabled = false;
        salarioInput.style.background = '#f9fafb';
        salarioInput.style.color = '#374151';
        salarioInput.focus();
    }
}

function toggleEditarSalarioInput() {
    const salarioInput = document.getElementById('editar_salario');
    const tipoNegociable = document.querySelector('input[name="editar_salario_tipo"][value="negociable"]').checked;
    
    if (tipoNegociable) {
        salarioInput.disabled = true;
        salarioInput.value = '';
        salarioInput.style.background = '#f3f4f6';
        salarioInput.style.color = '#9ca3af';
    } else {
        salarioInput.disabled = false;
        salarioInput.style.background = '#f9fafb';
        salarioInput.style.color = '#374151';
        salarioInput.focus();
    }
}

function openReportModal(propuestaId) {
    document.getElementById('reportPropuestaId').value = propuestaId;
    const modal = document.getElementById('reportModal');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

function openFavoritoModal(propuestaId, isFavorite) {
    const msg = isFavorite ? 
        '¿Deseas quitar esta propuesta de favoritos?' : 
        '¿Deseas agregar esta propuesta a favoritos?';
    document.getElementById('favoritoMessage').textContent = msg;
    document.getElementById('confirmFavorito').onclick = () => {
        window.location.href = `favorito.php?id=${propuestaId}`;
    };
    const modal = document.getElementById('favoritoModal');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

function openCrearModal() {
    const modal = document.getElementById('crearPropuestaModal');
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('show');
        updateSalarioRadioStyles();
    }, 10);
}

function closeCrearModal() {
    const modal = document.getElementById('crearPropuestaModal');
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = 'none', 300);
}

function closePreviewModal() {
    const previewModal = document.getElementById('previewModal');
    const crearModal = document.getElementById('crearPropuestaModal');
    
    previewModal.classList.remove('show');
    setTimeout(() => {
        previewModal.style.display = 'none';
        crearModal.style.display = 'flex';
        setTimeout(() => crearModal.classList.add('show'), 10);
    }, 300);
}

function showPreview() {
    // Ocultar modal de crear
    document.getElementById('crearPropuestaModal').style.display = 'none';
    
    // Obtener valores del formulario
    const titulo = document.getElementById('propuesta').value;
    const localidad = document.getElementById('localidad').value;
    const categoria = document.getElementById('categoria').value;
    const horario = document.getElementById('horario').value;
    const salario = document.getElementById('salario').value;
    const descripcion = document.getElementById('descripcion').value;
    const contacto = document.getElementById('contacto').value;
    
    // Actualizar contenido del preview
    document.getElementById('previewTitulo').textContent = titulo || 'Título del puesto';
    document.getElementById('previewCategoria').textContent = categoria || 'Categoría';
    document.getElementById('previewHorario').textContent = horario || 'Horario';
    
    // Localidad dentro del badge
    const localidadSpan = document.querySelector('#previewLocalidad span');
    if (localidadSpan) {
        localidadSpan.textContent = localidad || 'Sin ubicación';
    }
    
    // Descripción - primeras líneas como bullets
    const descContainer = document.getElementById('previewDescripcion');
    descContainer.innerHTML = '';
    if (descripcion) {
        const lines = descripcion.split(/[\r\n]+/).filter(l => l.trim() !== '').slice(0, 2);
        lines.forEach(line => {
            const li = document.createElement('li');
            li.style.display = 'flex';
            li.style.gap = '8px';
            li.style.alignItems = 'flex-start';
            li.style.marginBottom = '6px';
            li.innerHTML = `<i class="fas fa-check-circle" style="color:#10b981;margin-top:4px;flex-shrink:0;"></i><span>${line}</span>`;
            descContainer.appendChild(li);
        });
    }
    
    // Descripción completa
    document.getElementById('previewDescripcionCompleta').textContent = descripcion || 'Sin descripción';
    
    // Salario - verificar si es negociable o específico
    const salarioContainer = document.getElementById('previewSalarioContainer');
    const salarioSpan = document.getElementById('previewSalario');
    const tipoNegociable = document.querySelector('input[name="salario_tipo"][value="negociable"]').checked;
    
    if (tipoNegociable) {
        salarioSpan.textContent = 'A negociar';
        salarioContainer.style.display = 'block';
    } else if (salario && salario.trim() !== '') {
        salarioSpan.textContent = salario;
        salarioContainer.style.display = 'block';
    } else {
        salarioContainer.style.display = 'none';
    }
    
    // Contacto
    document.getElementById('previewContacto').textContent = contacto || 'Sin contacto';
    
    // Mostrar modal de preview con animación
    const crearModal = document.getElementById('crearPropuestaModal');
    const previewModal = document.getElementById('previewModal');
    
    crearModal.classList.remove('show');
    setTimeout(() => {
        crearModal.style.display = 'none';
        previewModal.style.display = 'flex';
        setTimeout(() => previewModal.classList.add('show'), 10);
    }, 300);
}

function submitForm() {
    const formData = new FormData(document.getElementById('propuestaForm'));
    
    // Si está seleccionado "negociable", establecer salario como "A negociar"
    const tipoNegociable = document.querySelector('input[name="salario_tipo"][value="negociable"]').checked;
    if (tipoNegociable) {
        formData.set('salario', 'A negociar');
    }
    
    fetch('crear.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload(); // Recargar para ver la nueva propuesta
        } else {
            alert('Error al crear la propuesta: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear la propuesta');
    });
}

function openEditarModal(propuestaId) {
    // Buscar la card para obtener los datos
    const card = document.querySelector(`.card[data-id="${propuestaId}"]`);
    if (!card) return;
    
    // Llenar el formulario con los datos actuales
    document.getElementById('editar_id').value = propuestaId;
    document.getElementById('editar_propuesta').value = card.dataset.title || '';
    document.getElementById('editar_localidad').value = card.dataset.localidad || '';
    document.getElementById('editar_descripcion').value = card.dataset.descripcion || '';
    document.getElementById('editar_contacto').value = card.dataset.contacto || '';
    document.getElementById('editar_categoria').value = card.dataset.categoria || '';
    document.getElementById('editar_horario').value = card.dataset.horario || '';
    
    // Manejar salario - si tiene valor, seleccionar "especifico", sino "negociable"
    const salarioValue = card.dataset.salario || '';
    const salarioInput = document.getElementById('editar_salario');
    
    if (salarioValue && salarioValue.trim() !== '' && salarioValue !== 'A negociar') {
        document.querySelector('input[name="editar_salario_tipo"][value="especifico"]').checked = true;
        salarioInput.value = salarioValue;
        salarioInput.disabled = false;
        salarioInput.style.background = '#f9fafb';
        salarioInput.style.color = '#374151';
    } else {
        document.querySelector('input[name="editar_salario_tipo"][value="negociable"]').checked = true;
        salarioInput.value = '';
        salarioInput.disabled = true;
        salarioInput.style.background = '#f3f4f6';
        salarioInput.style.color = '#9ca3af';
    }
    
    // Mostrar el modal con animación
    const modal = document.getElementById('editarPropuestaModal');
    modal.style.display = 'flex';
    
    // Actualizar estilos de los radio buttons después de mostrar el modal
    setTimeout(() => {
        modal.classList.add('show');
        updateEditarSalarioRadioStyles();
    }, 10);
}

function closeEditarModal() {
    const modal = document.getElementById('editarPropuestaModal');
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = 'none', 300);
}

function submitEditarForm() {
    const formData = new FormData(document.getElementById('editarForm'));
    
    // Si está seleccionado "negociable", establecer salario como "A negociar"
    const tipoNegociable = document.querySelector('input[name="editar_salario_tipo"][value="negociable"]').checked;
    if (tipoNegociable) {
        formData.set('salario', 'A negociar');
    }
    
    fetch('editar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload(); // Recargar para ver los cambios
        } else {
            alert('Error al actualizar la propuesta: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la propuesta');
    });
}

function openFavoritoDetail(propuestaId) {
    // Buscar la card en favoritos con el ID
    const card = document.querySelector(`.favoritos .card[data-id="${propuestaId}"]`);
    if (!card) return;
    
    // Llenar el modal con los datos
    const modal = document.getElementById('cardDetailModal');
    document.getElementById('detailTitle').textContent = card.dataset.title || '';
    document.getElementById('detailLocation').textContent = card.dataset.localidad || '';
    document.getElementById('detailHorario').textContent = card.dataset.horario || 'No especificado';
    document.getElementById('detailDescription').textContent = card.dataset.descripcion || '';
    document.getElementById('detailContact').textContent = card.dataset.contacto || '';
    document.getElementById('detailCategory').textContent = card.dataset.categoria || '';
    document.getElementById('detailCreator').textContent = card.dataset.creador || '';
    document.getElementById('detailAvatar').src = card.dataset.avatar || 'avatars/default.png';
    document.getElementById('detailSalary').textContent = card.dataset.salario || '';
    document.getElementById('detailWhen').textContent = card.dataset.fecha || '';
    
    // Mostrar el modal con animación
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

function openLogoutModal() {
    const modal = document.getElementById('logoutModal');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = 'none', 300);
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        const modal = event.target;
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

// Función para copiar el contacto al portapapeles
function copiarContacto() {
    const contacto = document.getElementById('modalContacto').textContent;
    
    // Usar la API del portapapeles
    navigator.clipboard.writeText(contacto).then(() => {
        // Cambiar el texto del botón temporalmente
        const btnText = document.getElementById('copiarBtnText');
        const btn = document.getElementById('copiarContactoBtn');
        const originalText = btnText.textContent;
        
        btnText.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
        btn.style.background = '#10b981';
        
        setTimeout(() => {
            btnText.innerHTML = 'Copiar Contacto';
            btn.style.background = '#7C5CFC';
        }, 2000);
    }).catch(err => {
        // Fallback para navegadores antiguos
        const textarea = document.createElement('textarea');
        textarea.value = contacto;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        
        const btnText = document.getElementById('copiarBtnText');
        btnText.textContent = '¡Copiado!';
        setTimeout(() => {
            btnText.textContent = 'Copiar Contacto';
        }, 2000);
    });
}

// Close modals with ESC key
// Función para cerrar el modal de detalle de card
function closeCardDetailModal() {
    const modal = document.getElementById('cardDetailModal');
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = 'none', 300);
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 300);
        });
    }
});
</script>
<script>
// Tarjeta seleccionable: al hacer click (sin clicar en enlaces o botones) se marca como selected
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.card').forEach(function(card){
        card.addEventListener('click', function(e){
            if (e.target.closest('a') || e.target.closest('button')) return; // evitar cuando se clican links o botones
            document.querySelectorAll('.card.selected').forEach(function(c){ c.classList.remove('selected'); });
            card.classList.add('selected');
        });
    });
});
</script>
<script>
// Ver más: abrir modal con datos desde data-attributes
document.addEventListener('DOMContentLoaded', function(){
    function closeModal(modal){ modal.style.display = 'none'; }

    // abrir modal al clic en botón
    document.querySelectorAll('.ver-mas-card').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.stopPropagation();
            var card = btn.closest('.card');
            if (!card) return;
            var modal = document.getElementById('cardDetailModal');
            document.getElementById('detailTitle').textContent = card.dataset.title || '';
            document.getElementById('detailLocation').textContent = card.dataset.localidad || '';
            document.getElementById('detailHorario').textContent = card.dataset.horario || 'No especificado';
            document.getElementById('detailDescription').textContent = card.dataset.descripcion || '';
            document.getElementById('detailContact').textContent = card.dataset.contacto || '';
            document.getElementById('detailCategory').textContent = card.dataset.categoria || '';
            document.getElementById('detailCreator').textContent = card.dataset.creador || '';
            document.getElementById('detailAvatar').src = card.dataset.avatar || 'avatars/default.png';
            document.getElementById('detailSalary').textContent = card.dataset.salario || '';
            document.getElementById('detailWhen').textContent = card.dataset.fecha || '';
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        });
    });

    // cerrar modals con el botón .modal-close
    document.querySelectorAll('.modal-close').forEach(function(btn){
        btn.addEventListener('click', function(){
            var modal = btn.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => modal.style.display = 'none', 300);
            }
        });
    });
});
</script>
</body>
</html>
