-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 11:12 PM
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
-- Table structure for table `salary_dashboard`
--

CREATE TABLE `salary_dashboard` (
  `id` int(11) NOT NULL,
  `emp_id` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('Paid','Pending') DEFAULT 'Pending',
  `base_salary` decimal(12,2) NOT NULL DEFAULT 0.00,
  `bonus` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(12,2) GENERATED ALWAYS AS (`base_salary` + `bonus` - `deductions`) STORED,
  `last_pay_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_dashboard`
--

INSERT INTO `salary_dashboard` (`id`, `emp_id`, `first_name`, `last_name`, `role`, `department`, `email`, `phone`, `status`, `base_salary`, `bonus`, `deductions`, `last_pay_date`, `created_at`) VALUES
(1, 'FAC001', 'Ahmed', 'Rahman', 'Professor', 'Engineering', 'ahmed.rahman@skst.edu', '01710000001', 'Paid', 120000.00, 5000.00, 2000.00, '2025-09-01', '2025-09-03 21:09:34'),
(2, 'LIB002', 'Asif', 'Iqbal', 'Senior Librarian', 'Library', 'asif.iqbal@skst.edu', '01710000002', 'Paid', 60000.00, 2000.00, 500.00, '2025-09-01', '2025-09-03 21:09:34'),
(3, 'ADM002', 'Anika', 'Chowdhury', 'Administrator', 'Administration', 'anika.chowdhury@skst.edu', '01710000003', 'Pending', 80000.00, 3000.00, 1000.00, '2025-09-01', '2025-09-03 21:09:34'),
(4, 'SUP002', 'Karim', 'Uddin', 'Technical Support', 'Support Services', 'karim.uddin@skst.edu', '01710000004', 'Paid', 50000.00, 1000.00, 300.00, '2025-09-01', '2025-09-03 21:09:34'),
(5, 'FAC002', 'Fatima', 'Khan', 'Associate Professor', 'Medicine', 'fatima.khan@skst.edu', '01710000005', 'Paid', 110000.00, 4000.00, 1500.00, '2025-09-01', '2025-09-03 21:09:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `salary_dashboard`
--
ALTER TABLE `salary_dashboard`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emp_id` (`emp_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `salary_dashboard`
--
ALTER TABLE `salary_dashboard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
