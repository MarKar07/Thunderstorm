-- ========================================
-- THUNDERSTORM ROCK FESTIVAL
-- Tietokantarakenne
-- ========================================
-- 
-- ASENNUSOHJEET:
-- 1. Luo tietokanta phpMyAdminissa tai komentorivillä
-- 2. Aja tämä SQL-tiedosto tietokantaan
-- 3. Kopioi config/database.example.php -> config/database.php
-- 4. Päivitä config/database.php:ssä omat tietokantasi tiedot
--
-- VALMIS ADMIN-TUNNUS TESTAUKSEEN:
-- Käyttäjä: admin
-- Salasana: admin123
-- ========================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ========================================
-- TIETOKANNAN LUOMINEN
-- ========================================
-- HUOM: Muuta "thunderstorm" omaksi tietokannan nimeksi tarvittaessa
CREATE DATABASE IF NOT EXISTS `thunderstorm` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `thunderstorm`;

-- ========================================
-- TAULUT
-- ========================================

-- --------------------------------------------------------
-- Taulu: users
-- Kuvaus: Käyttäjätiedot (sekä normaalit käyttäjät että adminit)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Taulu: profiles
-- Kuvaus: Käyttäjien lisätiedot (etunimi, sukunimi, puh, jne.)
-- --------------------------------------------------------
CREATE TABLE `profiles` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Taulu: event_registrations
-- Kuvaus: Festivaali-ilmoittautumiset ja lipputyypit
-- --------------------------------------------------------
CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `ticket_type` enum('day','weekend','vip') DEFAULT 'day',
  `status` enum('active','cancelled') DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Taulu: feedback
-- Kuvaus: Palautelomakkeen viestit
-- --------------------------------------------------------
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','read','resolved') DEFAULT 'new',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- TESTIDATAN LISÄÄMINEN
-- ========================================

-- Admin-käyttäjä (testikäyttöön)
-- Käyttäjätunnus: admin
-- Salasana: admin123
INSERT INTO `users` (`username`, `email`, `password`, `role`, `is_active`) VALUES
('admin', 'admin@thunderstormrock.fi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Tavallinen testikäyttäjä
-- Käyttäjätunnus: testuser
-- Salasana: test123
INSERT INTO `users` (`username`, `email`, `password`, `role`, `is_active`) VALUES
('testuser', 'test@example.com', '$2y$10$FQZq0wWfQKLXGKJ0wWfQKLXGKJ0wWfQKLXGKJ0wWfQKLXGKJ0wW', 'user', 1);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ========================================
-- VALMIS!
-- ========================================
-- Tietokanta on nyt valmis käytettäväksi.
-- Muista päivittää config/database.php:ssä omat tietosi!