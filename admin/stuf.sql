-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2025 at 03:21 PM
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
-- Table structure for table `stuf`
--

CREATE TABLE `stuf` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `student_phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `last_exam` varchar(100) DEFAULT NULL,
  `board` varchar(100) DEFAULT NULL,
  `other_board` varchar(100) DEFAULT NULL,
  `year_of_passing` int(11) DEFAULT NULL,
  `institution_name` varchar(255) DEFAULT NULL,
  `result` decimal(5,2) DEFAULT NULL,
  `subject_group` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `present_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stuf`
--

INSERT INTO `stuf` (`id`, `first_name`, `last_name`, `father_name`, `mother_name`, `date_of_birth`, `guardian_phone`, `student_phone`, `email`, `password`, `last_exam`, `board`, `other_board`, `year_of_passing`, `institution_name`, `result`, `subject_group`, `gender`, `blood_group`, `nationality`, `religion`, `present_address`, `permanent_address`, `department`, `photo_path`, `signature_path`, `submission_date`) VALUES
(1, 'shafiul ', 'islam', 'Robert Doe', 'Mary Doe', '1990-05-15', '+8801712345678', '+8801812345678', '23303106@iubat.edu', 'shafiul', 'Bachelor of Science', 'Dhaka', NULL, 2012, 'University of Dhaka', 3.75, 'Science', 'Male', 'B+', 'Bangladeshi', 'Islam', '123 Main Road, Dhaka', '456 Village Street, Faridpur', 'Computer Science', '/uploads/photos/john_doe.jpg', '/uploads/signatures/john_doe_sig.png', '2025-07-31 10:04:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `stuf`
--
ALTER TABLE `stuf`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);
ALTER TABLE `stuf` ADD FULLTEXT KEY `first_name` (`first_name`,`last_name`,`father_name`,`mother_name`,`email`,`institution_name`,`present_address`,`permanent_address`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `stuf`
--
ALTER TABLE `stuf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
