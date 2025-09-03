-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2025 at 09:24 PM
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
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `key` int(5) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `registration_date` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime(6) DEFAULT current_timestamp(6),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `full_name`, `username`, `password`, `email`, `phone`, `key`, `profile_picture`, `registration_date`, `last_login`, `is_active`) VALUES
(1, 'Admin User', 'admin', 'admin123', 'admin@university.edu', '123-456-7890', 123, '', '2025-09-01 21:21:06', '2025-09-01 21:16:08.636532', 1),
(23303106, 'Md. Shafiul Islam', 'Admin-1', 'kawsar1234', '23303106@iubat.edu', '01701535780', 123, 'uploads/admin_pictures/1756740221_{E41600A5-6F2F-477B-A387-7B418585B689}.png', '2025-09-01 21:21:06', '2025-09-02 01:08:20.000000', 1);

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

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `alumni_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `graduation_year` int(11) DEFAULT NULL,
  `degree` varchar(100) DEFAULT NULL,
  `major` varchar(100) DEFAULT NULL,
  `current_job` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `registration_date` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alumni`
--

INSERT INTO `alumni` (`alumni_id`, `name`, `email`, `password`, `graduation_year`, `degree`, `major`, `current_job`, `company`, `phone`, `address`, `profile_picture`, `last_login`, `registration_date`, `is_active`) VALUES
(1, 'John Doe', 'john.doe@example.com', 'password123', 2015, 'Bachelor of Science', 'Computer Science', 'Software Engineer', 'Tech Corp', '1234567890', '123 Main St, Anytown', NULL, '2025-07-25 21:53:04', '2025-07-26 01:51:35', 1),
(2, 'Jane Smith', 'jane.smith@example.com', 'securepass', 2018, 'Master of Business', 'Business Administration', 'Marketing Manager', 'Global Inc', '9876543210', '456 Oak Ave, Somewhere', NULL, NULL, '2025-07-26 01:51:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `status` enum('Present','Absent') NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `publication_year` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `total_copies` int(11) NOT NULL DEFAULT 1,
  `available_copies` int(11) NOT NULL DEFAULT 1,
  `shelf_location` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author`, `isbn`, `publication_year`, `category`, `total_copies`, `available_copies`, `shelf_location`, `created_at`, `updated_at`) VALUES
(1, 'The Pragmatic Programmer', 'Andrew Hunt and David Thomas', '9780201616224', 1999, 'Programming', 5, 5, 'A1-07', '2025-08-07 04:02:48', '2025-08-07 04:02:48'),
(2, 'CSC 247', 'William Starlings ', '099779849', 1990, 'Computer Architecture ', 5, 5, 'B4', '2025-08-07 04:29:29', '2025-08-07 04:29:29'),
(3, 'csc 233', 'shafiul', '', 1000, 'computer science', 6, 6, 'c4', '2025-08-15 13:06:46', '2025-08-15 13:06:46');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_books`
--

CREATE TABLE `borrow_books` (
  `borrow_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('borrowed','returned') DEFAULT 'borrowed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_books`
--

INSERT INTO `borrow_books` (`borrow_id`, `book_id`, `user_id`, `borrow_date`, `due_date`, `return_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1000, '2025-08-21', '2025-09-11', '2025-09-25', 'borrowed', '2025-08-21 08:27:10', '2025-08-25 12:29:16');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `credit_hours` int(11) NOT NULL,
  `department` varchar(50) NOT NULL,
  `semester` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_id`, `course_code`, `course_name`, `credit_hours`, `department`, `semester`, `created_at`, `updated_at`) VALUES
(112, 'csc 112', 'dbms', 4, 'BCSE', 1, '2025-09-01 12:21:45', '2025-09-01 12:21:45'),
(123, 'csc 123', 'dbms', 5, 'BCSE', 1, '2025-09-01 18:59:59', '2025-09-01 18:59:59'),
(222, 'csc 222', 'computer archetecture', 3, 'BCSE', 2, '2025-09-01 18:17:02', '2025-09-01 18:17:02');

-- --------------------------------------------------------

--
-- Table structure for table `ebook`
--

CREATE TABLE `ebook` (
  `id` int(11) NOT NULL,
  `book_name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `publish_year` int(11) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ebook`
--

INSERT INTO `ebook` (`id`, `book_name`, `title`, `author`, `publish_year`, `link`) VALUES
(1, 'Don Quixote', 'Don Quixote', 'Miguel de Cervantes', 1605, 'https://www.pinkmonkey.com/dl/library1/book0530.pdf'),
(2, 'Pride and Prejudice', 'Pride and Prejudice', 'Jane Austen', 1813, 'https://icrrd.com/public/media/15-05-2021-083943Pride-Prejudice-Jane-Austen.pdf'),
(3, '1984', '1984', 'George Orwell', 1949, 'https://www.clarkchargers.org/ourpages/auto/2015/3/10/50720556/1984.pdf'),
(4, 'To Kill a Mockingbird', 'To Kill a Mockingbird', 'Harper Lee', 1960, 'https://www.raio.org/TKMFullText.pdf'),
(5, 'The Great Gatsby', 'The Great Gatsby', 'F. Scott Fitzgerald', 1925, 'https://ct02210097.schoolwires.net/site/handlers/filedownload.ashx?moduleinstanceid=26616&dataid=28467&FileName=The%20Great%20Gatsby.pdf'),
(6, 'Moby-Dick', 'Moby-Dick', 'Herman Melville', 1851, 'https://uberty.org/wp-content/uploads/2015/12/herman-melville-moby-dick.pdf'),
(7, 'War and Peace', 'War and Peace', 'Leo Tolstoy', 1869, 'https://antilogicalism.com/wp-content/uploads/2017/07/war-and-peace.pdf'),
(8, 'Crime and Punishment', 'Crime and Punishment', 'Fyodor Dostoevsky', 1866, 'https://www.planetpublish.com/wp-content/uploads/2011/11/Crime_and_Punishment_T.pdf'),
(9, 'The Catcher in the Rye', 'The Catcher in the Rye', 'J.D. Salinger', 1951, 'https://msweinfurter.weebly.com/uploads/5/4/3/7/5437316/catcher_in_the_rye_pdf.pdf'),
(10, 'The Hobbit', 'The Hobbit', 'J.R.R. Tolkien', 1937, 'https://rsd2-alert-durden-reading-room.weebly.com/uploads/6/7/1/6/6716949/the_hobbit_tolkien.pdf'),
(11, 'The Lord of the Rings', 'The Lord of the Rings', 'J.R.R. Tolkien', 1954, 'https://gosafir.com/mag/wp-content/uploads/2019/12/Tolkien-J.-The-lord-of-the-rings-HarperCollins-ebooks-2010.pdf'),
(12, 'Brave New World', 'Brave New World', 'Aldous Huxley', 1932, 'https://www.plato-philosophy.org/wp-content/uploads/2016/05/BraveNewWorld-1.pdf'),
(13, 'The Odyssey', 'The Odyssey', 'Homer', 1488, 'https://docdrop.org/download_annotation_doc/The-Odyssey-of-Homer---Lattimore-Richmond-lv1hj.pdf'),
(14, 'The Iliad', 'The Iliad', 'Homer', 1873, 'https://www.gutenberg.org/files/6130/old/6130-pdf.pdf'),
(15, 'Jane Eyre', 'Jane Eyre', 'Charlotte Brontë', 1847, 'https://publicdomainlibrary.org/en/ebooks/jane-eyre?gad_source=1&gad_campaignid=22457924354&gbraid=0AAAAA_TPsDgbIx6XUSJ1vCfPbeQCwS3UJ&gclid=CjwKCAjw2brFBhBOEiwAVJX5GMADHKeIEtiezfl1tkfISEzVktiQZBaryfAqoOAYnDJvvKQ4C-4LqxoCRDMQAvD_BwE'),
(16, 'Wuthering Heights', 'Wuthering Heights', 'Emily Brontë', 1847, 'https://www.ucm.es/data/cont/docs/119-2014-04-09-Wuthering%20Heights.pdf'),
(17, 'The Brothers Karamazov', 'The Brothers Karamazov', 'Fyodor Dostoevsky', 1880, 'https://www.gutenberg.org/files/28054/old/28054-pdf.pdf'),
(18, 'Anna Karenina', 'Anna Karenina', 'Leo Tolstoy', 1877, 'https://pwc.res.zabanshenas.ir/Anna_Karenina_Leo_Tolstoy_Z_Library_d5fb2c5f65.pdf'),
(19, 'Les Misérables', 'Les Misérables', 'Victor Hugo', 1862, 'https://giove.isti.cnr.it/demo/eread/Libri/sad/LesMiserables.pdf'),
(20, 'The Divine Comedy', 'The Divine Comedy', 'Dante Alighieri', 1320, 'https://wyomingcatholic.edu/wp-content/uploads/dante-01-inferno.pdf'),
(21, 'The Picture of Dorian Gray', 'The Picture of Dorian Gray', 'Oscar Wilde', 1890, 'https://dn790008.ca.archive.org/0/items/pictureofdoriang00wildiala/pictureofdoriang00wildiala.pdf'),
(22, 'The Stranger', 'The Stranger', 'Albert Camus', 1942, 'https://www.macobo.com/essays/epdf/CAMUS,%20Albert%20-%20The%20Stranger.pdf'),
(23, 'The Trial', 'The Trial', 'Franz Kafka', 1914, 'https://files.libcom.org/files/The%20Trial%20-%20Franz%20Kafka.pdf'),
(24, 'The Metamorphosis', 'The Metamorphosis', 'Franz Kafka', 1915, 'https://www.sas.upenn.edu/~cavitch/pdf-library/Kafka_Metamorphosis.pdf'),
(25, 'The Grapes of Wrath', 'The Grapes of Wrath', 'John Steinbeck', 1939, 'https://ca01001129.schoolwires.net/cms/lib/CA01001129/Centricity/Domain/270/grapes_of_wrath_john_steinbeck2.pdf'),
(26, 'Fahrenheit 451', 'Fahrenheit 451', 'Ray Bradbury', 1953, 'https://web.english.upenn.edu/~cavitch/pdf-library/Bradbury_Fahrenheit_451.pdf'),
(27, 'The Handmaid’s Tale', 'The Handmaid’s Tale', 'Margaret Atwood', 1985, 'https://ieas-szeged.hu/downtherabbithole/wp-content/uploads/2020/02/Margaret-Atwood_-The-Handmaids-Tale-1.pdf'),
(28, 'The Road', 'The Road', 'Cormac McCarthy', 2006, 'https://mrsfieldstchs.weebly.com/uploads/3/7/7/1/37719247/the_road_-_text.pdf'),
(29, 'The Hunger Games', 'The Hunger Games', 'Suzanne Collins', 2008, 'https://www.pinkmonkey.com/dl/library1/book0530.pdf'),
(30, 'Harry Potter and the Sorcerer’s Stone', 'Harry Potter and the Sorcerer’s Stone', 'J.K. Rowling', 1997, 'https://pwc.res.zabanshenas.ir/Harry_Potter_and_the_Sorcerer_s_Stone_www_libpdf_blog_ir_aba92c0a66.pdf'),
(31, 'The Fault in Our Stars', 'The Fault in Our Stars', 'John Green', 2012, 'https://spensabayalibrary.wordpress.com/wp-content/uploads/2016/04/the-fault-in-our-stars.pdf'),
(32, 'The Girl on the Train', 'The Girl on the Train', 'Paula Hawkins', 2015, 'https://everything830.wordpress.com/wp-content/uploads/2016/08/the_girl_on_the_train_-_paula_hawkins.pdf'),
(33, 'Gone Girl', 'Gone Girl', 'Gillian Flynn', 2012, 'https://icrrd.com/public/media/15-05-2021-082725Gone-Girl-Gillian-Flynn.pdf'),
(34, 'The Alchemist', 'The Alchemist', 'Paulo Coelho', 1988, 'https://icrrd.com/public/media/15-05-2021-084550The-Alchemist-Paulo-Coelho.pdf'),
(35, 'The Little Prince', 'The Little Prince', 'Antoine de Saint-Exupéry', 1943, 'https://blogs.ubc.ca/edcp508/files/2016/02/TheLittlePrince.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `room_number` varchar(50) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notice`
--

CREATE TABLE `notice` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `section` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `author` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `viewed` tinyint(1) DEFAULT 0 COMMENT '0=unread, 1=read'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice`
--

INSERT INTO `notice` (`id`, `title`, `section`, `content`, `author`, `created_at`, `viewed`) VALUES
(23303106, 'off day', '', 'for my weekness today is off', 'shafiul', '2025-07-30 17:06:21', 0),
(23303105, 'new', 'cse', 'test', 'kawsar', '2025-07-30 17:45:02', 0),
(23303106, 'peragraph', 'Bcse', 'A paragraph is a series of sentences that are organized and coherent, and are all related to a single topic.', 'Md shafiul Islam', '2025-07-31 07:49:23', 0),
(23303106, 'library notice check', 'Library', 'library notice section works successfully', 'Md Shafiul Islam', '2025-08-06 11:03:52', 0),
(0, '234444', 'Alumni', 'inseert alumni data notice', 'shafiul', '2025-08-06 16:56:33', 0),
(0, '23444455', 'Alumni', 'inseert alumni data notice', 'shafiul', '2025-08-06 16:57:00', 0),
(23303106, 'for stuf checking', 'stuf', 'this is correct successfully', 'shafiul', '2025-08-16 10:47:08', 0),
(23303106, 'for stuf checking', 'stuf', 'this is correct successfully', 'shafiul', '2025-08-16 10:48:16', 1),
(23303106, 'sdftgsdff', 'staf', 'sdfgsd 2nd', 'shafiul', '2025-08-16 11:01:44', 0),
(23303106, 'sdftgsdff', 'Staff', 'sdfgsd 2nd', 'shafiul', '2025-08-16 11:10:07', 1),
(23303105, 'sdfvads', 'Staff', 'dfgj fghikyu rftyjughhj', 'ssssssssss', '2025-08-16 11:50:53', 1),
(23303106, 'sdfgjkhjksd ', 'Staff', 'jkhkjhsjkdf ', 'shafiul ', '2025-08-26 12:57:53', 1),
(23303105, 'fgfgfdf', 'Staff', 'gjhuhfyh', 'shafiul', '2025-08-26 12:59:03', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 12:59:59', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 13:07:38', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 13:07:50', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 13:08:01', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 13:08:14', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 13:08:25', 1),
(23303106, 'sdfgsdf', 'Staff', 'sdfgsd', 'sdfg', '2025-08-26 14:40:26', 1),
(23303106, 'sdfgsdf', 'Staf', 'sdfgsd', 'sdfg', '2025-08-26 14:40:44', 0),
(23303106, 'off day', '', 'for my weekness today is off', 'shafiul', '2025-07-30 17:06:21', 0),
(23303105, 'new', 'cse', 'test', 'kawsar', '2025-07-30 17:45:02', 0),
(23303106, 'peragraph', 'Bcse', 'A paragraph is a series of sentences that are organized and coherent, and are all related to a single topic.', 'Md shafiul Islam', '2025-07-31 07:49:23', 0),
(23303106, 'library notice check', 'Library', 'library notice section works successfully', 'Md Shafiul Islam', '2025-08-06 11:03:52', 0),
(0, '234444', 'Alumni', 'inseert alumni data notice', 'shafiul', '2025-08-06 16:56:33', 0),
(0, '23444455', 'Alumni', 'inseert alumni data notice', 'shafiul', '2025-08-06 16:57:00', 0),
(23303106, 'for stuf checking', 'stuf', 'this is correct successfully', 'shafiul', '2025-08-16 10:47:08', 0),
(23303106, 'for stuf checking', 'stuf', 'this is correct successfully', 'shafiul', '2025-08-16 10:48:16', 1),
(23303106, 'sdftgsdff', 'staf', 'sdfgsd 2nd', 'shafiul', '2025-08-16 11:01:44', 0),
(23303106, 'sdftgsdff', 'Staff', 'sdfgsd 2nd', 'shafiul', '2025-08-16 11:10:07', 1),
(23303105, 'sdfvads', 'Staff', 'dfgj fghikyu rftyjughhj', 'ssssssssss', '2025-08-16 11:50:53', 1),
(23303106, 'sdfgjkhjksd ', 'Staff', 'jkhkjhsjkdf ', 'shafiul ', '2025-08-26 12:57:53', 1),
(23303105, 'fgfgfdf', 'Staff', 'gjhuhfyh', 'shafiul', '2025-08-26 12:59:03', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 12:59:59', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 13:07:38', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 13:07:50', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 13:08:01', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 13:08:14', 1),
(23303105, 'ghdfhh', 'Staff', 'hgff', 'hfg', '2025-08-26 13:08:25', 1),
(23303106, 'sdfgsdf', 'Staff', 'sdfgsd', 'sdfg', '2025-08-26 14:40:26', 1),
(23303106, 'sdfgsdf', 'Staf', 'sdfgsd', 'sdfg', '2025-08-26 14:40:44', 0);

-- --------------------------------------------------------

--
-- Table structure for table `scholarship_application`
--

CREATE TABLE `scholarship_application` (
  `id` int(11) NOT NULL,
  `application_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department` enum('BBA','BSCE','BSAg','BSME','BATHM','BSN','BCSE','BSEEE','BA Econ','BA Eng') NOT NULL,
  `semester` int(11) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `current_semester_sgpa` decimal(3,2) NOT NULL,
  `cgpa` decimal(3,2) NOT NULL,
  `previous_semester_cgpa` decimal(3,2) NOT NULL,
  `scholarship_percentage` decimal(5,2) NOT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `password` varchar(200) NOT NULL,
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
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_registration`
--

INSERT INTO `student_registration` (`id`, `first_name`, `last_name`, `father_name`, `mother_name`, `date_of_birth`, `guardian_phone`, `student_phone`, `email`, `password`, `last_exam`, `board`, `other_board`, `year_of_passing`, `institution_name`, `result`, `subject_group`, `gender`, `blood_group`, `nationality`, `religion`, `present_address`, `permanent_address`, `department`, `photo_path`, `signature_path`, `submission_date`) VALUES
(23303105, 'Md. kawsar', 'Miah', 'sdfg', 'sdfg', '2006-08-02', '01701535780', '01701535780', 'mdsdfs@gmail.com', '1234', 'HSC', 'Dinajpur', NULL, '2022', 'ccr', '5.00', 'science', 'male', 'B+', 'Bangladeshi', 'Islam', 'sdfgsdfgsd', 'sdfgsdfgsd', NULL, NULL, NULL, '2025-08-15 13:09:51'),
(23303106, 'shafiul islam', 'Estia', 'Md.Mazid', 'Mina Islam', '2003-12-14', '01718520912', '01530811222', 'shafiul@gmail.com', '11', 'HSC', 'Dhaka', NULL, '2022', 'Uttara high school', '5.00', 'science', 'female', 'A+', 'Bangladeshi', 'Islam', 'Uttara', 'Uttara', 'Cse', NULL, NULL, '2025-07-26 17:17:40'),
(23303137, 'sumaiya', 'haq', 'father', 'mother', '2003-09-17', '+8801712345678', '01701535780', '23303137@iubat.edu', 'sumaiya', 'HSC', 'Dhaka', NULL, '2021', 'dhaka college', '3.75', 'science', 'female', 'O+', 'Bangladeshi', 'Islam', 'sdfg sdfg sdfg sdfg', 'sfg sdfgh fghj fghj', 'BCSE', NULL, NULL, '2025-08-29 04:51:27');

-- --------------------------------------------------------

--
-- Table structure for table `student_result`
--

CREATE TABLE `student_result` (
  `st_id` int(20) NOT NULL,
  `semister` int(100) NOT NULL,
  `course` varchar(50) NOT NULL,
  `grade` varchar(20) DEFAULT NULL,
  `cgpa` float DEFAULT NULL,
  `sgpa` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_result`
--

INSERT INTO `student_result` (`st_id`, `semister`, `course`, `grade`, `cgpa`, `sgpa`) VALUES
(23303106, 1, 'csc 112', '4', 3.2, 3.98),
(23303106, 2, 'csc 112', '3.7', 3.2, 3.98);

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
  `stuff_phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `position` varchar(200) NOT NULL,
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

INSERT INTO `stuf` (`id`, `first_name`, `last_name`, `father_name`, `mother_name`, `date_of_birth`, `guardian_phone`, `stuff_phone`, `email`, `password`, `position`, `last_exam`, `board`, `other_board`, `year_of_passing`, `institution_name`, `result`, `subject_group`, `gender`, `blood_group`, `nationality`, `religion`, `present_address`, `permanent_address`, `department`, `photo_path`, `signature_path`, `submission_date`) VALUES
(1, 'shafiul ', 'islam', 'Robert Doe', 'Mary Doe', '1990-05-15', '+8801712345678', '+8801812345678', '23303106@iubat.edu', 'shafiul', 'Menager', 'Bachelor of Science', 'Dhaka', NULL, 2012, 'University of Dhaka', 3.75, 'Science', 'Male', 'B+', 'Bangladeshi', 'Islam', '123 Main Road, Dhaka', '456 Village Street, Faridpur', 'Computer Science', '/uploads/photos/john_doe.jpg', '/uploads/signatures/john_doe_sig.png', '2025-07-31 10:04:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `library_card_number` varchar(20) NOT NULL,
  `user_type` varchar(20) NOT NULL,
  `id` int(11) NOT NULL,
  `max_books_allowed` int(11) DEFAULT 5,
  `membership_start_date` date NOT NULL,
  `membership_end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `library_card_number`, `user_type`, `id`, `max_books_allowed`, `membership_start_date`, `membership_end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1000, '123456789', 'student', 23303106, 5, '2025-08-08', '2035-08-09', 1, '2025-08-08 09:49:58', '2025-08-08 09:49:58'),
(1001, '1234512345', 'Faculty', 7654321, 5, '2025-08-08', '2029-02-08', 1, '2025-08-08 12:15:21', '2025-08-08 12:15:21'),
(1003, '333333333333', 'Faculty', 7654322, 5, '2025-08-15', '2025-08-15', 1, '2025-08-15 13:31:19', '2025-08-15 13:31:19'),
(1005, '222222222', 'Faculty', 7654323, 5, '2025-08-15', '2025-08-15', 1, '2025-08-15 13:34:36', '2025-08-15 13:34:36'),
(1006, '11111111111', 'Faculty', 10, 5, '2025-08-15', '2025-08-15', 1, '2025-08-15 13:38:14', '2025-08-15 13:38:14'),
(1007, '333333', 'Faculty', 11, 5, '2025-08-15', '2025-08-15', 1, '2025-08-15 13:48:30', '2025-08-15 13:48:30');

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
  `password` varchar(255) NOT NULL DEFAULT 'volunteer123'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `volunteers`
--

INSERT INTO `volunteers` (`sl`, `student_id`, `student_name`, `department`, `email`, `phone`, `activity_name`, `activity_date`, `role`, `hours`, `remarks`, `stratus`, `password`) VALUES
(1, 2, 'Rahim Uddin', 'Computer Science', 'rahim@example.com', '01710000001', 'Blood Donation Camp', '2025-02-15', 'Volunteer', 5, 'Donated blood and helped in registration', '1', 'volunteer123'),
(2, 201, 'Karim Hasan', 'Electrical Engineering', 'karim@example.com', '01710000002', 'Tree Plantation Drive', '2025-03-05', 'Organizer', 8, 'Coordinated volunteers and managed logistics', '1', 'volunteer123'),
(3, 3, 'Nusrat Jahan', 'Business Administration', 'nusrat@example.com', '01710000003', 'Campus Clean-up', '2025-04-10', 'Volunteer', 4, 'Participated in cleaning and waste management', '1', 'volunteer123'),
(4, NULL, 'Taslima Akter', 'Civil Engineering', 'taslima@example.com', '01710000004', 'Fundraising Event', '2025-05-20', 'Leader', 10, 'Led a fundraising team for charity', '1', 'volunteer123'),
(5, 15, 'Mahmudul Islam', 'Mechanical Engineering', 'mahmud@example.com', '01710000005', 'Cultural Festival', '2025-06-12', 'Volunteer', 6, 'Assisted in stage setup and coordination', '1', 'volunteer123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`alumni_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faculty_id` (`faculty_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Indexes for table `borrow_books`
--
ALTER TABLE `borrow_books`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`);

--
-- Indexes for table `ebook`
--
ALTER TABLE `ebook`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `scholarship_application`
--
ALTER TABLE `scholarship_application`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`);

--
-- Indexes for table `student_registration`
--
ALTER TABLE `student_registration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_result`
--
ALTER TABLE `student_result`
  ADD PRIMARY KEY (`st_id`,`semister`,`course`),
  ADD KEY `course` (`course`);

--
-- Indexes for table `stuf`
--
ALTER TABLE `stuf`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);
ALTER TABLE `stuf` ADD FULLTEXT KEY `first_name` (`first_name`,`last_name`,`father_name`,`mother_name`,`email`,`institution_name`,`present_address`,`permanent_address`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `library_card_number` (`library_card_number`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `library_card_number_2` (`library_card_number`),
  ADD KEY `user_type` (`user_type`);

--
-- Indexes for table `volunteers`
--
ALTER TABLE `volunteers`
  ADD PRIMARY KEY (`sl`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23303107;

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `alumni`
--
ALTER TABLE `alumni`
  MODIFY `alumni_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `borrow_books`
--
ALTER TABLE `borrow_books`
  MODIFY `borrow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7654324;

--
-- AUTO_INCREMENT for table `scholarship_application`
--
ALTER TABLE `scholarship_application`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_registration`
--
ALTER TABLE `student_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23303138;

--
-- AUTO_INCREMENT for table `stuf`
--
ALTER TABLE `stuf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1008;

--
-- AUTO_INCREMENT for table `volunteers`
--
ALTER TABLE `volunteers`
  MODIFY `sl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_registration` (`id`);

--
-- Constraints for table `borrow_books`
--
ALTER TABLE `borrow_books`
  ADD CONSTRAINT `borrow_books_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  ADD CONSTRAINT `borrow_books_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `student_result`
--
ALTER TABLE `student_result`
  ADD CONSTRAINT `student_result_ibfk_1` FOREIGN KEY (`st_id`) REFERENCES `student_registration` (`id`),
  ADD CONSTRAINT `student_result_ibfk_2` FOREIGN KEY (`course`) REFERENCES `course` (`course_code`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
