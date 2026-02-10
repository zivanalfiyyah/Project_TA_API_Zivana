-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2026 at 10:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_zivana_ta`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_zivana`
--

CREATE TABLE `chat_zivana` (
  `id_chat` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `input_text` text NOT NULL,
  `fitur` enum('rewrite','adaptation') NOT NULL,
  `mode_rewrite` enum('perbaiki_error','rapikan_struktur','konversi_bahasa','jelaskan_kode') DEFAULT NULL,
  `bahasa_tujuan` varchar(50) DEFAULT NULL,
  `gaya_bahasa` enum('formal','santai','akademis') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `result_zivana`
--

CREATE TABLE `result_zivana` (
  `id_result` int(11) NOT NULL,
  `id_chat` int(11) NOT NULL,
  `output_text` text NOT NULL,
  `ringkasan_perubahan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_zivana`
--

CREATE TABLE `user_zivana` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_zivana`
--

INSERT INTO `user_zivana` (`id_user`, `nama`, `email`, `password`, `created_at`, `role`) VALUES
(3, 'ciel', 'ciel@gmail.com', '$2y$10$57A8T6J/3BeNmR3jmSQ3OeWheaMXTjIsxDNdY6zkzfy1SM5hppDXa', '2026-02-10 07:14:05', 'user'),
(5, 'admin', 'admin@ai.com', '$2y$10$eUxZXP43uJvY6CQ1Uz7MTOzParoT9xO5sybVPN2dkbDSUEvJ3FCmO', '2026-02-10 08:08:13', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_zivana`
--
ALTER TABLE `chat_zivana`
  ADD PRIMARY KEY (`id_chat`),
  ADD KEY `fk_chat_user` (`id_user`);

--
-- Indexes for table `result_zivana`
--
ALTER TABLE `result_zivana`
  ADD PRIMARY KEY (`id_result`),
  ADD KEY `fk_result_chat` (`id_chat`);

--
-- Indexes for table `user_zivana`
--
ALTER TABLE `user_zivana`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_zivana`
--
ALTER TABLE `chat_zivana`
  MODIFY `id_chat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `result_zivana`
--
ALTER TABLE `result_zivana`
  MODIFY `id_result` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_zivana`
--
ALTER TABLE `user_zivana`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_zivana`
--
ALTER TABLE `chat_zivana`
  ADD CONSTRAINT `chat_zivana_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user_zivana` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chat_user` FOREIGN KEY (`id_user`) REFERENCES `user_zivana` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `result_zivana`
--
ALTER TABLE `result_zivana`
  ADD CONSTRAINT `fk_result_chat` FOREIGN KEY (`id_chat`) REFERENCES `chat_zivana` (`id_chat`) ON DELETE CASCADE,
  ADD CONSTRAINT `result_zivana_ibfk_1` FOREIGN KEY (`id_chat`) REFERENCES `chat_zivana` (`id_chat`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
