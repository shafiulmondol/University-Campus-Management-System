-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 26, 2025 at 02:42 PM
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
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
