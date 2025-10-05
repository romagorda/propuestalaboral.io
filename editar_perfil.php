<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos actuales
$stmt = $conn->prepare("SELECT id, nombre_usuario, gmail, rol, IFNULL(avatar_url,'') as avatar_url FROM login WHERE id = ?");
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $gmail = trim($_POST['gmail'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validaciones simples
    if ($nombre === '' || $gmail === '') {
        $error = 'El nombre y el email son obligatorios.';
    } else {
        // Manejo de avatar (si se sube)
        $new_avatar_url = $user['avatar_url'];
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $f = $_FILES['avatar'];
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $error = 'Error subiendo el archivo.';
            } elseif (!in_array(mime_content_type($f['tmp_name']), $allowed)) {
                $error = 'Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.';
            } elseif ($f['size'] > 10 * 1024 * 1024) {
                $error = 'La imagen supera 10MB.';
            } else {
                $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $filename = 'avatar_' . $usuario_id . '_' . time() . '.' . $ext;
                $target = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                if (move_uploaded_file($f['tmp_name'], $target)) {
                    // Guardar ruta relativa para usar en src
                    $new_avatar_url = 'uploads/avatars/' . $filename;
                } else {
                    $error = 'No se pudo mover el archivo subido.';
                }
            }
        }

        if ($error === '') {
            // Asegurar que la columna avatar_url exista (intento no destructivo)
            $colRes = $conn->query("SHOW COLUMNS FROM login LIKE 'avatar_url'");
            if ($colRes && $colRes->num_rows === 0) {
                // Intentar crear la columna (si el usuario DB tiene permisos)
                $conn->query("ALTER TABLE login ADD COLUMN avatar_url VARCHAR(255) NULL AFTER gmail");
            }

            // Preparar UPDATE dinámico
            $fields = [];
            $types = '';
            $params = [];

            $fields[] = 'nombre_usuario = ?'; $types .= 's'; $params[] = $nombre;
            $fields[] = 'gmail = ?'; $types .= 's'; $params[] = $gmail;

            if ($password !== '') {
                $pw_hash = password_hash($password, PASSWORD_DEFAULT);
                $fields[] = 'password = ?'; $types .= 's'; $params[] = $pw_hash;
            }

            // Si se subió una nueva imagen, incluir avatar_url en la actualización
            if ($new_avatar_url !== $user['avatar_url']) {
                $fields[] = 'avatar_url = ?'; $types .= 's'; $params[] = $new_avatar_url;
            }

            $types .= 'i';
            $params[] = $usuario_id;

            $sql = 'UPDATE login SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $upd = $conn->prepare($sql);
            if ($upd) {
                // bind_param requiere variables; usamos el operador splat
                $upd->bind_param($types, ...$params);
                if ($upd->execute()) {
                    $success = 'Perfil actualizado correctamente.';
                    // refrescar datos de usuario
                    $user['nombre_usuario'] = $nombre;
                    $user['gmail'] = $gmail;
                    $user['avatar_url'] = $new_avatar_url;
                    // actualizar nombre de sesión si corresponde
                    if (isset($_SESSION['nombre_usuario'])) {
                        $_SESSION['nombre_usuario'] = $nombre;
                    }
                } else {
                    $error = 'Error al actualizar el perfil.';
                }
            } else {
                $error = 'Error preparando la consulta.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Editar perfil</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .edit-box { max-width:700px; margin:30px auto; background:#fff; padding:20px; border-radius:12px; }
        .edit-box label { display:block; margin-top:10px; color:#333; }
        .edit-box input[type=text], .edit-box input[type=email], .edit-box input[type=password] { width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; }
        .avatar-preview { display:flex; gap:14px; align-items:center; margin-top:10px; }
        .avatar-preview img { width:96px; height:96px; border-radius:12px; object-fit:cover; border:3px solid #f3f0ff; }
        .msg { padding:10px; border-radius:8px; margin-bottom:12px; }
        .msg.error { background:#ffecec; color:#a33; }
        .msg.ok { background:#e8ffe8; color:#183; }
    </style>
</head>
<body>
<div class="edit-box">
    <h2>Editar perfil</h2>
    <?php if ($error): ?>
        <div class="msg error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="msg ok"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="nombre">Nombre de usuario</label>
        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($user['nombre_usuario']) ?>" required>

        <label for="gmail">Email</label>
        <input type="email" id="gmail" name="gmail" value="<?= htmlspecialchars($user['gmail']) ?>" required>

        <label for="password">Cambiar contraseña (dejar vacío para mantener)</label>
        <input type="password" id="password" name="password" placeholder="Nueva contraseña">

    <label for="avatar">Avatar (JPG/PNG/GIF/WEBP, máx 10MB)</label>
        <input type="file" id="avatar" name="avatar" accept="image/*">

        <div class="avatar-preview">
            <div>
                <strong>Avatar actual</strong>
                <div>
                    <?php if (!empty($user['avatar_url'])): ?>
                        <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="avatar">
                    <?php else: ?>
                        <img src="perfil.png" alt="avatar">
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="margin-top:14px; display:flex; gap:10px;">
            <button class="btn" type="submit">Guardar cambios</button>
            <a class="btn" href="perfil.php">Volver</a>
        </div>
    </form>
</div>
</body>
</html>
