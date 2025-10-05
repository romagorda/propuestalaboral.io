-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-09-2025 a las 05:00:05
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `trabajo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `propuesta_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login`
--

CREATE TABLE `login` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `gmail` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `rol` enum('usuario','admin') NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `login`
--

INSERT INTO `login` (`id`, `nombre_usuario`, `gmail`, `contraseña`, `rol`) VALUES
(5, 'romagorda', 'romanquiroga456@gmail.com', '$2y$10$9/NMyKCKicqWQQRd8cMdHOEs6h6qMeJv8hfbwpzSZQa9A4yH056P2', 'admin'),
(6, 'troll', 'troll345@gmail.com', '$2y$10$ADEgy.sFclKKHxPepYJcSeq9O2GdXEf9XRQa0eF3AzVtopJBB1SeS', 'usuario'),
(7, 'pelotudo', 'pelotudo123@gmail.com', '$2y$10$29dn1DOMzhmpwJkivt70zOJ7HwxPddK0dovon5f5mxjpWbCp9dYgO', 'usuario'),
(8, 'rata', 'rata123@gmail.com', '$2y$10$t95AgtU.8zeHuZ4/uZA/IePmlXituj33/M954TvxE3BvY8M4NwssC', 'usuario'),
(9, 'roberto', 'robertito@gmail.com', '$2y$10$ygK94ydcojk1ea0yFDjMd.feCCoxSqH7znhyo75iIl/FTnBRhzql.', 'usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propuestas`
--

