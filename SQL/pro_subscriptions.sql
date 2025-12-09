-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 03:38 AM
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
(1, 1, '01003504114', '', 'monthly', 59.00, 'uploads/receipts/1765158736_photo_5886630439181552802_y.jpg', '{\"amount\":213984157073,\"date\":\"\",\"reference\":\"213984157073\",\"sender\":\"\"}', '2025-12-08 03:52:16', 'verified', '2025-12-08 01:52:16'),
(2, 1, '01003504114', 'asdf', 'monthly', 59.00, 'uploads/receipts/1765159761_photo_5886630439181552802_y.jpg', '{\"amount\":\"2025\",\"date\":\"\",\"reference\":\"213984157073\",\"sender\":\"\"}', '2025-12-08 04:09:21', 'pending', '2025-12-08 02:09:21'),
(3, 1, '01003504114', 'asdf', 'yearly', 659.00, 'uploads/receipts/1765161203_photo_5886630439181552802_y.jpg', '{\"amount\":\"50.59\",\"date\":\"01:20 AM\",\"reference\":\"213984157073\",\"sender\":\"Mostafa Mohamed Taha\",\"transactionType\":\"ارسال نقود\"}', '2025-12-08 04:33:23', 'pending', '2025-12-08 02:33:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pro_subscriptions`
--
ALTER TABLE `pro_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pro_subscriptions`
--
ALTER TABLE `pro_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
