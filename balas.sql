-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 05, 2025 at 07:52 PM
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
(191, 1, 'Logged in as Admin', '2025-10-06 01:30:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36');

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
  `role` enum('Admin','Official') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `first_name`, `last_name`, `middle_name`, `email`, `contact_number`, `position`, `photo_path`, `status`, `created_at`, `updated_at`, `last_login`, `role`) VALUES
(1, 'administrator', '$2y$10$rUKGC0CbAREdpcyydH2EwesD5R5WNgexhfUBiuJwIg0.bMeayFs1a', 'Admin', 'Account', '', 'barangaybalas@gmail.com', '09999999999', 'Barangay Secretary', 'uploads/profile_photos/profile_1_1759683016.png', 'Active', '2025-08-04 04:20:14', '2025-10-05 17:30:33', '2025-10-06 01:30:33', 'Admin'),
(4, 'mlorenzo', '$2y$10$vmFUayGWQI.07Tg21TBN8e.FzH2dXFk8qisrUVp32M5/8GkwNK6AS', 'Marvin', 'Lorenzo', 'Chan', 'marvin@gmail.com', '09123456788', 'Barangay Captain', NULL, 'Active', '2025-10-01 04:43:12', '2025-10-05 05:51:21', '2025-10-01 17:59:39', 'Official'),
(8, 'jtolentino', '$2y$10$FtqglK38/415f0.HY/rMtuduBzwEm3VFitcpzpJ41ETxuTfICToNO', 'Jarshane', 'Tolentinoo', '', 'jar@hotmail.com', '09123456783', 'Barangay Kagawad', NULL, 'Active', '2025-10-01 12:22:27', '2025-10-05 05:51:00', '2025-10-02 18:28:13', 'Official'),
(9, 'jquinto', '$2y$10$Z8Ory2g4rC01pWYnVDgX.O.2D1qnFHwDsrc5LL/UCHXuYc1y1ljS2', 'John Kyle', 'Quinto', '', 'johnkyle@gmail.com', '09999999991', 'Barangay Kagawad', NULL, 'Active', '2025-10-05 16:36:53', '2025-10-05 16:38:08', '2025-10-06 00:38:08', 'Official');

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
(17, 'Test 6', 'Content', '2025-09-22 10:33:02', 1, '2025-09-22 02:33:02', NULL),
(18, 'Test 7', 'content', '2025-10-06 00:35:32', 1, '2025-10-05 16:35:32', NULL);

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
(17, 15, 'uploads/announcements/1758507736_68d0b2d8726ff.png', '2025-09-22 02:22:16'),
(18, 18, 'uploads/announcements/1759682132_68e29e545f14e.png', '2025-10-05 16:35:32');

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
  `date_requested` date NOT NULL DEFAULT current_timestamp(),
  `date_processed` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `document_file_path` varchar(255) DEFAULT NULL
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
(1, 'Indigency'),
(2, 'Clearance'),
(3, 'Residency'),
(4, 'Business Permit');

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
(48, 'Jarshane', 'Tolentino', 'Sigua', '', 'female', '2003-11-21', 21, '', NULL, NULL, NULL, NULL, 0, 0, NULL, '0987654321', 'jars@gmail.com', '409', 'Purok 3', NULL, 'House 409, Purok 3, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id_68e2a89679be1.png', '2025-10-05 17:19:18', '2025-10-05 17:19:18'),
(49, 'John ', 'Quinto', 'Magpayo', '', 'male', '2003-07-29', 22, '', NULL, NULL, NULL, NULL, 0, 0, NULL, '12345678978', 'kyle@gmail.com', '409', 'Purok 3', NULL, 'House 409, Purok 3, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id_68e2ab2c0abb3.png', '2025-10-05 17:30:20', '2025-10-05 17:30:20');

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
(44, 48, 'jars@gmail.com', '$2y$10$PCFW7ANo9KvEkiUNNgTFXeLIb8rkigAsp6YzDgkzZ9/Qszk7POTri', 'Approved', 1, '2025-10-06 01:27:45', '', '2025-10-06 01:19:18'),
(45, 49, 'kyle@gmail.com', '$2y$10$XTqmbRW7CuV37E0w3VtmqOHNh6fkjHG5/jyO5CG32BQyvinFwNQFG', 'Approved', 1, '2025-10-06 01:50:42', '', '2025-10-06 01:30:20');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=192;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `announcement_images`
--
ALTER TABLE `announcement_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `document_qr_codes`
--
ALTER TABLE `document_qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `service_reservations`
--
ALTER TABLE `service_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `service_reservation_items`
--
ALTER TABLE `service_reservation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
