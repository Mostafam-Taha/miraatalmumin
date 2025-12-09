-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql207.infinityfree.com
-- Generation Time: Dec 06, 2025 at 08:29 PM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39304815_miraatalmuminif`
--

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `emoji` varchar(10) DEFAULT '?',
  `group_type` enum('public','private') NOT NULL DEFAULT 'private',
  `description` varchar(500) DEFAULT NULL,
  `join_code` varchar(100) NOT NULL,
  `join_link` varchar(500) NOT NULL,
  `custom_link` varchar(100) DEFAULT NULL,
  `allow_public_view` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `group_image` varchar(255) DEFAULT NULL,
  `settings_updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `group_name`, `emoji`, `group_type`, `description`, `join_code`, `join_link`, `custom_link`, `allow_public_view`, `created_by`, `created_at`, `group_image`, `settings_updated_at`) VALUES
(6, 'Server', '????', 'public', 'انضم إلا مجموعتي', 'group_6931883acab013.53037031', 'http://miraat-almumin.xo.je/groups/join_group.php?code=group_6931883acab013.53037031', NULL, 0, 5, '2025-12-04 13:10:17', NULL, NULL),
(7, 'صحبة الفردوس', '☕', 'public', '', 'group_6931a691bf0698.59581597', 'http://miraat-almumin.xo.je/groups/join_group.php?code=group_6931a691bf0698.59581597', NULL, 0, 2, '2025-12-04 15:19:45', NULL, NULL),
(8, 'صحبة الإسلام', '🎧', 'public', '', 'group_69320a96b03ca0.06114507', 'http://miraat-almumin.xo.je/groups/join_group.php?code=group_69320a96b03ca0.06114507', NULL, 0, 7, '2025-12-04 22:26:30', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','member') NOT NULL DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`id`, `group_id`, `user_id`, `role`, `joined_at`) VALUES
(9, 6, 5, 'admin', '2025-12-04 13:10:17'),
(10, 7, 2, 'admin', '2025-12-04 15:19:45'),
(11, 7, 5, 'member', '2025-12-04 16:22:22'),
(12, 7, 6, 'admin', '2025-12-04 16:25:56'),
(13, 8, 7, 'admin', '2025-12-04 22:26:30'),
(14, 8, 1, 'admin', '2025-12-05 01:49:07');

-- --------------------------------------------------------

--
-- Table structure for table `group_settings`
--

CREATE TABLE `group_settings` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL DEFAULT '0',
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(5, 1, 'العشاء', 'بعد', 2, '2025-12-03', '2025-12-03 16:22:05'),
(6, 2, 'الفجر', 'قبل', 2, '2025-12-03', '2025-12-03 16:44:11'),
(9, 2, 'المغرب', 'بعد', 2, '2025-12-03', '2025-12-03 17:03:22'),
(11, 2, 'الظهر', 'قبل', 4, '2025-12-03', '2025-12-03 17:03:28'),
(12, 2, 'الظهر', 'بعد', 2, '2025-12-03', '2025-12-03 17:03:28'),
(13, 1, 'الظهر', 'قبل', 4, '2026-01-25', '2025-12-03 19:18:58'),
(14, 1, 'الظهر', 'بعد', 2, '2026-01-25', '2025-12-03 19:18:58'),
(15, 2, 'العشاء', 'بعد', 2, '2025-12-03', '2025-12-03 20:02:41'),
(16, 1, 'العشاء', 'بعد', 2, '2026-01-25', '2025-12-03 20:18:48'),
(17, 1, 'الفجر', 'قبل', 2, '2025-12-10', '2025-12-03 20:20:34'),
(19, 1, 'الظهر', 'قبل', 4, '2025-12-10', '2025-12-03 20:29:17'),
(20, 1, 'الظهر', 'بعد', 2, '2025-12-10', '2025-12-03 20:29:17'),
(21, 1, 'المغرب', 'بعد', 2, '2025-12-03', '2025-12-03 22:44:21'),
(22, 2, 'المغرب', 'بعد', 2, '2025-11-29', '2025-12-03 23:16:37'),
(23, 2, 'العشاء', 'بعد', 2, '2025-11-29', '2025-12-03 23:16:40'),
(24, 2, 'الفجر', 'قبل', 2, '2025-11-30', '2025-12-03 23:16:45'),
(25, 2, 'الفجر', 'قبل', 2, '2025-12-04', '2025-12-04 15:22:02'),
(26, 2, 'الظهر', 'قبل', 4, '2025-12-04', '2025-12-04 15:22:05'),
(27, 2, 'الظهر', 'بعد', 2, '2025-12-04', '2025-12-04 15:22:05'),
(28, 2, 'المغرب', 'بعد', 2, '2025-12-04', '2025-12-04 15:22:10'),
(29, 2, 'العشاء', 'بعد', 2, '2025-12-04', '2025-12-04 15:22:13'),
(30, 2, 'الظهر', 'قبل', 4, '2025-11-21', '2025-12-04 15:23:55'),
(31, 2, 'الظهر', 'بعد', 2, '2025-11-21', '2025-12-04 15:23:55'),
(32, 2, 'العشاء', 'بعد', 2, '2025-11-21', '2025-12-04 15:24:01'),
(35, 2, 'الظهر', 'قبل', 4, '2025-11-01', '2025-12-04 15:56:58'),
(36, 2, 'الظهر', 'بعد', 2, '2025-11-01', '2025-12-04 15:56:58'),
(37, 2, 'المغرب', 'بعد', 2, '2025-11-01', '2025-12-04 15:57:07'),
(38, 2, 'العشاء', 'بعد', 2, '2025-11-01', '2025-12-04 15:57:11'),
(40, 2, 'المغرب', 'بعد', 2, '2025-11-04', '2025-12-04 15:58:43'),
(41, 2, 'العشاء', 'بعد', 2, '2025-11-04', '2025-12-04 15:58:51'),
(42, 2, 'الظهر', 'قبل', 4, '2025-11-04', '2025-12-04 15:58:55'),
(43, 2, 'الظهر', 'بعد', 2, '2025-11-04', '2025-12-04 15:58:55'),
(44, 2, 'الظهر', 'قبل', 4, '2025-11-05', '2025-12-04 15:59:09'),
(45, 2, 'الظهر', 'بعد', 2, '2025-11-05', '2025-12-04 15:59:09'),
(46, 2, 'المغرب', 'بعد', 2, '2025-11-05', '2025-12-04 15:59:16'),
(47, 2, 'العشاء', 'بعد', 2, '2025-11-05', '2025-12-04 15:59:23'),
(48, 2, 'الظهر', 'قبل', 4, '2025-11-06', '2025-12-04 16:03:21'),
(49, 2, 'الظهر', 'بعد', 2, '2025-11-06', '2025-12-04 16:03:21'),
(50, 2, 'المغرب', 'بعد', 2, '2025-11-06', '2025-12-04 16:03:25'),
(51, 2, 'العشاء', 'بعد', 2, '2025-11-06', '2025-12-04 16:03:38'),
(52, 7, 'الفجر', 'قبل', 2, '2025-12-04', '2025-12-04 22:25:07');

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
(7, 2, 'الفجر', '2025-12-03', 'prayed_in_mosque', '2025-12-03 16:44:11'),
(9, 2, 'الظهر', '2025-12-03', 'prayed_in_mosque', '2025-12-03 16:45:46'),
(10, 2, 'العصر', '2025-12-03', 'delayed', '2025-12-03 16:58:40'),
(11, 2, 'المغرب', '2025-12-03', 'prayed_alone', '2025-12-03 17:03:22'),
(12, 1, 'الظهر', '2026-01-25', 'prayed_in_mosque', '2025-12-03 19:18:58'),
(13, 2, 'العشاء', '2025-12-03', 'prayed_in_mosque', '2025-12-03 20:02:41'),
(14, 1, 'العشاء', '2026-01-25', 'delayed', '2025-12-03 20:18:48'),
(15, 1, 'الفجر', '2025-12-10', 'prayed_in_mosque', '2025-12-03 20:20:34'),
(17, 1, 'الظهر', '2025-12-10', 'prayed_in_mosque', '2025-12-03 20:29:17'),
(18, 1, 'العصر', '2025-12-10', 'prayed_in_mosque', '2025-12-03 20:30:35'),
(19, 1, 'الفجر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 22:43:44'),
(20, 1, 'الظهر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 22:43:47'),
(21, 1, 'العصر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 22:43:52'),
(22, 1, 'المغرب', '2025-12-04', 'prayed_in_mosque', '2025-12-03 22:43:54'),
(23, 1, 'العشاء', '2025-12-04', 'prayed_in_mosque', '2025-12-03 22:43:57'),
(24, 1, 'الفجر', '2025-12-05', 'prayed_in_mosque', '2025-12-03 22:45:12'),
(25, 1, 'الظهر', '2025-12-05', 'prayed_in_mosque', '2025-12-03 22:45:14'),
(26, 1, 'العصر', '2025-12-05', 'prayed_in_mosque', '2025-12-03 22:45:17'),
(27, 1, 'المغرب', '2025-12-05', 'prayed_in_mosque', '2025-12-03 22:45:19'),
(28, 1, 'العشاء', '2025-12-05', 'prayed_in_mosque', '2025-12-03 22:45:22'),
(29, 1, 'الفجر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 22:45:55'),
(30, 1, 'الظهر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 22:45:58'),
(31, 1, 'العصر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 22:46:01'),
(32, 1, 'المغرب', '2025-12-06', 'prayed_in_mosque', '2025-12-03 22:46:03'),
(33, 1, 'العشاء', '2025-12-06', 'prayed_in_mosque', '2025-12-03 22:46:05'),
(34, 1, 'الفجر', '2025-12-07', 'prayed_in_mosque', '2025-12-03 22:46:41'),
(35, 1, 'الظهر', '2025-12-07', 'prayed_in_mosque', '2025-12-03 22:46:43'),
(36, 1, 'العصر', '2025-12-07', 'prayed_in_mosque', '2025-12-03 22:46:46'),
(37, 1, 'المغرب', '2025-12-07', 'prayed_in_mosque', '2025-12-03 22:46:49'),
(38, 1, 'العشاء', '2025-12-07', 'prayed_in_mosque', '2025-12-03 22:46:51'),
(39, 2, 'الفجر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 22:50:42'),
(40, 2, 'الظهر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 22:50:45'),
(41, 2, 'المغرب', '2025-12-04', 'prayed_in_mosque', '2025-12-03 22:50:47'),
(42, 2, 'العصر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 22:50:49'),
(43, 2, 'العشاء', '2025-12-04', 'prayed_in_mosque', '2025-12-03 22:50:51'),
(44, 2, 'الفجر', '2025-12-05', 'prayed_in_mosque', '2025-12-03 22:50:55'),
(45, 2, 'الظهر', '2025-12-05', 'prayed_in_mosque', '2025-12-03 22:50:57'),
(46, 2, 'العصر', '2025-12-05', 'prayed_in_mosque', '2025-12-03 22:51:00'),
(47, 2, 'المغرب', '2025-12-05', 'prayed_in_mosque', '2025-12-03 22:51:02'),
(48, 2, 'العشاء', '2025-12-05', 'prayed_in_mosque', '2025-12-03 22:51:05'),
(49, 2, 'الفجر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 22:51:09'),
(50, 2, 'الظهر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 22:51:11'),
(51, 2, 'العصر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 22:51:14'),
(52, 2, 'المغرب', '2025-12-06', 'prayed_in_mosque', '2025-12-03 22:51:16'),
(53, 2, 'العشاء', '2025-12-06', 'prayed_in_mosque', '2025-12-03 22:51:19'),
(54, 2, 'الفجر', '2025-11-29', 'prayed_in_mosque', '2025-12-03 23:16:28'),
(55, 2, 'الظهر', '2025-11-29', 'prayed_in_mosque', '2025-12-03 23:16:30'),
(56, 2, 'العصر', '2025-11-29', 'prayed_in_mosque', '2025-12-03 23:16:33'),
(57, 2, 'المغرب', '2025-11-29', 'prayed_in_mosque', '2025-12-03 23:16:37'),
(58, 2, 'العشاء', '2025-11-29', 'prayed_in_mosque', '2025-12-03 23:16:40'),
(59, 2, 'الفجر', '2025-11-30', 'prayed_in_mosque', '2025-12-03 23:16:45'),
(60, 2, 'الظهر', '2025-11-30', 'prayed_in_mosque', '2025-12-03 23:16:47'),
(61, 2, 'العصر', '2025-11-30', 'prayed_in_mosque', '2025-12-03 23:16:49'),
(62, 2, 'المغرب', '2025-11-30', 'prayed_in_mosque', '2025-12-03 23:16:52'),
(63, 2, 'العشاء', '2025-11-30', 'prayed_in_mosque', '2025-12-03 23:16:57'),
(64, 2, 'الفجر', '2025-12-01', 'prayed_in_mosque', '2025-12-03 23:17:02'),
(65, 2, 'الظهر', '2025-12-01', 'prayed_in_mosque', '2025-12-03 23:17:05'),
(66, 2, 'العصر', '2025-12-01', 'prayed_in_mosque', '2025-12-03 23:17:08'),
(67, 1, 'الفجر', '2025-11-27', 'prayed_in_mosque', '2025-12-04 00:12:20'),
(68, 1, 'الظهر', '2025-11-27', 'prayed_in_mosque', '2025-12-04 00:12:22'),
(69, 1, 'العصر', '2025-11-27', 'prayed_in_mosque', '2025-12-04 00:12:25'),
(70, 1, 'المغرب', '2025-11-27', 'prayed_in_mosque', '2025-12-04 00:12:28'),
(71, 1, 'العشاء', '2025-11-27', 'prayed_in_mosque', '2025-12-04 00:12:31'),
(72, 1, 'الفجر', '2025-11-28', 'prayed_in_mosque', '2025-12-04 00:12:37'),
(73, 1, 'الظهر', '2025-11-28', 'prayed_in_mosque', '2025-12-04 00:12:39'),
(74, 1, 'العصر', '2025-11-28', 'prayed_in_mosque', '2025-12-04 00:12:43'),
(75, 1, 'المغرب', '2025-11-28', 'prayed_in_mosque', '2025-12-04 00:12:45'),
(76, 1, 'العشاء', '2025-11-28', 'prayed_in_mosque', '2025-12-04 00:12:48'),
(77, 1, 'الفجر', '2025-11-29', 'prayed_in_mosque', '2025-12-04 00:12:52'),
(78, 1, 'الظهر', '2025-11-29', 'prayed_in_mosque', '2025-12-04 00:12:54'),
(79, 1, 'العصر', '2025-11-29', 'prayed_in_mosque', '2025-12-04 00:12:57'),
(80, 1, 'المغرب', '2025-11-29', 'prayed_in_mosque', '2025-12-04 00:12:59'),
(81, 1, 'العشاء', '2025-11-29', 'prayed_in_mosque', '2025-12-04 00:13:02'),
(82, 1, 'الفجر', '2025-12-01', 'prayed_in_mosque', '2025-12-04 00:15:00'),
(83, 1, 'الظهر', '2025-12-01', 'prayed_in_mosque', '2025-12-04 00:15:03'),
(84, 1, 'العصر', '2025-12-01', 'prayed_in_mosque', '2025-12-04 00:15:06'),
(85, 1, 'المغرب', '2025-12-01', 'prayed_in_mosque', '2025-12-04 00:15:08'),
(86, 1, 'العشاء', '2025-12-01', 'prayed_in_mosque', '2025-12-04 00:15:11'),
(87, 1, 'الفجر', '2025-12-02', 'prayed_in_mosque', '2025-12-04 00:15:16'),
(88, 1, 'الظهر', '2025-12-02', 'prayed_in_mosque', '2025-12-04 00:15:18'),
(89, 1, 'الفجر', '2025-12-08', 'prayed_in_mosque', '2025-12-04 00:15:32'),
(90, 1, 'الظهر', '2025-12-08', 'prayed_in_mosque', '2025-12-04 00:15:34'),
(91, 1, 'العصر', '2025-12-08', 'prayed_in_mosque', '2025-12-04 00:15:36'),
(92, 1, 'المغرب', '2025-12-08', 'prayed_in_mosque', '2025-12-04 00:15:39'),
(93, 1, 'العشاء', '2025-12-08', 'prayed_in_mosque', '2025-12-04 00:15:41'),
(94, 1, 'الفجر', '2025-12-09', 'prayed_in_mosque', '2025-12-04 00:15:45'),
(95, 1, 'الظهر', '2025-12-09', 'prayed_in_mosque', '2025-12-04 00:15:47'),
(96, 1, 'المغرب', '2025-12-09', 'prayed_in_mosque', '2025-12-04 00:15:52'),
(97, 1, 'العشاء', '2025-12-09', 'prayed_in_mosque', '2025-12-04 00:15:54'),
(98, 2, 'الفجر', '2025-12-08', 'prayed_alone', '2025-12-04 13:23:53'),
(99, 2, 'الفجر', '2025-11-28', 'prayed_in_mosque', '2025-12-04 15:23:45'),
(100, 2, 'الفجر', '2025-11-21', 'prayed_in_mosque', '2025-12-04 15:23:50'),
(101, 2, 'المغرب', '2025-11-21', 'prayed_in_mosque', '2025-12-04 15:23:52'),
(102, 2, 'الظهر', '2025-11-21', 'prayed_in_mosque', '2025-12-04 15:23:55'),
(103, 2, 'العصر', '2025-11-21', 'prayed_in_mosque', '2025-12-04 15:23:58'),
(104, 2, 'العشاء', '2025-11-21', 'prayed_in_mosque', '2025-12-04 15:24:01'),
(105, 2, 'الفجر', '2025-11-01', 'prayed_alone', '2025-12-04 15:56:53'),
(106, 2, 'الظهر', '2025-11-01', 'prayed_alone', '2025-12-04 15:56:57'),
(107, 2, 'العصر', '2025-11-01', 'prayed_alone', '2025-12-04 15:57:03'),
(108, 2, 'المغرب', '2025-11-01', 'prayed_alone', '2025-12-04 15:57:07'),
(109, 2, 'العشاء', '2025-11-01', 'prayed_alone', '2025-12-04 15:57:11'),
(110, 2, 'الفجر', '2025-11-02', 'not_prayed', '2025-12-04 15:57:20'),
(111, 2, 'الظهر', '2025-11-02', 'prayed_alone', '2025-12-04 15:57:33'),
(112, 2, 'العصر', '2025-11-02', 'prayed_alone', '2025-12-04 15:57:36'),
(113, 2, 'المغرب', '2025-11-02', 'prayed_alone', '2025-12-04 15:57:43'),
(114, 2, 'العشاء', '2025-11-02', 'prayed_alone', '2025-12-04 15:57:47'),
(115, 2, 'الفجر', '2025-11-03', 'not_prayed', '2025-12-04 15:57:55'),
(116, 2, 'الظهر', '2025-11-03', 'prayed_alone', '2025-12-04 15:58:07'),
(117, 2, 'العصر', '2025-11-03', 'prayed_alone', '2025-12-04 15:58:11'),
(118, 2, 'المغرب', '2025-11-03', 'prayed_alone', '2025-12-04 15:58:16'),
(119, 2, 'العشاء', '2025-11-03', 'prayed_alone', '2025-12-04 15:58:23'),
(120, 2, 'الفجر', '2025-11-04', 'not_prayed', '2025-12-04 15:58:32'),
(121, 2, 'الظهر', '2025-11-04', 'prayed_in_mosque', '2025-12-04 15:58:35'),
(122, 2, 'العصر', '2025-11-04', 'prayed_alone', '2025-12-04 15:58:38'),
(123, 2, 'المغرب', '2025-11-04', 'prayed_alone', '2025-12-04 15:58:43'),
(124, 2, 'العشاء', '2025-11-04', 'prayed_in_mosque', '2025-12-04 15:58:51'),
(125, 2, 'الفجر', '2025-11-05', 'not_prayed', '2025-12-04 15:59:05'),
(126, 2, 'الظهر', '2025-11-05', 'prayed_in_mosque', '2025-12-04 15:59:09'),
(127, 2, 'العصر', '2025-11-05', 'prayed_in_mosque', '2025-12-04 15:59:12'),
(128, 2, 'المغرب', '2025-11-05', 'prayed_in_mosque', '2025-12-04 15:59:16'),
(129, 2, 'العشاء', '2025-11-05', 'prayed_in_mosque', '2025-12-04 15:59:23'),
(130, 2, 'الفجر', '2025-11-06', 'delayed', '2025-12-04 16:03:17'),
(131, 2, 'الظهر', '2025-11-06', 'delayed', '2025-12-04 16:03:21'),
(132, 2, 'المغرب', '2025-11-06', 'delayed', '2025-12-04 16:03:25'),
(133, 2, 'العصر', '2025-11-06', 'prayed_in_mosque', '2025-12-04 16:03:30'),
(134, 2, 'العشاء', '2025-11-06', 'delayed', '2025-12-04 16:03:38'),
(135, 7, 'الفجر', '2025-12-04', 'prayed_in_mosque', '2025-12-04 22:25:07');

-- --------------------------------------------------------

--
-- Table structure for table `setting_categories`
--

CREATE TABLE `setting_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(2, '107567217322334047953', 'mostafamtaha66@gmail.com', 'Mostafa Taha', 'https://lh3.googleusercontent.com/a/ACg8ocJs4KnLtjalY248GvqkYyn1Sorr5alNMz8ZsXLJL6a_wpqbVw=s96-c', '2025-12-03 15:13:57'),
(3, '117297619382063078383', 'moaazmohamed1jj@gmail.com', 'MoaazMohamed', 'https://lh3.googleusercontent.com/a/ACg8ocL6JjDNmJ-Djl6U6ZEUzA2VWVEFL77MRa8t78xkcZihcqKb7A=s96-c', '2025-12-03 17:36:50'),
(5, '114236082106807795275', 'mostafamohammedtaha3@gmail.com', 'Mostafa Mohammed', 'https://lh3.googleusercontent.com/a/ACg8ocLoDAAp1pQD661T1_-mQjXJaWFGxZu-6u1Ok73Zm17ITI7CAQ=s96-c', '2025-12-04 03:30:36'),
(6, '112770162303863287119', 'mosafamtaha@gmail.com', 'Mostafam taha', 'https://lh3.googleusercontent.com/a/ACg8ocLsEwx5toYRnjKOgTcoE2kN6qGfo23jH2Bm9pn3-Aut1WCZB_g=s96-c', '2025-12-04 16:25:32'),
(7, '103987897004814714957', 'poijlsjsohddiir@gmail.com', 'abdo mostafa', 'https://lh3.googleusercontent.com/a/ACg8ocLHWjmBhQtVYB6w9KQ_2Ty1AU6beGU41twLa2vCxYr0cZ93Ngku=s96-c', '2025-12-04 22:24:41'),
(8, '107681726258782612532', 'sahatalllm@gmail.com', 'Sahat Al_llm', 'https://lh3.googleusercontent.com/a/ACg8ocLua0dLyyBRKQ3lCqXBjm52mxRrPBZ1kniBT1WPVfq_Tn4LsQ=s96-c', '2025-12-05 20:53:33'),
(9, '100673552528024131889', 'fthyahmdmhmdahmd532@gmail.com', 'أحمد فتحي أحمد محمد', 'https://lh3.googleusercontent.com/a/ACg8ocJa94k9fCPfFhU1aeO-bK8WrgVAQozKhjyXANX7ZTzk-AnitA=s96-c', '2025-12-05 22:07:10'),
(10, '109117710110731879594', 'moaazgomaa121@gmail.com', 'Moaaz Gomaaa', 'https://lh3.googleusercontent.com/a/ACg8ocIby0aKBgoD_XLvAV4ELX5b6iisY-dHGXk8DkokvhTYGQPz=s96-c', '2025-12-05 22:20:37'),
(11, '113981024221948904220', 'poooga208@gmail.com', 'MO dy', 'https://lh3.googleusercontent.com/a/ACg8ocLOVMYf3j1hotljfCtvAeb4Ovg9hPQkIZUKTMHX89G3qpKKIknK=s96-c', '2025-12-05 22:49:12'),
(12, '113124499439641799549', 'nadymahmoudnady0@gmail.com', 'nady mahmoudnady', 'https://lh3.googleusercontent.com/a/ACg8ocLD6p9V4XMoidvNW8giZznVkm2oCdKCJPnvq2-A5chHQGqnuw=s96-c', '2025-12-05 23:58:55'),
(13, '110975382209790669686', 'mohamedmoneim515@gmail.com', 'Mohamed Moneim', 'https://lh3.googleusercontent.com/a/ACg8ocK9gjnk5J5lNa64JRac7u4ptD8dpSVkBJTZMFrauCTKXo8H0tJA=s96-c', '2025-12-06 04:13:31'),
(14, '106357297211131090239', '2adel2tarek2@gmail.com', 'Adel Tarek', 'https://lh3.googleusercontent.com/a/ACg8ocLeuG_sSqg_8IhNnfLWZ5iNuuRlZGGhrfWC6AWyWVsi_I-HBQ=s96-c', '2025-12-06 10:33:11'),
(15, '110574543715303343484', 'sayedqwerty9@gmail.com', 'Sayed Mahmoud', 'https://lh3.googleusercontent.com/a/ACg8ocI5Dz_8J0XdXMbu-6rgbU9NXtzZAu_ZBb_ENmfJdFUmILsPvw=s96-c', '2025-12-06 11:23:36'),
(16, '105037158687994132873', 'darshgamer673@gmail.com', 'darsh gamer 3', 'https://lh3.googleusercontent.com/a/ACg8ocIbmPTdE7Quh9Ak3rp2XxqGEgOttCap66BfMVXqwNLS3znEwQ=s96-c', '2025-12-06 12:36:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `join_code` (`join_code`),
  ADD UNIQUE KEY `custom_link` (`custom_link`),
  ADD KEY `fk_groups_created_by` (`created_by`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_membership` (`group_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `group_settings`
--
ALTER TABLE `group_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_setting` (`group_id`,`setting_key`),
  ADD KEY `category_index` (`category_id`);

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
-- Indexes for table `setting_categories`
--
ALTER TABLE `setting_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`name`);

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
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `group_settings`
--
ALTER TABLE `group_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nawafil_records`
--
ALTER TABLE `nawafil_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `prayer_records`
--
ALTER TABLE `prayer_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `setting_categories`
--
ALTER TABLE `setting_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `fk_groups_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_settings`
--
ALTER TABLE `group_settings`
  ADD CONSTRAINT `group_settings_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

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
