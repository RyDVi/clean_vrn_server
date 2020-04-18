-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Апр 18 2020 г., 14:22
-- Версия сервера: 10.4.11-MariaDB
-- Версия PHP: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `cleanvrn`
--

-- --------------------------------------------------------

--
-- Структура таблицы `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `name` varchar(63) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `games`
--

INSERT INTO `games` (`id`, `id_status`, `name`, `description`, `route`, `datetime`) VALUES
(50, 1, 'Игра на березке', NULL, '70м, 20м', '2020-05-15 10:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `games_places`
--

CREATE TABLE `games_places` (
  `id` int(11) NOT NULL,
  `id_game` int(11) NOT NULL,
  `id_place_type` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `point` point DEFAULT NULL,
  `polygon` polygon DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `games_places`
--

INSERT INTO `games_places` (`id`, `id_game`, `id_place_type`, `description`, `point`, `polygon`) VALUES
(34, 50, 5, '', NULL, 0x0000000001030000000100000008000000743c1f7964d94940deff8fa8539c43400b5aefb2add94940e5ffbf35979b4340db45fbdcf2d94940e9ffaf56779b43407c6cf9d435da4940b9ff0f73cd9b43404b74060044da4940210080651f9c43407cf405ba21da49404200800d599c434012999873fed949401b00401c7b9c4340743c1f7964d94940deff8fa8539c4340),
(35, 50, 4, 'Старт у площадки', 0x0000000001010000005cdb36dee8d94940090050666c9c4340, NULL),
(36, 50, 3, '', 0x0000000001010000000bb9aa0632da4940c9ff7f4f179c4340, NULL),
(37, 50, 3, '', 0x00000000010100000098d0dbb13cda49403800c036689c4340, NULL),
(38, 50, 3, '', 0x00000000010100000028efba321cda4940c3ffcf0d819c4340, NULL),
(39, 50, 3, '', 0x0000000001010000003db02cc9c9d94940230050aadd9b4340, NULL),
(40, 50, 3, '', 0x000000000101000000c07932840ada49403c004039e69b4340, NULL),
(41, 50, 3, '', 0x000000000101000000d8ebe8d9c6d949400100f0db479c4340, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `game_statuses`
--

CREATE TABLE `game_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(63) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `game_statuses`
--

INSERT INTO `game_statuses` (`id`, `name`) VALUES
(1, 'Начата'),
(2, 'Окончена'),
(3, 'Запланировано');

-- --------------------------------------------------------

--
-- Структура таблицы `game_users`
--

