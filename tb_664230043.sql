-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2025 at 06:27 AM
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
-- Database: `db6646_043`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_664230043`
--

CREATE TABLE `tb_664230043` (
  `user_id` int(5) NOT NULL COMMENT 'ลำดับ',
  `std_id` varchar(9) NOT NULL COMMENT 'รหัสนักศึกษา',
  `f_name` varchar(100) NOT NULL COMMENT 'ชื่อ',
  `L_name` varchar(100) NOT NULL COMMENT 'สกุล',
  `mail` varchar(100) NOT NULL COMMENT 'อีเมล',
  `tel` varchar(20) NOT NULL COMMENT 'เบอร์โทร',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'เวลาสร้าง',
  `information` varchar(100) NOT NULL COMMENT 'ข้อมูลนักศึกษา'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='ตารางรางข้อมูลนักศึกษา';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_664230043`
--
ALTER TABLE `tb_664230043`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_664230043`
--
ALTER TABLE `tb_664230043`
  MODIFY `user_id` int(5) NOT NULL AUTO_INCREMENT COMMENT 'ลำดับ';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
