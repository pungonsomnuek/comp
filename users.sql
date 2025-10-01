-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2025 at 06:43 PM
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
-- Database: `online`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin1', 'admin_pass', 'admin1@example.com', 'Admin One', 'admin', '2025-08-07 03:38:43'),
(2, 'member1', 'member_pass', 'member1@example.com', 'John Doe', 'member', '2025-08-07 03:38:43'),
(3, 'member2', 'member_pass', 'member2@example.com', 'Jane Smith', 'member', '2025-08-07 03:38:43'),
(12, 'Rattachai', '$2y$10$GuX.GQTxygKQCjtczUVjn.7ELvQKOXyXKXuvI06sicXWFh6UztSeq', '664230028@webmail.npru.ac.th', 'รัชตะชัย คล้ายแดง', 'member', '2025-08-14 14:15:04'),
(13, 'รัชตะชัย', '$2y$10$QGyEWrOkj5sRWXhDNAgbgOg/V3tYU9SEClwDJsu.fnXejnwUrb8na', 'bellzone@gmail.com', 'ตล้ายแดง', 'member', '2025-09-04 02:56:03'),
(14, 'เบล', '$2y$10$wrD/h/zVv4bn3PkPUhdgae65z21pY0PSj7rXzgJ2F5eV1Wn4.hIIy', 'bell@gmail.com', 'นะ', 'member', '2025-09-10 14:42:33'),
(15, 'Bell1', '$2y$10$x23SYUhvxLBg7rZ/sMoVa.F1L0gOOBLe4OTjS1gqOPeGaLvmw4ZO2', 'bellza@gmail.com', 'na', 'admin', '2025-09-10 15:53:03'),
(16, 'puntakorn', '$2y$10$v1zpUTy3N7Hg2l2CjDlf1.KjLtg.oLykO1GXixRIuH3yVC7I8w9kK', 'pun@gmail.com', 'data peach', 'admin', '2025-10-01 16:20:46'),
(17, 'Rawipa', '$2y$10$SwBXIof.Nw4rOdbO45z04OAUtEvdYIN8LYVqrhlIiIno086xZPBoS', 'rawi@gmail.com', 'data peach', 'admin', '2025-10-01 16:21:40'),
(18, 'eieiei', '$2y$10$UzTyEc7rZ2Q.bwtLEUkIMeu4yt7ADrVyExa.c94chAIWB7KSGYnvu', 'para@gmail.com', 'พารา เซตามอล', 'admin', '2025-10-01 16:23:14'),
(19, 'linlada', '$2y$10$IWIaUpu.w905APuVdgIkheSV0ynsOEsfge9fhhVqDeDeUUexrSYQy', 'tee@gmail.com', 'data 123', 'member', '2025-10-01 16:32:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
