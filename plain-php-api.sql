-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Már 14. 13:43
-- Kiszolgáló verziója: 10.4.28-MariaDB
-- PHP verzió: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `plain-php-api`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `occurrence` datetime NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `events`
--

INSERT INTO `events` (`id`, `user_id`, `title`, `occurrence`, `description`) VALUES
(1, 1, 'PHP Workshop', '2025-03-15 14:00:00', 'lets patch this desc!'),
(5, 1, 'PHP Workshop', '2025-03-15 14:00:00', 'this event is the number 5!'),
(6, 1, 'PHP Workshop', '2025-03-15 14:00:00', 'hello this is another Test which should be num 6!');

-- --------------------------------------------------------

--
-- A nézet helyettes szerkezete `gf`
-- (Lásd alább az aktuális nézetet)
--
CREATE TABLE `gf` (
`id` int(11)
,`email` varchar(255)
,`password` varchar(64)
,`token` varchar(255)
);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `last_attempt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip_address`, `failed_attempts`, `last_attempt`) VALUES
(6, '127.0.0.1', 6, '2025-03-14 13:16:34');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `products`
--

CREATE TABLE `products` (
  `id` smallint(5) NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_hungarian_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_hungarian_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_hungarian_ci NOT NULL,
  `picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_hungarian_ci NOT NULL,
  `price` mediumint(9) NOT NULL,
  `stock` mediumint(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `products`
--

INSERT INTO `products` (`id`, `category`, `name`, `description`, `picture`, `price`, `stock`) VALUES
(1, 'Bogyós', 'Málna', 'Kézzel termelt egészség', 'https://upload.wikimedia.org/wikipedia/commons/a/a2/Raspberries_%28Rubus_Idaeus%29.jpg', 3800, 500),
(2, 'Bogyós', 'áfonya', 'Kézzel termelt finomság', 'peldaurl', 3200, 120),
(3, 'Bogyós', 'szeder', 'Kézzel termelt királyság', 'peldaurl1', 4000, 350),
(5, 'Bogyós', 'Eper', 'Egy tavaszi harapás', 'https://hur.webmania.cc/img/eper.jpg', 1440, 0),
(6, 'Bogyós', 'Homoktövis', 'Mezei csemege', 'https://hur.webmania.cc/img/homoktovis.jpg', 3200, 100),
(7, 'Bogyós', 'Som', 'A fanyar gyönyör', 'https://hur.webmania.cc/img/som.jpg', 900, 10),
(8, 'Bogyós', 'Fanyarka', 'Édes mint a méz', 'https://hur.webmania.cc/img/fanyarka.jpg', 990, 25),
(9, 'Bogyós', 'Piszke', 'Egres', 'https://hur.webmania.cc/img/piszke.jpg', 750, 100),
(10, 'Bogyós', 'Ribizli', 'Fanyar, vasban gazdag', 'https://hur.webmania.cc/img/ribizli.jpg', 1300, 170),
(11, 'Magyaros', 'Meggy', 'A falusi kincs', 'https://hur.webmania.cc/img/meggy.jpg', 600, 300),
(12, 'Magyaros', 'Szilva', 'A falusi kincs', 'https://hur.webmania.cc/img/szilva.jpg', 770, 200),
(13, 'Magyaros', 'cseresznye', 'finom jóféle', 'https://cseresznyeinfo.hu/wp-content/uploads/2021/01/cseresznye_erese.jpg', 2050, 600),
(14, 'Ruha', 'farmernadrág', 'de szép nadrág ez', 'http://valami.huhuh/pic.jpg', 500, 12),
(15, 'Ruha', 'zokni', 'de szép zokni ez', 'http://valami.huhuh/pic.jpg', 200, 100),
(16, 'Ruha', 'alsogatya', 'de szép bugyi ez', 'http://valami.huhuh/pic.jpg', 240, 150);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(64) DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `token`, `reset_token`, `reset_expires`) VALUES
(1, 'miskolc@nemletezik.cc', 'e91a001f61abe3f78f66f8db3dbb390967aa3c759b0632bc47b32bf9757d367d', '270%jidO4oJKiDuij$dja23!JfjIIFO4oloD', NULL, NULL);

-- --------------------------------------------------------

--
-- Nézet szerkezete `gf`
--
DROP TABLE IF EXISTS `gf`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `gf`  AS SELECT `users`.`id` AS `id`, `users`.`email` AS `email`, `users`.`password` AS `password`, `users`.`token` AS `token` FROM `users` ;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT a táblához `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT a táblához `products`
--
ALTER TABLE `products`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
