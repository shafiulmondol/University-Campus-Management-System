-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 05:18 PM
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
-- Table structure for table `student_financials`
--

CREATE TABLE `student_financials` (
  `id` int(11) NOT NULL,
  `student_code` varchar(20) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `registration_fee` decimal(12,2) DEFAULT NULL,
  `registration_status` enum('Paid','Pending') DEFAULT 'Pending',
  `registration_date` date DEFAULT NULL,
  `scholarship_type` varchar(50) DEFAULT NULL,
  `scholarship_discount` decimal(5,2) DEFAULT NULL,
  `scholarship_savings` decimal(12,2) DEFAULT NULL,
  `final_program_cost` decimal(12,2) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `tuition_amount` decimal(12,2) DEFAULT NULL,
  `tuition_payment_date` date DEFAULT NULL,
  `tuition_status` enum('Completed','Pending') DEFAULT 'Pending',
  `tuition_method` varchar(50) DEFAULT NULL,
  `current_semester` int(11) DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `credits_completed` int(11) DEFAULT NULL,
  `total_credits` int(11) DEFAULT NULL,
  `expected_graduation` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_financials`
--

INSERT INTO `student_financials` (`id`, `student_code`, `first_name`, `last_name`, `email`, `phone`, `department`, `registration_fee`, `registration_status`, `registration_date`, `scholarship_type`, `scholarship_discount`, `scholarship_savings`, `final_program_cost`, `semester`, `tuition_amount`, `tuition_payment_date`, `tuition_status`, `tuition_method`, `current_semester`, `cgpa`, `credits_completed`, `total_credits`, `expected_graduation`) VALUES
(1, 'ST-23303116', 'Toimoon', 'Islam', 'toimoon@skst.edu', '+8801712345678', 'CSE', 15000.00, 'Paid', '2023-01-10', '50% Scholarship', 50.00, 325000.00, 350000.00, 1, 29167.00, '2023-03-15', 'Completed', 'bKash', 4, 3.75, 45, 144, '2026-12-01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `student_financials`
--
ALTER TABLE `student_financials`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `student_financials`
--
ALTER TABLE `student_financials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
