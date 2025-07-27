-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2025 at 06:32 AM
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
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` int(6) UNSIGNED NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `program` varchar(50) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `level_study` varchar(20) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `guardian_name` varchar(100) NOT NULL,
  `guardian_number` varchar(15) NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `division` varchar(50) NOT NULL,
  `district` varchar(50) NOT NULL,
  `upzilla` varchar(50) NOT NULL,
  `post_office` varchar(50) NOT NULL,
  `post_code` varchar(20) NOT NULL,
  `village` varchar(50) NOT NULL,
  `birth_date` date NOT NULL,
  `hsc_institute` varchar(100) NOT NULL,
  `hsc_group` varchar(50) NOT NULL,
  `hsc_passing_year` varchar(4) NOT NULL,
  `hsc_result` varchar(10) NOT NULL,
  `ssc_institute` varchar(100) NOT NULL,
  `ssc_group` varchar(50) NOT NULL,
  `ssc_passing_year` varchar(4) NOT NULL,
  `ssc_result` varchar(10) NOT NULL,
  `parent_income` varchar(50) NOT NULL,
  `source_info` varchar(50) NOT NULL,
  `payment_type` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admissions`
--

INSERT INTO `admissions` (`id`, `fullname`, `email`, `mobile_number`, `program`, `semester`, `level_study`, `gender`, `guardian_name`, `guardian_number`, `nationality`, `division`, `district`, `upzilla`, `post_office`, `post_code`, `village`, `birth_date`, `hsc_institute`, `hsc_group`, `hsc_passing_year`, `hsc_result`, `ssc_institute`, `ssc_group`, `ssc_passing_year`, `ssc_result`, `parent_income`, `source_info`, `payment_type`, `password`, `reg_date`) VALUES
(1, 'Md. Kawsar Miah', 'mdkawsarmiah@gmail.com', '01884273156', 'BSc in Computer Science and Engineering', 'Fall-2025', 'Undergraduate', 'Male', 'Amzad Hossain', '01634086979', 'Bangladeshi', 'Dhaka', 'Tangail', 'Mirzapur', 'Tarafpur', '1940', 'Sit Mamudpur', '2003-11-27', 'Ideal College', 'Science', '2022', '4.25', 'Bashtoil M M Ali High School', 'Science', '2020', '4.06', 'Below 200,000', 'Friends/Family', 'Online Payment', '$2y$10$bGl6zCzyQuEMu5Q3uLGBk.wJqBC7VG4iWLsSjNHz9eiffAgeMFZzS', '2025-07-26 12:51:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
