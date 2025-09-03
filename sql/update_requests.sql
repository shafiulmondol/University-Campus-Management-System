-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 09:05 PM
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
-- Database: `skst_university`
--

-- --------------------------------------------------------

--
-- Table structure for table `update_requests`
--

CREATE TABLE `update_requests` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) DEFAULT NULL,
  `admin_email` varchar(50) NOT NULL,
  `category` varchar(100) NOT NULL,
  `update_type` enum('password','email') NOT NULL,
  `current_value` varchar(255) NOT NULL,
  `new_value` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  `request_time` datetime NOT NULL,
  `action` tinyint(1) DEFAULT 0 COMMENT '0 = Pending, 1 = Approved, 2 = Rejected'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `update_requests`
--

INSERT INTO `update_requests` (`id`, `applicant_id`, `admin_email`, `category`, `update_type`, `current_value`, `new_value`, `comments`, `request_time`, `action`) VALUES
(1, 0, '23303106@iubat.edu', '', 'password', 'shafiul', 'shafiulmondol', 'i think that i any one hack my id', '2025-08-31 20:39:28', 0),
(2, 0, 'admin@university.edu', 'Student', 'password', 'shafiul', 'shafiulmondol', 'nothing', '2025-09-01 03:07:52', 0),
(3, 23303106, '23303105@ao.edu', 'Student', 'password', 'shafiul', 'shafiulmondol', 'nothing', '2025-09-04 00:49:06', 2),
(4, 23303106, '23303105@ao.edu', 'Student', 'password', 'shafiul', 'shafiulmondol', 'nothing', '2025-09-04 00:53:26', 1),
(5, 23303106, '23303105@ao.edu', 'Student', 'password', 'shafiul', 'shafiulmondol', 'nothing', '2025-09-04 01:01:42', 0),
(6, 23303106, '23303105@ao.edu', 'Student', 'password', 'shafiul', 'shafiulmondol', 'ghffh', '2025-09-04 01:02:46', 2),
(7, 23303106, '23303105@ao.edu', 'Student', 'password', 'shafiul', 'shafiulmondol', 'ghffh', '2025-09-04 01:03:31', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `update_requests`
--
ALTER TABLE `update_requests`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `update_requests`
--
ALTER TABLE `update_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
