-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 06:22 PM
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
-- Table structure for table `faculty_salary`
--

CREATE TABLE `faculty_salary` (
  `salary_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `current_salary` decimal(12,2) DEFAULT 0.00,
  `ytd_earnings` decimal(12,2) DEFAULT 0.00,
  `ytd_deductions` decimal(12,2) DEFAULT 0.00,
  `ytd_net` decimal(12,2) DEFAULT 0.00,
  `total_earnings` decimal(12,2) DEFAULT 0.00,
  `total_deductions` decimal(12,2) DEFAULT 0.00,
  `net_salary` decimal(12,2) DEFAULT 0.00,
  `basic_salary` decimal(12,2) DEFAULT 0.00,
  `hra` decimal(12,2) DEFAULT 0.00,
  `da` decimal(12,2) DEFAULT 0.00,
  `travel_allowance` decimal(12,2) DEFAULT 0.00,
  `research_grant` decimal(12,2) DEFAULT 0.00,
  `provident_fund` decimal(12,2) DEFAULT 0.00,
  `professional_tax` decimal(12,2) DEFAULT 0.00,
  `income_tax` decimal(12,2) DEFAULT 0.00,
  `insurance` decimal(12,2) DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `salary_month` varchar(20) DEFAULT NULL,
  `status` enum('Paid','Pending') DEFAULT 'Paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_salary`
--

INSERT INTO `faculty_salary` (`salary_id`, `faculty_id`, `name`, `email`, `department`, `current_salary`, `ytd_earnings`, `ytd_deductions`, `ytd_net`, `total_earnings`, `total_deductions`, `net_salary`, `basic_salary`, `hra`, `da`, `travel_allowance`, `research_grant`, `provident_fund`, `professional_tax`, `income_tax`, `insurance`, `payment_date`, `salary_month`, `status`) VALUES
(1, 101, 'Dr. Tanjila Islam', 'tanjila@skst.edu', 'Computer Science', 102600.00, 1179000.00, 137400.00, 1041600.00, 126000.00, 23400.00, 102600.00, 78000.00, 19500.00, 14820.00, 6480.00, 7200.00, 9360.00, 3000.00, 9840.00, 1200.00, '2023-10-05', 'September 2023', 'Paid'),
(2, 101, 'Dr. Tanjila Islam', 'tanjila@skst.edu', 'Computer Science', 102600.00, 1179000.00, 137400.00, 1041600.00, 126000.00, 23400.00, 102600.00, 78000.00, 19500.00, 14820.00, 6480.00, 7200.00, 9360.00, 3000.00, 9840.00, 1200.00, '2023-09-05', 'August 2023', 'Paid'),
(3, 101, 'Dr. Tanjila Islam', 'tanjila@skst.edu', 'Computer Science', 102600.00, 1179000.00, 137400.00, 1041600.00, 126000.00, 23400.00, 102600.00, 78000.00, 19500.00, 14820.00, 6480.00, 7200.00, 9360.00, 3000.00, 9840.00, 1200.00, '2023-08-05', 'July 2023', 'Paid');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `faculty_salary`
--
ALTER TABLE `faculty_salary`
  ADD PRIMARY KEY (`salary_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `faculty_salary`
--
ALTER TABLE `faculty_salary`
  MODIFY `salary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
