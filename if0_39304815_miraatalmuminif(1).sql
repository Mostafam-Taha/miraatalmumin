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
(2, 'admin', 'admin', 'admin@example.com', 'password', 'admin', NULL, '2026-04-28 09:42:30', '2026-04-28 09:41:52');

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
(71, 9, NULL, 'settings_changed', NULL, '156.123.120.35', NULL, '2026-05-24 13:36:39');

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
(45, 26, 29, 'admin', '2026-05-17 19:36:23');

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
(131, 22, 650, 'الفجر', 'قبل', 2, '2026-05-26', '2026-05-26 01:51:21');

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
(652, 1, 'العصر', 'obligatory', '2026-05-26', 'prayed_in_mosque', '2026-05-26 14:05:55');

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
(29, '112770162303863287119', NULL, 'user@example.com', 'user', '#', '2026-05-15 14:26:38', 0, 0, NULL, 0, NULL, NULL, NULL, '2026-05-15 14:26:38', 0, NULL, NULL, NULL);

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
(22, 9, '#', 'Android - Chrome', 'android', 'Chrome', 'Android', '#', NULL, '2026-05-20 06:48:55', '2026-05-20 06:48:55', 0, 1);

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
