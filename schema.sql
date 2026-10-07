-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 05, 2026 at 01:55 PM
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
-- Database: `sjbce_election`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@sjbce.edu.gh', NULL, '$2y$10$NKJ4IHENDZpXg.37NjYss.30skMbZq7pqBsLXsCJSMVfuB8bpN/om', 'super_admin', 1, '2026-09-19 00:25:02', '2026-08-16 23:26:17', '2026-09-19 00:25:02');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('admin','student','system') NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_type`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', '2026-08-16 23:28:01'),
(2, 'admin', 1, 'CREATE_ELECTION', 'src general', '::1', '2026-08-16 23:31:05'),
(3, 'admin', 1, 'CREATE_POSITION', 'president', '127.0.0.1', '2026-08-16 23:31:34'),
(4, 'admin', 1, 'CREATE_POSITION', 'wocom', '::1', '2026-08-16 23:31:41'),
(5, 'admin', 1, 'CREATE_POSITION', 'sec', '::1', '2026-08-16 23:31:52'),
(6, 'admin', 1, 'CREATE_CANDIDATE', 'john', '::1', '2026-08-16 23:32:14'),
(7, 'admin', 1, 'CREATE_CANDIDATE', 'kow', '127.0.0.1', '2026-08-16 23:32:32'),
(8, 'admin', 1, 'CREATE_CANDIDATE', 'jow', '::1', '2026-08-16 23:32:52'),
(9, 'admin', 1, 'CREATE_CANDIDATE', 'gen', '::1', '2026-08-16 23:33:10'),
(10, 'admin', 1, 'CREATE_CANDIDATE', 'youth', '127.0.0.1', '2026-08-16 23:33:36'),
(11, 'admin', 1, 'ELECTION_STATUS_CHANGE', 'Election #1 -> active', '127.0.0.1', '2026-08-16 23:33:41'),
(12, 'admin', 1, 'ELECTION_STATUS_CHANGE', 'Election #1 -> ended', '::1', '2026-08-16 23:34:07'),
(13, 'admin', 1, 'UPDATE_ELECTION', 'Election #1 updated (src general)', '::1', '2026-08-16 23:34:31'),
(14, 'admin', 1, 'ELECTION_STATUS_CHANGE', 'Election #1 -> active', '::1', '2026-08-16 23:34:33'),
(15, 'admin', 1, 'IMPORT_STUDENTS', '11 added, 0 skipped', '::1', '2026-08-16 23:53:47'),
(16, 'admin', 1, 'ELECTION_STATUS_CHANGE', 'Election #1 -> ended', '::1', '2026-08-16 23:54:07'),
(17, 'admin', 1, 'UPDATE_ELECTION', 'Election #1 updated (src general)', '::1', '2026-08-16 23:54:41'),
(18, 'admin', 1, 'ELECTION_STATUS_CHANGE', 'Election #1 -> active', '::1', '2026-08-16 23:54:43'),
(19, 'student', 1, 'OTP_REQUESTED', 'aneborfrimpong@gmail.com', '::1', '2026-08-16 23:56:10'),
(20, 'student', 1, 'OTP_REQUESTED', 'aneborfrimpong@gmail.com', '::1', '2026-08-16 23:56:28'),
(21, 'student', 1, 'OTP_PASSWORD_SET', 'Election #1', '::1', '2026-08-16 23:57:07'),
(22, 'student', 1, 'LOGIN', 'Student logged in', '::1', '2026-08-16 23:57:40'),
(23, 'student', 1, 'LOGIN', 'Student logged in', '::1', '2026-08-16 23:57:55'),
(24, 'system', NULL, 'STUDENT_LOGIN_FAILED', 'Attempt for email: admin@sjbce.edu.gh', '::1', '2026-09-19 00:21:07'),
(25, 'system', NULL, 'STUDENT_LOGIN_FAILED', 'Attempt for email: admin@sjbce.edu.gh', '::1', '2026-09-19 00:24:03'),
(26, 'system', NULL, 'STUDENT_LOGIN_FAILED', 'Attempt for email: SJB/1234/56', '::1', '2026-09-19 00:24:32'),
(27, 'admin', 1, 'LOGIN', 'Admin logged in', '127.0.0.1', '2026-09-19 00:25:02'),
(28, 'admin', 1, 'ELECTION_STATUS_CHANGE', 'Election #1 -> ended', '::1', '2026-09-19 00:25:15'),
(29, 'system', NULL, 'STUDENT_LOGIN_FAILED', 'Attempt for email: admin@sjbce.edu.gh', '::1', '2026-09-19 15:53:36'),
(30, 'system', NULL, 'STUDENT_LOGIN_FAILED', 'Attempt for email: admin@sjbce.edu.gh', '::1', '2026-09-19 19:05:31'),
(31, 'system', NULL, 'STUDENT_LOGIN_FAILED', 'Attempt for email: admin@sjbce.edu.gh', '::1', '2026-09-19 19:11:45'),
(32, 'system', NULL, 'STUDENT_LOGIN_FAILED', 'Attempt for email: SJB/1234/56', '::1', '2026-09-19 19:11:54'),
(33, 'system', NULL, 'STUDENT_LOGIN_FAILED', 'Attempt for email: admin@sjbce.edu.gh', '::1', '2026-09-19 21:56:06'),
(34, 'admin', 1, 'IMPORT_STUDENTS', '0 added, 10 skipped', '::1', '2026-09-19 22:02:48'),
(35, 'admin', 1, 'IMPORT_STUDENTS', '0 added, 9 skipped', '::1', '2026-09-19 22:12:21'),
(36, 'admin', 1, 'IMPORT_STUDENTS', '0 added, 9 skipped', '::1', '2026-09-19 22:14:20'),
(37, 'admin', 1, 'IMPORT_STUDENTS', '0 added, 9 skipped', '::1', '2026-09-19 22:16:11'),
(38, 'admin', 1, 'IMPORT_STUDENTS', '0 added, 9 skipped', '::1', '2026-09-19 22:16:25'),
(39, 'admin', 1, 'IMPORT_STUDENTS', '9 added, 1 skipped', '::1', '2026-09-19 22:26:26'),
(40, 'admin', 1, 'IMPORT_STUDENTS', '1 added, 10 skipped', '::1', '2026-09-19 22:31:50'),
(41, 'admin', 1, 'IMPORT_STUDENTS', '0 added, 10 skipped', '::1', '2026-09-19 22:33:11'),
(42, 'admin', 1, 'GRADUATE_STUDENTS', '1 Level 400 students marked as graduated', '::1', '2026-09-20 00:48:56'),
(43, 'admin', 1, 'PROMOTE_STUDENTS', '6 students moved from Level 100 to Level 200', '::1', '2026-09-20 00:49:38'),
(44, 'admin', 1, 'PROMOTE_STUDENTS', '13 students moved from Level 200 to Level 300', '::1', '2026-09-20 00:49:42'),
(45, 'admin', 1, 'PROMOTE_STUDENTS', '20 students moved from Level 300 to Level 400', '::1', '2026-09-20 00:49:45'),
(46, 'admin', 1, 'GRADUATE_STUDENTS', '20 Level 400 students marked as graduated', '::1', '2026-09-20 00:49:51'),
(47, 'system', NULL, 'STUDENT_LOGIN_FAILED', 'Attempt for email: aneborfrimpong@gmail.com', '::1', '2026-09-30 18:53:36'),
(48, 'admin', 1, 'ELECTION_STATUS_CHANGE', 'Election #1 -> active', '::1', '2026-10-05 10:31:35'),
(49, 'admin', 1, 'UPDATE_ELECTION', 'Election #1 updated (src general)', '::1', '2026-10-05 10:35:08'),
(50, 'admin', 1, 'ELECTION_STATUS_CHANGE', 'Election #1 -> ended', '::1', '2026-10-05 10:35:26'),
(51, 'admin', 1, 'ELECTION_STATUS_CHANGE', 'Election #1 -> active', '::1', '2026-10-05 10:35:27'),
(52, 'student', 1, 'OTP_REQUESTED', 'aneborfrimpong@gmail.com', '::1', '2026-10-05 10:49:18'),
(53, 'student', 1, 'OTP_PASSWORD_SET', 'Election #1', '::1', '2026-10-05 10:50:33'),
(54, 'system', NULL, 'STUDENT_LOGIN_FAILED', 'Attempt for email: aneborfrimpong@gmail.com', '::1', '2026-10-05 10:51:03'),
(55, 'student', 1, 'LOGIN', 'Student logged in', '::1', '2026-10-05 10:51:34'),
(56, 'student', 1, 'CAST_VOTE', 'Position #3, Candidate #5', '::1', '2026-10-05 10:51:50'),
(57, 'student', 1, 'CAST_VOTE', 'Position #2, Candidate #4', '::1', '2026-10-05 10:51:55'),
(58, 'student', 1, 'CAST_VOTE', 'Position #1, Candidate #3', '127.0.0.1', '2026-10-05 10:52:00'),
(59, 'student', 1, 'OTP_REQUESTED', 'aneborfrimpong@gmail.com', '::1', '2026-10-05 11:03:13'),
(60, 'student', 1, 'OTP_LOGIN', 'Election #1', '::1', '2026-10-05 11:03:40'),
(61, 'student', 1, 'LOGOUT', 'Student logged out', '127.0.0.1', '2026-10-05 11:39:56'),
(62, 'student', 1, 'OTP_REQUESTED', 'aneborfrimpong@gmail.com', '::1', '2026-10-05 11:40:15'),
(63, 'student', 1, 'OTP_REQUESTED', 'aneborfrimpong@gmail.com', '::1', '2026-10-05 11:40:40');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `election_id` bigint(20) UNSIGNED NOT NULL,
  `position_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `manifesto` text DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `election_id`, `position_id`, `student_id`, `full_name`, `phone`, `photo_path`, `manifesto`, `is_approved`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'john', '0558710190', '0e435138d61c112ac61943a0cd25d0c2.jpg', '', 1, '2026-08-16 23:32:14', '2026-08-16 23:32:14'),
(2, 1, 1, NULL, 'kow', '92389283923', '922edcd328c8c75f988fe3827b05a1d9.jpg', '', 1, '2026-08-16 23:32:32', '2026-08-16 23:32:32'),
(3, 1, 1, NULL, 'jow', '0505147781', '3edf231d2dee2369e77a1778ca9c91b3.jpg', '', 1, '2026-08-16 23:32:52', '2026-08-16 23:32:52'),
(4, 1, 2, NULL, 'gen', '92389283923', 'a9fd49dd7a5a8a3c58a355c0b861b175.jpg', '', 1, '2026-08-16 23:33:10', '2026-08-16 23:33:10'),
(5, 1, 3, NULL, 'youth', '309u293u092', '8c5eb22645c1773d17390bae769af66a.jpg', '', 1, '2026-08-16 23:33:36', '2026-08-16 23:33:36');

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('pending','active','paused','ended') NOT NULL DEFAULT 'pending',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`id`, `title`, `description`, `start_time`, `end_time`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'src general', '', '2026-10-05 10:35:00', '2026-10-05 13:00:00', 'active', 1, '2026-08-16 23:31:05', '2026-10-05 10:35:27');

-- --------------------------------------------------------

--
-- Table structure for table `email_otps`
--

CREATE TABLE `email_otps` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `identifier` varchar(150) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_type` enum('admin','student') NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `identifier`, `ip_address`, `user_type`, `attempted_at`) VALUES
(1, 'admin@sjbce.edu.gh', '::1', 'student', '2026-09-19 00:21:07'),
(2, 'admin@sjbce.edu.gh', '::1', 'student', '2026-09-19 00:24:03'),
(3, 'SJB/1234/56', '::1', 'student', '2026-09-19 00:24:32'),
(4, 'admin@sjbce.edu.gh', '::1', 'student', '2026-09-19 15:53:36'),
(5, 'admin@sjbce.edu.gh', '::1', 'student', '2026-09-19 19:05:31'),
(6, 'admin@sjbce.edu.gh', '::1', 'student', '2026-09-19 19:11:45'),
(7, 'SJB/1234/56', '::1', 'student', '2026-09-19 19:11:54'),
(8, 'admin@sjbce.edu.gh', '::1', 'student', '2026-09-19 21:56:06'),
(9, 'aneborfrimpong@gmail.com', '::1', 'student', '2026-09-30 18:53:36'),
(10, 'aneborfrimpong@gmail.com', '::1', 'student', '2026-10-05 10:51:03');

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `election_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `student_id`, `election_id`, `code`, `expires_at`, `used`, `attempts`, `created_at`) VALUES
(1, 1, 1, '877769', '2026-08-17 00:06:10', 0, 0, '2026-08-16 23:56:10'),
(2, 1, 1, '309173', '2026-08-17 00:06:28', 1, 0, '2026-08-16 23:56:28'),
(3, 1, 1, '545866', '2026-10-05 10:59:18', 1, 0, '2026-10-05 10:49:18'),
(4, 1, 1, '108085', '2026-10-05 11:13:13', 1, 0, '2026-10-05 11:03:13'),
(5, 1, 1, '364696', '2026-10-05 11:50:15', 0, 0, '2026-10-05 11:40:15'),
(6, 1, 1, '456111', '2026-10-05 11:50:40', 0, 0, '2026-10-05 11:40:40');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('admin','student') NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `election_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `election_id`, `title`, `description`, `display_order`, `created_at`) VALUES
(1, 1, 'president', '', 0, '2026-08-16 23:31:34'),
(2, 1, 'wocom', '', 0, '2026-08-16 23:31:41'),
(3, 1, 'sec', '', 0, '2026-08-16 23:31:52');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` varchar(30) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `department` varchar(100) NOT NULL,
  `level` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password_valid_for_election_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 1,
  `verification_token` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('active','suspended','graduated') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `full_name`, `email`, `phone`, `department`, `level`, `password_hash`, `password_valid_for_election_id`, `is_verified`, `verification_token`, `is_approved`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SJBCE/2026/00001', 'Anebor Frimpong', 'aneborfrimpong@gmail.com', '0244000000', 'Computer Science', 'Level 200', '$2y$10$rzKbqHJa0PniNsQyX.EwLe9hrL5JMTt/f4XNAT5VuA6HVb1ErNYz.', 1, 1, NULL, 1, 'active', '2026-08-16 23:53:46', '2026-10-05 10:50:33'),
(2, 'SJBCE/2026/00002', 'Ama Boateng', 'ama.boateng@example.com', '0244111111', 'Computer Science', 'Level 400', '$2y$10$eiNEs1MhqBKVJTi5VTAaSeYOZNrleGx.eXFelOZz6cVoFL2pEQAI.', NULL, 1, NULL, 1, 'graduated', '2026-08-16 23:53:46', '2026-09-20 00:49:51'),
(3, 'SJBCE/2026/00003', 'Kwame Mensah', 'kwame.mensah@example.com', '0244222222', 'Mathematics', 'Level 400', '$2y$10$bvsH/gaQ7RtnUd1PayzFy.TRQnjc2pkKsCGtwiyVN.PICnqO1Ydre', NULL, 1, NULL, 1, 'graduated', '2026-08-16 23:53:46', '2026-09-20 00:49:51'),
(4, 'SJBCE/2026/00004', 'Kofi Asare', 'kofi.asare@example.com', '0244333333', 'Information Technology', 'Level 400', '$2y$10$qNQnXhSumTHvpHd2p0J9ZOxOmW8hAQGwio1mOk0ds.s.CuoU31rKS', NULL, 1, NULL, 1, 'graduated', '2026-08-16 23:53:46', '2026-09-20 00:49:51'),
(5, 'SJBCE/2026/00005', 'Abena Owusu', 'abena.owusu@example.com', '0244444444', 'Computer Science', 'Level 400', '$2y$10$Bf1Ex6UiA8hUlB.SsJOSMOLZ0lgrkgRe.kjVImcihzW0eZcs5lRDy', NULL, 1, NULL, 1, 'graduated', '2026-08-16 23:53:46', '2026-09-20 00:49:51'),
(6, 'SJBCE/2026/00006', 'Yaw Osei', 'yaw.osei@example.com', '0244555555', 'Information Technology', 'Level 400', '$2y$10$Qg8P3GfZCMCB70oEgi0UlOzhxPR/VQbM.cJxBkLeYt4EGaO24DBZS', NULL, 1, NULL, 1, 'graduated', '2026-08-16 23:53:47', '2026-09-20 00:49:51'),
(7, 'SJBCE/2026/00007', 'Akosua Addo', 'akosua.addo@example.com', '0244666666', 'Mathematics', 'Level 400', '$2y$10$W0IEf64UA.VCSWJnEPGB0uqk.m9amuCbIkyyNos0JNY3zwvmfocbO', NULL, 1, NULL, 1, 'graduated', '2026-08-16 23:53:47', '2026-09-20 00:49:51'),
(8, 'SJBCE/2026/00008', 'Kojo Appiah', 'kojo.appiah@example.com', '0244777777', 'Computer Science', 'Level 400', '$2y$10$6u.kp3BIAifW1UQZqgM/KOvk2h04e/lBb/gKg5ZM5/Dwt3/b4KgDS', NULL, 1, NULL, 1, 'graduated', '2026-08-16 23:53:47', '2026-09-20 00:49:51'),
(9, 'SJBCE/2026/00009', 'Esi Antwi', 'esi.antwi@example.com', '0244888888', 'Information Technology', 'Level 400', '$2y$10$90daMs1i5hgPRNu29gzFrufvfyIkT1Rc2K39NKf.WoSAxKbY7B68a', NULL, 1, NULL, 1, 'graduated', '2026-08-16 23:53:47', '2026-09-20 00:49:51'),
(10, 'SJBCE/2026/00010', 'Daniel Arthur', 'daniel.arthur@example.com', '0244999999', 'Computer Science', 'Level 400', '$2y$10$ItXq1E4pLVSSvVUCRjHkNebYD6a.UWZ84eFYHKsW0CBXagMR1T9xO', NULL, 1, NULL, 1, 'graduated', '2026-08-16 23:53:47', '2026-09-20 00:49:51'),
(11, 'SJBCE/2026/00011', 'Michael Asante', 'michael.asante@example.com', '0244001111', 'Mathematics', 'Level 400', '$2y$10$P51u9wZYezl.N4MW.CxfcuLuumkrqyy0kjkKF9pIQdJ7txxHTvClC', NULL, 1, NULL, 1, 'graduated', '2026-08-16 23:53:47', '2026-09-20 00:49:51'),
(12, 'SJBCE/2026/00012', 'Kwame Mensah', 'kwamemensah@gmail.com', '0244222222', 'Mathematics', 'Level 400', '$2y$10$6yqmbMjcRRzeeoD/UxuM.OoTyfC3yWGvLRm0PW3VVGxdAS4Z8jOdW', NULL, 1, NULL, 1, 'graduated', '2026-09-19 22:26:25', '2026-09-20 00:49:51'),
(13, 'SJBCE/2026/00013', 'Ama Owusu', 'amaowusu@gmail.com', '0244333333', 'Computer Science', 'Level 400', '$2y$10$bAoWU.UWBkbeWB1NJGDEEuURmbLQtjc.Hj0phb.OK7G3Hw87LsG.2', NULL, 1, NULL, 1, 'graduated', '2026-09-19 22:26:25', '2026-09-20 00:49:51'),
(14, 'SJBCE/2026/00014', 'Kofi Asare', 'kofiasare@gmail.com', '0244444444', 'Physics', 'Level 400', '$2y$10$v1pVCIOhwF/L3dwoXfpWzO08s9/5bf.aICBAusHhjKTz1v6i5XqzK', NULL, 1, NULL, 1, 'graduated', '2026-09-19 22:26:25', '2026-09-20 00:49:51'),
(15, 'SJBCE/2026/00015', 'Abena Boateng', 'abenaboateng@gmail.com', '0244555555', 'Chemistry', 'Level 400', '$2y$10$NGoqdNeCJaJUMI992Z4Mgu2xZVT.lN0oEJx5pOj3ZvM3JWeA55mJK', NULL, 1, NULL, 1, 'graduated', '2026-09-19 22:26:26', '2026-09-20 00:49:51'),
(16, 'SJBCE/2026/00016', 'Yaw Osei', 'yawosei@gmail.com', '0244666666', 'Biology', 'Level 400', '$2y$10$nw7gto/CJOH7g53eypZ/cekOFgE8CKVYzORTxVqMj3yZWot3jhDTe', NULL, 1, NULL, 1, 'graduated', '2026-09-19 22:26:26', '2026-09-20 00:49:51'),
(17, 'SJBCE/2026/00017', 'Akosua Addo', 'akosuaaddo@gmail.com', '0244777777', 'Mathematics', 'Level 400', '$2y$10$Cy1F4aWeyr.MeEj4Z9CXgO9mOp47sH7xTDOJWoUfdlSdHWw1OKG7K', NULL, 1, NULL, 1, 'graduated', '2026-09-19 22:26:26', '2026-09-20 00:49:51'),
(18, 'SJBCE/2026/00018', 'Daniel Arthur', 'danielarthur@gmail.com', '0244888888', 'Computer Science', 'Level 400', '$2y$10$T.TUwmk0hmGA5y.WE2rseua3MxQbpdUlsgZ5v4xhSqH8lDBQIuUjW', NULL, 1, NULL, 1, 'graduated', '2026-09-19 22:26:26', '2026-09-20 00:48:56'),
(19, 'SJBCE/2026/00019', 'Esi Antwi', 'esiantwi@gmail.com', '0244999999', 'Physics', 'Level 400', '$2y$10$kJFMvjg5A55ziYSm8/lAgeoSwUAfhXCIoMi8LngyvXAwZdYVqKkJ.', NULL, 1, NULL, 1, 'graduated', '2026-09-19 22:26:26', '2026-09-20 00:49:51'),
(20, 'SJBCE/2026/00020', 'Kojo Appiah', 'kojoappiah@gmail.com', '0244000000', 'Chemistry', 'Level 400', '$2y$10$2J4DkXUh03vyahspI1xTh.sneIJTdfaOEzjT3UjKQsmEIFPpxeah6', NULL, 1, NULL, 1, 'graduated', '2026-09-19 22:26:26', '2026-09-20 00:49:51');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `election_id` bigint(20) UNSIGNED NOT NULL,
  `position_id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `voted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `election_id`, `position_id`, `candidate_id`, `student_id`, `voted_at`, `ip_address`) VALUES
(1, 1, 3, 5, 1, '2026-10-05 10:51:50', '::1'),
(2, 1, 2, 4, 1, '2026-10-05 10:51:55', '::1'),
(3, 1, 1, 3, 1, '2026-10-05 10:52:00', '127.0.0.1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_type`,`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_candidates_student` (`student_id`),
  ADD KEY `idx_candidates_position` (`position_id`),
  ADD KEY `idx_candidates_election` (`election_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_elections_admin` (`created_by`),
  ADD KEY `idx_elections_status` (`status`),
  ADD KEY `idx_elections_window` (`start_time`,`end_time`);

--
-- Indexes for table `email_otps`
--
ALTER TABLE `email_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier_time` (`identifier`,`user_type`,`attempted_at`),
  ADD KEY `idx_ip_time` (`ip_address`,`attempted_at`);

--
-- Indexes for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_election` (`student_id`,`election_id`),
  ADD KEY `fk_otp_election` (`election_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_reset_user` (`user_type`,`user_id`),
  ADD KEY `idx_reset_expiry` (`expires_at`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_position_per_election` (`election_id`,`title`),
  ADD KEY `idx_positions_election` (`election_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_students_department` (`department`),
  ADD KEY `idx_students_level` (`level`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_one_vote_per_position` (`student_id`,`position_id`),
  ADD KEY `fk_votes_position` (`position_id`),
  ADD KEY `idx_votes_election` (`election_id`),
  ADD KEY `idx_votes_candidate` (`candidate_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_otps`
--
ALTER TABLE `email_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `fk_candidates_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_candidates_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_candidates_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `elections`
--
ALTER TABLE `elections`
  ADD CONSTRAINT `fk_elections_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD CONSTRAINT `fk_otp_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_otp_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `fk_positions_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `fk_votes_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_votes_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_votes_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_votes_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
