-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 13.10.2025 klo 08:40
-- Palvelimen versio: 10.6.22-MariaDB-0ubuntu0.22.04.1
-- PHP Version: 8.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `markar`
--

-- --------------------------------------------------------

--
-- Rakenne taululle `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `ticket_type` enum('day','weekend','vip') DEFAULT 'day',
  `status` enum('active','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `user_id`, `registration_date`, `ticket_type`, `status`) VALUES
(1, 2, '2025-09-22 10:06:36', 'day', 'cancelled'),
(2, 2, '2025-09-22 10:57:56', 'vip', 'active'),
(4, 5, '2025-10-08 12:20:44', 'day', 'active');

-- --------------------------------------------------------

--
-- Rakenne taululle `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','read','resolved') DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `status`) VALUES
(1, 'Esko', 'esko@mail.com', 'Yleinen palaute', 'hyvät vestivaalit!', '2025-10-03 11:14:43', 'read'),
(2, 'Esko Esimerkki', 'esko@mail.com', 'Muu', 'Toimiiko tämä puhelimellakin?!', '2025-10-03 15:42:53', 'resolved'),
(3, 'Esko', 'esko@mail.com', 'Yleinen palaute', 'tämä on yleinen palaute', '2025-10-08 12:17:42', 'new'),
(4, 'Testaaja', 'testaaja@mail.com', 'Yleinen palaute', 'testauspalaute', '2025-10-08 12:29:03', 'new');

-- --------------------------------------------------------

--
-- Rakenne taululle `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token`, `created_at`, `expires_at`, `used`) VALUES
(1, 2, '161428bb0476d2a5bf9fe29d66079322a97d1c13fb38697709004e507f54d79e', '2025-10-10 08:30:27', '2025-10-10 09:30:27', 0),
(2, 2, '88c423dc9892d475767d31a1956aa8db8287e684cdfcfd1ed228b3f13b2017e2', '2025-10-10 08:34:08', '2025-10-10 09:34:08', 0),
(3, 2, '3f5003e657b624d2cc64eadd9b24778f730f0687a681260f7809ddd544276e56', '2025-10-10 08:36:31', '2025-10-10 09:36:31', 0),
(4, 5, 'fad5fc76d93f652d34b47efa4fd7216bc7b85064c798c6fbceb7e06d5146c7b7', '2025-10-10 08:57:08', '2025-10-10 09:57:08', 1);

-- --------------------------------------------------------

--
-- Rakenne taululle `profiles`
--

CREATE TABLE `profiles` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `profiles`
--

INSERT INTO `profiles` (`user_id`, `first_name`, `last_name`, `phone`, `age`, `city`, `created_at`) VALUES
(2, 'Esko', 'Esimerkki', '050 123 123 1234', 70, 'Tampere', '2025-10-02 08:03:06'),
(5, 'Test', 'Aaja', '05012345678', 66, 'Helsinki', '2025-10-08 12:20:20');

-- --------------------------------------------------------

--
-- Rakenne taululle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `is_active`) VALUES
(2, 'Esko Esimerkki', 'esko@mail.com', '$2y$10$Ail7WTVvNpHOF1/4UYEMzOj25l2fy.icJtQ/OTEnJMlEZtVwLyZ0C', 'user', '2025-09-22 09:58:34', 1),
(3, 'admin', 'admin@rockfestival.com', '$2y$10$46sLbbmYoLeYFoblJ8.ft.gfn2hObrPy5FQft09eXL.yqCqXKX.Ga', 'admin', '2025-09-22 10:16:02', 1),
(5, 'Testaaja', 'testaaja@mail.com', '$2y$10$pFYARVQ70SZqGWAqee6MvO0WsW4jz8du.iLYm4FK4MhhlJrarC8D6', 'user', '2025-10-08 12:19:51', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`),
  ADD KEY `idx_token_used` (`token`,`used`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Rajoitteet vedostauluille
--

--
-- Rajoitteet taululle `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
