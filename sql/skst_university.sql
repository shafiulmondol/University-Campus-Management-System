-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2025 at 09:47 AM
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
(1, 'Admin User', 'admin1234', 'shafiul', '23303105@iubat.edu', '123-456-7890', 123, '', '2025-09-01 21:21:06', '2025-09-03 15:25:11.000000', 1),
(1490, 'shafiul islam', 'Admission Officer', 'shafiul', '23303106@iubat.edu', '01884273156', 123, '', '2025-09-03 15:04:27', '2025-09-06 13:26:27.000000', 1);

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
(1, 'Md. Kawsar Miah', '23303105@iubat.edu', 'kawsar', 2015, 'Bachelor of Science', 'Computer Science', 'Software Engineer', 'Tech Corp', '1234567890', '123 Main St, Anytown', NULL, '2025-07-25 21:53:04', '2025-07-26 01:51:35', 1),
(2, 'Md. Shafiul Islam', '23303106@iubat.edu', 'shafiul', 2018, 'Master of Business', 'Business Administration', 'Marketing Manager', 'Global Inc', '9876543210', '456 Oak Ave, Somewhere', NULL, NULL, '2025-07-26 01:51:35', 1);

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
-- Table structure for table `bank_officers`
--

CREATE TABLE `bank_officers` (
  `officer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(20) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','retired') DEFAULT 'active',
  `hire_date` date DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_officers`
--

INSERT INTO `bank_officers` (`officer_id`, `name`, `email`, `password`, `phone`, `department`, `position`, `status`, `hire_date`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Md. Kawsar Miah', '23303105@iubat.edu', 'kawsar', '01710000001', 'Loans', 'Senior Officer', 'active', '2020-03-15', '2025-09-05 06:51:25', '2025-09-04 15:23:50', '2025-09-05 00:51:25'),
(2, 'Md. Shafiul Islam', '23303106@iubat.edu', 'shafiul', '01710000002', 'Accounts', 'Manager', 'active', '2018-07-10', '2025-09-04 21:23:50', '2025-09-04 15:23:50', '2025-09-04 16:02:12'),
(3, 'Toymoon Islam Estia', '23303116@iubat.edu', 'toymoon', '01710000003', 'Customer Service', 'Junior Officer', 'inactive', '2022-01-20', '2025-09-04 21:23:50', '2025-09-04 15:23:50', '2025-09-04 16:03:04');

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
-- Table structure for table `book_borrowings`
--

CREATE TABLE `book_borrowings` (
  `borrow_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('borrowed','returned','overdue','lost') NOT NULL DEFAULT 'borrowed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_borrowings`
--

INSERT INTO `book_borrowings` (`borrow_id`, `member_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `fine_amount`, `status`, `created_at`) VALUES
(1, 1, 1, '2025-09-05', '2025-09-19', '2025-09-25', 30.00, 'returned', '2025-09-05 13:42:02'),
(2, 1, 2, '2025-09-05', '2025-09-19', '2025-09-26', 35.00, 'returned', '2025-09-05 16:58:22');

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
(100, 'EEE 100', 'meaning', 3, 'EEE', 4, '2025-09-04 09:26:25', '2025-09-04 09:26:25'),
(112, 'csc 112', 'DBMS', 4, 'BCSE', 3, '2025-09-01 12:21:45', '2025-09-01 12:21:45'),
(123, 'csc 123', 'dbms', 5, 'BCSE', 1, '2025-09-01 18:59:59', '2025-09-01 18:59:59'),
(222, 'csc 222', 'computer archetecture', 3, 'BCSE', 2, '2025-09-01 18:17:02', '2025-09-01 18:17:02'),
(234, 'TT 234', 'testing', 5, 'TT', 5, '2025-09-04 18:32:04', '2025-09-04 18:32:04'),
(777, 'sss 777', 'sss', 3, 's', 5, '2025-09-05 16:25:24', '2025-09-05 16:25:24');

-- --------------------------------------------------------

--
-- Table structure for table `course_instructor`
--

CREATE TABLE `course_instructor` (
  `faculty_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `class_day` enum('Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `class_time` enum('8:30-9:30','9:35-10:35','10:40-11:40','11:45-12:45','1:10-2:10','2:15-3:15','4:20-5:20') NOT NULL,
  `room_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_instructor`
--

INSERT INTO `course_instructor` (`faculty_id`, `course_id`, `class_day`, `class_time`, `room_number`) VALUES
(7654327, 100, 'Sunday', '8:30-9:30', '1001'),
(7654327, 100, 'Sunday', '8:30-9:30', '555'),
(7654327, 112, 'Sunday', '10:40-11:40', '1211'),
(7654327, 123, 'Thursday', '10:40-11:40', '1001'),
(7654328, 123, 'Sunday', '8:30-9:30', '333'),
(7654328, 222, 'Tuesday', '8:30-9:30', '999'),
(7654324, 332, 'Saturday', '9:35-10:35', '1000');

-- --------------------------------------------------------

--
-- Table structure for table `course_materials`
--

CREATE TABLE `course_materials` (
  `material_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_type` varchar(50) DEFAULT 'document',
  `file_size` varchar(20) DEFAULT '0 KB'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_materials`
--

INSERT INTO `course_materials` (`material_id`, `course_id`, `faculty_id`, `title`, `description`, `file_path`, `upload_date`, `file_type`, `file_size`) VALUES
(1, 112, 7654327, 'picture', 'vv', 'uploads/course_materials/1757064352_Screenshot 2025-09-05 115333.png', '2025-09-05 09:25:52', 'png', '101.95 KB'),
(2, 112, 7654327, 'picture', 'x', 'uploads/course_materials/1757065994_Screenshot 2025-09-05 123943.png', '2025-09-05 09:53:14', 'png', '1.28 KB'),
(3, 112, 7654327, 'memory', '2got', 'uploads/course_materials/1757071466_1709488820435.jpg', '2025-09-05 11:24:26', 'jpg', '1.16 MB');

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
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('enrolled','completed','dropped') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `course_id`, `faculty_id`, `enrollment_date`, `status`) VALUES
(3, 23303137, 100, 7654327, '2025-09-06 02:19:10', 'enrolled'),
(4, 23303105, 777, 7654328, '2025-09-06 02:19:34', 'enrolled'),
(5, 23303106, 100, 7654328, '2025-09-06 02:27:32', 'enrolled'),
(6, 23303105, 777, 7654328, '2025-09-06 02:28:45', 'enrolled'),
(7, 23303105, 100, 7654328, '2025-09-06 03:07:40', 'enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `exm_routine`
--

CREATE TABLE `exm_routine` (
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `day` enum('Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `exam_date` date NOT NULL,
  `time` time NOT NULL,
  `room_no` varchar(20) NOT NULL,
  `set_no` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exm_routine`
--

INSERT INTO `exm_routine` (`student_id`, `course_id`, `faculty_id`, `day`, `exam_date`, `time`, `room_no`, `set_no`) VALUES
(23303105, 100, 7654328, 'Tuesday', '2025-09-23', '09:00:00', '100', 1),
(23303105, 777, 7654328, 'Thursday', '2025-09-25', '13:00:00', '100', 1),
(23303106, 100, 7654328, 'Tuesday', '2025-09-23', '09:00:00', '100', 2),
(23303137, 100, 7654327, 'Tuesday', '2025-09-23', '09:00:00', '100', 3);

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

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `name`, `email`, `password`, `department`, `address`, `phone`, `room_number`, `salary`, `last_login`, `profile_picture`) VALUES
(7654327, 'Md. Kawsar Miah', '23303105@iubat.edu', 'kawsar', 'BCSE', 'Uttara, Dhaka-1230', '01884273156', '1010', 1000.00, '2025-09-03 09:56:24', NULL),
(7654328, 'Md Shafiul Islam', '23303106@iubat.edu', 'shafiul', 'BCSE', 'Bamrar Tech, Chan Miah Vila', '01701535780', '1001', 1000000.00, '2025-09-05 21:27:51', 'uploads/faculty/68b96270b8d12.png');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_payments`
--

CREATE TABLE `faculty_payments` (
  `payment_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_type` enum('salary','bonus','allowance','reimbursement','other') NOT NULL,
  `payment_month` varchar(20) NOT NULL,
  `payment_year` year(4) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','processed','failed') NOT NULL DEFAULT 'pending',
  `bank_transaction_id` varchar(100) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_payments`
--

INSERT INTO `faculty_payments` (`payment_id`, `faculty_id`, `amount`, `payment_type`, `payment_month`, `payment_year`, `description`, `status`, `bank_transaction_id`, `processed_by`, `created_at`, `processed_at`) VALUES
(1, 1, 50000.00, 'salary', 'September', '2025', 'Have a good Day', 'processed', 'BX 122030 YZ', NULL, '2025-09-04 23:46:13', NULL);

--
-- Triggers `faculty_payments`
--
DELIMITER $$
CREATE TRIGGER `after_faculty_payments_insert` AFTER INSERT ON `faculty_payments` FOR EACH ROW BEGIN
  INSERT INTO transaction_history 
    (transaction_type, source_table, source_id, amount, transaction_date, description, related_user_type, related_user_id)
  VALUES 
    ('outgoing', 'faculty_payments', NEW.payment_id, NEW.amount, DATE(NEW.created_at),
     CONCAT('Faculty Payment: ', NEW.payment_type, ' - ', NEW.amount, ' Taka'),
     'faculty', NEW.faculty_id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `library_members`
--

CREATE TABLE `library_members` (
  `member_id` int(11) NOT NULL,
  `user_type` enum('student','faculty','staff','alumni','bank_officer','admin') NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `max_books` int(11) NOT NULL DEFAULT 3,
  `current_books` int(11) NOT NULL DEFAULT 0,
  `membership_status` enum('active','suspended','graduated','retired') NOT NULL DEFAULT 'active',
  `membership_start` date NOT NULL,
  `membership_end` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `library_members`
--

INSERT INTO `library_members` (`member_id`, `user_type`, `user_id`, `full_name`, `email`, `department`, `max_books`, `current_books`, `membership_status`, `membership_start`, `membership_end`, `created_at`, `updated_at`) VALUES
(1, 'faculty', 7654327, 'Md. Kawsar Miah', '23303105@iubat.edu', 'BCSE', 3, 0, 'active', '2025-09-05', NULL, '2025-09-05 12:30:51', '2025-09-05 12:30:51'),
(2, 'alumni', 1258, 'Toymoon Islam Estia', '23303116@iubat.edu', 'BCSE', 10, 0, 'graduated', '2025-09-05', NULL, '2025-09-05 16:56:48', '2025-09-05 16:56:48');

-- --------------------------------------------------------

--
-- Table structure for table `notice`
--

CREATE TABLE `notice` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `section` varchar(100) NOT NULL,
  `sub_section` varchar(50) DEFAULT NULL,
  `content` text NOT NULL,
  `author` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `viewed` tinyint(1) DEFAULT 0 COMMENT '0=unread, 1=read'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice`
--

INSERT INTO `notice` (`id`, `title`, `section`, `sub_section`, `content`, `author`, `created_at`, `viewed`) VALUES
(0, '234444', 'Alumni', NULL, 'inseert alumni data notice', 'shafiul', '2025-08-06 16:56:33', 1),
(0, '23444455', 'Alumni', NULL, 'inseert alumni data notice', 'shafiul', '2025-08-06 16:57:00', 1),
(0, '234444', 'Alumni', NULL, 'inseert alumni data notice', 'shafiul', '2025-08-06 16:56:33', 1),
(0, '23444455', 'Alumni', NULL, 'inseert alumni data notice', 'shafiul', '2025-08-06 16:57:00', 1),
(11111111, 'dfdfd', 'Library', NULL, 'sdfsdf dsfds ', 'shafiul islam', '2025-09-04 08:27:06', 1),
(0, '000000000000000000000000000000000000000', 'Bank', NULL, 'dsadffdsfdsfds', 'shafiul', '2025-09-04 18:55:17', 1),
(0, '000000000000000000000000000000000000000', 'Bank', NULL, 'dsadffdsfdsfds', 'shafiul', '2025-09-04 19:00:53', 1),
(1, 'dfgd', 'Student', NULL, 'fdgfd', 'shafiul', '2025-09-05 02:58:07', 1),
(1, 'come', 'Student', NULL, 'fdgfd', 'shafiul', '2025-09-05 02:59:15', 1),
(123, 'check department', 'Department', 'BCSE', 'nothing', 'admin', '2025-09-05 03:53:33', 1),
(23303106, 'ssss', 'Student', '', 'dfd', 'admin', '2025-09-05 17:15:42', 1),
(0, 'due', 'Library', '', 'dddddd', 'admin', '2025-09-05 17:19:22', 0),
(23303106, 'sss', 'Library', '', 'dddd', 'shafiul', '2025-09-05 17:21:56', 0),
(23303106, 'ssss', 'Staff', '', 'ssss', 'shafiul', '2025-09-05 17:23:37', 1);

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
-- Table structure for table `staff_payments`
--

CREATE TABLE `staff_payments` (
  `payment_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_type` enum('salary','bonus','allowance','reimbursement','other') NOT NULL,
  `payment_month` varchar(20) NOT NULL,
  `payment_year` year(4) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','processed','failed') NOT NULL DEFAULT 'pending',
  `bank_transaction_id` varchar(100) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_payments`
--

INSERT INTO `staff_payments` (`payment_id`, `staff_id`, `amount`, `payment_type`, `payment_month`, `payment_year`, `description`, `status`, `bank_transaction_id`, `processed_by`, `created_at`, `processed_at`) VALUES
(1, 1, 5000.00, 'salary', 'September', '2025', 'Have a good day.', 'pending', 'NO 12345 YT', NULL, '2025-09-05 00:00:51', NULL);

--
-- Triggers `staff_payments`
--
DELIMITER $$
CREATE TRIGGER `after_staff_payments_insert` AFTER INSERT ON `staff_payments` FOR EACH ROW BEGIN
  INSERT INTO transaction_history 
    (transaction_type, source_table, source_id, amount, transaction_date, description, related_user_type, related_user_id)
  VALUES 
    ('outgoing', 'staff_payments', NEW.payment_id, NEW.amount, DATE(NEW.created_at),
     CONCAT('Staff Payment: ', NEW.payment_type, ' - ', NEW.amount, ' Taka'),
     'staff', NEW.staff_id);
END
$$
DELIMITER ;

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
  `key` int(11) NOT NULL,
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

INSERT INTO `student_registration` (`id`, `first_name`, `last_name`, `father_name`, `mother_name`, `date_of_birth`, `guardian_phone`, `student_phone`, `email`, `password`, `key`, `last_exam`, `board`, `other_board`, `year_of_passing`, `institution_name`, `result`, `subject_group`, `gender`, `blood_group`, `nationality`, `religion`, `present_address`, `permanent_address`, `department`, `photo_path`, `signature_path`, `submission_date`) VALUES
(23303105, 'Md Shafiul', 'Islam', 'asraful', 'sohida', '2003-06-10', '01701535780', '01701535780', '23303105@iubat.edu', 'shafiulmondol', 123, 'HSC', 'Dinajpur', '', '2021', 'ccr', '5.00', 'Science', 'male', 'B+', 'Bangladeshi', 'Islam', 'Bamrar Tech, Chan Miah Vila', 'bamnartec', 'CSE', NULL, NULL, '2025-09-04 11:38:48'),
(23303106, 'shafiul islam', 'mondol', 'Md.Mazid', 'Mina Islam', '2003-12-14', '01718520912', '01530811222', '23303106@iubat.edu', 'shafiul', 123, 'HSC', 'Dhaka', NULL, '2022', 'Uttara high school', '5.00', 'science', 'female', 'A+', 'Bangladeshi', 'Islam', 'Uttara', 'Uttara', 'Cse', NULL, NULL, '2025-07-26 17:17:40'),
(23303137, 'sumaiya', 'haq', 'father', 'mother', '2003-09-17', '+8801712345678', '01701535780', '23303137@iubat.edu', 'sumaiya', 123, 'HSC', 'Dhaka', NULL, '2021', 'dhaka college', '3.75', 'science', 'female', 'O+', 'Bangladeshi', 'Islam', 'sdfg sdfg sdfg sdfg', 'sfg sdfgh fghj fghj', 'BCSE', NULL, NULL, '2025-08-29 04:51:27');

-- --------------------------------------------------------

--
-- Table structure for table `student_result`
--

CREATE TABLE `student_result` (
  `st_id` int(20) NOT NULL,
  `semister` int(100) NOT NULL,
  `course` varchar(50) NOT NULL,
  `grade` varchar(20) DEFAULT NULL,
  `marks` float NOT NULL,
  `cgpa` float DEFAULT NULL,
  `sgpa` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_result`
--

INSERT INTO `student_result` (`st_id`, `semister`, `course`, `grade`, `marks`, `cgpa`, `sgpa`) VALUES
(23303105, 1, 'EEE 100', NULL, 0, NULL, NULL),
(23303106, 1, 'csc 112', '4', 40, 3.2, 3.98),
(23303106, 2, 'csc 112', '3.7', 70, 3.2, 3.98);

-- --------------------------------------------------------

--
-- Table structure for table `stuf`
--

CREATE TABLE `stuf` (
  `id` int(11) NOT NULL,
  `sector` varchar(100) NOT NULL,
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

INSERT INTO `stuf` (`id`, `sector`, `first_name`, `last_name`, `father_name`, `mother_name`, `date_of_birth`, `guardian_phone`, `stuff_phone`, `email`, `password`, `position`, `last_exam`, `board`, `other_board`, `year_of_passing`, `institution_name`, `result`, `subject_group`, `gender`, `blood_group`, `nationality`, `religion`, `present_address`, `permanent_address`, `department`, `photo_path`, `signature_path`, `submission_date`) VALUES
(1, '', 'shafiul ', 'islam', 'Robert Doe', 'Mary Doe', '1990-05-15', '+8801712345678', '+8801812345678', '23303106@iubat.edu', 'shafiul', 'Menager', 'Bachelor of Science', 'Dhaka', NULL, 2012, 'University of Dhaka', 3.75, 'Science', 'Male', 'B+', 'Bangladeshi', 'Islam', '123 Main Road, Dhaka', '456 Village Street, Faridpur', 'Computer Science', '/uploads/photos/john_doe.jpg', '/uploads/signatures/john_doe_sig.png', '2025-07-31 10:04:12');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_history`
--

CREATE TABLE `transaction_history` (
  `history_id` int(11) NOT NULL,
  `transaction_type` enum('incoming','outgoing') NOT NULL,
  `source_table` varchar(50) NOT NULL,
  `source_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL COMMENT 'Amount in Taka',
  `transaction_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `related_user_type` enum('student','staff','faculty') DEFAULT NULL,
  `related_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_history`
--

INSERT INTO `transaction_history` (`history_id`, `transaction_type`, `source_table`, `source_id`, `amount`, `transaction_date`, `description`, `related_user_type`, `related_user_id`, `created_at`) VALUES
(1, 'incoming', 'university_bank_payments', 3, 5000.00, '2025-09-05', 'Student Payment: library - 5000.00 Taka', 'student', 23303106, '2025-09-05 01:14:11');

-- --------------------------------------------------------

--
-- Table structure for table `university_bank_payments`
--

CREATE TABLE `university_bank_payments` (
  `bank_payment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_type` enum('registration','tuition','exam','library','hostel','other') NOT NULL,
  `semester` varchar(20) NOT NULL,
  `academic_year` year(4) NOT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `university_bank_payments`
--

INSERT INTO `university_bank_payments` (`bank_payment_id`, `student_id`, `transaction_id`, `amount`, `payment_type`, `semester`, `academic_year`, `status`, `created_at`) VALUES
(1, 23303106, 'TXNS00123456', 5000.00, 'registration', 'Fall 2025', '2025', 'verified', '2025-09-04 17:28:05'),
(2, 23303137, 'TXNS00123458', 5000.00, 'registration', 'Fall 2025', '2025', 'verified', '2025-09-04 19:10:22'),
(3, 23303106, '23303106', 5000.00, 'library', 'Fall 2025', '2025', 'verified', '2025-09-05 01:14:11');

--
-- Triggers `university_bank_payments`
--
DELIMITER $$
CREATE TRIGGER `after_university_bank_payments_insert` AFTER INSERT ON `university_bank_payments` FOR EACH ROW BEGIN
  INSERT INTO transaction_history 
    (transaction_type, source_table, source_id, amount, transaction_date, description, related_user_type, related_user_id)
  VALUES 
    ('incoming', 'university_bank_payments', NEW.bank_payment_id, NEW.amount, DATE(NEW.created_at),
     CONCAT('Student Payment: ', NEW.payment_type, ' - ', NEW.amount, ' Taka'),
     'student', NEW.student_id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `update_requests`
--

CREATE TABLE `update_requests` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) DEFAULT NULL,
  `admin_email` varchar(50) NOT NULL,
  `category` varchar(100) NOT NULL,
  `update_type` enum('password','email') NOT NULL,
  `current_value` varchar(255) NOT NULL,
  `new_value` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  `request_time` datetime NOT NULL,
  `action` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `update_requests`
--

INSERT INTO `update_requests` (`id`, `applicant_id`, `admin_email`, `category`, `update_type`, `current_value`, `new_value`, `comments`, `request_time`, `action`) VALUES
(15, 23303106, '23303106@iubat.edu', 'Student', 'password', 'shafiulmondol', 'shafiulmondol', 'sss | Old Value: shafiul | Old Value: shafiulmondol', '2025-09-05 22:14:59', 4),
(16, 23303105, '23303106@iubat.edu', 'Student', 'password', 'shafiulmondol', 'shafiulmondol', 'sss | Old Value: shafiul', '2025-09-05 22:21:16', 4),
(17, 23303106, '23303106@iubat.edu', 'Student', 'password', 'shafiul', 'shafiul', 'ssss | Old Value: shafiulmondol', '2025-09-05 23:16:43', 4);

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
(1007, '333333', 'Faculty', 11, 5, '2025-08-15', '2025-08-15', 1, '2025-08-15 13:48:30', '2025-08-15 13:48:30'),
(1008, '333333333333222', 'Student', 23303105, 5, '2025-09-05', '2025-09-19', 1, '2025-09-05 17:25:48', '2025-09-05 17:25:48');

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
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

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
-- Indexes for table `bank_officers`
--
ALTER TABLE `bank_officers`
  ADD PRIMARY KEY (`officer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Indexes for table `book_borrowings`
--
ALTER TABLE `book_borrowings`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `book_id` (`book_id`);

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
-- Indexes for table `course_instructor`
--
ALTER TABLE `course_instructor`
  ADD PRIMARY KEY (`faculty_id`,`class_day`,`class_time`,`room_number`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `ebook`
--
ALTER TABLE `ebook`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `fk_student` (`student_id`),
  ADD KEY `fk_course` (`course_id`),
  ADD KEY `fk_faculty` (`faculty_id`);

--
-- Indexes for table `exm_routine`
--
ALTER TABLE `exm_routine`
  ADD PRIMARY KEY (`student_id`,`exam_date`,`time`),
  ADD UNIQUE KEY `exam_date` (`exam_date`,`time`,`room_no`,`set_no`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `faculty_payments`
--
ALTER TABLE `faculty_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `library_members`
--
ALTER TABLE `library_members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `unique_user_reference` (`user_type`,`user_id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `scholarship_application`
--
ALTER TABLE `scholarship_application`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`);

--
-- Indexes for table `staff_payments`
--
ALTER TABLE `staff_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `student_registration`
--
ALTER TABLE `student_registration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- Indexes for table `transaction_history`
--
ALTER TABLE `transaction_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `transaction_date` (`transaction_date`),
  ADD KEY `source_table_source_id` (`source_table`,`source_id`);

--
-- Indexes for table `university_bank_payments`
--
ALTER TABLE `university_bank_payments`
  ADD PRIMARY KEY (`bank_payment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `update_requests`
--
ALTER TABLE `update_requests`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `bank_officers`
--
ALTER TABLE `bank_officers`
  MODIFY `officer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `book_borrowings`
--
ALTER TABLE `book_borrowings`
  MODIFY `borrow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `borrow_books`
--
ALTER TABLE `borrow_books`
  MODIFY `borrow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7654329;

--
-- AUTO_INCREMENT for table `faculty_payments`
--
ALTER TABLE `faculty_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `library_members`
--
ALTER TABLE `library_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `scholarship_application`
--
ALTER TABLE `scholarship_application`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_payments`
--
ALTER TABLE `staff_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_registration`
--
ALTER TABLE `student_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23303139;

--
-- AUTO_INCREMENT for table `stuf`
--
ALTER TABLE `stuf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transaction_history`
--
ALTER TABLE `transaction_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `university_bank_payments`
--
ALTER TABLE `university_bank_payments`
  MODIFY `bank_payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `update_requests`
--
ALTER TABLE `update_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1009;

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
-- Constraints for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD CONSTRAINT `course_materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`),
  ADD CONSTRAINT `course_materials_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`),
  ADD CONSTRAINT `fk_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`),
  ADD CONSTRAINT `fk_student` FOREIGN KEY (`student_id`) REFERENCES `student_registration` (`id`);

--
-- Constraints for table `exm_routine`
--
ALTER TABLE `exm_routine`
  ADD CONSTRAINT `exm_routine_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_registration` (`id`),
  ADD CONSTRAINT `exm_routine_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`),
  ADD CONSTRAINT `exm_routine_ibfk_3` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `staff_payments`
--
ALTER TABLE `staff_payments`
  ADD CONSTRAINT `staff_payments_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `stuf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_result`
--
ALTER TABLE `student_result`
  ADD CONSTRAINT `student_result_ibfk_1` FOREIGN KEY (`st_id`) REFERENCES `student_registration` (`id`),
  ADD CONSTRAINT `student_result_ibfk_2` FOREIGN KEY (`course`) REFERENCES `course` (`course_code`);

--
-- Constraints for table `university_bank_payments`
--
ALTER TABLE `university_bank_payments`
  ADD CONSTRAINT `university_bank_payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_registration` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
