-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Aug 23, 2025 at 05:54 PM
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
-- Database: `restorativecare`
--

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `admitted_on` datetime DEFAULT current_timestamp(),
  `status` enum('admitted','discharged') DEFAULT 'admitted',
  `qr_code_path` varchar(255) DEFAULT NULL,
  `admission_pdf_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admissions`
--

INSERT INTO `admissions` (`id`, `patient_id`, `admitted_on`, `status`, `qr_code_path`, `admission_pdf_path`) VALUES
(1, 1, '2025-08-01 11:00:00', 'admitted', 'qr/adm_1.png', 'pdf/admission_1.pdf'),
(2, 2, '2025-08-01 12:00:00', 'admitted', 'qr/adm_2.png', 'pdf/admission_2.pdf'),
(3, 3, '2025-08-01 13:00:00', 'admitted', 'qr/adm_3.png', 'pdf/admission_3.pdf'),
(4, 4, '2025-08-01 14:00:00', 'admitted', 'qr/adm_4.png', 'pdf/admission_4.pdf'),
(5, 5, '2025-08-01 15:00:00', 'admitted', 'qr/adm_5.png', 'pdf/admission_5.pdf'),
(6, 6, '2025-08-01 16:00:00', 'admitted', 'qr/adm_6.png', 'pdf/admission_6.pdf'),
(7, 7, '2025-08-01 17:00:00', 'discharged', 'qr/adm_7.png', 'pdf/admission_7.pdf'),
(8, 8, '2025-08-01 18:00:00', 'discharged', 'qr/adm_8.png', 'pdf/admission_8.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `ambulance_tracking`
--

CREATE TABLE `ambulance_tracking` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('enroute','arrived','completed') DEFAULT 'enroute',
  `eta` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ambulance_tracking`
--

INSERT INTO `ambulance_tracking` (`id`, `patient_id`, `location`, `status`, `eta`, `updated_at`) VALUES
(1, 1, 'Loc 1.00, 1.50', 'enroute', '2025-09-01 05:30:00', '2025-09-01 09:30:00'),
(2, 2, 'Loc 2.00, 2.50', 'arrived', '2025-09-01 06:30:00', '2025-09-01 10:30:00'),
(3, 3, 'Loc 3.00, 3.50', 'completed', '2025-09-01 07:30:00', '2025-09-01 11:30:00'),
(4, 4, 'Loc 4.00, 4.50', 'enroute', '2025-09-01 08:30:00', '2025-09-01 12:30:00'),
(5, 5, 'Loc 5.00, 5.50', 'arrived', '2025-09-01 09:30:00', '2025-09-01 13:30:00'),
(6, 6, 'Loc 6.00, 6.50', 'completed', '2025-09-01 10:30:00', '2025-09-01 14:30:00'),
(7, 7, 'Loc 7.00, 7.50', 'enroute', '2025-09-01 11:30:00', '2025-09-01 15:30:00'),
(8, 8, 'Loc 8.00, 8.50', 'arrived', '2025-09-01 12:30:00', '2025-09-01 16:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `urgency` enum('low','medium','high') DEFAULT 'low'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `scheduled_at`, `status`, `urgency`) VALUES
(1, 1, 5, '2025-08-06 11:00:00', 'scheduled', 'medium'),
(2, 2, 6, '2025-08-06 12:00:00', 'scheduled', 'high'),
(3, 3, 5, '2025-08-06 13:00:00', 'scheduled', 'low'),
(4, 4, 6, '2025-08-06 14:00:00', 'scheduled', 'medium'),
(5, 5, 5, '2025-08-06 15:00:00', 'scheduled', 'high'),
(6, 6, 6, '2025-08-06 16:00:00', 'scheduled', 'low'),
(7, 7, 5, '2025-08-06 17:00:00', 'completed', 'medium'),
(8, 8, 6, '2025-08-06 18:00:00', 'completed', 'high');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'login', 'Action login by user 1', '2025-08-07 05:30:00'),
(2, 2, 'update', 'Action update by user 2', '2025-08-07 06:30:00'),
(3, 3, 'login', 'Action login by user 3', '2025-08-07 07:30:00'),
(4, 4, 'update', 'Action update by user 4', '2025-08-07 08:30:00'),
(5, 5, 'login', 'Action login by user 5', '2025-08-07 09:30:00'),
(6, 6, 'update', 'Action update by user 6', '2025-08-07 10:30:00'),
(7, 7, 'login', 'Action login by user 7', '2025-08-07 11:30:00'),
(8, 8, 'update', 'Action update by user 8', '2025-08-07 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `beds`
--

CREATE TABLE `beds` (
  `totalbeds` int(11) DEFAULT NULL,
  `occupied_beds` int(11) DEFAULT NULL,
  `bedsleft` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `content`, `author_id`, `created_at`) VALUES
(1, 'Health Tips 1', 'Content for blog post 1.', 7, '2025-08-08 05:30:00'),
(2, 'Health Tips 2', 'Content for blog post 2.', 8, '2025-08-08 06:30:00'),
(3, 'Health Tips 3', 'Content for blog post 3.', 7, '2025-08-08 07:30:00'),
(4, 'Health Tips 4', 'Content for blog post 4.', 8, '2025-08-08 08:30:00'),
(5, 'Health Tips 5', 'Content for blog post 5.', 7, '2025-08-08 09:30:00'),
(6, 'Health Tips 6', 'Content for blog post 6.', 8, '2025-08-08 10:30:00'),
(7, 'Health Tips 7', 'Content for blog post 7.', 7, '2025-08-08 11:30:00'),
(8, 'Health Tips 8', 'Content for blog post 8.', 8, '2025-08-08 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `blood_inventory`
--

CREATE TABLE `blood_inventory` (
  `id` int(11) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') DEFAULT NULL,
  `units_available` int(11) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blood_inventory`
--

INSERT INTO `blood_inventory` (`id`, `blood_group`, `units_available`, `last_updated`) VALUES
(1, 'A+', 6, '2025-08-20 05:30:00'),
(2, 'A-', 7, '2025-08-20 06:30:00'),
(3, 'B+', 8, '2025-08-20 07:30:00'),
(4, 'B-', 9, '2025-08-20 08:30:00'),
(5, 'O+', 10, '2025-08-20 09:30:00'),
(6, 'O-', 11, '2025-08-20 10:30:00'),
(7, 'AB+', 12, '2025-08-20 11:30:00'),
(8, 'AB-', 13, '2025-08-20 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `case_notes`
--

CREATE TABLE `case_notes` (
  `id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_notes`
--

INSERT INTO `case_notes` (`id`, `admission_id`, `doctor_id`, `note`, `created_at`) VALUES
(1, 1, 5, 'Case note 1 for admission 1', '2025-08-09 05:30:00'),
(2, 2, 6, 'Case note 2 for admission 2', '2025-08-09 06:30:00'),
(3, 3, 5, 'Case note 3 for admission 3', '2025-08-09 07:30:00'),
(4, 4, 6, 'Case note 4 for admission 4', '2025-08-09 08:30:00'),
(5, 5, 5, 'Case note 5 for admission 5', '2025-08-09 09:30:00'),
(6, 6, 6, 'Case note 6 for admission 6', '2025-08-09 10:30:00'),
(7, 7, 5, 'Case note 7 for admission 7', '2025-08-09 11:30:00'),
(8, 8, 6, 'Case note 8 for admission 8', '2025-08-09 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `cognitive_tests`
--

CREATE TABLE `cognitive_tests` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `test_type` varchar(100) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `taken_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cognitive_tests`
--

INSERT INTO `cognitive_tests` (`id`, `patient_id`, `test_type`, `score`, `taken_at`) VALUES
(1, 1, 'MMSE-1', 25.50, '2025-08-10 05:30:00'),
(2, 2, 'MMSE-2', 26.00, '2025-08-10 06:30:00'),
(3, 3, 'MMSE-3', 26.50, '2025-08-10 07:30:00'),
(4, 4, 'MMSE-4', 27.00, '2025-08-10 08:30:00'),
(5, 5, 'MMSE-5', 27.50, '2025-08-10 09:30:00'),
(6, 6, 'MMSE-6', 28.00, '2025-08-10 10:30:00'),
(7, 7, 'MMSE-7', 28.50, '2025-08-10 11:30:00'),
(8, 8, 'MMSE-8', 29.00, '2025-08-10 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `consent_forms`
--

CREATE TABLE `consent_forms` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `form_type` varchar(100) DEFAULT NULL,
  `signed_by` int(11) DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `consent_forms`
--

INSERT INTO `consent_forms` (`id`, `patient_id`, `form_type`, `signed_by`, `signature_path`, `created_at`) VALUES
(1, 1, 'General-1', 7, 'sigs/consent_1.png', '2025-08-11 05:30:00'),
(2, 2, 'General-2', 8, 'sigs/consent_2.png', '2025-08-11 06:30:00'),
(3, 3, 'General-3', 7, 'sigs/consent_3.png', '2025-08-11 07:30:00'),
(4, 4, 'General-4', 8, 'sigs/consent_4.png', '2025-08-11 08:30:00'),
(5, 5, 'General-5', 7, 'sigs/consent_5.png', '2025-08-11 09:30:00'),
(6, 6, 'General-6', 8, 'sigs/consent_6.png', '2025-08-11 10:30:00'),
(7, 7, 'General-7', 7, 'sigs/consent_7.png', '2025-08-11 11:30:00'),
(8, 8, 'General-8', 8, 'sigs/consent_8.png', '2025-08-11 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Visitor 1', 'visitor1@mail.test', 'Message content 1', '2025-08-21 05:30:00'),
(2, 'Visitor 2', 'visitor2@mail.test', 'Message content 2', '2025-08-21 06:30:00'),
(3, 'Visitor 3', 'visitor3@mail.test', 'Message content 3', '2025-08-21 07:30:00'),
(4, 'Visitor 4', 'visitor4@mail.test', 'Message content 4', '2025-08-21 08:30:00'),
(5, 'Visitor 5', 'visitor5@mail.test', 'Message content 5', '2025-08-21 09:30:00'),
(6, 'Visitor 6', 'visitor6@mail.test', 'Message content 6', '2025-08-21 10:30:00'),
(7, 'Visitor 7', 'visitor7@mail.test', 'Message content 7', '2025-08-21 11:30:00'),
(8, 'Visitor 8', 'visitor8@mail.test', 'Message content 8', '2025-08-21 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `discharge_summaries`
--

CREATE TABLE `discharge_summaries` (
  `id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `summary` text DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discharge_summaries`
--

INSERT INTO `discharge_summaries` (`id`, `admission_id`, `summary`, `pdf_path`, `qr_code_path`, `created_at`) VALUES
(1, 1, 'Discharge summary 1', 'discharge/summary_1.pdf', 'qr/discharge_1.png', '2025-08-12 05:30:00'),
(2, 2, 'Discharge summary 2', 'discharge/summary_2.pdf', 'qr/discharge_2.png', '2025-08-12 06:30:00'),
(3, 3, 'Discharge summary 3', 'discharge/summary_3.pdf', 'qr/discharge_3.png', '2025-08-12 07:30:00'),
(4, 4, 'Discharge summary 4', 'discharge/summary_4.pdf', 'qr/discharge_4.png', '2025-08-12 08:30:00'),
(5, 5, 'Discharge summary 5', 'discharge/summary_5.pdf', 'qr/discharge_5.png', '2025-08-12 09:30:00'),
(6, 6, 'Discharge summary 6', 'discharge/summary_6.pdf', 'qr/discharge_6.png', '2025-08-12 10:30:00'),
(7, 7, 'Discharge summary 7', 'discharge/summary_7.pdf', 'qr/discharge_7.png', '2025-08-12 11:30:00'),
(8, 8, 'Discharge summary 8', 'discharge/summary_8.pdf', 'qr/discharge_8.png', '2025-08-12 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `donor_requests`
--

CREATE TABLE `donor_requests` (
  `id` int(11) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') DEFAULT NULL,
  `units_needed` int(11) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','fulfilled','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donor_requests`
--

INSERT INTO `donor_requests` (`id`, `blood_group`, `units_needed`, `requested_at`, `status`) VALUES
(1, 'A+', 2, '2025-08-22 05:30:00', 'fulfilled'),
(2, 'A-', 3, '2025-08-22 06:30:00', 'cancelled'),
(3, 'B+', 1, '2025-08-22 07:30:00', 'pending'),
(4, 'B-', 2, '2025-08-22 08:30:00', 'fulfilled'),
(5, 'O+', 3, '2025-08-22 09:30:00', 'cancelled'),
(6, 'O-', 1, '2025-08-22 10:30:00', 'pending'),
(7, 'AB+', 2, '2025-08-22 11:30:00', 'fulfilled'),
(8, 'AB-', 3, '2025-08-22 12:30:00', 'cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `ecg_data`
--

CREATE TABLE `ecg_data` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `data_path` varchar(255) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecg_data`
--

INSERT INTO `ecg_data` (`id`, `patient_id`, `data_path`, `recorded_at`) VALUES
(1, 1, 'signals/ecg_1.csv', '2025-08-13 05:30:00'),
(2, 2, 'signals/ecg_2.csv', '2025-08-13 06:30:00'),
(3, 3, 'signals/ecg_3.csv', '2025-08-13 07:30:00'),
(4, 4, 'signals/ecg_4.csv', '2025-08-13 08:30:00'),
(5, 5, 'signals/ecg_5.csv', '2025-08-13 09:30:00'),
(6, 6, 'signals/ecg_6.csv', '2025-08-13 10:30:00'),
(7, 7, 'signals/ecg_7.csv', '2025-08-13 11:30:00'),
(8, 8, 'signals/ecg_8.csv', '2025-08-13 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `eeg_data`
--

CREATE TABLE `eeg_data` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `data_path` varchar(255) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `eeg_data`
--

INSERT INTO `eeg_data` (`id`, `patient_id`, `data_path`, `recorded_at`) VALUES
(1, 1, 'signals/eeg_1.edf', '2025-08-14 05:30:00'),
(2, 2, 'signals/eeg_2.edf', '2025-08-14 06:30:00'),
(3, 3, 'signals/eeg_3.edf', '2025-08-14 07:30:00'),
(4, 4, 'signals/eeg_4.edf', '2025-08-14 08:30:00'),
(5, 5, 'signals/eeg_5.edf', '2025-08-14 09:30:00'),
(6, 6, 'signals/eeg_6.edf', '2025-08-14 10:30:00'),
(7, 7, 'signals/eeg_7.edf', '2025-08-14 11:30:00'),
(8, 8, 'signals/eeg_8.edf', '2025-08-14 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `patient_id`, `rating`, `comments`, `created_at`) VALUES
(1, 1, 2, 'Feedback 1', '2025-08-15 05:30:00'),
(2, 2, 3, 'Feedback 2', '2025-08-15 06:30:00'),
(3, 3, 4, 'Feedback 3', '2025-08-15 07:30:00'),
(4, 4, 5, 'Feedback 4', '2025-08-15 08:30:00'),
(5, 5, 1, 'Feedback 5', '2025-08-15 09:30:00'),
(6, 6, 2, 'Feedback 6', '2025-08-15 10:30:00'),
(7, 7, 3, 'Feedback 7', '2025-08-15 11:30:00'),
(8, 8, 4, 'Feedback 8', '2025-08-15 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `relation` varchar(50) DEFAULT NULL,
  `access_level` enum('view','update') DEFAULT 'view'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guardians`
--

INSERT INTO `guardians` (`id`, `user_id`, `patient_id`, `relation`, `access_level`) VALUES
(1, 7, 1, 'guardian', 'view'),
(2, 7, 2, 'guardian', 'update'),
(3, 7, 3, 'guardian', 'view'),
(4, 7, 4, 'guardian', 'update'),
(5, 8, 5, 'guardian', 'view'),
(6, 8, 6, 'guardian', 'update'),
(7, 8, 7, 'guardian', 'view'),
(8, 8, 8, 'guardian', 'update');

-- --------------------------------------------------------

--
-- Table structure for table `housekeeping_logs`
--

CREATE TABLE `housekeeping_logs` (
  `id` int(11) NOT NULL,
  `room_no` varchar(20) DEFAULT NULL,
  `service_type` enum('cleaning','laundry','food') DEFAULT NULL,
  `status` enum('pending','done') DEFAULT 'pending',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `housekeeping_logs`
--

INSERT INTO `housekeeping_logs` (`id`, `room_no`, `service_type`, `status`, `updated_at`) VALUES
(1, 'W01-R001', 'cleaning', 'pending', '2025-08-23 05:30:00'),
(2, 'W02-R002', 'laundry', 'done', '2025-08-23 06:30:00'),
(3, 'W03-R003', 'food', 'pending', '2025-08-23 07:30:00'),
(4, 'W04-R004', 'cleaning', 'done', '2025-08-23 08:30:00'),
(5, 'W05-R005', 'laundry', 'pending', '2025-08-23 09:30:00'),
(6, 'W06-R006', 'food', 'done', '2025-08-23 10:30:00'),
(7, 'W07-R007', 'cleaning', 'pending', '2025-08-23 11:30:00'),
(8, 'W08-R008', 'food', 'done', '2025-08-23 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `immersive_sessions`
--

CREATE TABLE `immersive_sessions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `type` enum('visual','audio','environmental') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `immersive_sessions`
--

INSERT INTO `immersive_sessions` (`id`, `patient_id`, `type`, `description`, `file_path`, `started_at`) VALUES
(1, 1, 'visual', 'Visual session 1', 'sessions/visual_1.bin', '2025-08-16 05:30:00'),
(2, 2, 'audio', 'Audio session 2', 'sessions/audio_2.bin', '2025-08-16 06:30:00'),
(3, 3, 'environmental', 'Environmental session 3', 'sessions/environmental_3.bin', '2025-08-16 07:30:00'),
(4, 4, 'visual', 'Visual session 4', 'sessions/visual_4.bin', '2025-08-16 08:30:00'),
(5, 5, 'audio', 'Audio session 5', 'sessions/audio_5.bin', '2025-08-16 09:30:00'),
(6, 6, 'environmental', 'Environmental session 6', 'sessions/environmental_6.bin', '2025-08-16 10:30:00'),
(7, 7, 'visual', 'Visual session 7', 'sessions/visual_7.bin', '2025-08-16 11:30:00'),
(8, 8, 'audio', 'Audio session 8', 'sessions/audio_8.bin', '2025-08-16 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `insurance_claims`
--

CREATE TABLE `insurance_claims` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `provider` varchar(100) DEFAULT NULL,
  `claim_status` enum('submitted','approved','rejected','processing') DEFAULT 'submitted',
  `claim_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `insurance_claims`
--

INSERT INTO `insurance_claims` (`id`, `patient_id`, `provider`, `claim_status`, `claim_amount`, `created_at`) VALUES
(1, 1, 'Provider 1', 'submitted', 10500.00, '2025-08-17 05:30:00'),
(2, 2, 'Provider 2', 'approved', 11000.00, '2025-08-17 06:30:00'),
(3, 3, 'Provider 3', 'rejected', 11500.00, '2025-08-17 07:30:00'),
(4, 4, 'Provider 1', 'processing', 12000.00, '2025-08-17 08:30:00'),
(5, 5, 'Provider 2', 'submitted', 12500.00, '2025-08-17 09:30:00'),
(6, 6, 'Provider 3', 'approved', 13000.00, '2025-08-17 10:30:00'),
(7, 7, 'Provider 1', 'rejected', 13500.00, '2025-08-17 11:30:00'),
(8, 8, 'Provider 2', 'processing', 14000.00, '2025-08-17 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `threshold` int(11) DEFAULT 5,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `stock`, `threshold`, `last_updated`) VALUES
(1, 'Item 1', 11, 6, '2025-08-18 05:30:00'),
(2, 'Item 2', 12, 7, '2025-08-18 06:30:00'),
(3, 'Item 3', 13, 5, '2025-08-18 07:30:00'),
(4, 'Item 4', 14, 6, '2025-08-18 08:30:00'),
(5, 'Item 5', 15, 7, '2025-08-18 09:30:00'),
(6, 'Item 6', 16, 5, '2025-08-18 10:30:00'),
(7, 'Item 7', 17, 6, '2025-08-18 11:30:00'),
(8, 'Item 8', 18, 7, '2025-08-18 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `lab_orders`
--

CREATE TABLE `lab_orders` (
  `id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `test_name` varchar(100) DEFAULT NULL,
  `ordered_by` int(11) DEFAULT NULL,
  `status` enum('ordered','in_progress','completed') DEFAULT 'ordered',
  `ordered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lab_orders`
--

INSERT INTO `lab_orders` (`id`, `admission_id`, `test_name`, `ordered_by`, `status`, `ordered_at`) VALUES
(1, 1, 'CBC', 5, 'ordered', '2025-08-04 05:30:00'),
(2, 2, 'LFT', 6, 'ordered', '2025-08-04 06:30:00'),
(3, 3, 'KFT', 5, 'ordered', '2025-08-04 07:30:00'),
(4, 4, 'CRP', 6, 'ordered', '2025-08-04 08:30:00'),
(5, 5, 'X-Ray', 5, 'ordered', '2025-08-04 09:30:00'),
(6, 6, 'MRI', 6, 'ordered', '2025-08-04 10:30:00'),
(7, 7, 'CT', 5, 'ordered', '2025-08-04 11:30:00'),
(8, 8, 'ECG', 6, 'ordered', '2025-08-04 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `lab_results`
--

CREATE TABLE `lab_results` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `result` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lab_results`
--

INSERT INTO `lab_results` (`id`, `order_id`, `result`, `file_path`, `created_at`) VALUES
(1, 1, 'Result for order 1: normal', 'lab/results_1.pdf', '2025-08-05 05:30:00'),
(2, 2, 'Result for order 2: normal', 'lab/results_2.pdf', '2025-08-05 06:30:00'),
(3, 3, 'Result for order 3: normal', 'lab/results_3.pdf', '2025-08-05 07:30:00'),
(4, 4, 'Result for order 4: normal', 'lab/results_4.pdf', '2025-08-05 08:30:00'),
(5, 5, 'Result for order 5: normal', 'lab/results_5.pdf', '2025-08-05 09:30:00'),
(6, 6, 'Result for order 6: normal', 'lab/results_6.pdf', '2025-08-05 10:30:00'),
(7, 7, 'Result for order 7: normal', 'lab/results_7.pdf', '2025-08-05 11:30:00'),
(8, 8, 'Result for order 8: normal', 'lab/results_8.pdf', '2025-08-05 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `id` int(11) NOT NULL,
  `treatment_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `frequency` varchar(50) DEFAULT NULL,
  `prescribed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`id`, `treatment_id`, `name`, `dosage`, `frequency`, `prescribed_by`) VALUES
(1, 1, 'Med-1', '10mg', 'OD', 5),
(2, 2, 'Med-2', '15mg', 'BID', 6),
(3, 3, 'Med-3', '20mg', 'OD', 5),
(4, 4, 'Med-4', '25mg', 'BID', 6),
(5, 5, 'Med-5', '30mg', 'OD', 5),
(6, 6, 'Med-6', '35mg', 'BID', 6),
(7, 7, 'Med-7', '40mg', 'OD', 5),
(8, 8, 'Med-8', '45mg', 'BID', 6);

-- --------------------------------------------------------

--
-- Table structure for table `medication_logs`
--

CREATE TABLE `medication_logs` (
  `id` int(11) NOT NULL,
  `medication_id` int(11) NOT NULL,
  `taken_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medication_logs`
--

INSERT INTO `medication_logs` (`id`, `medication_id`, `taken_at`) VALUES
(1, 1, '2025-08-02 11:00:00'),
(2, 2, '2025-08-02 12:00:00'),
(3, 3, '2025-08-02 13:00:00'),
(4, 4, '2025-08-02 14:00:00'),
(5, 5, '2025-08-02 15:00:00'),
(6, 6, '2025-08-02 16:00:00'),
(7, 7, '2025-08-02 17:00:00'),
(8, 8, '2025-08-02 18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `mood_logs`
--

CREATE TABLE `mood_logs` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `mood` enum('happy','neutral','sad','anxious','angry') NOT NULL,
  `note` text DEFAULT NULL,
  `logged_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mood_logs`
--

INSERT INTO `mood_logs` (`id`, `patient_id`, `mood`, `note`, `logged_at`) VALUES
(1, 1, 'happy', 'Mood note 1', '2025-08-24 11:00:00'),
(2, 2, 'neutral', 'Mood note 2', '2025-08-24 12:00:00'),
(3, 3, 'sad', 'Mood note 3', '2025-08-24 13:00:00'),
(4, 4, 'anxious', 'Mood note 4', '2025-08-24 14:00:00'),
(5, 5, 'angry', 'Mood note 5', '2025-08-24 15:00:00'),
(6, 6, 'happy', 'Mood note 6', '2025-08-24 16:00:00'),
(7, 7, 'neutral', 'Mood note 7', '2025-08-24 17:00:00'),
(8, 8, 'sad', 'Mood note 8', '2025-08-24 18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `urgency` enum('low','medium','high') DEFAULT 'low',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `urgency`, `created_at`, `read_at`) VALUES
(1, 1, 'Notification 1', 'medium', '2025-08-25 05:30:00', NULL),
(2, 2, 'Notification 2', 'high', '2025-08-25 06:30:00', '2025-08-26 12:00:00'),
(3, 3, 'Notification 3', 'low', '2025-08-25 07:30:00', NULL),
(4, 4, 'Notification 4', 'medium', '2025-08-25 08:30:00', '2025-08-26 14:00:00'),
(5, 5, 'Notification 5', 'high', '2025-08-25 09:30:00', NULL),
(6, 6, 'Notification 6', 'low', '2025-08-25 10:30:00', '2025-08-26 16:00:00'),
(7, 7, 'Notification 7', 'medium', '2025-08-25 11:30:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ot_schedule`
--

CREATE TABLE `ot_schedule` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `scheduled_time` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ot_schedule`
--

INSERT INTO `ot_schedule` (`id`, `patient_id`, `doctor_id`, `scheduled_time`, `duration_minutes`, `status`) VALUES
(1, 1, 5, '2025-08-27 11:00:00', 35, 'scheduled'),
(2, 2, 6, '2025-08-27 12:00:00', 40, 'scheduled'),
(3, 3, 5, '2025-08-27 13:00:00', 45, 'scheduled'),
(4, 4, 6, '2025-08-27 14:00:00', 50, 'scheduled'),
(5, 5, 5, '2025-08-27 15:00:00', 55, 'scheduled'),
(6, 6, 6, '2025-08-27 16:00:00', 60, 'scheduled'),
(7, 7, 5, '2025-08-27 17:00:00', 65, 'cancelled'),
(8, 8, 6, '2025-08-27 18:00:00', 70, 'cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `dob`, `gender`, `address`) VALUES
(1, 1, '2045-07-28', 'male', '#101, Health Street, City 1'),
(2, 2, '2045-07-29', 'female', '#102, Health Street, City 2'),
(3, 3, '2045-07-30', 'other', '#103, Health Street, City 3'),
(4, 4, '2045-07-31', 'male', '#104, Health Street, City 4'),
(5, 5, '2045-08-01', 'female', '#105, Health Street, City 5'),
(6, 6, '2045-08-02', 'male', '#106, Health Street, City 6'),
(7, 7, '2045-08-03', 'female', '#107, Health Street, City 7'),
(8, 8, '2045-08-04', 'other', '#108, Health Street, City 8');

-- --------------------------------------------------------

--
-- Table structure for table `patient_transfers`
--

CREATE TABLE `patient_transfers` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `from_ward` varchar(100) DEFAULT NULL,
  `to_ward` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `transferred_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_transfers`
--

INSERT INTO `patient_transfers` (`id`, `patient_id`, `from_ward`, `to_ward`, `reason`, `transferred_at`) VALUES
(1, 1, 'Ward-2', 'Ward-3', 'Reason 1', '2025-08-28 05:30:00'),
(2, 2, 'Ward-3', 'Ward-4', 'Reason 2', '2025-08-28 06:30:00'),
(3, 3, 'Ward-1', 'Ward-2', 'Reason 3', '2025-08-28 07:30:00'),
(4, 4, 'Ward-2', 'Ward-3', 'Reason 4', '2025-08-28 08:30:00'),
(5, 5, 'Ward-3', 'Ward-4', 'Reason 5', '2025-08-28 09:30:00'),
(6, 6, 'Ward-1', 'Ward-2', 'Reason 6', '2025-08-28 10:30:00'),
(7, 7, 'Ward-2', 'Ward-3', 'Reason 7', '2025-08-28 11:30:00'),
(8, 8, 'Ward-3', 'Ward-4', 'Reason 8', '2025-08-28 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `patient_vitals`
--

CREATE TABLE `patient_vitals` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `bp` varchar(20) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `spo2` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_vitals`
--

INSERT INTO `patient_vitals` (`id`, `patient_id`, `bp`, `heart_rate`, `temperature`, `spo2`, `respiratory_rate`, `logged_at`) VALUES
(1, 1, '111/71', 61, 98.5, 96, 13, '2025-08-29 05:30:00'),
(2, 2, '112/72', 62, 99.0, 97, 14, '2025-08-29 06:30:00'),
(3, 3, '113/73', 63, 98.0, 98, 15, '2025-08-29 07:30:00'),
(4, 4, '114/74', 64, 98.5, 99, 12, '2025-08-29 08:30:00'),
(5, 5, '115/75', 65, 99.0, 95, 13, '2025-08-29 09:30:00'),
(6, 6, '116/76', 66, 98.0, 96, 14, '2025-08-29 10:30:00'),
(7, 7, '117/77', 67, 98.5, 97, 15, '2025-08-29 11:30:00'),
(8, 8, '118/78', 68, 99.0, 98, 12, '2025-08-29 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `method` enum('cash','card','upi','insurance') DEFAULT NULL,
  `status` enum('pending','paid','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `patient_id`, `amount`, `method`, `status`, `created_at`) VALUES
(1, 1, 600.00, 'cash', 'paid', '2025-08-30 05:30:00'),
(2, 2, 700.00, 'card', 'refunded', '2025-08-30 06:30:00'),
(3, 3, 800.00, 'upi', 'pending', '2025-08-30 07:30:00'),
(4, 4, 900.00, 'insurance', 'paid', '2025-08-30 08:30:00'),
(5, 5, 1000.00, 'cash', 'refunded', '2025-08-30 09:30:00'),
(6, 6, 1100.00, 'card', 'pending', '2025-08-30 10:30:00'),
(7, 7, 1200.00, 'upi', 'paid', '2025-08-30 11:30:00'),
(8, 8, 1300.00, 'insurance', 'refunded', '2025-08-30 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('video','article','pdf') DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `title`, `type`, `url`, `uploaded_at`) VALUES
(1, 'Resource 1', 'video', 'https://example.com/resource/1', '2025-08-19 05:30:00'),
(2, 'Resource 2', 'article', 'https://example.com/resource/2', '2025-08-19 06:30:00'),
(3, 'Resource 3', 'pdf', 'https://example.com/resource/3', '2025-08-19 07:30:00'),
(4, 'Resource 4', 'video', 'https://example.com/resource/4', '2025-08-19 08:30:00'),
(5, 'Resource 5', 'article', 'https://example.com/resource/5', '2025-08-19 09:30:00'),
(6, 'Resource 6', 'pdf', 'https://example.com/resource/6', '2025-08-19 10:30:00'),
(7, 'Resource 7', 'video', 'https://example.com/resource/7', '2025-08-19 11:30:00'),
(8, 'Resource 8', 'pdf', 'https://example.com/resource/8', '2025-08-19 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `staff_schedule`
--

CREATE TABLE `staff_schedule` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `shift_start` datetime DEFAULT NULL,
  `shift_end` datetime DEFAULT NULL,
  `role` enum('doctor','nurse','admin','support') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_schedule`
--

INSERT INTO `staff_schedule` (`id`, `staff_id`, `shift_start`, `shift_end`, `role`) VALUES
(1, 5, '2025-08-31 18:00:00', '2025-08-31 22:00:00', 'doctor'),
(2, 6, '2025-09-01 02:00:00', '2025-09-01 06:00:00', 'doctor'),
(3, 7, '2025-09-01 10:00:00', '2025-09-01 14:00:00', 'nurse'),
(4, 8, '2025-09-01 18:00:00', '2025-09-01 22:00:00', 'admin'),
(5, 5, '2025-09-02 02:00:00', '2025-09-02 06:00:00', 'doctor'),
(6, 6, '2025-09-02 10:00:00', '2025-09-02 14:00:00', 'doctor'),
(7, 7, '2025-09-02 18:00:00', '2025-09-02 22:00:00', 'nurse'),
(8, 8, '2025-09-03 02:00:00', '2025-09-03 06:00:00', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `treatment_plans`
--

CREATE TABLE `treatment_plans` (
  `id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `progress` tinyint(3) UNSIGNED DEFAULT 0 CHECK (`progress` between 0 and 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `treatment_plans`
--

INSERT INTO `treatment_plans` (`id`, `admission_id`, `description`, `progress`) VALUES
(1, 1, 'Treatment plan #1 for admission 1', 10),
(2, 2, 'Treatment plan #2 for admission 2', 20),
(3, 3, 'Treatment plan #3 for admission 3', 30),
(4, 4, 'Treatment plan #4 for admission 4', 40),
(5, 5, 'Treatment plan #5 for admission 5', 50),
(6, 6, 'Treatment plan #6 for admission 6', 60),
(7, 7, 'Treatment plan #7 for admission 7', 70),
(8, 8, 'Treatment plan #8 for admission 8', 80);

-- --------------------------------------------------------

--
-- Table structure for table `triage`
--

CREATE TABLE `triage` (
  `id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'low',
  `priority` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `triage`
--

INSERT INTO `triage` (`id`, `admission_id`, `severity`, `priority`, `created_at`) VALUES
(1, 1, 'low', 1, '2025-08-03 05:30:00'),
(2, 2, 'medium', 2, '2025-08-03 06:30:00'),
(3, 3, 'high', 3, '2025-08-03 07:30:00'),
(4, 4, 'critical', 4, '2025-08-03 08:30:00'),
(5, 5, 'low', 0, '2025-08-03 09:30:00'),
(6, 6, 'medium', 1, '2025-08-03 10:30:00'),
(7, 7, 'high', 2, '2025-08-03 11:30:00'),
(8, 8, 'critical', 3, '2025-08-03 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('patient','doctor','nurse','admin','superadmin') NOT NULL DEFAULT 'patient',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pswd` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `created_at`, `pswd`) VALUES
(1, 'User 1', 'user1@restorativecare.test', '+91-900000001', 'patient', '2025-08-01 05:30:00', 'admin123'),
(2, 'User 2', 'user2@restorativecare.test', '+91-900000002', 'patient', '2025-08-01 06:30:00', 'admin123'),
(3, 'User 3', 'user3@restorativecare.test', '+91-900000003', 'patient', '2025-08-01 07:30:00', 'admin123'),
(4, 'User 4', 'user4@restorativecare.test', '+91-900000004', 'patient', '2025-08-01 08:30:00', 'admin123'),
(5, 'User 5', 'user5@restorativecare.test', '+91-900000005', 'doctor', '2025-08-01 09:30:00', 'admin123'),
(6, 'User 6', 'user6@restorativecare.test', '+91-900000006', 'doctor', '2025-08-01 10:30:00', 'admin123'),
(7, 'User 7', 'user7@restorativecare.test', '+91-900000007', 'nurse', '2025-08-01 11:30:00', 'admin123'),
(8, 'User 8', 'user8@restorativecare.test', '+91-900000008', 'admin', '2025-08-01 12:30:00', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `relation` varchar(50) DEFAULT NULL,
  `visit_time` datetime DEFAULT current_timestamp(),
  `pass_qr_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `patient_id`, `name`, `relation`, `visit_time`, `pass_qr_path`) VALUES
(1, 1, 'Visitor 1', 'friend', '2025-09-02 11:00:00', 'qr/visitor_1.png'),
(2, 2, 'Visitor 2', 'family', '2025-09-02 12:00:00', 'qr/visitor_2.png'),
(3, 3, 'Visitor 3', 'friend', '2025-09-02 13:00:00', 'qr/visitor_3.png'),
(4, 4, 'Visitor 4', 'family', '2025-09-02 14:00:00', 'qr/visitor_4.png'),
(5, 5, 'Visitor 5', 'friend', '2025-09-02 15:00:00', 'qr/visitor_5.png'),
(6, 6, 'Visitor 6', 'family', '2025-09-02 16:00:00', 'qr/visitor_6.png'),
(7, 7, 'Visitor 7', 'friend', '2025-09-02 17:00:00', 'qr/visitor_7.png'),
(8, 8, 'Visitor 8', 'family', '2025-09-02 18:00:00', 'qr/visitor_8.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_id` (`patient_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `ambulance_tracking`
--
ALTER TABLE `ambulance_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_id` (`patient_id`),
  ADD KEY `idx_doctor_id` (`doctor_id`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);
ALTER TABLE `blog_posts` ADD FULLTEXT KEY `title` (`title`,`content`);

--
-- Indexes for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_notes`
--
ALTER TABLE `case_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admission_id` (`admission_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `cognitive_tests`
--
ALTER TABLE `cognitive_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `consent_forms`
--
ALTER TABLE `consent_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `signed_by` (`signed_by`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`);
ALTER TABLE `contact_messages` ADD FULLTEXT KEY `message` (`message`);

--
-- Indexes for table `discharge_summaries`
--
ALTER TABLE `discharge_summaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admission_id` (`admission_id`);

--
-- Indexes for table `donor_requests`
--
ALTER TABLE `donor_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecg_data`
--
ALTER TABLE `ecg_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `eeg_data`
--
ALTER TABLE `eeg_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`patient_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `housekeeping_logs`
--
ALTER TABLE `housekeeping_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `immersive_sessions`
--
ALTER TABLE `immersive_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `insurance_claims`
--
ALTER TABLE `insurance_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_orders`
--
ALTER TABLE `lab_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admission_id` (`admission_id`),
  ADD KEY `ordered_by` (`ordered_by`);

--
-- Indexes for table `lab_results`
--
ALTER TABLE `lab_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescribed_by` (`prescribed_by`),
  ADD KEY `idx_treatment_id` (`treatment_id`);

--
-- Indexes for table `medication_logs`
--
ALTER TABLE `medication_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_medication_id` (`medication_id`),
  ADD KEY `idx_taken_at` (`taken_at`);

--
-- Indexes for table `mood_logs`
--
ALTER TABLE `mood_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_id` (`patient_id`),
  ADD KEY `idx_logged_at` (`logged_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`read_at`);

--
-- Indexes for table `ot_schedule`
--
ALTER TABLE `ot_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `patient_transfers`
--
ALTER TABLE `patient_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_id` (`patient_id`);

--
-- Indexes for table `patient_vitals`
--
ALTER TABLE `patient_vitals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_id` (`patient_id`),
  ADD KEY `idx_logged_at` (`logged_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_schedule`
--
ALTER TABLE `staff_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `treatment_plans`
--
ALTER TABLE `treatment_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admission_id` (`admission_id`);

--
-- Indexes for table `triage`
--
ALTER TABLE `triage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admission_id` (`admission_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_id` (`patient_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ambulance_tracking`
--
ALTER TABLE `ambulance_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `case_notes`
--
ALTER TABLE `case_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cognitive_tests`
--
ALTER TABLE `cognitive_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `consent_forms`
--
ALTER TABLE `consent_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `discharge_summaries`
--
ALTER TABLE `discharge_summaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `donor_requests`
--
ALTER TABLE `donor_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ecg_data`
--
ALTER TABLE `ecg_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `eeg_data`
--
ALTER TABLE `eeg_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `housekeeping_logs`
--
ALTER TABLE `housekeeping_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `immersive_sessions`
--
ALTER TABLE `immersive_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `insurance_claims`
--
ALTER TABLE `insurance_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `lab_orders`
--
ALTER TABLE `lab_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `lab_results`
--
ALTER TABLE `lab_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `medication_logs`
--
ALTER TABLE `medication_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mood_logs`
--
ALTER TABLE `mood_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ot_schedule`
--
ALTER TABLE `ot_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `patient_transfers`
--
ALTER TABLE `patient_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `patient_vitals`
--
ALTER TABLE `patient_vitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `staff_schedule`
--
ALTER TABLE `staff_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `treatment_plans`
--
ALTER TABLE `treatment_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `triage`
--
ALTER TABLE `triage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admissions`
--
ALTER TABLE `admissions`
  ADD CONSTRAINT `admissions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ambulance_tracking`
--
ALTER TABLE `ambulance_tracking`
  ADD CONSTRAINT `ambulance_tracking_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `case_notes`
--
ALTER TABLE `case_notes`
  ADD CONSTRAINT `case_notes_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_notes_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cognitive_tests`
--
ALTER TABLE `cognitive_tests`
  ADD CONSTRAINT `cognitive_tests_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `consent_forms`
--
ALTER TABLE `consent_forms`
  ADD CONSTRAINT `consent_forms_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consent_forms_ibfk_2` FOREIGN KEY (`signed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `discharge_summaries`
--
ALTER TABLE `discharge_summaries`
  ADD CONSTRAINT `discharge_summaries_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ecg_data`
--
ALTER TABLE `ecg_data`
  ADD CONSTRAINT `ecg_data_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eeg_data`
--
ALTER TABLE `eeg_data`
  ADD CONSTRAINT `eeg_data_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `guardians_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guardians_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `immersive_sessions`
--
ALTER TABLE `immersive_sessions`
  ADD CONSTRAINT `immersive_sessions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `insurance_claims`
--
ALTER TABLE `insurance_claims`
  ADD CONSTRAINT `insurance_claims_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lab_orders`
--
ALTER TABLE `lab_orders`
  ADD CONSTRAINT `lab_orders_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lab_orders_ibfk_2` FOREIGN KEY (`ordered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lab_results`
--
ALTER TABLE `lab_results`
  ADD CONSTRAINT `lab_results_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `lab_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medications`
--
ALTER TABLE `medications`
  ADD CONSTRAINT `medications_ibfk_1` FOREIGN KEY (`treatment_id`) REFERENCES `treatment_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medications_ibfk_2` FOREIGN KEY (`prescribed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `medication_logs`
--
ALTER TABLE `medication_logs`
  ADD CONSTRAINT `medication_logs_ibfk_1` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mood_logs`
--
ALTER TABLE `mood_logs`
  ADD CONSTRAINT `mood_logs_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ot_schedule`
--
ALTER TABLE `ot_schedule`
  ADD CONSTRAINT `ot_schedule_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ot_schedule_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_transfers`
--
ALTER TABLE `patient_transfers`
  ADD CONSTRAINT `patient_transfers_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_vitals`
--
ALTER TABLE `patient_vitals`
  ADD CONSTRAINT `patient_vitals_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_schedule`
--
ALTER TABLE `staff_schedule`
  ADD CONSTRAINT `staff_schedule_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `treatment_plans`
--
ALTER TABLE `treatment_plans`
  ADD CONSTRAINT `treatment_plans_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `triage`
--
ALTER TABLE `triage`
  ADD CONSTRAINT `triage_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visitors`
--
ALTER TABLE `visitors`
  ADD CONSTRAINT `visitors_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
