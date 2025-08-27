-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 27, 2025 at 10:59 AM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ebook`
--
ALTER TABLE `ebook`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
