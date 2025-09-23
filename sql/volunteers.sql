-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2025 at 05:32 AM
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
-- Table structure for table `volunteers`
--

CREATE TABLE `volunteers` (
  `sl` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `student_name` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `activity_name` varchar(150) NOT NULL,
  `activity_date` date NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `hours` int(11) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `stratus` varchar(10) NOT NULL DEFAULT '1',
  `password` varchar(255) NOT NULL DEFAULT 'volunteer123',
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `volunteers`
--

INSERT INTO `volunteers` (`sl`, `student_id`, `student_name`, `department`, `email`, `phone`, `activity_name`, `activity_date`, `role`, `hours`, `remarks`, `stratus`, `password`, `profile_picture`) VALUES
(1, 2, 'Rahim Uddin', 'Computer Science', 'rahim@example.com', '01710000001', 'Blood Donation Camp', '2025-02-15', 'Volunteer', 5, 'Donated blood and helped in registration', '1', 'volunteer123', 'uploads/volunteer_1.png'),
(2, 201, 'Karim Hasan', 'Electrical Engineering', 'karim@example.com', '01710000002', 'Tree Plantation Drive', '2025-03-05', 'Organizer', 8, 'Coordinated volunteers and managed logistics', '1', 'volunteer123', NULL),
(3, 3, 'Nusrat Jahan', 'Business Administration', 'nusrat@example.com', '01710000003', 'Campus Clean-up', '2025-04-10', 'Volunteer', 4, 'Participated in cleaning and waste management', '1', 'volunteer123', NULL),
(4, NULL, 'Taslima Akter', 'Civil Engineering', 'taslima@example.com', '01710000004', 'Fundraising Event', '2025-05-20', 'Leader', 10, 'Led a fundraising team for charity', '1', 'volunteer123', NULL),
(5, 15, 'Mahmudul Islam', 'Mechanical Engineering', 'mahmud@example.com', '01710000005', 'Cultural Festival', '2025-06-12', 'Volunteer', 6, 'Assisted in stage setup and coordination', '1', 'volunteer123', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `volunteers`
--
ALTER TABLE `volunteers`
  ADD PRIMARY KEY (`sl`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `volunteers`
--
ALTER TABLE `volunteers`
  MODIFY `sl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
