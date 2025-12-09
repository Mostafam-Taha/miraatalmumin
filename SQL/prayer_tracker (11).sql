-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 12:56 PM
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
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `feedback_type` enum('suggestion','complaint','bug','thanks') NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('new','read','in_progress','resolved') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `user_id`, `user_name`, `user_email`, `feedback_type`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mostafam Ta3', 'mostafamta347@gmail.com', 'thanks', 'شكرًا????', 'read', '2025-12-08 10:41:28', '2025-12-08 11:01:09'),
(2, 1, 'Mostafam Ta3', 'mostafamta347@gmail.com', 'complaint', 'scrollbar-width: none;\n            -ms-overflow-style: none;', 'resolved', '2025-12-08 11:34:57', '2025-12-08 11:35:15'),
(3, 1, 'Mostafam Ta3', 'mostafamta347@gmail.com', 'bug', 'asfd', 'new', '2025-12-08 11:36:29', '2025-12-08 11:36:29');

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
  `settings_updated_at` timestamp NULL DEFAULT NULL,
  `telegram_bot_token` varchar(255) DEFAULT NULL,
  `telegram_chat_id` varchar(100) DEFAULT NULL,
  `telegram_username` varchar(100) DEFAULT NULL,
  `telegram_linked_at` timestamp NULL DEFAULT NULL,
  `telegram_notifications` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `user_id`, `group_name`, `emoji`, `group_type`, `description`, `join_code`, `join_link`, `created_by`, `created_at`, `group_image`, `hide_group_name`, `hide_group_image`, `allow_public_members_view`, `custom_join_link`, `settings_updated_at`, `telegram_bot_token`, `telegram_chat_id`, `telegram_username`, `telegram_linked_at`, `telegram_notifications`) VALUES
