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
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `emp_id` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `status` enum('active','onleave','inactive') DEFAULT 'active',
  `last_attendance_date` date DEFAULT NULL,
  `attendance_status` enum('Present','Absent','Leave') DEFAULT NULL,
  `base_salary` decimal(10,2) DEFAULT NULL,
  `bonus` decimal(10,2) DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) GENERATED ALWAYS AS (`base_salary` + `bonus` - `deductions`) STORED,
  `last_pay_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `emp_id`, `first_name`, `last_name`, `position`, `department`, `email`, `phone`, `status`, `last_attendance_date`, `attendance_status`, `base_salary`, `bonus`, `deductions`, `last_pay_date`, `created_at`) VALUES
(1, 'FAC001', 'Ahmed', 'Rahman', 'Professor', 'Engineering', 'ahmed.rahman@skst.edu', '01710000001', 'active', '2025-09-02', 'Present', 120000.00, 5000.00, 2000.00, '2025-09-01', '2025-09-03 14:41:01'),
(2, 'LIB002', 'Asif', 'Iqbal', 'Senior Librarian', 'Library', 'asif.iqbal@skst.edu', '01710000002', 'active', '2025-09-02', 'Present', 60000.00, 2000.00, 500.00, '2025-09-01', '2025-09-03 14:41:01'),
(3, 'ADM002', 'Anika', 'Chowdhury', 'Administrator', 'Administration', 'anika.chowdhury@skst.edu', '01710000003', 'onleave', '2025-09-02', 'Leave', 80000.00, 3000.00, 1000.00, '2025-09-01', '2025-09-03 14:41:01'),
(4, 'SUP002', 'Karim', 'Uddin', 'Technical Support', 'Support Services', 'karim.uddin@skst.edu', '01710000004', 'active', '2025-09-02', 'Present', 50000.00, 1000.00, 300.00, '2025-09-01', '2025-09-03 14:41:01'),
(5, 'FAC002', 'Fatima', 'Khan', 'Associate Professor', 'Medicine', 'fatima.khan@skst.edu', '01710000005', 'active', '2025-09-02', 'Present', 110000.00, 4000.00, 1500.00, '2025-09-01', '2025-09-03 14:41:01'),
(6, 'CS101', 'Dr. Tanjila', 'Islam', 'Professor', 'Computer Science', 'tanjila@skst.edu', '01710000006', 'active', NULL, NULL, 102600.00, 23400.00, 1200.00, NULL, '2025-09-03 14:41:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emp_id` (`emp_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
