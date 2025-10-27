<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Marcar como resuelto
if (isset($_GET['resuelto'])) {
    $id = intval($_GET['resuelto']);
    $stmt = $conn->prepare("UPDATE reportes SET estado='resuelto' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$query = "SELECT r.*, p.propuesta, l.nombre_usuario 
          FROM reportes r
          INNER JOIN propuestas p ON r.propuesta_id = p.id
          INNER JOIN login l ON r.usuario_id = l.id
          ORDER BY r.fecha DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reportes - Panel de Administración</title>
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
        padding: 20px;
        min-height: 100vh;
    }
    
    .container {
        max-width: 1400px;
        margin: 0 auto;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .header {
        background: linear-gradient(135deg, #7d5be6 0%, #6b4dd6 100%);
        color: white;
        padding: 30px;
        text-align: center;
        position: relative;
    }
    
    .header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 300;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .header .subtitle {
        opacity: 0.9;
        font-size: 1.1rem;
        margin-top: 10px;
    }
    
    .content {
        padding: 30px;
    }
    
    .volver-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 25px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #7d5be6 0%, #6b4dd6 100%);
        color: #fff;
        text-decoration: none;
        border-radius: 50px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(125, 91, 230, 0.3);
        font-weight: 500;
    }
    
    .volver-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(125, 91, 230, 0.4);
        background: linear-gradient(135deg, #6b4dd6 0%, #5a3cc4 100%);
    }
    
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border-left: 4px solid #7d5be6;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #7d5be6;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 5px;
    }
    
    .table-container {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    
    thead {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    th {
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    td {
        padding: 15px;
        border-bottom: 1px solid #f1f3f4;
        vertical-align: middle;
    }
    
    tbody tr {
        transition: all 0.2s ease;
    }
    
    tbody tr:hover {
        background: #f8f9ff;
        transform: scale(1.01);
    }
    
    .resuelto {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        border-left: 4px solid #17a2b8;
    }
    
    .pendiente {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border-left: 4px solid #dc3545;
    }
    
    .estado-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .estado-pendiente {
        background: #dc3545;
        color: white;
    }
    
    .estado-resuelto {
        background: #28a745;
        color: white;
    }
    
    .accion-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }
    
    .accion-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    }
    
    .id-badge {
        background: #7d5be6;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.8rem;
    }
    
    .motivo-text {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .fecha-text {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        .container {
            margin: 10px;
            border-radius: 10px;
        }
        
        .content {
            padding: 20px 15px;
        }
        
        .header h1 {
            font-size: 2rem;
        }
        
        table {
            font-size: 0.8rem;
        }
        
        th, td {
            padding: 10px 8px;
        }
    }
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-flag"></i> Panel de Reportes</h1>
        <div class="subtitle">Gestión de reportes de propuestas laborales</div>
    </div>
    
    <div class="content">
        <!-- Botón volver al inicio -->
        <a href="index.php" class="volver-btn">
            <i class="fas fa-arrow-left"></i>
            Volver al Inicio
        </a>

        <!-- Estadísticas rápidas -->
        <div class="stats-container">
            <?php
            $total_result = $conn->query("SELECT COUNT(*) as total FROM reportes");
            $total = $total_result->fetch_assoc()['total'];
            
            $pendientes_result = $conn->query("SELECT COUNT(*) as pendientes FROM reportes WHERE estado='pendiente'");
            $pendientes = $pendientes_result->fetch_assoc()['pendientes'];
            
            $resueltos = $total - $pendientes;
            ?>
            <div class="stat-card">
                <div class="stat-number"><?= $total ?></div>
                <div class="stat-label">Total Reportes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #dc3545;"><?= $pendientes ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #28a745;"><?= $resueltos ?></div>
                <div class="stat-label">Resueltos</div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-briefcase"></i> Propuesta</th>
                        <th><i class="fas fa-user"></i> Usuario</th>
                        <th><i class="fas fa-comment-alt"></i> Motivo</th>
                        <th><i class="fas fa-calendar"></i> Fecha</th>
                        <th><i class="fas fa-info-circle"></i> Estado</th>
                        <th><i class="fas fa-cogs"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = $result->fetch_assoc()): ?>
                    <tr class="<?= $r['estado'] ?>">
                        <td><span class="id-badge">#<?= $r['id'] ?></span></td>
                        <td><strong><?= htmlspecialchars($r['propuesta']) ?></strong></td>
                        <td><?= htmlspecialchars($r['nombre_usuario']) ?></td>
                        <td><span class="motivo-text" title="<?= htmlspecialchars($r['motivo']) ?>"><?= htmlspecialchars($r['motivo']) ?></span></td>
                        <td><span class="fecha-text"><?= date('d/m/Y H:i', strtotime($r['fecha'])) ?></span></td>
                        <td>
                            <span class="estado-badge estado-<?= $r['estado'] ?>">
                                <?= $r['estado'] == 'pendiente' ? 'Pendiente' : 'Resuelto' ?>
                            </span>
                        </td>
                        <td>
                            <?php if($r['estado']=='pendiente'): ?>
                            <a href="?resuelto=<?= $r['id'] ?>" class="accion-btn">
                                <i class="fas fa-check"></i>
                                Marcar Resuelto
                            </a>
                            <?php else: ?>
                            <span style="color: #6c757d; font-style: italic;">
                                <i class="fas fa-check-circle"></i> Completado
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
