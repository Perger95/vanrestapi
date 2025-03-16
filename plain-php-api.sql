-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Már 16. 02:29
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
(3, 1, 'Kertészkedés', '2025-04-12 10:00:00', 'Nagyon jo sütögetés lesz a teraszon'),
(4, 2, 'Debreceni kertvárosi napok', '2025-06-11 11:00:00', 'Koncert a békástónál.'),
(5, 1, 'Új esemény', '2026-11-11 11:11:00', 'Családi ebéd Alinával az étteremben'),
(17, 3, 'Első Eseményem', '2025-03-20 22:20:00', 'Buli a haverokkal'),
(22, 1, 'UCC interjú', '2025-03-22 10:10:00', 'Menni fog!'),
(23, 2, 'UCC interjú kiválasztás', '2025-03-22 10:00:00', 'Sikerül az interjúm!');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `faq`
--

INSERT INTO `faq` (`id`, `question`, `answer`) VALUES
(1, 'Hogyan frissíthetek eseményt?', 'Az esemény frissítéséhez írd be tapsolj 3mat és kiálts hangosan!'),
(2, 'Hogyan hozhatok létre új eseményt?', 'Lépjen be a fiókjába, majd kattintson az \"Új esemény létrehozása\" gombra. Írja be az esemény részleteit, majd mentse el.'),
(3, 'Hogyan törölhetek egy eseményt?', 'Kattintson az esemény nevére, majd válassza a \"Törlés\" lehetőséget. A rendszer kér egy megerősítést.'),
(4, 'Miért nem tudok belépni?', 'Győződjön meg róla, hogy helyes e-mail címet és jelszót adott meg. Ha elfelejtette jelszavát, használja a \"Forgot password\" lehetőséget.'),
(5, 'Mennyi ideig maradok bejelentkezve?', 'A bejelentkezés után a rendszer 1 órán át aktív marad. Ha inaktív marad, automatikusan kijelentkezteti Önt.'),
(6, 'Hogyan változtathatom meg a jelszavam?', 'Kattintson a \"Forgot password\" lehetőségre a belépési oldalon, adja meg az e-mail címét, és kövesse az e-mailben kapott utasításokat.'),
(7, 'Hogyan módosíthatom egy esemény adatait?', 'Az esemény nevére kattintva válassza a \"Szerkesztés\" lehetőséget, majd módosítsa a kívánt mezőket. A módosításokat mentse el.'),
(8, 'Mi történik, ha az AI nem tud válaszolni?', 'Ha az AI nem talál megfelelő választ, lehetősége lesz kapcsolatba lépni egy operátorral (ha ez az opció elérhető lesz a jövőben).'),
(9, 'How can I update an event?', 'To update the event, clap three times and shout loudly!'),
(10, 'How can I create a new event?', 'Log into your account, then click on the \"Create New Event\" button. Enter the event details and save it.'),
(11, 'How can I delete an event?', 'Click on the event name, then select \"Delete\". The system will ask for confirmation.'),
(12, 'Why cant I log in?', 'Make sure you have entered the correct email and password. If you forgot your password, use the \"Forgot password\" option.'),
(13, 'How long will I stay logged in?', 'After logging in, the system will remain active for 1 hour. If you stay inactive, you will be automatically logged out.'),
(14, 'How can I change my password?', 'Click on the \"Forgot password\" option on the login page, enter your email, and follow the instructions sent in the email.'),
(15, 'How can I modify the details of an event?', 'Click on the event name, select \"Edit\", and modify the desired fields. Then, save the changes.'),
(16, 'What happens if the AI cannot answer?', 'If the AI cannot find an appropriate answer, you may have the option to contact an operator (if this option becomes available in the future).');

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
  `reset_expires` datetime DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `token`, `reset_token`, `reset_expires`, `token_expires`) VALUES
(1, 'miskolc@nemletezik.cc', '$2y$10$dHftRI7NaNixJJ7KPdGEMOOgDYCRkwMG6ugovIfhHqaO8C2PUt892', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJleHAiOjE3NDIwOTA4NDZ9.UFVeQYi7Z7W614y2rPceqGy0REOw70QYvGcxIqnIEhk', 'a9a182c0fdb47a384f0ea05fe953ecf2629b941467bc24d703bb3adc3aed92e2', '2025-03-16 01:46:48', '2025-03-16 03:07:26'),
(2, 'debrecen@nemletezik.cc', '$2a$12$fhLzoAX5BdLoxzTZb0rjAOitsEAcpoBOeQH5vgTPM5jw21i2ufSAG', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoyLCJleHAiOjE3NDIwODk3Mzl9.nhKRoUF1YaKVgPSfhVHHk8erL-WW5-_RWav1iCrfhsg', '', '2025-03-14 23:34:07', '2025-03-16 02:48:59'),
(3, 'perger.tamas95@gmail.com', '$2a$12$Y5Aq55MJXOco9TmzFVxQrex/iBQwKHWBKGEMGO.axhUJy/RcRlYRy', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjozLCJleHAiOjE3NDIwNzU4NzR9.ZyHWWadlcXgrPEgxCK_5pQZ01a8LHlIughYrVDMHIZA', NULL, NULL, '2025-03-15 22:57:54');

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
-- A tábla indexei `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT a táblához `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT a táblához `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
