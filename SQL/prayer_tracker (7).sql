-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 06, 2025 at 05:01 AM
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
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `emoji` varchar(10) DEFAULT '?',
  `group_type` enum('public','private') NOT NULL DEFAULT 'private',
  `description` varchar(500) DEFAULT NULL,
  `join_code` varchar(100) NOT NULL,
  `join_link` varchar(500) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `group_image` varchar(255) DEFAULT NULL,
  `hide_group_name` tinyint(1) DEFAULT 0,
  `hide_group_image` tinyint(1) DEFAULT 0,
  `allow_public_members_view` tinyint(1) DEFAULT 0,
  `custom_join_link` varchar(100) DEFAULT NULL,
  `settings_updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `user_id`, `group_name`, `emoji`, `group_type`, `description`, `join_code`, `join_link`, `created_by`, `created_at`, `group_image`, `hide_group_name`, `hide_group_image`, `allow_public_members_view`, `custom_join_link`, `settings_updated_at`) VALUES
(1, 0, 'askldfas', '❤', 'public', 'asdfasd', 'group_6930af16918f77.88379552', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930af16918f77.88379552', 1, '2025-12-03 21:43:50', NULL, 0, 0, 0, NULL, NULL),
(2, 0, 'asdfasdasdfasdf', 'dd', 'private', 'dd', 'group_6930afef88b4d6.31395784', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930afef88b4d6.31395784', 2, '2025-12-03 21:47:27', NULL, 0, 0, 0, NULL, NULL),
(5, 0, 'مجموعة طرش', '????', 'public', 'شسيب', 'group_6930c2974ac1c1.74847837', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c2974ac1c1.74847837', 1, '2025-12-03 23:07:03', NULL, 0, 0, 0, NULL, NULL),
(6, 0, 'مجموعة طرشasdfsdf', '????', 'private', 'asdfsdf', 'group_6930c5615b6005.00146987', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5615b6005.00146987', 1, '2025-12-03 23:18:57', NULL, 0, 0, 0, NULL, NULL),
(7, 0, 'مجموعة طرشasdfsdf', '????', 'public', 'asdfasdf', 'group_6930c5d022bda8.71250908', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5d022bda8.71250908', 1, '2025-12-03 23:20:48', NULL, 0, 0, 0, NULL, NULL),
(8, 0, 'adfsadf', '????', 'public', 'asdfasf', 'group_6930c5de6330c4.13104305', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5de6330c4.13104305', 1, '2025-12-03 23:21:02', NULL, 0, 0, 0, NULL, NULL),
(9, 0, 'sdaf', '????', 'public', 'asdfasdfasdf', 'group_6930c5ec247804.36706612', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5ec247804.36706612', 1, '2025-12-03 23:21:16', NULL, 0, 0, 0, NULL, NULL),
(10, 0, 'asdfasdf', '????', 'public', 'sadfasdf', 'group_6930c5f3361a98.78284016', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5f3361a98.78284016', 1, '2025-12-03 23:21:23', NULL, 0, 0, 0, NULL, NULL),
(11, 0, 'صحبة', '⚽', 'public', 'شكل تجريبي', 'group_6930c5fb6aab62.66089247', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5fb6aab62.66089247', 1, '2025-12-03 23:21:31', 'group_11_6933788df3ee99.58258418.webp', 1, 0, 1, 'mostafamtaha', '2025-12-06 03:58:24'),
(13, 0, 'dasf', '????', 'public', 'asdfasdf', 'group_6930c6628da175.29882562', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c6628da175.29882562', 2, '2025-12-03 23:23:14', NULL, 0, 0, 0, NULL, NULL),
(17, 0, 'plays', '????', 'public', 'مجموعة', 'group_693187246b9a21.61960455', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_693187246b9a21.61960455', 3, '2025-12-04 13:05:40', NULL, 0, 0, 0, NULL, NULL),
(19, 0, 'd', '????', 'public', 'asdf', 'group_69332f479bc8b7.59810630', 'https://localhost/Miraat Al_Mumin/groups/group_preview.php?code=group_69332f479bc8b7.59810630', 3, '2025-12-05 19:15:19', NULL, 0, 0, 0, NULL, NULL),
(20, 0, 'saf', '????', 'public', '0', 'group_69336c5bb4b917.95400092', 'https://localhost/Miraat Al_Mumin/groups/group_preview.php?code=group_69336c5bb4b917.95400092', 1, '2025-12-05 23:35:55', NULL, 0, 0, 0, NULL, NULL);

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
(2, 1, 2, 'admin', '2025-12-03 21:45:27'),
(3, 2, 2, 'admin', '2025-12-03 21:47:27'),
(10, 5, 1, 'admin', '2025-12-03 23:07:03'),
(11, 6, 1, 'admin', '2025-12-03 23:18:57'),
(13, 7, 1, 'admin', '2025-12-03 23:20:48'),
(14, 8, 1, 'admin', '2025-12-03 23:21:02'),
(15, 9, 1, 'admin', '2025-12-03 23:21:16'),
(16, 10, 1, 'admin', '2025-12-03 23:21:23'),
(17, 11, 1, 'admin', '2025-12-03 23:21:31'),
(19, 13, 2, 'admin', '2025-12-03 23:23:14'),
(24, 17, 3, 'admin', '2025-12-04 13:05:40'),
(31, 7, 3, 'member', '2025-12-05 19:14:33'),
(32, 19, 3, 'admin', '2025-12-05 19:15:20'),
(33, 20, 1, 'admin', '2025-12-05 23:35:57');

-- --------------------------------------------------------

--
-- Table structure for table `group_settings`
--

CREATE TABLE `group_settings` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `hide_group_name` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=غير مفعل, 1=مفعل',
  `hide_group_image` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=غير مفعل, 1=مفعل',
  `allow_public_view_members` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=غير مفعل, 1=مفعل',
  `custom_join_link` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nawafil_records`