CREATE TABLE `game_users` (
  `id` int(11) NOT NULL,
  `id_game` int(11) NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `game_users`
--

INSERT INTO `game_users` (`id`, `id_game`, `id_user`) VALUES
(27, 50, 32);

-- --------------------------------------------------------

--
-- Структура таблицы `garbages`
--

CREATE TABLE `garbages` (
  `id` int(11) NOT NULL,
  `name` varchar(63) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `garbages`
--

INSERT INTO `garbages` (`id`, `name`) VALUES
(1, 'Пластик'),
(2, 'Стекло');

-- --------------------------------------------------------

--
-- Структура таблицы `garbage_coefficients`
--

CREATE TABLE `garbage_coefficients` (
  `id` int(11) NOT NULL,
  `id_garbage` int(11) NOT NULL,
  `id_game` int(11) NOT NULL,
  `coefficient` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `garbage_coefficients`
--

INSERT INTO `garbage_coefficients` (`id`, `id_garbage`, `id_game`, `coefficient`) VALUES
(48, 1, 50, 1),
(49, 2, 50, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `name` varchar(63) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `places`
--

INSERT INTO `places` (`id`, `name`) VALUES
(1, 'Другое'),
(2, 'Туалет'),
(3, 'Отходы, мусор'),
(4, 'Место старта'),
(5, 'Зона проведения игры');

-- --------------------------------------------------------

--
-- Структура таблицы `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `name` varchar(63) NOT NULL,
  `id_game` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `teams`
--

INSERT INTO `teams` (`id`, `number`, `name`, `id_game`) VALUES
(36, 1, 'пупсики', 50),
(37, 2, 'Колбаски', 50);

-- --------------------------------------------------------

--
-- Структура таблицы `teams_garbages`
--

CREATE TABLE `teams_garbages` (
  `id` int(11) NOT NULL,
  `id_team` int(11) NOT NULL,
  `id_garbage` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `teams_garbages`
--

INSERT INTO `teams_garbages` (`id`, `id_team`, `id_garbage`, `count`) VALUES
(39, 36, 1, 5),
(40, 36, 2, 10),
(41, 37, 1, 50),
(42, 37, 2, 50);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `id_type` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `middlename` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(63) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `id_type`, `firstname`, `lastname`, `middlename`, `email`, `phone`, `password`) VALUES
(3, 1, 'Админ', 'Админ', 'Админ', 'admin@mail.ru', '8-800-888-88-88', ''),
(4, 1, 'Пуп22', 'Пупочкин2', 'Пупочкович2', 'pup@mail.ru', '8-800-555-36-36', '202cb962ac59075b964b07152d234b70'),
(5, 2, 'Test1', 'TEst1', 'Test1', 'Test1', '', ''),
(6, 2, 'Test1T', 'Test1', 'Test1', 'Test1', '', ''),
(7, 2, 'Test', 'Test', 'Test', 'Test', '', ''),
(8, 2, 'Test', 'Test', 'Test', 'Test', '', ''),
(9, 2, 'Test', 'Test', 'Test', 'Test', '', ''),
(10, 2, 'Test', 'TEest', 'TEst', 'TEst', '', ''),
(20, 2, 'Дмитрий', 'Рябчунов', 'Витальевич', 'dmitrii.kardan.rch@mail.ru', '89204071414', '7338f13a99552041fc9bfc492510d280'),
(26, 2, 'Анатолий', 'Анатольев', 'Сергеевич', 'anatoliev.a.s@mail.com', '8005553535', ''),
(28, 2, 'asdfaaabb', 'asdfaaabb', 'aasd', 'sadfa@mail.ru', '1231124114', ''),
(29, 2, 'asdf', 'asdf', '', 'dsfsafsd@mail.ru', '1231234141', 'f23c969c77c9a5d4942c5e90c164f6ee'),
(30, 2, 'asdfas', 'asdfas', '', 'asdf@mail.ru', '1231412235', ''),
(31, 2, 'asdf', 'sdaf', '', 'asdfas@mail.ru', '1231231232', ''),
(32, 2, 'Дмитрий', 'Рябчунов', 'Витальевич', 'dmitrii.kardan.rch@mail.ru', '9204071414', '65ff09eac2e32bd3b4fbf2e434e007fb');

-- --------------------------------------------------------

--
-- Структура таблицы `users_types`
--

CREATE TABLE `users_types` (
  `id` int(11) NOT NULL,
  `name` varchar(31) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users_types`
--

INSERT INTO `users_types` (`id`, `name`) VALUES
(1, 'Администратор'),
(2, 'Организатор'),
(3, 'Участник');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `games`
--
ALTER TABLE `games`
  ADD UNIQUE KEY `id_games` (`id`),
  ADD KEY `id_status` (`id_status`);

--
-- Индексы таблицы `games_places`
--
ALTER TABLE `games_places`
  ADD UNIQUE KEY `id_games_places` (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_game` (`id_game`,`id_place_type`),
  ADD KEY `games_places_ibfk_1` (`id_place_type`);

--
-- Индексы таблицы `game_statuses`
--
ALTER TABLE `game_statuses`
  ADD UNIQUE KEY `id_game_statuses` (`id`);

--
-- Индексы таблицы `game_users`
--
ALTER TABLE `game_users`
  ADD UNIQUE KEY `id_game_users` (`id`),
  ADD KEY `id_game` (`id_game`),
  ADD KEY `game_users_ibfk_2` (`id_user`);

--
-- Индексы таблицы `garbages`
--
ALTER TABLE `garbages`
  ADD UNIQUE KEY `id_garbage` (`id`);

--
-- Индексы таблицы `garbage_coefficients`
--
ALTER TABLE `garbage_coefficients`
  ADD PRIMARY KEY (`id_garbage`,`id_game`),
  ADD UNIQUE KEY `id_garbage_coefficients` (`id`),
  ADD KEY `garbage_coefficients_ibfk_1` (`id_game`);

--
-- Индексы таблицы `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `places`
--
ALTER TABLE `places`
  ADD UNIQUE KEY `id_places` (`id`);

--
-- Индексы таблицы `teams`
--
ALTER TABLE `teams`
  ADD UNIQUE KEY `id_teams` (`id`),
  ADD KEY `id_game` (`id_game`);

--
-- Индексы таблицы `teams_garbages`
--
ALTER TABLE `teams_garbages`
  ADD PRIMARY KEY (`id_team`,`id_garbage`),
  ADD UNIQUE KEY `id_garbages` (`id`),
  ADD KEY `id_garbage` (`id_garbage`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `id_users` (`id`),
  ADD KEY `id_type` (`id_type`);

--
-- Индексы таблицы `users_types`
--
ALTER TABLE `users_types`
  ADD UNIQUE KEY `id_users_types` (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT для таблицы `games_places`
--
ALTER TABLE `games_places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT для таблицы `game_statuses`
--
ALTER TABLE `game_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `game_users`
--
ALTER TABLE `game_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT для таблицы `garbages`
--
ALTER TABLE `garbages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `garbage_coefficients`
--
ALTER TABLE `garbage_coefficients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT для таблицы `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT для таблицы `teams_garbages`
--
ALTER TABLE `teams_garbages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT для таблицы `users_types`
--
ALTER TABLE `users_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`id_status`) REFERENCES `game_statuses` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `games_places`
--
ALTER TABLE `games_places`
  ADD CONSTRAINT `games_places_ibfk_1` FOREIGN KEY (`id_place_type`) REFERENCES `places` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `games_places_ibfk_2` FOREIGN KEY (`id_game`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `game_users`
--
ALTER TABLE `game_users`
  ADD CONSTRAINT `game_users_ibfk_1` FOREIGN KEY (`id_game`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `game_users_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `garbage_coefficients`
--
ALTER TABLE `garbage_coefficients`
  ADD CONSTRAINT `garbage_coefficients_ibfk_1` FOREIGN KEY (`id_game`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `garbage_coefficients_ibfk_2` FOREIGN KEY (`id_garbage`) REFERENCES `garbages` (`id`);

--
-- Ограничения внешнего ключа таблицы `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`id_game`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `teams_garbages`
--
ALTER TABLE `teams_garbages`
  ADD CONSTRAINT `teams_garbages_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teams_garbages_ibfk_2` FOREIGN KEY (`id_garbage`) REFERENCES `garbages` (`id`);

--
-- Ограничения внешнего ключа таблицы `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_type`) REFERENCES `users_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
