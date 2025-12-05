-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 09:03 PM
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
-- Table structure for table `nawafil_records`
--

CREATE TABLE `nawafil_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prayer_name` varchar(20) NOT NULL,
  `nawafil_type` varchar(20) NOT NULL COMMENT 'before, after',
  `rakat_count` int(11) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nawafil_records`
--

INSERT INTO `nawafil_records` (`id`, `user_id`, `prayer_name`, `nawafil_type`, `rakat_count`, `date`, `created_at`) VALUES
(1, 1, 'الظهر', 'بعد', 2, '2025-12-03', '2025-12-03 16:04:25'),
(2, 1, 'الظهر', 'قبل', 4, '2025-12-03', '2025-12-03 16:04:25'),
(3, 1, 'الفجر', 'قبل', 2, '2025-12-03', '2025-12-03 16:04:32'),
(4, 1, 'المغرب', 'بعد', 2, '2025-12-03', '2025-12-03 16:05:16'),
(5, 1, 'العشاء', 'بعد', 2, '2025-12-03', '2025-12-03 16:22:05'),
(6, 1, 'الظهر', 'قبل', 4, '2025-12-04', '2025-12-03 17:40:02'),
(7, 1, 'الظهر', 'بعد', 2, '2025-12-04', '2025-12-03 17:40:02'),
(8, 1, 'الفجر', 'قبل', 2, '2025-12-04', '2025-12-03 17:40:08'),
(9, 1, 'المغرب', 'بعد', 2, '2025-12-04', '2025-12-03 17:40:12'),
(10, 1, 'العشاء', 'بعد', 2, '2025-12-04', '2025-12-03 17:40:23'),
(11, 1, 'الفجر', 'قبل', 2, '2025-12-05', '2025-12-03 17:40:30'),
(12, 1, 'المغرب', 'بعد', 2, '2025-12-05', '2025-12-03 17:40:41'),
(13, 1, 'العشاء', 'بعد', 2, '2025-12-05', '2025-12-03 17:40:46'),
(14, 1, 'الظهر', 'بعد', 2, '2025-12-06', '2025-12-03 17:46:57'),
(15, 1, 'الظهر', 'قبل', 4, '2025-12-06', '2025-12-03 17:46:57'),
(16, 1, 'الفجر', 'قبل', 2, '2025-12-06', '2025-12-03 17:47:01');

-- --------------------------------------------------------

--
-- Table structure for table `prayer_records`
--

CREATE TABLE `prayer_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prayer_name` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `status` varchar(20) NOT NULL COMMENT 'prayed_in_mosque, prayed_alone, not_prayed, delayed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prayer_records`
--

INSERT INTO `prayer_records` (`id`, `user_id`, `prayer_name`, `date`, `status`, `created_at`) VALUES
(1, 1, 'الظهر', '2025-12-03', 'not_prayed', '2025-12-03 16:04:25'),
(2, 1, 'الفجر', '2025-12-03', 'prayed_in_mosque', '2025-12-03 16:04:32'),
(3, 1, 'المغرب', '2025-12-03', 'prayed_in_mosque', '2025-12-03 16:05:16'),
(4, 1, 'المغرب', '2025-12-03', 'delayed', '2025-12-03 16:06:09'),
(5, 1, 'العصر', '2025-12-03', 'prayed_alone', '2025-12-03 16:06:14'),
(6, 1, 'العشاء', '2025-12-03', 'prayed_in_mosque', '2025-12-03 16:22:05'),
(7, 1, 'الظهر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 17:40:02'),
(8, 1, 'الفجر', '2025-12-04', 'prayed_alone', '2025-12-03 17:40:08'),
(9, 1, 'المغرب', '2025-12-04', 'prayed_in_mosque', '2025-12-03 17:40:12'),
(10, 1, 'العصر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 17:40:18'),
(11, 1, 'العشاء', '2025-12-04', 'prayed_in_mosque', '2025-12-03 17:40:23'),
(12, 1, 'الفجر', '2025-12-05', 'prayed_alone', '2025-12-03 17:40:30'),
(13, 1, 'العصر', '2025-12-05', 'prayed_in_mosque', '2025-12-03 17:40:34'),
(14, 1, 'الظهر', '2025-12-05', 'prayed_in_mosque', '2025-12-03 17:40:36'),
(15, 1, 'المغرب', '2025-12-05', 'prayed_in_mosque', '2025-12-03 17:40:41'),
(16, 1, 'العشاء', '2025-12-05', 'prayed_in_mosque', '2025-12-03 17:40:46'),
(17, 1, 'الظهر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 17:46:57'),
(18, 1, 'الفجر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 17:47:01'),
(19, 1, 'العصر', '2025-12-06', 'delayed', '2025-12-03 17:47:04'),
(20, 1, 'العشاء', '2025-12-06', 'prayed_in_mosque', '2025-12-03 17:47:08'),
(21, 1, 'الفجر', '2025-12-08', 'delayed', '2025-12-03 17:47:13'),
(22, 1, 'الظهر', '2025-12-08', 'not_prayed', '2025-12-03 17:47:16'),
(23, 1, 'العصر', '2025-12-08', 'not_prayed', '2025-12-03 17:47:22'),
(24, 1, 'المغرب', '2025-12-08', 'not_prayed', '2025-12-03 17:47:26'),
(25, 1, 'العشاء', '2025-12-08', 'not_prayed', '2025-12-03 17:47:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `google_id` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `google_id`, `email`, `name`, `profile_picture`, `created_at`) VALUES
(1, '118380663984086018501', 'mostafamta347@gmail.com', 'Mostafam Ta3', 'https://lh3.googleusercontent.com/a/ACg8ocKoPaBU55MVAUPWK8vAXRZL5l2zajBxEXOg3H-IujH4wFfVIA=s96-c', '2025-12-03 13:59:53'),
(2, '107567217322334047953', 'mostafamtaha66@gmail.com', 'Mostafa Taha', 'https://lh3.googleusercontent.com/a/ACg8ocJs4KnLtjalY248GvqkYyn1Sorr5alNMz8ZsXLJL6a_wpqbVw=s96-c', '2025-12-03 15:13:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `nawafil_records`
--
ALTER TABLE `nawafil_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `prayer_records`
--
ALTER TABLE `prayer_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `nawafil_records`
--
ALTER TABLE `nawafil_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `prayer_records`
--
ALTER TABLE `prayer_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `nawafil_records`
--
ALTER TABLE `nawafil_records`
  ADD CONSTRAINT `nawafil_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prayer_records`
--
ALTER TABLE `prayer_records`
  ADD CONSTRAINT `prayer_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