--

CREATE TABLE `nawafil_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prayer_record_id` int(11) DEFAULT NULL,
  `prayer_name` varchar(20) NOT NULL,
  `nawafil_type` varchar(20) NOT NULL COMMENT 'before, after',
  `rakat_count` int(11) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nawafil_records`
--

INSERT INTO `nawafil_records` (`id`, `user_id`, `prayer_record_id`, `prayer_name`, `nawafil_type`, `rakat_count`, `date`, `created_at`) VALUES
(1, 1, NULL, 'الظهر', 'بعد', 2, '2025-12-03', '2025-12-03 16:04:25'),
(2, 1, NULL, 'الظهر', 'قبل', 4, '2025-12-03', '2025-12-03 16:04:25'),
(3, 1, NULL, 'الفجر', 'قبل', 2, '2025-12-03', '2025-12-03 16:04:32'),
(4, 1, NULL, 'المغرب', 'بعد', 2, '2025-12-03', '2025-12-03 16:05:16'),
(5, 1, NULL, 'العشاء', 'بعد', 2, '2025-12-03', '2025-12-03 16:22:05'),
(10, 1, NULL, 'العشاء', 'بعد', 2, '2025-12-04', '2025-12-03 17:40:23'),
(12, 1, NULL, 'المغرب', 'بعد', 2, '2025-12-05', '2025-12-03 17:40:41'),
(13, 1, NULL, 'العشاء', 'بعد', 2, '2025-12-05', '2025-12-03 17:40:46'),
(14, 1, NULL, 'الظهر', 'بعد', 2, '2025-12-06', '2025-12-03 17:46:57'),
(15, 1, NULL, 'الظهر', 'قبل', 4, '2025-12-06', '2025-12-03 17:46:57'),
(16, 1, NULL, 'الفجر', 'قبل', 2, '2025-12-06', '2025-12-03 17:47:01'),
(17, 1, NULL, 'الظهر', 'قبل', 4, '2025-12-09', '2025-12-03 20:16:03'),
(18, 1, NULL, 'الظهر', 'بعد', 2, '2025-12-09', '2025-12-03 20:16:03'),
(19, 1, NULL, 'الفجر', 'قبل', 2, '2025-12-10', '2025-12-03 20:16:48'),
(20, 1, NULL, 'المغرب', 'بعد', 2, '2025-12-10', '2025-12-03 20:34:11'),
(21, 1, NULL, 'الفجر', 'قبل', 2, '2025-12-02', '2025-12-04 00:05:12'),
(22, 1, NULL, 'الظهر', 'قبل', 4, '2025-12-02', '2025-12-04 00:05:16'),
(23, 1, NULL, 'المغرب', 'بعد', 2, '2025-12-02', '2025-12-04 00:05:24'),
(24, 1, NULL, 'العشاء', 'بعد', 2, '2025-12-02', '2025-12-04 00:05:28'),
(25, 1, NULL, 'الفجر', 'قبل', 2, '2025-12-05', '2025-12-04 01:07:27'),
(29, 1, NULL, 'الفجر', 'قبل', 2, '2025-12-04', '2025-12-04 03:35:51'),
(31, 1, NULL, 'العشاء', 'بعد', 2, '2026-02-05', '2025-12-04 12:00:43'),
(32, 1, NULL, 'المغرب', 'بعد', 2, '2025-12-04', '2025-12-04 12:14:29'),
(33, 3, NULL, 'المغرب', 'بعد', 2, '2025-12-04', '2025-12-04 14:24:36'),
(34, 3, NULL, 'العشاء', 'بعد', 2, '2025-12-04', '2025-12-04 14:24:41'),
(35, 1, NULL, 'الظهر', 'قبل', 4, '2025-12-05', '2025-12-05 02:14:30'),
(36, 1, NULL, 'الظهر', 'بعد', 2, '2025-12-05', '2025-12-05 02:14:30'),
(37, 1, NULL, 'الظهر', 'قبل', 4, '2025-12-04', '2025-12-05 02:25:20'),
(38, 1, NULL, 'الظهر', 'بعد', 2, '2025-12-04', '2025-12-05 02:25:20'),
(39, 1, NULL, 'الفجر', 'قبل', 2, '2025-11-28', '2025-12-05 02:25:44'),
(40, 1, 68, 'الظهر', 'قبل', 4, '2025-11-28', '2025-12-05 02:28:47'),
(41, 1, 68, 'الظهر', 'بعد', 2, '2025-11-28', '2025-12-05 02:28:47'),
(42, 1, 12, 'الفجر', 'قبل', 2, '2025-12-05', '2025-12-05 02:33:25'),
(44, 1, 14, 'الظهر', 'بعد', 2, '2025-12-05', '2025-12-05 02:33:45'),
(45, 3, 69, 'الفجر', 'قبل', 2, '2025-12-05', '2025-12-05 19:02:11'),
(46, 3, 70, 'الظهر', 'قبل', 4, '2025-12-05', '2025-12-05 19:02:16'),
(47, 3, 70, 'الظهر', 'بعد', 2, '2025-12-05', '2025-12-05 19:02:16'),
(48, 3, 72, 'المغرب', 'بعد', 2, '2025-12-05', '2025-12-05 19:02:26'),
(49, 3, 73, 'العشاء', 'بعد', 2, '2025-12-05', '2025-12-05 19:02:29'),
(50, 1, 15, 'المغرب', 'بعد', 2, '2025-12-05', '2025-12-05 21:09:53');

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
(5, 1, 'العصر', '2025-12-03', 'prayed_in_mosque', '2025-12-03 16:06:14'),
(6, 1, 'العشاء', '2025-12-03', 'prayed_in_mosque', '2025-12-03 16:22:05'),
(7, 1, 'الظهر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 17:40:02'),
(8, 1, 'الفجر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 17:40:08'),
(9, 1, 'المغرب', '2025-12-04', 'prayed_alone', '2025-12-03 17:40:12'),
(10, 1, 'العصر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 17:40:18'),
(11, 1, 'العشاء', '2025-12-04', 'prayed_in_mosque', '2025-12-03 17:40:23'),
(12, 1, 'الفجر', '2025-12-05', 'prayed_in_mosque', '2025-12-03 17:40:30'),
(13, 1, 'العصر', '2025-12-05', 'prayed_alone', '2025-12-03 17:40:34'),
(14, 1, 'الظهر', '2025-12-05', 'prayed_in_mosque', '2025-12-03 17:40:36'),
(15, 1, 'المغرب', '2025-12-05', 'prayed_in_mosque', '2025-12-03 17:40:41'),
(16, 1, 'العشاء', '2025-12-05', 'prayed_in_mosque', '2025-12-03 17:40:46'),
(17, 1, 'الظهر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 17:46:57'),
(18, 1, 'الفجر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 17:47:01'),
(19, 1, 'العصر', '2025-12-06', 'prayed_in_mosque', '2025-12-03 17:47:04'),
(20, 1, 'العشاء', '2025-12-06', 'prayed_in_mosque', '2025-12-03 17:47:08'),
(21, 1, 'الفجر', '2025-12-08', 'delayed', '2025-12-03 17:47:13'),
(22, 1, 'الظهر', '2025-12-08', 'not_prayed', '2025-12-03 17:47:16'),
(23, 1, 'العصر', '2025-12-08', 'not_prayed', '2025-12-03 17:47:22'),
(24, 1, 'المغرب', '2025-12-08', 'not_prayed', '2025-12-03 17:47:26'),
(25, 1, 'العشاء', '2025-12-08', 'not_prayed', '2025-12-03 17:47:29'),
(26, 1, 'الفجر', '2025-12-07', 'prayed_in_mosque', '2025-12-03 20:08:57'),
(27, 1, 'الظهر', '2025-12-07', 'prayed_in_mosque', '2025-12-03 20:09:01'),
(28, 1, 'الظهر', '2025-12-09', 'prayed_in_mosque', '2025-12-03 20:16:03'),
(29, 1, 'المغرب', '2025-12-09', 'prayed_in_mosque', '2025-12-03 20:16:09'),
(30, 1, 'الظهر', '2025-12-10', 'prayed_in_mosque', '2025-12-03 20:16:32'),
(31, 1, 'الفجر', '2025-12-10', 'prayed_in_mosque', '2025-12-03 20:16:48'),
(32, 1, 'المغرب', '2025-12-10', 'prayed_alone', '2025-12-03 20:34:11'),
(33, 1, 'العصر', '2025-12-10', 'not_prayed', '2025-12-03 20:49:19'),
(34, 1, 'الفجر', '2025-12-17', 'not_prayed', '2025-12-03 20:49:39'),
(35, 1, 'العصر', '2025-12-09', 'prayed_alone', '2025-12-03 20:50:54'),
(36, 1, 'الفجر', '2025-12-16', 'not_prayed', '2025-12-03 21:09:02'),
(37, 2, 'الفجر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 23:22:18'),
(38, 2, 'الظهر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 23:22:21'),
(39, 2, 'العصر', '2025-12-04', 'prayed_in_mosque', '2025-12-03 23:22:23'),
(40, 2, 'المغرب', '2025-12-04', 'prayed_in_mosque', '2025-12-03 23:22:33'),
(41, 2, 'العشاء', '2025-12-04', 'prayed_in_mosque', '2025-12-03 23:22:37'),
(42, 1, 'الفجر', '2025-12-02', 'prayed_in_mosque', '2025-12-04 00:05:11'),
(43, 1, 'الظهر', '2025-12-02', 'prayed_in_mosque', '2025-12-04 00:05:16'),
(44, 1, 'العصر', '2025-12-02', 'prayed_in_mosque', '2025-12-04 00:05:19'),
(45, 1, 'المغرب', '2025-12-02', 'prayed_in_mosque', '2025-12-04 00:05:24'),
(46, 1, 'العشاء', '2025-12-02', 'prayed_in_mosque', '2025-12-04 00:05:27'),
(47, 1, 'المغرب', '2025-12-06', 'prayed_in_mosque', '2025-12-04 01:07:37'),
(48, 1, 'العصر', '2025-12-07', 'prayed_in_mosque', '2025-12-04 01:07:42'),
(49, 1, 'المغرب', '2025-12-07', 'prayed_in_mosque', '2025-12-04 01:07:45'),
(50, 1, 'العشاء', '2025-12-07', 'prayed_in_mosque', '2025-12-04 01:07:47'),
(51, 1, 'الظهر', '2026-01-01', 'prayed_in_mosque', '2025-12-04 03:47:18'),
(52, 1, 'الظهر', '2026-01-06', 'prayed_in_mosque', '2025-12-04 03:47:22'),
(53, 1, 'الفجر', '2026-02-05', 'prayed_in_mosque', '2025-12-04 12:00:26'),
(54, 1, 'الظهر', '2026-02-05', 'prayed_alone', '2025-12-04 12:00:29'),
(55, 1, 'العصر', '2026-02-05', 'delayed', '2025-12-04 12:00:33'),
(56, 1, 'العشاء', '2026-02-05', 'not_prayed', '2025-12-04 12:00:39'),
(57, 1, 'المغرب', '2026-02-05', 'prayed_alone', '2025-12-04 12:00:48'),
(58, 1, 'المغرب', '2025-12-31', 'prayed_in_mosque', '2025-12-04 12:01:54'),
(59, 1, 'الفجر', '2025-12-11', 'prayed_in_mosque', '2025-12-04 12:09:05'),
(60, 1, 'الظهر', '2025-12-11', 'not_prayed', '2025-12-04 12:09:09'),
(61, 3, 'الفجر', '2025-12-04', 'delayed', '2025-12-04 12:26:55'),
(62, 3, 'الظهر', '2025-12-04', 'prayed_alone', '2025-12-04 12:36:09'),
(63, 3, 'العصر', '2025-12-04', 'delayed', '2025-12-04 12:36:31'),
(64, 3, 'الفجر', '2025-12-03', 'prayed_in_mosque', '2025-12-04 14:11:17'),
(65, 3, 'المغرب', '2025-12-04', 'not_prayed', '2025-12-04 14:24:36'),
(66, 3, 'العشاء', '2025-12-04', 'not_prayed', '2025-12-04 14:24:40'),
(67, 1, 'الفجر', '2025-11-28', 'prayed_in_mosque', '2025-12-05 02:25:44'),
(68, 1, 'الظهر', '2025-11-28', 'prayed_in_mosque', '2025-12-05 02:28:47'),
(69, 3, 'الفجر', '2025-12-05', 'prayed_in_mosque', '2025-12-05 19:02:10'),
(70, 3, 'الظهر', '2025-12-05', 'prayed_in_mosque', '2025-12-05 19:02:16'),
(71, 3, 'العصر', '2025-12-05', 'prayed_in_mosque', '2025-12-05 19:02:22'),
(72, 3, 'المغرب', '2025-12-05', 'prayed_in_mosque', '2025-12-05 19:02:25'),
(73, 3, 'العشاء', '2025-12-05', 'prayed_in_mosque', '2025-12-05 19:02:29');

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
(3, '107681726258782612532', 'sahatalllm@gmail.com', 'Sahat Al_llm', 'https://lh3.googleusercontent.com/a/ACg8ocLua0dLyyBRKQ3lCqXBjm52mxRrPBZ1kniBT1WPVfq_Tn4LsQ=s96-c', '2025-12-04 12:17:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `block_all_groups` tinyint(1) DEFAULT 0 COMMENT '0 = معطلة, 1 = مفعلة',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `block_all_groups`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-12-05 18:31:39', '2025-12-05 19:35:37'),
(2, 3, 0, '2025-12-05 18:50:00', '2025-12-05 19:14:28');

-- --------------------------------------------------------

--
-- Table structure for table `worship`
--

CREATE TABLE `worship` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('night_prayer','daily_reminder') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `worship`
--

INSERT INTO `worship` (`id`, `name`, `type`, `description`, `created_at`) VALUES
(1, 'mostafa mtaha', 'night_prayer', NULL, '2025-12-05 01:17:49'),
(2, 'mostafa mtaha', 'night_prayer', NULL, '2025-12-05 01:17:50'),
(3, 'd', 'night_prayer', NULL, '2025-12-05 01:17:55'),
(4, 'd', 'night_prayer', NULL, '2025-12-05 01:17:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `join_code` (`join_code`),
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
  ADD UNIQUE KEY `group_id` (`group_id`),
  ADD KEY `group_id_fk` (`group_id`);

--
-- Indexes for table `nawafil_records`
--
ALTER TABLE `nawafil_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_prayer_record_id` (`prayer_record_id`);

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
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Indexes for table `worship`
--
ALTER TABLE `worship`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `group_settings`
--
ALTER TABLE `group_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nawafil_records`
--
ALTER TABLE `nawafil_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `prayer_records`
--
ALTER TABLE `prayer_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `worship`
--
ALTER TABLE `worship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  ADD CONSTRAINT `fk_nawafil_prayer_records` FOREIGN KEY (`prayer_record_id`) REFERENCES `prayer_records` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `nawafil_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prayer_records`
--
ALTER TABLE `prayer_records`
  ADD CONSTRAINT `prayer_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
