-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-02-2026 a las 22:46:36
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `apiba_padel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carnets`
--

CREATE TABLE `carnets` (
  `id` int(11) NOT NULL,
  `jugador_id` int(11) DEFAULT NULL,
  `numero_carnet` varchar(20) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carnets`
--

INSERT INTO `carnets` (`id`, `jugador_id`, `numero_carnet`, `qr_code`, `creado`) VALUES
(1, 1, 'APIBA-1', 'qr_1.png', '2026-02-06 22:20:42'),
(3, 2, 'APIBA-2', 'qr_2.png', '2026-02-07 19:47:35'),
(5, 4, 'APIBA-000004', 'qr_4_1770569605.png', '2026-02-08 16:53:27'),
(6, 5, 'APIBA-000005', 'qr_5_1770735302.png', '2026-02-10 14:55:04'),
(7, 6, 'APIBA-000006', 'qr_6_1770736058.png', '2026-02-10 15:07:39'),
(8, 7, 'APIBA-000007', 'qr_7_1770747699.png', '2026-02-10 18:21:41'),
(9, 8, 'APIBA-000008', 'qr_8_1770815404.png', '2026-02-11 13:10:05'),
(10, 9, 'APIBA-000009', 'qr_9_1770834803.png', '2026-02-11 18:33:25'),
(11, 44, 'APIBA-000044', 'qr_44_1770834808.png', '2026-02-11 18:33:30'),
(12, 43, 'APIBA-000043', 'qr_43_1770834813.png', '2026-02-11 18:33:34'),
(13, 42, 'APIBA-000042', 'qr_42_1770834816.png', '2026-02-11 18:33:37'),
(14, 41, 'APIBA-000041', 'qr_41_1770834829.png', '2026-02-11 18:33:50'),
(15, 40, 'APIBA-000040', 'qr_40_1770834833.png', '2026-02-11 18:33:54'),
(16, 39, 'APIBA-000039', 'qr_39_1770834853.png', '2026-02-11 18:34:14'),
(17, 38, 'APIBA-000038', 'qr_38_1770834857.png', '2026-02-11 18:34:18'),
(18, 37, 'APIBA-000037', 'qr_37_1770834866.png', '2026-02-11 18:34:28'),
(19, 36, 'APIBA-000036', 'qr_36_1770834871.png', '2026-02-11 18:34:32'),
(20, 34, 'APIBA-000034', 'qr_34_1770834875.png', '2026-02-11 18:34:36'),
(21, 35, 'APIBA-000035', 'qr_35_1770836361.png', '2026-02-11 18:59:22'),
(22, 33, 'APIBA-000033', 'qr_33_1770836370.png', '2026-02-11 18:59:31'),
(23, 32, 'APIBA-000032', 'qr_32_1770836376.png', '2026-02-11 18:59:37'),
(24, 31, 'APIBA-000031', 'qr_31_1770836380.png', '2026-02-11 18:59:41'),
(25, 30, 'APIBA-000030', 'qr_30_1770836383.png', '2026-02-11 18:59:44'),
(26, 29, 'APIBA-000029', 'qr_29_1770836386.png', '2026-02-11 18:59:47'),
(27, 28, 'APIBA-000028', 'qr_28_1770836389.png', '2026-02-11 18:59:50'),
(28, 27, 'APIBA-000027', 'qr_27_1770836394.png', '2026-02-11 18:59:55'),
(29, 26, 'APIBA-000026', 'qr_26_1770836397.png', '2026-02-11 18:59:58'),
(30, 25, 'APIBA-000025', 'qr_25_1770836400.png', '2026-02-11 19:00:02'),
(31, 24, 'APIBA-000024', 'qr_24_1770836403.png', '2026-02-11 19:00:05'),
(32, 10, 'APIBA-000010', 'qr_10_1770836418.png', '2026-02-11 19:00:19'),
(33, 23, 'APIBA-000023', 'qr_23_1770836422.png', '2026-02-11 19:00:24'),
(34, 22, 'APIBA-000022', 'qr_22_1770836425.png', '2026-02-11 19:00:26'),
(35, 21, 'APIBA-000021', 'qr_21_1770836427.png', '2026-02-11 19:00:29'),
(36, 20, 'APIBA-000020', 'qr_20_1770836429.png', '2026-02-11 19:00:31'),
(37, 19, 'APIBA-000019', 'qr_19_1770836432.png', '2026-02-11 19:00:33'),
(38, 18, 'APIBA-000018', 'qr_18_1770836434.png', '2026-02-11 19:00:35'),
(39, 17, 'APIBA-000017', 'qr_17_1770836436.png', '2026-02-11 19:00:37'),
(40, 16, 'APIBA-000016', 'qr_16_1770836438.png', '2026-02-11 19:00:39'),
(41, 15, 'APIBA-000015', 'qr_15_1770836440.png', '2026-02-11 19:00:41'),
(42, 14, 'APIBA-000014', 'qr_14_1770836442.png', '2026-02-11 19:00:44'),
(43, 13, 'APIBA-000013', 'qr_13_1770836444.png', '2026-02-11 19:00:46'),
(44, 12, 'APIBA-000012', 'qr_12_1770836446.png', '2026-02-11 19:00:48'),
(45, 11, 'APIBA-000011', 'qr_11_1770836448.png', '2026-02-11 19:00:50'),
(46, 45, 'APIBA-000045', 'qr_45_1770837051.png', '2026-02-11 19:10:53'),
(47, 46, 'APIBA-000046', 'qr_46_1770870858.png', '2026-02-12 04:34:19'),
(48, 47, 'APIBA-000047', 'qr_47_1770870935.png', '2026-02-12 04:35:37'),
(49, 48, 'APIBA-000048', 'qr_48_1770871011.png', '2026-02-12 04:36:52'),
(50, 49, 'APIBA-000049', 'qr_49_1770871088.png', '2026-02-12 04:38:09'),
(51, 50, 'APIBA-000050', 'qr_50_1770871166.png', '2026-02-12 04:39:27'),
(52, 51, 'APIBA-000051', 'qr_51_1770871257.png', '2026-02-12 04:40:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fixture`
--

CREATE TABLE `fixture` (
  `id` int(11) NOT NULL,
  `torneo_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `local` varchar(100) DEFAULT NULL,
  `visitante` varchar(100) DEFAULT NULL,
  `horario` varchar(10) DEFAULT NULL,
  `cancha` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fixture`
--

INSERT INTO `fixture` (`id`, `torneo_id`, `fecha`, `local`, `visitante`, `horario`, `cancha`) VALUES
(1, 3, '2026-03-01', 'Pareja A', 'Pareja B', '18:00', 'Cancha 1'),
(2, 3, '2026-03-01', 'Pareja C', 'Pareja D', '19:00', 'Cancha 2'),
(3, 3, '2026-03-08', 'Ganador 1', 'Ganador 2', '18:00', 'Cancha 1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL,
  `torneo_id` int(11) DEFAULT NULL,
  `jugador_id` int(11) DEFAULT NULL,
  `categoria_anotada` varchar(100) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `torneo_id`, `jugador_id`, `categoria_anotada`, `fecha`) VALUES
(7, 4, 2, '5TA CATEGORIA CABALLEROS', '2026-02-10 10:58:18'),
(11, 4, 5, '5TA CATEGORIA CABALLEROS', '2026-02-10 12:07:59'),
(12, 3, 6, '6TA CATEGORIA CABALLEROS', '2026-02-10 12:14:01'),
(13, 3, 5, '6TA CATEGORIA CABALLEROS', '2026-02-10 12:14:06'),
(16, 5, 6, '5TA CATEGORIA CABALLEROS', '2026-02-10 18:29:11'),
(17, 5, 4, '5TA CATEGORIA CABALLEROS', '2026-02-10 18:30:08'),
(18, 5, 2, '5TA CATEGORIA CABALLEROS', '2026-02-10 18:30:14'),
(19, 5, 5, '5TA CATEGORIA CABALLEROS', '2026-02-10 18:30:21'),
(20, 6, 6, '5TA CATEGORIA CABALLEROS', '2026-02-10 18:55:36'),
(21, 6, 4, '5TA CATEGORIA CABALLEROS', '2026-02-10 18:55:42'),
(22, 6, 5, '5TA CATEGORIA CABALLEROS', '2026-02-10 18:55:48'),
(23, 6, 2, '5TA CATEGORIA CABALLEROS', '2026-02-10 18:55:52'),
(24, 7, 6, '4TA CATEGORIA CABALLEROS', '2026-02-11 10:05:37'),
(25, 7, 1, '4TA CATEGORIA CABALLEROS', '2026-02-11 10:05:45'),
(26, 7, 4, '4TA CATEGORIA CABALLEROS', '2026-02-11 10:05:50'),
(27, 7, 2, '4TA CATEGORIA CABALLEROS', '2026-02-11 10:05:54'),
(28, 7, 5, '4TA CATEGORIA CABALLEROS', '2026-02-11 10:05:57'),
(29, 7, 8, '4TA CATEGORIA CABALLEROS', '2026-02-11 10:10:22'),
(30, 1, 34, '7MA CATEGORIA DAMAS', '2026-02-11 16:17:39'),
(31, 1, 44, '7MA DAMAS', '2026-02-11 16:36:20'),
(32, 8, 1, '4TA CABALLEROS', '2026-02-11 16:41:21'),
(34, 1, 19, '', '2026-02-11 16:49:49'),
(35, 1, 20, '', '2026-02-11 16:50:23'),
(36, 1, 24, '', '2026-02-11 16:55:37'),
(37, 1, 32, '', '2026-02-11 16:55:37'),
(38, 1, 27, '', '2026-02-11 16:55:37'),
(39, 1, 40, '', '2026-02-11 16:55:37'),
(40, 1, 33, '', '2026-02-11 16:55:37'),
(41, 1, 35, '', '2026-02-11 16:55:37'),
(42, 1, 10, '', '2026-02-11 16:55:37'),
(43, 1, 18, '', '2026-02-11 16:55:37'),
(44, 1, 39, '', '2026-02-11 16:55:38'),
(45, 1, 9, '', '2026-02-11 16:55:38'),
(46, 1, 21, '', '2026-02-11 16:55:38'),
(47, 1, 36, '', '2026-02-11 16:55:38'),
(48, 1, 11, '', '2026-02-11 16:55:38'),
(49, 1, 41, '', '2026-02-11 16:55:38'),
(50, 1, 12, '', '2026-02-11 16:55:38'),
(51, 1, 25, '', '2026-02-11 16:55:38'),
(52, 1, 31, '', '2026-02-11 16:55:38'),
(53, 1, 38, '', '2026-02-11 16:55:38'),
(54, 1, 28, '', '2026-02-11 16:55:38'),
(55, 1, 45, '', '2026-02-11 16:55:38'),
(56, 1, 13, '', '2026-02-11 16:55:38'),
(57, 1, 42, '', '2026-02-11 16:55:38'),
(58, 1, 37, '', '2026-02-11 16:55:38'),
(59, 1, 16, '', '2026-02-11 16:55:38'),
(60, 1, 14, '', '2026-02-11 16:55:38'),
(61, 1, 26, '', '2026-02-11 16:55:38'),
(62, 1, 23, '', '2026-02-11 16:55:38'),
(63, 1, 15, '', '2026-02-11 16:55:38'),
(64, 1, 29, '', '2026-02-11 16:55:38'),
(65, 1, 22, '', '2026-02-11 16:55:38'),
(67, 1, 30, '', '2026-02-11 16:55:38'),
(68, 1, 43, '', '2026-02-11 16:55:38'),
(69, 8, 6, '', '2026-02-12 01:30:10'),
(70, 8, 8, '', '2026-02-12 01:30:10'),
(71, 8, 4, '', '2026-02-12 01:30:10'),
(72, 8, 2, '', '2026-02-12 01:30:10'),
(73, 8, 5, '', '2026-02-12 01:30:11'),
(74, 8, 50, '', '2026-02-12 01:39:45'),
(75, 8, 46, '', '2026-02-12 01:39:45'),
(76, 8, 49, '', '2026-02-12 01:39:45'),
(77, 8, 47, '', '2026-02-12 01:39:45'),
(78, 8, 48, '', '2026-02-12 01:39:45'),
(79, 8, 51, '', '2026-02-12 01:41:12'),
(80, 10, 19, '', '2026-02-12 19:36:51'),
(81, 10, 24, '', '2026-02-12 19:36:51'),
(82, 10, 20, '', '2026-02-12 19:36:51'),
(83, 10, 32, '', '2026-02-12 19:36:51'),
(84, 10, 44, '', '2026-02-12 19:36:51'),
(85, 10, 27, '', '2026-02-12 19:36:51'),
(86, 10, 40, '', '2026-02-12 19:36:51'),
(87, 10, 33, '', '2026-02-12 19:36:51'),
(88, 10, 9, '', '2026-02-12 19:36:51'),
(89, 10, 21, '', '2026-02-12 19:36:52'),
(90, 10, 11, '', '2026-02-12 19:36:52'),
(91, 10, 41, '', '2026-02-12 19:36:52'),
(92, 10, 12, '', '2026-02-12 19:36:52'),
(93, 10, 38, '', '2026-02-12 19:36:52'),
(94, 10, 45, '', '2026-02-12 19:36:52'),
(95, 10, 34, '', '2026-02-12 19:36:52'),
(96, 10, 13, '', '2026-02-12 19:36:52'),
(97, 10, 37, '', '2026-02-12 19:36:52'),
(98, 10, 23, '', '2026-02-12 19:36:52'),
(99, 10, 35, '', '2026-02-12 19:45:15'),
(100, 10, 10, '', '2026-02-12 19:45:15'),
(101, 10, 18, '', '2026-02-12 19:45:15'),
(102, 10, 39, '', '2026-02-12 19:45:15'),
(103, 10, 36, '', '2026-02-12 19:45:15'),
(104, 10, 25, '', '2026-02-12 19:45:15'),
(105, 10, 31, '', '2026-02-12 19:45:15'),
(106, 10, 28, '', '2026-02-12 21:26:03'),
(107, 10, 42, '', '2026-02-12 21:26:03'),
(108, 11, 19, '', '2026-02-13 00:29:56'),
(109, 11, 24, '', '2026-02-13 00:29:56'),
(110, 11, 20, '', '2026-02-13 00:29:56'),
(111, 11, 32, '', '2026-02-13 00:29:56'),
(112, 11, 40, '', '2026-02-13 00:29:56'),
(113, 11, 10, '', '2026-02-13 00:29:56'),
(114, 11, 18, '', '2026-02-13 00:29:56'),
(115, 11, 39, '', '2026-02-13 00:29:56'),
(116, 11, 41, '', '2026-02-13 00:29:56'),
(117, 11, 31, '', '2026-02-13 00:29:56'),
(118, 11, 28, '', '2026-02-13 00:29:56'),
(119, 11, 45, '', '2026-02-13 00:29:56'),
(120, 11, 34, '', '2026-02-13 00:29:56'),
(121, 11, 13, '', '2026-02-13 00:29:56'),
(122, 11, 42, '', '2026-02-13 00:29:56'),
(123, 11, 37, '', '2026-02-13 00:29:56'),
(124, 11, 16, '', '2026-02-13 00:29:56'),
(125, 11, 14, '', '2026-02-13 00:29:56'),
(126, 11, 26, '', '2026-02-13 00:29:56'),
(127, 11, 23, '', '2026-02-13 00:29:56'),
(128, 11, 15, '', '2026-02-13 00:29:56'),
(129, 11, 29, '', '2026-02-13 00:29:56'),
(130, 11, 22, '', '2026-02-13 00:29:56'),
(131, 11, 17, '', '2026-02-13 00:29:56'),
(132, 11, 30, '', '2026-02-13 00:29:56'),
(133, 11, 43, '', '2026-02-13 00:29:56'),
(134, 11, 44, '', '2026-02-13 00:30:13'),
(135, 11, 27, '', '2026-02-13 00:30:13'),
(136, 11, 33, '', '2026-02-13 00:30:13'),
(137, 11, 35, '', '2026-02-13 00:30:13'),
(138, 11, 9, '', '2026-02-13 00:30:13'),
(139, 11, 21, '', '2026-02-13 00:30:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jugadores`
--

CREATE TABLE `jugadores` (
  `id` int(11) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `dni` varchar(20) NOT NULL,
  `sexo` enum('M','F','X') DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `localidad` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `puntos` int(11) NOT NULL DEFAULT 0,
  `ranking` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `jugadores`
--

INSERT INTO `jugadores` (`id`, `apellido`, `nombre`, `email`, `password`, `dni`, `sexo`, `categoria`, `telefono`, `localidad`, `foto`, `activo`, `created_at`, `puntos`, `ranking`) VALUES
(1, 'Martínez Neta', 'Facundo', 'facucas23@gmail.com', '$2y$10$3RiayOtBwv540bY45uUR8upq18TtvP.VxdXOf7d5XsNRj9nbrHfi2', '27830085', 'M', '4TA CABALLEROS', '2923647346', 'Casbas', '6986687e1a68a.jpg', 1, '2026-02-06 22:17:34', 75, 0),
(2, 'Perez', 'Juan', 'juan@test.com', '$2y$10$iYV5.Eo2popohukvU413tuBkPNRWNGD7O1/qCwM2Xj0QaXw0hAQji', '12345678', 'M', '5TA CABALLEROS', '1122334455', 'Tres Lomas', 'jug_6987925f5c11b.jpg', 1, '2026-02-07 19:28:31', 370, 0),
(4, 'Martínez Neta', 'Mateo', 'mateomartinezneta@gmail.com', '$2y$10$Ptg4w16vVSW4j.uHh3xJiOW/D4Xq8jGvxV0Sxljc20xtmJEYjgL5G', '46001293', 'M', '5TA CABALLEROS', '2923580050', 'Casbas', 'jug_6988bf807d7ad.jpg', 1, '2026-02-08 16:53:20', 280, 0),
(5, 'Stupaczuk', 'Franco', 'fstupaczuk@gmail.com', '$2y$10$G8kEwcg7oO10VDGcRiL6U.jfhEUWY52J4fwKhgXlvnuAEh4nNugKm', '38999999', 'M', '7MA CABALLEROS', '2923666666', 'Chaco', 'jug_698b4690dc571.jpg', 1, '2026-02-10 14:54:08', 435, 0),
(6, 'Chingoto', 'Federico', 'fchingoto@gmail.com', '$2y$10$vAyeJ5nuqoEP4R3/9n5uk.ozqngJRL128CanBMq4GLJ3CzkQf9gf2', '40555555', 'M', '6TA CABALLEROS', '2923657816', 'Bolivar', 'jug_698b49b63ed47.jpg', 1, '2026-02-10 15:07:34', 290, 0),
(7, 'Delfina', 'Brea', 'delfibrea@gmail.com', '$2y$10$exQBj3fqoP5CcIiDPM1oHO3dKmwtiRwLC.LI9L6MQjt7uLwG43toy', '45258258', 'F', '4TA DAMAS', '02923659832', 'Buenos Aires', 'jug_698b75e39d08c.jpg', 1, '2026-02-10 18:16:03', 0, 0),
(8, 'Mani', 'Victor', 'vmani@gmail.com', '$2y$10$rkWhYp0mW43MQBk0dzOcVusvq4Og1deoO3KCgA3LYfFJsvdYlZAve', '25560350', 'M', '7MA CABALLEROS', '2392482331', 'Tres Lomas', 'jug_698c7fa5aea18.jpg', 1, '2026-02-11 13:09:57', 100, 0),
(9, 'Gómez', 'Sofía', 'sofia.gomez7@gmail.com', '$2y$10$demoHash123456789', '40111223', 'F', '7MA DAMAS', '2396-450001', 'Pehuajó', 'gomez_sofia.jpg', 1, '2026-02-11 18:32:54', 90, 0),
(10, 'Fernández', 'Valentina', 'valen.fernandez7@gmail.com', '$2y$10$demoHash123456789', '40111224', 'F', '7MA DAMAS', '2396-450002', 'Pehuajó', 'fernandez_valentina.jpg', 1, '2026-02-11 18:32:54', 40, 0),
(11, 'López', 'Martina', 'martina.lopez7@gmail.com', '$2y$10$demoHash123456789', '40111225', 'F', '7MA DAMAS', '2396-450003', 'Carlos Casares', 'lopez_martina.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(12, 'Martínez', 'Camila', 'camila.martinez7@gmail.com', '$2y$10$demoHash123456789', '40111226', 'F', '7MA DAMAS', '2396-450004', 'Trenque Lauquen', 'martinez_camila.jpg', 1, '2026-02-11 18:32:54', 80, 0),
(13, 'Pérez', 'Lucía', 'lucia.perez7@gmail.com', '$2y$10$demoHash123456789', '40111227', 'F', '7MA DAMAS', '2396-450005', '9 de Julio', 'perez_lucia.jpg', 1, '2026-02-11 18:32:54', 50, 0),
(14, 'Rodríguez', 'Emma', 'emma.rodriguez7@gmail.com', '$2y$10$demoHash123456789', '40111228', 'F', '7MA DAMAS', '2396-450006', 'Bolívar', 'rodriguez_emma.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(15, 'Sánchez', 'Olivia', 'olivia.sanchez7@gmail.com', '$2y$10$demoHash123456789', '40111229', 'F', '7MA DAMAS', '2396-450007', 'Pehuajó', 'sanchez_olivia.jpg', 1, '2026-02-11 18:32:54', 60, 0),
(16, 'Ramírez', 'Catalina', 'catalina.ramirez7@gmail.com', '$2y$10$demoHash123456789', '40111230', 'F', '7MA DAMAS', '2396-450008', 'Carlos Casares', 'ramirez_catalina.jpg', 1, '2026-02-11 18:32:54', 60, 0),
(17, 'Torres', 'Renata', 'renata.torres7@gmail.com', '$2y$10$demoHash123456789', '40111231', 'F', '7MA DAMAS', '2396-450009', 'Trenque Lauquen', 'torres_renata.jpg', 1, '2026-02-11 18:32:54', 0, 0),
(18, 'Flores', 'Micaela', 'mica.flores7@gmail.com', '$2y$10$demoHash123456789', '40111232', 'F', '7MA DAMAS', '2396-450010', 'Pehuajó', 'flores_micaela.jpg', 1, '2026-02-11 18:32:54', 40, 0),
(19, 'Acosta', 'Julieta', 'julieta.acosta7@gmail.com', '$2y$10$demoHash123456789', '40111233', 'F', '7MA DAMAS', '2396-450011', '9 de Julio', 'acosta_julieta.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(20, 'Benítez', 'Agustina', 'agus.benitez7@gmail.com', '$2y$10$demoHash123456789', '40111234', 'F', '7MA DAMAS', '2396-450012', 'Bolívar', 'benitez_agustina.jpg', 1, '2026-02-11 18:32:54', 60, 0),
(21, 'Herrera', 'Brenda', 'brenda.herrera7@gmail.com', '$2y$10$demoHash123456789', '40111235', 'F', '7MA DAMAS', '2396-450013', 'Pehuajó', 'herrera_brenda.jpg', 1, '2026-02-11 18:32:54', 40, 0),
(22, 'Suárez', 'Milagros', 'mili.suarez7@gmail.com', '$2y$10$demoHash123456789', '40111236', 'F', '7MA DAMAS', '2396-450014', 'Carlos Casares', 'suarez_milagros.jpg', 1, '2026-02-11 18:32:54', 60, 0),
(23, 'Romero', 'Florencia', 'flor.romero7@gmail.com', '$2y$10$demoHash123456789', '40111237', 'F', '7MA DAMAS', '2396-450015', 'Trenque Lauquen', 'romero_florencia.jpg', 1, '2026-02-11 18:32:54', 75, 0),
(24, 'Álvarez', 'Antonella', 'anto.alvarez7@gmail.com', '$2y$10$demoHash123456789', '40111238', 'F', '7MA DAMAS', '2396-450016', 'Pehuajó', 'alvarez_antonella.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(25, 'Molina', 'Paula', 'paula.molina7@gmail.com', '$2y$10$demoHash123456789', '40111239', 'F', '7MA DAMAS', '2396-450017', 'Bolívar', 'molina_paula.jpg', 1, '2026-02-11 18:32:54', 50, 0),
(26, 'Rojas', 'Lara', 'lara.rojas7@gmail.com', '$2y$10$demoHash123456789', '40111240', 'F', '7MA DAMAS', '2396-450018', '9 de Julio', 'rojas_lara.jpg', 1, '2026-02-11 18:32:54', 75, 0),
(27, 'Castro', 'Victoria', 'vicky.castro7@gmail.com', '$2y$10$demoHash123456789', '40111241', 'F', '7MA DAMAS', '2396-450019', 'Pehuajó', 'castro_victoria.jpg', 1, '2026-02-11 18:32:54', 100, 0),
(28, 'Ortiz', 'Ariana', 'ariana.ortiz7@gmail.com', '$2y$10$demoHash123456789', '40111242', 'F', '7MA DAMAS', '2396-450020', 'Carlos Casares', 'ortiz_ariana.jpg', 1, '2026-02-11 18:32:54', 50, 0),
(29, 'Silva', 'Guadalupe', 'guada.silva7@gmail.com', '$2y$10$demoHash123456789', '40111243', 'F', '7MA DAMAS', '2396-450021', 'Trenque Lauquen', 'silva_guadalupe.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(30, 'Vega', 'Malena', 'malena.vega7@gmail.com', '$2y$10$demoHash123456789', '40111244', 'F', '7MA DAMAS', '2396-450022', 'Pehuajó', 'vega_malena.jpg', 1, '2026-02-11 18:32:54', 60, 0),
(31, 'Morales', 'Bianca', 'bianca.morales7@gmail.com', '$2y$10$demoHash123456789', '40111245', 'F', '7MA DAMAS', '2396-450023', 'Bolívar', 'morales_bianca.jpg', 1, '2026-02-11 18:32:54', 60, 0),
(32, 'Cabrera', 'Candela', 'cande.cabrera7@gmail.com', '$2y$10$demoHash123456789', '40111246', 'F', '7MA DAMAS', '2396-450024', '9 de Julio', 'cabrera_candela.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(33, 'Domínguez', 'Daniela', 'dani.dominguez7@gmail.com', '$2y$10$demoHash123456789', '40111247', 'F', '7MA DAMAS', '2396-450025', 'Pehuajó', 'dominguez_daniela.jpg', 1, '2026-02-11 18:32:54', 60, 0),
(34, 'Peralta', 'Josefina', 'jose.peralta7@gmail.com', '$2y$10$demoHash123456789', '40111248', 'F', '7MA DAMAS', '2396-450026', 'Carlos Casares', 'peralta_josefina.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(35, 'Farias', 'Rocío', 'rocio.farias7@gmail.com', '$2y$10$demoHash123456789', '40111249', 'F', '7MA DAMAS', '2396-450027', 'Trenque Lauquen', 'farias_rocio.jpg', 1, '2026-02-11 18:32:54', 100, 0),
(36, 'Ibarra', 'Abril', 'abril.ibarra7@gmail.com', '$2y$10$demoHash123456789', '40111250', 'F', '7MA DAMAS', '2396-450028', 'Pehuajó', 'ibarra_abril.jpg', 1, '2026-02-11 18:32:54', 40, 0),
(37, 'Quiroga', 'Zoe', 'zoe.quiroga7@gmail.com', '$2y$10$demoHash123456789', '40111251', 'F', '7MA DAMAS', '2396-450029', 'Bolívar', 'quiroga_zoe.jpg', 1, '2026-02-11 18:32:54', 80, 0),
(38, 'Navarro', 'Noelia', 'noe.navarro7@gmail.com', '$2y$10$demoHash123456789', '40111252', 'F', '7MA DAMAS', '2396-450030', '9 de Julio', 'navarro_noelia.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(39, 'Giménez', 'Belén', 'belen.gimenez7@gmail.com', '$2y$10$demoHash123456789', '40111253', 'F', '7MA DAMAS', '2396-450031', 'Pehuajó', 'gimenez_belen.jpg', 1, '2026-02-11 18:32:54', 90, 0),
(40, 'Correa', 'Carolina', 'caro.correa7@gmail.com', '$2y$10$demoHash123456789', '40111254', 'F', '7MA DAMAS', '2396-450032', 'Carlos Casares', 'correa_carolina.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(41, 'Luna', 'Natalia', 'natalia.luna7@gmail.com', '$2y$10$demoHash123456789', '40111255', 'F', '7MA DAMAS', '2396-450033', 'Trenque Lauquen', 'luna_natalia.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(42, 'Ponce', 'Tamara', 'tamara.ponce7@gmail.com', '$2y$10$demoHash123456789', '40111256', 'F', '7MA DAMAS', '2396-450034', 'Pehuajó', 'ponce_tamara.jpg', 1, '2026-02-11 18:32:54', 60, 0),
(43, 'Vera', 'Romina', 'romina.vera7@gmail.com', '$2y$10$demoHash123456789', '40111257', 'F', '7MA DAMAS', '2396-450035', 'Bolívar', 'vera_romina.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(44, 'Campos', 'Eliana', 'eliana.campos7@gmail.com', '$2y$10$Y93HJ/Zz9hpb0bm9uaCpa.2HrcM5CodvVhHujTdDaPtzQsytsa18W', '40111258', 'F', '7MA DAMAS', '2396-450036', '9 de Julio', 'campos_eliana.jpg', 1, '2026-02-11 18:32:54', 35, 0),
(45, 'Pamela', 'García', 'pame_789@gmail.com', '$2y$10$sqE4J/qV0txAQJRDQjXaPOY40vKWlTONmxxkf35TufzIPikc7xF7S', '123456789', 'F', '7MA DAMAS', '1122334455', 'Santa Rosa', NULL, 1, '2026-02-11 19:09:52', 50, 0),
(46, 'Pelufo', 'Jose', 'jose@gmail.com', '$2y$10$ZSn7VOFK8LwzRYe7bPwX0umOtcf9PPOy/BQzckoJMXp/TBb1C4td.', '321654987', 'M', '6TA Caballeros', '321654987', 'Santa Rosa', 'jug_698d582693d50.jpg', 1, '2026-02-12 04:33:42', 0, 0),
(47, 'Sosa', 'Mario', 'mario@gmail.com', '$2y$10$Ra9RK4p8gySAyPDtPRkJ2ukGOPGLvP0nMu3kJVAeCcKXT297DX6Wy', '654321987', 'M', '6TA Caballeros', '987654321', 'Carhué', 'jug_698d589413125.jpg', 1, '2026-02-12 04:35:32', 0, 0),
(48, 'Suarez', 'Juan', 'juan@poco.com', '$2y$10$crdzAC077.I3eqYrdQDbC.AplUyjQnXcLWVxZT/4NFYynzNtzhBrC', '2233554477', 'M', '6TA Caballeros', '336699885522', 'Pigué', 'jug_698d58e053e58.jpg', 1, '2026-02-12 04:36:48', 0, 0),
(49, 'Perez', 'Anibal', 'anibal@erp.com', '$2y$10$CmGGRPzW683/UHyZcvKqPuUICDrX.1SD.R8j4yR6vmmQYcXB9Vnse', '234455677', 'M', '7MA Caballeros', '2354898789', 'Huanguelén', 'jug_698d592d36eba.jpg', 1, '2026-02-12 04:38:05', 0, 0),
(50, 'Pedernera', 'Sergio', 'sergio@pepe.com', '$2y$10$U2a95wAv7WeJOynxIZ/Jw..267J3xq1y9SO89uiVc7Jj1oW8DJE/6', '123456798', 'M', '7MA Caballeros', '654987987', 'Salliqueló', 'jug_698d597bc5677.jpg', 1, '2026-02-12 04:39:24', 0, 0),
(51, 'Martin', 'Gaston', 'gaston@tre.com', '$2y$10$rIsQsdlKCRY9daOehERrfuUF5e4RYmFul1qqeKSKZF6KSfNDV2r.G', '5564612321', 'M', '7MA Caballeros', '321654987', 'Huanguelén', 'jug_698d59d744741.jpg', 1, '2026-02-12 04:40:55', 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `localidades`
--

CREATE TABLE `localidades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `localidades`
--

INSERT INTO `localidades` (`id`, `nombre`, `activo`) VALUES
(1, 'Casbas', 1),
(2, 'Tres Lomas', 1),
(3, '30 de Agosto', 1),
(4, 'Bolivar', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `contenido` text NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `destacada` tinyint(1) DEFAULT 0,
  `fecha_publicacion` datetime DEFAULT current_timestamp(),
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `slug`, `contenido`, `imagen`, `destacada`, `fecha_publicacion`, `activa`) VALUES
(1, 'APIBA Hacia el Futuro: Innovación Tecnológica 2026', 'apiba-hacia-el-futuro-innovacion-tecnologica-2026', 'En APIBA iniciamos este 2026 marcando un hito en nuestra gestión. Nos complace anunciar la firma de un contrato estratégico con una empresa tecnológica líder para la implementación de un nuevo sistema de gestión de torneos.\r\nEste programa será de gran utilidad para toda nuestra comunidad, permitiendo:\r\nAutomatización de llaves: Generación instantánea de cuadros (desde 6 hasta 90 parejas) con lógica de cruces profesional.\r\nTransparencia: Seguimiento de resultados y zonas en tiempo real.\r\nEficiencia: Una experiencia optimizada para jugadores y organizadores, reduciendo tiempos de espera y errores manuales.\r\nCon esta inversión, ponemos a disposición de nuestros socios tecnología de vanguardia para que la competencia esté a la altura de los más altos estándares internacionales.\r\n\r\n¡El futuro de APBA ya está en producción!', 'not_698dcc4f7a45c.jpg', 1, '2026-02-12 00:00:00', 1),
(2, 'Víctor Mani asume como el nuevo Fiscal General del Pádel en APIBA', 'victor-mani-asume-como-el-nuevo-fiscal-general-del-padel-en-apiba', 'En un paso decisivo para fortalecer la transparencia y el reglamento de la competencia, la Asociación de Pádel (APIBA) ha oficializado la designación de Víctor Mani como el nuevo Fiscal General del Pádel.\r\n\r\nCon esta incorporación, APIBA busca elevar la vara en la organización de sus torneos, asegurando que el fair play y el cumplimiento de las normativas vigentes sean el eje central de cada jornada deportiva. Mani, reconocido por su trayectoria y conocimiento del reglamento, será el responsable de supervisar la disciplina y el correcto desarrollo de las llaves y zonas en toda la provincia.\r\n\r\n\"La llegada de Víctor Mani representa un compromiso con el orden y la justicia deportiva. Su rol será clave para que los jugadores solo tengan que preocuparse por su nivel dentro de la pista\", expresaron desde la dirigencia de la asociación.\r\n\r\nEste nombramiento coincide con la reciente implementación tecnológica de APIBA para este 2026, conformando un equipo de trabajo que combina innovación técnica y rigor institucional. Con Víctor Mani a la cabeza de la fiscalización, el pádel de la región se encamina hacia una profesionalización sin precedentes.', 'not_698de675695b9.jpg', 0, '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puntos_torneo`
--

CREATE TABLE `puntos_torneo` (
  `id` int(11) NOT NULL,
  `torneo_id` int(11) NOT NULL,
  `jugador_id` int(11) NOT NULL,
  `fase` varchar(30) NOT NULL,
  `puntos` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `puntos_torneo`
--

INSERT INTO `puntos_torneo` (`id`, `torneo_id`, `jugador_id`, `fase`, `puntos`, `updated_at`) VALUES
(1, 4, 2, 'Campeon', 100, '2026-02-10 19:09:15'),
(2, 4, 5, 'Semi (+)', 80, '2026-02-10 19:21:44'),
(11, 3, 6, 'Campeon', 100, '2026-02-10 20:51:31'),
(12, 3, 5, 'Finalista', 90, '2026-02-10 20:51:31'),
(15, 5, 6, 'Semi (+)', 80, '2026-02-10 22:32:37'),
(16, 5, 4, 'Finalista', 90, '2026-02-10 21:31:33'),
(17, 5, 2, 'Campeon', 100, '2026-02-10 22:32:37'),
(18, 5, 5, 'Semi (-)', 75, '2026-02-10 21:31:33'),
(19, 6, 6, 'Zona', 35, '2026-02-10 23:33:52'),
(20, 6, 4, 'Campeon', 100, '2026-02-10 23:33:52'),
(21, 6, 2, 'Semi (+)', 80, '2026-02-10 22:32:19'),
(22, 6, 5, 'Finalista', 90, '2026-02-10 23:33:52'),
(47, 7, 8, 'Campeon', 100, '2026-02-11 17:31:24'),
(48, 7, 5, 'Campeon', 100, '2026-02-11 17:31:24'),
(49, 7, 4, 'Finalista', 90, '2026-02-11 17:31:24'),
(50, 7, 2, 'Finalista', 90, '2026-02-11 17:31:24'),
(51, 7, 1, 'Semi (-)', 75, '2026-02-11 17:31:24'),
(52, 7, 6, 'Semi (-)', 75, '2026-02-11 17:31:24'),
(53, 1, 27, 'Campeon', 100, '2026-02-12 07:51:53'),
(54, 1, 35, 'Campeon', 100, '2026-02-12 07:51:53'),
(55, 1, 14, 'Zona', 35, '2026-02-12 07:51:53'),
(56, 1, 29, 'Zona', 35, '2026-02-12 07:51:53'),
(57, 1, 18, 'Dieciseisavos', 40, '2026-02-12 07:51:53'),
(58, 1, 21, 'Dieciseisavos', 40, '2026-02-12 07:51:53'),
(59, 1, 39, 'Finalista', 90, '2026-02-12 07:51:53'),
(60, 1, 9, 'Finalista', 90, '2026-02-12 07:51:53'),
(61, 1, 12, 'Semi (+)', 80, '2026-02-12 07:51:53'),
(62, 1, 37, 'Semi (+)', 80, '2026-02-12 07:51:53'),
(63, 1, 43, 'Zona', 35, '2026-02-12 07:51:53'),
(64, 1, 38, 'Zona', 35, '2026-02-12 07:51:53'),
(65, 1, 20, 'Cuartos', 60, '2026-02-12 07:51:53'),
(66, 1, 33, 'Cuartos', 60, '2026-02-12 07:51:53'),
(67, 1, 19, 'Zona', 35, '2026-02-12 07:51:53'),
(68, 1, 24, 'Zona', 35, '2026-02-12 07:51:53'),
(69, 1, 15, 'Cuartos', 60, '2026-02-12 07:51:53'),
(70, 1, 42, 'Cuartos', 60, '2026-02-12 07:51:53'),
(71, 1, 31, 'Cuartos', 60, '2026-02-12 07:51:53'),
(72, 1, 16, 'Cuartos', 60, '2026-02-12 07:51:53'),
(73, 1, 11, 'Zona', 35, '2026-02-12 07:51:53'),
(74, 1, 41, 'Zona', 35, '2026-02-12 07:51:53'),
(75, 1, 26, 'Semi (-)', 75, '2026-02-12 07:51:53'),
(76, 1, 23, 'Semi (-)', 75, '2026-02-12 07:51:54'),
(77, 1, 10, 'Dieciseisavos', 40, '2026-02-12 07:51:54'),
(78, 1, 36, 'Dieciseisavos', 40, '2026-02-12 07:51:54'),
(79, 1, 44, 'Zona', 35, '2026-02-12 07:51:54'),
(80, 1, 34, 'Zona', 35, '2026-02-12 07:51:54'),
(81, 1, 28, 'Octavos', 50, '2026-02-12 07:51:54'),
(82, 1, 45, 'Octavos', 50, '2026-02-12 07:51:54'),
(83, 1, 30, 'Cuartos', 60, '2026-02-12 07:51:54'),
(84, 1, 22, 'Cuartos', 60, '2026-02-12 07:51:54'),
(85, 1, 25, 'Octavos', 50, '2026-02-12 07:51:54'),
(86, 1, 13, 'Octavos', 50, '2026-02-12 07:51:54'),
(87, 1, 32, 'Zona', 35, '2026-02-12 07:51:54'),
(88, 1, 40, 'Zona', 35, '2026-02-12 07:51:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ranking`
--

CREATE TABLE `ranking` (
  `id` int(11) NOT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `jugador` varchar(100) DEFAULT NULL,
  `puntos` int(11) DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `jugador_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ranking`
--

INSERT INTO `ranking` (`id`, `categoria`, `jugador`, `puntos`, `posicion`, `jugador_id`) VALUES
(255, '4TA CABALLEROS', 'Mani Victor', 100, 1, 8),
(256, '4TA CABALLEROS', 'Stupaczuk Franco', 100, 2, 5),
(257, '4TA CABALLEROS', 'Martínez Neta Mateo', 90, 3, 4),
(258, '4TA CABALLEROS', 'Perez Juan', 90, 4, 2),
(259, '4TA CABALLEROS', 'Chingoto Federico', 75, 5, 6),
(260, '4TA CABALLEROS', 'Martínez Neta Facundo', 75, 6, 1),
(261, '5TA CABALLEROS', 'Perez Juan', 280, 1, 2),
(262, '5TA CABALLEROS', 'Stupaczuk Franco', 245, 2, 5),
(263, '5TA CABALLEROS', 'Martínez Neta Mateo', 190, 3, 4),
(264, '5TA CABALLEROS', 'Chingoto Federico', 115, 4, 6),
(265, '6TA CABALLEROS', 'Chingoto Federico', 100, 1, 6),
(266, '6TA CABALLEROS', 'Stupaczuk Franco', 90, 2, 5),
(267, '7MA DAMAS', 'Castro Victoria', 100, 1, 27),
(268, '7MA DAMAS', 'Farias Rocío', 100, 2, 35),
(269, '7MA DAMAS', 'Giménez Belén', 90, 3, 39),
(270, '7MA DAMAS', 'Gómez Sofía', 90, 4, 9),
(271, '7MA DAMAS', 'Martínez Camila', 80, 5, 12),
(272, '7MA DAMAS', 'Quiroga Zoe', 80, 6, 37),
(273, '7MA DAMAS', 'Rojas Lara', 75, 7, 26),
(274, '7MA DAMAS', 'Romero Florencia', 75, 8, 23),
(275, '7MA DAMAS', 'Benítez Agustina', 60, 9, 20),
(276, '7MA DAMAS', 'Domínguez Daniela', 60, 10, 33),
(277, '7MA DAMAS', 'Morales Bianca', 60, 11, 31),
(278, '7MA DAMAS', 'Ponce Tamara', 60, 12, 42),
(279, '7MA DAMAS', 'Ramírez Catalina', 60, 13, 16),
(280, '7MA DAMAS', 'Sánchez Olivia', 60, 14, 15),
(281, '7MA DAMAS', 'Suárez Milagros', 60, 15, 22),
(282, '7MA DAMAS', 'Vega Malena', 60, 16, 30),
(283, '7MA DAMAS', 'Molina Paula', 50, 17, 25),
(284, '7MA DAMAS', 'Ortiz Ariana', 50, 18, 28),
(285, '7MA DAMAS', 'Pamela García', 50, 19, 45),
(286, '7MA DAMAS', 'Pérez Lucía', 50, 20, 13),
(287, '7MA DAMAS', 'Fernández Valentina', 40, 21, 10),
(288, '7MA DAMAS', 'Flores Micaela', 40, 22, 18),
(289, '7MA DAMAS', 'Herrera Brenda', 40, 23, 21),
(290, '7MA DAMAS', 'Ibarra Abril', 40, 24, 36),
(291, '7MA DAMAS', 'Acosta Julieta', 35, 25, 19),
(292, '7MA DAMAS', 'Álvarez Antonella', 35, 26, 24),
(293, '7MA DAMAS', 'Cabrera Candela', 35, 27, 32),
(294, '7MA DAMAS', 'Campos Eliana', 35, 28, 44),
(295, '7MA DAMAS', 'Correa Carolina', 35, 29, 40),
(296, '7MA DAMAS', 'López Martina', 35, 30, 11),
(297, '7MA DAMAS', 'Luna Natalia', 35, 31, 41),
(298, '7MA DAMAS', 'Navarro Noelia', 35, 32, 38),
(299, '7MA DAMAS', 'Peralta Josefina', 35, 33, 34),
(300, '7MA DAMAS', 'Rodríguez Emma', 35, 34, 14),
(301, '7MA DAMAS', 'Silva Guadalupe', 35, 35, 29),
(302, '7MA DAMAS', 'Vera Romina', 35, 36, 43);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ranking_prev`
--

CREATE TABLE `ranking_prev` (
  `id` int(11) NOT NULL,
  `categoria` varchar(255) NOT NULL,
  `jugador_id` int(11) NOT NULL DEFAULT 0,
  `jugador` varchar(255) NOT NULL,
  `puntos` int(11) NOT NULL DEFAULT 0,
  `posicion` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ranking_prev`
--

INSERT INTO `ranking_prev` (`id`, `categoria`, `jugador_id`, `jugador`, `puntos`, `posicion`, `created_at`) VALUES
(278, '4TA CATEGORIA CABALLEROS', 8, 'Mani Victor', 100, 1, '2026-02-12 07:52:12'),
(279, '4TA CATEGORIA CABALLEROS', 5, 'Stupaczuk Franco', 100, 2, '2026-02-12 07:52:12'),
(280, '4TA CATEGORIA CABALLEROS', 4, 'Martínez Neta Mateo', 90, 3, '2026-02-12 07:52:12'),
(281, '4TA CATEGORIA CABALLEROS', 2, 'Perez Juan', 90, 4, '2026-02-12 07:52:12'),
(282, '4TA CATEGORIA CABALLEROS', 6, 'Chingoto Federico', 75, 5, '2026-02-12 07:52:12'),
(283, '4TA CATEGORIA CABALLEROS', 1, 'Martínez Neta Facundo', 75, 6, '2026-02-12 07:52:12'),
(284, '5TA CATEGORIA CABALLEROS', 2, 'Perez Juan', 280, 1, '2026-02-12 07:52:12'),
(285, '5TA CATEGORIA CABALLEROS', 5, 'Stupaczuk Franco', 245, 2, '2026-02-12 07:52:12'),
(286, '5TA CATEGORIA CABALLEROS', 4, 'Martínez Neta Mateo', 190, 3, '2026-02-12 07:52:12'),
(287, '5TA CATEGORIA CABALLEROS', 6, 'Chingoto Federico', 115, 4, '2026-02-12 07:52:12'),
(288, '6TA CATEGORIA CABALLEROS', 6, 'Chingoto Federico', 100, 1, '2026-02-12 07:52:12'),
(289, '6TA CATEGORIA CABALLEROS', 5, 'Stupaczuk Franco', 90, 2, '2026-02-12 07:52:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torneos`
--

CREATE TABLE `torneos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('abierto','en_curso','finalizado') DEFAULT 'abierto',
  `sede` varchar(60) NOT NULL DEFAULT '90´S PADEL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `torneos`
--

INSERT INTO `torneos` (`id`, `nombre`, `categoria`, `fecha_inicio`, `fecha_fin`, `estado`, `sede`) VALUES
(1, '7MA CATEGORIA DAMAS', '7MA DAMAS', '2026-03-13', '2026-03-15', 'abierto', '90´S PADEL'),
(3, '6TA CATEGORIA CABALLEROS', '6TA CABALLEROS', '2026-03-27', NULL, 'abierto', '90´S PADEL'),
(4, '5TA CATEGORIA CABALLEROS', '5TA CABALLEROS', '2026-03-13', NULL, 'abierto', 'FRAY PADEL'),
(5, '5TA CATEGORIA CABALLEROS', '5TA CABALLEROS', '2026-02-13', NULL, 'abierto', '90´S PADEL'),
(6, '5TA CATEGORIA CABALLEROS', '5TA CABALLEROS', '2026-03-21', NULL, 'abierto', 'LA QUINTA PADEL'),
(7, '4TA CATEGORIA CABALLEROS', '4TA CABALLEROS', '2026-02-13', NULL, 'abierto', 'CASBAS PADEL'),
(8, '4TA CATEGORIA CABALLEROS', '4TA CATEGORIA CABALLEROS', '2026-02-14', NULL, '', 'FRAY PADEL'),
(9, '6TA CATEGORIA DAMAS', '6TA CATEGORIA DAMAS', '2026-02-21', NULL, 'abierto', 'FRAY PADEL'),
(10, '7MA CATEGORIA DAMAS', '7MA CATEGORIA DAMAS', '2026-02-21', NULL, '', 'LA QUINTA PADEL'),
(11, '7MA CATEGORIA DAMAS', '7MA CATEGORIA DAMAS', '2026-02-28', NULL, '', '90´S PADEL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torneo_equipos`
--

CREATE TABLE `torneo_equipos` (
  `id` int(11) NOT NULL,
  `torneo_id` int(11) NOT NULL,
  `jugador1_id` int(11) NOT NULL,
  `categoria_j1` varchar(100) NOT NULL,
  `jugador2_id` int(11) NOT NULL,
  `categoria_j2` varchar(100) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `torneo_equipos`
--

INSERT INTO `torneo_equipos` (`id`, `torneo_id`, `jugador1_id`, `categoria_j1`, `jugador2_id`, `categoria_j2`, `creado_en`) VALUES
(2, 7, 8, '4TA CATEGORIA CABALLEROS', 5, '4TA CATEGORIA CABALLEROS', '2026-02-11 14:30:51'),
(3, 7, 4, '4TA CATEGORIA CABALLEROS', 2, '4TA CATEGORIA CABALLEROS', '2026-02-11 14:31:05'),
(7, 7, 6, '4TA CATEGORIA CABALLEROS', 1, '4TA CATEGORIA CABALLEROS', '2026-02-11 17:30:54'),
(9, 1, 19, '7MA DAMAS', 24, '7MA DAMAS', '2026-02-11 19:56:11'),
(10, 1, 27, '7MA DAMAS', 35, '7MA DAMAS', '2026-02-11 19:56:23'),
(11, 1, 32, '7MA DAMAS', 40, '7MA DAMAS', '2026-02-11 19:56:32'),
(12, 1, 20, '7MA DAMAS', 33, '7MA DAMAS', '2026-02-11 19:56:42'),
(14, 1, 39, '7MA DAMAS', 9, '7MA DAMAS', '2026-02-11 20:26:33'),
(16, 1, 18, '7MA DAMAS', 21, '7MA DAMAS', '2026-02-11 21:01:55'),
(17, 1, 10, '7MA DAMAS', 36, '7MA DAMAS', '2026-02-11 21:05:23'),
(18, 1, 28, '7MA DAMAS', 45, '7MA DAMAS', '2026-02-11 21:09:51'),
(19, 1, 11, '7MA DAMAS', 41, '7MA DAMAS', '2026-02-11 21:18:33'),
(20, 1, 25, '7MA DAMAS', 13, '7MA DAMAS', '2026-02-11 21:19:24'),
(21, 1, 12, '7MA DAMAS', 37, '7MA DAMAS', '2026-02-11 21:25:31'),
(22, 1, 30, '7MA DAMAS', 22, '7MA DAMAS', '2026-02-11 21:25:40'),
(23, 1, 43, '7MA DAMAS', 38, '7MA DAMAS', '2026-02-11 21:25:48'),
(24, 1, 31, '7MA DAMAS', 16, '7MA DAMAS', '2026-02-11 21:25:54'),
(25, 1, 15, '7MA DAMAS', 42, '7MA DAMAS', '2026-02-11 21:25:58'),
(26, 1, 26, '7MA DAMAS', 23, '7MA DAMAS', '2026-02-11 21:26:02'),
(27, 1, 14, '7MA DAMAS', 29, '7MA DAMAS', '2026-02-11 21:26:14'),
(28, 1, 44, '7MA DAMAS', 34, '7MA CATEGORIA DAMAS', '2026-02-11 22:16:44'),
(29, 8, 6, '4TA CABALLEROS', 8, '4TA CABALLEROS', '2026-02-12 04:30:59'),
(30, 8, 1, '4TA CABALLEROS', 5, '4TA CABALLEROS', '2026-02-12 04:31:06'),
(31, 8, 4, '4TA CABALLEROS', 2, '4TA CABALLEROS', '2026-02-12 04:31:10'),
(32, 8, 50, '4TA CABALLEROS', 46, '4TA CABALLEROS', '2026-02-12 04:39:54'),
(33, 8, 49, '4TA CABALLEROS', 47, '4TA CABALLEROS', '2026-02-12 04:39:58'),
(34, 8, 51, '4TA CABALLEROS', 48, '4TA CABALLEROS', '2026-02-12 04:41:19'),
(42, 10, 36, '7MA CATEGORIA DAMAS', 21, '7MA CATEGORIA DAMAS', '2026-02-12 22:49:46'),
(43, 10, 25, '7MA CATEGORIA DAMAS', 12, '7MA CATEGORIA DAMAS', '2026-02-12 22:49:49'),
(45, 10, 23, '7MA CATEGORIA DAMAS', 34, '7MA CATEGORIA DAMAS', '2026-02-12 22:49:56'),
(46, 10, 13, '7MA CATEGORIA DAMAS', 38, '7MA CATEGORIA DAMAS', '2026-02-12 22:50:00'),
(47, 10, 37, '7MA CATEGORIA DAMAS', 45, '7MA CATEGORIA DAMAS', '2026-02-12 22:50:02'),
(48, 10, 19, '7MA CATEGORIA DAMAS', 40, '7MA CATEGORIA DAMAS', '2026-02-12 22:54:29'),
(49, 10, 33, '7MA CATEGORIA DAMAS', 35, '7MA CATEGORIA DAMAS', '2026-02-12 22:55:06'),
(50, 10, 41, '7MA CATEGORIA DAMAS', 11, '7MA CATEGORIA DAMAS', '2026-02-12 22:55:10'),
(51, 10, 39, '7MA CATEGORIA DAMAS', 10, '7MA CATEGORIA DAMAS', '2026-02-12 22:55:39'),
(52, 10, 24, '7MA CATEGORIA DAMAS', 20, '7MA CATEGORIA DAMAS', '2026-02-12 23:04:14'),
(53, 10, 18, '7MA CATEGORIA DAMAS', 32, '7MA CATEGORIA DAMAS', '2026-02-12 23:04:20'),
(54, 10, 44, '7MA CATEGORIA DAMAS', 27, '7MA CATEGORIA DAMAS', '2026-02-12 23:12:41'),
(55, 10, 9, '7MA CATEGORIA DAMAS', 31, '7MA CATEGORIA DAMAS', '2026-02-12 23:12:49'),
(56, 10, 28, '7MA CATEGORIA DAMAS', 42, '7MA CATEGORIA DAMAS', '2026-02-13 00:26:12'),
(63, 11, 17, '7MA CATEGORIA DAMAS', 29, '7MA CATEGORIA DAMAS', '2026-02-13 03:55:17'),
(64, 11, 20, '7MA CATEGORIA DAMAS', 32, '7MA CATEGORIA DAMAS', '2026-02-13 03:55:24'),
(65, 11, 27, '7MA CATEGORIA DAMAS', 40, '7MA CATEGORIA DAMAS', '2026-02-13 03:55:28'),
(66, 11, 44, '7MA CATEGORIA DAMAS', 9, '7MA CATEGORIA DAMAS', '2026-02-13 03:55:33'),
(67, 11, 41, '7MA CATEGORIA DAMAS', 28, '7MA CATEGORIA DAMAS', '2026-02-13 03:55:36'),
(68, 11, 34, '7MA CATEGORIA DAMAS', 42, '7MA CATEGORIA DAMAS', '2026-02-13 03:55:40'),
(69, 11, 16, '7MA CATEGORIA DAMAS', 21, '7MA CATEGORIA DAMAS', '2026-02-13 03:55:44'),
(70, 11, 23, '7MA CATEGORIA DAMAS', 14, '7MA CATEGORIA DAMAS', '2026-02-13 03:55:48'),
(71, 11, 26, '7MA CATEGORIA DAMAS', 37, '7MA CATEGORIA DAMAS', '2026-02-13 03:55:52'),
(72, 11, 15, '7MA CATEGORIA DAMAS', 30, '7MA CATEGORIA DAMAS', '2026-02-13 03:55:56'),
(73, 11, 18, '7MA CATEGORIA DAMAS', 31, '7MA CATEGORIA DAMAS', '2026-02-13 03:59:18'),
(74, 11, 33, '7MA CATEGORIA DAMAS', 39, '7MA CATEGORIA DAMAS', '2026-02-13 03:59:22'),
(75, 11, 19, '7MA CATEGORIA DAMAS', 24, '7MA CATEGORIA DAMAS', '2026-02-13 04:01:06'),
(76, 11, 35, '7MA CATEGORIA DAMAS', 10, '7MA CATEGORIA DAMAS', '2026-02-13 04:01:09'),
(77, 11, 22, '7MA CATEGORIA DAMAS', 43, '7MA CATEGORIA DAMAS', '2026-02-13 17:55:17'),
(78, 11, 45, '7MA CATEGORIA DAMAS', 13, '7MA CATEGORIA DAMAS', '2026-02-13 17:55:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torneo_zonas`
--

CREATE TABLE `torneo_zonas` (
  `id` int(11) NOT NULL,
  `torneo_id` int(11) NOT NULL,
  `codigo` varchar(5) NOT NULL,
  `orden` int(11) NOT NULL,
  `tamanio_objetivo` int(11) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `torneo_zonas`
--

INSERT INTO `torneo_zonas` (`id`, `torneo_id`, `codigo`, `orden`, `tamanio_objetivo`) VALUES
(50, 1, 'LIBRE', 7, 18),
(51, 1, 'A', 1, 3),
(52, 1, 'B', 2, 3),
(53, 1, 'C', 3, 3),
(54, 1, 'D', 4, 3),
(55, 1, 'E', 5, 3),
(56, 1, 'F', 6, 3),
(57, 8, 'LIBRE', 3, 6),
(58, 8, 'A', 1, 3),
(61, 8, 'B', 2, 3),
(62, 10, 'LIBRE', 5, 14),
(63, 10, 'A', 1, 3),
(64, 10, 'B', 2, 3),
(65, 10, 'C', 3, 4),
(66, 10, 'D', 4, 4),
(67, 11, 'LIBRE', 6, 16),
(68, 11, 'A', 1, 3),
(69, 11, 'B', 2, 3),
(70, 11, 'C', 3, 3),
(71, 11, 'D', 4, 3),
(72, 11, 'E', 5, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torneo_zona_equipos`
--

CREATE TABLE `torneo_zona_equipos` (
  `id` int(11) NOT NULL,
  `zona_id` int(11) NOT NULL,
  `equipo_id` int(11) NOT NULL,
  `posicion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `torneo_zona_equipos`
--

INSERT INTO `torneo_zona_equipos` (`id`, `zona_id`, `equipo_id`, `posicion`) VALUES
(145, 51, 10, 1),
(146, 51, 27, 2),
(147, 51, 16, 3),
(148, 52, 14, 1),
(149, 52, 21, 2),
(150, 52, 23, 3),
(151, 53, 12, 1),
(152, 53, 9, 2),
(153, 56, 11, 3),
(154, 54, 24, 1),
(155, 55, 18, 3),
(156, 54, 19, 2),
(157, 55, 17, 1),
(158, 55, 28, 2),
(159, 54, 26, 3),
(160, 53, 25, 3),
(161, 56, 22, 1),
(162, 56, 20, 2),
(163, 58, 29, 1),
(164, 58, 30, 2),
(165, 61, 31, 3),
(166, 61, 32, 1),
(167, 61, 33, 2),
(168, 58, 34, 3),
(169, 63, 42, 1),
(170, 63, 43, 3),
(171, 63, 45, 2),
(172, 64, 46, 1),
(173, 64, 47, 2),
(174, 64, 48, 3),
(175, 65, 49, 1),
(176, 65, 50, 2),
(177, 65, 51, 3),
(178, 65, 52, 4),
(179, 66, 53, 1),
(180, 66, 54, 2),
(181, 66, 55, 3),
(182, 66, 56, 4),
(183, 68, 57, 1),
(184, 71, 58, 3),
(185, 68, 59, 2),
(186, 69, 60, 1),
(187, 69, 61, 2),
(188, 69, 62, 3),
(189, 70, 63, 1),
(190, 70, 64, 2),
(191, 70, 65, 3),
(192, 71, 66, 1),
(193, 68, 67, 3),
(194, 71, 68, 2),
(195, 72, 69, 1),
(196, 72, 70, 2),
(197, 72, 71, 3),
(198, 72, 72, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol` enum('admin','usuario') DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `email`, `password`, `rol`, `activo`) VALUES
(5, 'admin@apiba.com', '$2y$10$PdEoLC/unCsJatYdVZZ9ouKlopL3YY4RYU5cmwaKZt9DzVYHSmi4.', 'admin', 1),
(7, '90spadel', '$2y$10$kx1oMkFcRZd4wOBPm6c2VObG5tEgh4zP/V/vaA9WPEPZvGl1gUWlK', 'admin', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carnets`
--
ALTER TABLE `carnets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jugador_id` (`jugador_id`);

--
-- Indices de la tabla `fixture`
--
ALTER TABLE `fixture`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `torneo_id` (`torneo_id`,`jugador_id`),
  ADD UNIQUE KEY `uniq_torneo_jugador` (`torneo_id`,`jugador_id`);

--
-- Indices de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uq_jugadores_email` (`email`),
  ADD UNIQUE KEY `uq_jugadores_dni` (`dni`);

--
-- Indices de la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indices de la tabla `puntos_torneo`
--
ALTER TABLE `puntos_torneo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_torneo_jugador` (`torneo_id`,`jugador_id`),
  ADD KEY `idx_torneo` (`torneo_id`),
  ADD KEY `idx_jugador` (`jugador_id`);

--
-- Indices de la tabla `ranking`
--
ALTER TABLE `ranking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ranking_jugador_id` (`jugador_id`);

--
-- Indices de la tabla `ranking_prev`
--
ALTER TABLE `ranking_prev`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `torneos`
--
ALTER TABLE `torneos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `torneo_equipos`
--
ALTER TABLE `torneo_equipos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_torneo` (`torneo_id`),
  ADD KEY `idx_j1` (`jugador1_id`),
  ADD KEY `idx_j2` (`jugador2_id`);

--
-- Indices de la tabla `torneo_zonas`
--
ALTER TABLE `torneo_zonas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_torneo_codigo` (`torneo_id`,`codigo`),
  ADD KEY `idx_torneo` (`torneo_id`);

--
-- Indices de la tabla `torneo_zona_equipos`
--
ALTER TABLE `torneo_zona_equipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_zona_pos` (`zona_id`,`posicion`),
  ADD UNIQUE KEY `uq_equipo` (`equipo_id`),
  ADD KEY `idx_zona` (`zona_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carnets`
--
ALTER TABLE `carnets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `fixture`
--
ALTER TABLE `fixture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `localidades`
--
ALTER TABLE `localidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `puntos_torneo`
--
ALTER TABLE `puntos_torneo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT de la tabla `ranking`
--
ALTER TABLE `ranking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=303;

--
-- AUTO_INCREMENT de la tabla `ranking_prev`
--
ALTER TABLE `ranking_prev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=290;

--
-- AUTO_INCREMENT de la tabla `torneos`
--
ALTER TABLE `torneos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `torneo_equipos`
--
ALTER TABLE `torneo_equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `torneo_zonas`
--
ALTER TABLE `torneo_zonas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `torneo_zona_equipos`
--
ALTER TABLE `torneo_zona_equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carnets`
--
ALTER TABLE `carnets`
  ADD CONSTRAINT `carnets_ibfk_1` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`);

--
-- Filtros para la tabla `torneo_zona_equipos`
--
ALTER TABLE `torneo_zona_equipos`
  ADD CONSTRAINT `fk_tze_zona` FOREIGN KEY (`zona_id`) REFERENCES `torneo_zonas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
