-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2025 at 01:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
(118, 1, '1', '2025-09-19 11:48:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(119, 43, 'Requested document (ID: 3)', '2025-09-19 11:52:15', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1 Edg/140.0.0.0'),
(120, 43, 'Requested document (ID: 2)', '2025-09-19 12:32:58', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1 Edg/140.0.0.0'),
(121, 43, 'Requested document (ID: 3)', '2025-09-19 12:33:52', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1 Edg/140.0.0.0'),
(122, 1, '1', '2025-09-21 08:53:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(123, 1, 'Approved service reservation (ID: 14)', '2025-09-21 13:19:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(124, 1, 'Rejected service reservation (ID: 12)', '2025-09-21 13:19:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(125, 43, 'Cancelled service reservation #13 (Services: Vehicle, Purpose: Test)', '2025-09-21 13:20:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(126, 43, 'Cancelled Clearance document request #116', '2025-09-21 13:20:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(127, 43, 'Cancelled Residency document request #117', '2025-09-21 13:21:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(128, 1, 'Updated service reservation status to \'In Progress\' (ID: 14)', '2025-09-21 13:22:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(129, 43, 'Updated profile photo', '2025-09-21 13:26:47', NULL, NULL),
(130, 43, 'Updated profile photo', '2025-09-21 13:44:48', NULL, NULL),
(131, 43, 'Updated profile photo', '2025-09-21 13:48:58', NULL, NULL),
(132, 43, 'Updated profile photo', '2025-09-21 13:52:48', NULL, NULL),
(133, 1, '1', '2025-09-21 19:04:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(134, 1, '1', '2025-09-22 10:01:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(135, 1, '1', '2025-09-22 10:33:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(136, 1, '1', '2025-09-22 23:40:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(137, 1, '1', '2025-09-23 08:30:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(138, 1, '1', '2025-09-23 08:40:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(139, 43, 'Updated profile information', '2025-09-23 08:46:07', NULL, NULL),
(140, 1, '1', '2025-09-23 09:02:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(141, 1, '1', '2025-09-23 09:06:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(142, 1, '1', '2025-09-23 11:43:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(143, 44, 'Updated profile information', '2025-09-23 11:44:52', NULL, NULL),
(144, 1, '1', '2025-09-23 12:22:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(145, 1, '1', '2025-09-23 13:08:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(146, 43, 'Updated profile information.', '2025-09-23 13:35:05', NULL, NULL);

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
(1, 'administrator', '$2y$10$rUKGC0CbAREdpcyydH2EwesD5R5WNgexhfUBiuJwIg0.bMeayFs1a', 'Admin na Maganda', 'Jarshane', NULL, 'jarshanetolentino@gmail.com', '09999999999', 'Secretary', NULL, 'Active', '2025-08-04 04:20:14', '2025-09-23 05:08:15', '2025-09-23 13:08:15', 'Official');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `date_posted` datetime NOT NULL,
  `posted_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `posted_by`, `created_at`, `updated_at`) VALUES
(8, 'Test 1', 'Debugging session', '2025-09-19 00:00:00', 1, '2025-09-19 03:51:05', '2025-09-21 19:04:37'),
(9, 'Test 2', 'Debugging Session', '2025-09-21 00:00:00', 1, '2025-09-21 11:08:35', '2025-09-21 19:09:12'),
(14, 'Test 3', 'Debugging Session', '2025-09-22 00:00:00', 1, '2025-09-22 02:17:03', NULL),
(15, 'Test 4', 'Debug', '2025-09-22 10:22:16', 1, '2025-09-22 02:22:16', NULL),
(16, 'Test 5', 'Content', '2025-09-22 10:30:37', 1, '2025-09-22 02:30:37', NULL),
(17, 'Test 6', 'Content', '2025-09-22 10:33:02', 1, '2025-09-22 02:33:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `announcement_images`
--

CREATE TABLE `announcement_images` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement_images`
--

INSERT INTO `announcement_images` (`id`, `announcement_id`, `image_path`, `created_at`) VALUES
(6, 8, 'uploads/announcements/1758253865_68ccd329e98c9.jpg', '2025-09-19 03:51:05'),
(7, 8, 'uploads/announcements/1758452677_68cfdbc57c82b.jpg', '2025-09-21 11:04:37'),
(8, 9, 'uploads/announcements/1758452915_68cfdcb35f1cb.png', '2025-09-21 11:08:35'),
(9, 9, 'uploads/announcements/1758452931_68cfdcc3d3eda.png', '2025-09-21 11:08:51'),
(10, 9, 'uploads/announcements/1758452952_68cfdcd89c86f.jpeg', '2025-09-21 11:09:12'),
(16, 14, 'uploads/announcements/1758507423_68d0b19fa693a.jpg', '2025-09-22 02:17:03'),
(17, 15, 'uploads/announcements/1758507736_68d0b2d8726ff.png', '2025-09-22 02:22:16');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_officials`
--

-- --------------------------------------------------------

--
-- Table structure for table `document_qr_codes`
--

CREATE TABLE `document_qr_codes` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `qr_code_image_path` varchar(255) DEFAULT NULL,
  `verification_attempts` int(11) DEFAULT 0,
  `last_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_qr_codes`
--

INSERT INTO `document_qr_codes` (`id`, `request_id`, `qr_code`, `qr_code_image_path`, `verification_attempts`, `last_verified_at`, `created_at`) VALUES
(102, 115, '4aac9b2fdd7452e9', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_115_1758253993.png', 0, NULL, '2025-09-19 03:53:13');

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
  `sex` enum('male','female') NOT NULL,
  `birthdate` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Disapproved','Cancelled') DEFAULT 'Pending',
  `shipping_method` text NOT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_processed` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `document_file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_requests`
--

INSERT INTO `document_requests` (`id`, `resident_id`, `document_type_id`, `first_name`, `middle_name`, `last_name`, `houseno`, `purok`, `civil_status`, `sex`, `birthdate`, `age`, `email`, `purpose`, `status`, `shipping_method`, `date_requested`, `date_processed`, `processed_by`, `notes`, `document_file_path`) VALUES
(115, 43, 3, 'Marvin', 'Chan', 'Lorenzo', '1', '1', 'Single', 'female', '2000', 24, '927365', 'Debugging session', 'Approved', 'Claim Anytime', '2025-09-19 03:52:15', '2025-09-19 03:53:13', NULL, '', 'uploads/generated_docs/request_115_1758253993.pdf'),
(116, 43, 2, 'Marvin', 'Chan', 'Lorenzo', '1', '1', 'Single', 'male', '2000', 24, '927365', 'test', '', 'Claim Anytime', '2025-09-19 04:32:57', NULL, NULL, NULL, NULL),
(117, 43, 3, 'Jarshane', 'Sigua', 'Tolentino', '1', '1', 'Single', 'female', '2000', 24, '0', 'Test', 'Cancelled', 'Claim Anytime', '2025-09-19 04:33:52', NULL, NULL, NULL, NULL);

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
(1, 'Indigency'),
(2, 'Clearance'),
(3, 'Residency'),
(4, 'Business Permit');

-- --------------------------------------------------------

--
-- Table structure for table `household_structure`
--

CREATE TABLE `household_structure` (
  `id` int(11) NOT NULL,
  `household_id` varchar(50) NOT NULL,
  `head_resident_id` int(11) NOT NULL,
  `house_number` varchar(20) NOT NULL,
  `purok` varchar(20) NOT NULL,
  `household_type` enum('Single Person','Nuclear Family','Extended Family','Composite') DEFAULT 'Nuclear Family',
  `total_members` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
  `educational_attainment` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `philhealth_number` varchar(50) DEFAULT NULL,
  `is_indigent` tinyint(1) DEFAULT 0,
  `is_4ps_member` tinyint(1) DEFAULT 0,
  `medical_history` text DEFAULT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `house_number` varchar(20) NOT NULL,
  `purok` varchar(20) NOT NULL,
  `relationship_to_head` varchar(50) DEFAULT NULL,
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

INSERT INTO `residents` (`id`, `first_name`, `last_name`, `middle_name`, `suffix`, `sex`, `birthdate`, `age`, `civil_status`, `educational_attainment`, `religion`, `occupation`, `philhealth_number`, `is_indigent`, `is_4ps_member`, `medical_history`, `contact_number`, `email`, `house_number`, `purok`, `relationship_to_head`, `address`, `verification_status`, `resident_status`, `photo_path`, `valid_id_path`, `created_at`, `updated_at`) VALUES
(43, 'Jarshane', 'Tolentino', 'Sigua', '', 'female', '2003-11-21', 21, 'Single', 'College', 'INC', 'Studemt', '', 1, 1, 'N/A', '91234567890', 'jarshanetolentino@gmail.com', '1', '1', NULL, 'House 1, Purok Purok 1, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', 'uploads/profiles/profile_43_1758433968.jpg', 'uploads/valid_ids/id_68ccd2d474896.jpeg', '2025-09-19 03:49:40', '2025-09-23 05:35:05'),
(44, 'Jarlawrence', 'Tolentino', 'Sigua', '', 'male', '2006-03-11', 19, 'Single', '', '', '', NULL, 0, 0, NULL, '09123456781', 'jarlawrence@gmail.com', '1', '1', NULL, 'House 1, Purok 1, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id_68d1f1b863587.JPG', '2025-09-23 01:02:48', '2025-09-23 03:44:52'),
(45, 'Jarvin', 'Tolentino ', 'Sigua', '', 'male', '2000-11-28', 24, '', NULL, NULL, NULL, NULL, 0, 0, NULL, '09123456781', 'jarvin@gmail.com', '1', 'Purok 1', NULL, 'House 1, Purok Purok 1, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id_68d1f26d49ae2.JPG', '2025-09-23 01:05:49', '2025-09-23 01:05:49'),
(46, 'Jarhim', 'Tolentino ', 'Sigua', '', 'male', '2003-01-03', 22, '', NULL, NULL, NULL, NULL, 0, 0, NULL, '09123456781', 'jarhim@gmail.com', '1', 'Purok 1', NULL, 'House 1, Purok 1, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id_68d2176870f79.pdf', '2025-09-23 03:43:36', '2025-09-23 03:43:36');

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
  `date_requested` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_accounts`
--

INSERT INTO `resident_accounts` (`id`, `resident_id`, `email`, `password`, `account_status`, `processed_by`, `date_processed`, `notes`, `date_requested`) VALUES
(39, 43, 'jarshanetolentino@gmail.com', '$2y$10$6zD4th95dwQLSUsNQvsH2.z4H148hN2nCz7k/.M9y9lO49YXwT2de', 'Approved', 1, '2025-09-19 11:50:12', '', '2025-09-19 11:49:40'),
(40, 44, 'jarlawrence@gmail.com', '$2y$10$191OwOzg6c9LeiqQ/Ae96OaQUSsGFckfylyn.s8FLn5Ef4rRo3/ty', 'Approved', 1, '2025-09-23 09:03:16', 'Hakdog', '2025-09-23 09:02:48'),
(41, 45, 'jarvin@gmail.com', '$2y$10$M.8Es0jYYndneZNhzAo9fu0vAaVI/rCnTNfx.dkdkwnxzO/tUkVAa', 'Approved', 1, '2025-09-23 09:06:19', '', '2025-09-23 09:05:49'),
(42, 46, 'jarhim@gmail.com', '$2y$10$fyZPq0ueMvn.aAIv8hME4O8jGtZTGD4kdYSylY/MA7E2xbbjEhftK', 'Approved', 1, '2025-09-23 11:44:08', '', '2025-09-23 11:43:36');

-- --------------------------------------------------------

--
-- Table structure for table `service_reservations`
--

CREATE TABLE `service_reservations` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `resident_name` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `reservation_date_start` date NOT NULL,
  `reservation_date_end` date DEFAULT NULL,
  `duration_days` int(11) NOT NULL DEFAULT 1,
  `purpose` text NOT NULL,
  `status` enum('Pending','Approved','In Progress','Completed','Cancelled','Rejected') DEFAULT 'Pending',
  `scheduled_datetime` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_processed` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_reservations`
--

INSERT INTO `service_reservations` (`id`, `resident_id`, `resident_name`, `contact_number`, `email`, `reservation_date_start`, `reservation_date_end`, `duration_days`, `purpose`, `status`, `scheduled_datetime`, `notes`, `rejection_reason`, `date_requested`, `date_processed`, `processed_by`, `created_at`, `updated_at`) VALUES
(10, 43, 'Jarshane Tolentino', '91234567890', 'jarshanetolentino@gmail.com', '2025-11-11', '2025-11-12', 2, 'Test', 'Pending', NULL, 'Setup Time: 08:00\nDuration Type: full_day\nEvent Location: House #1, Purok 1\n', NULL, '2025-09-21 01:22:51', NULL, NULL, '2025-09-21 01:22:51', '2025-09-21 01:49:14'),
(11, NULL, 'Jarshane Tolentino', '09999999999', 'jarshanetolentino@gmail.com', '2025-09-22', '2025-09-22', 1, 'TEST', 'Pending', NULL, 'Setup Time: 08:00\nDuration Type: half_day\nEvent Location: House #1, Purok 1\n', NULL, '2025-09-21 01:26:56', NULL, NULL, '2025-09-21 01:26:56', '2025-09-21 01:26:56'),
(12, NULL, 'Jarshane Tolentino', '09999999999', 'jarshanetolentino@gmail.com', '2025-11-11', '2025-11-11', 1, 'Test', 'Rejected', NULL, 'Start Time: 08:00\nEnd Time: 14:00\n', 'Test', '2025-09-21 01:35:32', '2025-09-21 05:19:50', 1, '2025-09-21 01:35:32', '2025-09-21 05:19:50'),
(13, 43, 'Jarshane Tolentino', '91234567890', 'jarshanetolentino@gmail.com', '2025-11-11', '2025-11-11', 1, 'Test', 'Cancelled', NULL, 'Start Time: 08:00\nEnd Time: 20:00\n', NULL, '2025-09-21 05:18:00', NULL, NULL, '2025-09-21 05:18:00', '2025-09-21 05:20:11'),
(14, 43, 'Jarshane Tolentino', '91234567890', 'jarshanetolentino@gmail.com', '2025-11-11', '2025-11-11', 1, 'Test', 'In Progress', NULL, '', NULL, '2025-09-21 05:19:15', '2025-09-21 05:22:34', 1, '2025-09-21 05:19:15', '2025-09-21 05:22:34');

-- --------------------------------------------------------

--
-- Table structure for table `service_reservation_items`
--

CREATE TABLE `service_reservation_items` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `service_type_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_reservation_items`
--

INSERT INTO `service_reservation_items` (`id`, `reservation_id`, `service_type_id`, `quantity`, `notes`) VALUES
(12, 10, 4, 2, NULL),
(13, 11, 1, 2, NULL),
(14, 11, 4, 4, NULL),
(15, 11, 3, 2, NULL),
(16, 12, 2, 1, NULL),
(17, 13, 2, 1, NULL),
(18, 14, 1, 3, NULL),
(19, 14, 4, 4, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_types`
--

CREATE TABLE `service_types` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_types`
--

INSERT INTO `service_types` (`id`, `service_name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Tent', 'Tent rental for events and gatherings', 1, '2025-09-13 18:43:53'),
(2, 'Vehicle', 'Vehicle rental for transportation needs', 1, '2025-09-13 18:43:53'),
(3, 'Sound System', 'Sound system rental for events', 1, '2025-09-13 18:43:53'),
(4, 'Tables and Chairs', 'Tables and chairs rental for events', 1, '2025-09-13 18:43:53');

-- --------------------------------------------------------

--
-- Table structure for table `viewer_tokens`
--

CREATE TABLE `viewer_tokens` (
  `id` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `request_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `announcement_images`
--
ALTER TABLE `announcement_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_id` (`announcement_id`);

--
-- Indexes for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `document_qr_codes`
--
ALTER TABLE `document_qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_code` (`qr_code`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_request_resident` (`resident_id`),
  ADD KEY `fk_request_type` (`document_type_id`),
  ADD KEY `fk_document_requests_processed_by` (`processed_by`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `household_structure`
--
ALTER TABLE `household_structure`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `household_id` (`household_id`),
  ADD KEY `head_resident_id` (`head_resident_id`);

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
-- Indexes for table `service_reservations`
--
ALTER TABLE `service_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_service_resident` (`resident_id`),
  ADD KEY `fk_service_processed_by` (`processed_by`);

--
-- Indexes for table `service_reservation_items`
--
ALTER TABLE `service_reservation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reservation_item` (`reservation_id`),
  ADD KEY `fk_service_type` (`service_type_id`);

--
-- Indexes for table `service_types`
--
ALTER TABLE `service_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `viewer_tokens`
--
ALTER TABLE `viewer_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `request_id` (`request_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `announcement_images`
--
ALTER TABLE `announcement_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `document_qr_codes`
--
ALTER TABLE `document_qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `household_structure`
--
ALTER TABLE `household_structure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `service_reservations`
--
ALTER TABLE `service_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `service_reservation_items`
--
ALTER TABLE `service_reservation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `service_types`
--
ALTER TABLE `service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `viewer_tokens`
--
ALTER TABLE `viewer_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcement_images`
--
ALTER TABLE `announcement_images`
  ADD CONSTRAINT `fk_announcement_images` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_qr_codes`
--
ALTER TABLE `document_qr_codes`
  ADD CONSTRAINT `fk_qr_request` FOREIGN KEY (`request_id`) REFERENCES `document_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD CONSTRAINT `fk_document_requests_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_request_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_request_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`),
  ADD CONSTRAINT `fk_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `household_structure`
--
ALTER TABLE `household_structure`
  ADD CONSTRAINT `household_structure_ibfk_1` FOREIGN KEY (`head_resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  ADD CONSTRAINT `resident_accounts_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resident_accounts_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_reservations`
--
ALTER TABLE `service_reservations`
  ADD CONSTRAINT `fk_service_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_service_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_reservation_items`
--
ALTER TABLE `service_reservation_items`
  ADD CONSTRAINT `fk_reservation_item` FOREIGN KEY (`reservation_id`) REFERENCES `service_reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_service_type` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `viewer_tokens`
--
ALTER TABLE `viewer_tokens`
  ADD CONSTRAINT `viewer_tokens_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `document_requests` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
