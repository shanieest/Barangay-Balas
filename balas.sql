-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2025 at 10:44 AM
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
(4, 73, 'archived', 'Automatically archived due to 1 year inactivity', NULL, '2025-10-16 01:07:54');

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
(191, 1, 'Logged in as Admin', '2025-10-06 01:30:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(192, 1, 'Logged in as Admin', '2025-10-06 08:12:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(193, 48, 'Updated profile photo.', '2025-10-06 08:13:27', NULL, NULL),
(194, 1, 'Logged in as Admin', '2025-10-06 09:15:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(195, 48, 'Requested document (ID: 1)', '2025-10-06 09:35:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(196, 1, 'Logged in as Admin', '2025-10-06 09:42:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(197, 1, 'Approved service reservation (ID: 17)', '2025-10-06 09:43:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(198, 1, 'Logged in as Admin', '2025-10-06 10:03:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(199, 1, 'Logged in as Admin', '2025-10-06 10:22:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(200, 1, 'Logged in as Admin', '2025-10-06 10:25:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(201, 1, 'Logged in as Admin', '2025-10-06 10:34:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(202, 1, 'Logged in as Admin', '2025-10-07 11:02:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(203, 1, 'Logged in as Admin', '2025-10-09 11:06:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(204, 1, 'Logged in as Admin', '2025-10-09 16:25:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(205, 1, 'Logged in as Admin', '2025-10-10 22:52:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(206, 1, 'Logged in as Admin', '2025-10-10 23:29:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(207, 60, 'Requested document (ID: 3)', '2025-10-10 23:34:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(208, 60, 'Requested document (ID: 3)', '2025-10-10 23:42:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(209, 1, 'Approved service reservation (ID: 18)', '2025-10-11 00:33:07', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0'),
(210, 1, 'Updated service reservation status to \'In Progress\' (ID: 18)', '2025-10-11 00:33:54', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0'),
(211, 1, 'Updated service reservation status to \'Completed\' (ID: 18)', '2025-10-11 00:34:44', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0'),
(212, 1, 'Logged in as Admin', '2025-10-11 01:01:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(213, 1, 'Logged in as Admin', '2025-10-12 23:50:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(214, 63, 'Requested document (ID: 3)', '2025-10-12 23:55:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(215, 1, 'Approved service reservation (ID: 19)', '2025-10-12 23:58:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(216, 1, 'Updated service reservation status to \'In Progress\' (ID: 19)', '2025-10-12 23:58:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(217, 1, 'Updated service reservation status to \'Completed\' (ID: 19)', '2025-10-12 23:59:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(218, 1, 'Approved service reservation (ID: 20)', '2025-10-13 00:00:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(219, 63, 'Updated profile photo.', '2025-10-13 00:04:54', NULL, NULL),
(220, 1, 'Logged in as Admin', '2025-10-13 00:05:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(221, 1, 'Approved service reservation (ID: 21)', '2025-10-13 00:34:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(222, 1, 'Logged in as Admin', '2025-10-13 10:41:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(223, 64, 'Updated profile information.', '2025-10-13 11:14:08', NULL, NULL),
(224, 64, 'Updated profile information.', '2025-10-13 11:15:42', NULL, NULL),
(225, 64, 'Requested document (ID: 1)', '2025-10-13 11:21:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(226, 1, 'Logged in as Admin', '2025-10-13 19:03:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(227, 1, 'Logged in as Admin', '2025-10-13 21:35:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(228, 1, 'Logged in as Admin', '2025-10-13 21:38:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(229, 10, 'Logged in as Admin', '2025-10-13 21:39:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(230, 10, 'Logged in as Official', '2025-10-13 21:40:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(231, 1, 'Logged in as Admin', '2025-10-13 22:01:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(232, 11, 'Logged in as Admin', '2025-10-14 00:08:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(233, 22, 'Logged in as Admin', '2025-10-14 00:16:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(234, 23, 'Logged in as Official', '2025-10-14 00:17:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(235, 18, 'Logged in as Official', '2025-10-14 00:23:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(236, 19, 'Logged in as Official', '2025-10-14 00:24:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(237, 20, 'Logged in as Official', '2025-10-14 00:25:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(238, 16, 'Logged in as Official', '2025-10-14 00:27:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(239, 17, 'Logged in as Official', '2025-10-14 00:29:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(240, 14, 'Logged in as Official', '2025-10-14 00:30:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(241, 15, 'Logged in as Official', '2025-10-14 00:30:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(242, 21, 'Logged in as Official', '2025-10-14 00:31:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(243, 22, 'Logged in as Admin', '2025-10-14 00:35:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(244, 22, 'Approved service reservation (ID: 22)', '2025-10-14 12:29:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(245, 22, 'Logged in as Admin', '2025-10-14 16:48:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(246, 64, 'Requested document (ID: 3)', '2025-10-14 17:11:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(247, 64, 'Requested document (ID: 1)', '2025-10-14 17:13:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(248, 22, 'Logged in as Admin', '2025-10-14 22:04:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(249, 65, 'Updated profile photo.', '2025-10-14 22:45:16', NULL, NULL),
(250, 22, 'Approved service reservation (ID: 25)', '2025-10-14 22:57:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(251, 22, 'Logged in as Admin', '2025-10-15 00:05:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(252, 22, 'Approved service reservation (ID: 26)', '2025-10-15 00:08:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(253, 22, 'Updated service reservation status to \'Cancelled\' (ID: 26)', '2025-10-15 00:10:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(254, 22, 'Disapproved service reservation (ID: 24)', '2025-10-15 00:10:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(255, 22, 'Updated service reservation status to \'In Progress\' (ID: 25)', '2025-10-15 00:18:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(256, 65, 'Requested document (ID: 1)', '2025-10-15 00:51:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(257, 22, 'Logged in as Admin', '2025-10-15 09:33:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(258, 22, 'Updated service reservation status to \'Completed\' (ID: 25)', '2025-10-15 09:37:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(259, 22, 'Logged in as Admin', '2025-10-15 12:18:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(260, 22, 'Logged in as Admin', '2025-10-15 12:20:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(261, 22, 'Logged in as Admin', '2025-10-15 15:50:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(262, 22, 'Approved service reservation (ID: 27)', '2025-10-15 16:10:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(263, 65, 'Requested document (ID: 1)', '2025-10-15 16:58:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(264, 22, 'Logged in as Admin', '2025-10-15 19:00:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(265, 22, 'Logged in as Admin', '2025-10-15 19:23:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(266, 22, 'Logged in as Admin', '2025-10-15 21:37:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(267, 22, 'Updated service reservation status to \'In Progress\' (ID: 27)', '2025-10-16 00:17:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(268, 65, 'Updated profile information.', '2025-10-16 01:38:57', NULL, NULL),
(269, 65, 'Updated profile information.', '2025-10-16 01:39:06', NULL, NULL),
(270, 22, 'Logged in as Admin', '2025-10-16 18:28:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(271, 65, 'Requested document (ID: 3)', '2025-10-16 19:29:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(272, 65, 'Requested document (ID: 1)', '2025-10-17 01:03:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(273, 65, 'Requested document (ID: 3)', '2025-10-17 01:24:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(274, 65, 'Requested document (ID: 3)', '2025-10-17 01:36:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(275, 65, 'Requested document (ID: 3)', '2025-10-17 01:42:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(276, 65, 'Requested document (ID: 1)', '2025-10-17 02:12:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(277, 65, 'Requested document (ID: 1)', '2025-10-17 02:29:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(278, 65, 'Requested document (ID: 1)', '2025-10-17 03:08:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(279, 65, 'Requested document (ID: 1)', '2025-10-17 03:43:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(280, 65, 'Requested document (ID: 1)', '2025-10-17 03:44:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(281, 65, 'Requested document (ID: 3)', '2025-10-17 04:00:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(282, 22, 'Logged in as Admin', '2025-10-18 12:15:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(283, 22, 'Logged in as Admin', '2025-10-18 14:22:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(284, 22, 'Logged in as Admin', '2025-10-18 14:55:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(285, 22, 'Logged in as Admin', '2025-10-18 14:55:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(286, 22, 'Logged in as Admin', '2025-10-18 16:36:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(287, 22, 'Logged in as Admin', '2025-10-18 18:22:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(288, 65, 'Requested document (ID: 23)', '2025-10-18 18:24:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(289, 22, 'Logged in as Admin', '2025-10-18 18:24:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(290, 22, 'Logged in as Admin', '2025-10-18 20:30:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(291, 65, 'Updated profile information.', '2025-10-18 20:32:17', NULL, NULL),
(292, 22, 'Logged in as Admin', '2025-10-18 20:37:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(293, 74, 'Requested document (ID: 12)', '2025-10-18 20:40:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(294, 22, 'Logged in as Admin', '2025-10-18 21:03:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(295, 22, 'Logged in as Admin', '2025-10-19 14:27:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(296, 75, 'Requested document (ID: 3)', '2025-10-19 15:09:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(297, 22, 'Approved service reservation (ID: 28)', '2025-10-19 15:23:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(298, 22, 'Logged in as Admin', '2025-10-19 22:05:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(299, 75, 'Requested document (ID: 2)', '2025-10-19 22:14:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(300, 75, 'Requested document (ID: 17)', '2025-10-19 22:30:18', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36'),
(301, 22, 'Logged in as Admin', '2025-10-20 19:42:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(302, 74, 'Requested document (ID: 4)', '2025-10-20 19:43:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(303, 74, 'Updated profile information.', '2025-10-20 19:53:17', NULL, NULL),
(304, 74, 'Requested document (ID: 13)', '2025-10-20 19:54:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(305, 22, 'Logged in as Admin', '2025-10-21 16:29:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(306, 22, 'Logged in as Admin', '2025-10-21 16:40:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(307, 22, 'Logged in as Admin', '2025-10-21 16:42:51', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Safari/605.1.15 Edg/141.0.0.0');

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
  `role` enum('Admin','Official') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `first_name`, `last_name`, `middle_name`, `email`, `contact_number`, `position`, `committee_position`, `photo_path`, `status`, `created_at`, `updated_at`, `last_login`, `role`) VALUES
(11, 'rmanaloto', '$2y$10$azqScqL11i68dY456zQ8yO8CkWFPOYxBkWQa0dg4CIAbImIeskhIO', 'Ronnie', 'Manaloto', 'D.', 'ronnie@gmail.com', '09123456789', 'Barangay Captain', '', 'uploads/profile_photos/profile_11_1760371827.jpg', 'Active', '2025-10-13 14:39:44', '2025-10-13 16:10:27', '2025-10-14 00:08:33', 'Admin'),
(14, 'rsese', '$2y$10$u7MMNWMihDvDnyY8aUhfuukze4jynbFW1qgICReIw6tiudnA1nYoa', 'Raymond', 'Sese', 'T.', 'raymond@gmail.com', '09123456781', 'Barangay Kagawad', 'Committee in Peace & Order / Public Rules & Ethics / Public Safety, BADAC(Brgy. Anti Drug Abuse Coun', 'uploads/profile_photos/profile_14_1760373026.jpg', 'Active', '2025-10-13 14:51:05', '2025-10-13 16:30:26', '2025-10-14 00:30:09', 'Official'),
(15, 'jtambo', '$2y$10$3egTSYbSvTRFIr4BTWYLAurJlsUGc46qinM/Wh8jVY/IgYqfPxyH.', 'Jessie', 'Tambo', 'V.', 'jessie@gmail.com', '09123456782', 'Barangay Kagawad', 'Committee on Public Works & Infrastructure / Trade & Industry & Communication', 'uploads/profile_photos/profile_15_1760373075.jpg', 'Active', '2025-10-13 15:06:26', '2025-10-13 16:31:15', '2025-10-14 00:30:57', 'Official'),
(16, 'ipabalan', '$2y$10$CRvLjHAtp1pF6Fk0bwOeQupGF1U.cfJotnPAdqwcd0d2xwib0NH2W', 'Isagani', 'Pabalan', 'L.', 'isagani@gmail.com', '09123456783', 'Barangay Kagawad', 'Committee on Agriculture & Fisheries Livelihood Cooperative / Program & Economic Enterprise & Employ', 'uploads/profile_photos/profile_16_1760372892.jpg', 'Active', '2025-10-13 15:11:38', '2025-10-13 16:28:12', '2025-10-14 00:27:43', 'Official'),
(17, 'rpineda', '$2y$10$pTeP8NTsebTZJwBLCmj3puqlxfSQ1Z.eDsctwGa95ic0aCdAPeJ4S', 'Raymond', 'Pineda', 'P.', 'raymondp@gmail.com', '09123456784', 'Barangay Kagawad', 'Committee on Social Welfare Services, Waste & Means, Ecological Solid Waste Management', 'uploads/profile_photos/profile_17_1760372961.jpg', 'Active', '2025-10-13 15:13:25', '2025-10-13 16:29:21', '2025-10-14 00:29:00', 'Official'),
(18, 'ragad', '$2y$10$d8ZrhARaMeXV5.omzR14huiHHL46O1Z8YYEHZ3P.IKvelIYxsqK0u', 'Raul', 'Agad', 'A.', 'raul@gmail.com', '09123456785', 'Barangay Kagawad', 'Committee on Education, Government Organization & Non Government Organization', 'uploads/profile_photos/profile_18_1760372628.jpg', 'Active', '2025-10-13 15:17:24', '2025-10-13 16:23:48', '2025-10-14 00:23:10', 'Official'),
(19, 'barcilla', '$2y$10$Af7P0NsECj6/bEqF9gWtOuKKeTJCK8PucFKRK.oDNPzJve.M69.Iq', 'Billy', 'Arcilla', 'P.', 'billy@gmail.com', '09123456786', 'Barangay Kagawad', 'Committee on Health Sanitation / Finance Budget & Appropriation / Tourism, Culture & Affairs', 'uploads/profile_photos/profile_19_1760372717.jpg', 'Active', '2025-10-13 15:20:36', '2025-10-13 16:25:17', '2025-10-14 00:24:35', 'Official'),
(20, 'mmanaloto', '$2y$10$9gLquAUboKM/lcDV6XaqHOvOdBkLHiyLPRSvkurwmybpKHSuerlGS', 'Monico', 'Manaloto', 'T.', 'monico@gmail.com', '09123564787', 'Barangay Kagawad', 'Committee on Cleanliness & Beautification Environmental Protection / Woman & Family Affairs', 'uploads/profile_photos/profile_20_1760372788.jpg', 'Active', '2025-10-13 15:23:01', '2025-10-13 16:26:28', '2025-10-14 00:25:58', 'Official'),
(21, 'elenon', '$2y$10$90nxRaifdCVM5quMMR.I/emoawJlvaaS5NwGkDVhO08O2onNNbEKi', 'EJ Ron', 'Lenon', 'Y.', 'ejron@gmail.com', '09123456788', 'SK Chairman', 'Committee on Youth & Sports & Development / Games, Amusement & Enterntainment, Physical Fitness', 'uploads/profile_photos/profile_21_1760373130.jpg', 'Active', '2025-10-13 15:25:28', '2025-10-13 16:32:10', '2025-10-14 00:31:47', 'Official'),
(22, 'mpangilinan', '$2y$10$Sfoz7xKaOCg6BnKzB7h7zOybECj.QKJxsv7mzMnw7wzM8/DJYU.g.', 'Mercedita', 'Pangilinan', 'M.', 'mercedita@gmail.com', '09123456799', 'Barangay Secretary', '', 'uploads/profile_photos/profile_22_1760372176.jpg', 'Active', '2025-10-13 15:28:11', '2025-10-21 08:42:51', '2025-10-21 16:42:51', 'Admin'),
(23, 'lmaniti', '$2y$10$/aYzw5Sqq3bHDdhDQshhL.Qmm7avHpdn80nd2jXm98UeHNZpzcVQy', 'Loida', 'Maniti', 'J.', 'loida@gmail.com', '09123456773', 'Barangay Treasurer', '', 'uploads/profile_photos/profile_23_1760372434.jpg', 'Active', '2025-10-13 15:29:44', '2025-10-13 16:20:34', '2025-10-14 00:17:37', 'Official');

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
(115, 132, '4d3dee5caf97f9efb4601b8444a3f15b', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_132_1760327270.png', 0, NULL, '2025-10-13 03:47:50'),
(116, 132, 'd5150d7b31d0efafb89ad6921af86306', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_132_1760327282.png', 0, NULL, '2025-10-13 03:48:03'),
(117, 135, '6357b531d4796601442ab867eece60c9', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_135_1760460688.png', 0, NULL, '2025-10-14 16:51:28'),
(118, 135, '9e4d80dd512b90fb29a37c3b7f0c081f', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_135_1760460700.png', 0, NULL, '2025-10-14 16:51:40'),
(119, 136, '18e7b7c1e004f73750010af5a68c6a53', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_136_1760518711.png', 0, NULL, '2025-10-15 08:58:31'),
(120, 136, 'bce777e6fc9718fb6225953401704f76', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_136_1760518722.png', 0, NULL, '2025-10-15 08:58:42'),
(121, 137, '4643db27386b32f0aae8375509e9c200', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_137_1760614207.png', 0, NULL, '2025-10-16 11:30:07'),
(122, 137, 'bd06ea25689fa64a0fce8c1ff779a399', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_137_1760614215.png', 0, NULL, '2025-10-16 11:30:15'),
(123, 138, '64e16ad28be1d4e0d8c2a14b0d1153b4', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_138_1760634242.png', 0, NULL, '2025-10-16 17:04:02'),
(124, 138, '67e4f2a30773f2c89c16e42f02ba4ef8', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_138_1760634252.png', 0, NULL, '2025-10-16 17:04:13'),
(125, 138, 'fbb1613f5774ac760a7d6fa77ff51c7c', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_138_1760634262.png', 0, NULL, '2025-10-16 17:04:22'),
(126, 138, 'a500a4205620eedbb34b572ecb1ffa53', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_138_1760634271.png', 0, NULL, '2025-10-16 17:04:31'),
(127, 139, '54e70e7415e3e679e06996a5ac1cf081', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_139_1760635502.png', 0, NULL, '2025-10-16 17:25:02'),
(128, 139, '3fcbc50e6c82b706b67e30e6ed626f5f', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_139_1760635512.png', 0, NULL, '2025-10-16 17:25:12'),
(129, 139, 'd362f44c9b1f002311540526eafa5a3b', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_139_1760635522.png', 0, NULL, '2025-10-16 17:25:22'),
(130, 139, 'd1de69a1a224595ed55e7162cf8371b5', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_139_1760635532.png', 0, NULL, '2025-10-16 17:25:32'),
(131, 140, 'c3fbe3b4a4c42cc85028e77443dee61c', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_140_1760636172.png', 0, NULL, '2025-10-16 17:36:12'),
(132, 140, 'fda0af8192d148b76efcb9cb3c51a144', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_140_1760636182.png', 0, NULL, '2025-10-16 17:36:22'),
(133, 140, '90aca40b81c04357233b159e1ec837de', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_140_1760636192.png', 0, NULL, '2025-10-16 17:36:32'),
(134, 140, 'ebc56dc6c2da8f0ef094da37ee8ff722', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_140_1760636201.png', 0, NULL, '2025-10-16 17:36:41'),
(135, 141, 'd7b41ee2e664d1ee97da5a10c257f912', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_141_1760636589.png', 0, NULL, '2025-10-16 17:43:09'),
(136, 141, '2ef1775269dc4225c5847b0619a9f86a', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_141_1760636599.png', 0, NULL, '2025-10-16 17:43:19'),
(137, 141, 'd8709e11fc9d1f88a67d60aa9fe6c92f', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_141_1760636608.png', 0, NULL, '2025-10-16 17:43:28'),
(138, 141, '7b3d211795e6d8b76bf06a42001da7fe', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_141_1760636618.png', 0, NULL, '2025-10-16 17:43:38'),
(139, 142, '6f7b310abe99019f9851256052be3cdf', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_142_1760638354.png', 0, NULL, '2025-10-16 18:12:34'),
(140, 142, 'afe4bee32220082d2d7b1eec5b17766d', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_142_1760638363.png', 0, NULL, '2025-10-16 18:12:43'),
(141, 142, '15096ea41555239cc1c32e61f02ab3ae', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_142_1760638373.png', 0, NULL, '2025-10-16 18:12:53'),
(142, 142, '3550532b86a57ef53a5d76c4066c2864', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_142_1760638382.png', 0, NULL, '2025-10-16 18:13:02'),
(143, 143, 'e37a58d9acf311e7e62acef0bcaac9cc', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_143_1760639399.png', 0, NULL, '2025-10-16 18:29:59'),
(144, 143, '6f2e9fe86adbdd639b20f7acf32cc522', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_143_1760639409.png', 0, NULL, '2025-10-16 18:30:09'),
(145, 143, '5504b31aba6fe722fe365cedb17610eb', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_143_1760639418.png', 0, NULL, '2025-10-16 18:30:18'),
(146, 143, 'c352f2f0fd8bae0566192baa4d5d0091', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_143_1760639428.png', 0, NULL, '2025-10-16 18:30:28'),
(147, 144, '715c77c5733fb795b988a1647f52019e', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_144_1760642432.png', 0, NULL, '2025-10-16 19:20:32'),
(148, 144, '4f06e9a7161d96bc8e00356720cb92b6', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_144_1760642442.png', 0, NULL, '2025-10-16 19:20:42'),
(149, 144, 'f46fdc750a51f70841b73b0cbe38feb6', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_144_1760642451.png', 0, NULL, '2025-10-16 19:20:51'),
(150, 144, '5a104790aae7178d1a0ec86ea4eebc6d', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_144_1760642461.png', 0, NULL, '2025-10-16 19:21:01'),
(151, 146, '88db84fe388081dd5fe134e5d8fd2170', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_146_1760644024.png', 0, NULL, '2025-10-16 19:47:04'),
(152, 146, '7a32d999e491f8713801364825eb182e', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_146_1760644031.png', 0, NULL, '2025-10-16 19:47:11'),
(153, 146, 'abaf714052b31b2803f0810a14206442', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_146_1760644037.png', 0, NULL, '2025-10-16 19:47:17'),
(154, 146, '355ce701d709b031edc805a84c5110fe', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_146_1760644044.png', 0, NULL, '2025-10-16 19:47:24'),
(155, 145, '8d4ce6722bcfb5a34cf7dfbfe3d0b2bd', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_145_1760644196.png', 0, NULL, '2025-10-16 19:49:56'),
(156, 145, 'b2d4a1f41b62503856ae23218adc6f9c', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_145_1760644203.png', 0, NULL, '2025-10-16 19:50:03'),
(157, 145, '00066dee853fe8f62b7050bcabe119ac', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_145_1760644210.png', 0, NULL, '2025-10-16 19:50:10'),
(158, 145, '48f3a1a3d86a9416ecda37a9544402fe', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_145_1760644217.png', 0, NULL, '2025-10-16 19:50:17'),
(159, 147, 'c06a65bc7a843899ad0c9504a377a84c', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_147_1760644824.png', 0, NULL, '2025-10-16 20:00:24'),
(160, 147, '3e0c32e34f4905cdc51d9b74d1935a17', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_147_1760644831.png', 0, NULL, '2025-10-16 20:00:31'),
(161, 147, 'df1f791a88d00fffc2d7f5f74f8f7363', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_147_1760644838.png', 0, NULL, '2025-10-16 20:00:38'),
(162, 147, '90c0988dfe9da99f41d39dbd42986133', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_147_1760644845.png', 0, NULL, '2025-10-16 20:00:45'),
(163, 148, 'b165718a7533addad38cfe9788e891db', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_148_1760783239.png', 0, NULL, '2025-10-18 10:27:19'),
(164, 148, 'c00d9a297b160a7491bf50d44dcf0101', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_148_1760783247.png', 0, NULL, '2025-10-18 10:27:27'),
(165, 148, 'bff987049f4eaced17e57bb92643b484', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_148_1760783253.png', 0, NULL, '2025-10-18 10:27:33'),
(166, 148, '38b64099eedc7891ce443fcc21751a37', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_148_1760783260.png', 0, NULL, '2025-10-18 10:27:40'),
(167, 149, '4543f504e47fd25e460f1c10ad8e0ffd', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_149_1760791231.png', 0, NULL, '2025-10-18 12:40:31'),
(168, 149, '23a2971fc814549ab2f144dfdd677852', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_149_1760791244.png', 0, NULL, '2025-10-18 12:40:44'),
(169, 149, '4737d7d92bd91074e7879951dcbd569b', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_149_1760791253.png', 0, NULL, '2025-10-18 12:40:53'),
(170, 149, 'a4abf4b0cb58811fcd9e699d2504c71a', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_149_1760791262.png', 0, NULL, '2025-10-18 12:41:02'),
(171, 150, '1256cb48f6bd7fac3540d1605bbbbc22', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_150_1760857872.png', 0, NULL, '2025-10-19 07:11:12'),
(172, 150, '9f3094002aeccc19aa8a1bbf49d18266', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_150_1760857883.png', 0, NULL, '2025-10-19 07:11:23'),
(173, 150, '1fa4b95baa6f7123f62d75c91c30cc39', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_150_1760857893.png', 0, NULL, '2025-10-19 07:11:33'),
(174, 150, 'd7361a72d15d4032e1706813e16dc621', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_150_1760857902.png', 0, NULL, '2025-10-19 07:11:42'),
(175, 154, '49bf66fecf5ab149eef45da4d7430b82', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_154_1760961294.png', 0, NULL, '2025-10-20 11:54:54'),
(176, 154, '5072db05ba00ca2d68961a0e0ed2c634', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_154_1760961305.png', 0, NULL, '2025-10-20 11:55:06'),
(177, 154, 'e726f8c606de63d0bd9fe63f9b283598', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_154_1760961315.png', 0, NULL, '2025-10-20 11:55:15'),
(178, 154, 'ba5717d82e656ba17cf2f09c4019b604', 'C:\\xampp\\htdocs\\Barangay-Balas\\admin/../public/uploads/qr_codes/qr_154_1760961325.png', 0, NULL, '2025-10-20 11:55:25');

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
(132, 64, 1, 'Michael', 'Jordan', 'Jordan', '88', '1', 'Single', 'male', '2015-09-08', 10, 'Work', 'Approved', 'Claim Anytime', '2025-10-13 00:00:00', '2025-10-13 03:48:02', NULL, '', 'uploads/generated_docs/request_132_1760327282.pdf'),
(133, 64, 3, 'Michael', 'Jordan', 'dianelo', '409', '3', 'Married', 'male', '2020-11-11', 4, '111111', 'Pending', 'Claim Anytime', '2025-10-14 00:00:00', NULL, NULL, NULL, NULL),
(134, 64, 1, 'Michael', 'dela cruz', 'dianelo', '409', '3', 'Single', 'male', '2002-11-11', 22, 'uhuih', 'Pending', 'Claim Anytime', '2025-10-14 17:13:50', NULL, NULL, NULL, NULL),
(135, 65, 1, 'Neptune', 'Twix', 'Vriatrus', '409', '3', 'Single', 'female', '2000-11-11', 24, 'yuyu', 'Approved', 'Claim Anytime', '2025-10-15 00:51:17', '2025-10-14 16:51:40', 22, '', 'uploads/generated_docs/request_135_1760460700.pdf'),
(136, 65, 1, 'Jarshane', 'Sigua', 'Tolentino', '111', '1', 'Single', 'male', '2001-11-11', 23, 'Test', 'Approved', 'Claim Anytime', '2025-10-15 16:58:14', '2025-10-15 08:58:42', 22, '', 'uploads/generated_docs/request_136_1760518722.pdf'),
(137, 65, 3, 'JARSHANE', 'Sigua', 'Tolentino', '121', '4', 'Single', 'female', '2000-11-11', 24, 'SGD test', 'Approved', 'Claim Anytime', '2025-10-16 19:29:52', '2025-10-16 11:30:15', 22, '', 'uploads/generated_docs/request_137_1760614215.pdf'),
(138, 65, 1, 'JARSHANE', 'Sigua', 'Tolentino', '121', '4', 'Single', 'male', '2000-11-11', 24, 'Test svg', 'Approved', 'Claim Anytime', '2025-10-17 01:03:55', '2025-10-16 17:04:31', 22, '', 'uploads/generated_docs/request_138_1760634271.pdf'),
(139, 65, 3, 'JARSHANE', 'Sigua', 'Tolentino', '121', '4', 'Single', 'male', '2001-11-11', 23, '11111111', 'Approved', 'Claim Anytime', '2025-10-17 01:24:50', '2025-10-16 17:25:32', 22, '', 'uploads/generated_docs/request_139_1760635532.pdf'),
(140, 65, 3, 'JARSHANE', 'SIGUA', 'TOLENTINO', '1', '1', 'Single', 'male', '2003-11-11', 21, '11111', 'Approved', 'Claim Anytime', '2025-10-17 01:36:01', '2025-10-16 17:36:41', 22, '', 'uploads/generated_docs/request_140_1760636201.pdf'),
(141, 65, 3, 'JARSHANE', 'Sigua', 'Tolentino', '121', '4', 'Single', 'female', '2000-11-11', 24, 'KOIJ', 'Approved', 'Claim Anytime', '2025-10-17 01:42:46', '2025-10-16 17:43:38', 22, '', 'uploads/generated_docs/request_141_1760636618.pdf'),
(142, 65, 1, 'Joshua', 'Cheese', 'Tolentino', '121', '1', 'Married', 'female', '2024-11-11', 0, '1111', 'Approved', 'Claim Anytime', '2025-10-17 02:12:20', '2025-10-16 18:13:02', 22, '', 'uploads/generated_docs/request_142_1760638382.pdf'),
(143, 65, 1, 'JARSHANE', 'Sigua', 'Tolentino', '121', '1', 'Married', 'male', '2001-11-11', 23, '111', 'Approved', 'Claim Anytime', '2025-10-17 02:29:49', '2025-10-16 18:30:28', 22, '', 'uploads/generated_docs/request_143_1760639428.pdf'),
(144, 65, 1, 'Joshua', 'Cheese', 'User', '121', '1', 'Single', 'male', '2001-02-11', 24, 'dfds', 'Approved', 'Claim Anytime', '2025-10-17 03:08:54', '2025-10-16 19:21:01', 22, '', 'uploads/generated_docs/request_144_1760642461.pdf'),
(145, 65, 1, 'JARSHANE', 'Sigua', 'Tolentino', '121', '4', 'Single', 'female', '2001-11-11', 23, 'qqq', 'Approved', 'Claim Anytime', '2025-10-17 03:43:45', '2025-10-16 19:50:17', 22, '', 'uploads/generated_docs/request_145_1760644217.pdf'),
(146, 65, 1, 'Joshua', 'Cheese', 'User', '121', '4', 'Married', 'female', '2001-11-11', 23, 'das', 'Approved', 'Claim Anytime', '2025-10-17 03:44:10', '2025-10-16 19:47:24', 22, '', 'uploads/generated_docs/request_146_1760644044.pdf'),
(147, 65, 3, 'JARSHANE', 'Sigua', 'Tolentino', '121', '4', 'Single', 'female', '2005-08-07', 20, 'gygy', 'Approved', 'Claim Anytime', '2025-10-17 04:00:17', '2025-10-16 20:00:45', 22, '', 'uploads/generated_docs/request_147_1760644845.pdf'),
(148, 65, 23, 'Neptune', 'Twix', 'Vriatrus', '409', '3', 'Single', 'female', '2022-11-11', 2, '', 'Approved', 'Claim Anytime', '2025-10-18 18:24:04', '2025-10-18 10:27:40', 22, '', 'uploads/generated_docs/request_148_1760783260.pdf'),
(149, 74, 12, 'Jarshane', 'Sigua', 'Tolentino', '1111', '2', 'Single', 'female', '2003-12-11', 21, '1111', 'Approved', 'Claim Anytime', '2025-10-18 20:40:21', '2025-10-18 12:41:02', 22, '', 'uploads/generated_docs/request_149_1760791262.pdf'),
(150, 75, 3, 'Marvin', 'Chan', 'Lorenzo', '408', '1', 'Single', 'male', '2003-12-17', 21, 'Testing 1', 'Approved', 'Claim Anytime', '2025-10-19 15:09:56', '2025-10-19 07:11:42', 22, 'Testing ', 'uploads/generated_docs/request_150_1760857902.pdf'),
(151, 75, 2, 'Jarshane', '', 'Tolentino', '408', '1', 'Single', 'female', '2003-11-21', 21, 'Educational Purposes', 'Pending', 'Claim Anytime', '2025-10-19 22:14:52', NULL, NULL, NULL, NULL),
(152, 75, 17, 'Jarshane', '', 'Tolentino', '409', '1', 'Single', 'female', '2003-11-21', 21, 'Purposes', 'Pending', '', '2025-10-19 22:30:18', NULL, NULL, NULL, NULL),
(153, 74, 4, 'Jarshane', '', 'Tolentino', '409', '1', 'Single', 'female', '2003-11-21', 21, 'Test', 'Pending', '', '2025-10-20 19:43:50', NULL, NULL, NULL, NULL),
(154, 74, 13, 'Shane', 'Sigua', 'Tolentino', '1', '3', 'Single', 'female', '2002-11-11', 22, 'Shshs', 'Approved', '', '2025-10-20 19:54:33', '2025-10-20 11:55:25', 22, '', 'uploads/generated_docs/request_154_1760961325.pdf');

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
  `valid_id_path_2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `first_name`, `last_name`, `middle_name`, `suffix`, `sex`, `birthdate`, `age`, `civil_status`, `educational_attainment`, `religion`, `occupation`, `philhealth_number`, `is_indigent`, `is_4ps_member`, `medical_history`, `contact_number`, `email`, `house_number`, `purok`, `relationship_to_head`, `address`, `verification_status`, `resident_status`, `photo_path`, `valid_id_path`, `valid_id_path_2`, `created_at`, `updated_at`) VALUES
(59, 'John Kyle', 'Chan', 'Cruz', '', 'female', '2001-11-11', 23, '', NULL, NULL, NULL, NULL, 0, 0, NULL, '09876543211', 'quintojohnkyle@gmail.com', '409', 'Purok 5', 'Head of Household', 'House 409, Purok 5, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id1_68e91f2a75fa6.png', 'uploads/valid_ids/id2_68e91f2a76a1b.png', '2025-10-10 14:58:50', '2025-10-10 15:46:52'),
(60, 'Jarshane', 'Tolentino', 'Sigua', '', 'female', '2003-11-21', 21, '', NULL, NULL, NULL, NULL, 0, 0, NULL, '09876543211', 'jarshanetolentino@gmail.com', '409', 'Purok 3', NULL, 'House 409, Purok 3, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id1_68e926f1cbadb.png', 'uploads/valid_ids/id2_68e926f1cc383.png', '2025-10-10 15:32:01', '2025-10-10 15:32:01'),
(63, 'Jarshane', 'Tolentino', 'Sigua', '', 'female', '2003-11-21', 21, '', NULL, NULL, NULL, NULL, 0, 0, NULL, '09156699382', 'neptwix@gmail.com', '409', 'Purok 3', NULL, 'House 409, Purok 3, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', 'uploads/profile_photos/profile_63_1760285094.png', 'uploads/valid_ids/id1_68ebce1ac3d34.JPG', 'uploads/valid_ids/id2_68ebce1ac54ac.png', '2025-10-12 15:49:46', '2025-10-12 16:04:54'),
(64, 'juan', 'dianelo', 'dela cruz', '', 'male', '1984-04-20', 41, 'Single', 'College Graduate', '', '', '', 1, 1, '', '09063659029', 'fdianelosanfernando@gmail.com', '88', '1', 'Head of Household', 'kugjgnga', 'Pending', 'Active', NULL, 'uploads/valid_ids/id1_68ec6b8d7e04b.png', 'uploads/valid_ids/id2_68ec6b8d7ee83.png', '2025-10-13 03:01:33', '2025-10-13 03:15:42'),
(65, 'Jarshane', 'Tolentino', 'Sigua', '', 'female', '2003-11-21', 21, '', '', '', '', '', 0, 0, '', '09156699382', 'jarshanetolentino@gmail.com', '4009', '3', NULL, '4009, Purok 3, Barangay Balas, Mexico, Pampanga, Philippines', 'Unverified', 'Active', 'uploads/profile_photos/profile_65_1760453116.png', NULL, NULL, '2025-10-14 14:43:48', '2025-10-18 12:32:17'),
(69, 'Maria', 'Santos', 'R.', NULL, 'female', '1985-03-15', 40, 'Married', NULL, NULL, NULL, NULL, 0, 0, NULL, '09171111111', 'maria.santos@test.com', '101', '1', NULL, 'House 101, Purok 1, Balas, Mexico, Pampanga', 'Verified', 'Active', NULL, NULL, NULL, '2025-10-15 17:07:06', '2025-10-15 17:07:06'),
(70, 'Juan', 'Cruz', 'M.', NULL, 'male', '1990-07-20', 34, 'Single', NULL, NULL, NULL, NULL, 0, 0, NULL, '09172222222', 'juan.cruz@test.com', '202', '2', NULL, 'House 202, Purok 2, Balas, Mexico, Pampanga', 'Verified', 'Active', NULL, NULL, NULL, '2025-10-15 17:07:06', '2025-10-15 17:07:06'),
(73, 'Sofia', 'Lopez', 'T.', NULL, 'female', '1987-12-25', 37, 'Married', NULL, NULL, NULL, NULL, 0, 0, NULL, '09175555555', 'sofia.lopez@test.com', '505', '5', NULL, 'House 505, Purok 5, Balas, Mexico, Pampanga', 'Verified', 'Active', NULL, NULL, NULL, '2025-10-15 17:07:06', '2025-10-15 17:07:06'),
(74, 'Jarshane', 'Tolentino', 'Sigua', '', 'female', '2006-11-11', 18, '', '', '', '', '', 0, 0, '', '09972353679', '2022307283@pampangastateu.edu.ph', '1', '3', NULL, '1, Purok 3, Barangay Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id1_68f38a0584619.png', 'uploads/valid_ids/id2_68f38a05855e8.jpg', '2025-10-18 12:37:25', '2025-10-20 11:53:17'),
(75, 'Jarshane ', 'Tolentino', 'Sigua', '', 'female', '2003-11-21', 21, '', NULL, NULL, NULL, NULL, 0, 0, NULL, '09789456123', 'jarshanethesecond@gmail.com', '409', 'Purok 3', NULL, 'House 409, Purok 3, Balas, Mexico, Pampanga, Philippines', 'Pending', 'Active', NULL, 'uploads/valid_ids/id1_68f48ca3c1967.JPG', 'uploads/valid_ids/id2_68f48ca3c2d7d.JPG', '2025-10-19 07:00:51', '2025-10-19 07:00:51');

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
(61, 65, 'jarshanetolentino@gmail.com', '$2y$10$j9Ayx3iZXYGUQn.ZmFz8ruFKwpTiyz4jo5yr0U.OejKs46LYBUeSi', 'Approved', 22, '2025-10-14 22:43:48', '2025-10-18 21:01:30', 0, NULL, NULL, NULL, '2025-10-14 22:43:48'),
(65, 69, 'maria.santos@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pending', NULL, '2022-10-05 01:07:06', NULL, NULL, NULL, NULL, NULL, '2025-10-16 01:07:06'),
(66, 70, 'juan.cruz@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Disapproved', 22, '2025-10-18 14:57:35', NULL, 0, NULL, NULL, NULL, '2025-10-16 01:07:06'),
(70, 74, '2022307283@pampangastateu.edu.ph', '$2y$10$IJKUqMHRkZBsfI500nKkGO5wNhe7WXsETnuRF7eJC8jZTHOsTQmQq', 'Approved', 22, '2025-10-18 20:37:56', '2025-10-20 23:10:51', 0, NULL, NULL, '', '2025-10-18 20:37:25'),
(71, 75, 'jarshanethesecond@gmail.com', '$2y$10$6TEeDINvgJJNY/GxZ6tELuyack8qxpINJDJYybUNXz1q1xPq/nrMW', 'Approved', 22, '2025-10-19 15:03:23', '2025-10-19 21:56:32', 0, NULL, NULL, 'Testing', '2025-10-19 15:00:51');

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
(22, 64, 'juan dianelo', '09063659029', NULL, '2025-10-14 00:00:00', '2027-10-14 00:00:00', 731, 'trip', 'Approved', NULL, '', NULL, '2025-10-13 11:55:22', '2025-10-14 12:29:46', 22, '2025-10-13 03:55:22', '2025-10-14 04:29:46'),
(23, 64, 'juan dianelo', '09063659029', NULL, '2025-11-11 00:00:00', '2925-11-20 00:00:00', 328728, 'sfdf', 'Pending', NULL, 'Start Time: 11:11\nEnd Time: 11:11\nStart Time: 11:11\r\nEnd Time: 11:11\r\n\n', NULL, '2025-10-14 17:25:47', NULL, NULL, '2025-10-14 09:25:47', '2025-10-14 09:25:47'),
(24, 65, 'Jarshane Tolentino', '09156699382', NULL, '2026-01-10 00:00:00', '2026-01-10 00:00:00', 1, 'Test 1', 'Disapproved', NULL, 'Start Time: 11:11\nEnd Time: 23:11\nStart Time: 11:11\r\nEnd Time: 23:11\r\n\n', 'Test', '2025-10-14 22:49:23', '2025-10-15 00:10:54', 22, '2025-10-14 14:49:23', '2025-10-14 16:10:54'),
(25, 65, 'Jarshane Tolentino', '09156699382', NULL, '2026-02-14 00:00:00', '2026-02-14 00:00:00', 1, 'Test', 'Completed', NULL, 'Test', NULL, '2025-10-14 22:56:23', '2025-10-15 09:37:42', 22, '2025-10-14 14:56:23', '2025-10-15 01:37:42'),
(26, 65, 'Jarshane Tolentino', '09156699382', NULL, '2025-11-11 00:00:00', '2025-11-22 00:00:00', 12, 'Test duration', 'Cancelled', NULL, '', NULL, '2025-10-14 23:06:46', '2025-10-15 00:10:28', 22, '2025-10-14 15:06:46', '2025-10-14 16:10:28'),
(27, 65, 'Jarshane Tolentino', '09156699382', NULL, '2025-10-24 00:00:00', '2025-10-24 00:00:00', 1, 'Test', 'In Progress', NULL, '', NULL, '2025-10-15 16:09:23', '2025-10-16 00:17:18', 22, '2025-10-15 08:09:23', '2025-10-15 16:17:18'),
(28, 75, 'Jarshane Tolentino', '09789456123', NULL, '2025-10-22 00:00:00', '2025-10-23 00:00:00', 2, 'Testing', 'Approved', NULL, 'Testing', NULL, '2025-10-19 15:19:42', '2025-10-19 15:23:53', 22, '2025-10-19 07:19:42', '2025-10-19 07:23:53'),
(29, 75, 'Jarshane Tolentino', '09789456123', 'jarshanethesecond@gmail.com', '2025-11-11 00:00:00', '2025-11-11 00:00:00', 1, 'Test', 'Pending', NULL, 'Start Time: 11:00\nEnd Time: 23:00\nStart Time: 11:00\r\nEnd Time: 23:00\r\n\n', NULL, '2025-10-19 22:11:21', NULL, NULL, '2025-10-19 14:11:21', '2025-10-19 14:11:21'),
(30, 74, 'Jarshane Tolentino', '09972353679', '2022307283@pampangastateu.edu.ph', '2025-11-11 00:00:00', '2025-11-11 00:00:00', 1, 'Test', 'Pending', NULL, 'Start Time: 11:00\nEnd Time: 15:00\nStart Time: 11:00\r\nEnd Time: 15:00\r\n\n', NULL, '2025-10-20 19:42:08', NULL, NULL, '2025-10-20 11:42:08', '2025-10-20 11:42:08'),
(31, 74, 'Jarshane Tolentino', '09972353679', '2022307283@pampangastateu.edu.ph', '2025-11-11 00:00:00', '2025-11-12 00:00:00', 2, 'Testing phase', 'Pending', NULL, 'Start Time: 11:11\nEnd Time: 11:00\nStart Time: 11:11\r\nEnd Time: 11:00\r\n\n', NULL, '2025-10-20 23:12:09', NULL, NULL, '2025-10-20 15:12:09', '2025-10-20 15:12:09'),
(32, 74, 'Jarshane Tolentino', '09972353679', '2022307283@pampangastateu.edu.ph', '2025-11-11 00:00:00', '2025-11-11 00:00:00', 1, 'Test', 'Pending', NULL, 'Start Time: 11:00\nEnd Time: 15:00\nStart Time: 11:00\r\nEnd Time: 15:00\r\n\n', NULL, '2025-10-20 23:17:49', NULL, NULL, '2025-10-20 15:17:49', '2025-10-20 15:17:49'),
(33, 74, 'Jarshane Tolentino', '09972353679', '2022307283@pampangastateu.edu.ph', '2025-12-12 00:00:00', '2025-12-12 00:00:00', 1, 'Hshshs', 'Pending', NULL, 'Start Time: 14:00\nEnd Time: 17:00\nStart Time: 14:00\r\nEnd Time: 17:00\r\n\n', NULL, '2025-10-20 23:18:54', NULL, NULL, '2025-10-20 15:18:54', '2025-10-20 15:18:54');

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
(29, 22, 2, 1, NULL),
(30, 23, 2, 1, NULL),
(31, 24, 2, 1, NULL),
(32, 25, 1, 1, NULL),
(33, 26, 4, 1, NULL),
(34, 27, 2, 1, NULL),
(35, 28, 2, 1, NULL),
(36, 29, 2, 1, NULL),
(37, 30, 2, 1, NULL),
(38, 31, 2, 1, NULL),
(39, 32, 2, 1, NULL),
(40, 33, 6, 1, NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=308;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `announcement_images`
--
ALTER TABLE `announcement_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `document_qr_codes`
--
ALTER TABLE `document_qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `service_reservations`
--
ALTER TABLE `service_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `service_reservation_items`
--
ALTER TABLE `service_reservation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `service_types`
--
ALTER TABLE `service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
