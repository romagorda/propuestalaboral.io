<?php
session_start();
include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Invitado';
$rol = $_SESSION['rol'] ?? 'usuario';

// Procesar búsqueda y categoría
$busqueda = $_GET['busqueda'] ?? '';
$categoria = $_GET['categoria'] ?? '';


// Consulta que incluye tags y el nombre del usuario que creó la propuesta
$query = "SELECT DISTINCT p.*, l.nombre_usuario AS creador, l.id AS creador_id, l.avatar_url AS creador_avatar FROM propuestas p
LEFT JOIN login l ON p.usuario_id = l.id
LEFT JOIN propuesta_tag pt ON p.id = pt.propuesta_id
LEFT JOIN tags t ON pt.tag_id = t.id
WHERE 1=1";
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
    <title>Bolsa de Trabajo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<!-- Barra superior de búsqueda y categorías -->
<?php
// Obtener categorías únicas de las propuestas
$categorias_result = $conn->query("SELECT DISTINCT categoria FROM propuestas");
$categorias = [];
while ($row = $categorias_result->fetch_assoc()) {
    $categorias[] = $row['categoria'];
}
?>
<div class="top-bar">
    <form method="get" class="busqueda-form">
        <div class="busqueda-input-wrapper" style="position:relative;">
            <input type="text" name="busqueda" placeholder="Buscar propuestas...">
            <button type="submit" class="icon-search-btn" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:linear-gradient(90deg,#e9e9ff 0%,#f7f7fa 100%);border-radius:50%;border:2px solid #e0e0e0;width:38px;height:38px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(166,142,247,0.10);color:#7d5be6;cursor:pointer;transition:background 0.2s,color 0.2s,border 0.2s;outline:none;padding:0;">
                <i class="fa fa-search"></i>
            </button>
        </div>
    </form>
    <div class="categorias-bar">
        <span class="cat-title"></span>
        <!-- Botón especial de inicio -->
        <a href="index.php" class="cat-item cat-home"><i class="fa fa-home"></i></a>
        <?php
        $extra_categorias = [
            'Tecnología', 'Diseño', 'Finanzas', 'Salud', 'Educación', 'Administración', 'Ventas', 'Legal', 'Recursos Humanos', 'Construcción'
        ];
        $todas_categorias = array_unique(array_filter(array_merge($extra_categorias, $categorias)));
        foreach ($todas_categorias as $cat):
            if (trim($cat) !== ''): ?>
                <a href="?categoria=<?= urlencode($cat) ?>" class="cat-item"><?= htmlspecialchars($cat) ?></a>
            <?php endif;
        endforeach; ?>
    </div>
</div>

<div class="container">
    <!-- Sidebar izquierdo -->
    <aside class="sidebar">
        <div class="profile">
            <a href="perfil.php" class="profile-link">
                <img src="perfil.png" alt="Perfil">
                <span><?= htmlspecialchars($nombre_usuario) ?> (<?= htmlspecialchars($rol) ?>)</span>
            </a>
        </div>
        <nav class="menu">
            <a href="#">Inicio</a>
            <a href="#">Mis Propuestas</a>
            <a href="#">Configuración</a>
           <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="logout.php">Cerrar sesión</a>
           <?php else: ?>
                <a href="login.php">Iniciar sesión</a>
           <?php endif; ?>
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
        <section class="propuestas" style="max-height: 70vh; overflow-y: auto; padding-right: 8px;">
            <?php while($p = $result->fetch_assoc()):
                $fav_stmt = $conn->prepare("SELECT * FROM favoritos WHERE usuario_id=? AND propuesta_id=?");
                $fav_stmt->bind_param("ii", $usuario_id, $p['id']);
                $fav_stmt->execute();
                $fav_result = $fav_stmt->get_result();
                $es_favorito = $fav_result->num_rows > 0;
            ?>
            <div class="card">
                <div class="creador" style="display:flex;align-items:center;gap:10px;">
                    <a href="perfil.php?id=<?= $p['creador_id'] ?>">
                        <?php if (!empty($p['creador_avatar'])): ?>
                            <img src="<?= htmlspecialchars($p['creador_avatar']) ?>" alt="avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;vertical-align:middle;">
                        <?php else: ?>
                            <img src="perfil.png" alt="avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;vertical-align:middle;">
                        <?php endif; ?>
                    </a>
                    <span><a href="perfil.php?id=<?= $p['creador_id'] ?>" style="color:#7d5be6;"><?= htmlspecialchars($p['creador']) ?></a></span>
                </div>
                <h3><?= htmlspecialchars($p['propuesta']) ?></h3>
                <p class="categoria">Categoría: <?= htmlspecialchars($p['categoria']) ?></p>
                <p class="ubicacion"><?= htmlspecialchars($p['localidad']) ?></p>
                <p><?= htmlspecialchars($p['descripcion']) ?></p>
                <p><?= htmlspecialchars($p['contacto']) ?></p>
                <div class="acciones">
                    <?php if ($rol === 'admin'): ?>
                        <a href="editar.php?id=<?= $p['id'] ?>" class="ver-mas">EDITAR</a>
                        <a href="eliminar.php?id=<?= $p['id'] ?>" class="ver-mas" onclick="return confirm('¿Seguro que querés eliminar esta propuesta?')">ELIMINAR</a>
                    <?php endif; ?>
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
