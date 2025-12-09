-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql207.infinityfree.com
-- Generation Time: Dec 06, 2025 at 08:28 PM
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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
