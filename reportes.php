<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Eliminar propuesta
if (isset($_GET['eliminar'])) {
    $propuesta_id = intval($_GET['eliminar']);
    
    // Eliminar tags asociados
    $stmt_tags = $conn->prepare("DELETE FROM propuesta_tag WHERE propuesta_id=?");
    $stmt_tags->bind_param("i", $propuesta_id);
    $stmt_tags->execute();
    
    // Eliminar reportes asociados
    $stmt_rep = $conn->prepare("DELETE FROM reportes WHERE propuesta_id=?");
    $stmt_rep->bind_param("i", $propuesta_id);
    $stmt_rep->execute();
    
    // Eliminar favoritos asociados
    $stmt_fav = $conn->prepare("DELETE FROM favoritos WHERE propuesta_id=?");
    $stmt_fav->bind_param("i", $propuesta_id);
    $stmt_fav->execute();
    
    // Eliminar la propuesta
    $stmt_prop = $conn->prepare("DELETE FROM propuestas WHERE id=?");
    $stmt_prop->bind_param("i", $propuesta_id);
    $stmt_prop->execute();
    
    header("Location: reportes.php");
    exit;
}

// Cambiar estado del reporte
if (isset($_GET['cambiar_estado'])) {
    $id = intval($_GET['cambiar_estado']);
    $estado = intval($_GET['estado']);
    
    // 0 = pendiente, 1 = resuelto, 2 = desactivado
    $estados_validos = ['pendiente', 'resuelto', 'desactivado'];
    if (isset($estados_validos[$estado])) {
        $nuevo_estado = $estados_validos[$estado];
        
        // Actualizar el estado del reporte
        $stmt = $conn->prepare("UPDATE reportes SET estado=? WHERE id=?");
        $stmt->bind_param("si", $nuevo_estado, $id);
        $stmt->execute();
        
        // Obtener el propuesta_id
        $stmt_prop = $conn->prepare("SELECT propuesta_id FROM reportes WHERE id=?");
        $stmt_prop->bind_param("i", $id);
        $stmt_prop->execute();
        $result_prop = $stmt_prop->get_result();
        
        if ($row_prop = $result_prop->fetch_assoc()) {
            $propuesta_id = $row_prop['propuesta_id'];
            
            // Si el estado es desactivado (2), desactivar la propuesta y eliminar de favoritos
            if ($estado == 2) {
                // Desactivar la propuesta
                $stmt_deactivate = $conn->prepare("UPDATE propuestas SET activo=0 WHERE id=?");
                $stmt_deactivate->bind_param("i", $propuesta_id);
                $stmt_deactivate->execute();
                
                // Eliminar de favoritos de todos los usuarios
                $stmt_delete_fav = $conn->prepare("DELETE FROM favoritos WHERE propuesta_id=?");
                $stmt_delete_fav->bind_param("i", $propuesta_id);
                $stmt_delete_fav->execute();
            }
            
            // Si el estado es pendiente (0) o resuelto (1), reactivar la propuesta
            if ($estado == 0 || $estado == 1) {
                $stmt_reactivate = $conn->prepare("UPDATE propuestas SET activo=1 WHERE id=?");
                $stmt_reactivate->bind_param("i", $propuesta_id);
                $stmt_reactivate->execute();
            }
        }
    }
    // Redirect para refrescar la página
    header("Location: reportes.php");
    exit;
}

