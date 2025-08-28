-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2025 at 06:58 PM
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
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` varchar(20) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `budget_allocation` decimal(15,2) NOT NULL,
  `fiscal_year` varchar(9) NOT NULL,
  `account_status` varchar(20) NOT NULL DEFAULT 'Active' CHECK (`account_status` in ('Active','Inactive','Suspended')),
  `created_date` date NOT NULL DEFAULT curdate(),
  `last_transaction` date DEFAULT NULL,
  `account_manager` varchar(100) NOT NULL,
  `contact_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `account_name`, `account_type`, `department`, `current_balance`, `budget_allocation`, `fiscal_year`, `account_status`, `created_date`, `last_transaction`, `account_manager`, `contact_email`) VALUES
('A12B23T', 'Student Fees 2023', 'Student Fees 2023', 'All Departments', 1554807.75, 1500000.00, '2023-2024', 'Active', '2025-07-20', '2025-07-29', 'Tanjila Islam', 'account@skst.edu');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `department` (`department`),
  ADD KEY `account_type` (`account_type`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