CREATE TABLE `propuestas` (
  `id` int(11) NOT NULL,
  `propuesta` varchar(255) NOT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `contacto` varchar(150) DEFAULT NULL,
  `categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- SI NO EXISTE la columna `usuario_id` en la tabla `propuestas`, ejecutar:
-- ALTER TABLE `propuestas` ADD COLUMN `usuario_id` INT(11) NULL AFTER `categoria`;
-- (luego puedes poblarla con UPDATE si necesitas asociar propuestas existentes a usuarios)

--
-- Volcado de datos para la tabla `propuestas`
--

INSERT INTO `propuestas` (`id`, `propuesta`, `localidad`, `descripcion`, `contacto`, `categoria`) VALUES
(2, 'PROFESOR', 'Santa Teresita', 'SE NECESITA PROFE DE EDUCACION FISICA EN LA 12', '2257500653', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propuesta_tag`
--

CREATE TABLE `propuesta_tag` (
  `propuesta_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tags`
--

INSERT INTO `tags` (`id`, `nombre`, `categoria`) VALUES
(1, 'PHP', 'Tecnología'),
(2, 'JavaScript', 'Tecnología'),
(3, 'Python', 'Tecnología'),
(4, 'HTML', 'Tecnología'),
(5, 'CSS', 'Tecnología'),
(6, 'React', 'Tecnología'),
(7, 'Node.js', 'Tecnología'),
(8, 'Angular', 'Tecnología'),
(9, 'Vue.js', 'Tecnología'),
(10, 'Laravel', 'Tecnología'),
(11, 'Django', 'Tecnología'),
(12, 'Flask', 'Tecnología'),
(13, 'SQL', 'Tecnología'),
(14, 'NoSQL', 'Tecnología'),
(15, 'MongoDB', 'Tecnología'),
(16, 'MySQL', 'Tecnología'),
(17, 'PostgreSQL', 'Tecnología'),
(18, 'Java', 'Tecnología'),
(19, 'C#', 'Tecnología'),
(20, '.NET', 'Tecnología'),
(21, 'Ruby', 'Tecnología'),
(22, 'Rails', 'Tecnología'),
(23, 'Swift', 'Tecnología'),
(24, 'Kotlin', 'Tecnología'),
(25, 'Android', 'Tecnología'),
(26, 'iOS', 'Tecnología'),
(27, 'Firebase', 'Tecnología'),
(28, 'AWS', 'Tecnología'),
(29, 'Azure', 'Tecnología'),
(30, 'Google Cloud', 'Tecnología'),
(31, 'DevOps', 'Tecnología'),
(32, 'Docker', 'Tecnología'),
(33, 'Kubernetes', 'Tecnología'),
(34, 'Git', 'Tecnología'),
(35, 'GitHub', 'Tecnología'),
(36, 'Testing', 'Tecnología'),
(37, 'Jest', 'Tecnología'),
(38, 'Mocha', 'Tecnología'),
(39, 'Selenium', 'Tecnología'),
(40, 'QA', 'Tecnología'),
(41, 'Scrum', 'Tecnología'),
(42, 'Agile', 'Tecnología'),
(43, 'UX', 'Tecnología'),
(44, 'UI', 'Tecnología'),
(45, 'Web', 'Tecnología'),
(46, 'Backend', 'Tecnología'),
(47, 'Frontend', 'Tecnología'),
(48, 'Fullstack', 'Tecnología'),
(49, 'API', 'Tecnología'),
(50, 'REST', 'Tecnología'),
(51, 'Photoshop', 'Diseño'),
(52, 'Illustrator', 'Diseño'),
(53, 'InDesign', 'Diseño'),
(54, 'CorelDRAW', 'Diseño'),
(55, 'Sketch', 'Diseño'),
(56, 'Figma', 'Diseño'),
(57, 'XD', 'Diseño'),
(58, 'Canva', 'Diseño'),
(59, 'Branding', 'Diseño'),
(60, 'Identidad', 'Diseño'),
(61, 'Tipografía', 'Diseño'),
(62, 'Color', 'Diseño'),
(63, 'Maquetación', 'Diseño'),
(64, 'Packaging', 'Diseño'),
(65, 'Editorial', 'Diseño'),
(66, 'Publicidad', 'Diseño'),
(67, 'Carteles', 'Diseño'),
(68, 'Logotipos', 'Diseño'),
(69, 'Ilustración', 'Diseño'),
(70, 'Animación', 'Diseño'),
(71, '3D', 'Diseño'),
(72, 'Modelado', 'Diseño'),
(73, 'Render', 'Diseño'),
(74, 'After Effects', 'Diseño'),
(75, 'Premiere', 'Diseño'),
(76, 'Video', 'Diseño'),
(77, 'Fotografía', 'Diseño'),
(78, 'Retoque', 'Diseño'),
(79, 'Web', 'Diseño'),
(80, 'UI', 'Diseño'),
(81, 'UX', 'Diseño'),
(82, 'Wireframe', 'Diseño'),
(83, 'Prototipo', 'Diseño'),
(84, 'Diseño gráfico', 'Diseño'),
(85, 'Diseño web', 'Diseño'),
(86, 'Diseño editorial', 'Diseño'),
(87, 'Diseño publicitario', 'Diseño'),
(88, 'Diseño de producto', 'Diseño'),
(89, 'Diseño industrial', 'Diseño'),
(90, 'Diseño de moda', 'Diseño'),
(91, 'Diseño de interiores', 'Diseño'),
(92, 'Diseño de envases', 'Diseño'),
(93, 'Diseño de apps', 'Diseño'),
(94, 'Diseño digital', 'Diseño'),
(95, 'Diseño corporativo', 'Diseño'),
(96, 'Diseño social', 'Diseño'),
(97, 'Diseño sostenible', 'Diseño'),
(98, 'Diseño interactivo', 'Diseño'),
(99, 'Diseño multimedia', 'Diseño'),
(100, 'Diseño estratégico', 'Diseño'),
(101, 'Contabilidad', 'Finanzas'),
(102, 'Impuestos', 'Finanzas'),
(103, 'Auditoría', 'Finanzas'),
(104, 'Tesorería', 'Finanzas'),
(105, 'Presupuesto', 'Finanzas'),
(106, 'Cuentas', 'Finanzas'),
(107, 'Balance', 'Finanzas'),
(108, 'Facturación', 'Finanzas'),
(109, 'Finanzas personales', 'Finanzas'),
(110, 'Finanzas corporativas', 'Finanzas'),
(111, 'Inversiones', 'Finanzas'),
(112, 'Bolsa', 'Finanzas'),
(113, 'Créditos', 'Finanzas'),
(114, 'Deuda', 'Finanzas'),
(115, 'Gestión financiera', 'Finanzas'),
(116, 'Análisis financiero', 'Finanzas'),
(117, 'Riesgo', 'Finanzas'),
(118, 'Seguros', 'Finanzas'),
(119, 'Banca', 'Finanzas'),
(120, 'Cartera', 'Finanzas'),
(121, 'Gestión de activos', 'Finanzas'),
(122, 'Gestión de pasivos', 'Finanzas'),
(123, 'Planificación', 'Finanzas'),
(124, 'Forecast', 'Finanzas'),
(125, 'Cash flow', 'Finanzas'),
(126, 'Costos', 'Finanzas'),
(127, 'Control', 'Finanzas'),
(128, 'Cierre', 'Finanzas'),
(129, 'Pagos', 'Finanzas'),
(130, 'Cobros', 'Finanzas'),
(131, 'Fintech', 'Finanzas'),
(132, 'Criptomonedas', 'Finanzas'),
(133, 'Blockchain', 'Finanzas'),
(134, 'Finanzas internacionales', 'Finanzas'),
(135, 'Finanzas públicas', 'Finanzas'),
(136, 'Finanzas privadas', 'Finanzas'),
(137, 'Gestión patrimonial', 'Finanzas'),
(138, 'Gestión fiscal', 'Finanzas'),
(139, 'Gestión presupuestaria', 'Finanzas'),
(140, 'Gestión contable', 'Finanzas'),
(141, 'Gestión bancaria', 'Finanzas'),
(142, 'Gestión de riesgos', 'Finanzas'),
(143, 'Gestión de inversiones', 'Finanzas'),
(144, 'Gestión de créditos', 'Finanzas'),
(145, 'Gestión de seguros', 'Finanzas'),
(146, 'Gestión de tesorería', 'Finanzas'),
(147, 'Gestión de cuentas', 'Finanzas'),
(148, 'Gestión de facturación', 'Finanzas'),
(149, 'Gestión de auditoría', 'Finanzas'),
(150, 'Gestión de impuestos', 'Finanzas'),
(151, 'Medicina', 'Salud'),
(152, 'Enfermería', 'Salud'),
(153, 'Farmacia', 'Salud'),
(154, 'Odontología', 'Salud'),
(155, 'Psicología', 'Salud'),
(156, 'Fisioterapia', 'Salud'),
(157, 'Nutrición', 'Salud'),
(158, 'Ginecología', 'Salud'),
(159, 'Pediatría', 'Salud'),
(160, 'Cardiología', 'Salud'),
(161, 'Traumatología', 'Salud'),
(162, 'Dermatología', 'Salud'),
(163, 'Oncología', 'Salud'),
(164, 'Neurología', 'Salud'),
(165, 'Psiquiatría', 'Salud'),
(166, 'Radiología', 'Salud'),
(167, 'Laboratorio', 'Salud'),
(168, 'Urgencias', 'Salud'),
(169, 'Cirugía', 'Salud'),
(170, 'Anestesia', 'Salud'),
(171, 'Terapia', 'Salud'),
(172, 'Rehabilitación', 'Salud'),
(173, 'Salud pública', 'Salud'),
(174, 'Salud mental', 'Salud'),
(175, 'Salud ocupacional', 'Salud'),
(176, 'Salud familiar', 'Salud'),
(177, 'Salud infantil', 'Salud'),
(178, 'Salud preventiva', 'Salud'),
(179, 'Salud comunitaria', 'Salud'),
(180, 'Salud sexual', 'Salud'),
(181, 'Salud reproductiva', 'Salud'),
(182, 'Salud ambiental', 'Salud'),
(183, 'Salud laboral', 'Salud'),
(184, 'Salud escolar', 'Salud'),
(185, 'Salud geriátrica', 'Salud'),
(186, 'Salud visual', 'Salud'),
(187, 'Salud auditiva', 'Salud'),
(188, 'Salud bucal', 'Salud'),
(189, 'Salud cardiovascular', 'Salud'),
(190, 'Salud respiratoria', 'Salud'),
(191, 'Salud renal', 'Salud'),
(192, 'Salud digestiva', 'Salud'),
(193, 'Salud muscular', 'Salud'),
(194, 'Salud ósea', 'Salud'),
(195, 'Salud inmunológica', 'Salud'),
(196, 'Salud endocrina', 'Salud'),
(197, 'Salud metabólica', 'Salud'),
(198, 'Salud social', 'Salud'),
(199, 'Salud integral', 'Salud'),
(200, 'Salud clínica', 'Salud'),
(201, 'Docencia', 'Educación'),
(202, 'Pedagogía', 'Educación'),
(203, 'Didáctica', 'Educación'),
(204, 'Educación infantil', 'Educación'),
(205, 'Educación primaria', 'Educación'),
(206, 'Educación secundaria', 'Educación'),
(207, 'Educación superior', 'Educación'),
(208, 'Educación especial', 'Educación'),
(209, 'Educación física', 'Educación'),
(210, 'Educación artística', 'Educación'),
(211, 'Educación musical', 'Educación'),
(212, 'Educación tecnológica', 'Educación'),
(213, 'Educación ambiental', 'Educación'),
(214, 'Educación emocional', 'Educación'),
(215, 'Educación sexual', 'Educación'),
(216, 'Educación cívica', 'Educación'),
(217, 'Educación ética', 'Educación'),
(218, 'Educación religiosa', 'Educación'),
(219, 'Educación intercultural', 'Educación'),
(220, 'Educación inclusiva', 'Educación'),
(221, 'Educación virtual', 'Educación'),
(222, 'Educación a distancia', 'Educación'),
(223, 'Educación presencial', 'Educación'),
(224, 'Educación bilingüe', 'Educación'),
(225, 'Educación rural', 'Educación'),
(226, 'Educación urbana', 'Educación'),
(227, 'Educación pública', 'Educación'),
(228, 'Educación privada', 'Educación'),
(229, 'Educación comunitaria', 'Educación'),
(230, 'Educación alternativa', 'Educación'),
(231, 'Educación formal', 'Educación'),
(232, 'Educación informal', 'Educación'),
(233, 'Educación no formal', 'Educación'),
(234, 'Educación continua', 'Educación'),
(235, 'Educación permanente', 'Educación'),
(236, 'Educación profesional', 'Educación'),
(237, 'Educación técnica', 'Educación'),
(238, 'Educación universitaria', 'Educación'),
(239, 'Educación empresarial', 'Educación'),
(240, 'Educación social', 'Educación'),
(241, 'Educación familiar', 'Educación'),
(242, 'Educación para la salud', 'Educación'),
(243, 'Educación para el trabajo', 'Educación'),
(244, 'Educación para la paz', 'Educación'),
(245, 'Educación para la ciudadanía', 'Educación'),
(246, 'Educación para el desarrollo', 'Educación'),
(247, 'Educación para la diversidad', 'Educación'),
(248, 'Educación para la sostenibilidad', 'Educación'),
(249, 'Educación para la igualdad', 'Educación'),
(250, 'Educación para la creatividad', 'Educación'),
(251, 'Gestión', 'Administración'),
(252, 'Organización', 'Administración'),
(253, 'Planificación', 'Administración'),
(254, 'Dirección', 'Administración'),
(255, 'Control', 'Administración'),
(256, 'Recursos', 'Administración'),
(257, 'Procesos', 'Administración'),
(258, 'Estrategia', 'Administración'),
(259, 'Liderazgo', 'Administración'),
(260, 'Toma de decisiones', 'Administración'),
(261, 'Comunicación', 'Administración'),
(262, 'Trabajo en equipo', 'Administración'),
(263, 'Motivación', 'Administración'),
(264, 'Evaluación', 'Administración'),
(265, 'Supervisión', 'Administración'),
(266, 'Delegación', 'Administración'),
(267, 'Coordinación', 'Administración'),
(268, 'Gestión de proyectos', 'Administración'),
(269, 'Gestión de calidad', 'Administración'),
(270, 'Gestión de recursos humanos', 'Administración'),
(271, 'Gestión financiera', 'Administración'),
(272, 'Gestión comercial', 'Administración'),
(273, 'Gestión logística', 'Administración'),
(274, 'Gestión de compras', 'Administración'),
(275, 'Gestión de ventas', 'Administración'),
(276, 'Gestión de inventarios', 'Administración'),
(277, 'Gestión de almacenes', 'Administración'),
(278, 'Gestión de contratos', 'Administración'),
(279, 'Gestión de proveedores', 'Administración'),
(280, 'Gestión de clientes', 'Administración'),
(281, 'Gestión documental', 'Administración'),
(282, 'Gestión administrativa', 'Administración'),
(283, 'Gestión operativa', 'Administración'),
(284, 'Gestión estratégica', 'Administración'),
(285, 'Gestión táctica', 'Administración'),
(286, 'Gestión organizacional', 'Administración'),
(287, 'Gestión institucional', 'Administración'),
(288, 'Gestión pública', 'Administración'),
(289, 'Gestión privada', 'Administración'),
(290, 'Gestión internacional', 'Administración'),
(291, 'Gestión local', 'Administración'),
(292, 'Gestión regional', 'Administración'),
(293, 'Gestión nacional', 'Administración'),
(294, 'Gestión global', 'Administración'),
(295, 'Gestión empresarial', 'Administración'),
(296, 'Gestión social', 'Administración'),
(297, 'Gestión ambiental', 'Administración'),
(298, 'Gestión tecnológica', 'Administración'),
(299, 'Gestión de riesgos', 'Administración'),
(300, 'Gestión de innovación', 'Administración'),
(301, 'Comercial', 'Ventas'),
(302, 'Negociación', 'Ventas'),
(303, 'Cierre', 'Ventas'),
(304, 'Prospección', 'Ventas'),
(305, 'Presentación', 'Ventas'),
(306, 'Seguimiento', 'Ventas'),
(307, 'CRM', 'Ventas'),
(308, 'Clientes', 'Ventas'),
(309, 'Fidelización', 'Ventas'),
(310, 'Estrategia', 'Ventas'),
(311, 'Objetivos', 'Ventas'),
(312, 'Resultados', 'Ventas'),
(313, 'Indicadores', 'Ventas'),
(314, 'Ventas online', 'Ventas'),
(315, 'Ventas directas', 'Ventas'),
(316, 'Ventas telefónicas', 'Ventas'),
(317, 'Ventas presenciales', 'Ventas'),
(318, 'Ventas B2B', 'Ventas'),
(319, 'Ventas B2C', 'Ventas'),
(320, 'Ventas internacionales', 'Ventas'),
(321, 'Ventas nacionales', 'Ventas'),
(322, 'Ventas regionales', 'Ventas'),
(323, 'Ventas locales', 'Ventas'),
(324, 'Ventas minoristas', 'Ventas'),
(325, 'Ventas mayoristas', 'Ventas'),
(326, 'Ventas de servicios', 'Ventas'),
(327, 'Ventas de productos', 'Ventas'),
(328, 'Ventas técnicas', 'Ventas'),
(329, 'Ventas consultivas', 'Ventas'),
(330, 'Ventas relacionales', 'Ventas'),
(331, 'Ventas emocionales', 'Ventas'),
(332, 'Ventas estratégicas', 'Ventas'),
(333, 'Ventas operativas', 'Ventas'),
(334, 'Ventas institucionales', 'Ventas'),
(335, 'Ventas públicas', 'Ventas'),
(336, 'Ventas privadas', 'Ventas'),
(337, 'Ventas corporativas', 'Ventas'),
(338, 'Ventas empresariales', 'Ventas'),
(339, 'Ventas sociales', 'Ventas'),
(340, 'Ventas sostenibles', 'Ventas'),
(341, 'Ventas digitales', 'Ventas'),
(342, 'Ventas tradicionales', 'Ventas'),
(343, 'Ventas innovadoras', 'Ventas'),
(344, 'Ventas de valor', 'Ventas'),
(345, 'Ventas de soluciones', 'Ventas'),
(346, 'Ventas de experiencia', 'Ventas'),
(347, 'Ventas de confianza', 'Ventas'),
(348, 'Ventas de calidad', 'Ventas'),
(349, 'Ventas de resultados', 'Ventas'),
(350, 'Ventas de éxito', 'Ventas'),
(351, 'Abogado', 'Legal'),
(352, 'Juez', 'Legal'),
(353, 'Fiscal', 'Legal'),
(354, 'Defensor', 'Legal'),
(355, 'Notario', 'Legal'),
(356, 'Contratos', 'Legal'),
(357, 'Testamentos', 'Legal'),
(358, 'Herencias', 'Legal'),
(359, 'Divorcios', 'Legal'),
(360, 'Demandas', 'Legal'),
(361, 'Juicios', 'Legal'),
(362, 'Sentencias', 'Legal'),
(363, 'Leyes', 'Legal'),
(364, 'Normas', 'Legal'),
(365, 'Reglamentos', 'Legal'),
(366, 'Constitución', 'Legal'),
(367, 'Derecho civil', 'Legal'),
(368, 'Derecho penal', 'Legal'),
(369, 'Derecho laboral', 'Legal'),
(370, 'Derecho mercantil', 'Legal'),
(371, 'Derecho internacional', 'Legal'),
(372, 'Derecho administrativo', 'Legal'),
(373, 'Derecho ambiental', 'Legal'),
(374, 'Derecho tributario', 'Legal'),
(375, 'Derecho de familia', 'Legal'),
(376, 'Derecho de sucesiones', 'Legal'),
(377, 'Derecho de propiedad', 'Legal'),
(378, 'Derecho de contratos', 'Legal'),
(379, 'Derecho de sociedades', 'Legal'),
(380, 'Derecho de seguros', 'Legal'),
(381, 'Derecho de consumo', 'Legal'),
(382, 'Derecho de competencia', 'Legal'),
(383, 'Derecho de transporte', 'Legal'),
(384, 'Derecho de telecomunicaciones', 'Legal'),
(385, 'Derecho de tecnología', 'Legal'),
(386, 'Derecho de salud', 'Legal'),
(387, 'Derecho de educación', 'Legal'),
(388, 'Derecho de inmigración', 'Legal'),
(389, 'Derecho de extranjería', 'Legal'),
(390, 'Derecho de protección de datos', 'Legal'),
(391, 'Derecho de propiedad intelectual', 'Legal'),
(392, 'Derecho de patentes', 'Legal'),
(393, 'Derecho de marcas', 'Legal'),
(394, 'Derecho de autor', 'Legal'),
(395, 'Derecho de imagen', 'Legal'),
(396, 'Derecho de internet', 'Legal'),
(397, 'Derecho de comercio electrónico', 'Legal'),
(398, 'Derecho de seguridad', 'Legal'),
(399, 'Derecho de responsabilidad', 'Legal'),
(400, 'Derecho de garantías', 'Legal'),
(401, 'Selección', 'Recursos Humanos'),
(402, 'Reclutamiento', 'Recursos Humanos'),
(403, 'Entrevistas', 'Recursos Humanos'),
(404, 'Contratación', 'Recursos Humanos'),
(405, 'Capacitación', 'Recursos Humanos'),
(406, 'Formación', 'Recursos Humanos'),
(407, 'Desarrollo', 'Recursos Humanos'),
(408, 'Evaluación', 'Recursos Humanos'),
(409, 'Desempeño', 'Recursos Humanos'),
(410, 'Clima laboral', 'Recursos Humanos'),
(411, 'Compensación', 'Recursos Humanos'),
(412, 'Beneficios', 'Recursos Humanos'),
(413, 'Salario', 'Recursos Humanos'),
(414, 'Nomina', 'Recursos Humanos'),
(415, 'Relaciones laborales', 'Recursos Humanos'),
(416, 'Gestión de talento', 'Recursos Humanos'),
(417, 'Gestión de personas', 'Recursos Humanos'),
(418, 'Gestión de equipos', 'Recursos Humanos'),
(419, 'Gestión de liderazgo', 'Recursos Humanos'),
(420, 'Gestión de cultura', 'Recursos Humanos'),
(421, 'Gestión de diversidad', 'Recursos Humanos'),
(422, 'Gestión de inclusión', 'Recursos Humanos'),
(423, 'Gestión de igualdad', 'Recursos Humanos'),
(424, 'Gestión de género', 'Recursos Humanos'),
(425, 'Gestión de conflictos', 'Recursos Humanos'),
(426, 'Gestión de comunicación', 'Recursos Humanos'),
(427, 'Gestión de motivación', 'Recursos Humanos'),
(428, 'Gestión de formación', 'Recursos Humanos'),
(429, 'Gestión de capacitación', 'Recursos Humanos'),
(430, 'Gestión de desarrollo', 'Recursos Humanos'),
(431, 'Gestión de evaluación', 'Recursos Humanos'),
(432, 'Gestión de desempeño', 'Recursos Humanos'),
(433, 'Gestión de clima', 'Recursos Humanos'),
(434, 'Gestión de compensación', 'Recursos Humanos'),
(435, 'Gestión de beneficios', 'Recursos Humanos'),
(436, 'Gestión de salario', 'Recursos Humanos'),
(437, 'Gestión de nómina', 'Recursos Humanos'),
(438, 'Gestión de relaciones', 'Recursos Humanos'),
(439, 'Gestión de talento humano', 'Recursos Humanos'),
(440, 'Gestión de recursos humanos', 'Recursos Humanos'),
(441, 'Gestión de personal', 'Recursos Humanos'),
(442, 'Gestión de empleados', 'Recursos Humanos'),
(443, 'Gestión de trabajadores', 'Recursos Humanos'),
(444, 'Gestión de colaboradores', 'Recursos Humanos'),
(445, 'Gestión de directivos', 'Recursos Humanos'),
(446, 'Gestión de mandos', 'Recursos Humanos'),
(447, 'Gestión de supervisores', 'Recursos Humanos'),
(448, 'Gestión de gerentes', 'Recursos Humanos'),
(449, 'Gestión de líderes', 'Recursos Humanos'),
(450, 'Gestión de jefes', 'Recursos Humanos'),
(451, 'Arquitectura', 'Construcción'),
(452, 'Ingeniería', 'Construcción'),
(453, 'Obra', 'Construcción'),
(454, 'Proyecto', 'Construcción'),
(455, 'Planos', 'Construcción'),
(456, 'Materiales', 'Construcción'),
(457, 'Estructuras', 'Construcción'),
(458, 'Cimientos', 'Construcción'),
(459, 'Hormigón', 'Construcción'),
(460, 'Ladrillos', 'Construcción'),
(461, 'Acero', 'Construcción'),
(462, 'Madera', 'Construcción'),
(463, 'Vidrio', 'Construcción'),
(464, 'Albañilería', 'Construcción'),
(465, 'Electricidad', 'Construcción'),
(466, 'Fontanería', 'Construcción'),
(467, 'Pintura', 'Construcción'),
(468, 'Revestimiento', 'Construcción'),
(469, 'Aislamiento', 'Construcción'),
(470, 'Impermeabilización', 'Construcción'),
(471, 'Techos', 'Construcción'),
(472, 'Fachadas', 'Construcción'),
(473, 'Interiores', 'Construcción'),
(474, 'Exteriores', 'Construcción'),
(475, 'Urbanismo', 'Construcción'),
(476, 'Infraestructura', 'Construcción'),
(477, 'Vivienda', 'Construcción'),
(478, 'Edificios', 'Construcción'),
(479, 'Puentes', 'Construcción'),
(480, 'Carreteras', 'Construcción'),
(481, 'Ferrocarriles', 'Construcción'),
(482, 'Túneles', 'Construcción'),
(483, 'Instalaciones', 'Construcción'),
(484, 'Supervisión', 'Construcción'),
(485, 'Dirección', 'Construcción'),
(486, 'Gestión de obra', 'Construcción'),
(487, 'Gestión de proyectos', 'Construcción'),
(488, 'Gestión de materiales', 'Construcción'),
(489, 'Gestión de personal', 'Construcción'),
(490, 'Gestión de seguridad', 'Construcción'),
(491, 'Gestión de calidad', 'Construcción'),
(492, 'Gestión de costos', 'Construcción'),
(493, 'Gestión de plazos', 'Construcción'),
(494, 'Gestión de riesgos', 'Construcción'),
(495, 'Gestión de contratos', 'Construcción'),
(496, 'Gestión de proveedores', 'Construcción'),
(497, 'Gestión de clientes', 'Construcción'),
(498, 'Gestión de maquinaria', 'Construcción'),
(499, 'Gestión de herramientas', 'Construcción'),
(500, 'Gestión de recursos', 'Construcción'),
(501, 'Photoshop', 'Diseño'),
(502, 'Illustrator', 'Diseño'),
(503, 'InDesign', 'Diseño'),
(504, 'CorelDRAW', 'Diseño'),
(505, 'Sketch', 'Diseño'),
(506, 'Figma', 'Diseño'),
(507, 'XD', 'Diseño'),
(508, 'Canva', 'Diseño'),
(509, 'Branding', 'Diseño'),
(510, 'Identidad', 'Diseño'),
(511, 'Tipografía', 'Diseño'),
(512, 'Color', 'Diseño'),
(513, 'Maquetación', 'Diseño'),
(514, 'Packaging', 'Diseño'),
(515, 'Editorial', 'Diseño'),
(516, 'Publicidad', 'Diseño'),
(517, 'Carteles', 'Diseño'),
(518, 'Logotipos', 'Diseño'),
(519, 'Ilustración', 'Diseño'),
(520, 'Animación', 'Diseño'),
(521, '3D', 'Diseño'),
(522, 'Modelado', 'Diseño'),
(523, 'Render', 'Diseño'),
(524, 'After Effects', 'Diseño'),
(525, 'Premiere', 'Diseño'),
(526, 'Video', 'Diseño'),
(527, 'Fotografía', 'Diseño'),
(528, 'Retoque', 'Diseño'),
(529, 'Web', 'Diseño'),
(530, 'UI', 'Diseño'),
(531, 'UX', 'Diseño'),
(532, 'Wireframe', 'Diseño'),
(533, 'Prototipo', 'Diseño'),
(534, 'Diseño gráfico', 'Diseño'),
(535, 'Diseño web', 'Diseño'),
(536, 'Diseño editorial', 'Diseño'),
(537, 'Diseño publicitario', 'Diseño'),
(538, 'Diseño de producto', 'Diseño'),
(539, 'Diseño industrial', 'Diseño'),
(540, 'Diseño de moda', 'Diseño'),
(541, 'Diseño de interiores', 'Diseño'),
(542, 'Diseño de envases', 'Diseño'),
(543, 'Diseño de apps', 'Diseño'),
(544, 'Diseño digital', 'Diseño'),
(545, 'Diseño corporativo', 'Diseño'),
(546, 'Diseño social', 'Diseño'),
(547, 'Diseño sostenible', 'Diseño'),
(548, 'Diseño interactivo', 'Diseño'),
(549, 'Diseño multimedia', 'Diseño'),
(550, 'Diseño estratégico', 'Diseño');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`propuesta_id`),
  ADD KEY `propuesta_id` (`propuesta_id`);

--
-- Indices de la tabla `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gmail` (`gmail`);

--
-- Indices de la tabla `propuestas`
--
ALTER TABLE `propuestas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `propuesta_tag`
--
ALTER TABLE `propuesta_tag`
  ADD KEY `propuesta_id` (`propuesta_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indices de la tabla `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `login`
--
ALTER TABLE `login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `propuestas`
--
ALTER TABLE `propuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=551;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `login` (`id`),
  ADD CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`propuesta_id`) REFERENCES `propuestas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
