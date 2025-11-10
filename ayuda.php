<?php
session_start();
include 'conexion.php';

$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$nombre_usuario = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : 'Invitado';
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'usuario';
$avatar_url = 'avatars/default-avatar.png';

if ($usuario_id) {
    $stmt = $conn->prepare("SELECT avatar_url FROM login WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $avatar_url = !empty($row['avatar_url']) ? $row['avatar_url'] : $avatar_url;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayuda - PropuestaLaboral.io</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header h1 {
            color: #667eea;
            font-size: 2em;
        }

        .header-icon {
            font-size: 3em;
            color: #667eea;
        }

        .btn-volver {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-volver:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(102, 126, 234, 0.5);
        }

        .content {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .intro {
            text-align: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .intro h2 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 15px;
        }

        .intro p {
            color: #666;
            font-size: 1.1em;
            line-height: 1.6;
        }

        .faq-section {
            margin-bottom: 50px;
        }

        .faq-category {
            margin-bottom: 30px;
        }

        .category-title {
            color: #667eea;
            font-size: 1.5em;
            margin-bottom: 20px;
            padding-left: 15px;
            border-left: 4px solid #667eea;
        }

        .faq-item {
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .faq-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .faq-question {
            background: #f8f9fa;
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: #333;
            transition: all 0.3s;
        }

        .faq-question:hover {
            background: #667eea;
            color: white;
        }

        .faq-question i {
            transition: transform 0.3s;
        }

        .faq-question.active i {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s;
        }

        .faq-answer.active {
            padding: 20px;
            max-height: 500px;
        }

        .faq-answer p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .faq-answer ul {
            margin-left: 20px;
            color: #666;
        }

        .faq-answer li {
            margin-bottom: 8px;
        }

        .contact-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-top: 40px;
        }

        .contact-section h3 {
            font-size: 1.8em;
            margin-bottom: 15px;
        }

        .contact-section p {
            font-size: 1.1em;
            margin-bottom: 25px;
        }

        .contact-info {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1em;
        }

        .contact-item i {
            font-size: 1.5em;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .header-left {
                flex-direction: column;
            }

            .content {
                padding: 20px;
            }

            .contact-info {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <i class="fas fa-question-circle header-icon"></i>
                <h1>Centro de Ayuda</h1>
            </div>
            <a href="index.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al inicio
            </a>
        </div>

        <div class="content">
            <div class="intro">
                <h2>¿Cómo podemos ayudarte?</h2>
                <p>Encuentra respuestas a las preguntas más frecuentes sobre PropuestaLaboral.io</p>
            </div>

            <div class="faq-section">
                <!-- Primeros Pasos -->
                <div class="faq-category">
                    <h3 class="category-title"><i class="fas fa-rocket"></i> Primeros Pasos</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Cómo puedo registrarme en la plataforma?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Para registrarte en PropuestaLaboral.io:</p>
                            <ul>
                                <li>Haz clic en "Iniciar sesión" en el menú lateral</li>
                                <li>Selecciona "Regístrate" en la página de login</li>
                                <li>Completa el formulario con tu nombre de usuario, email y contraseña</li>
                                <li>Acepta los términos y condiciones</li>
                                <li>¡Listo! Ya puedes empezar a usar la plataforma</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Puedo usar la plataforma sin registrarme?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Sí, puedes navegar y ver las propuestas laborales sin necesidad de registrarte. Sin embargo, para crear propuestas, reportar contenido, guardar favoritos o personalizar tu perfil, necesitarás crear una cuenta.</p>
                        </div>
                    </div>
                </div>

                <!-- Propuestas Laborales -->
                <div class="faq-category">
                    <h3 class="category-title"><i class="fas fa-briefcase"></i> Propuestas Laborales</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Cómo puedo crear una nueva propuesta laboral?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Para crear una propuesta laboral:</p>
                            <ul>
                                <li>Inicia sesión en tu cuenta</li>
                                <li>Haz clic en el botón "Nueva Propuesta" en el menú lateral</li>
                                <li>Completa todos los campos: título, descripción, localidad, horario, salario, contacto y categoría</li>
                                <li>Puedes agregar hasta 3 etiquetas para categorizar mejor tu propuesta</li>
                                <li>Haz clic en "Crear Propuesta" y se publicará inmediatamente</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Cómo puedo contactar a un empleador?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Para contactar a un empleador:</p>
                            <ul>
                                <li>Haz clic en "Ver más" en cualquier propuesta</li>
                                <li>En el modal que se abre, encontrarás el botón "Copiar Contacto"</li>
                                <li>Al hacer clic, se copiará automáticamente la información de contacto al portapapeles</li>
                                <li>Podrás pegar esta información en tu aplicación de email, WhatsApp o cualquier otro medio para contactar al empleador</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Cómo puedo editar o eliminar mi propuesta?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Para gestionar tus propuestas:</p>
                            <ul>
                                <li>Ve a tu perfil haciendo clic en "Mi Perfil" en el menú lateral</li>
                                <li>En la pestaña "Mis Posts" verás todas las propuestas que has creado</li>
                                <li>Cada propuesta tiene botones de "Editar" (lápiz) y "Eliminar" (papelera)</li>
                                <li>Al editar, podrás modificar todos los campos de la propuesta</li>
                                <li>Al eliminar, la propuesta se borrará permanentemente</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Qué información debe incluir el campo "Contacto"?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>En el campo "Contacto" debes incluir la información necesaria para que los interesados puedan comunicarse contigo. Puede ser:</p>
                            <ul>
                                <li>Email: ejemplo@correo.com</li>
                                <li>Teléfono: +34 600 123 456</li>
                                <li>WhatsApp: +34 600 123 456</li>
                                <li>Formulario web: https://tuempresa.com/contacto</li>
                            </ul>
                            <p>Puedes incluir múltiples métodos de contacto separados por comas o saltos de línea.</p>
                        </div>
                    </div>
                </div>

                <!-- Perfil y Configuración -->
                <div class="faq-category">
                    <h3 class="category-title"><i class="fas fa-user-cog"></i> Perfil y Configuración</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Cómo puedo cambiar mi foto de perfil?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Para cambiar tu avatar:</p>
                            <ul>
                                <li>Ve a "Configuración" desde el menú lateral</li>
                                <li>En la sección "Foto de Perfil", haz clic en "Seleccionar archivo"</li>
                                <li>Elige una imagen (JPG, PNG o GIF)</li>
                                <li>Haz clic en "Actualizar Avatar"</li>
                                <li>Tu nueva foto se mostrará en todo el sitio</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Cómo cambio mi contraseña?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Para cambiar tu contraseña:</p>
                            <ul>
                                <li>Ve a "Configuración" desde el menú lateral</li>
                                <li>En la sección "Seguridad", ingresa tu contraseña actual</li>
                                <li>Ingresa tu nueva contraseña (mínimo 6 caracteres)</li>
                                <li>Confirma la nueva contraseña</li>
                                <li>Haz clic en "Cambiar Contraseña"</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Puedo cambiar mi nombre de usuario?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Sí, puedes cambiar tu nombre de usuario y email desde la sección "Configuración". Ve a "Información Personal" y actualiza los campos que desees modificar.</p>
                        </div>
                    </div>
                </div>

                <!-- Favoritos y Búsqueda -->
                <div class="faq-category">
                    <h3 class="category-title"><i class="fas fa-search"></i> Búsqueda y Favoritos</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Cómo puedo buscar propuestas específicas?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Puedes buscar propuestas de varias formas:</p>
                            <ul>
                                <li>Usa la barra de búsqueda en la parte superior para buscar por palabras clave</li>
                                <li>Filtra por categoría usando los botones de categoría</li>
                                <li>Haz clic en las etiquetas para ver propuestas relacionadas</li>
                                <li>Ordena por fecha o popularidad</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Dónde puedo ver mis propuestas favoritas?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Puedes ver tus favoritos en tu perfil:</p>
                            <ul>
                                <li>Haz clic en "Mi Perfil" en el menú lateral</li>
                                <li>Selecciona la pestaña "Favoritos"</li>
                                <li>Verás todas las propuestas que has marcado como favoritas</li>
                                <li>Puedes eliminarlas de favoritos haciendo clic en el ícono de corazón nuevamente</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Reportes y Moderación -->
                <div class="faq-category">
                    <h3 class="category-title"><i class="fas fa-flag"></i> Reportes y Moderación</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Cómo puedo reportar una propuesta inapropiada?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Si encuentras contenido inapropiado:</p>
                            <ul>
                                <li>Haz clic en el botón "Reportar" (bandera) en la propuesta</li>
                                <li>Selecciona el motivo del reporte</li>
                                <li>Proporciona detalles adicionales si es necesario</li>
                                <li>Envía el reporte</li>
                                <li>Nuestro equipo de moderación lo revisará lo antes posible</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Qué tipo de contenido está prohibido?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Está prohibido publicar:</p>
                            <ul>
                                <li>Ofertas laborales falsas o fraudulentas</li>
                                <li>Contenido discriminatorio u ofensivo</li>
                                <li>Información de contacto falsa</li>
                                <li>Spam o publicidad no relacionada con empleo</li>
                                <li>Contenido que viole los derechos de autor</li>
                                <li>Información personal de terceros sin consentimiento</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Qué pasa con las propuestas reportadas?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Cuando una propuesta es reportada:</p>
                            <ul>
                                <li>Los administradores revisan el reporte</li>
                                <li>Si es válido, pueden marcarla como "Resuelto" (si se solucionó el problema)</li>
                                <li>O pueden "Desactivar" la propuesta (la oculta de la plataforma)</li>
                                <li>Las propuestas desactivadas se eliminan automáticamente de los favoritos de todos los usuarios</li>
                                <li>Los administradores pueden reactivar propuestas si fue un error</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Problemas Técnicos -->
                <div class="faq-category">
                    <h3 class="category-title"><i class="fas fa-tools"></i> Problemas Técnicos</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>No puedo iniciar sesión, ¿qué hago?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Si tienes problemas para iniciar sesión:</p>
                            <ul>
                                <li>Verifica que tu nombre de usuario y contraseña sean correctos</li>
                                <li>Asegúrate de que las mayúsculas/minúsculas sean correctas</li>
                                <li>Limpia la caché y cookies de tu navegador</li>
                                <li>Intenta desde otro navegador</li>
                                <li>Si olvidaste tu contraseña, contacta al soporte</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>¿Por qué no puedo copiar el contacto de una propuesta?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Si el botón "Copiar Contacto" no funciona:</p>
                            <ul>
                                <li>Asegúrate de estar usando un navegador moderno (Chrome, Firefox, Edge, Safari)</li>
                                <li>Algunos navegadores antiguos no soportan la función de copiar automáticamente</li>
                                <li>Puedes copiar manualmente seleccionando el texto del contacto</li>
                                <li>Verifica que tu navegador tenga permisos de portapapeles</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>La imagen que subí no se muestra correctamente</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Si tu avatar no se carga:</p>
                            <ul>
                                <li>Verifica que la imagen sea JPG, PNG o GIF</li>
                                <li>Asegúrate de que el tamaño del archivo no sea muy grande (máximo 5MB recomendado)</li>
                                <li>Intenta con una imagen diferente</li>
                                <li>Refresca la página (Ctrl+F5) para limpiar la caché</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Contacto -->
            <div class="contact-section">
                <h3><i class="fas fa-envelope"></i> ¿Necesitas más ayuda?</h3>
                <p>Si no encontraste la respuesta a tu pregunta, contáctanos directamente:</p>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>soporte@propuestalaboral.io</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+34 900 123 456</span>
                    </div>
                    <div class="contact-item">
                        <i class="fab fa-whatsapp"></i>
                        <span>+34 600 123 456</span>
                    </div>
                </div>

                <p style="margin-top: 30px; font-size: 0.95em; opacity: 0.9;">
                    Horario de atención: Lunes a Viernes de 9:00 a 18:00 (hora de España)
                </p>
            </div>
        </div>
    </div>

    <script>
        function toggleFaq(element) {
            const faqItem = element.closest('.faq-item');
            const answer = faqItem.querySelector('.faq-answer');
            const question = faqItem.querySelector('.faq-question');
            
            // Cerrar todas las demás preguntas
            document.querySelectorAll('.faq-item').forEach(item => {
                if (item !== faqItem) {
                    item.querySelector('.faq-answer').classList.remove('active');
                    item.querySelector('.faq-question').classList.remove('active');
                }
            });
            
            // Toggle la pregunta actual
            answer.classList.toggle('active');
            question.classList.toggle('active');
        }
    </script>
</body>
</html>