$query = "SELECT r.*, 
          p.propuesta, p.activo as propuesta_activa, p.localidad, p.descripcion, 
          p.contacto, p.categoria, p.horario, p.salario,
          l.nombre_usuario, l.avatar_url as creador_avatar 
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
        background: #fff;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        overflow-x: hidden;
    }
    
    .container {
        max-width: 100%;
        margin: 0;
        background: #fff;
        border-radius: 0;
        box-shadow: none;
        overflow: hidden;
        min-height: 100vh;
        width: 100vw;
    }
    
    .header {
        background: linear-gradient(135deg, #7d5be6 0%, #6b4dd6 100%);
        color: white;
        padding: 30px;
        text-align: center;
        position: relative;
        width: 100%;
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
        padding: 30px 20px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .volver-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 25px;
        margin-left: 10px;
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
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 20px;
        width: 100%;
        padding: 0 10px;
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
        border-radius: 0;
        overflow-x: auto;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: none;
        border-top: 1px solid #e9ecef;
        border-bottom: 1px solid #e9ecef;
        width: 100%;
        max-width: 100%;
        margin: 0;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        min-width: 800px;
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
    
    .desactivado {
        background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%);
        border-left: 4px solid #6c757d;
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
    
    .estado-desactivado {
        background: #6c757d;
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
        margin: 2px;
    }
    
    .accion-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    }
    
    .accion-btn.danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }
    
    .accion-btn.danger:hover {
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    }
    
    .accion-btn.secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
    }
    
    .accion-btn.secondary:hover {
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
    }
    
    .accion-btn.delete {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }
    
    .accion-btn.delete:hover {
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
    }
    
    .acciones-group {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        align-items: center;
        min-width: 200px;
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
    
    .propuesta-link {
        transition: all 0.3s ease;
    }
    
    .propuesta-link:hover {
        text-decoration: underline;
        color: #6b4dd6 !important;
    }
    
    /* Modal de detalles */
    .modal-reportes {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    
    .modal-reportes.show {
        display: flex;
        animation: fadeIn 0.3s ease;
    }
    
    .modal-reportes-content {
        background: white;
        border-radius: 20px;
        max-width: 700px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.4s cubic-bezier(0.34,1.56,0.64,1);
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    
    .modal-reportes-header {
        background: linear-gradient(135deg, #7d5be6 0%, #6b4dd6 100%);
        color: white;
        padding: 30px;
        border-radius: 20px 20px 0 0;
        position: relative;
    }
    
    .modal-reportes-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
    }
    
    .modal-reportes-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 20px;
        transition: all 0.3s ease;
    }
    
    .modal-reportes-close:hover {
        background: rgba(255,255,255,0.3);
        transform: rotate(90deg);
    }
    
    .modal-reportes-body {
        padding: 30px;
    }
    
    .modal-reportes-body .detail-item {
        margin-bottom: 20px;
    }
    
    .modal-reportes-body .detail-label {
        font-weight: 600;
        color: #7d5be6;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .modal-reportes-body .detail-value {
        color: #495057;
        line-height: 1.6;
    }
    
    .modal-alert-inactive {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-left: 4px solid #dc3545;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .modal-alert-inactive i {
        color: #dc3545;
        font-size: 20px;
    }
    
    .modal-alert-inactive .alert-text {
        color: #991b1b;
        font-weight: 600;
    }
    
    .modal-reporte-info {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-left: 4px solid #f59e0b;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .modal-reporte-info .reporte-label {
        color: #92400e;
        font-weight: 700;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    
    .modal-reporte-info .reporte-text {
        color: #78350f;
        line-height: 1.5;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
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
        
        .stats-container {
            grid-template-columns: repeat(2, 1fr);
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
            
            $resueltos_result = $conn->query("SELECT COUNT(*) as resueltos FROM reportes WHERE estado='resuelto'");
            $resueltos = $resueltos_result->fetch_assoc()['resueltos'];
            
            $desactivados_result = $conn->query("SELECT COUNT(*) as desactivados FROM reportes WHERE estado='desactivado'");
            $desactivados = $desactivados_result->fetch_assoc()['desactivados'];
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
            <div class="stat-card">
                <div class="stat-number" style="color: #6c757d;"><?= $desactivados ?></div>
                <div class="stat-label">Desactivados</div>
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
                        <td>
                            <strong 
                                class="propuesta-link" 
                                onclick="openPropuestaModal(
                                    <?= $r['propuesta_id'] ?>,
                                    '<?= htmlspecialchars($r['propuesta'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($r['localidad'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($r['horario'] ?? '', ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($r['salario'] ?? '', ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($r['descripcion'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($r['contacto'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($r['categoria'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($r['nombre_usuario'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($r['creador_avatar'] ?? '', ENT_QUOTES) ?>',
                                    <?= $r['propuesta_activa'] ?>,
                                    '<?= htmlspecialchars($r['motivo'], ENT_QUOTES) ?>'
                                )"
                                style="cursor: pointer; color: #7d5be6;"
                                title="Click para ver detalles"
                            >
                                <?= htmlspecialchars($r['propuesta']) ?>
                            </strong>
                        </td>
                        <td><?= htmlspecialchars($r['nombre_usuario']) ?></td>
                        <td><span class="motivo-text" title="<?= htmlspecialchars($r['motivo']) ?>"><?= htmlspecialchars($r['motivo']) ?></span></td>
                        <td><span class="fecha-text"><?= date('d/m/Y H:i', strtotime($r['fecha'])) ?></span></td>
                        <td>
                            <span class="estado-badge estado-<?= $r['estado'] ?>">
                                <?php 
                                    if($r['estado'] == 'pendiente') echo 'Pendiente';
                                    elseif($r['estado'] == 'resuelto') echo 'Resuelto';
                                    else echo 'Desactivado';
                                ?>
                            </span>
                            <?php if($r['propuesta_activa'] == 0): ?>
                                <br><small style="color: #dc3545;"><i class="fas fa-ban"></i> Propuesta inactiva</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="acciones-group">
                                <?php if($r['estado'] == 'pendiente'): ?>
                                    <a href="?cambiar_estado=<?= $r['id'] ?>&estado=1" class="accion-btn" title="Marcar como resuelto">
                                        <i class="fas fa-check"></i>
                                        Resuelto
                                    </a>
                                    <a href="?cambiar_estado=<?= $r['id'] ?>&estado=2" class="accion-btn danger" title="Desactivar propuesta reportada" onclick="return confirm('¿Desactivar esta propuesta? Se ocultará de todos los usuarios.')">
                                        <i class="fas fa-ban"></i>
                                        Desactivar
                                    </a>
                                <?php elseif($r['estado'] == 'resuelto'): ?>
                                    <a href="?cambiar_estado=<?= $r['id'] ?>&estado=0" class="accion-btn secondary" title="Marcar como pendiente">
                                        <i class="fas fa-undo"></i>
                                        Reabrir
                                    </a>
                                <?php else: ?>
                                    <span style="color: #6c757d; font-style: italic;">
                                        <i class="fas fa-ban"></i> Propuesta desactivada
                                    </span>
                                    <a href="?cambiar_estado=<?= $r['id'] ?>&estado=0" class="accion-btn secondary" title="Reactivar como pendiente">
                                        <i class="fas fa-undo"></i>
                                        Reactivar
                                    </a>
                                <?php endif; ?>
                                <a href="?eliminar=<?= $r['propuesta_id'] ?>" class="accion-btn delete" title="Eliminar propuesta permanentemente" onclick="return confirm('⚠️ ADVERTENCIA: Esto eliminará permanentemente la propuesta #<?= $r['propuesta_id'] ?> y todos sus reportes, favoritos y tags asociados.\n\n¿Estás seguro de continuar?')">
                                    <i class="fas fa-trash"></i>
                                    Eliminar
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de detalles de propuesta -->
<div id="propuestaModal" class="modal-reportes">
    <div class="modal-reportes-content">
        <div class="modal-reportes-header">
            <h2 id="modalTitulo"></h2>
            <button class="modal-reportes-close" onclick="closePropuestaModal()">&times;</button>
        </div>
        <div class="modal-reportes-body">
            <!-- Alerta de propuesta inactiva -->
            <div id="modalAlertInactive" class="modal-alert-inactive" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="alert-text">Esta propuesta está DESACTIVADA y no es visible para los usuarios</div>
            </div>
            
            <!-- Información del reporte -->
            <div id="modalReporteInfo" class="modal-reporte-info">
                <div class="reporte-label">
                    <i class="fas fa-flag"></i>
                    MOTIVO DEL REPORTE
                </div>
                <div class="reporte-text" id="modalMotivo"></div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Localidad</div>
                <div class="detail-value" id="modalLocalidad"></div>
            </div>
            <div class="detail-item" id="modalHorarioContainer">
                <div class="detail-label"><i class="fas fa-clock"></i> Horario</div>
                <div class="detail-value" id="modalHorario"></div>
            </div>
            <div class="detail-item" id="modalSalarioContainer">
                <div class="detail-label"><i class="fas fa-dollar-sign"></i> Salario</div>
                <div class="detail-value" id="modalSalario"></div>
            </div>
            <div class="detail-item">
                <div class="detail-label"><i class="fas fa-align-left"></i> Descripción</div>
                <div class="detail-value" id="modalDescripcion"></div>
            </div>
            <div class="detail-item">
                <div class="detail-label"><i class="fas fa-envelope"></i> Contacto</div>
                <div class="detail-value" id="modalContacto"></div>
            </div>
            <div class="detail-item">
                <div class="detail-label"><i class="fas fa-tag"></i> Categoría</div>
                <div class="detail-value" id="modalCategoria"></div>
            </div>
            <div class="detail-item">
                <div class="detail-label"><i class="fas fa-user"></i> Publicado por</div>
                <div class="detail-value" id="modalCreador"></div>
            </div>
        </div>
    </div>
</div>

<script>
function openPropuestaModal(id, titulo, localidad, horario, salario, descripcion, contacto, categoria, creador, avatar, activo, motivo) {
    document.getElementById('modalTitulo').textContent = titulo;
    document.getElementById('modalLocalidad').textContent = localidad;
    document.getElementById('modalDescripcion').textContent = descripcion;
    document.getElementById('modalContacto').textContent = contacto;
    document.getElementById('modalCategoria').textContent = categoria;
    document.getElementById('modalCreador').textContent = creador;
    
    // Mostrar alerta si la propuesta está inactiva
    if (activo == 0) {
        document.getElementById('modalAlertInactive').style.display = 'flex';
    } else {
        document.getElementById('modalAlertInactive').style.display = 'none';
    }
    
    // Mostrar motivo del reporte
    document.getElementById('modalMotivo').textContent = motivo;
    
    // Mostrar u ocultar horario según si tiene valor
    if (horario && horario.trim() !== '') {
        document.getElementById('modalHorario').textContent = horario;
        document.getElementById('modalHorarioContainer').style.display = 'block';
    } else {
        document.getElementById('modalHorarioContainer').style.display = 'none';
    }
    
    // Mostrar u ocultar salario según si tiene valor
    if (salario && salario.trim() !== '') {
        document.getElementById('modalSalario').textContent = salario;
        document.getElementById('modalSalarioContainer').style.display = 'block';
    } else {
        document.getElementById('modalSalarioContainer').style.display = 'none';
    }
    
    const modal = document.getElementById('propuestaModal');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

function closePropuestaModal() {
    const modal = document.getElementById('propuestaModal');
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = 'none', 300);
}

// Cerrar modal al hacer click fuera
document.getElementById('propuestaModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePropuestaModal();
    }
});
</script>

</body>
</html>