(1, 0, 'askldfas', '❤', 'public', 'asdfasd', 'group_6930af16918f77.88379552', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930af16918f77.88379552', 1, '2025-12-03 21:43:50', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(2, 0, 'asdfasdasdfasdf', 'dd', 'private', 'dd', 'group_6930afef88b4d6.31395784', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930afef88b4d6.31395784', 2, '2025-12-03 21:47:27', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(5, 0, 'مجموعة طرش', '????', 'public', 'شسيب', 'group_6930c2974ac1c1.74847837', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c2974ac1c1.74847837', 1, '2025-12-03 23:07:03', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(6, 0, 'مجموعة طرشasdfsdf', '????', 'private', 'asdfsdf', 'group_6930c5615b6005.00146987', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5615b6005.00146987', 1, '2025-12-03 23:18:57', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(7, 0, 'مجموعة طرشasdfsdf', '????', 'public', 'asdfasdf', 'group_6930c5d022bda8.71250908', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5d022bda8.71250908', 1, '2025-12-03 23:20:48', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(8, 0, 'adfsadf', '????', 'public', 'asdfasf', 'group_6930c5de6330c4.13104305', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5de6330c4.13104305', 1, '2025-12-03 23:21:02', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(9, 0, 'sdaf', '????', 'public', 'asdfasdfasdf', 'group_6930c5ec247804.36706612', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5ec247804.36706612', 1, '2025-12-03 23:21:16', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(10, 0, 'asdfasdf', '????', 'public', 'sadfasdf', 'group_6930c5f3361a98.78284016', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5f3361a98.78284016', 1, '2025-12-03 23:21:23', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(11, 0, 'صحبة', '⚽', 'public', 'miraatalmumin', 'group_6930c5fb6aab62.66089247', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c5fb6aab62.66089247', 1, '2025-12-03 23:21:31', 'group_11_6933788df3ee99.58258418.webp', 0, 0, 0, 'mostafamtaha', '2025-12-06 23:23:34', '6898971699:AAEJuQsk78Ye5knm7pmqTir3xN4AAdGhX58', NULL, '@booot1235', '2025-12-07 00:19:33', 1),
(13, 0, 'dasf', '????', 'public', 'asdfasdf', 'group_6930c6628da175.29882562', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_6930c6628da175.29882562', 2, '2025-12-03 23:23:14', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(17, 0, 'plays', '????', 'public', 'مجموعة', 'group_693187246b9a21.61960455', 'http://localhost/Miraat Al_Mumin/groups/join_group.php?code=group_693187246b9a21.61960455', 3, '2025-12-04 13:05:40', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(19, 0, 'd', '????', 'public', 'asdf', 'group_69332f479bc8b7.59810630', 'https://localhost/Miraat Al_Mumin/groups/group_preview.php?code=group_69332f479bc8b7.59810630', 3, '2025-12-05 19:15:19', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(20, 0, 'saf', '????', 'public', '0', 'group_69336c5bb4b917.95400092', 'https://localhost/Miraat Al_Mumin/groups/group_preview.php?code=group_69336c5bb4b917.95400092', 1, '2025-12-05 23:35:55', NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(21, 0, 'مجموعة طرشddd', '????', 'public', '', 'group_6933aee96fe470.42342295', 'https://localhost/Miraat Al_Mumin/groups/group_preview.php?code=group_6933aee96fe470.42342295', 1, '2025-12-06 04:19:54', NULL, 0, 0, 0, NULL, NULL, '8211691283:AAHzZoQDJIhGBG9jSUir5qWlxjfpuGFpj5A', NULL, '@booot1235', '2025-12-07 00:54:01', 1);

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
(24, 17, 3, 'admin', '2025-12-04 13:05:40'),
(31, 7, 3, 'member', '2025-12-05 19:14:33'),
(32, 11, 3, 'member', '2025-12-05 19:15:20'),
(33, 20, 1, 'admin', '2025-12-05 23:35:57'),
(34, 21, 1, 'admin', '2025-12-06 04:19:54'),
(36, 11, 2, 'member', '2025-12-06 15:10:26');

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
(34, 3, 14, 'العشاء', 'بعد', 2, '2025-12-04', '2025-12-04 14:24:41'),
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
(50, 1, 15, 'المغرب', 'بعد', 2, '2025-12-05', '2025-12-05 21:09:53'),
(53, 4, 74, 'الفجر', 'قبل', 2, '2025-12-06', '2025-12-06 12:32:38'),
(54, 4, 75, 'الظهر', 'قبل', 4, '2025-12-06', '2025-12-06 12:32:44'),
(55, 4, 75, 'الظهر', 'بعد', 2, '2025-12-06', '2025-12-06 12:32:44'),
(56, 1, 76, 'الفجر', 'قبل', 2, '2025-12-01', '2025-12-06 12:34:53'),
(62, 2, 77, 'الفجر', 'قبل', 2, '2025-12-06', '2025-12-06 13:47:39'),
(63, 2, 78, 'الظهر', 'قبل', 4, '2025-12-06', '2025-12-06 13:47:42'),
(64, 2, 78, 'الظهر', 'بعد', 2, '2025-12-06', '2025-12-06 13:47:42'),
(65, 2, 80, 'المغرب', 'بعد', 2, '2025-12-06', '2025-12-06 13:47:45'),
(66, 2, NULL, 'العشاء', 'بعد', 2, '2025-12-06', '2025-12-06 13:47:47'),
(67, 1, 20, 'العشاء', 'بعد', 2, '2025-12-06', '2025-12-06 14:30:13'),
(68, 1, 47, 'المغرب', 'بعد', 2, '2025-12-06', '2025-12-06 14:30:16'),
(69, 1, 17, 'الظهر', 'بعد', 2, '2025-12-06', '2025-12-06 14:30:19'),
(70, 1, 17, 'الظهر', 'قبل', 4, '2025-12-06', '2025-12-06 14:30:19'),
(73, 2, 98, 'العشاء', 'بعد', 2, '2025-12-06', '2025-12-06 14:48:31'),
(74, 1, 18, 'الفجر', 'قبل', 2, '2025-12-06', '2025-12-07 00:07:29');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 3, '6a2c9b3194bfd2c62ce43f74b396303c', '2025-12-08 12:07:28', '2025-12-08 10:05:33'),
(4, 2, '38df544397ce11094df54116816b37a3', '2025-12-08 12:16:46', '2025-12-08 10:16:46');

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
(1, 1, 'الظهر', '2025-12-03', 'delayed', '2025-12-03 16:04:25'),
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
(21, 1, 'الفجر', '2025-12-08', 'prayed_in_mosque', '2025-12-03 17:47:13'),
(22, 1, 'الظهر', '2025-12-08', 'not_prayed', '2025-12-03 17:47:16'),
(23, 1, 'العصر', '2025-12-08', 'prayed_alone', '2025-12-03 17:47:22'),
(24, 1, 'المغرب', '2025-12-08', 'prayed_alone', '2025-12-03 17:47:26'),
(25, 1, 'العشاء', '2025-12-08', 'prayed_alone', '2025-12-03 17:47:29'),
(26, 1, 'الفجر', '2025-12-07', 'prayed_alone', '2025-12-03 20:08:57'),
(27, 1, 'الظهر', '2025-12-07', 'prayed_in_mosque', '2025-12-03 20:09:01'),
(28, 1, 'الظهر', '2025-12-09', 'prayed_in_mosque', '2025-12-03 20:16:03'),
(29, 1, 'المغرب', '2025-12-09', 'prayed_in_mosque', '2025-12-03 20:16:09'),
(30, 1, 'الظهر', '2025-12-10', 'prayed_in_mosque', '2025-12-03 20:16:32'),
(31, 1, 'الفجر', '2025-12-10', 'prayed_in_mosque', '2025-12-03 20:16:48'),
(32, 1, 'المغرب', '2025-12-10', 'prayed_alone', '2025-12-03 20:34:11'),
(33, 1, 'العصر', '2025-12-10', 'prayed_alone', '2025-12-03 20:49:19'),
(34, 1, 'الفجر', '2025-12-17', 'prayed_alone', '2025-12-03 20:49:39'),
(35, 1, 'العصر', '2025-12-09', 'prayed_alone', '2025-12-03 20:50:54'),
(36, 1, 'الفجر', '2025-12-16', 'prayed_alone', '2025-12-03 21:09:02'),
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
(56, 1, 'العشاء', '2026-02-05', 'prayed_alone', '2025-12-04 12:00:39'),
(57, 1, 'المغرب', '2026-02-05', 'prayed_alone', '2025-12-04 12:00:48'),
(58, 1, 'المغرب', '2025-12-31', 'prayed_in_mosque', '2025-12-04 12:01:54'),
(59, 1, 'الفجر', '2025-12-11', 'prayed_in_mosque', '2025-12-04 12:09:05'),
(60, 1, 'الظهر', '2025-12-11', 'prayed_alone', '2025-12-04 12:09:09'),
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
(73, 3, 'العشاء', '2025-12-05', 'prayed_in_mosque', '2025-12-05 19:02:29'),
(74, 4, 'الفجر', '2025-12-06', 'prayed_in_mosque', '2025-12-06 12:32:38'),
(75, 4, 'الظهر', '2025-12-06', 'prayed_in_mosque', '2025-12-06 12:32:44'),
(76, 1, 'الفجر', '2025-12-01', 'prayed_in_mosque', '2025-12-06 12:34:53'),
(77, 2, 'الفجر', '2025-12-06', 'prayed_in_mosque', '2025-12-06 13:46:20'),
(78, 2, 'الظهر', '2025-12-06', 'prayed_in_mosque', '2025-12-06 13:46:24'),
(79, 2, 'العصر', '2025-12-06', 'prayed_in_mosque', '2025-12-06 13:46:27'),
(80, 2, 'المغرب', '2025-12-06', 'prayed_in_mosque', '2025-12-06 13:46:30'),
(82, 2, 'الفجر', '2025-12-05', 'prayed_in_mosque', '2025-12-06 13:48:32'),
(83, 2, 'الظهر', '2025-12-05', 'prayed_in_mosque', '2025-12-06 13:48:35'),
(85, 4, 'المغرب', '2025-12-05', 'prayed_in_mosque', '2025-12-07 13:48:42'),
(86, 4, 'العشاء', '2025-12-05', 'prayed_in_mosque', '2025-12-07 13:48:46'),
(87, 4, 'الفجر', '2025-12-05', 'prayed_in_mosque', '2025-12-06 14:02:06'),
(88, 4, 'الظهر', '2025-12-05', 'prayed_in_mosque', '2025-12-06 14:02:09'),
(89, 4, 'العصر', '2025-12-05', 'prayed_in_mosque', '2025-12-06 14:02:12'),
(90, 4, 'المغرب', '2025-12-05', 'prayed_in_mosque', '2025-12-06 14:02:15'),
(91, 4, 'العشاء', '2025-12-05', 'prayed_in_mosque', '2025-12-06 14:02:18'),
(92, 4, 'العصر', '2025-12-06', 'prayed_in_mosque', '2025-12-06 14:02:37'),
(93, 4, 'المغرب', '2025-12-06', 'prayed_in_mosque', '2025-12-06 14:02:39'),
(94, 4, 'العشاء', '2025-12-06', 'prayed_in_mosque', '2025-12-06 14:02:43'),
(95, 2, 'العصر', '2025-12-05', 'prayed_in_mosque', '2025-12-06 14:43:49'),
(96, 2, 'المغرب', '2025-12-05', 'prayed_in_mosque', '2025-12-06 14:44:03'),
(97, 2, 'العشاء', '2025-12-05', 'prayed_in_mosque', '2025-12-06 14:44:06'),
(98, 2, 'العشاء', '2025-12-06', 'prayed_in_mosque', '2025-12-06 14:48:11');

-- --------------------------------------------------------

--
-- Table structure for table `pro_subscriptions`
--

CREATE TABLE `pro_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `insta_user` varchar(100) DEFAULT NULL,
  `plan_type` enum('monthly','yearly') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `extracted_data` text DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pro_subscriptions`
--

INSERT INTO `pro_subscriptions` (`id`, `user_id`, `phone`, `insta_user`, `plan_type`, `amount`, `receipt_image`, `extracted_data`, `payment_date`, `status`, `created_at`) VALUES
(1, 1, '01003504114', '', 'monthly', 59.00, 'uploads/receipts/1765158736_photo_5886630439181552802_y.jpg', '{\"amount\":213984157073,\"date\":\"\",\"reference\":\"213984157073\",\"sender\":\"\"}', '2025-12-08 03:52:16', 'pending', '2025-12-08 01:52:16'),
(2, 1, '01003504114', 'asdf', 'monthly', 59.00, 'uploads/receipts/1765159761_photo_5886630439181552802_y.jpg', '{\"amount\":\"2025\",\"date\":\"\",\"reference\":\"213984157073\",\"sender\":\"\"}', '2025-12-08 04:09:21', 'pending', '2025-12-08 02:09:21'),
(3, 1, '01003504114', 'asdf', 'yearly', 659.00, 'uploads/receipts/1765161203_photo_5886630439181552802_y.jpg', '{\"amount\":\"50.59\",\"date\":\"01:20 AM\",\"reference\":\"213984157073\",\"sender\":\"Mostafa Mohamed Taha\",\"transactionType\":\"ارسال نقود\"}', '2025-12-08 04:33:23', 'pending', '2025-12-08 02:33:23'),
(4, 1, '01003504114', 'asdf', 'monthly', 59.00, 'uploads/receipts/1765162657_photo_5886630439181552802_y.jpg', '{\"amount\":\"50.59\",\"date\":\"01:20 AM\",\"reference\":\"213984157073\",\"sender\":\"Mostafa Mohamed Taha\",\"transactionType\":\"ارسال نقود\"}', '2025-12-08 04:57:37', 'rejected', '2025-12-08 02:57:37'),
(5, 2, '01003504114', 'asdf', 'monthly', 59.00, 'uploads/receipts/1765166835_photo_5886630439181552802_y.jpg', '{\"amount\":\"50.59\",\"date\":\"01:20 AM\",\"reference\":\"213984157073\",\"sender\":\"Mostafa Mohamed Taha\",\"transactionType\":\"ارسال نقود\"}', '2025-12-08 06:07:15', 'verified', '2025-12-08 04:07:15');

-- --------------------------------------------------------

--
-- Table structure for table `telegram_settings`
--

CREATE TABLE `telegram_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `bot_name` varchar(100) DEFAULT NULL,
  `chat_id` varchar(50) DEFAULT NULL,
  `enable_notifications` tinyint(1) DEFAULT 1,
  `enable_backups` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `telegram_settings`
--

INSERT INTO `telegram_settings` (`id`, `user_id`, `token`, `bot_name`, `chat_id`, `enable_notifications`, `enable_backups`, `created_at`, `updated_at`) VALUES
(2, 1, '6898971699:AAHgfK4iHyFRmGwaaaNrEaxotw8p7kdLJxw', 'sahatalllmbot', '5299691286', 1, 1, '2025-12-08 03:25:05', '2025-12-08 03:25:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `google_id` varchar(255) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `current_prayer_streak` int(11) DEFAULT 0,
  `max_prayer_streak` int(11) DEFAULT 0,
  `last_prayer_date` date DEFAULT NULL,
  `Pro` tinyint(1) NOT NULL DEFAULT 0,
  `device_type` varchar(50) DEFAULT NULL,
  `last_device_login` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `google_id`, `password_hash`, `email`, `name`, `profile_picture`, `created_at`, `current_prayer_streak`, `max_prayer_streak`, `last_prayer_date`, `Pro`, `device_type`, `last_device_login`, `user_agent`, `last_login`) VALUES
(1, '118380663984086018501', NULL, 'mostafamta347@gmail.com', 'Mostafam Ta3', 'https://lh3.googleusercontent.com/a/ACg8ocKoPaBU55MVAUPWK8vAXRZL5l2zajBxEXOg3H-IujH4wFfVIA=s96-c', '2025-12-03 13:59:53', 0, 7, NULL, 0, 'windows', 'windows - Windows - Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-08 11:53:15'),
(2, '107567217322334047953', NULL, 'mostafamtaha66@gmail.com', 'Mostafa Taha', 'https://lh3.googleusercontent.com/a/ACg8ocJs4KnLtjalY248GvqkYyn1Sorr5alNMz8ZsXLJL6a_wpqbVw=s96-c', '2025-12-03 15:13:57', 0, 3, '2025-12-06', 1, NULL, NULL, NULL, NULL),
(3, '107681726258782612532', NULL, 'sahatalllm@gmail.com', 'Sahat Al_llm', 'https://lh3.googleusercontent.com/a/ACg8ocLua0dLyyBRKQ3lCqXBjm52mxRrPBZ1kniBT1WPVfq_Tn4LsQ=s96-c', '2025-12-04 12:17:34', 1, 1, NULL, 0, NULL, NULL, NULL, NULL),
(4, '110520728785467475730', NULL, 'miraatalmumin@gmail.com', 'Miraat al-Mumin', 'https://lh3.googleusercontent.com/a/ACg8ocK4gVWJYCKqft4oVQh15lUYBiE6TzMKORmIKykivgV57q044Q=s96-c', '2025-12-06 12:24:48', 2, 2, '2025-12-06', 0, NULL, NULL, NULL, NULL),
(5, '$2y$10$DVKcCGUGVFlDemZAkWCobuiqAC0L15UG6gfdbClxIdOCVZ7TmwrF.', NULL, 'mostafamtahad66@gmail.com', 'test', 'https://ui-avatars.com/api/?name=test&background=059669&color=fff&size=256', '2025-12-08 10:09:54', 0, 0, NULL, 0, NULL, NULL, NULL, NULL),
(6, '$2y$10$7KUhsgrhvvxozhUxkeyAZuVBWv.Wxh97vidcaIUliKTAXiQxgWeRC', NULL, 'mostafamtaha6@gmail.com', 'test', NULL, '2025-12-08 10:17:50', 0, 0, NULL, 0, NULL, NULL, NULL, NULL);

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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_type` (`feedback_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

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
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`),
  ADD UNIQUE KEY `unique_token` (`token`),
  ADD KEY `idx_token_expiry` (`token`,`expires_at`);

--
-- Indexes for table `prayer_records`
--
ALTER TABLE `prayer_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`),
  ADD KEY `idx_user_date` (`user_id`,`date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `pro_subscriptions`
--
ALTER TABLE `pro_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `telegram_settings`
--
ALTER TABLE `telegram_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_last_login` (`last_login`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `group_settings`
--
ALTER TABLE `group_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nawafil_records`
--
ALTER TABLE `nawafil_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `prayer_records`
--
ALTER TABLE `prayer_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `pro_subscriptions`
--
ALTER TABLE `pro_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `telegram_settings`
--
ALTER TABLE `telegram_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
