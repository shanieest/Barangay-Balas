-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 03:25 AM
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
-- Table structure for table `account_archive_history`
--

CREATE TABLE `account_archive_history` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `action` enum('archived','restored') NOT NULL,
  `reason` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `performed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_archive_history`
--

INSERT INTO `account_archive_history` (`id`, `resident_id`, `action`, `reason`, `performed_by`, `performed_at`) VALUES
(4, 73, 'archived', 'Automatically archived due to 1 year inactivity', NULL, '2025-10-16 01:07:54'),
(10, 76, 'restored', 'Account restored by admin', 22, '2025-11-02 00:57:09'),
(11, 69, 'restored', 'Account restored by admin', 22, '2025-11-02 01:00:50'),
(12, 69, 'restored', 'Account restored by admin', 22, '2025-11-02 01:02:54'),
(13, 69, 'restored', 'Account restored by admin', 22, '2025-11-02 01:04:48');

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
(533, 79, 'Requested document (ID: 13)', '2025-11-09 08:58:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36');

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
  `committee_position` varchar(100) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `role` enum('Admin','Official','Social Worker') DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `otp_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `first_name`, `last_name`, `middle_name`, `email`, `contact_number`, `position`, `committee_position`, `photo_path`, `status`, `created_at`, `updated_at`, `last_login`, `role`, `otp_code`, `otp_expires_at`, `otp_verified`) VALUES
(11, 'rmanaloto', '$2y$10$azqScqL11i68dY456zQ8yO8CkWFPOYxBkWQa0dg4CIAbImIeskhIO', 'Ronnie', 'Manaloto', 'D.', 'ronnie@gmail.com', '09123456789', 'Barangay Captain', '', 'uploads/profile_photos/profile_11_1760371827.jpg', 'Active', '2025-10-13 14:39:44', '2025-10-13 16:10:27', '2025-10-14 00:08:33', 'Admin', NULL, NULL, 0),
(14, 'rsese', '$2y$10$u7MMNWMihDvDnyY8aUhfuukze4jynbFW1qgICReIw6tiudnA1nYoa', 'Raymond', 'Sese', 'T.', 'raymond@gmail.com', '09123456781', 'Barangay Kagawad', 'Committee in Peace & Order / Public Rules & Ethics / Public Safety, BADAC(Brgy. Anti Drug Abuse Coun', 'uploads/profile_photos/profile_14_1760373026.jpg', 'Active', '2025-10-13 14:51:05', '2025-10-13 16:30:26', '2025-10-14 00:30:09', 'Official', NULL, NULL, 0),
(15, 'jtambo', '$2y$10$3egTSYbSvTRFIr4BTWYLAurJlsUGc46qinM/Wh8jVY/IgYqfPxyH.', 'Jessie', 'Tambo', 'V.', 'jessie@gmail.com', '09123456782', 'Barangay Kagawad', 'Committee on Public Works & Infrastructure / Trade & Industry & Communication', 'uploads/profile_photos/profile_15_1760373075.jpg', 'Active', '2025-10-13 15:06:26', '2025-10-13 16:31:15', '2025-10-14 00:30:57', 'Official', NULL, NULL, 0),
(16, 'ipabalan', '$2y$10$CRvLjHAtp1pF6Fk0bwOeQupGF1U.cfJotnPAdqwcd0d2xwib0NH2W', 'Isagani', 'Pabalan', 'L.', 'isagani@gmail.com', '09123456783', 'Barangay Kagawad', 'Committee on Agriculture & Fisheries Livelihood Cooperative / Program & Economic Enterprise & Employ', 'uploads/profile_photos/profile_16_1760372892.jpg', 'Active', '2025-10-13 15:11:38', '2025-10-13 16:28:12', '2025-10-14 00:27:43', 'Official', NULL, NULL, 0),
(17, 'rpineda', '$2y$10$pTeP8NTsebTZJwBLCmj3puqlxfSQ1Z.eDsctwGa95ic0aCdAPeJ4S', 'Raymond', 'Pineda', 'P.', 'raymondp@gmail.com', '09123456784', 'Barangay Kagawad', 'Committee on Social Welfare Services, Waste & Means, Ecological Solid Waste Management', 'uploads/profile_photos/profile_17_1760372961.jpg', 'Active', '2025-10-13 15:13:25', '2025-10-13 16:29:21', '2025-10-14 00:29:00', 'Official', NULL, NULL, 0),
(18, 'ragad', '$2y$10$d8ZrhARaMeXV5.omzR14huiHHL46O1Z8YYEHZ3P.IKvelIYxsqK0u', 'Raul', 'Agad', 'A.', 'raul@gmail.com', '09123456785', 'Barangay Kagawad', 'Committee on Education, Government Organization & Non Government Organization', 'uploads/profile_photos/profile_18_1760372628.jpg', 'Active', '2025-10-13 15:17:24', '2025-11-03 04:17:20', '2025-11-03 12:17:20', 'Official', NULL, NULL, 0),
(19, 'barcilla', '$2y$10$Af7P0NsECj6/bEqF9gWtOuKKeTJCK8PucFKRK.oDNPzJve.M69.Iq', 'Billy', 'Arcilla', 'P.', 'billy@gmail.com', '09123456786', 'Barangay Kagawad', 'Committee on Health Sanitation / Finance Budget & Appropriation / Tourism, Culture & Affairs', 'uploads/profile_photos/profile_19_1760372717.jpg', 'Active', '2025-10-13 15:20:36', '2025-10-13 16:25:17', '2025-10-14 00:24:35', 'Official', NULL, NULL, 0),
(20, 'mmanaloto', '$2y$10$9gLquAUboKM/lcDV6XaqHOvOdBkLHiyLPRSvkurwmybpKHSuerlGS', 'Monico', 'Manaloto', 'T.', 'monico@gmail.com', '09123564787', 'Barangay Kagawad', 'Committee on Cleanliness & Beautification Environmental Protection / Woman & Family Affairs', 'uploads/profile_photos/profile_20_1760372788.jpg', 'Active', '2025-10-13 15:23:01', '2025-10-13 16:26:28', '2025-10-14 00:25:58', 'Official', NULL, NULL, 0),
(21, 'elenon', '$2y$10$90nxRaifdCVM5quMMR.I/emoawJlvaaS5NwGkDVhO08O2onNNbEKi', 'EJ Ron', 'Lenon', 'Y.', 'ejron@gmail.com', '09123456788', 'SK Chairman', 'Committee on Youth & Sports & Development / Games, Amusement & Enterntainment, Physical Fitness', 'uploads/profile_photos/profile_21_1760373130.jpg', 'Active', '2025-10-13 15:25:28', '2025-10-13 16:32:10', '2025-10-14 00:31:47', 'Official', NULL, NULL, 0),
(22, 'mpangilinan', '$2y$10$Sfoz7xKaOCg6BnKzB7h7zOybECj.QKJxsv7mzMnw7wzM8/DJYU.g.', 'Mercedita', 'Pangilinan', 'M.', 'chixtwix@gmail.com', '09123456799', 'Barangay Secretary', '', 'uploads/profile_photos/profile_22_1762252080.jpg', 'Active', '2025-10-13 15:28:11', '2025-11-08 11:30:15', '2025-11-08 02:53:23', 'Admin', '293817', '2025-11-08 19:34:35', 1),
(23, 'lmaniti', '$2y$10$/aYzw5Sqq3bHDdhDQshhL.Qmm7avHpdn80nd2jXm98UeHNZpzcVQy', 'Loida', 'Maniti', 'J.', 'loida@gmail.com', '09123456773', 'Barangay Treasurer', '', 'uploads/profile_photos/profile_23_1760372434.jpg', 'Active', '2025-10-13 15:29:44', '2025-10-13 16:20:34', '2025-10-14 00:17:37', 'Official', NULL, NULL, 0),
(30, 'jtolentino', '$2y$10$JOqqIgn5Wy1t3AQOti2pvuFI1bry9SfHTa7nH.b3kU8tTCBz0l1mu', 'Jarshanee', 'Tolentino', '', 'jarshanetolentino@gmail.com', '09972353679', 'Daycare Social Worker', NULL, NULL, 'Active', '2025-10-31 02:38:09', '2025-11-08 03:25:31', '2025-11-06 10:37:46', 'Social Worker', '012278', '2025-11-08 11:30:31', 0),
(33, 'bpampanga', '$2y$10$zdWLZzdklNd3H.T9Api7/Ord7WkFe6t7EAT4fOPpY2VcIK7yMu926', 'Balas', 'Pampanga', 'Mexico', 'balasmexico2026@gmail.com', '', 'Other', '', NULL, 'Active', '2025-11-08 02:16:30', '2025-11-08 11:39:37', NULL, 'Admin', '438542', '2025-11-08 19:44:10', 1);

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
(27, 'Balas, Mexico, Pampanga', 'Tysm', '2025-11-05 08:07:19', 22, '2025-11-05 00:07:19', NULL),
(28, 'Balas, Mexico', 'One', '2025-11-05 08:51:38', 22, '2025-11-05 00:51:38', NULL);

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
(23, 27, 'uploads/announcements/1762301239_690a9537b036f.jpg', '2025-11-05 00:07:19'),
(24, 28, 'uploads/announcements/1762303898_690a9f9a14ccc.jpg', '2025-11-05 00:51:38');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_id_applications`
--

