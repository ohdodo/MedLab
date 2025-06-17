-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2025 at 04:30 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medlab_users`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthday` datetime NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `email`, `birthday`, `password`, `is_admin`) VALUES
(9, 'admin', 'user', 'admin1234@gmail.com', '2004-03-28 00:00:00', '$2y$10$oUIedENdlaK1kPc4ODgz3uIA4t4F0qnUuNpvp0gDEvbz6P69Ceyhm', 1),
(10, 'admin', 'user', 'admin@gmail.com', '2004-03-28 00:00:00', '$2y$10$3wZdmVS97rBIpT98r0wvc.eHXvn..NT5CO370Kmxb/pKpVpwhsXpG', 0),
(11, 'admin user', 'user', 'admin123@gmail.com', '2004-03-28 00:00:00', '$2y$10$Me8iFwrWS3QR8k6sFS/iw.Kn5oRV261WvX81xZafBCAwFjWBgXEQ.', 1),
(12, 'test', 'test', 'test@gmail.com', '2004-03-28 00:00:00', '$2y$10$hhdjA8GJwFsEBWVs9Zm4su55X8FNzvQr8Km9SFcmyJq/io8sF2356', 1),
(13, 'med', 'lab', 'test1@gmail.com', '2004-03-28 00:00:00', '$2y$10$tTlHPqmC8sKDL5zNOOhTMeEaaILxuGd3o/GGxN.e.XNSZ7XBdm5PW', 0),
(14, 'lab', 'med', 'test2@gmail.com', '2004-03-28 00:00:00', '$2y$10$yQ4MnMjbBKroGRr5.Hd/AevFcr4nhgH/4S/4ebdSEojemg1Gq3hB.', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
