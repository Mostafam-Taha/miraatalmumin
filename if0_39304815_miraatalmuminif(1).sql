-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql207.infinityfree.com
-- Generation Time: Jun 03, 2026 at 06:58 AM
-- Server version: 11.4.12-MariaDB
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
-- Table structure for table `additional_prayers`
--

CREATE TABLE `additional_prayers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prayer_name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `rakats` int(11) NOT NULL DEFAULT 2,
  `notes` text DEFAULT NULL,
  `is_checked` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `additional_prayers`
--

INSERT INTO `additional_prayers` (`id`, `user_id`, `prayer_name`, `date`, `rakats`, `notes`, `is_checked`) VALUES
(5, 9, 'http://miraat-almumin.xo.je/api/reset_password.php?token=9138c03e6537db76090635156bcb740e', '2026-02-22', 2, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `admin-stv`
--

CREATE TABLE `admin-stv` (
  `id` int(11) NOT NULL,
  `admin_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','moderator') DEFAULT 'admin',
  `avatar` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin-stv`
--

INSERT INTO `admin-stv` (`id`, `admin_id`, `name`, `email`, `password`, `role`, `avatar`, `last_login`, `created_at`) VALUES
(3, 'admin_id', 'Mostafamohamedtaha ai', 'mostafamtaha@gmail.com', '$2y$10$N0oxpsmebISYgl8rOR5PfeWRQ4PNd6bq987OuPnJWU/iBR298rs9u', 'admin', NULL, '2026-05-02 20:36:57', '2026-05-02 20:36:02'),
(2, 'mostafaadmin', 'Mostafamohamedtaha', 'mostafamta347@gmail.com', '$2y$10$4Yl4IcXtJ8uCZprBP9Lzm.d8.j.wrbW1yYnE0yORgQ2uRmY/J.Ev2', 'admin', NULL, '2026-04-28 09:42:30', '2026-04-28 09:41:52');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `role` enum('super_admin','admin','moderator') NOT NULL DEFAULT 'admin',
  `permissions` text DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `role`, `permissions`, `last_login`, `last_ip`, `is_active`, `created_by`) VALUES
(1, 'mostafamtaha', 'super_admin', 'a', '2026-04-28 07:00:00', 'a', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `device_activity_log`
--

CREATE TABLE `device_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `action` enum('login','logout','forced_logout','device_added','device_removed','settings_changed') NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `device_activity_log`
--

