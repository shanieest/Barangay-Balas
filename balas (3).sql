-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2025 at 03:28 AM
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
-- Database: `balas`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `timestamp`, `ip_address`, `user_agent`) VALUES
(1, 1, 'Logged in to the system', '2025-08-04 12:29:35', NULL, NULL),
(2, 1, 'Logged in to the system', '2025-08-12 18:52:58', NULL, NULL),
(3, 1, 'Logged in to the system', '2025-08-13 14:33:34', NULL, NULL),
(4, 1, 'Logged in to the system', '2025-08-14 08:33:34', NULL, NULL),
(5, 1, 'Logged in to the system', '2025-08-14 09:30:37', NULL, NULL),
(6, 1, 'Logged in to the system', '2025-08-15 14:31:56', NULL, NULL),
(7, 1, 'Logged in to the system', '2025-08-16 15:02:09', NULL, NULL),
(8, 1, 'Logged in to the system', '2025-08-18 12:32:35', NULL, NULL),
(9, 1, 'Logged in to the system', '2025-08-18 13:07:51', NULL, NULL),
(10, 1, 'Logged in to the system', '2025-08-18 18:05:33', NULL, NULL),
(11, 1, 'Logged in to the system', '2025-08-18 21:24:34', NULL, NULL),
(12, 1, 'Logged in to the system', '2025-08-18 21:34:03', NULL, NULL),
(13, 1, 'Logged in to the system', '2025-08-21 09:30:19', NULL, NULL),
(14, 1, 'Logged in to the system', '2025-08-21 09:53:59', NULL, NULL),
(15, 1, 'Logged in to the system', '2025-08-21 16:16:06', NULL, NULL),
(16, 1, 'Logged out', '2025-08-21 17:14:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(17, 1, 'Logged in to the system', '2025-08-21 17:14:14', NULL, NULL),
(18, 1, 'Logged out', '2025-08-21 17:14:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(19, 1, 'Logged in to the system', '2025-08-21 17:15:54', NULL, NULL),
(20, 1, 'Logged out', '2025-08-21 17:16:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(21, 1, 'Logged in to the system', '2025-08-21 17:17:34', NULL, NULL),
(22, 1, 'Logged in to the system', '2025-08-24 08:31:57', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `role` enum('Admin','Official') DEFAULT 'Official'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `first_name`, `last_name`, `middle_name`, `email`, `contact_number`, `position`, `photo_path`, `status`, `created_at`, `updated_at`, `last_login`, `role`) VALUES
(1, 'administrator', '$2y$10$rUKGC0CbAREdpcyydH2EwesD5R5WNgexhfUBiuJwIg0.bMeayFs1a', 'Admin na Mganda', 'Jarshane', NULL, 'jarshanetolentino@gmail.com', '09999999999', 'Secretary', NULL, 'Active', '2025-08-04 04:20:14', '2025-08-24 00:31:57', '2025-08-24 08:31:57', 'Official');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `date_posted` date NOT NULL,
  `posted_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `image_path`, `date_posted`, `posted_by`, `created_at`) VALUES
(2, 'Clean up drive', 'initial', 'uploads/announcements/1755538165_images (15).jpeg', '2025-02-11', 1, '2025-08-18 17:29:25');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_officials`
--

CREATE TABLE `barangay_officials` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `admin_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_requests`
--

CREATE TABLE `document_requests` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `document_type_id` int(11) NOT NULL,
  `first_name` varchar(250) NOT NULL,
  `middle_name` varchar(250) NOT NULL,
  `last_name` varchar(250) NOT NULL,
  `houseno` varchar(50) NOT NULL,
  `purok` varchar(50) NOT NULL,
  `civil_status` text NOT NULL,
  `sex` enum('male','female','other','') NOT NULL,
  `birthdate` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Disapproved','Processing','Released') DEFAULT 'Pending',
  `shipping_method` text NOT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_processed` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `id` int(11) NOT NULL,
  `document_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`id`, `document_type`) VALUES
(1, 'Barangay Clearance'),
(2, 'Indigency'),
(3, 'Business Permit'),
(4, 'Residency Certificate'),
(5, 'Barangay ID');

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `id` int(11) NOT NULL,
  `household_number` varchar(50) NOT NULL,
  `head_of_family_id` int(11) DEFAULT NULL,
  `purok` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `house_type` enum('Single-detached','Duplex','Apartment','Shanty','Others') DEFAULT 'Others',
  `status` enum('Active','Inactive','Incomplete') DEFAULT 'Active',
  `ownership` enum('Owned','Rented','Mortgaged','Free Use') DEFAULT 'Owned',
  `year_built` year(4) DEFAULT NULL,
  `water_source` varchar(100) DEFAULT NULL,
  `electricity` enum('With Meter','Without Meter','None') DEFAULT 'With Meter',
  `internet` enum('DSL','Fiber','Wireless','Mobile Data','None') DEFAULT 'None',
  `toilet_facility` varchar(100) DEFAULT NULL,
  `waste_disposal` varchar(100) DEFAULT NULL,
  `vehicle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `household_assistance`
--

CREATE TABLE `household_assistance` (
  `id` int(11) NOT NULL,
  `household_id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `year_received` year(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `household_livelihood`
--

CREATE TABLE `household_livelihood` (
  `id` int(11) NOT NULL,
  `household_id` int(11) NOT NULL,
  `source` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `sex` enum('male','female') NOT NULL,
  `birthdate` date NOT NULL,
  `age` int(11) NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `house_number` varchar(20) NOT NULL,
  `purok` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `verification_status` enum('Verified','Pending','Unverified') DEFAULT 'Unverified',
  `resident_status` enum('Active','Inactive','Deceased') DEFAULT 'Active',
  `photo_path` varchar(255) DEFAULT NULL,
  `valid_id_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `first_name`, `last_name`, `middle_name`, `suffix`, `sex`, `birthdate`, `age`, `civil_status`, `contact_number`, `email`, `house_number`, `purok`, `address`, `verification_status`, `resident_status`, `photo_path`, `valid_id_path`, `created_at`, `updated_at`) VALUES
(1, 'Jarshane', 'Tolentino', 'Sigua', '', 'female', '2003-11-21', 21, '', '+639531692675', 'jarshanetolentino@gmail.com', '409', '1', '409, Purok 1, Balas, Mexico, Pampanga, Philippines', 'Unverified', 'Active', NULL, '689034b541c69.png', '2025-08-04 04:19:01', '2025-08-04 04:19:01'),
(6, 'Marvin', 'Lorenzo', 'Chan', '', 'male', '2003-12-17', 21, '', '09123456781', 'marvin@gmail.com', '1', '1', 'House 1, Purok 1, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id_68a67c14603e5.png', '2025-08-21 01:53:24', '2025-08-21 01:53:24'),
(7, 'Joshua', 'Punzalan', 'Manalo', '', 'male', '2001-01-01', 24, '', '09123456781', 'joshua@gmail.com', '1', '4', 'House 1, Purok 4, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id_68a6eba879588.png', '2025-08-21 09:49:28', '2025-08-21 09:49:28');

-- --------------------------------------------------------

--
-- Table structure for table `resident_accounts`
--

CREATE TABLE `resident_accounts` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `account_status` enum('Pending','Approved','Disapproved') DEFAULT 'Pending',
  `processed_by` int(11) DEFAULT NULL,
  `date_processed` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `date_requested` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','disapproved') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_accounts`
--

INSERT INTO `resident_accounts` (`id`, `resident_id`, `email`, `password`, `account_status`, `processed_by`, `date_processed`, `notes`, `date_requested`, `status`) VALUES
(1, 1, 'jarshanetolentino@gmail.com', '$2y$10$UfToiQJMnbnqLlvHoUmw8u2tICCEbMSdjCgpvXJdaYw6MUmqqb1Pi', 'Approved', 1, '2025-08-12 18:57:50', '', '2025-08-12 18:57:35', 'pending'),
(6, 6, 'marvin@gmail.com', '$2y$10$06Bs.2lCZIsupQtz0TD4jOlvlPbuVbar4xNAFDb7mDqcW9c37CivC', 'Approved', 1, '2025-08-21 09:54:20', '', '2025-08-21 09:53:24', 'pending'),
(7, 7, 'joshua@gmail.com', '$2y$10$Er6PAigjoHUDSvJfb41VT.KbPM/o5VNLrpLHFeKjzKXEOpl5Hlog6', 'Pending', NULL, NULL, NULL, '2025-08-21 17:49:28', 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_bo_admin_user` (`admin_user_id`);

--
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_resident` (`resident_id`),
  ADD KEY `fk_document_type` (`document_type_id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `household_number` (`household_number`),
  ADD KEY `head_of_family_id` (`head_of_family_id`);

--
-- Indexes for table `household_assistance`
--
ALTER TABLE `household_assistance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `household_id` (`household_id`);

--
-- Indexes for table `household_livelihood`
--
ALTER TABLE `household_livelihood`
  ADD PRIMARY KEY (`id`),
  ADD KEY `household_id` (`household_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `household_assistance`
--
ALTER TABLE `household_assistance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `household_livelihood`
--
ALTER TABLE `household_livelihood`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD CONSTRAINT `fk_bo_admin_user` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD CONSTRAINT `fk_document_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `households`
--
ALTER TABLE `households`
  ADD CONSTRAINT `households_ibfk_1` FOREIGN KEY (`head_of_family_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `household_assistance`
--
ALTER TABLE `household_assistance`
  ADD CONSTRAINT `household_assistance_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `household_livelihood`
--
ALTER TABLE `household_livelihood`
  ADD CONSTRAINT `household_livelihood_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  ADD CONSTRAINT `resident_accounts_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resident_accounts_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
