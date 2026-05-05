-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2026 at 10:06 AM
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
-- Database: `pawhub_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `app_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pet_id` int(11) NOT NULL,
  `applicant_name` varchar(100) NOT NULL,
  `applicant_email` varchar(100) NOT NULL,
  `applicant_phone` varchar(20) DEFAULT NULL,
  `home_type` varchar(50) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`app_id`, `user_id`, `pet_id`, `applicant_name`, `applicant_email`, `applicant_phone`, `home_type`, `reason`, `status`, `submitted_at`) VALUES
(1, 2, 1, 'Gaurav Thapa', 'gaurav@pawhub.com', '9811111111', 'Apartment', 'I have always wanted to adopt a rescue dog and Buddy seems perfect.', 'Rejected', '2026-04-26 04:59:34'),
(2, 2, 3, 'Gaurav Thapa', 'gaurav@pawhub.com', '9811111111', 'House with garden', 'Looking for a quiet companion for our home.', 'Approved', '2026-04-26 04:59:34'),
(3, NULL, 1, 'Gaurav Thapa', 'gaurabthapa2062@gmail.com', '9861963786', 'House with garden', 'cuz.. dog cute', 'Rejected', '2026-04-26 07:42:36'),
(4, NULL, 1, 'ACP Pradyuman', 'acppaduwa100@gmail.com', '9861963786', 'House with garden', 'cute ihi', 'Rejected', '2026-04-26 07:57:52'),
(5, NULL, 1, 'Kamana Shrestha', 'kamana123@gmail.com', '9871648532', 'House with garden', 'wow kya dog hai', 'Rejected', '2026-04-26 08:21:26'),
(6, NULL, 2, 'ACP Pradyuman', 'acppaduwa100@gmail.com', '9861963786', 'Apartment', 'cute', 'Rejected', '2026-04-26 14:24:34'),
(7, NULL, 6, 'ACP Pradyuman', 'acppaduwa100@gmail.com', '9861963786', 'House with garden', 'banana', 'Approved', '2026-04-26 17:51:48');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `pet_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `species` varchar(20) NOT NULL,
  `breed` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `personality` text DEFAULT NULL,
  `health` text DEFAULT NULL,
  `shelter` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gif` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`pet_id`, `name`, `species`, `breed`, `age`, `gender`, `size`, `color`, `description`, `personality`, `health`, `shelter`, `image`, `gif`, `status`, `created_at`) VALUES
(7, 'Banana', 'Dog', 'Banana', 1, 'Male', 'Small', 'Yellow', 'banana', 'banana', 'banana', 'banana', '1777276247_banana.jpg', '1777276247_gif_bananacat.gif', 'Available', '2026-04-27 07:50:47'),
(8, 'Scuba', 'Cat', 'scuba', 2, 'Male', 'Small', 'white', 'scuba', 'scuba', 'scuba', 'scuba', '1777276337_scuba.jpg', '1777276337_gif_scuba.gif', 'Available', '2026-04-27 07:52:17'),
(9, 'Husky', 'Dog', 'husky', 4, 'Male', 'Medium', 'Gray', 'Husky', 'dancer', 'good', 'nachiya', '1777276424_husky.jpg', '1777276424_gif_husky.gif', 'Available', '2026-04-27 07:53:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `role` varchar(10) DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `address`, `role`, `created_at`) VALUES
(1, 'Admin User', 'admin@pawhub.com', 'admin123', '9800000000', 'Kathmandu', 'admin', '2026-04-26 04:59:34'),
(2, 'Gaurav Thapa', 'gaurav@pawhub.com', 'user123', '9811111111', 'Kathmandu', 'user', '2026-04-26 04:59:34'),
(3, 'ACP Pradyuman', 'acppaduwa100@gmail.com', 'bhutututu123', '9861963786', NULL, 'user', '2026-04-26 07:57:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`app_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`pet_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `pet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
