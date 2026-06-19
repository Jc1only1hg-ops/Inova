-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-06-2026 a las 19:21:16
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
-- Base de datos: `ineova`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'Para saber a que relación pertenece papa.',
  `nombre_empresa` varchar(150) NOT NULL,
  `rif` varchar(20) NOT NULL COMMENT 'Registro de información fiscal, OBLIGATORIO!',
  `sitio_web` varchar(150) DEFAULT NULL COMMENT 'Tiene sitio web? si? lo coloca, no tiene? no lo coloca.',
  `plan_id` int(11) NOT NULL COMMENT 'Este campo guardará el número del plan de suscripción que pagaron, conectándose con tabla planes_suscripcion.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id`, `usuario_id`, `nombre_empresa`, `rif`, `sitio_web`, `plan_id`) VALUES
(3, 1, 'Serenos La Guaira', 'V-23452312', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante_habilidad`
--

CREATE TABLE `estudiante_habilidad` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `habilidad_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiante_habilidad`
--

INSERT INTO `estudiante_habilidad` (`id`, `estudiante_id`, `habilidad_id`) VALUES
(1, 1, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habilidades`
--

CREATE TABLE `habilidades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre de las habilidades, como: Javascript, PHP, MySQL, contabilidad, etc.',
  `tipo_habilidad` varchar(50) NOT NULL COMMENT 'Para separar si es "Técnica" o "Blanda/Soft skill"'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habilidades`
--

INSERT INTO `habilidades` (`id`, `nombre`, `tipo_habilidad`) VALUES
(3, 'MySQL', 'Técnica');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_estudiantes`
--

CREATE TABLE `perfil_estudiantes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'este campo conectará el perfil con su cuenta en la tabla usuarios.',
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `universidad_id` int(11) NOT NULL COMMENT 'Este campo guardará el número de la universidad a la que pertenece, conectándose con la tabla universidades',
  `telefono` varchar(20) NOT NULL,
  `autobiografia` text NOT NULL COMMENT 'aquí el estudiante escribirá una breve introducción sobre sí mismo y necesita espacio.',
  `github_url` varchar(255) NOT NULL COMMENT 'si el estudiante tiene repositorio, aqui mostrara sus proyectos.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfil_estudiantes`
--

INSERT INTO `perfil_estudiantes` (`id`, `usuario_id`, `nombre`, `apellido`, `universidad_id`, `telefono`, `autobiografia`, `github_url`) VALUES
(1, 2, 'Pedro\r\n', 'Pérez ', 1, '0412-1234567', 'Estudiante de ingeniería apasionado por el desarrollo backend y las bases de datos.', 'https://github.com/pedroperez');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes_suscripcion`
--

CREATE TABLE `planes_suscripcion` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL COMMENT 'Nombre de la suscripción, como: gratuito, premium o VIP.',
  `precio` decimal(10,2) NOT NULL COMMENT 'Para guardar el costo del plan.',
  `limite_busquedas` int(11) NOT NULL COMMENT 'Para limitar las búsquedas según el plan.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `planes_suscripcion`
--

INSERT INTO `planes_suscripcion` (`id`, `nombre`, `precio`, `limite_busquedas`) VALUES
(1, 'Bronce', 25.99, 25),
(7, 'Plata', 59.99, 50),
(8, 'Oro', 99.99, 9999),
(9, 'Gratuito', 0.00, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reseñas`
--

CREATE TABLE `reseñas` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reseñas`
--

INSERT INTO `reseñas` (`id`, `empresa_id`, `estudiante_id`, `comentario`, `fecha`) VALUES
(1, 3, 1, 'Excelente desempeño técnico durante la pasantía. Muy proactivo con el manejo de consultas SQL complejas.', '2026-05-31 04:06:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `universidades`
--

CREATE TABLE `universidades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL COMMENT 'Nombre de la universidad.',
  `siglas` varchar(20) NOT NULL COMMENT 'Siglas como por ejemplo: UCV, UCM, UC, etc.',
  `ranking_uni` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Número de ranking: 1 = mejor puesto, 0 = sin asignar.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `universidades`
--

INSERT INTO `universidades` (`id`, `nombre`, `siglas`, `ranking_uni`) VALUES
(1, 'Universidad marítima del caribe', 'UMC', 33);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `correo` varchar(100) NOT NULL COMMENT 'Correo electrónico de los usuarios Duh.',
  `contraseña` varchar(255) NOT NULL COMMENT 'password de los usuarios por Dios.',
  `rol` varchar(20) NOT NULL COMMENT 'Esto le dira al sistema que usuario inicio sesión, estudiante, empresa, admin.',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Se guarda automáticamente la fecha de registro.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `correo`, `contraseña`, `rol`, `fecha_registro`) VALUES
(1, 'ale2305@gmail.com', '12345', 'Empresa', '2026-05-31 00:40:15'),
(2, 'pepecara@gmail.com', '13452', 'Estudiante', '2026-05-31 00:41:34'),
(3, 'Anton@gmail.com', '1923n', 'Empresa', '2026-05-21 03:53:01');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rif_unico` (`rif`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indices de la tabla `estudiante_habilidad`
--
ALTER TABLE `estudiante_habilidad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `habilidad_id` (`habilidad_id`);

--
-- Indices de la tabla `habilidades`
--
ALTER TABLE `habilidades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `perfil_estudiantes`
--
ALTER TABLE `perfil_estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `universidad_id` (`universidad_id`);

--
-- Indices de la tabla `planes_suscripcion`
--
ALTER TABLE `planes_suscripcion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reseñas`
--
ALTER TABLE `reseñas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `estudiante_id` (`estudiante_id`);

--
-- Indices de la tabla `universidades`
--
ALTER TABLE `universidades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo_unico` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `estudiante_habilidad`
--
ALTER TABLE `estudiante_habilidad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `habilidades`
--
ALTER TABLE `habilidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `perfil_estudiantes`
--
ALTER TABLE `perfil_estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `planes_suscripcion`
--
ALTER TABLE `planes_suscripcion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `reseñas`
--
ALTER TABLE `reseñas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `universidades`
--
ALTER TABLE `universidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `empresas_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `planes_suscripcion` (`id`);

--
-- Filtros para la tabla `estudiante_habilidad`
--
ALTER TABLE `estudiante_habilidad`
  ADD CONSTRAINT `estudiantes_fk` FOREIGN KEY (`estudiante_id`) REFERENCES `perfil_estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `habilidades_fk` FOREIGN KEY (`habilidad_id`) REFERENCES `habilidades` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `perfil_estudiantes`
--
ALTER TABLE `perfil_estudiantes`
  ADD CONSTRAINT `universidad_fk` FOREIGN KEY (`universidad_id`) REFERENCES `universidades` (`id`),
  ADD CONSTRAINT `usuarios_fk` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reseñas`
--
ALTER TABLE `reseñas`
  ADD CONSTRAINT `empresa_fk` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estudiante_fk` FOREIGN KEY (`estudiante_id`) REFERENCES `perfil_estudiantes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
