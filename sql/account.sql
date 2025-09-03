-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 10:44 PM
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
-- Table structure for table `university_accounts_expanded`
--

CREATE TABLE `university_accounts_expanded` (
  `id` int(11) NOT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `account_status` varchar(50) DEFAULT NULL,
  `scholarship1_name` varchar(150) DEFAULT NULL,
  `scholarship1_description` text DEFAULT NULL,
  `scholarship2_name` varchar(150) DEFAULT NULL,
  `scholarship2_description` text DEFAULT NULL,
  `scholarship3_name` varchar(150) DEFAULT NULL,
  `scholarship3_description` text DEFAULT NULL,
  `support1` varchar(150) DEFAULT NULL,
  `support2` varchar(150) DEFAULT NULL,
  `support3` varchar(150) DEFAULT NULL,
  `support4` varchar(150) DEFAULT NULL,
  `support5` varchar(150) DEFAULT NULL,
  `degree_program` varchar(100) DEFAULT NULL,
  `admission_requirements` text DEFAULT NULL,
  `admission_test_required` tinyint(1) DEFAULT 1,
  `program_duration` varchar(50) DEFAULT NULL,
  `credit_hours` int(11) DEFAULT NULL,
  `tuition_fee` decimal(12,2) DEFAULT NULL,
  `facility1` varchar(150) DEFAULT NULL,
  `facility2` varchar(150) DEFAULT NULL,
  `facility3` varchar(150) DEFAULT NULL,
  `facility4` varchar(150) DEFAULT NULL,
  `facility5` varchar(150) DEFAULT NULL,
  `facility6` varchar(150) DEFAULT NULL,
  `quicklink1_title` varchar(100) DEFAULT NULL,
  `quicklink1_url` varchar(255) DEFAULT NULL,
  `quicklink2_title` varchar(100) DEFAULT NULL,
  `quicklink2_url` varchar(255) DEFAULT NULL,
  `quicklink3_title` varchar(100) DEFAULT NULL,
  `quicklink3_url` varchar(255) DEFAULT NULL,
  `quicklink4_title` varchar(100) DEFAULT NULL,
  `quicklink4_url` varchar(255) DEFAULT NULL,
  `quicklink5_title` varchar(100) DEFAULT NULL,
  `quicklink5_url` varchar(255) DEFAULT NULL,
  `quicklink6_title` varchar(100) DEFAULT NULL,
  `quicklink6_url` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `university_accounts_expanded`
--

INSERT INTO `university_accounts_expanded` (`id`, `user_type`, `first_name`, `last_name`, `email`, `phone`, `password`, `account_status`, `scholarship1_name`, `scholarship1_description`, `scholarship2_name`, `scholarship2_description`, `scholarship3_name`, `scholarship3_description`, `support1`, `support2`, `support3`, `support4`, `support5`, `degree_program`, `admission_requirements`, `admission_test_required`, `program_duration`, `credit_hours`, `tuition_fee`, `facility1`, `facility2`, `facility3`, `facility4`, `facility5`, `facility6`, `quicklink1_title`, `quicklink1_url`, `quicklink2_title`, `quicklink2_url`, `quicklink3_title`, `quicklink3_url`, `quicklink4_title`, `quicklink4_url`, `quicklink5_title`, `quicklink5_url`, `quicklink6_title`, `quicklink6_url`, `last_updated`) VALUES
(1, 'Student', 'Taimoon', 'Islam', 'taimoon@example.com', '017XXXXXXXX', NULL, 'Active', 'Merit Scholarship', 'For top 10% students', NULL, NULL, NULL, NULL, 'Teaching Assistance', 'Orientation', 'Online Courses', 'Debating Forum', 'Grooming', 'B.Sc. Computer Science', 'Pass admission test', 1, '4 years', 120, 1500.00, 'Wifi', 'Library', 'Sports', 'Open Study Arena', 'English Learning Center', 'Technical Training', 'Apply Bank Account', 'account.php', 'Student Account', 'working.html', 'Faculty Account', 'working.html', 'Employee Account', 'working.html', 'Account Officer', 'account_officer.php', 'About', 'working.html', '2025-09-03 20:38:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `university_accounts_expanded`
--
ALTER TABLE `university_accounts_expanded`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `university_accounts_expanded`
--
ALTER TABLE `university_accounts_expanded`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