INSERT INTO `device_activity_log` (`id`, `user_id`, `session_id`, `action`, `device_name`, `ip_address`, `user_agent`, `created_at`) VALUES
(24, 9, 10, 'login', 'Android - Chrome', '156.222.184.42', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 04:09:29'),
(25, 9, 11, 'login', 'Windows - Edge', '156.222.184.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-03 04:09:37'),
(26, 9, 10, 'logout', 'Android - Chrome', '196.136.176.11', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 04:10:20'),
(27, 9, 12, 'login', 'Android - Chrome', '196.136.176.11', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 04:10:31'),
(28, 9, 12, 'logout', 'Android - Chrome', '156.222.184.42', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 04:12:56'),
(29, 9, 13, 'login', 'Android - Chrome', '156.222.184.42', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 04:13:04'),
(30, 9, 13, 'logout', 'Android - Chrome', '156.222.184.42', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 04:13:11'),
(31, 9, 14, 'login', 'Android - Chrome', '156.222.184.42', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 04:13:29'),
(32, 9, 14, 'forced_logout', 'Android - Chrome', '156.222.184.42', NULL, '2026-05-03 04:13:39'),
(33, 9, 15, 'login', 'Android - Chrome', '156.222.184.42', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 04:14:12'),
(34, 9, 11, 'forced_logout', 'Windows - Edge', '156.222.184.42', NULL, '2026-05-03 04:14:22'),
(35, 9, 15, 'logout', 'Android - Chrome', '156.222.184.42', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 04:37:02'),
(36, 26, 16, 'login', 'Android - Chrome', '156.222.184.42', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 04:37:10'),
(37, 9, 17, 'login', 'Windows - Edge', '156.222.146.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-03 10:15:32'),
(38, 28, 18, 'login', 'Windows - Edge', '156.222.146.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-06 20:51:02'),
(39, 9, 17, 'login', 'Windows - Edge', '156.222.6.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-09 06:52:01'),
(40, 9, 19, 'login', 'Android - Chrome', '156.222.6.45', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-10 09:43:32'),
(41, 9, 19, 'login', 'Android - Chrome', '156.222.74.249', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-15 09:31:05'),
(42, 9, 19, 'logout', 'Android - Chrome', '156.222.74.249', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-15 14:26:26'),
(43, 29, 20, 'login', 'Android - Chrome', '156.222.74.249', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-15 14:26:38'),
(44, 9, 21, 'login', 'Android - Chrome', '156.222.213.177', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-17 19:37:11'),
(45, 9, NULL, 'settings_changed', NULL, '156.222.213.177', NULL, '2026-05-18 04:12:54'),
(46, 9, NULL, 'settings_changed', NULL, '156.222.213.177', NULL, '2026-05-18 04:12:54'),
(47, 9, 21, 'login', 'Android - Chrome', '156.222.213.177', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 17:35:43'),
(48, 9, 21, 'logout', 'Android - Chrome', '156.222.213.177', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-20 06:48:46'),
(49, 9, 22, 'login', 'Android - Chrome', '156.222.213.177', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-20 06:48:55'),
(50, 9, 17, 'login', 'Windows - Edge', '156.222.213.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', '2026-05-21 00:26:42'),
(51, 9, 17, 'login', 'Windows - Edge', '156.222.109.153', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', '2026-05-24 13:29:53'),
(52, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:34'),
(53, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:35'),
(54, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:36'),
(55, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:36'),
(56, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:36'),
(57, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:36'),
(58, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:37'),
(59, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:37'),
(60, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:37'),
(61, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:37'),
(62, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:37'),
(63, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:38'),
(64, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:38'),
(65, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:38'),
(66, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:38'),
(67, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:38'),
(68, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:39'),
(69, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:39'),
(70, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:39'),
(71, 9, NULL, 'settings_changed', NULL, '156.222.109.153', NULL, '2026-05-24 13:36:39');

-- --------------------------------------------------------

--
-- Table structure for table `fasting_records`
--

CREATE TABLE `fasting_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fasting_type` enum('obligatory','sunnah','voluntary') NOT NULL DEFAULT 'voluntary',
  `date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `is_checked` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fasting_records`
--

INSERT INTO `fasting_records` (`id`, `user_id`, `fasting_type`, `date`, `notes`, `is_checked`) VALUES
(3, 9, 'obligatory', '2026-02-22', 'javascript:(function(){var s=document.createElement(\'script\');s.src=\'https://cdn.jsdelivr.net/npm/eruda\';document.head.appendChild(s);s.onload=function(){eruda.init();}})();', 0),
(6, 9, 'sunnah', '2026-05-19', 'ذي الحجة', 1),
(7, 9, 'sunnah', '2026-05-26', 'صيام يوم عرفة', 0);

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
(4, 9, 'miraatalmumin', 'miraatalmumin@gmail.com', 'thanks', 'رسالة اختبار', 'read', '2025-12-11 04:37:13', '2025-12-11 04:37:41'),
(5, 16, 'عبدالرحمن', 'poooga208@gmail.com', 'thanks', '', 'new', '2025-12-12 10:43:58', '2025-12-12 10:43:58'),
(6, 16, 'عبدالرحمن', 'poooga208@gmail.com', 'thanks', '', 'new', '2025-12-12 10:44:03', '2025-12-12 10:44:03'),
(7, 16, 'عبدالرحمن', 'poooga208@gmail.com', 'bug', '', 'resolved', '2025-12-12 10:44:16', '2025-12-22 21:22:30'),
(8, 9, 'miraatalmumin', 'miraatalmumin@gmail.com', 'thanks', '', 'new', '2025-12-12 14:00:31', '2025-12-12 14:00:31'),
(9, 9, 'miraatalmumin', 'miraatalmumin@gmail.com', 'thanks', 'رؤياوزتةاااووو', 'new', '2026-02-07 21:12:59', '2026-02-07 21:12:59'),
(10, 16, 'عبدالرحمن', 'poooga208@gmail.com', 'thanks', '', 'read', '2026-04-24 16:28:26', '2026-04-28 07:51:52'),
(11, 11, 'Moaaz الحراق 2', 'moaazmohamed1jj@gmail.com', 'complaint', 'انا اعترض علي وجوب الصلاة في الموقع', 'read', '2026-04-28 12:23:03', '2026-04-29 03:39:41'),
(12, 9, 'Miraat al-Mumin', 'miraatalmumin@gmail.com', 'thanks', '', 'new', '2026-04-29 03:21:50', '2026-04-29 03:21:50'),
(13, 9, 'Miraat al-Mumin', 'miraatalmumin@gmail.com', 'complaint', 'ازيك ، يريت تبعت استفسارك بشكل مباشر علشان نرد اول منشوف  الرساله ????\n\n الرقم غير مخصص للاستشارات الشخصيه ????', 'new', '2026-05-19 13:40:53', '2026-05-19 13:40:53');

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
(24, 10, 'المشطشطين', '????', 'public', 'مجموعة المشطشطين الحراقين المولعين', 'group_69399502d8c413.16354601', 'http://miraat-almumin.xo.je/groups/group_preview.php?code=group_69399502d8c413.16354601', 10, '2025-12-10 15:42:58', 'group_24_693997382418f6.76889172.webp', 0, 0, 0, '', '2025-12-29 13:32:56', NULL, NULL, NULL, NULL, 1),
(26, 9, 'miraatalmumin', '☕', 'public', '', 'group_69f179a3034020.90898394', 'http://miraat-almumin.xo.je/groups/group_preview.php?code=group_69f179a3034020.90898394', 9, '2026-04-29 03:23:14', 'group_26_6a08044a679f47.55430879.webp', 0, 0, 1, '', '2026-05-16 05:42:57', NULL, NULL, NULL, NULL, 1);

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
(38, 24, 10, 'admin', '2025-12-10 15:42:58'),
(39, 24, 9, 'member', '2025-12-10 15:48:55'),
(40, 24, 11, 'member', '2025-12-10 15:49:24'),
(42, 24, 16, 'member', '2026-04-24 16:27:26'),
(44, 26, 9, 'admin', '2026-04-29 03:23:14'),
(45, 26, 29, 'member', '2026-05-17 19:36:23');

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
-- Table structure for table `habits`
--

CREATE TABLE `habits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'مالك العادة (NULL للعادات الافتراضية)',
  `name` varchar(100) NOT NULL COMMENT 'اسم العادة',
  `icon` varchar(10) NOT NULL DEFAULT '⭐' COMMENT 'إيموجي العادة',
  `category` enum('صلاة','قرآن','ذكر','صيام','صدقة','أخرى') NOT NULL DEFAULT 'أخرى',
  `target_type` enum('boolean','count') NOT NULL DEFAULT 'boolean' COMMENT 'boolean=نعم/لا | count=عدد',
  `target_value` int(11) NOT NULL DEFAULT 1 COMMENT 'الهدف اليومي (عدد الصفحات/الأذكار...)',
  `target_unit` varchar(30) DEFAULT NULL COMMENT 'وحدة القياس: صفحة، ركعة، مرة...',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'هل العادة نشطة؟',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habits`
--

INSERT INTO `habits` (`id`, `user_id`, `name`, `icon`, `category`, `target_type`, `target_value`, `target_unit`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 9, 'قيام الليل', '🌙', 'صلاة', 'boolean', 1, NULL, 1, 1, '2026-04-29 03:12:54', '2026-04-29 03:17:52'),
(2, 0, 'صلاة الضحى', '☀️', 'صلاة', 'count', 2, 'ركعة', 1, 2, '2026-04-29 03:12:54', '2026-04-29 03:12:54'),
(3, 0, 'قراءة الورد اليومي', '📖', 'قرآن', 'count', 1, 'جزء', 1, 3, '2026-04-29 03:12:54', '2026-04-29 03:12:54'),
(4, 0, 'صيام', '🌙', 'صيام', 'boolean', 1, NULL, 1, 4, '2026-04-29 03:12:54', '2026-04-29 03:12:54'),
(5, 0, 'صدقة يومية', '💚', 'صدقة', 'boolean', 1, NULL, 1, 5, '2026-04-29 03:12:54', '2026-04-29 03:12:54'),
(6, 0, 'أذكار الصباح والمساء', '📿', 'ذكر', 'boolean', 1, NULL, 1, 6, '2026-04-29 03:12:54', '2026-04-29 03:12:54'),
(7, 0, 'الاستغفار', '🤲', 'ذكر', 'count', 100, 'مرة', 1, 7, '2026-04-29 03:12:54', '2026-04-29 03:12:54'),
(8, 0, 'الصلاة على النبي ﷺ', '💚', 'ذكر', 'count', 100, 'مرة', 1, 8, '2026-04-29 03:12:54', '2026-04-29 03:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `habit_logs`
--

CREATE TABLE `habit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `habit_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('done','skipped','partial') NOT NULL DEFAULT 'done',
  `value` int(11) NOT NULL DEFAULT 1 COMMENT 'القيمة المنجزة (للعادات العددية)',
  `note` varchar(255) DEFAULT NULL COMMENT 'ملاحظة اختيارية',
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
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
(106, 22, 486, 'الفجر', 'قبل', 2, '2026-04-22', '2026-04-28 16:10:09'),
(108, 11, 491, 'الفجر', 'قبل', 2, '2026-04-29', '2026-04-29 16:23:19'),
(110, 9, NULL, 'المغرب', 'بعد', 2, '2026-05-03', '2026-05-03 03:17:23'),
(111, 9, NULL, 'الظهر', 'قبل', 4, '2026-05-03', '2026-05-03 03:17:51'),
(112, 9, NULL, 'الظهر', 'بعد', 2, '2026-05-03', '2026-05-03 03:17:51'),
(113, 16, NULL, 'الفجر', 'قبل', 2, '2026-05-06', '2026-05-06 01:58:19'),
(114, 28, NULL, 'قيام الليل', 'أداء', 12, '2026-05-06', '2026-05-06 20:51:20'),
(117, 28, 520, 'قيام الليل', 'أداء', 12, '2026-05-07', '2026-05-06 21:16:06'),
(119, 28, 519, 'الضحى', 'أداء', 8, '2026-05-07', '2026-05-06 21:24:07'),
(121, 9, NULL, 'الضحى', 'أداء', 2, '2026-05-07', '2026-05-06 21:49:27'),
(122, 29, 530, 'الفجر', 'قبل', 2, '2026-05-15', '2026-05-15 14:26:49'),
(123, 9, 531, 'الفجر', 'قبل', 2, '2026-05-15', '2026-05-15 14:30:34'),
(125, 9, 536, 'الفجر', 'قبل', 2, '2026-05-16', '2026-05-16 05:41:41'),
(126, 9, 538, 'الفجر', 'قبل', 2, '2026-05-17', '2026-05-17 10:02:31'),
(129, 9, 611, 'الفجر', 'قبل', 2, '2026-05-19', '2026-05-20 00:19:36'),
(130, 9, 616, 'الفجر', 'قبل', 2, '2026-05-20', '2026-05-20 06:43:06'),
(131, 9, 650, 'الفجر', 'قبل', 2, '2026-05-26', '2026-05-26 01:51:21');

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

-- --------------------------------------------------------

--
-- Table structure for table `prayer_records`
--

CREATE TABLE `prayer_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prayer_name` varchar(20) NOT NULL,
  `type` enum('obligatory','sunnah') NOT NULL DEFAULT 'obligatory',
  `date` date NOT NULL,
  `status` varchar(20) NOT NULL COMMENT 'prayed_in_mosque, prayed_alone, not_prayed, delayed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prayer_records`
--

INSERT INTO `prayer_records` (`id`, `user_id`, `prayer_name`, `type`, `date`, `status`, `created_at`) VALUES
(128, 9, 'الفجر', 'obligatory', '2025-12-10', 'not_prayed', '2025-12-10 18:23:41'),
(129, 9, 'الظهر', 'obligatory', '2025-12-10', 'not_prayed', '2025-12-10 18:23:46'),
(130, 9, 'العصر', 'obligatory', '2025-12-10', 'not_prayed', '2025-12-10 18:23:49'),
(131, 9, 'المغرب', 'obligatory', '2025-12-10', 'not_prayed', '2025-12-10 18:23:53'),
(132, 9, 'العشاء', 'obligatory', '2025-12-10', 'not_prayed', '2025-12-10 18:23:57'),
(133, 15, 'الفجر', 'obligatory', '2025-12-11', 'not_prayed', '2025-12-11 08:16:33'),
(134, 9, 'الفجر', 'obligatory', '2025-12-12', 'not_prayed', '2025-12-12 01:03:33'),
(135, 9, 'الظهر', 'obligatory', '2025-12-12', 'not_prayed', '2025-12-12 01:03:35'),
(136, 9, 'العصر', 'obligatory', '2025-12-12', 'not_prayed', '2025-12-12 01:03:38'),
(137, 9, 'المغرب', 'obligatory', '2025-12-12', 'not_prayed', '2025-12-12 01:03:44'),
(138, 9, 'العشاء', 'obligatory', '2025-12-12', 'not_prayed', '2025-12-12 01:03:48'),
(139, 16, 'الظهر', 'obligatory', '2025-12-12', 'prayed_in_mosque', '2025-12-12 10:43:36'),
(140, 16, 'الفجر', 'obligatory', '2025-12-12', 'prayed_alone', '2025-12-12 10:43:42'),
(141, 9, 'الفجر', 'obligatory', '2025-12-11', 'not_prayed', '2025-12-12 13:56:00'),
(142, 9, 'العصر', 'obligatory', '2025-12-11', 'not_prayed', '2025-12-12 13:56:04'),
(143, 9, 'المغرب', 'obligatory', '2025-12-11', 'not_prayed', '2025-12-12 13:56:08'),
(144, 9, 'العشاء', 'obligatory', '2025-12-11', 'not_prayed', '2025-12-12 13:56:12'),
(145, 9, 'الظهر', 'obligatory', '2025-12-11', 'not_prayed', '2025-12-12 13:56:15'),
(146, 9, 'الفجر', 'obligatory', '2025-12-24', 'prayed_alone', '2025-12-24 03:51:21'),
(147, 9, 'الفجر', 'obligatory', '2025-12-29', 'prayed_alone', '2025-12-29 05:26:37'),
(148, 9, 'الظهر', 'obligatory', '2025-12-29', 'prayed_in_mosque', '2025-12-29 12:49:16'),
(149, 9, 'الظهر', 'obligatory', '2025-12-24', 'not_prayed', '2025-12-29 12:49:47'),
(150, 9, 'العصر', 'obligatory', '2025-12-24', 'not_prayed', '2025-12-29 12:49:50'),
(151, 9, 'المغرب', 'obligatory', '2025-12-24', 'not_prayed', '2025-12-29 12:49:53'),
(152, 9, 'العشاء', 'obligatory', '2025-12-24', 'not_prayed', '2025-12-29 12:49:56'),
(153, 9, 'الفجر', 'obligatory', '2025-12-25', 'not_prayed', '2025-12-29 12:50:01'),
(154, 9, 'الظهر', 'obligatory', '2025-12-25', 'not_prayed', '2025-12-29 12:50:05'),
(155, 9, 'العصر', 'obligatory', '2025-12-25', 'not_prayed', '2025-12-29 12:50:09'),
(156, 9, 'المغرب', 'obligatory', '2025-12-25', 'not_prayed', '2025-12-29 12:50:12'),
(157, 9, 'العشاء', 'obligatory', '2025-12-25', 'not_prayed', '2025-12-29 12:50:15'),
(158, 9, 'الفجر', 'obligatory', '2025-12-26', 'not_prayed', '2025-12-29 12:50:20'),
(159, 9, 'الظهر', 'obligatory', '2025-12-26', 'not_prayed', '2025-12-29 12:50:23'),
(160, 9, 'العصر', 'obligatory', '2025-12-26', 'not_prayed', '2025-12-29 12:50:26'),
(161, 9, 'المغرب', 'obligatory', '2025-12-26', 'not_prayed', '2025-12-29 12:50:29'),
(162, 9, 'العشاء', 'obligatory', '2025-12-26', 'not_prayed', '2025-12-29 12:50:33'),
(163, 9, 'الفجر', 'obligatory', '2025-12-27', 'not_prayed', '2025-12-29 12:50:37'),
(164, 9, 'الظهر', 'obligatory', '2025-12-27', 'not_prayed', '2025-12-29 12:50:41'),
(165, 9, 'العصر', 'obligatory', '2025-12-27', 'not_prayed', '2025-12-29 12:50:45'),
(166, 9, 'المغرب', 'obligatory', '2025-12-27', 'not_prayed', '2025-12-29 12:50:49'),
(167, 9, 'العشاء', 'obligatory', '2025-12-27', 'not_prayed', '2025-12-29 12:50:52'),
(168, 9, 'الفجر', 'obligatory', '2025-12-28', 'not_prayed', '2025-12-29 12:50:57'),
(169, 9, 'الظهر', 'obligatory', '2025-12-28', 'not_prayed', '2025-12-29 12:51:00'),
(170, 9, 'العصر', 'obligatory', '2025-12-28', 'not_prayed', '2025-12-29 12:51:02'),
(171, 9, 'المغرب', 'obligatory', '2025-12-28', 'not_prayed', '2025-12-29 12:51:05'),
(172, 9, 'العشاء', 'obligatory', '2025-12-28', 'not_prayed', '2025-12-29 12:51:08'),
(173, 9, 'الفجر', 'obligatory', '2025-12-13', 'not_prayed', '2025-12-29 12:52:32'),
(174, 9, 'الظهر', 'obligatory', '2025-12-13', 'not_prayed', '2025-12-29 12:52:34'),
(175, 9, 'العصر', 'obligatory', '2025-12-13', 'not_prayed', '2025-12-29 12:52:36'),
(176, 9, 'المغرب', 'obligatory', '2025-12-13', 'not_prayed', '2025-12-29 12:52:39'),
(177, 9, 'العشاء', 'obligatory', '2025-12-13', 'not_prayed', '2025-12-29 12:52:41'),
(178, 9, 'الفجر', 'obligatory', '2025-12-14', 'not_prayed', '2025-12-29 12:52:45'),
(179, 9, 'الظهر', 'obligatory', '2025-12-14', 'not_prayed', '2025-12-29 12:52:47'),
(180, 9, 'العصر', 'obligatory', '2025-12-14', 'not_prayed', '2025-12-29 12:52:49'),
(181, 9, 'المغرب', 'obligatory', '2025-12-14', 'not_prayed', '2025-12-29 12:52:52'),
(182, 9, 'العشاء', 'obligatory', '2025-12-14', 'not_prayed', '2025-12-29 12:52:55'),
(183, 9, 'الفجر', 'obligatory', '2025-12-15', 'not_prayed', '2025-12-29 12:52:59'),
(184, 9, 'الظهر', 'obligatory', '2025-12-15', 'not_prayed', '2025-12-29 12:53:02'),
(185, 9, 'العصر', 'obligatory', '2025-12-15', 'not_prayed', '2025-12-29 12:53:06'),
(186, 9, 'المغرب', 'obligatory', '2025-12-15', 'not_prayed', '2025-12-29 12:53:09'),
(187, 9, 'العشاء', 'obligatory', '2025-12-15', 'not_prayed', '2025-12-29 12:53:12'),
(188, 9, 'الفجر', 'obligatory', '2025-12-16', 'not_prayed', '2025-12-29 12:53:17'),
(189, 9, 'الظهر', 'obligatory', '2025-12-16', 'not_prayed', '2025-12-29 12:53:21'),
(190, 9, 'العصر', 'obligatory', '2025-12-16', 'not_prayed', '2025-12-29 12:53:24'),
(191, 9, 'المغرب', 'obligatory', '2025-12-16', 'not_prayed', '2025-12-29 12:53:26'),
(192, 9, 'العشاء', 'obligatory', '2025-12-16', 'not_prayed', '2025-12-29 12:53:29'),
(193, 9, 'الفجر', 'obligatory', '2025-12-17', 'not_prayed', '2025-12-29 12:53:34'),
(194, 9, 'الظهر', 'obligatory', '2025-12-17', 'not_prayed', '2025-12-29 12:53:36'),
(195, 9, 'العصر', 'obligatory', '2025-12-17', 'not_prayed', '2025-12-29 12:53:39'),
(196, 9, 'المغرب', 'obligatory', '2025-12-17', 'not_prayed', '2025-12-29 12:53:43'),
(197, 9, 'العشاء', 'obligatory', '2025-12-17', 'not_prayed', '2025-12-29 12:53:46'),
(198, 9, 'الفجر', 'obligatory', '2025-12-18', 'not_prayed', '2025-12-29 12:53:51'),
(199, 9, 'الظهر', 'obligatory', '2025-12-18', 'not_prayed', '2025-12-29 12:53:54'),
(200, 9, 'العصر', 'obligatory', '2025-12-18', 'not_prayed', '2025-12-29 12:53:58'),
(201, 9, 'المغرب', 'obligatory', '2025-12-18', 'not_prayed', '2025-12-29 12:54:01'),
(202, 9, 'العشاء', 'obligatory', '2025-12-18', 'not_prayed', '2025-12-29 12:54:04'),
(203, 9, 'الفجر', 'obligatory', '2025-12-19', 'not_prayed', '2025-12-29 12:54:09'),
(204, 9, 'العشاء', 'obligatory', '2025-12-19', 'not_prayed', '2025-12-29 12:54:12'),
(205, 9, 'المغرب', 'obligatory', '2025-12-19', 'not_prayed', '2025-12-29 12:54:14'),
(206, 9, 'العصر', 'obligatory', '2025-12-19', 'not_prayed', '2025-12-29 12:54:17'),
(207, 9, 'الظهر', 'obligatory', '2025-12-19', 'not_prayed', '2025-12-29 12:54:20'),
(208, 9, 'الفجر', 'obligatory', '2025-12-20', 'not_prayed', '2025-12-29 12:54:24'),
(209, 9, 'الظهر', 'obligatory', '2025-12-20', 'not_prayed', '2025-12-29 12:54:26'),
(210, 9, 'العصر', 'obligatory', '2025-12-20', 'not_prayed', '2025-12-29 12:54:29'),
(211, 9, 'المغرب', 'obligatory', '2025-12-20', 'not_prayed', '2025-12-29 12:54:33'),
(212, 9, 'العشاء', 'obligatory', '2025-12-20', 'not_prayed', '2025-12-29 12:54:37'),
(213, 9, 'الفجر', 'obligatory', '2025-12-21', 'not_prayed', '2025-12-29 12:54:41'),
(214, 9, 'الظهر', 'obligatory', '2025-12-21', 'not_prayed', '2025-12-29 12:54:44'),
(215, 9, 'العصر', 'obligatory', '2025-12-21', 'not_prayed', '2025-12-29 12:54:46'),
(216, 9, 'المغرب', 'obligatory', '2025-12-21', 'not_prayed', '2025-12-29 12:54:50'),
(217, 9, 'العشاء', 'obligatory', '2025-12-21', 'not_prayed', '2025-12-29 12:54:52'),
(218, 9, 'الفجر', 'obligatory', '2025-12-22', 'not_prayed', '2025-12-29 12:55:02'),
(219, 9, 'الظهر', 'obligatory', '2025-12-22', 'not_prayed', '2025-12-29 12:55:05'),
(220, 9, 'العصر', 'obligatory', '2025-12-22', 'not_prayed', '2025-12-29 12:55:08'),
(221, 9, 'المغرب', 'obligatory', '2025-12-22', 'not_prayed', '2025-12-29 12:55:11'),
(222, 9, 'العشاء', 'obligatory', '2025-12-22', 'not_prayed', '2025-12-29 12:55:16'),
(223, 9, 'الفجر', 'obligatory', '2025-12-23', 'not_prayed', '2025-12-29 12:55:20'),
(224, 9, 'الظهر', 'obligatory', '2025-12-23', 'not_prayed', '2025-12-29 12:55:24'),
(225, 9, 'العصر', 'obligatory', '2025-12-23', 'not_prayed', '2025-12-29 12:55:27'),
(226, 9, 'المغرب', 'obligatory', '2025-12-23', 'not_prayed', '2025-12-29 12:55:31'),
(227, 9, 'العشاء', 'obligatory', '2025-12-23', 'not_prayed', '2025-12-29 12:55:35'),
(228, 9, 'الفجر', 'obligatory', '2025-12-01', 'not_prayed', '2025-12-29 13:05:51'),
(229, 9, 'الظهر', 'obligatory', '2025-12-01', 'not_prayed', '2025-12-29 13:05:53'),
(230, 9, 'العصر', 'obligatory', '2025-12-01', 'not_prayed', '2025-12-29 13:05:55'),
(231, 9, 'المغرب', 'obligatory', '2025-12-01', 'not_prayed', '2025-12-29 13:05:59'),
(232, 9, 'العشاء', 'obligatory', '2025-12-01', 'not_prayed', '2025-12-29 13:06:01'),
(233, 9, 'الفجر', 'obligatory', '2025-12-02', 'not_prayed', '2025-12-29 13:06:05'),
(234, 9, 'الظهر', 'obligatory', '2025-12-02', 'not_prayed', '2025-12-29 13:06:07'),
(235, 9, 'العصر', 'obligatory', '2025-12-02', 'not_prayed', '2025-12-29 13:06:09'),
(236, 9, 'المغرب', 'obligatory', '2025-12-02', 'not_prayed', '2025-12-29 13:06:12'),
(237, 9, 'العشاء', 'obligatory', '2025-12-02', 'not_prayed', '2025-12-29 13:06:16'),
(238, 9, 'الفجر', 'obligatory', '2025-12-03', 'not_prayed', '2025-12-29 13:06:20'),
(239, 9, 'الظهر', 'obligatory', '2025-12-03', 'not_prayed', '2025-12-29 13:06:22'),
(240, 9, 'العصر', 'obligatory', '2025-12-03', 'not_prayed', '2025-12-29 13:06:24'),
(241, 9, 'المغرب', 'obligatory', '2025-12-03', 'not_prayed', '2025-12-29 13:06:27'),
(242, 9, 'العشاء', 'obligatory', '2025-12-03', 'not_prayed', '2025-12-29 13:06:29'),
(243, 9, 'الفجر', 'obligatory', '2025-12-04', 'not_prayed', '2025-12-29 13:06:34'),
(244, 9, 'الظهر', 'obligatory', '2025-12-04', 'not_prayed', '2025-12-29 13:06:39'),
(245, 9, 'العصر', 'obligatory', '2025-12-04', 'not_prayed', '2025-12-29 13:06:41'),
(246, 9, 'العشاء', 'obligatory', '2025-12-04', 'not_prayed', '2025-12-29 13:06:45'),
(247, 9, 'المغرب', 'obligatory', '2025-12-04', 'not_prayed', '2025-12-29 13:06:48'),
(248, 9, 'الفجر', 'obligatory', '2025-12-05', 'not_prayed', '2025-12-29 13:06:52'),
(249, 9, 'الظهر', 'obligatory', '2025-12-05', 'not_prayed', '2025-12-29 13:06:55'),
(250, 9, 'العصر', 'obligatory', '2025-12-05', 'not_prayed', '2025-12-29 13:06:57'),
(251, 9, 'المغرب', 'obligatory', '2025-12-05', 'not_prayed', '2025-12-29 13:06:59'),
(252, 9, 'العشاء', 'obligatory', '2025-12-05', 'not_prayed', '2025-12-29 13:07:02'),
(253, 9, 'الفجر', 'obligatory', '2025-12-06', 'not_prayed', '2025-12-29 13:07:06'),
(254, 9, 'الظهر', 'obligatory', '2025-12-06', 'not_prayed', '2025-12-29 13:07:08'),
(255, 9, 'العصر', 'obligatory', '2025-12-06', 'not_prayed', '2025-12-29 13:07:11'),
(256, 9, 'المغرب', 'obligatory', '2025-12-06', 'not_prayed', '2025-12-29 13:07:15'),
(257, 9, 'العشاء', 'obligatory', '2025-12-06', 'not_prayed', '2025-12-29 13:07:18'),
(258, 9, 'الفجر', 'obligatory', '2025-12-07', 'not_prayed', '2025-12-29 13:07:23'),
(259, 9, 'الظهر', 'obligatory', '2025-12-07', 'not_prayed', '2025-12-29 13:07:28'),
(260, 9, 'العصر', 'obligatory', '2025-12-07', 'not_prayed', '2025-12-29 13:07:31'),
(261, 9, 'المغرب', 'obligatory', '2025-12-07', 'not_prayed', '2025-12-29 13:07:34'),
(262, 9, 'العشاء', 'obligatory', '2025-12-07', 'not_prayed', '2025-12-29 13:07:37'),
(263, 9, 'الفجر', 'obligatory', '2025-12-08', 'not_prayed', '2025-12-29 13:07:43'),
(264, 9, 'الظهر', 'obligatory', '2025-12-08', 'not_prayed', '2025-12-29 13:07:46'),
(265, 9, 'العصر', 'obligatory', '2025-12-08', 'not_prayed', '2025-12-29 13:07:48'),
(266, 9, 'المغرب', 'obligatory', '2025-12-08', 'not_prayed', '2025-12-29 13:07:51'),
(267, 9, 'العشاء', 'obligatory', '2025-12-08', 'not_prayed', '2025-12-29 13:07:55'),
(268, 9, 'الفجر', 'obligatory', '2025-12-09', 'not_prayed', '2025-12-29 13:08:00'),
(269, 9, 'الظهر', 'obligatory', '2025-12-09', 'not_prayed', '2025-12-29 13:08:03'),
(270, 9, 'العصر', 'obligatory', '2025-12-09', 'not_prayed', '2025-12-29 13:08:06'),
(271, 9, 'المغرب', 'obligatory', '2025-12-09', 'not_prayed', '2025-12-29 13:08:09'),
(272, 9, 'العشاء', 'obligatory', '2025-12-09', 'not_prayed', '2025-12-29 13:08:12'),
(273, 9, 'المغرب', 'obligatory', '2025-12-29', 'prayed_in_mosque', '2025-12-30 04:18:11'),
(274, 9, 'العصر', 'obligatory', '2025-12-29', 'not_prayed', '2025-12-30 04:18:14'),
(275, 9, 'العشاء', 'obligatory', '2025-12-29', 'not_prayed', '2025-12-30 04:18:17'),
(276, 9, 'الفجر', 'obligatory', '2025-12-30', 'prayed_alone', '2025-12-30 04:18:22'),
(277, 9, 'الظهر', 'obligatory', '2025-12-30', 'not_prayed', '2025-12-30 15:10:43'),
(278, 9, 'العصر', 'obligatory', '2025-12-30', 'not_prayed', '2025-12-30 15:10:46'),
(279, 9, 'المغرب', 'obligatory', '2025-12-30', 'not_prayed', '2025-12-30 15:10:49'),
(280, 9, 'الفجر', 'obligatory', '2026-01-01', 'not_prayed', '2026-01-03 07:25:42'),
(281, 9, 'الظهر', 'obligatory', '2026-01-01', 'not_prayed', '2026-01-03 07:25:44'),
(282, 9, 'العصر', 'obligatory', '2026-01-01', 'not_prayed', '2026-01-03 07:25:47'),
(283, 9, 'المغرب', 'obligatory', '2026-01-01', 'not_prayed', '2026-01-03 07:25:51'),
(284, 9, 'العشاء', 'obligatory', '2026-01-01', 'not_prayed', '2026-01-03 07:25:53'),
(285, 9, 'الفجر', 'obligatory', '2026-01-02', 'not_prayed', '2026-01-03 07:25:58'),
(286, 9, 'الظهر', 'obligatory', '2026-01-02', 'prayed_in_mosque', '2026-01-03 07:26:00'),
(287, 9, 'العصر', 'obligatory', '2026-01-02', 'not_prayed', '2026-01-03 07:26:02'),
(288, 9, 'المغرب', 'obligatory', '2026-01-02', 'not_prayed', '2026-01-03 07:26:05'),
(289, 9, 'العشاء', 'obligatory', '2026-01-02', 'not_prayed', '2026-01-03 07:26:08'),
(290, 9, 'الفجر', 'obligatory', '2026-01-03', 'not_prayed', '2026-01-03 07:26:24'),
(291, 9, 'الفجر', 'obligatory', '2025-12-31', 'not_prayed', '2026-01-03 07:27:57'),
(292, 9, 'العصر', 'obligatory', '2025-12-31', 'not_prayed', '2026-01-03 07:28:00'),
(293, 9, 'الظهر', 'obligatory', '2025-12-31', 'not_prayed', '2026-01-03 07:28:03'),
(294, 9, 'المغرب', 'obligatory', '2025-12-31', 'not_prayed', '2026-01-03 07:28:05'),
(295, 9, 'العشاء', 'obligatory', '2025-12-31', 'not_prayed', '2026-01-03 07:28:08'),
(296, 9, 'الظهر', 'obligatory', '2026-01-03', 'not_prayed', '2026-01-04 10:15:50'),
(297, 9, 'العصر', 'obligatory', '2026-01-03', 'prayed_in_mosque', '2026-01-04 10:15:55'),
(298, 9, 'المغرب', 'obligatory', '2026-01-03', 'not_prayed', '2026-01-04 10:15:58'),
(299, 9, 'العشاء', 'obligatory', '2026-01-03', 'not_prayed', '2026-01-04 10:16:03'),
(300, 9, 'الفجر', 'obligatory', '2026-01-04', 'delayed', '2026-01-04 10:16:10'),
(301, 9, 'الظهر', 'obligatory', '2026-01-04', 'not_prayed', '2026-01-04 10:16:13'),
(302, 9, 'العصر', 'obligatory', '2026-01-04', 'not_prayed', '2026-01-04 20:36:26'),
(303, 9, 'المغرب', 'obligatory', '2026-01-04', 'not_prayed', '2026-01-04 20:36:33'),
(304, 9, 'العشاء', 'obligatory', '2026-01-04', 'not_prayed', '2026-01-04 20:36:37'),
(305, 9, 'العشاء', 'obligatory', '2025-12-30', 'not_prayed', '2026-01-04 20:37:13'),
(306, 9, 'الفجر', 'obligatory', '2026-01-05', 'not_prayed', '2026-01-05 19:33:13'),
(307, 9, 'الظهر', 'obligatory', '2026-01-05', 'not_prayed', '2026-01-05 19:33:15'),
(308, 9, 'العصر', 'obligatory', '2026-01-05', 'not_prayed', '2026-01-05 19:33:18'),
(309, 9, 'المغرب', 'obligatory', '2026-01-05', 'not_prayed', '2026-01-05 19:33:21'),
(310, 9, 'العشاء', 'obligatory', '2026-01-05', 'not_prayed', '2026-01-05 19:33:25'),
(311, 9, 'الفجر', 'obligatory', '2026-01-06', 'not_prayed', '2026-01-09 13:57:21'),
(312, 9, 'الظهر', 'obligatory', '2026-01-06', 'not_prayed', '2026-01-09 13:57:23'),
(313, 9, 'العصر', 'obligatory', '2026-01-06', 'not_prayed', '2026-01-09 13:57:25'),
(314, 9, 'المغرب', 'obligatory', '2026-01-06', 'not_prayed', '2026-01-09 13:57:28'),
(315, 9, 'العشاء', 'obligatory', '2026-01-06', 'not_prayed', '2026-01-09 13:57:30'),
(316, 9, 'الفجر', 'obligatory', '2026-01-07', 'not_prayed', '2026-01-09 13:57:38'),
(317, 9, 'الظهر', 'obligatory', '2026-01-07', 'not_prayed', '2026-01-09 13:57:41'),
(318, 9, 'العصر', 'obligatory', '2026-01-07', 'not_prayed', '2026-01-09 13:57:43'),
(319, 9, 'المغرب', 'obligatory', '2026-01-07', 'not_prayed', '2026-01-09 13:57:47'),
(320, 9, 'العشاء', 'obligatory', '2026-01-07', 'not_prayed', '2026-01-09 13:57:49'),
(321, 9, 'الفجر', 'obligatory', '2026-01-08', 'not_prayed', '2026-01-09 13:57:53'),
(322, 9, 'الظهر', 'obligatory', '2026-01-08', 'not_prayed', '2026-01-09 13:57:55'),
(323, 9, 'العصر', 'obligatory', '2026-01-08', 'not_prayed', '2026-01-09 13:57:58'),
(324, 9, 'المغرب', 'obligatory', '2026-01-08', 'not_prayed', '2026-01-09 13:58:02'),
(325, 9, 'العشاء', 'obligatory', '2026-01-08', 'not_prayed', '2026-01-09 13:58:04'),
(326, 9, 'الفجر', 'obligatory', '2026-01-09', 'not_prayed', '2026-01-09 13:58:08'),
(327, 9, 'الظهر', 'obligatory', '2026-01-09', 'not_prayed', '2026-01-09 13:58:11'),
(328, 9, 'العصر', 'obligatory', '2026-01-09', 'not_prayed', '2026-01-09 13:58:13'),
(329, 9, 'المغرب', 'obligatory', '2026-01-09', 'not_prayed', '2026-01-09 13:58:16'),
(330, 9, 'العشاء', 'obligatory', '2026-01-09', 'not_prayed', '2026-01-09 13:58:18'),
(331, 9, 'الفجر', 'obligatory', '2026-01-10', 'not_prayed', '2026-01-21 01:35:46'),
(332, 9, 'الظهر', 'obligatory', '2026-01-10', 'not_prayed', '2026-01-21 01:35:50'),
(333, 9, 'العصر', 'obligatory', '2026-01-10', 'not_prayed', '2026-01-21 01:35:52'),
(334, 9, 'المغرب', 'obligatory', '2026-01-10', 'not_prayed', '2026-01-21 01:35:55'),
(335, 9, 'العشاء', 'obligatory', '2026-01-10', 'not_prayed', '2026-01-21 01:35:57'),
(336, 9, 'الفجر', 'obligatory', '2026-01-11', 'not_prayed', '2026-01-21 01:36:01'),
(337, 9, 'الظهر', 'obligatory', '2026-01-11', 'not_prayed', '2026-01-21 01:36:04'),
(338, 9, 'العصر', 'obligatory', '2026-01-11', 'not_prayed', '2026-01-21 01:36:07'),
(339, 9, 'المغرب', 'obligatory', '2026-01-11', 'not_prayed', '2026-01-21 01:36:09'),
(340, 9, 'العشاء', 'obligatory', '2026-01-11', 'not_prayed', '2026-01-21 01:36:12'),
(341, 9, 'الفجر', 'obligatory', '2026-01-12', 'not_prayed', '2026-01-21 01:36:15'),
(342, 9, 'الظهر', 'obligatory', '2026-01-12', 'not_prayed', '2026-01-21 01:36:19'),
(343, 9, 'العصر', 'obligatory', '2026-01-12', 'not_prayed', '2026-01-21 01:36:21'),
(344, 9, 'المغرب', 'obligatory', '2026-01-12', 'not_prayed', '2026-01-21 01:36:23'),
(345, 9, 'العشاء', 'obligatory', '2026-01-12', 'not_prayed', '2026-01-21 01:36:25'),
(346, 9, 'الفجر', 'obligatory', '2026-01-13', 'not_prayed', '2026-01-21 01:36:29'),
(347, 9, 'الظهر', 'obligatory', '2026-01-13', 'not_prayed', '2026-01-21 01:36:31'),
(348, 9, 'العصر', 'obligatory', '2026-01-13', 'not_prayed', '2026-01-21 01:36:34'),
(349, 9, 'المغرب', 'obligatory', '2026-01-13', 'not_prayed', '2026-01-21 01:36:37'),
(350, 9, 'العشاء', 'obligatory', '2026-01-13', 'not_prayed', '2026-01-21 01:36:39'),
(351, 9, 'الفجر', 'obligatory', '2026-01-14', 'not_prayed', '2026-01-21 01:36:43'),
(352, 9, 'الظهر', 'obligatory', '2026-01-14', 'not_prayed', '2026-01-21 01:36:46'),
(353, 9, 'العصر', 'obligatory', '2026-01-14', 'not_prayed', '2026-01-21 01:36:49'),
(354, 9, 'المغرب', 'obligatory', '2026-01-14', 'not_prayed', '2026-01-21 01:36:52'),
(355, 9, 'العشاء', 'obligatory', '2026-01-14', 'not_prayed', '2026-01-21 01:36:54'),
(356, 9, 'الفجر', 'obligatory', '2026-01-15', 'not_prayed', '2026-01-21 01:36:57'),
(357, 9, 'الظهر', 'obligatory', '2026-01-15', 'not_prayed', '2026-01-21 01:36:59'),
(358, 9, 'العصر', 'obligatory', '2026-01-15', 'not_prayed', '2026-01-21 01:37:02'),
(359, 9, 'المغرب', 'obligatory', '2026-01-15', 'not_prayed', '2026-01-21 01:37:07'),
(360, 9, 'العشاء', 'obligatory', '2026-01-15', 'not_prayed', '2026-01-21 01:37:10'),
(361, 9, 'الفجر', 'obligatory', '2026-01-16', 'not_prayed', '2026-01-21 01:37:14'),
(362, 9, 'الظهر', 'obligatory', '2026-01-16', 'not_prayed', '2026-01-21 01:37:17'),
(363, 9, 'العصر', 'obligatory', '2026-01-16', 'not_prayed', '2026-01-21 01:37:20'),
(364, 9, 'المغرب', 'obligatory', '2026-01-16', 'not_prayed', '2026-01-21 01:37:22'),
(365, 9, 'العشاء', 'obligatory', '2026-01-16', 'not_prayed', '2026-01-21 01:37:25'),
(366, 9, 'الفجر', 'obligatory', '2026-01-17', 'not_prayed', '2026-01-21 01:37:29'),
(367, 9, 'الظهر', 'obligatory', '2026-01-17', 'not_prayed', '2026-01-21 01:37:32'),
(368, 9, 'العصر', 'obligatory', '2026-01-17', 'not_prayed', '2026-01-21 01:37:34'),
(369, 9, 'المغرب', 'obligatory', '2026-01-17', 'not_prayed', '2026-01-21 01:37:36'),
(370, 9, 'العشاء', 'obligatory', '2026-01-17', 'not_prayed', '2026-01-21 01:37:38'),
(371, 9, 'الفجر', 'obligatory', '2026-01-18', 'not_prayed', '2026-01-21 01:37:42'),
(372, 9, 'الظهر', 'obligatory', '2026-01-18', 'not_prayed', '2026-01-21 01:37:45'),
(373, 9, 'المغرب', 'obligatory', '2026-01-18', 'not_prayed', '2026-01-21 01:37:47'),
(374, 9, 'العصر', 'obligatory', '2026-01-18', 'not_prayed', '2026-01-21 01:37:50'),
(375, 9, 'العشاء', 'obligatory', '2026-01-18', 'not_prayed', '2026-01-21 01:37:52'),
(376, 9, 'الفجر', 'obligatory', '2026-01-19', 'not_prayed', '2026-01-21 01:37:56'),
(377, 9, 'الظهر', 'obligatory', '2026-01-19', 'not_prayed', '2026-01-21 01:37:59'),
(378, 9, 'العصر', 'obligatory', '2026-01-19', 'not_prayed', '2026-01-21 01:38:01'),
(379, 9, 'المغرب', 'obligatory', '2026-01-19', 'not_prayed', '2026-01-21 01:38:03'),
(380, 9, 'العشاء', 'obligatory', '2026-01-19', 'not_prayed', '2026-01-21 01:38:05'),
(381, 9, 'الفجر', 'obligatory', '2026-01-20', 'prayed_alone', '2026-01-21 01:38:10'),
(382, 9, 'الظهر', 'obligatory', '2026-01-20', 'not_prayed', '2026-01-21 01:38:17'),
(383, 9, 'العصر', 'obligatory', '2026-01-20', 'prayed_in_mosque', '2026-01-21 01:38:19'),
(384, 9, 'المغرب', 'obligatory', '2026-01-20', 'not_prayed', '2026-01-21 01:38:22'),
(385, 9, 'العشاء', 'obligatory', '2026-01-20', 'not_prayed', '2026-01-21 01:38:24'),
(386, 9, 'الفجر', 'obligatory', '2026-01-21', 'not_prayed', '2026-01-21 01:41:41'),
(387, 9, 'الظهر', 'obligatory', '2026-01-21', 'not_prayed', '2026-01-25 00:00:16'),
(388, 9, 'العصر', 'obligatory', '2026-01-21', 'not_prayed', '2026-01-25 00:00:20'),
(389, 9, 'المغرب', 'obligatory', '2026-01-21', 'not_prayed', '2026-01-25 00:00:24'),
(390, 9, 'العشاء', 'obligatory', '2026-01-21', 'not_prayed', '2026-01-25 00:00:27'),
(391, 9, 'الفجر', 'obligatory', '2026-01-22', 'not_prayed', '2026-01-25 00:00:33'),
(392, 9, 'الظهر', 'obligatory', '2026-01-22', 'not_prayed', '2026-01-25 00:00:38'),
(393, 9, 'العصر', 'obligatory', '2026-01-22', 'not_prayed', '2026-01-25 00:00:41'),
(394, 9, 'المغرب', 'obligatory', '2026-01-22', 'not_prayed', '2026-01-25 00:00:43'),
(395, 9, 'العشاء', 'obligatory', '2026-01-22', 'not_prayed', '2026-01-25 00:00:46'),
(396, 9, 'الفجر', 'obligatory', '2026-01-23', 'prayed_in_mosque', '2026-01-25 00:01:00'),
(397, 9, 'الظهر', 'obligatory', '2026-01-23', 'not_prayed', '2026-01-25 00:01:03'),
(398, 9, 'العصر', 'obligatory', '2026-01-23', 'not_prayed', '2026-01-25 00:01:06'),
(399, 9, 'المغرب', 'obligatory', '2026-01-23', 'not_prayed', '2026-01-25 00:01:09'),
(400, 9, 'العشاء', 'obligatory', '2026-01-23', 'not_prayed', '2026-01-25 00:01:12'),
(401, 9, 'الفجر', 'obligatory', '2026-01-24', 'not_prayed', '2026-01-25 00:01:18'),
(402, 9, 'الظهر', 'obligatory', '2026-01-24', 'not_prayed', '2026-01-25 00:01:20'),
(403, 9, 'العصر', 'obligatory', '2026-01-24', 'not_prayed', '2026-01-25 00:01:23'),
(404, 9, 'المغرب', 'obligatory', '2026-01-24', 'prayed_in_mosque', '2026-01-25 00:01:29'),
(405, 9, 'العشاء', 'obligatory', '2026-01-24', 'not_prayed', '2026-01-25 00:01:32'),
(406, 9, 'الفجر', 'obligatory', '2026-01-25', 'not_prayed', '2026-02-04 07:19:24'),
(407, 9, 'الظهر', 'obligatory', '2026-01-25', 'not_prayed', '2026-02-04 07:19:27'),
(408, 9, 'العصر', 'obligatory', '2026-01-25', 'not_prayed', '2026-02-04 07:19:30'),
(409, 9, 'المغرب', 'obligatory', '2026-01-25', 'not_prayed', '2026-02-04 07:19:33'),
(410, 9, 'العشاء', 'obligatory', '2026-01-25', 'not_prayed', '2026-02-04 07:19:36'),
(411, 9, 'الفجر', 'obligatory', '2026-01-26', 'not_prayed', '2026-02-04 07:19:41'),
(412, 9, 'الظهر', 'obligatory', '2026-01-26', 'not_prayed', '2026-02-04 07:19:43'),
(413, 9, 'العصر', 'obligatory', '2026-01-26', 'not_prayed', '2026-02-04 07:19:45'),
(414, 9, 'المغرب', 'obligatory', '2026-01-26', 'not_prayed', '2026-02-04 07:19:48'),
(415, 9, 'العشاء', 'obligatory', '2026-01-26', 'not_prayed', '2026-02-04 07:19:51'),
(416, 9, 'الفجر', 'obligatory', '2026-01-27', 'not_prayed', '2026-02-04 07:20:07'),
(417, 9, 'الظهر', 'obligatory', '2026-01-27', 'not_prayed', '2026-02-04 07:20:10'),
(418, 9, 'العصر', 'obligatory', '2026-01-27', 'not_prayed', '2026-02-04 07:20:11'),
(419, 9, 'المغرب', 'obligatory', '2026-01-27', 'not_prayed', '2026-02-04 07:20:15'),
(420, 9, 'العشاء', 'obligatory', '2026-01-27', 'not_prayed', '2026-02-04 07:20:18'),
(421, 9, 'الفجر', 'obligatory', '2026-01-28', 'not_prayed', '2026-02-04 07:20:23'),
(422, 9, 'الظهر', 'obligatory', '2026-01-28', 'not_prayed', '2026-02-04 07:20:26'),
(423, 9, 'العصر', 'obligatory', '2026-01-28', 'not_prayed', '2026-02-04 07:20:28'),
(424, 9, 'المغرب', 'obligatory', '2026-01-28', 'not_prayed', '2026-02-04 07:20:31'),
(425, 9, 'العشاء', 'obligatory', '2026-01-28', 'not_prayed', '2026-02-04 07:20:51'),
(426, 9, 'الفجر', 'obligatory', '2026-01-29', 'not_prayed', '2026-02-04 07:21:12'),
(427, 9, 'الظهر', 'obligatory', '2026-01-29', 'not_prayed', '2026-02-04 07:21:20'),
(428, 9, 'العصر', 'obligatory', '2026-01-29', 'not_prayed', '2026-02-04 07:21:23'),
(429, 9, 'المغرب', 'obligatory', '2026-01-29', 'not_prayed', '2026-02-04 07:21:34'),
(430, 9, 'العشاء', 'obligatory', '2026-01-29', 'not_prayed', '2026-02-04 07:21:38'),
(431, 9, 'الفجر', 'obligatory', '2026-01-30', 'prayed_alone', '2026-02-04 07:21:41'),
(432, 9, 'الظهر', 'obligatory', '2026-01-30', 'prayed_in_mosque', '2026-02-04 07:21:45'),
(433, 9, 'العصر', 'obligatory', '2026-01-30', 'not_prayed', '2026-02-04 07:21:47'),
(434, 9, 'المغرب', 'obligatory', '2026-01-30', 'not_prayed', '2026-02-04 07:21:49'),
(435, 9, 'العشاء', 'obligatory', '2026-01-30', 'not_prayed', '2026-02-04 07:21:55'),
(436, 9, 'الفجر', 'obligatory', '2026-01-31', 'not_prayed', '2026-02-04 07:22:00'),
(437, 9, 'الظهر', 'obligatory', '2026-01-31', 'not_prayed', '2026-02-04 07:22:02'),
(438, 9, 'العصر', 'obligatory', '2026-01-31', 'not_prayed', '2026-02-04 07:22:07'),
(439, 9, 'المغرب', 'obligatory', '2026-01-31', 'not_prayed', '2026-02-04 07:22:10'),
(440, 9, 'العشاء', 'obligatory', '2026-01-31', 'not_prayed', '2026-02-04 07:22:14'),
(441, 9, 'الفجر', 'obligatory', '2026-02-01', 'not_prayed', '2026-02-04 07:22:20'),
(442, 9, 'الظهر', 'obligatory', '2026-02-01', 'not_prayed', '2026-02-04 07:22:24'),
(443, 9, 'العصر', 'obligatory', '2026-02-01', 'not_prayed', '2026-02-04 07:22:26'),
(444, 9, 'المغرب', 'obligatory', '2026-02-01', 'not_prayed', '2026-02-04 07:22:29'),
(445, 9, 'العشاء', 'obligatory', '2026-02-01', 'not_prayed', '2026-02-04 07:22:31'),
(446, 9, 'الفجر', 'obligatory', '2026-02-02', 'not_prayed', '2026-02-04 07:22:46'),
(447, 9, 'الظهر', 'obligatory', '2026-02-02', 'not_prayed', '2026-02-04 07:22:51'),
(448, 9, 'العصر', 'obligatory', '2026-02-02', 'not_prayed', '2026-02-04 07:22:53'),
(449, 9, 'المغرب', 'obligatory', '2026-02-02', 'not_prayed', '2026-02-04 07:22:56'),
(450, 9, 'العشاء', 'obligatory', '2026-02-02', 'not_prayed', '2026-02-04 07:23:01'),
(451, 9, 'الفجر', 'obligatory', '2026-02-03', 'prayed_alone', '2026-02-04 07:23:12'),
(452, 9, 'الظهر', 'obligatory', '2026-02-03', 'not_prayed', '2026-02-04 07:23:15'),
(453, 9, 'العصر', 'obligatory', '2026-02-03', 'not_prayed', '2026-02-04 07:23:18'),
(454, 9, 'المغرب', 'obligatory', '2026-02-03', 'not_prayed', '2026-02-04 07:23:21'),
(455, 9, 'العشاء', 'obligatory', '2026-02-03', 'not_prayed', '2026-02-04 07:23:26'),
(456, 9, 'الفجر', 'obligatory', '2026-02-04', 'not_prayed', '2026-02-04 07:23:36'),
(457, 9, 'الظهر', 'obligatory', '2026-02-04', 'not_prayed', '2026-02-06 21:32:52'),
(458, 9, 'العصر', 'obligatory', '2026-02-04', 'not_prayed', '2026-02-06 21:32:54'),
(459, 9, 'العشاء', 'obligatory', '2026-02-04', 'not_prayed', '2026-02-06 21:32:58'),
(460, 9, 'المغرب', 'obligatory', '2026-02-04', 'not_prayed', '2026-02-06 21:33:01'),
(461, 9, 'الفجر', 'obligatory', '2026-02-05', 'not_prayed', '2026-02-06 21:33:05'),
(462, 9, 'الظهر', 'obligatory', '2026-02-05', 'not_prayed', '2026-02-06 21:33:08'),
(463, 9, 'العصر', 'obligatory', '2026-02-05', 'not_prayed', '2026-02-06 21:33:10'),
(464, 9, 'المغرب', 'obligatory', '2026-02-05', 'not_prayed', '2026-02-06 21:33:13'),
(465, 9, 'العشاء', 'obligatory', '2026-02-05', 'not_prayed', '2026-02-06 21:33:15'),
(466, 9, 'الفجر', 'obligatory', '2026-02-06', 'not_prayed', '2026-02-06 21:33:20'),
(467, 9, 'الظهر', 'obligatory', '2026-02-06', 'not_prayed', '2026-02-06 21:33:23'),
(468, 9, 'العصر', 'obligatory', '2026-02-06', 'not_prayed', '2026-02-06 21:33:25'),
(469, 9, 'المغرب', 'obligatory', '2026-02-06', 'prayed_in_mosque', '2026-02-06 21:33:27'),
(470, 9, 'العشاء', 'obligatory', '2026-02-06', 'prayed_in_mosque', '2026-02-06 21:33:30'),
(471, 9, 'الفجر', 'obligatory', '2026-02-07', 'not_prayed', '2026-02-07 14:26:35'),
(472, 9, 'الظهر', 'obligatory', '2026-02-07', 'not_prayed', '2026-02-07 14:26:37'),
(473, 9, 'العصر', 'obligatory', '2026-02-07', 'prayed_in_mosque', '2026-02-07 14:26:40'),
(474, 9, 'المغرب', 'obligatory', '2026-02-07', 'prayed_in_mosque', '2026-02-07 21:03:15'),
(475, 9, 'العشاء', 'obligatory', '2026-02-07', 'not_prayed', '2026-02-07 21:03:21'),
(476, 9, 'الفجر', 'obligatory', '2026-02-08', 'prayed_alone', '2026-02-08 04:44:52'),
(477, 16, 'الظهر', 'obligatory', '2026-04-24', 'prayed_in_mosque', '2026-04-24 16:26:22'),
(478, 16, 'الفجر', 'obligatory', '2026-04-24', 'not_prayed', '2026-04-24 16:26:29'),
(479, 9, 'الفجر', 'obligatory', '2026-04-28', 'prayed_alone', '2026-04-28 07:44:49'),
(486, 22, 'الفجر', 'obligatory', '2026-04-22', 'prayed_in_mosque', '2026-04-28 16:09:04'),
(487, 22, 'الفجر', 'obligatory', '2026-04-29', 'prayed_in_mosque', '2026-04-29 02:06:43'),
(488, 9, 'الفجر', 'obligatory', '2026-04-29', 'prayed_alone', '2026-04-29 02:11:41'),
(489, 9, 'المغرب', 'obligatory', '2026-04-29', 'prayed_in_mosque', '2026-04-29 03:20:33'),
(490, 11, 'العصر', 'obligatory', '2026-04-29', 'delayed', '2026-04-29 16:23:02'),
(491, 11, 'الفجر', 'obligatory', '2026-04-29', 'not_prayed', '2026-04-29 16:23:19'),
(492, 11, 'الظهر', 'obligatory', '2026-04-29', 'not_prayed', '2026-04-29 16:23:45'),
(493, 11, 'المغرب', 'obligatory', '2026-04-29', 'prayed_alone', '2026-04-29 16:23:50'),
(494, 11, 'العشاء', 'obligatory', '2026-04-29', 'delayed', '2026-04-29 16:23:53'),
(495, 9, 'العشاء', 'obligatory', '2026-05-02', 'not_prayed', '2026-05-03 02:51:41'),
(496, 9, 'المغرب', 'obligatory', '2026-05-02', 'prayed_in_mosque', '2026-05-03 02:51:47'),
(497, 9, 'العصر', 'obligatory', '2026-05-02', 'prayed_alone', '2026-05-03 02:51:51'),
(507, 9, 'الفجر', 'obligatory', '2026-05-03', 'not_prayed', '2026-05-03 03:06:59'),
(508, 9, 'الظهر', 'obligatory', '2026-02-22', 'prayed_alone', '2026-05-03 03:08:30'),
(509, 9, 'المغرب', 'obligatory', '2026-05-03', 'not_prayed', '2026-05-03 03:17:23'),
(510, 9, 'الظهر', 'obligatory', '2026-05-03', 'not_prayed', '2026-05-03 03:17:51'),
(511, 9, 'العصر', 'obligatory', '2026-05-03', 'not_prayed', '2026-05-03 03:18:00'),
(512, 9, 'العشاء', 'obligatory', '2026-05-03', 'not_prayed', '2026-05-03 03:19:25'),
(513, 27, 'المغرب', 'obligatory', '2026-05-05', 'prayed_in_mosque', '2026-05-06 01:33:15'),
(514, 16, 'الفجر', 'obligatory', '2026-05-06', 'prayed_alone', '2026-05-06 01:58:19'),
(515, 28, 'قيام الليل', 'obligatory', '2026-05-06', 'optional', '2026-05-06 20:51:20'),
(516, 28, 'الفجر', 'obligatory', '2026-05-06', 'prayed_alone', '2026-05-06 20:55:55'),
(518, 28, 'العشاء', 'obligatory', '2026-05-07', 'prayed_in_mosque', '2026-05-06 21:01:54'),
(519, 28, 'الضحى', 'obligatory', '2026-05-07', 'prayed_alone', '2026-05-06 21:14:02'),
(520, 28, 'قيام الليل', 'obligatory', '2026-05-07', 'optional', '2026-05-06 21:16:06'),
(525, 28, 'الفجر', 'obligatory', '2026-05-07', 'prayed_in_mosque', '2026-05-06 21:24:22'),
(526, 9, 'الفجر', 'obligatory', '2026-05-09', 'prayed_in_mosque', '2026-05-09 06:52:11'),
(530, 29, 'الفجر', 'obligatory', '2026-05-15', 'prayed_in_mosque', '2026-05-15 14:26:45'),
(531, 9, 'الفجر', 'obligatory', '2026-05-15', 'prayed_in_mosque', '2026-05-15 14:30:34'),
(532, 9, 'الظهر', 'obligatory', '2026-05-15', 'prayed_in_mosque', '2026-05-15 14:30:38'),
(533, 9, 'العصر', 'obligatory', '2026-05-15', 'prayed_in_mosque', '2026-05-15 14:30:42'),
(534, 9, 'المغرب', 'obligatory', '2026-05-15', 'not_prayed', '2026-05-16 05:41:20'),
(535, 9, 'العشاء', 'obligatory', '2026-05-15', 'not_prayed', '2026-05-16 05:41:26'),
(536, 9, 'الفجر', 'obligatory', '2026-05-16', 'prayed_in_mosque', '2026-05-16 05:41:41'),
(538, 9, 'الفجر', 'obligatory', '2026-05-17', 'prayed_in_mosque', '2026-05-17 10:02:31'),
(539, 9, 'الظهر', 'obligatory', '2026-05-16', 'not_prayed', '2026-05-17 10:03:19'),
(540, 9, 'العصر', 'obligatory', '2026-05-16', 'not_prayed', '2026-05-17 10:03:24'),
(541, 9, 'المغرب', 'obligatory', '2026-05-16', 'prayed_in_mosque', '2026-05-17 10:03:29'),
(542, 9, 'العشاء', 'obligatory', '2026-05-16', 'not_prayed', '2026-05-17 10:03:34'),
(543, 9, 'الظهر', 'obligatory', '2026-05-17', 'prayed_in_mosque', '2026-05-17 10:21:44'),
(545, 9, 'العصر', 'obligatory', '2026-05-17', 'not_prayed', '2026-05-17 19:23:23'),
(546, 9, 'المغرب', 'obligatory', '2026-05-17', 'not_prayed', '2026-05-17 19:23:28'),
(547, 9, 'العشاء', 'obligatory', '2026-05-17', 'not_prayed', '2026-05-17 19:23:32'),
(548, 9, 'الفجر', 'obligatory', '2026-05-01', 'not_prayed', '2026-05-17 19:25:18'),
(549, 9, 'الظهر', 'obligatory', '2026-05-01', 'not_prayed', '2026-05-17 19:25:22'),
(550, 9, 'العصر', 'obligatory', '2026-05-01', 'not_prayed', '2026-05-17 19:25:26'),
(551, 9, 'المغرب', 'obligatory', '2026-05-01', 'not_prayed', '2026-05-17 19:25:30'),
(552, 9, 'العشاء', 'obligatory', '2026-05-01', 'not_prayed', '2026-05-17 19:25:35'),
(553, 9, 'الفجر', 'obligatory', '2026-05-02', 'not_prayed', '2026-05-17 19:25:42'),
(554, 9, 'الظهر', 'obligatory', '2026-05-02', 'not_prayed', '2026-05-17 19:25:45'),
(555, 9, 'الفجر', 'obligatory', '2026-05-04', 'not_prayed', '2026-05-17 19:25:55'),
(556, 9, 'الظهر', 'obligatory', '2026-05-04', 'not_prayed', '2026-05-17 19:25:58'),
(557, 9, 'العصر', 'obligatory', '2026-05-04', 'not_prayed', '2026-05-17 19:26:03'),
(558, 9, 'المغرب', 'obligatory', '2026-05-04', 'not_prayed', '2026-05-17 19:26:06'),
(559, 9, 'العشاء', 'obligatory', '2026-05-04', 'not_prayed', '2026-05-17 19:26:10'),
(560, 9, 'الفجر', 'obligatory', '2026-05-05', 'not_prayed', '2026-05-17 19:26:52'),
(561, 9, 'الظهر', 'obligatory', '2026-05-05', 'not_prayed', '2026-05-17 19:26:55'),
(562, 9, 'العصر', 'obligatory', '2026-05-05', 'not_prayed', '2026-05-17 19:26:59'),
(563, 9, 'المغرب', 'obligatory', '2026-05-05', 'not_prayed', '2026-05-17 19:27:05'),
(564, 9, 'العشاء', 'obligatory', '2026-05-05', 'not_prayed', '2026-05-17 19:27:09'),
(565, 9, 'الفجر', 'obligatory', '2026-05-06', 'not_prayed', '2026-05-17 19:27:18'),
(566, 9, 'الظهر', 'obligatory', '2026-05-06', 'not_prayed', '2026-05-17 19:27:21'),
(567, 9, 'العصر', 'obligatory', '2026-05-06', 'not_prayed', '2026-05-17 19:27:25'),
(568, 9, 'المغرب', 'obligatory', '2026-05-06', 'not_prayed', '2026-05-17 19:27:30'),
(569, 9, 'العشاء', 'obligatory', '2026-05-06', 'not_prayed', '2026-05-17 19:27:35'),
(570, 9, 'الفجر', 'obligatory', '2026-05-07', 'not_prayed', '2026-05-17 19:27:43'),
(571, 9, 'الظهر', 'obligatory', '2026-05-07', 'not_prayed', '2026-05-17 19:27:46'),
(572, 9, 'العصر', 'obligatory', '2026-05-07', 'not_prayed', '2026-05-17 19:27:50'),
(573, 9, 'المغرب', 'obligatory', '2026-05-07', 'not_prayed', '2026-05-17 19:27:54'),
(574, 9, 'العشاء', 'obligatory', '2026-05-07', 'not_prayed', '2026-05-17 19:27:58'),
(575, 9, 'الفجر', 'obligatory', '2026-05-08', 'not_prayed', '2026-05-17 19:28:05'),
(576, 9, 'الظهر', 'obligatory', '2026-05-08', 'not_prayed', '2026-05-17 19:28:11'),
(577, 9, 'العصر', 'obligatory', '2026-05-08', 'not_prayed', '2026-05-17 19:28:14'),
(578, 9, 'المغرب', 'obligatory', '2026-05-08', 'not_prayed', '2026-05-17 19:28:17'),
(579, 9, 'العشاء', 'obligatory', '2026-05-08', 'not_prayed', '2026-05-17 19:28:21'),
(580, 9, 'الظهر', 'obligatory', '2026-05-09', 'not_prayed', '2026-05-17 19:28:31'),
(581, 9, 'العصر', 'obligatory', '2026-05-09', 'not_prayed', '2026-05-17 19:28:36'),
(582, 9, 'المغرب', 'obligatory', '2026-05-09', 'not_prayed', '2026-05-17 19:28:40'),
(583, 9, 'العشاء', 'obligatory', '2026-05-09', 'not_prayed', '2026-05-17 19:28:44'),
(584, 9, 'الفجر', 'obligatory', '2026-05-10', 'not_prayed', '2026-05-17 19:28:53'),
(585, 9, 'الظهر', 'obligatory', '2026-05-10', 'not_prayed', '2026-05-17 19:28:56'),
(586, 9, 'العصر', 'obligatory', '2026-05-10', 'not_prayed', '2026-05-17 19:29:00'),
(587, 9, 'المغرب', 'obligatory', '2026-05-10', 'not_prayed', '2026-05-17 19:29:11'),
(588, 9, 'العشاء', 'obligatory', '2026-05-10', 'not_prayed', '2026-05-17 19:29:15'),
(589, 9, 'الفجر', 'obligatory', '2026-05-11', 'not_prayed', '2026-05-17 19:29:26'),
(590, 9, 'الظهر', 'obligatory', '2026-05-11', 'not_prayed', '2026-05-17 19:29:29'),
(591, 9, 'العصر', 'obligatory', '2026-05-11', 'not_prayed', '2026-05-17 19:29:32'),
(592, 9, 'المغرب', 'obligatory', '2026-05-11', 'not_prayed', '2026-05-17 19:29:36'),
(593, 9, 'العشاء', 'obligatory', '2026-05-11', 'not_prayed', '2026-05-17 19:29:39'),
(594, 9, 'الفجر', 'obligatory', '2026-05-12', 'not_prayed', '2026-05-17 19:30:03'),
(595, 9, 'الظهر', 'obligatory', '2026-05-12', 'not_prayed', '2026-05-17 19:30:07'),
(596, 9, 'العصر', 'obligatory', '2026-05-12', 'not_prayed', '2026-05-17 19:30:10'),
(597, 9, 'المغرب', 'obligatory', '2026-05-12', 'not_prayed', '2026-05-17 19:30:14'),
(598, 9, 'العشاء', 'obligatory', '2026-05-12', 'not_prayed', '2026-05-17 19:30:21'),
(599, 9, 'الفجر', 'obligatory', '2026-05-13', 'not_prayed', '2026-05-17 19:30:26'),
(600, 9, 'الظهر', 'obligatory', '2026-05-13', 'not_prayed', '2026-05-17 19:30:30'),
(601, 9, 'العصر', 'obligatory', '2026-05-13', 'not_prayed', '2026-05-17 19:30:34'),
(602, 9, 'المغرب', 'obligatory', '2026-05-13', 'not_prayed', '2026-05-17 19:30:38'),
(603, 9, 'العشاء', 'obligatory', '2026-05-13', 'not_prayed', '2026-05-17 19:30:41'),
(604, 9, 'الفجر', 'obligatory', '2026-05-14', 'not_prayed', '2026-05-17 19:30:50'),
(605, 9, 'الظهر', 'obligatory', '2026-05-14', 'not_prayed', '2026-05-17 19:30:53'),
(606, 9, 'العصر', 'obligatory', '2026-05-14', 'not_prayed', '2026-05-17 19:30:56'),
(607, 9, 'المغرب', 'obligatory', '2026-05-14', 'not_prayed', '2026-05-17 19:31:00'),
(608, 9, 'العشاء', 'obligatory', '2026-05-14', 'not_prayed', '2026-05-17 19:31:05'),
(609, 29, 'الفجر', 'obligatory', '2026-05-17', 'prayed_in_mosque', '2026-05-17 19:36:51'),
(610, 9, 'الفجر', 'obligatory', '2026-05-18', 'prayed_in_mosque', '2026-05-18 02:35:35'),
(611, 9, 'الفجر', 'obligatory', '2026-05-19', 'prayed_in_mosque', '2026-05-20 00:19:36'),
(612, 9, 'الظهر', 'obligatory', '2026-05-19', 'not_prayed', '2026-05-20 00:19:42'),
(613, 9, 'العصر', 'obligatory', '2026-05-19', 'prayed_in_mosque', '2026-05-20 00:19:47'),
(614, 9, 'المغرب', 'obligatory', '2026-05-19', 'not_prayed', '2026-05-20 00:19:52'),
(615, 9, 'العشاء', 'obligatory', '2026-05-19', 'not_prayed', '2026-05-20 00:19:55'),
(616, 9, 'الفجر', 'obligatory', '2026-05-20', 'prayed_in_mosque', '2026-05-20 06:43:06'),
(617, 9, 'الظهر', 'obligatory', '2026-05-18', 'not_prayed', '2026-05-20 06:47:07'),
(618, 9, 'العصر', 'obligatory', '2026-05-18', 'not_prayed', '2026-05-20 06:47:10'),
(619, 9, 'المغرب', 'obligatory', '2026-05-18', 'not_prayed', '2026-05-20 06:47:15'),
(620, 9, 'العشاء', 'obligatory', '2026-05-18', 'not_prayed', '2026-05-20 06:47:20'),
(621, 9, 'الفجر', 'obligatory', '2026-05-24', 'prayed_in_mosque', '2026-05-24 13:31:15'),
(622, 9, 'الظهر', 'obligatory', '2026-05-24', 'prayed_in_mosque', '2026-05-24 13:31:22'),
(623, 9, 'الفجر', 'obligatory', '2026-05-23', 'not_prayed', '2026-05-24 13:33:21'),
(624, 9, 'الظهر', 'obligatory', '2026-05-23', 'prayed_in_mosque', '2026-05-24 13:33:34'),
(625, 9, 'العصر', 'obligatory', '2026-05-23', 'not_prayed', '2026-05-24 13:33:39'),
(626, 9, 'المغرب', 'obligatory', '2026-05-23', 'not_prayed', '2026-05-24 13:33:44'),
(627, 9, 'العشاء', 'obligatory', '2026-05-23', 'not_prayed', '2026-05-24 13:33:51'),
(628, 9, 'الفجر', 'obligatory', '2026-05-22', 'not_prayed', '2026-05-24 13:34:03'),
(629, 9, 'الظهر', 'obligatory', '2026-05-22', 'not_prayed', '2026-05-24 13:34:10'),
(630, 9, 'العصر', 'obligatory', '2026-05-22', 'not_prayed', '2026-05-24 13:34:18'),
(631, 9, 'المغرب', 'obligatory', '2026-05-22', 'not_prayed', '2026-05-24 13:34:22'),
(632, 9, 'العشاء', 'obligatory', '2026-05-22', 'not_prayed', '2026-05-24 13:34:27'),
(633, 9, 'الفجر', 'obligatory', '2026-05-21', 'not_prayed', '2026-05-24 13:34:32'),
(634, 9, 'الظهر', 'obligatory', '2026-05-21', 'not_prayed', '2026-05-24 13:34:35'),
(635, 9, 'العصر', 'obligatory', '2026-05-21', 'not_prayed', '2026-05-24 13:34:38'),
(636, 9, 'المغرب', 'obligatory', '2026-05-21', 'not_prayed', '2026-05-24 13:34:41'),
(637, 9, 'العشاء', 'obligatory', '2026-05-21', 'not_prayed', '2026-05-24 13:34:46'),
(638, 9, 'الظهر', 'obligatory', '2026-05-20', 'not_prayed', '2026-05-24 13:34:56'),
(639, 9, 'العصر', 'obligatory', '2026-05-20', 'not_prayed', '2026-05-24 13:35:01'),
(640, 9, 'المغرب', 'obligatory', '2026-05-20', 'not_prayed', '2026-05-24 13:35:06'),
(641, 9, 'العشاء', 'obligatory', '2026-05-20', 'not_prayed', '2026-05-24 13:35:10'),
(642, 9, 'العصر', 'obligatory', '2026-05-24', 'prayed_in_mosque', '2026-05-24 14:15:49'),
(643, 9, 'الفجر', 'obligatory', '2026-05-25', 'not_prayed', '2026-05-25 18:51:31'),
(644, 9, 'الظهر', 'obligatory', '2026-05-25', 'prayed_in_mosque', '2026-05-25 18:51:36'),
(645, 9, 'العصر', 'obligatory', '2026-05-25', 'prayed_in_mosque', '2026-05-25 18:51:40'),
(646, 9, 'المغرب', 'obligatory', '2026-05-25', 'prayed_in_mosque', '2026-05-25 18:51:44'),
(647, 9, 'العشاء', 'obligatory', '2026-05-25', 'prayed_in_mosque', '2026-05-25 18:51:48'),
(648, 9, 'المغرب', 'obligatory', '2026-05-24', 'prayed_in_mosque', '2026-05-25 18:53:28'),
(649, 9, 'العشاء', 'obligatory', '2026-05-24', 'not_prayed', '2026-05-25 18:53:33'),
(650, 9, 'الفجر', 'obligatory', '2026-05-26', 'prayed_in_mosque', '2026-05-26 01:51:21'),
(651, 9, 'الظهر', 'obligatory', '2026-05-26', 'prayed_in_mosque', '2026-05-26 10:37:59'),
(652, 9, 'العصر', 'obligatory', '2026-05-26', 'prayed_in_mosque', '2026-05-26 14:05:55');

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
(7, 17, '01003504114', '', 'monthly', '59.00', 'uploads/receipts/1777363442_17773633930721671875190932980571.jpg', '{\"amount\":\"\",\"date\":\"\",\"reference\":\"\",\"sender\":\"تح فحت\",\"transactionType\":\"\"}', '2026-04-28 01:04:01', 'rejected', '2026-04-28 08:04:01'),
(8, 19, '01234567890', '', 'monthly', '59.00', 'uploads/receipts/1777364365_photo_5886630439181552809_y.jpg', '{\"amount\":\"2025\",\"date\":\"12:26 PM\",\"reference\":\"803150959201\",\"sender\":\"Mostafa Mohamed Taha\",\"transactionType\":\"ارسال نقود\"}', '2026-04-28 01:19:24', 'verified', '2026-04-28 08:19:24'),
(9, 20, '+201003504114', '5', 'monthly', '59.00', 'uploads/receipts/1777386013_17773859270889015309715906700375.jpg', '{\"amount\":\"011\",\"date\":\"\",\"reference\":\"\",\"sender\":\"ال تب\",\"transactionType\":\"\"}', '2026-04-28 07:20:13', 'verified', '2026-04-28 14:20:13'),
(10, 23, '01003504114', 'Jje', 'monthly', '59.00', 'uploads/receipts/1777392868_17773927797428344713632997202932.jpg', '{\"amount\":\"\",\"date\":\"\",\"reference\":\"\",\"sender\":\"اناا مشا مدت ةا\",\"transactionType\":\"\"}', '2026-04-28 09:14:28', 'verified', '2026-04-28 16:14:28');

-- --------------------------------------------------------

--
-- Table structure for table `telegram_link_tokens`
--

CREATE TABLE `telegram_link_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telegram_settings`
--

CREATE TABLE `telegram_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bot_token` varchar(255) NOT NULL,
  `bot_username` varchar(100) DEFAULT NULL,
  `bot_name` varchar(100) DEFAULT NULL,
  `chat_id` varchar(50) DEFAULT NULL,
  `notifications_enabled` tinyint(4) DEFAULT 1,
  `connected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telegram_webhooks`
--

CREATE TABLE `telegram_webhooks` (
  `id` int(11) NOT NULL,
  `bot_token` varchar(255) NOT NULL,
  `webhook_url` text NOT NULL,
  `setup_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `last_login` timestamp NULL DEFAULT NULL,
  `disable_device_tracking` tinyint(1) DEFAULT 0,
  `telegram_id` bigint(20) DEFAULT NULL,
  `telegram_username` varchar(255) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `google_id`, `password_hash`, `email`, `name`, `profile_picture`, `created_at`, `current_prayer_streak`, `max_prayer_streak`, `last_prayer_date`, `Pro`, `device_type`, `last_device_login`, `user_agent`, `last_login`, `disable_device_tracking`, `telegram_id`, `telegram_username`, `admin_id`) VALUES
(9, '$2y$10$72UBwFWa9G/I5Dqeu5UZm.Z5rfRAuxuuHJg8/7LUHGVc6txJmQd6y', NULL, 'miraatalmumin@gmail.com', 'Miraat al-Mumin', 'http://miraat-almumin.xo.je/uploads/profile_pictures/avatar_9_1779629883_fd5fa787.jpg', '2025-12-10 15:29:24', 0, 1, NULL, 1, 'windows', 'windows - Windows - Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-24 13:29:53', 0, NULL, NULL, 1),
(10, '$2y$10$AVl.aC8rsM.cWMabdKYNKeJRxnr65lY0B7NSo1BU0OcP4.2B217Ju', NULL, 'moaazmohamed4uh@gmail.com', 'Moaaz الحراق', 'http://miraat-almumin.xo.je/uploads/profile_pictures/user_10_1765381509.jpg', '2025-12-10 15:39:40', 0, 0, NULL, 1, NULL, NULL, NULL, '2025-12-14 20:14:02', 0, NULL, NULL, NULL),
(11, '$2y$10$.CVkJm0.FOxNi9quLnL6mOtQNpxULiWnB6btiXoh0TmoYbVJ9rs0K', NULL, 'moaazmohamed1jj@gmail.com', 'Moaaz الحراق 2', 'https://ui-avatars.com/api/?name=Moaaz+%D8%A7%D9%84%D8%AD%D8%B1%D8%A7%D9%82+2&background=059669&color=fff&size=256', '2025-12-10 15:48:57', 0, 0, NULL, 1, NULL, NULL, NULL, '2025-12-10 15:49:08', 0, NULL, NULL, NULL),
(12, '$2y$10$dqnSbXpt2haxCnGpEgNDAeoLZ3/SkwYZnV/ifxDRSJu7SnBIhbFnC', NULL, 'admin@gmail.com', 'admin', 'https://ui-avatars.com/api/?name=admin&background=059669&color=fff&size=256', '2025-12-10 18:19:56', 0, 0, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(13, '$2y$10$rbO8jCkNZ9qdey00pomxh.xuCmKSFbvWYLy2ZmGRU2fr6TX.EM2fC', NULL, 'fthyahmdmhmdahmd532@gmail.com', 'أحمد فتحي أحمد', 'https://ui-avatars.com/api/?name=%D8%A3%D8%AD%D9%85%D8%AF+%D9%81%D8%AA%D8%AD%D9%8A+%D8%A3%D8%AD%D9%85%D8%AF&background=059669&color=fff&size=256', '2025-12-10 18:21:44', 0, 0, NULL, 1, NULL, NULL, NULL, '2025-12-10 18:24:57', 0, NULL, NULL, NULL),
(14, '$2y$10$V5jevwa7YNfe8qEpp/BAzeMzg866SsAr77n0hQIIihUUg3re8iwEG', NULL, 'zezo0115434@gmail.com', 'زياد محمود سيد', 'https://ui-avatars.com/api/?name=%D8%B2%D9%8A%D8%A7%D8%AF+%D9%85%D8%AD%D9%85%D9%88%D8%AF+%D8%B3%D9%8A%D8%AF&background=059669&color=fff&size=256', '2025-12-10 20:04:30', 0, 0, NULL, 0, NULL, NULL, NULL, '2025-12-10 20:05:11', 0, NULL, NULL, NULL),
(15, '$2y$10$t0DjtIDVP2qO9UY6AiQHAeEWh4/cgdlKE9fzjztHhGTepVcE7kiX2', NULL, 'adiltarekzhran@gmail.com', 'adel tarek', 'https://ui-avatars.com/api/?name=adel+tarek&background=059669&color=fff&size=256', '2025-12-11 08:16:02', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(16, '$2y$10$nPvdZjHlqhSqT.5x8Tc9ZOc6m9rxmRs2et9gKGmWutRe07QVSj2Ti', NULL, 'poooga208@gmail.com', 'عبدالرحمن', 'http://miraat-almumin.xo.je/uploads/profile_pictures/user_16_1765536332.jpg', '2025-12-12 10:43:22', 0, 0, NULL, 1, NULL, NULL, NULL, '2026-02-06 21:57:25', 0, NULL, NULL, NULL),
(22, '$2y$10$Ctof28hp4F2bWtHmGZUOvufOf1pL7E1KIp5EgCL.x/tzir90kxrQ.', NULL, 'mostafamtaha@gmail.com', 'Mostafamtaha', 'https://ui-avatars.com/api/?name=Mostafamtaha&background=059669&color=fff&size=256', '2026-04-28 16:07:33', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(24, '112343287434448403765', NULL, 'hk303792mmmm@gmail.com', 'Mmm Mm', 'https://lh3.googleusercontent.com/a/ACg8ocJJA2_4La57lpo17PrrsseW814JZlZIttFLu98VwWjPzaNu-Q=s96-c', '2026-05-03 02:40:25', 0, 0, NULL, 0, 'windows', 'windows - Windows - Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-03 02:40:25', 0, NULL, NULL, NULL),
(25, '$2y$10$17PU6OCok3pHSMgDVSEfJufa4czlJIjJPVrUjyyW0dnotSj5KDJuG', NULL, 'test@example.com', 'Test00', 'https://ui-avatars.com/api/?name=Test00&background=059669&color=fff&size=256', '2026-05-03 03:44:27', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(26, '107681726258782612532', NULL, 'sahatalllm@gmail.com', 'Sahat Al_llm', 'https://lh3.googleusercontent.com/a/ACg8ocLua0dLyyBRKQ3lCqXBjm52mxRrPBZ1kniBT1WPVfq_Tn4LsQ=s96-c', '2026-05-03 04:37:10', 0, 0, NULL, 0, NULL, NULL, NULL, '2026-05-03 04:37:10', 0, NULL, NULL, NULL),
(27, '$2y$10$vtNc3C0VYvWN0FnXUfccTeG0WfvgxCpj/1kWdU3mXNzzoOACX/upi', NULL, 'adminteater@gmail.com', 'mostafatmtaha', 'https://ui-avatars.com/api/?name=administration&background=059669&color=fff&size=256', '2026-05-06 01:31:50', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(28, '118380663984086018501', NULL, 'mostafamta347@gmail.com', 'Mostafam Ta3', 'https://lh3.googleusercontent.com/a/ACg8ocKoPaBU55MVAUPWK8vAXRZL5l2zajBxEXOg3H-IujH4wFfVIA=s96-c', '2026-05-06 20:51:02', 0, 0, NULL, 0, NULL, NULL, NULL, '2026-05-06 20:51:02', 0, NULL, NULL, NULL),
(29, '112770162303863287119', NULL, 'mosafamtaha@gmail.com', 'Mostafam taha', 'https://lh3.googleusercontent.com/a/ACg8ocLsEwx5toYRnjKOgTcoE2kN6qGfo23jH2Bm9pn3-Aut1WCZB_g=s96-c', '2026-05-15 14:26:38', 0, 0, NULL, 0, NULL, NULL, NULL, '2026-05-15 14:26:38', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_activities`
--

CREATE TABLE `user_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_habits`
--

CREATE TABLE `user_habits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `habit_id` int(11) NOT NULL COMMENT 'مرجع لجدول habits',
  `custom_target` int(11) DEFAULT NULL COMMENT 'هدف مخصص يتجاوز القيمة الافتراضية',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_security_settings`
--

CREATE TABLE `user_security_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notify_new_device` tinyint(1) DEFAULT 1,
  `allow_multiple_devices` tinyint(1) DEFAULT 1,
  `session_timeout_hours` int(11) DEFAULT 24,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_security_settings`
--

INSERT INTO `user_security_settings` (`id`, `user_id`, `notify_new_device`, `allow_multiple_devices`, `session_timeout_hours`, `updated_at`) VALUES
(1, 9, 1, 1, 168, '2026-05-03 03:32:09'),
(7, 26, 1, 1, 24, '2026-05-03 04:37:10'),
(8, 28, 1, 1, 24, '2026-05-06 20:51:02'),
(9, 29, 1, 1, 24, '2026-05-15 14:26:38');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `device_type` enum('android','ios','web','desktop') DEFAULT 'web',
  `browser` varchar(100) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_current` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `device_name`, `device_type`, `browser`, `os`, `ip_address`, `location`, `last_activity`, `created_at`, `is_current`, `is_active`) VALUES
(10, 9, '3cd5d7f75db3ed4e627365eac14b21f8ea0101bad97033890ab312baca66e11e', 'Android - Chrome', 'android', 'Chrome', 'Android', '156.222.184.42', NULL, '2026-05-03 04:09:38', '2026-05-03 04:09:29', 0, 0),
(11, 9, '057570df486dea315638291d2b1e97982bf2bb10bc2ea8dd7f2e86f8dc9b579a', 'Windows - Edge', 'desktop', 'Edge', 'Windows', '156.222.184.42', NULL, '2026-05-03 04:14:15', '2026-05-03 04:09:37', 0, 0),
(12, 9, '8b2e5a4e7b99e91de1c97372c5835b4672386059859e2e68f6bddd9ce535dfbd', 'Android - Chrome', 'android', 'Chrome', 'Android', '196.136.176.11', NULL, '2026-05-03 04:10:57', '2026-05-03 04:10:31', 0, 0),
(13, 9, '2a4ac984216269b47509571e75b92ae137411deea92c56d8a304d0d942caa3e9', 'Android - Chrome', 'android', 'Chrome', 'Android', '156.222.184.42', NULL, '2026-05-03 04:13:07', '2026-05-03 04:13:04', 0, 0),
(14, 9, '501e66bfb2daecbc1c973a3cab47673af9625feabdbcc5496ead65aa8b6b02b3', 'Android - Chrome', 'android', 'Chrome', 'Android', '156.222.184.42', NULL, '2026-05-03 04:13:29', '2026-05-03 04:13:29', 0, 0),
(15, 9, '0ab4de8308f8ed6ac4a3eea4bdd30f601e055b62a5342cf0dd46b8c420721976', 'Android - Chrome', 'android', 'Chrome', 'Android', '156.222.184.42', NULL, '2026-05-03 04:36:53', '2026-05-03 04:14:12', 0, 0),
(16, 26, 'afcd86890a5267c877fb2e6400e98d521c9fa2d905c9efbe758a75dc333d41f6', 'Android - Chrome', 'android', 'Chrome', 'Android', '156.222.184.42', NULL, '2026-05-03 04:37:15', '2026-05-03 04:37:10', 1, 1),
(17, 9, '65658c0775e5d4b074515a1875a8983dda158245a72585275cd48f4b09e01884', 'Windows - Edge', 'desktop', 'Edge', 'Windows', '156.222.109.153', NULL, '2026-05-24 14:17:36', '2026-05-03 10:15:32', 1, 1),
(18, 28, '0065bf23459ba00fe2fcec4029b0e9bfa506e4be709586067ad682179ce1e121', 'Windows - Edge', 'desktop', 'Edge', 'Windows', '156.222.146.63', NULL, '2026-05-06 20:52:22', '2026-05-06 20:51:02', 1, 1),
(19, 9, '1a7e301fd1cbf87dc89b0700cb36b3780c6fabced07b9154b8234b3668b45e77', 'Android - Chrome', 'android', 'Chrome', 'Android', '156.222.74.249', NULL, '2026-05-15 14:26:23', '2026-05-10 09:43:32', 0, 0),
(20, 29, 'd0c1b3f824193777bac8306bd447022b1c3c806f3644aec93f7f858a6e46dc9c', 'Android - Chrome', 'android', 'Chrome', 'Android', '156.222.74.249', NULL, '2026-05-15 14:26:53', '2026-05-15 14:26:38', 1, 0),
(21, 9, '02e1fc978363b41ca18c6d11f69e611c32c5f2be62e44827cb9e0d8d5ec2a226', 'Android - Chrome', 'android', 'Chrome', 'Android', '156.222.213.177', NULL, '2026-05-19 17:35:43', '2026-05-17 19:37:11', 0, 0),
(22, 9, '5d2236c887d505982cbe426b77ddda9bbaae4c98e728fb07d033d44a5bc9f6a5', 'Android - Chrome', 'android', 'Chrome', 'Android', '156.222.213.177', NULL, '2026-05-20 06:48:55', '2026-05-20 06:48:55', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `block_all_groups` tinyint(1) DEFAULT 0 COMMENT '0 = معطلة, 1 = مفعلة',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `no_track_devices` tinyint(1) DEFAULT 0 COMMENT '0 = تعقب مفعل, 1 = تعقب معطل',
  `private_account` tinyint(1) DEFAULT 0,
  `two_factor` tinyint(1) DEFAULT 0,
  `show_activity` tinyint(1) DEFAULT 1,
  `theme_mode` enum('light','dark','auto') DEFAULT 'auto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `block_all_groups`, `created_at`, `updated_at`, `no_track_devices`, `private_account`, `two_factor`, `show_activity`, `theme_mode`) VALUES
(1, 2, 0, '2025-12-10 04:39:35', '2025-12-10 04:43:46', 0, 0, 0, 1, 'auto'),
(2, 9, 0, '2025-12-18 02:53:20', '2026-05-10 09:45:15', 0, 0, 0, 1, 'auto'),
(3, 20, 1, '2026-04-28 10:18:52', '2026-04-28 10:18:52', 0, 0, 0, 1, 'auto'),
(4, 11, 1, '2026-04-29 16:16:36', '2026-04-29 16:16:54', 0, 0, 0, 1, 'auto');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `additional_prayers`
--
ALTER TABLE `additional_prayers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `admin-stv`
--
ALTER TABLE `admin-stv`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_id` (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `device_activity_log`
--
ALTER TABLE `device_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `fasting_records`
--
ALTER TABLE `fasting_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`);

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
-- Indexes for table `habits`
--
ALTER TABLE `habits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_active` (`user_id`,`is_active`);

--
-- Indexes for table `habit_logs`
--
ALTER TABLE `habit_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_habit_date` (`user_id`,`habit_id`,`date`),
  ADD KEY `idx_user_date` (`user_id`,`date`);

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
  ADD KEY `date` (`date`),
  ADD KEY `idx_user_date` (`user_id`,`date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `pro_subscriptions`
--
ALTER TABLE `pro_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `telegram_link_tokens`
--
ALTER TABLE `telegram_link_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `telegram_settings`
--
ALTER TABLE `telegram_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Indexes for table `telegram_webhooks`
--
ALTER TABLE `telegram_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bot` (`bot_token`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `unique_telegram_id` (`telegram_id`),
  ADD KEY `idx_last_login` (`last_login`);

--
-- Indexes for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_habits`
--
ALTER TABLE `user_habits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_habit` (`user_id`,`habit_id`),
  ADD KEY `idx_user_active` (`user_id`,`is_active`);

--
-- Indexes for table `user_security_settings`
--
ALTER TABLE `user_security_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_token` (`session_token`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `additional_prayers`
--
ALTER TABLE `additional_prayers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admin-stv`
--
ALTER TABLE `admin-stv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `device_activity_log`
--
ALTER TABLE `device_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `fasting_records`
--
ALTER TABLE `fasting_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `group_settings`
--
ALTER TABLE `group_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `habits`
--
ALTER TABLE `habits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `habit_logs`
--
ALTER TABLE `habit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nawafil_records`
--
ALTER TABLE `nawafil_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `prayer_records`
--
ALTER TABLE `prayer_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=653;

--
-- AUTO_INCREMENT for table `pro_subscriptions`
--
ALTER TABLE `pro_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `telegram_link_tokens`
--
ALTER TABLE `telegram_link_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `telegram_settings`
--
ALTER TABLE `telegram_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `telegram_webhooks`
--
ALTER TABLE `telegram_webhooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_habits`
--
ALTER TABLE `user_habits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_security_settings`
--
ALTER TABLE `user_security_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `device_activity_log`
--
ALTER TABLE `device_activity_log`
  ADD CONSTRAINT `device_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `telegram_link_tokens`
--
ALTER TABLE `telegram_link_tokens`
  ADD CONSTRAINT `telegram_link_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_security_settings`
--
ALTER TABLE `user_security_settings`
  ADD CONSTRAINT `user_security_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
