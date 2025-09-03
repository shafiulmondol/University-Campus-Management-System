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
-- Table structure for table `university_accounts`
--

CREATE TABLE `university_accounts` (
  `account_id` varchar(20) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL,
  `current_balance` decimal(15,2) NOT NULL,
  `budget_allocation` decimal(15,2) NOT NULL,
  `fiscal_year` varchar(9) NOT NULL,
  `account_status` varchar(20) NOT NULL CHECK (`account_status` in ('Active','Inactive','Suspended')),
  `created_date` date NOT NULL,
  `last_transaction_date` date DEFAULT NULL,
  `account_manager` varchar(100) NOT NULL,
  `contact_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `university_accounts`
--

INSERT INTO `university_accounts` (`account_id`, `account_name`, `account_type`, `department`, `current_balance`, `budget_allocation`, `fiscal_year`, `account_status`, `created_date`, `last_transaction_date`, `account_manager`, `contact_email`) VALUES
('ACC-2023-PY001', 'Payroll Account', 'Salary', 'Administration', 3200000.00, 40000000.00, '2023-2024', 'Active', '2023-07-01', '2023-10-12', 'Asfaq Rahman', 'payroll@university.edu.bd'),
('ACC-2023-RG015', 'Research Grants', 'Research Grant', 'Science Department', 4500000.00, 6000000.00, '2023-2024', 'Active', '2023-07-01', '2023-10-14', 'Ahmed Khan', 'research@university.edu.bd'),
('ACC-2023-SF001', 'Student Fees 2023', 'Student Fees', 'All Departments', 1254807.75, 1500000.00, '2023-2024', 'Active', '2023-07-01', '2023-10-15', 'Jane Smith', 'fees@university.edu.bd');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `university_accounts`
--
ALTER TABLE `university_accounts`
  ADD PRIMARY KEY (`account_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
