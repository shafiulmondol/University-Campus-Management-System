-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 10:12 PM
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
-- Table structure for table `account_officer_data`
--

CREATE TABLE `account_officer_data` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `account_id` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` varchar(50) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `current_balance` decimal(12,2) DEFAULT 0.00,
  `budget_allocation` decimal(12,2) DEFAULT 0.00,
  `fiscal_year` varchar(20) DEFAULT NULL,
  `account_status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `created_date` date DEFAULT NULL,
  `last_transaction` date DEFAULT NULL,
  `account_manager` varchar(100) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `transaction_date` date DEFAULT NULL,
  `transaction_description` varchar(255) DEFAULT NULL,
  `transaction_amount` decimal(12,2) DEFAULT NULL,
  `transaction_status` enum('Completed','Pending','Rejected') DEFAULT 'Completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_officer_data`
--

INSERT INTO `account_officer_data` (`id`, `student_id`, `student_name`, `account_id`, `account_name`, `account_type`, `department`, `current_balance`, `budget_allocation`, `fiscal_year`, `account_status`, `created_date`, `last_transaction`, `account_manager`, `contact_email`, `transaction_date`, `transaction_description`, `transaction_amount`, `transaction_status`) VALUES
(1, '23303116', 'Mim', 'ACC-2023-SF001', 'Student Fees 2023', 'Student Fees', 'All Departments', 1254807.75, 1500000.00, '2023-2024', 'Active', '2023-07-01', '2023-10-15', 'Jane Smith', 'fees@university.edu.bd', '2023-10-15', 'Batch 2023 Fees Payment', 852000.00, 'Completed');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_officer_data`
--
ALTER TABLE `account_officer_data`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_officer_data`
--
ALTER TABLE `account_officer_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
