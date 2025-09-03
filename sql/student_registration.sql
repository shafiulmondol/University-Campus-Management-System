-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 05:10 PM
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
-- Table structure for table `student_registration`
--

CREATE TABLE `student_registration` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `student_phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `last_exam` varchar(30) DEFAULT NULL,
  `board` varchar(50) DEFAULT NULL,
  `other_board` varchar(100) DEFAULT NULL,
  `year_of_passing` year(4) DEFAULT NULL,
  `institution_name` varchar(100) DEFAULT NULL,
  `result` varchar(20) DEFAULT NULL,
  `subject_group` varchar(30) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `blood_group` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `religion` varchar(30) DEFAULT NULL,
  `present_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `SSC` decimal(5,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_registration`
--

INSERT INTO `student_registration` (`id`, `first_name`, `last_name`, `father_name`, `mother_name`, `date_of_birth`, `guardian_phone`, `student_phone`, `email`, `last_exam`, `board`, `other_board`, `year_of_passing`, `institution_name`, `result`, `subject_group`, `gender`, `blood_group`, `nationality`, `religion`, `present_address`, `permanent_address`, `department`, `photo_path`, `signature_path`, `submission_date`, `SSC`) VALUES
(23303116, 'Taymoon Islam ', 'Estia', 'Md.Mazid', 'Mina Islam', '2003-12-14', '01718520912', '01530811222', 'Toimoon@gmail.com', 'HSC', 'Dhaka', NULL, '2022', 'Uttara high school', '5.00', 'science', 'female', 'A+', 'Bangladeshi', 'Islam', 'Uttara', 'Uttara', 'Cse', NULL, NULL, '2025-07-26 17:17:40', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `student_registration`
--
ALTER TABLE `student_registration`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `student_registration`
--
ALTER TABLE `student_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23303117;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