CREATE TABLE `barangay_id_applications` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `id_number` varchar(20) DEFAULT NULL,
  `application_date` datetime DEFAULT current_timestamp(),
  `purpose` varchar(255) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL COMMENT 'Path to resident signature image',
  `photo_path` varchar(255) DEFAULT NULL COMMENT 'Path to resident formal photo (2x2)',
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `processed_by` int(11) DEFAULT NULL,
  `date_processed` datetime DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `digital_id_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daycare_enrollments`
--

CREATE TABLE `daycare_enrollments` (
  `id` int(11) NOT NULL,
  `child_first_name` varchar(100) DEFAULT NULL,
  `child_middle_name` varchar(100) DEFAULT NULL,
  `child_last_name` varchar(100) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `guardian` varchar(100) DEFAULT NULL,
  `relationship_to_child` varchar(100) DEFAULT NULL,
  `first_language` varchar(100) DEFAULT NULL,
  `secondary_language` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_address` varchar(255) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `mother_contact` varchar(50) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_address` varchar(255) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `father_contact` varchar(50) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_relationship` varchar(100) DEFAULT NULL,
  `emergency_contact` varchar(50) DEFAULT NULL,
  `emergency_occupation` varchar(100) DEFAULT NULL,
  `school_year` varchar(9) NOT NULL DEFAULT '2025-2026',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daycare_enrollments`
--

INSERT INTO `daycare_enrollments` (`id`, `child_first_name`, `child_middle_name`, `child_last_name`, `sex`, `address`, `birthday`, `confirmed`, `confirmed_by`, `confirmed_at`, `guardian`, `relationship_to_child`, `first_language`, `secondary_language`, `guardian_name`, `email`, `mother_name`, `mother_address`, `mother_occupation`, `mother_contact`, `father_name`, `father_address`, `father_occupation`, `father_contact`, `emergency_name`, `emergency_relationship`, `emergency_contact`, `emergency_occupation`, `school_year`, `created_at`, `updated_at`) VALUES
(5, 'Zion', 'Alvarez', 'Garcia', 'male', '409 prk 3', '2020-02-17', 1, 30, '2025-10-31 14:12:52', 'Jarshane S. Tolentino', 'Step Son', 'English', 'Tagalog', 'Jarshane Tolentino', 'jarshanetolentino@gmail.com', 'Jarshane Tolentino', '409 prk 3', 'Programmer', '09156699382', 'Ethan Garcia', '409 prk 3', 'MedTech', '09156699382', 'Jarshane Tolentino', 'Step son', '09156699382', 'Programmer', '2025-2026', '2025-10-31 06:02:06', '2025-10-31 06:12:52'),
(6, 'Zion', 'Alvarez', 'Garcia', 'male', '409 prk 3', '2020-02-17', 1, 22, '2025-11-03 10:17:24', 'Jarshane S. Tolentino', 'Step Son', 'English', 'Tagalog', 'Jarshane Tolentino', 'jarshanetolentino@gmail.com', 'Jarshane Tolentino', '409 prk 3', 'Programmer', '09156699382', 'Ethan Garcia', '409 prk 3', 'MedTech', '09156699382', 'Jarshane Tolentino', 'Step son', '09156699382', 'Programmer', '2025-2026', '2025-10-31 06:02:46', '2025-11-03 02:17:24'),
(7, 'Zion', 'Alvarez', 'Garcia', 'male', '409 prk 3', '2020-11-17', 1, 30, '2025-11-06 10:38:03', 'Jarshane S. Tolentino', 'Step Son', 'English', 'Tagalog', 'Jarshane Tolentino', 'jarshanetolentino@gmail.com', 'Jarshane Tolentino', '409 prk 3', 'Programmer', '09156699382', 'Ethan Garcia', '409 prk 3', 'MedTech', '09156699382', 'Jarshane Tolentino', 'Son', '09156699382', 'Programmer', '2025-2026', '2025-10-31 06:04:37', '2025-11-06 02:38:03'),
(8, 'Zion', 'Alvarez', 'Garcia', 'male', '409 prk 3', '2020-11-17', 1, 22, '2025-11-06 10:37:18', 'Jarshane S. Tolentino', 'son', 'English', 'Tagalog', 'Jarshane Tolentino', 'jarshanetolentino@gmail.com', 'Jarshane Tolentino', '409 prk 3', 'Programmer', '09156699382', 'Ethan Garcia', '409 prk 3', 'Medtech', '09156699382', 'Jarshane Tolentino', 'Step son', '09156699382', 'Programmer', '2025-2026', '2025-10-31 06:12:29', '2025-11-06 02:37:18'),
(9, 'Jarshane', 'Alvarez', 'Tolentino', 'female', '409 prk 3', '2019-02-11', 1, 30, '2025-11-02 20:21:46', 'Jarshane S. Tolentino', 'Step Son', 'English', 'Tagalog', 'Jarshane Tolentino', 'jarshanetolentino@gmail.com', 'Jarshane Tolentino', '409 prk 3', 'Programmer', '09290380219', 'Ethan Garcia', '409 prk 3', 'MedTech', '09156699382', 'Jarshane Tolentino', 'Step son', '09156699382', 'Programmer', '2025-2026', '2025-10-31 06:19:42', '2025-11-02 12:21:46'),
(10, 'Zion', 'Alvarez', 'Garcia', 'male', 'Balas, Mexico, Pampanga', '2020-11-17', 1, 30, '2025-10-31 14:29:18', 'Jarshane S. Tolentino', 'Step Son', 'English', 'Tagalog', 'Jarshane Tolentino', 'neptwix@gmail.com', 'Jarshane Tolentino', '409 prk 3', 'Programmer', '09156699382', 'Jarshane Tolentino', '409 prk 3', 'MedTech', '09156699382', 'Jarshane Tolentino', 'Step son', '09156699382', 'Programmer', '2025-2026', '2025-10-31 06:27:30', '2025-10-31 06:29:18'),
(11, 'Jaisel', 'Tolentino', 'Henson', 'male', 'Balas, Mexico', '2019-01-20', 1, 22, '2025-11-05 07:33:35', 'Jarlene S. Tolentino', 'Son', 'Filipino', 'Tagalog', 'Jarlene Tolentino', 'jarshanetolentino@gmail.com', 'Jarlene Tolentino', 'Balas, Mexico', 'None', '0983787223', 'Russel Henson', 'Balas, Mexico', 'None', '09374627323', 'Jarlene Tolentino', 'Son', '0972362716', 'None', '2025-2026', '2025-11-04 23:30:16', '2025-11-04 23:33:35'),
(12, 'John Kyle', 'Magpayo', 'Quinto', 'male', '172, Purok 3 Barangay Balas Mexico Pampanga', '2003-07-29', 1, 22, '2025-11-06 09:24:49', 'Joshua Punzalan', 'Tito', 'Tagalog', 'Kapampangan', 'Joshua Punzalan', 'quintojohnkyle@gmail.com', 'Marvin Lorenzo', '172, Purok 3 Barangay Balas Mexico, Pampanga', 'N/A', '09958862532', 'John Loyd Lorenzo', '172, Purok 3 Barangay Balas Mexico, Pampanga', 'N/A', '09958862532', 'Joshua Punzalan', 'Tito', '09958862532', 'N/A', '2025-2026', '2025-11-06 01:22:38', '2025-11-06 01:24:49'),
(13, 'John Kyle', 'Magpayo', 'Quinto', 'male', '172, Purok 3 Barangay Balas Mexico Pampanga', '2003-07-29', 1, 22, '2025-11-06 09:23:32', 'Joshua Punzalan', 'Tito', 'Tagalog', 'Kapampangan', 'Joshua Punzalan', 'quintojohnkyle@gmail.com', 'Marvin Lorenzo', '172, Purok 3 Barangay Balas Mexico, Pampanga', 'N/A', '09958862532', 'John Loyd Lorenzo', '172, Purok 3 Barangay Balas Mexico, Pampanga', 'N/A', '09958862532', 'Joshua Punzalan', 'Tito', '09958862532', 'N/A', '2025-2026', '2025-11-06 01:22:41', '2025-11-06 01:23:32');

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
(249, 181, 'de7b1ed56afaa24afefe437692f8c2fa', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin\\backend/../public/uploads/qr_codes/qr_181_1762649901.png', 0, NULL, '2025-11-09 00:58:21'),
(250, 181, '3ac2ede1257fc429103075f8682094cd', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin\\backend/../public/uploads/qr_codes/qr_181_1762649906.png', 0, NULL, '2025-11-09 00:58:26');

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
  `purpose` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Disapproved','Cancelled') DEFAULT 'Pending',
  `shipping_method` text NOT NULL,
  `date_requested` datetime NOT NULL DEFAULT current_timestamp(),
  `date_processed` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `document_file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_requests`
--

INSERT INTO `document_requests` (`id`, `resident_id`, `document_type_id`, `first_name`, `middle_name`, `last_name`, `houseno`, `purok`, `civil_status`, `sex`, `birthdate`, `age`, `purpose`, `status`, `shipping_method`, `date_requested`, `date_processed`, `processed_by`, `notes`, `document_file_path`) VALUES
(181, 79, 13, 'Ethan', 'Alvarez', 'Garcia', '4009', 'Purok 3', 'Married', 'male', '1999-11-17', 25, 'hngf', 'Approved', '', '2025-11-09 08:58:10', '2025-11-09 00:58:26', 33, '', 'uploads/generated_docs/request_181_1762649906.pdf');

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
(4, 'Self Employed'),
(5, 'Construction Permit'),
(6, 'No Valid ID'),
(12, 'Transfer 4Ps'),
(13, 'No Income'),
(14, 'New Farmer'),
(16, 'Existing Business'),
(17, 'DTI'),
(18, 'DSWD'),
(19, 'No Land Title'),
(21, 'Job Seeker'),
(22, 'Solo Parent'),
(23, 'Bail');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_inventory`
--

CREATE TABLE `medicine_inventory` (
  `id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) NOT NULL DEFAULT 10,
  `unit` varchar(50) DEFAULT 'pcs',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_inventory`
--

INSERT INTO `medicine_inventory` (`id`, `medicine_name`, `category`, `description`, `stock_quantity`, `minimum_stock`, `unit`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Salbutamol', 'Pain', '', 1, 20, 'pcs', 1, '2025-11-02 05:32:29', '2025-11-02 05:32:29'),
(3, 'Asiton', 'Polish', 'Atsuuuu', 11, 0, 'tablets', 1, '2025-11-02 11:23:59', '2025-11-03 05:22:07'),
(4, 'Sisitrizin', 'Kati kati', 'Para sa kati', 4, 2, 'tablets', 1, '2025-11-03 05:21:34', '2025-11-03 05:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_requests`
--

CREATE TABLE `medicine_requests` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `request_number` varchar(20) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `medical_condition` text NOT NULL,
  `urgency_level` enum('low','medium','high','emergency') NOT NULL,
  `prescription_path` varchar(255) DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `status` enum('Pending','Approved','Disapproved','Completed') DEFAULT 'Pending',
  `disapproval_reason` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `date_requested` datetime DEFAULT current_timestamp(),
  `date_processed` datetime DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_requests`
--

INSERT INTO `medicine_requests` (`id`, `resident_id`, `request_number`, `medicine_name`, `medical_condition`, `urgency_level`, `prescription_path`, `additional_notes`, `status`, `disapproval_reason`, `admin_notes`, `date_requested`, `date_processed`, `processed_by`, `created_at`, `updated_at`) VALUES
(7, 79, 'MED-20251107-9030', 'Sisitrizin', 'allergy', 'medium', 'uploads/prescriptions/prescription_79_1762540751.jpeg', '', 'Pending', NULL, NULL, '2025-11-08 02:39:11', NULL, NULL, '2025-11-07 18:39:11', '2025-11-07 18:39:11');

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
  `place_of_birth` varchar(255) DEFAULT NULL,
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
  `valid_id_path_2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `first_name`, `last_name`, `middle_name`, `suffix`, `sex`, `birthdate`, `place_of_birth`, `age`, `civil_status`, `educational_attainment`, `religion`, `occupation`, `philhealth_number`, `is_indigent`, `is_4ps_member`, `medical_history`, `contact_number`, `email`, `house_number`, `purok`, `relationship_to_head`, `address`, `verification_status`, `resident_status`, `photo_path`, `valid_id_path`, `valid_id_path_2`, `created_at`, `updated_at`) VALUES
(64, 'juan', 'dianelo', 'dela cruz', '', 'male', '1984-04-20', NULL, 41, 'Single', 'College Graduate', '', '', '', 1, 1, '', '09063659029', 'fdianelosanfernando@gmail.com', '88', '1', 'Head of Household', 'kugjgnga', 'Pending', 'Active', NULL, 'uploads/valid_ids/id1_68ec6b8d7e04b.png', 'uploads/valid_ids/id2_68ec6b8d7ee83.png', '2025-10-13 03:01:33', '2025-10-13 03:15:42'),
(69, 'Maria', 'Santos', 'R.', NULL, 'female', '1985-03-15', NULL, 40, 'Married', NULL, NULL, NULL, NULL, 0, 0, NULL, '09171111111', 'maria.santos@test.com', '101', '1', NULL, 'House 101, Purok 1, Balas, Mexico, Pampanga', 'Verified', 'Active', NULL, NULL, NULL, '2025-10-15 17:07:06', '2025-10-15 17:07:06'),
(70, 'Juan', 'Cruz', 'M.', NULL, 'male', '1990-07-20', NULL, 34, 'Single', NULL, NULL, NULL, NULL, 0, 0, NULL, '09172222222', 'juan.cruz@test.com', '202', '2', NULL, 'House 202, Purok 2, Balas, Mexico, Pampanga', 'Verified', 'Active', NULL, NULL, NULL, '2025-10-15 17:07:06', '2025-10-15 17:07:06'),
(73, 'Sofia', 'Lopez', 'T.', NULL, 'female', '1987-12-25', NULL, 37, 'Married', NULL, NULL, NULL, NULL, 0, 0, NULL, '09175555555', 'sofia.lopez@test.com', '505', '5', NULL, 'House 505, Purok 5, Balas, Mexico, Pampanga', 'Verified', 'Active', NULL, NULL, NULL, '2025-10-15 17:07:06', '2025-10-15 17:07:06'),
(76, 'John Kyle', 'Quinto', 'Magpayo', '', 'male', '2003-07-29', NULL, 22, '', NULL, NULL, NULL, NULL, 0, 0, NULL, '09958862532', 'quintojohnkyle@gmail.com', '172', 'Purok 3', 'Head of Household', 'House 172, Purok 3, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id1_68f76ff3a08da.JPG', 'uploads/valid_ids/id2_68f76ff3a226b.JPG', '2025-10-21 11:35:15', '2025-10-21 11:49:23'),
(79, 'Ethan', 'Garcia', 'Alvarez', '', 'male', '1999-11-17', 'Fblanca', 25, 'Married', '', '', '', '0', 0, 0, '', '09221984532', 'neptwix@gmail.com', '4009', 'Purok 3', 'Head of Household', '4009, Purok Purok 3, Barangay Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id1_690dc387ccd0e.png', 'uploads/valid_ids/id2_690dc387cd8d4.jpg', '2025-11-07 10:01:43', '2025-11-07 17:33:26'),
(80, 'Jarshane', 'Garcia', '', '', 'female', '2003-11-21', 'Fblanca', 21, 'Single', '', '', '', '0', 0, 0, '', '09221984532', 'jarshanetolentino@gmail.com', '4009', 'Purok 3', 'Spouse', 'House 4009, Purok 3, Balas, Mexico, Pampanga, Philippines', 'Unverified', 'Active', 'uploads/profile_photos/profile_80_1762531366.png', NULL, NULL, '2025-11-07 10:37:33', '2025-11-07 16:02:46'),
(81, 'Zion', 'Alvarez', 'Alvarez', '', 'male', '2020-04-30', NULL, 5, '', NULL, NULL, NULL, NULL, 0, 0, NULL, '11111111111', 'jarshanethesecond@gmail.com', '4009', 'Purok 3', 'Son', 'House 4009, Purok 3, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id1_690dcf1b5267f.png', 'uploads/valid_ids/id2_690dcf1b53088.jpg', '2025-11-07 10:51:07', '2025-11-07 13:41:07');

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
  `last_login` datetime DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `archived_reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `date_requested` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_accounts`
--

INSERT INTO `resident_accounts` (`id`, `resident_id`, `email`, `password`, `account_status`, `processed_by`, `date_processed`, `last_login`, `is_archived`, `archived_at`, `archived_reason`, `notes`, `date_requested`) VALUES
(60, 64, 'fdianelosanfernando@gmail.com', '$2y$10$AaoHH885U/S9/Z60zXpLxekldd3.TkekKf1D2I2VDNr35EOraB0f2', 'Approved', 22, '2025-10-13 11:02:15', '2025-10-14 17:10:51', 0, NULL, NULL, '', '2025-10-13 11:01:33'),
(65, 69, 'maria.santos@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Approved', NULL, '2022-10-05 01:07:06', '2023-11-01 01:02:29', 0, NULL, NULL, NULL, '2025-10-16 01:07:06'),
(66, 70, 'juan.cruz@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Disapproved', 22, '2022-10-12 14:57:35', '2023-11-01 01:00:01', 0, NULL, NULL, NULL, '2025-10-16 01:07:06'),
(72, 76, 'quintojohnkyle@gmail.com', '$2y$10$KoVjNhXdvk2DruJ308MC9un/PnVrXgUgkX0CiZ8oI0tMbqE6m6f7C', 'Approved', 22, '2020-10-06 19:36:36', '2025-11-06 13:26:53', 0, NULL, NULL, 'eme mo\r\n', '2025-10-21 19:35:15'),
(75, 79, 'neptwix@gmail.com', '$2y$10$Ev.ImRuA0/DwpSQN3ZBVEOajmLTQ098FU8SvxuCyc/X38jSK1S7mu', 'Approved', 22, '2025-11-07 18:18:15', '2025-11-08 19:59:15', 0, NULL, NULL, '', '2025-11-07 18:01:43'),
(76, 80, 'jarshanetolentino@gmail.com', '$2y$10$3tMC4LyQ.jO8kjXGI/64Q.EgR6wAGwTTFU76NyAY6RYxhcqSxe2de', 'Approved', 22, '2025-11-07 18:37:33', '2025-11-07 23:52:00', 0, NULL, NULL, NULL, '2025-11-07 18:37:33'),
(77, 81, 'jarshanethesecond@gmail.com', '$2y$10$1dk2bUTCnVgWhIdZbR0yyu8d3NzOKrSMgnngN.wL4Wd6dGgDQUcSS', 'Pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2025-11-07 18:51:07');

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
  `reservation_date_start` datetime NOT NULL,
  `reservation_date_end` datetime DEFAULT NULL,
  `duration_days` int(11) NOT NULL DEFAULT 1,
  `purpose` text NOT NULL,
  `status` enum('Pending','Approved','In Progress','Completed','Cancelled','Disapproved') DEFAULT 'Pending',
  `scheduled_datetime` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `date_requested` datetime NOT NULL DEFAULT current_timestamp(),
  `date_processed` datetime DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_reservations`
--

INSERT INTO `service_reservations` (`id`, `resident_id`, `resident_name`, `contact_number`, `email`, `reservation_date_start`, `reservation_date_end`, `duration_days`, `purpose`, `status`, `scheduled_datetime`, `notes`, `rejection_reason`, `date_requested`, `date_processed`, `processed_by`, `created_at`, `updated_at`) VALUES
(41, 79, 'Ethan Garcia', '09221984532', 'neptwix@gmail.com', '2025-11-11 00:00:00', '2025-11-11 00:00:00', 1, 'Date', 'Approved', NULL, 'kiXzs ko Bhie', NULL, '2025-11-08 01:34:08', '2025-11-08 01:35:19', 22, '2025-11-07 17:34:08', '2025-11-07 17:35:19');

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
(52, 41, 1, 1, NULL);

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
(2, 'Patrol Car', 'Vehicle rental for transportation needs', 1, '2025-09-13 18:43:53'),
(3, 'Sound System', 'Sound system rental for events', 1, '2025-09-13 18:43:53'),
(4, 'Tables and Chairs', 'Tables and chairs rental for events', 1, '2025-09-13 18:43:53'),
(5, 'Ambulance', 'Vehicle rental for transportation needs', 1, '2025-10-20 14:51:51'),
(6, 'Motorcycle', 'Vehicle rental for transportation needs', 1, '2025-10-20 14:51:51');

-- --------------------------------------------------------

--
-- Table structure for table `social_workers`
--

CREATE TABLE `social_workers` (
  `id` int(11) NOT NULL,
  `admin_user_id` int(11) NOT NULL,
  `department` varchar(100) DEFAULT 'Daycare Center',
  `license_number` varchar(50) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `email_signature` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `social_workers`
--

INSERT INTO `social_workers` (`id`, `admin_user_id`, `department`, `license_number`, `specialization`, `email_signature`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 30, 'Daycare Center', NULL, 'Titser', NULL, 1, '2025-10-31 02:38:09', '2025-10-31 08:54:10');

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
-- Indexes for table `account_archive_history`
--
ALTER TABLE `account_archive_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_archive_resident` (`resident_id`),
  ADD KEY `fk_archive_admin` (`performed_by`),
  ADD KEY `idx_resident_action` (`resident_id`,`action`),
  ADD KEY `idx_performed_at` (`performed_at`);

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
-- Indexes for table `barangay_id_applications`
--
ALTER TABLE `barangay_id_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD KEY `fk_barangay_id_resident` (`resident_id`),
  ADD KEY `fk_barangay_id_processed_by` (`processed_by`),
  ADD KEY `idx_status_validity` (`status`,`valid_until`),
  ADD KEY `idx_photo_path` (`photo_path`);

--
-- Indexes for table `daycare_enrollments`
--
ALTER TABLE `daycare_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_daycare_confirmed_by` (`confirmed_by`),
  ADD KEY `idx_school_year` (`school_year`),
  ADD KEY `idx_confirmed_status` (`confirmed`,`school_year`);

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
-- Indexes for table `medicine_inventory`
--
ALTER TABLE `medicine_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `medicine_name` (`medicine_name`);

--
-- Indexes for table `medicine_requests`
--
ALTER TABLE `medicine_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_number` (`request_number`),
  ADD KEY `fk_medicine_resident` (`resident_id`),
  ADD KEY `fk_medicine_processed_by` (`processed_by`),
  ADD KEY `idx_status_urgency` (`status`,`urgency_level`);

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
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `idx_is_archived` (`is_archived`),
  ADD KEY `idx_last_login` (`last_login`),
  ADD KEY `idx_archive_check` (`account_status`,`is_archived`,`last_login`,`date_processed`),
  ADD KEY `idx_archive_status` (`account_status`,`is_archived`,`last_login`),
  ADD KEY `idx_date_processed` (`date_processed`),
  ADD KEY `idx_archived_at` (`archived_at`);

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
-- Indexes for table `social_workers`
--
ALTER TABLE `social_workers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_user_id` (`admin_user_id`);

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
-- AUTO_INCREMENT for table `account_archive_history`
--
ALTER TABLE `account_archive_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=534;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `announcement_images`
--
ALTER TABLE `announcement_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `barangay_id_applications`
--
ALTER TABLE `barangay_id_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `daycare_enrollments`
--
ALTER TABLE `daycare_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `document_qr_codes`
--
ALTER TABLE `document_qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `medicine_inventory`
--
ALTER TABLE `medicine_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medicine_requests`
--
ALTER TABLE `medicine_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `service_reservations`
--
ALTER TABLE `service_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `service_reservation_items`
--
ALTER TABLE `service_reservation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `service_types`
--
ALTER TABLE `service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `social_workers`
--
ALTER TABLE `social_workers`
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
-- Constraints for table `account_archive_history`
--
ALTER TABLE `account_archive_history`
  ADD CONSTRAINT `fk_archive_admin` FOREIGN KEY (`performed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_archive_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `barangay_id_applications`
--
ALTER TABLE `barangay_id_applications`
  ADD CONSTRAINT `fk_barangay_id_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_barangay_id_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `daycare_enrollments`
--
ALTER TABLE `daycare_enrollments`
  ADD CONSTRAINT `fk_daycare_confirmed_by` FOREIGN KEY (`confirmed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `medicine_requests`
--
ALTER TABLE `medicine_requests`
  ADD CONSTRAINT `fk_medicine_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_medicine_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `social_workers`
--
ALTER TABLE `social_workers`
  ADD CONSTRAINT `fk_social_worker_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `viewer_tokens`
--
ALTER TABLE `viewer_tokens`
  ADD CONSTRAINT `viewer_tokens_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `document_requests` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
