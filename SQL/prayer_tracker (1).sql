-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 02:19 PM
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
-- Table structure for table `nafl_prayers`
--

CREATE TABLE `nafl_prayers` (
  `id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `nafl_type` varchar(50) NOT NULL,
  `rakats` int(11) NOT NULL,
  `status` enum('completed','missed') DEFAULT 'completed',
  `prayer_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nafl_prayers`
--

INSERT INTO `nafl_prayers` (`id`, `record_id`, `nafl_type`, `rakats`, `status`, `prayer_time`) VALUES
(1, 1, 'نافلة 1', 2, 'completed', '2025-12-03 04:34:27'),
(2, 2, 'نافلة 1', 2, 'missed', NULL),
(3, 2, 'نافلة 2', 2, 'missed', NULL),
(4, 3, 'نافلة 1', 2, 'completed', '2025-12-03 04:35:13'),
(5, 4, 'نافلة 1', 2, 'completed', '2025-12-03 04:35:39'),
(8, 7, 'نافلة 1', 2, 'completed', '2025-12-03 04:36:52'),
(9, 6, 'نافلة 1', 2, 'completed', '2025-12-03 04:37:44'),
(11, 10, 'نافلة 1', 2, NULL, '2025-12-03 04:38:11'),
(12, 5, 'نافلة 1', 2, 'missed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `prayer_records`
--

CREATE TABLE `prayer_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prayer_name` varchar(50) NOT NULL,
  `prayer_date` date NOT NULL,
  `status` enum('missed','alone','mosque','delayed') NOT NULL,
  `is_special` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `main_prayer_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prayer_records`
--

INSERT INTO `prayer_records` (`id`, `user_id`, `prayer_name`, `prayer_date`, `status`, `is_special`, `notes`, `main_prayer_time`, `created_at`, `updated_at`) VALUES
(1, 2, 'fajr', '2025-12-03', 'mosque', 0, NULL, '2025-12-03 05:34:27', '2025-12-03 03:34:27', '2025-12-03 03:34:27'),
(2, 2, 'dhuhr', '2025-12-03', 'mosque', 0, NULL, '2025-12-03 05:34:39', '2025-12-03 03:34:39', '2025-12-03 03:34:39'),
(3, 2, 'tahajjud', '2025-12-03', 'mosque', 0, NULL, '2025-12-03 05:35:13', '2025-12-03 03:35:13', '2025-12-03 03:35:13'),
(4, 2, 'asr', '2025-12-03', 'alone', 0, NULL, '2025-12-03 05:35:39', '2025-12-03 03:35:39', '2025-12-03 03:35:39'),
(5, 2, 'maghrib', '2025-12-03', 'mosque', 0, NULL, '2025-12-03 05:46:47', '2025-12-03 03:35:49', '2025-12-03 03:46:47'),
(6, 2, 'isha', '2025-12-03', 'missed', 0, NULL, '2025-12-03 05:35:53', '2025-12-03 03:35:53', '2025-12-03 03:37:44'),
(7, 2, 'duha', '2025-12-03', 'mosque', 0, NULL, '2025-12-03 05:36:52', '2025-12-03 03:36:52', '2025-12-03 03:36:52'),
(10, 2, 'witr', '2025-12-03', 'alone', 0, NULL, '2025-12-03 05:38:11', '2025-12-03 03:38:11', '2025-12-03 03:38:11');

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
(2, '107681726258782612532', 'sahatalllm@gmail.com', 'Sahat Al_llm', 'Sahat', 'Al_llm', 'https://lh3.googleusercontent.com/a/ACg8ocLua0dLyyBRKQ3lCqXBjm52mxRrPBZ1kniBT1WPVfq_Tn4LsQ=s96-c', '', '2025-12-03 02:07:35', '2025-12-03 02:54:53', '2025-12-03 02:54:53', 'c77722a304019fd324be5ac6a387def6d3fa6480fb1aff846c41c045ed65b0c9', '2026-02-01 03:54:53'),
(3, '110520728785467475730', 'miraatalmumin@gmail.com', 'Miraat al-Mumin', 'Miraat', 'al-Mumin', 'https://lh3.googleusercontent.com/a/ACg8ocK4gVWJYCKqft4oVQh15lUYBiE6TzMKORmIKykivgV57q044Q=s96-c', '', '2025-12-03 12:45:41', '2025-12-03 12:45:42', '2025-12-03 12:45:42', 'e630cd8b0b81a08e736281a18fe6f20aa6de9e2b182b9e889f30f718baabb676', '2026-02-01 13:45:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `nafl_prayers`
--
ALTER TABLE `nafl_prayers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `prayer_records`
--
ALTER TABLE `prayer_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_prayer_per_day` (`user_id`,`prayer_name`,`prayer_date`);

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
-- AUTO_INCREMENT for table `nafl_prayers`
--
ALTER TABLE `nafl_prayers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `prayer_records`
--
ALTER TABLE `prayer_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `nafl_prayers`
--
ALTER TABLE `nafl_prayers`
  ADD CONSTRAINT `nafl_prayers_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `prayer_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prayer_records`
--
ALTER TABLE `prayer_records`
  ADD CONSTRAINT `prayer_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
