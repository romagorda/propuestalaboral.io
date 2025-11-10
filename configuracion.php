<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre_usuario'];
$rol = $_SESSION['rol'];

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT * FROM login WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_perfil'])) {
    $nuevo_nombre = trim($_POST['nombre_usuario']);
    $email = trim($_POST['email']);
    
    if (!empty($nuevo_nombre)) {
        $stmt_update = $conn->prepare("UPDATE login SET nombre_usuario = ?, email = ? WHERE id = ?");
        $stmt_update->bind_param("ssi", $nuevo_nombre, $email, $usuario_id);
        
        if ($stmt_update->execute()) {
            $_SESSION['nombre_usuario'] = $nuevo_nombre;
            $_SESSION['msg'] = "Perfil actualizado correctamente";
            header("Location: configuracion.php");
            exit;
        }
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmar = $_POST['password_confirmar'];
    
    // Verificar contraseña actual
    if (password_verify($password_actual, $usuario['clave'])) {
        if ($password_nueva === $password_confirmar) {
            if (strlen($password_nueva) >= 6) {
                $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
                $stmt_pass = $conn->prepare("UPDATE login SET clave = ? WHERE id = ?");
                $stmt_pass->bind_param("si", $password_hash, $usuario_id);
                
                if ($stmt_pass->execute()) {
                    $_SESSION['msg'] = "Contraseña actualizada correctamente";
                    header("Location: configuracion.php");
                    exit;
                }
            } else {
                $error_pass = "La contraseña debe tener al menos 6 caracteres";
            }
        } else {
            $error_pass = "Las contraseñas no coinciden";
        }
    } else {
        $error_pass = "La contraseña actual es incorrecta";
    }
}

// Procesar cambio de avatar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = 'avatar_' . $usuario_id . '_' . time() . '.' . $ext;
            $upload_path = 'uploads/avatars/' . $new_filename;
            
            // Crear directorio si no existe
            if (!file_exists('uploads/avatars')) {
                mkdir('uploads/avatars', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                // Eliminar avatar anterior si existe
                if (!empty($usuario['avatar_url']) && file_exists($usuario['avatar_url'])) {
                    unlink($usuario['avatar_url']);
                }
                
                $stmt_avatar = $conn->prepare("UPDATE login SET avatar_url = ? WHERE id = ?");
                $stmt_avatar->bind_param("si", $upload_path, $usuario_id);
                
                if ($stmt_avatar->execute()) {
                    $_SESSION['msg'] = "Avatar actualizado correctamente";
                    header("Location: configuracion.php");
                    exit;
                }
            }
        } else {
            $error_avatar = "Solo se permiten archivos JPG, PNG y GIF";
        }
    }
}

// Obtener avatar actualizado
$avatar_url = $usuario['avatar_url'] ?: 'avatars/default.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - PropuestaLaboral</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 36px;
            color: #111827;
            margin-bottom: 10px;
            font-weight: 800;
        }
        
        .header p {
            color: #6b7280;
            font-size: 16px;
        }
        
        .volver-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(124,92,252,0.3);
            margin-bottom: 30px;
        }
        
        .volver-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124,92,252,0.4);
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        
        .config-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
        }
        
        .config-section h2 {
            font-size: 22px;
            color: #111827;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
        }
        
        .config-section h2 i {
            color: #7C5CFC;
            font-size: 20px;
        }
        
        .avatar-section {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #7C5CFC;
            box-shadow: 0 8px 24px rgba(124,92,252,0.25);
        }
        
        .avatar-info {
            flex: 1;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #7C5CFC;
            box-shadow: 0 0 0 3px rgba(124,92,252,0.1);
        }
        
        .form-group input[type="file"] {
            padding: 10px;
            cursor: pointer;
        }
        
        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #7C5CFC 0%, #9b6dff 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(124,92,252,0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124,92,252,0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(239,68,68,0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(239,68,68,0.4);
        }
        
        .info-box {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 16px;
            margin-top: 16px;
        }
        
        .info-box p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @media (max-width: 768px) {
            .avatar-section {
                flex-direction: column;
                text-align: center;
            }
            
            .container {
                padding: 20px 16px;
            }
            
            .header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="volver-btn">
            <i class="fas fa-arrow-left"></i>
            Volver al Inicio
        </a>
        
        <div class="header">
            <h1><i class="fas fa-cog"></i> Configuración</h1>
            <p>Administra tu cuenta y preferencias</p>
        </div>
        
        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['msg']) ?>
            </div>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>
        
        <?php if (isset($error_pass)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error_pass) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_avatar)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error_avatar) ?>
            </div>
        <?php endif; ?>
        
        <!-- Avatar -->
        <div class="config-section">
            <h2><i class="fas fa-user-circle"></i> Foto de Perfil</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="avatar-section">
                    <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Avatar" class="avatar-preview">
                    <div class="avatar-info">
                        <div class="form-group">
                            <label for="avatar">Cambiar Avatar</label>
                            <input type="file" name="avatar" id="avatar" accept="image/*">
                        </div>
                        <button type="submit" name="cambiar_avatar" class="btn btn-primary">
                            <i class="fas fa-upload"></i>
                            Subir Nueva Foto
                        </button>
                    </div>
                </div>
                <div class="info-box">
                    <p><i class="fas fa-info-circle"></i> Formatos permitidos: JPG, PNG, GIF. Tamaño recomendado: 500x500px</p>
                </div>
            </form>
        </div>
        
        <!-- Información Personal -->
        <div class="config-section">
            <h2><i class="fas fa-id-card"></i> Información Personal</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="nombre_usuario">Nombre de Usuario</label>
                    <input type="text" name="nombre_usuario" id="nombre_usuario" value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label>Rol</label>
                    <input type="text" value="<?= htmlspecialchars($usuario['rol']) ?>" disabled style="background: #f3f4f6; cursor: not-allowed;">
                </div>
                
                <button type="submit" name="actualizar_perfil" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Cambios
                </button>
            </form>
        </div>
        
        <!-- Cambiar Contraseña -->
        <div class="config-section">
            <h2><i class="fas fa-lock"></i> Seguridad</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="password_actual">Contraseña Actual</label>
                    <input type="password" name="password_actual" id="password_actual" required>
                </div>
                
                <div class="form-group">
                    <label for="password_nueva">Nueva Contraseña</label>
                    <input type="password" name="password_nueva" id="password_nueva" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmar" id="password_confirmar" required minlength="6">
                </div>
                
                <button type="submit" name="cambiar_password" class="btn btn-primary">
                    <i class="fas fa-key"></i>
                    Cambiar Contraseña
                </button>
                
                <div class="info-box">
                    <p><i class="fas fa-shield-alt"></i> La contraseña debe tener al menos 6 caracteres</p>
                </div>
            </form>
        </div>
        
        <!-- Zona de Peligro -->
        <div class="config-section" style="border-left: 4px solid #ef4444;">
            <h2><i class="fas fa-exclamation-triangle"></i> Zona de Peligro</h2>
            <p style="color: #6b7280; margin-bottom: 16px;">Las siguientes acciones son permanentes y no se pueden deshacer.</p>
            <button onclick="if(confirm('¿Estás seguro de que quieres cerrar sesión?')) window.location.href='logout.php'" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
            </button>
        </div>
    </div>
</body>
</html>
