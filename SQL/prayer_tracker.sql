-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 04:20 AM
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
-- Database: `prayer_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `google_id` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(500) DEFAULT NULL,
  `locale` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NOT NULL DEFAULT current_timestamp(),
  `session_token` varchar(255) DEFAULT NULL,
  `session_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `google_id`, `email`, `full_name`, `first_name`, `last_name`, `profile_picture`, `locale`, `created_at`, `updated_at`, `last_login`, `session_token`, `session_expiry`) VALUES
(1, '118380663984086018501', 'mostafamta347@gmail.com', 'Mostafam Ta3', 'Mostafam', 'Ta3', 'https://lh3.googleusercontent.com/a/ACg8ocKoPaBU55MVAUPWK8vAXRZL5l2zajBxEXOg3H-IujH4wFfVIA=s96-c', '', '2025-12-03 02:02:46', '2025-12-03 02:39:41', '2025-12-03 02:39:41', 'ac0409c5818883918bfb2cd8129b77ae52df6892999826c9c72dae9c755f3b75', '2026-02-01 03:39:41'),
(2, '107681726258782612532', 'sahatalllm@gmail.com', 'Sahat Al_llm', 'Sahat', 'Al_llm', 'https://lh3.googleusercontent.com/a/ACg8ocLua0dLyyBRKQ3lCqXBjm52mxRrPBZ1kniBT1WPVfq_Tn4LsQ=s96-c', '', '2025-12-03 02:07:35', '2025-12-03 02:54:53', '2025-12-03 02:54:53', 'c77722a304019fd324be5ac6a387def6d3fa6480fb1aff846c41c045ed65b0c9', '2026-02-01 03:54:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_session_expiry` (`session_expiry`),
  ADD KEY `idx_last_login` (`last_login`),
  ADD KEY `idx_users_google_id` (`google_id`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
