-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2026 at 05:57 PM
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
-- Database: `geotraverse_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget_allocations`
--

CREATE TABLE `budget_allocations` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `allocated_amount` decimal(15,2) DEFAULT NULL,
  `used_amount` decimal(15,2) DEFAULT 0.00,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budget_allocations`
--

INSERT INTO `budget_allocations` (`id`, `category`, `allocated_amount`, `used_amount`, `year`, `month`, `department_id`, `description`, `created_at`) VALUES
(1, 'Operations', 50000000.00, 25000000.00, 2024, 5, 2, 'Monthly operational expenses including office supplies, utilities, and maintenance', '2026-05-10 16:46:59'),
(2, 'Salaries', 30000000.00, 15000000.00, 2024, 5, 2, 'Staff salaries for May 2024 - All departments', '2026-05-10 16:46:59'),
(3, 'Marketing', 20000000.00, 8000000.00, 2024, 5, 3, 'Digital advertising, print media, and promotional materials', '2026-05-10 16:46:59'),
(4, 'Events & Seminars', 10000000.00, 4000000.00, 2024, 5, 3, 'Industry events, trade shows, and customer seminars', '2026-05-10 16:46:59'),
(5, 'Equipment', 15000000.00, 5000000.00, 2024, 5, 10, 'Survey equipment purchase and maintenance', '2026-05-10 16:46:59'),
(6, 'Software Licenses', 8000000.00, 3500000.00, 2024, 5, 9, 'CAD, Revit, and design software licenses', '2026-05-10 16:46:59'),
(7, 'Training', 8000000.00, 2000000.00, 2024, 5, 4, 'Staff training and professional development', '2026-05-10 16:46:59'),
(8, 'Raw Materials - Bricks', 30000000.00, 15000000.00, 2024, 5, 6, 'Clay, cement, and materials for brick production', '2026-05-10 16:46:59'),
(9, 'Raw Materials - Aluminium', 25000000.00, 8000000.00, 2024, 5, 7, 'Aluminium sheets, glass, and hardware supplies', '2026-05-10 16:46:59'),
(10, 'Construction Materials', 50000000.00, 25000000.00, 2024, 5, 11, 'Cement, steel, timber, and construction supplies', '2026-05-10 16:46:59'),
(11, 'Travel & Transport', 5000000.00, 1500000.00, 2024, 5, 4, 'Staff travel and transport allowances', '2026-05-10 16:46:59'),
(12, 'Printing & Stationery', 2000000.00, 800000.00, 2024, 5, 5, 'Office printing and stationery supplies', '2026-05-10 16:46:59'),
(13, 'Maintenance', 3000000.00, 1000000.00, 2024, 5, 11, 'Equipment and facility maintenance', '2026-05-10 16:46:59'),
(14, 'Utilities', 4000000.00, 2000000.00, 2024, 5, 2, 'Electricity, water, and internet bills', '2026-05-10 16:46:59'),
(15, 'Contingency', 10000000.00, 0.00, 2024, 5, 1, 'Emergency fund for unexpected expenses', '2026-05-10 16:46:59'),
(16, 'Operations', 50000000.00, 25000000.00, 2024, 5, 2, 'Monthly operational expenses', '2026-05-12 10:15:42'),
(17, 'Salaries', 30000000.00, 15000000.00, 2024, 5, 2, 'Staff salaries', '2026-05-12 10:15:42'),
(18, 'Marketing', 20000000.00, 8000000.00, 2024, 5, 3, 'Digital advertising', '2026-05-12 10:15:42'),
(19, 'Raw Materials', 30000000.00, 15000000.00, 2024, 5, 6, 'Clay and cement for bricks', '2026-05-12 10:15:42'),
(20, 'Equipment', 15000000.00, 5000000.00, 2024, 5, 10, 'Survey equipment', '2026-05-12 10:15:42'),
(21, 'Software Licenses', 8000000.00, 3500000.00, 2024, 5, 9, 'CAD software', '2026-05-12 10:15:42'),
(22, 'Training', 8000000.00, 2000000.00, 2024, 5, 4, 'Staff training', '2026-05-12 10:15:42'),
(23, 'Construction Materials', 50000000.00, 25000000.00, 2024, 5, 11, 'Cement and steel', '2026-05-12 10:15:42'),
(24, 'Raw Materials - Aluminium', 25000000.00, 8000000.00, 2024, 5, 7, 'Aluminium sheets', '2026-05-12 10:15:42'),
(25, 'Office Supplies', 2000000.00, 800000.00, 2024, 5, 5, 'Printing and stationery', '2026-05-12 10:15:42'),
(26, 'Operations', 50000000.00, 25000000.00, 2024, 5, 2, 'Monthly operational expenses', '2026-05-12 10:26:09'),
(27, 'Salaries', 30000000.00, 15000000.00, 2024, 5, 2, 'Staff salaries', '2026-05-12 10:26:09'),
(28, 'Marketing', 20000000.00, 8000000.00, 2024, 5, 3, 'Digital advertising', '2026-05-12 10:26:09'),
(29, 'Raw Materials', 30000000.00, 15000000.00, 2024, 5, 6, 'Clay and cement for bricks', '2026-05-12 10:26:09'),
(30, 'Equipment', 15000000.00, 5000000.00, 2024, 5, 10, 'Survey equipment', '2026-05-12 10:26:09'),
(31, 'Software Licenses', 8000000.00, 3500000.00, 2024, 5, 9, 'CAD software', '2026-05-12 10:26:09'),
(32, 'Training', 8000000.00, 2000000.00, 2024, 5, 4, 'Staff training', '2026-05-12 10:26:09'),
(33, 'Construction Materials', 50000000.00, 25000000.00, 2024, 5, 11, 'Cement and steel', '2026-05-12 10:26:09'),
(34, 'Raw Materials - Aluminium', 25000000.00, 8000000.00, 2024, 5, 7, 'Aluminium sheets', '2026-05-12 10:26:09'),
(35, 'Operations', 48000000.00, 45000000.00, 2024, 3, 2, 'March operational expenses', '2026-05-12 12:37:35'),
(36, 'Salaries', 29000000.00, 28000000.00, 2024, 3, 2, 'March staff salaries', '2026-05-12 12:37:35'),
(37, 'Marketing', 18000000.00, 17000000.00, 2024, 3, 3, 'March marketing campaigns', '2026-05-12 12:37:35'),
(38, 'Bricks Production', 28000000.00, 26000000.00, 2024, 3, 6, 'March brick production', '2026-05-12 12:37:35'),
(39, 'Aluminium', 22000000.00, 21000000.00, 2024, 3, 7, 'March aluminium fabrication', '2026-05-12 12:37:35'),
(40, 'Construction', 48000000.00, 45000000.00, 2024, 3, 11, 'March construction costs', '2026-05-12 12:37:35'),
(41, 'Operations', 49000000.00, 47000000.00, 2024, 4, 2, 'April operational expenses', '2026-05-12 12:37:35'),
(42, 'Salaries', 30000000.00, 29000000.00, 2024, 4, 2, 'April staff salaries', '2026-05-12 12:37:35'),
(43, 'Marketing', 19000000.00, 18000000.00, 2024, 4, 3, 'April marketing campaigns', '2026-05-12 12:37:35'),
(44, 'Bricks Production', 30000000.00, 28000000.00, 2024, 4, 6, 'April brick production', '2026-05-12 12:37:35'),
(45, 'Aluminium', 24000000.00, 22000000.00, 2024, 4, 7, 'April aluminium fabrication', '2026-05-12 12:37:35'),
(46, 'Construction', 52000000.00, 50000000.00, 2024, 4, 11, 'April construction costs', '2026-05-12 12:37:35'),
(47, 'Operations', 55000000.00, 20000000.00, 2024, 7, 2, 'July operational expenses (planned)', '2026-05-12 12:37:35'),
(48, 'Salaries', 32000000.00, 15000000.00, 2024, 7, 2, 'July staff salaries (planned)', '2026-05-12 12:37:35'),
(49, 'Marketing', 22000000.00, 8000000.00, 2024, 7, 3, 'July marketing campaigns (planned)', '2026-05-12 12:37:35'),
(50, 'Bricks Production', 35000000.00, 15000000.00, 2024, 7, 6, 'July brick production (planned)', '2026-05-12 12:37:35'),
(51, 'Aluminium', 28000000.00, 12000000.00, 2024, 7, 7, 'July aluminium fabrication (planned)', '2026-05-12 12:37:35'),
(52, 'Construction', 65000000.00, 25000000.00, 2024, 7, 11, 'July construction costs (planned)', '2026-05-12 12:37:35'),
(53, 'Town Planning', 30000000.00, 10000000.00, 2024, 7, 8, 'July town planning activities', '2026-05-12 12:37:35'),
(54, 'Architectural', 25000000.00, 10000000.00, 2024, 7, 9, 'July architectural services', '2026-05-12 12:37:35'),
(55, 'Survey', 35000000.00, 15000000.00, 2024, 7, 10, 'July survey operations', '2026-05-12 12:37:35'),
(56, 'Hatimiliki', 40000000.00, 15000000.00, 2024, 7, 12, 'July title deed processing', '2026-05-12 12:37:35'),
(57, 'Raw Materials - Aluminium Sheets', 25000000.00, 15000000.00, 2024, 5, 7, 'Purchase of aluminium sheets for window frames', '2026-05-18 09:20:10'),
(58, 'Glass Panels', 8000000.00, 5000000.00, 2024, 5, 7, 'Glass panels for windows and doors', '2026-05-18 09:20:10'),
(59, 'Hardware & Accessories', 5000000.00, 2000000.00, 2024, 5, 7, 'Handles, hinges, locks and other hardware', '2026-05-18 09:20:10'),
(60, 'Salaries - Production Staff', 12000000.00, 10000000.00, 2024, 5, 7, 'Monthly salaries for aluminium fabrication team', '2026-05-18 09:20:10'),
(61, 'Equipment Maintenance', 3000000.00, 1000000.00, 2024, 5, 7, 'Maintenance of cutting and welding machines', '2026-05-18 09:20:10'),
(62, 'Transport & Logistics', 4000000.00, 1500000.00, 2024, 5, 7, 'Delivery of finished products to clients', '2026-05-18 09:20:10'),
(63, 'Marketing - Aluminium Products', 5000000.00, 1000000.00, 2024, 5, 7, 'Advertising and promotion of aluminium products', '2026-05-18 09:20:10'),
(64, 'Training & Development', 2000000.00, 500000.00, 2024, 5, 7, 'Staff training on new fabrication techniques', '2026-05-18 09:20:10'),
(65, 'Utilities', 3000000.00, 2000000.00, 2024, 5, 7, 'Electricity and water for production facility', '2026-05-18 09:20:10'),
(66, 'Contingency Fund', 5000000.00, 0.00, 2024, 5, 7, 'Emergency fund for unexpected expenses', '2026-05-18 09:20:10');

-- --------------------------------------------------------

--
-- Table structure for table `budget_tracking`
--

CREATE TABLE `budget_tracking` (
  `id` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `amount_used` decimal(15,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `budget_utilization_view`
-- (See below for the actual view)
--
CREATE TABLE `budget_utilization_view` (
`budget_id` int(11)
,`department_id` int(11)
,`department_name` varchar(100)
,`category` varchar(100)
,`allocated_amount` decimal(15,2)
,`used_amount` decimal(37,2)
,`remaining_amount` decimal(38,2)
,`utilization_percentage` decimal(43,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `sender_dept` int(11) DEFAULT NULL,
  `receiver_dept` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `status` enum('active','archived','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_by_user_id` int(11) DEFAULT NULL,
  `deleted_by_admin` tinyint(4) DEFAULT 0,
  `deleted_by_department` tinyint(4) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by_super_admin` tinyint(1) DEFAULT 0,
  `deleted_by_finance` tinyint(1) DEFAULT 0,
  `deleted_by_sales` tinyint(1) DEFAULT 0,
  `deleted_by_manager` tinyint(1) DEFAULT 0,
  `deleted_by_secretary` tinyint(1) DEFAULT 0,
  `deleted_by_bricks` tinyint(1) DEFAULT 0,
  `deleted_by_aluminium` tinyint(1) DEFAULT 0,
  `deleted_by_town_planning` tinyint(1) DEFAULT 0,
  `deleted_by_architectural` tinyint(1) DEFAULT 0,
  `deleted_by_survey` tinyint(1) DEFAULT 0,
  `deleted_by_construction` tinyint(1) DEFAULT 0,
  `deleted_by_hatimiliki` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `user_id`, `admin_id`, `sender_dept`, `receiver_dept`, `subject`, `status`, `created_at`, `updated_at`, `deleted_by_user_id`, `deleted_by_admin`, `deleted_by_department`, `deleted_at`, `deleted_by_super_admin`, `deleted_by_finance`, `deleted_by_sales`, `deleted_by_manager`, `deleted_by_secretary`, `deleted_by_bricks`, `deleted_by_aluminium`, `deleted_by_town_planning`, `deleted_by_architectural`, `deleted_by_survey`, `deleted_by_construction`, `deleted_by_hatimiliki`) VALUES
(1, 0, NULL, 3, 1, 'New Sales Lead - Major Client', 'active', '2026-05-22 21:12:05', '2026-05-25 06:12:28', NULL, 0, 0, '2026-05-24 15:05:52', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2, 0, NULL, 11, 2, 'Payment Request - Construction Materials', 'active', '2026-05-22 21:12:05', '2026-05-23 22:25:56', NULL, 0, 0, '2026-05-24 01:25:56', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(3, 0, NULL, 7, 3, 'New Aluminium Product Launch', 'active', '2026-05-22 21:12:05', '2026-05-24 23:41:47', NULL, 0, 0, '2026-05-23 15:12:48', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4, 0, NULL, 5, 4, 'Office Closure Notice - Public Holiday', 'active', '2026-05-22 21:12:05', '2026-05-23 11:20:47', NULL, 0, 0, '2026-05-23 14:20:47', 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(5, 0, NULL, 12, 1, 'Digital Title Deed System - Update', 'active', '2026-05-22 21:12:05', '2026-05-24 18:17:31', NULL, 0, 0, NULL, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6, 0, NULL, 6, 11, 'Bricks Supply Status', 'active', '2026-05-22 21:12:05', '2026-05-23 20:53:41', NULL, 0, 0, '2026-05-23 23:52:43', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(7, 0, NULL, 9, 8, 'Design Approval Request', 'active', '2026-05-22 21:12:05', '2026-05-23 21:14:45', NULL, 0, 0, '2026-05-24 00:14:45', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(8, 0, NULL, 10, 12, 'Survey Reports for Title Deeds', 'deleted', '2026-05-22 21:12:05', '2026-05-24 12:24:22', NULL, 0, 0, '2026-05-24 01:23:18', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1),
(9, 0, NULL, 4, 2, 'Q3 Budget Review', 'active', '2026-05-22 21:12:05', '2026-05-23 20:38:30', NULL, 0, 0, '2026-05-23 22:23:35', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(10, 0, NULL, 5, 4, 'New Staff Onboarding Request', 'active', '2026-05-22 21:12:05', '2026-05-24 11:41:53', NULL, 0, 0, '2026-05-24 14:41:53', 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0),
(11, 0, NULL, 3, 7, 'Bulk Aluminium Order - Government Project', 'active', '2026-05-22 21:12:05', '2026-05-24 09:39:19', NULL, 0, 0, '2026-05-24 12:39:19', 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(12, 0, NULL, 11, 6, 'Additional Brick Order', 'active', '2026-05-22 21:12:05', '2026-05-23 20:52:47', NULL, 0, 0, '2026-05-23 23:52:47', 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0),
(13, 0, NULL, 1, 4, 'Company Announcement - New Strategy', 'active', '2026-05-22 21:12:05', '2026-05-24 18:17:36', NULL, 0, 0, '2026-05-24 15:05:48', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(14, 0, NULL, 2, 4, 'Financial Year-End Closing', 'active', '2026-05-22 21:12:05', '2026-05-23 20:30:46', NULL, 0, 0, '2026-05-23 23:30:46', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(15, 0, NULL, 8, 9, 'New Urban Development Project', 'active', '2026-05-22 21:12:05', '2026-05-23 21:14:48', NULL, 0, 0, '2026-05-24 00:14:48', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(16, 0, NULL, 10, 11, 'Site Survey for New Project', 'active', '2026-05-22 21:12:05', '2026-05-23 20:52:50', NULL, 0, 0, '2026-05-23 23:52:50', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(17, 0, NULL, 12, 8, 'Land Use Verification', 'active', '2026-05-22 21:12:05', '2026-05-22 21:16:20', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(18, 0, NULL, 5, 4, 'Eid Holiday Schedule', 'active', '2026-05-22 21:12:05', '2026-05-24 08:52:02', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(19, 0, NULL, 7, 11, 'Aluminium Products Delivery', 'active', '2026-05-22 21:12:05', '2026-05-24 23:06:09', NULL, 0, 0, '2026-05-24 01:25:43', 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0),
(20, 0, NULL, 4, 2, 'Staff Performance Reviews', 'active', '2026-05-22 21:12:05', '2026-05-23 20:30:42', NULL, 0, 0, '2026-05-23 23:30:42', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(34, 1, 1, 1, 2, 'System Updates - Finance Department', 'active', '2024-06-01 06:00:00', '2026-05-25 09:07:34', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(35, 1, 1, 1, 3, 'Sales Performance Review', 'active', '2024-06-02 07:30:00', '2026-05-23 10:33:16', NULL, 1, 0, '2026-05-23 13:33:16', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(36, 1, 1, 1, 4, 'Managerial Strategy Meeting', 'active', '2024-06-03 08:00:00', '2026-05-24 08:36:07', NULL, 0, 1, '2026-05-24 11:36:07', 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(37, 1, 1, 1, 5, 'Secretary Office Procedures', 'active', '2024-06-04 05:15:00', '2026-05-24 18:17:41', 1, 0, 0, NULL, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(38, 1, 1, 1, 6, 'Bricks Production Targets', 'active', '2024-06-05 10:00:00', '2026-05-24 18:17:40', 1, 1, 0, '2026-05-23 17:55:58', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(39, 1, 1, 1, 7, 'Aluminium Order Updates', 'active', '2024-06-06 11:30:00', '2026-06-05 13:02:20', NULL, 0, 0, '2026-05-24 01:20:42', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(40, 1, 1, 1, 8, 'Town Planning Master Plan', 'active', '2024-06-07 06:45:00', '2026-05-25 09:44:21', NULL, 1, 0, '2026-05-23 14:46:38', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(41, 1, 1, 1, 9, 'Architectural Design Approvals', 'deleted', '2024-06-08 07:15:00', '2026-05-24 18:18:15', NULL, 0, 0, '2026-05-24 01:00:18', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(42, 1, 1, 1, 10, 'Survey Equipment Status', 'active', '2024-06-09 08:30:00', '2026-05-25 10:12:51', 1, 0, 0, '2026-05-23 14:46:45', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(43, 1, 1, 1, 11, 'Construction Progress Reports', 'active', '2024-06-10 05:00:00', '2026-05-24 18:17:29', NULL, 1, 0, NULL, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(44, 1, 1, 1, 12, 'Title Deeds Processing', 'active', '2024-06-11 06:20:00', '2026-05-23 22:23:00', NULL, 0, 0, '2026-05-24 01:23:00', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(45, 2, 1, 2, 3, 'Marketing Budget Approval', 'active', '2024-06-12 07:00:00', '2026-05-25 06:11:40', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(46, 2, 1, 2, 4, 'Quarterly Financial Report', 'active', '2024-06-13 06:30:00', '2026-05-23 19:28:00', NULL, 0, 0, '2026-05-23 22:28:00', 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(47, 2, 1, 2, 6, 'Bricks Payment Status', 'active', '2024-06-14 08:45:00', '2026-05-23 19:28:02', NULL, 0, 0, '2026-05-23 22:28:02', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0),
(48, 2, 1, 2, 7, 'Aluminium Invoice Query', 'active', '2024-06-15 11:00:00', '2026-05-24 23:17:48', NULL, 0, 0, '2026-05-23 22:28:04', 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0),
(49, 2, 1, 2, 11, 'Construction Budget Review', 'active', '2024-06-16 05:30:00', '2026-05-23 20:52:34', NULL, 0, 0, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(50, 2, 1, 2, 12, 'Title Deed Revenue Report', 'active', '2024-06-17 10:15:00', '2026-05-24 10:38:59', NULL, 0, 0, '2026-05-24 13:38:59', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(51, 4, 1, 3, 7, 'Aluminium Product Promotion', 'active', '2024-06-18 06:00:00', '2026-05-24 09:18:22', NULL, 0, 0, '2026-05-23 15:13:08', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(52, 4, 1, 3, 11, 'Construction Client Lead', 'active', '2024-06-19 07:30:00', '2026-05-24 10:48:28', NULL, 0, 0, '2026-05-24 13:48:28', 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(53, 4, 1, 3, 12, 'Title Deed Marketing Campaign', 'active', '2024-06-20 08:00:00', '2026-05-24 10:05:06', NULL, 0, 0, '2026-05-24 13:05:06', 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(54, 6, 1, 4, 2, 'Staff Salary Review', 'active', '2024-06-21 05:45:00', '2026-05-24 10:38:55', NULL, 0, 0, '2026-05-24 13:38:55', 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(55, 6, 1, 4, 5, 'Secretary Appointment Schedule', 'deleted', '2024-06-22 06:15:00', '2026-05-25 10:00:33', NULL, 0, 1, '2026-05-23 14:20:57', 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0),
(56, 6, 1, 4, 6, 'Production Capacity Planning', 'active', '2024-06-23 07:00:00', '2026-05-25 09:53:09', NULL, 0, 1, '2026-05-23 17:55:55', 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0),
(57, 6, 1, 4, 9, 'Architectural Project Priorities', 'active', '2024-06-24 10:30:00', '2026-05-24 13:10:22', NULL, 0, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(58, 8, 1, 5, 4, 'Meeting Room Booking', 'deleted', '2024-06-15 06:00:00', '2026-05-25 10:00:37', NULL, 0, 1, '2026-05-23 14:20:46', 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0),
(59, 8, 1, 5, 2, 'Office Supplies Order', 'active', '2024-06-20 08:00:00', '2026-05-23 16:49:27', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(60, 10, 1, 6, 11, 'Bricks Supply for Construction', 'active', '2024-06-25 04:30:00', '2026-05-23 20:52:56', NULL, 0, 0, '2026-05-23 23:52:56', 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0),
(61, 10, 1, 6, 2, 'Raw Material Payment Request', 'active', '2024-06-26 05:00:00', '2026-05-23 21:12:28', NULL, 0, 0, '2026-05-24 00:12:28', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0),
(62, 12, 1, 7, 11, 'Window Frame Delivery Schedule', 'active', '2024-06-27 06:00:00', '2026-05-23 20:47:58', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(63, 12, 1, 7, 9, 'Custom Design Specifications', 'active', '2024-06-28 07:30:00', '2026-05-24 23:01:02', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0),
(64, 14, 1, 8, 9, 'Zoning Approval for Designs', 'active', '2024-06-29 05:30:00', '2026-05-23 21:15:16', NULL, 0, 0, '2026-05-24 00:15:16', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(65, 14, 1, 8, 10, 'Survey Data for Planning', 'active', '2024-06-30 06:45:00', '2026-05-24 23:53:18', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(66, 16, 1, 9, 10, 'Site Survey Requirements', 'active', '2024-06-25 08:00:00', '2026-05-23 21:14:51', NULL, 0, 0, '2026-05-24 00:14:51', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(67, 16, 1, 9, 7, 'Aluminium Integration in Design', 'active', '2024-06-26 11:30:00', '2026-05-23 22:19:25', NULL, 0, 0, '2026-05-24 00:14:53', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(68, 18, 1, 10, 8, 'Boundary Data for Planning', 'active', '2024-06-27 05:00:00', '2026-05-23 23:10:52', NULL, 0, 0, '2026-05-24 02:10:52', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(69, 18, 1, 10, 12, 'Survey Reports for Title Deeds', 'active', '2024-06-28 07:00:00', '2026-05-24 12:24:27', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(70, 20, 1, 11, 6, 'Emergency Brick Order', 'active', '2024-06-29 10:00:00', '2026-05-23 20:52:53', NULL, 0, 0, '2026-05-23 23:52:53', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(71, 20, 1, 11, 7, 'Aluminium Installation Schedule', 'active', '2024-06-30 07:15:00', '2026-05-23 20:47:58', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0),
(72, 21, 1, 12, 10, 'Title Deed Survey Request', 'active', '2024-06-28 11:00:00', '2026-05-24 23:53:32', NULL, 0, 0, '2026-05-24 01:23:25', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(73, 21, 1, 12, 2, 'Title Deed Fee Report', 'active', '2024-06-29 06:30:00', '2026-05-23 22:22:46', NULL, 0, 0, '2026-05-24 01:22:46', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(74, 1, 1, 1, 2, 'Budget Review Q2 2024', 'active', '2026-05-23 10:03:08', '2026-05-23 20:39:21', NULL, 0, 0, '2026-05-23 23:39:21', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(75, 1, 1, 1, 3, 'Sales Targets for July', 'active', '2026-05-23 10:03:08', '2026-05-24 10:48:34', NULL, 0, 0, '2026-05-24 13:48:34', 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(76, 1, 1, 11, 1, 'Construction Progress Report', 'active', '2026-05-23 10:03:08', '2026-05-23 20:47:58', NULL, 0, 0, NULL, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(77, 1, 1, 1, 7, 'New Order Status', 'active', '2026-05-23 10:03:08', '2026-05-23 22:19:31', NULL, 0, 0, '2026-05-24 01:19:31', 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0),
(78, 1, 1, 12, 1, 'Digital Title Deed System', 'active', '2026-05-23 10:03:08', '2026-05-23 22:23:03', NULL, 0, 0, '2026-05-24 01:23:03', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(79, 1, 1, 1, 4, 'Staff Performance Review', 'active', '2026-05-23 10:03:08', '2026-05-23 11:24:52', NULL, 0, 0, '2026-05-23 14:24:52', 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(80, 1, 1, 6, 1, 'Production Update', 'active', '2026-05-23 10:03:08', '2026-05-23 20:39:48', NULL, 0, 0, '2026-05-23 23:39:48', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0),
(81, 1, 1, 5, 1, 'Office Administration Update', 'active', '2026-05-23 10:03:08', '2026-05-23 20:40:10', NULL, 0, 0, '2026-05-23 23:40:10', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(82, 1, 1, 10, 1, 'Survey Equipment Status', 'active', '2026-05-23 10:03:08', '2026-05-23 20:40:12', NULL, 0, 0, '2026-05-23 23:40:12', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(83, 1, 1, 9, 1, 'Design Approval Request', 'active', '2026-05-23 10:03:08', '2026-05-24 22:30:10', NULL, 0, 0, '2026-05-24 00:14:42', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(84, 1, 1, 8, 1, 'Master Plan Progress', 'active', '2026-05-23 10:03:08', '2026-05-24 12:36:13', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(85, 1, 1, 2, 1, 'Q2 Financial Report Ready', 'active', '2026-05-23 10:03:08', '2026-05-23 19:32:44', NULL, 0, 0, '2026-05-23 22:32:44', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(91, 0, NULL, 9, 2, 'Message from Architectural', 'active', '2026-05-23 14:13:30', '2026-05-23 21:15:18', NULL, 0, 0, '2026-05-24 00:15:18', 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(92, 0, NULL, 6, 4, 'New Message', 'active', '2026-05-23 14:23:48', '2026-05-23 15:48:02', NULL, 0, 0, '2026-05-23 17:55:53', 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0),
(94, 0, NULL, 2, 1, 'HII', 'active', '2026-05-23 20:57:08', '2026-05-25 09:06:31', NULL, 0, 0, NULL, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(95, 0, NULL, 2, 9, 'ARTCH', 'active', '2026-05-23 20:58:06', '2026-05-23 20:58:06', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(96, 0, NULL, 11, 9, 'ARCHT', 'active', '2026-05-23 20:58:24', '2026-05-24 23:00:11', NULL, 0, 0, '2026-05-24 00:14:26', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(97, 0, NULL, 1, 4, 'MNG', 'active', '2026-05-23 21:02:09', '2026-05-24 08:37:27', NULL, 0, 0, '2026-05-24 11:37:27', 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(98, 0, NULL, 6, 4, 'SAWA', 'active', '2026-05-23 21:08:23', '2026-05-23 21:49:10', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(99, 0, NULL, 6, 9, 'SAWA', 'active', '2026-05-23 21:08:45', '2026-05-24 22:49:38', NULL, 0, 0, '2026-05-24 00:15:20', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(100, 0, NULL, 6, 11, 'YES', 'active', '2026-05-23 21:12:18', '2026-05-23 21:12:18', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(101, 0, NULL, 6, 2, 'BRK', 'active', '2026-05-23 21:12:43', '2026-05-23 21:12:43', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(102, 0, NULL, 9, 6, 'BORA AJE MWENYEW', 'active', '2026-05-23 21:14:16', '2026-05-23 21:15:23', NULL, 0, 0, '2026-05-24 00:15:23', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(103, 0, NULL, 9, 2, 'OKAY', 'active', '2026-05-23 21:15:06', '2026-05-23 21:15:06', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(104, 0, NULL, 9, 1, 'hii', 'active', '2026-05-23 21:20:33', '2026-05-23 21:35:53', NULL, 0, 0, '2026-05-24 00:35:53', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(105, 0, NULL, 9, 3, 'mm', 'active', '2026-05-23 21:20:47', '2026-05-24 09:38:33', NULL, 0, 0, '2026-05-24 12:38:33', 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(106, 0, NULL, 1, 9, 'yes', 'active', '2026-05-23 21:21:05', '2026-05-23 21:35:01', NULL, 0, 0, '2026-05-24 00:35:01', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(107, 0, NULL, 9, 1, 'sawa', 'active', '2026-05-23 21:21:28', '2026-05-23 21:34:59', NULL, 0, 0, '2026-05-24 00:34:59', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(108, 0, NULL, 1, 9, 'okay', 'active', '2026-05-23 21:26:01', '2026-05-23 21:34:56', NULL, 0, 0, '2026-05-24 00:34:56', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(109, 0, NULL, 1, 9, 'sawa', 'active', '2026-05-23 21:26:11', '2026-05-23 21:34:53', NULL, 0, 0, '2026-05-24 00:34:53', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(110, 0, NULL, 9, 1, 'okay', 'active', '2026-05-23 21:26:48', '2026-05-23 21:34:51', NULL, 0, 0, '2026-05-24 00:34:51', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(111, 0, NULL, 1, 9, 'mm', 'active', '2026-05-23 21:27:31', '2026-05-23 21:34:49', NULL, 0, 0, '2026-05-24 00:34:49', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(112, 0, NULL, 1, 9, 'v', 'active', '2026-05-23 21:27:43', '2026-05-23 21:34:46', NULL, 0, 0, '2026-05-24 00:34:46', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(113, 0, NULL, 1, 9, 'c', 'active', '2026-05-23 21:27:50', '2026-05-23 21:34:44', NULL, 0, 0, '2026-05-24 00:34:44', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(114, 0, NULL, 1, 9, 'c', 'active', '2026-05-23 21:28:13', '2026-05-23 21:34:42', NULL, 0, 0, '2026-05-24 00:34:42', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(115, 0, NULL, 1, 9, 'c', 'active', '2026-05-23 21:28:21', '2026-05-23 21:34:38', NULL, 0, 0, '2026-05-24 00:34:38', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(116, 0, NULL, 1, 9, 'c', 'active', '2026-05-23 21:28:29', '2026-05-23 21:34:36', NULL, 0, 0, '2026-05-24 00:34:36', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(117, 0, NULL, 1, 9, 'c', 'active', '2026-05-23 21:28:37', '2026-05-23 21:34:34', NULL, 0, 0, '2026-05-24 00:34:34', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(118, 0, NULL, 1, 9, 'c', 'active', '2026-05-23 21:28:43', '2026-05-23 21:34:32', NULL, 0, 0, '2026-05-24 00:34:32', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(119, 0, NULL, 1, 9, 'dd', 'active', '2026-05-23 21:30:26', '2026-05-23 21:35:40', NULL, 0, 0, '2026-05-24 00:35:40', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(120, 0, NULL, 1, 9, 'yes', 'active', '2026-05-23 21:35:08', '2026-05-23 21:40:02', NULL, 0, 0, '2026-05-24 00:40:02', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(121, 0, NULL, 9, 1, 'admni', 'active', '2026-05-23 21:35:33', '2026-05-23 21:39:59', NULL, 0, 0, '2026-05-24 00:39:59', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(122, 0, NULL, 1, 9, 'yes', 'active', '2026-05-23 21:36:05', '2026-05-23 21:41:47', NULL, 0, 0, '2026-05-24 00:41:47', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(123, 0, NULL, 9, 1, 'hellow', 'active', '2026-05-23 21:39:41', '2026-05-23 21:41:45', NULL, 0, 0, '2026-05-24 00:41:45', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(124, 0, NULL, 1, 9, 'yes', 'active', '2026-05-23 21:39:50', '2026-05-23 21:41:39', NULL, 0, 0, '2026-05-24 00:41:39', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(125, 0, NULL, 1, 9, 'hii', 'active', '2026-05-23 21:40:12', '2026-05-23 21:41:33', NULL, 0, 0, '2026-05-24 00:41:33', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(126, 0, NULL, 12, 11, 'duh', 'active', '2026-05-23 22:23:58', '2026-05-23 22:25:28', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(127, 0, NULL, 5, 97, 'sawa', 'active', '2026-05-23 23:34:56', '2026-05-24 12:01:03', NULL, 0, 0, '2026-05-24 15:01:03', 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0),
(128, 0, NULL, 5, 37, 'POA', 'active', '2026-05-24 11:17:37', '2026-05-24 11:31:52', NULL, 0, 0, '2026-05-24 14:31:52', 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0),
(129, 0, NULL, 5, 3, 'hi', 'active', '2026-05-24 12:01:11', '2026-05-25 10:05:38', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(130, 0, NULL, 8, 4, 'Message', 'active', '2026-05-24 12:47:22', '2026-05-25 09:49:45', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(131, 0, NULL, 10, 4, 'Project from Survey', 'active', '2026-05-24 17:54:12', '2026-05-24 17:54:12', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(132, 0, NULL, 11, 4, 'Message', 'active', '2026-05-24 18:19:48', '2026-05-24 18:20:33', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(133, 0, NULL, 7, 6, 'New Report: Aluminium Production Report - June', 'active', '2026-05-24 22:35:54', '2026-05-25 00:03:55', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(134, 0, NULL, 7, 12, 'Project: Shopping Mall Construction', 'active', '2026-05-24 23:18:52', '2026-05-24 23:26:38', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(135, 0, NULL, 7, 4, 'Project: Luxury Apartment Complex', 'active', '2026-05-24 23:27:34', '2026-05-24 23:37:22', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(136, 0, NULL, 5, 7, 'Message', 'active', '2026-05-24 23:45:39', '2026-05-24 23:46:27', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(137, 0, NULL, 8, 2, 'Message', 'active', '2026-05-25 09:48:38', '2026-05-25 09:48:38', NULL, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `daily_work`
--

CREATE TABLE `daily_work` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `project_name` varchar(200) DEFAULT NULL,
  `work_description` text DEFAULT NULL,
  `income` decimal(15,2) DEFAULT 0.00,
  `expenses` decimal(15,2) DEFAULT 0.00,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('paid','partial','pending') DEFAULT 'pending',
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_user_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_work`
--

INSERT INTO `daily_work` (`id`, `date`, `project_name`, `work_description`, `income`, `expenses`, `paid_amount`, `status`, `department_id`, `created_at`, `is_deleted`, `deleted_by_admin`, `deleted_by_user_id`, `deleted_at`) VALUES
(1, '2024-05-01', 'Modern Villa', 'Foundation excavation and site preparation', 5000000.00, 2000000.00, 5000000.00, 'paid', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(2, '2024-05-01', 'Commercial Complex', 'Concrete pouring for ground floor', 8000000.00, 3500000.00, 4000000.00, 'partial', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(3, '2024-05-02', 'Modern Villa', 'Steel reinforcement installation', 6000000.00, 2500000.00, 6000000.00, 'paid', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(4, '2024-05-02', 'Bridge Project', 'Bridge pillar construction', 7500000.00, 3000000.00, 3000000.00, 'partial', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(5, '2024-05-03', 'Aluminium Window Fabrication', 'Window frame cutting and assembly', 3500000.00, 1200000.00, 3500000.00, 'paid', 7, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(6, '2024-05-03', 'Brick Supply - Residential Estate', 'Brick production - 25,000 units', 2250000.00, 1800000.00, 2250000.00, 'paid', 6, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(7, '2024-05-04', 'Title Deed Processing System', 'System development and testing', 5000000.00, 1500000.00, 5000000.00, 'paid', 12, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(8, '2024-05-04', 'Land Registration - Kinondoni', 'Field survey and data collection', 3000000.00, 1000000.00, 1500000.00, 'partial', 12, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(9, '2024-05-05', 'Town Planning Master Plan', 'Stakeholder consultation meeting', 4000000.00, 800000.00, 4000000.00, 'paid', 8, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(10, '2024-05-05', 'Architectural Design - City Center', '3D rendering and presentations', 6000000.00, 2000000.00, 6000000.00, 'paid', 9, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(11, '2024-05-06', 'Boundary Demarcation', 'GPS surveying and mapping', 3500000.00, 1200000.00, 3500000.00, 'paid', 10, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(12, '2024-05-06', 'Timber Processing - School', 'Timber cutting and treatment', 1800000.00, 800000.00, 1800000.00, 'paid', 6, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(13, '2024-05-07', 'Modern Villa', 'Wall construction - ground floor', 4500000.00, 1800000.00, 4500000.00, 'paid', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(14, '2024-05-07', 'Commercial Complex', 'Steel structure installation - floor 2', 7000000.00, 2800000.00, 3000000.00, 'paid', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(15, '2024-05-08', 'Aluminium Doors - Office Complex', 'Door frame fabrication', 2800000.00, 1000000.00, 2800000.00, 'paid', 7, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(16, '2024-05-08', 'Hatimiliki Digital Platform', 'Requirements gathering', 2000000.00, 500000.00, 1000000.00, 'partial', 12, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(17, '2024-05-09', 'School Construction', 'Final inspection and handover', 3000000.00, 500000.00, 3000000.00, 'paid', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(18, '2024-05-09', 'Survey - Kinondoni Plots', 'Final report preparation', 2500000.00, 600000.00, 2500000.00, 'paid', 10, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(19, '2024-05-10', 'Residential Estate - Mbezi', 'Site clearing and preparation', 4000000.00, 1500000.00, 2000000.00, 'paid', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(20, '2024-05-10', 'Brick Supply', 'Brick production - 30,000 units', 2700000.00, 2000000.00, 2700000.00, 'paid', 6, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(21, '2024-05-11', 'Modern Villa', 'Roofing installation', 5500000.00, 2200000.00, 5500000.00, 'paid', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(22, '2024-05-12', 'Town Planning Master Plan', 'GIS mapping and analysis', 3500000.00, 1000000.00, 3500000.00, 'paid', 8, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(23, '2024-05-12', 'Architectural Design', 'Client presentation and feedback', 4500000.00, 800000.00, 4500000.00, 'paid', 9, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(24, '2024-05-13', 'Aluminium Window Fabrication', 'Window installation - Modern Villa', 4000000.00, 1500000.00, 4000000.00, 'paid', 7, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(25, '2024-05-13', 'Bridge Project', 'Bridge deck reinforcement', 6000000.00, 2500000.00, 2000000.00, 'partial', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(26, '2024-05-14', 'Title Deed Processing', 'Document verification and approval', 3500000.00, 800000.00, 3500000.00, 'paid', 12, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(27, '2024-05-14', 'Land Registration', 'Data entry and processing', 2500000.00, 600000.00, 1200000.00, 'partial', 12, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(28, '2024-05-15', 'Boundary Demarcation', 'Final boundary marking', 3000000.00, 900000.00, 3000000.00, 'paid', 10, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(29, '2024-05-15', 'Residential Estate', 'Foundation excavation', 5000000.00, 2000000.00, 2000000.00, 'partial', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(31, '2026-05-10', 'Architectural Design', '', 5000000.00, 600000.00, 1500000.00, 'paid', 11, '2026-05-10 16:49:51', 0, 0, NULL, NULL),
(32, '2026-05-10', 'new', '', 60000000.00, 600000.00, 1500000.00, 'paid', 11, '2026-05-10 16:50:19', 0, 0, NULL, NULL),
(33, '2026-05-10', 'Architectural Design', '', 5000000.00, 1200000.00, 8000000.00, 'partial', 11, '2026-05-10 17:21:03', 0, 0, NULL, NULL),
(35, '2026-05-12', 'Architectural Design', '', 20000000.00, 15000000.00, 10000000.00, 'paid', 11, '2026-05-10 22:01:21', 0, 0, NULL, NULL),
(36, '2026-05-11', 'Architectural Design', 'imeanza', 5000000.00, 600000.00, 3000000.00, 'paid', 11, '2026-05-11 08:48:46', 0, 0, NULL, NULL),
(37, '2026-05-11', 'new', 'KALIPIA 3,000,000 BADO 2,000,000/=', 5000000.00, 1000000.00, 0.00, 'paid', 8, '2026-05-11 09:21:47', 0, 0, NULL, NULL),
(39, '2026-05-11', 'MYULA HOUSE', 'imeanza kujengwa', 180000000.00, 12000000.00, 55000000.00, 'paid', 10, '2026-05-11 10:46:32', 0, 0, NULL, NULL),
(42, '2026-05-19', 'Architectural Design', 'mm', 5000000.00, 1200000.00, 3000000.00, 'partial', 1, '2026-05-19 13:42:13', 0, 0, NULL, NULL),
(43, '2026-05-19', 'MYULA HOUSE 2', 'imefika renta', 0.00, 0.00, 0.00, 'pending', 1, '2026-05-19 19:01:41', 0, 0, NULL, NULL),
(51, '2026-06-03', 'Window Frame Supply - Housing Estate', '', 0.00, 0.00, 0.00, 'pending', 1, '2026-06-03 11:57:45', 0, 0, NULL, NULL),
(52, '2026-06-08', 'Bricks Production', 'M', 5000000.00, 0.00, 0.00, 'pending', 6, '2026-06-08 14:24:04', 0, 0, NULL, NULL),
(53, '2026-06-08', 'Bricks Production', '', 900000.00, 90000.00, 0.00, 'pending', 6, '2026-06-08 14:33:54', 0, 0, NULL, NULL),
(54, '2026-06-08', 'Bricks Production', '', 805000.00, 0.00, 0.00, 'pending', 6, '2026-06-08 15:02:24', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `dashboard_summary`
-- (See below for the actual view)
--
CREATE TABLE `dashboard_summary` (
`total_employees` bigint(21)
,`total_projects` bigint(21)
,`pending_projects` bigint(21)
,`in_progress_projects` bigint(21)
,`completed_projects` bigint(21)
,`total_income` decimal(37,2)
,`total_expenses` decimal(37,2)
,`daily_income` decimal(37,2)
,`daily_expenses` decimal(37,2)
,`draft_reports` bigint(21)
,`sent_reports` bigint(21)
,`unread_messages` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `email`, `phone`, `description`, `created_at`) VALUES
(1, 'Super Admin', 'admin@geotraverse.com', '+255 719 336 667', 'System Administrator - Full Access', '2026-05-10 16:46:59'),
(2, 'Finance Department', 'finance@geotraverse.com', '+255 712 345 678', 'Manages all financial transactions, budgets, and accounting', '2026-05-10 16:46:59'),
(3, 'Sales & Marketing', 'sales@geotraverse.com', '+255 713 456 789', 'Handles leads, customer acquisition, and marketing campaigns', '2026-05-10 16:46:59'),
(4, 'Manager Department', 'manager@geotraverse.com', '+255 714 567 890', 'Oversees all operations, projects, and client relationships', '2026-05-10 16:46:59'),
(5, 'Secretary Department', 'secretary@geotraverse.com', '+255 715 678 901', 'Manages visitors, appointments, and office administration', '2026-05-10 16:46:59'),
(6, 'Bricks & Timber', 'bricks@geotraverse.com', '+255 716 789 012', 'Bricks production and timber processing', '2026-05-10 16:46:59'),
(7, 'Aluminium Department', 'aluminium@geotraverse.com', '+255 717 890 123', 'Aluminium window and door fabrication', '2026-05-10 16:46:59'),
(8, 'Town Planning', 'townplanning@geotraverse.com', '+255 718 901 234', 'Urban planning, zoning, and land use management', '2026-05-10 16:46:59'),
(9, 'Architectural Department', 'architectural@geotraverse.com', '+255 719 012 345', 'Architectural design and building plans', '2026-05-10 16:46:59'),
(10, 'Survey Department', 'survey@geotraverse.com', '+255 720 123 456', 'Land surveying, boundary demarcation, and mapping', '2026-05-10 16:46:59'),
(11, 'Construction Department', 'construction@geotraverse.com', '+255 721 234 567', 'Construction project management and execution', '2026-05-10 16:46:59'),
(12, 'Hatimiliki Department', 'hatimiliki@geotraverse.com', '+255 722 345 678', 'Title deeds and land registration services', '2026-05-10 16:46:59');

-- --------------------------------------------------------

--
-- Table structure for table `department_permissions`
--

CREATE TABLE `department_permissions` (
  `id` int(11) NOT NULL,
  `viewer_department_id` int(11) NOT NULL,
  `target_department_id` int(11) NOT NULL,
  `can_view` tinyint(1) DEFAULT 0,
  `can_edit` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `department_summary_view`
-- (See below for the actual view)
--
CREATE TABLE `department_summary_view` (
`department_id` int(11)
,`department_name` varchar(100)
,`total_projects` bigint(21)
,`active_projects` bigint(21)
,`completed_projects` bigint(21)
,`total_income` decimal(37,2)
,`total_expense` decimal(37,2)
,`total_messages` bigint(21)
,`unread_messages` bigint(21)
,`total_reports` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `design_projects`
--

CREATE TABLE `design_projects` (
  `id` int(11) NOT NULL,
  `project_name` varchar(200) NOT NULL,
  `client_name` varchar(200) DEFAULT NULL,
  `client_phone` varchar(50) DEFAULT NULL,
  `project_type` enum('residential','commercial','industrial','institutional','mixed_use') DEFAULT 'residential',
  `design_stage` enum('concept','schematic','design_development','construction_docs','tender','completed') DEFAULT 'concept',
  `area_sq_meters` decimal(10,2) DEFAULT 0.00,
  `estimated_cost` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `design_files` text DEFAULT NULL,
  `status` enum('draft','in_progress','review','approved','completed','on_hold') DEFAULT 'draft',
  `department_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_by_department` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_user_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `design_projects`
--

INSERT INTO `design_projects` (`id`, `project_name`, `client_name`, `client_phone`, `project_type`, `design_stage`, `area_sq_meters`, `estimated_cost`, `description`, `design_files`, `status`, `department_id`, `created_by`, `created_at`, `updated_at`, `deleted_by_department`, `deleted_by_admin`, `deleted_by_user_id`, `deleted_at`) VALUES
(1, 'Modern Villa Kigamboni', 'John Mwita', NULL, 'residential', 'design_development', 450.00, 250000000.00, NULL, NULL, 'in_progress', 9, 16, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(2, 'Commercial Complex Pwani', 'Pwani Region', NULL, 'commercial', 'schematic', 2500.00, 450000000.00, NULL, NULL, 'review', 9, 16, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(3, 'School Design Kinondoni', 'Ministry of Education', NULL, 'institutional', 'construction_docs', 1200.00, 180000000.00, NULL, NULL, 'approved', 9, 16, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `marketing_campaigns`
--

CREATE TABLE `marketing_campaigns` (
  `id` int(11) NOT NULL,
  `campaign_name` varchar(200) NOT NULL,
  `campaign_type` enum('digital','print','tv_radio','event','social_media') DEFAULT 'digital',
  `budget` decimal(15,2) DEFAULT 0.00,
  `spent` decimal(15,2) DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `target_audience` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('planned','active','completed','cancelled') DEFAULT 'planned',
  `department_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_by_department` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_user_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `marketing_campaigns`
--

INSERT INTO `marketing_campaigns` (`id`, `campaign_name`, `campaign_type`, `budget`, `spent`, `start_date`, `end_date`, `target_audience`, `description`, `status`, `department_id`, `created_by`, `created_at`, `updated_at`, `deleted_by_department`, `deleted_by_admin`, `deleted_by_user_id`, `deleted_at`) VALUES
(1, 'Digital Ads Q2 2024', 'digital', 15000000.00, 8000000.00, '2024-04-01', '2024-06-30', NULL, NULL, 'active', 3, 4, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(2, 'Property Expo Dar es Salaam', 'event', 5000000.00, 3500000.00, '2024-05-15', '2024-05-17', NULL, NULL, 'completed', 3, 4, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(3, 'Social Media Campaign', 'social_media', 8000000.00, 2000000.00, '2024-05-01', '2024-07-31', NULL, NULL, 'active', 3, 4, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(4, 'Ramadan Special Promotion', 'print', 5000000.00, 2500000.00, '2024-03-01', '2024-04-30', 'Muslim community, families', 'Special discounts during Ramadan month', 'completed', 3, 4, '2026-05-19 07:00:00', '2026-05-19 20:14:33', 0, 0, NULL, NULL),
(5, 'TV Advertisement - Cloud FM', 'tv_radio', 12000000.00, 8000000.00, '2024-05-01', '2024-07-31', 'General public, property buyers', 'Prime time radio ads on Cloud FM', 'active', 3, 4, '2026-05-19 07:00:00', '2026-05-19 07:00:00', 0, 0, NULL, NULL),
(6, 'Billboard Installation - Mwenge', 'print', 3500000.00, 1500000.00, '2024-05-15', '2024-08-15', 'Commuters, businesses', 'Large billboard at Mwenge roundabout', 'active', 3, 4, '2026-05-19 07:00:00', '2026-05-19 07:00:00', 0, 0, NULL, NULL),
(7, 'Email Marketing Campaign', 'digital', 2000000.00, 500000.00, '2024-06-01', '2024-09-30', 'Existing customers, leads', 'Monthly newsletter and promotional emails', 'planned', 3, 4, '2026-05-19 07:00:00', '2026-05-19 07:00:00', 0, 0, NULL, NULL),
(8, 'Property Exhibition - Diamond Jubilee', 'event', 8000000.00, 6000000.00, '2024-07-10', '2024-07-12', 'Property investors, real estate agents', 'Annual property exhibition at Diamond Jubilee Hall', 'planned', 3, 4, '2026-05-19 07:00:00', '2026-05-19 07:00:00', 0, 0, NULL, NULL),
(9, 'ADD', 'event', 9000000.00, 0.00, '2026-05-19', '2026-05-30', '', '', 'active', 3, 4, '2026-05-19 20:15:06', '2026-05-19 20:15:06', 0, 0, NULL, NULL),
(10, 'Ramadan Special Promotion', 'tv_radio', 0.00, 0.00, '2026-05-19', NULL, '', '', 'planned', 3, 4, '2026-05-19 20:15:30', '2026-05-19 20:19:28', 1, 0, NULL, '2026-05-19 23:19:28'),
(11, 'rollup banner', 'event', 300000.00, 270000.00, '2026-05-25', '2026-05-30', '', '', 'active', 3, 4, '2026-05-25 10:04:57', '2026-05-25 10:04:57', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_dept` int(11) DEFAULT NULL,
  `receiver_dept` int(11) DEFAULT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `status` enum('sent','delivered','read') DEFAULT 'sent',
  `sender_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `receiver_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_by_sender` tinyint(1) DEFAULT 0,
  `deleted_by_receiver` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_dept`, `receiver_dept`, `conversation_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `read_at`, `status`, `sender_deleted`, `receiver_deleted`, `deleted_at`, `created_at`, `deleted_by_sender`, `deleted_by_receiver`) VALUES
(1, 3, 1, 1, 4, 1, 'Admin, we have a major lead from a construction company worth TZS 500M. They want to discuss potential partnership.', 1, '2026-05-23 13:14:23', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(2, 1, 3, 1, 1, 4, 'That is excellent news! Please schedule a meeting with them next week. I would like to attend.', 1, '2026-05-23 01:20:23', 'read', 0, 0, NULL, '2026-05-22 21:17:05', 1, 0),
(3, 3, 1, 1, 4, 1, 'Meeting scheduled for Tuesday at 10 AM. They confirmed attendance. I will send you the agenda.', 1, '2026-05-23 13:14:23', 'sent', 0, 0, NULL, '2026-05-22 21:27:05', 0, 0),
(4, 1, 3, 1, 1, 4, 'Perfect. Please prepare a presentation about our capabilities and recent projects.', 1, '2026-05-23 01:20:23', 'read', 0, 0, NULL, '2026-05-22 21:42:05', 1, 0),
(5, 11, 2, 2, 20, 2, 'Finance team, we need urgent payment of TZS 50M for construction materials. Suppliers are waiting.', 1, '2026-05-23 16:41:35', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(6, 2, 11, 2, 2, 20, 'Please submit the invoice and delivery notes for approval. We process payments on Tuesdays and Thursdays.', 1, '2026-05-23 14:59:32', 'sent', 0, 0, NULL, '2026-05-22 21:22:05', 0, 0),
(7, 11, 2, 2, 20, 2, 'Documents submitted via email. Can we expedite? Project deadline is approaching.', 1, '2026-05-23 16:41:35', 'sent', 0, 0, NULL, '2026-05-22 21:32:05', 0, 0),
(8, 2, 11, 2, 2, 20, 'Payment approved! Funds will be transferred tomorrow morning. Thank you for your patience.', 1, '2026-05-23 14:59:32', 'sent', 0, 0, NULL, '2026-05-22 21:57:05', 0, 0),
(9, 7, 3, 3, 12, 4, 'Sales team, we have developed a new premium aluminium product line. Need marketing support for launch.', 1, '2026-05-23 01:20:43', 'read', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(10, 3, 7, 3, 4, 12, 'Great news! Please share product specifications and pricing. We will prepare marketing materials.', 1, '2026-05-23 14:46:20', 'sent', 0, 0, NULL, '2026-05-22 21:20:05', 0, 0),
(11, 7, 3, 3, 12, 4, 'Product catalog attached. Pricing is competitive - 15% lower than market average. Launch planned for August.', 1, '2026-05-23 01:20:43', 'read', 0, 0, NULL, '2026-05-22 21:27:05', 0, 0),
(12, 3, 7, 3, 4, 12, 'Catalog received. We will create a launch campaign. Let\'s meet on Friday to finalize strategy.', 1, '2026-05-23 14:46:20', 'sent', 0, 0, NULL, '2026-05-22 21:37:05', 0, 0),
(13, 5, 4, 4, 8, 6, 'Manager, office will be closed on Friday due to public holiday. Please inform your team.', 1, '2026-05-23 01:15:13', 'read', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(14, 4, 5, 4, 6, 8, 'Noted. Please send a reminder to all departments. Also, ensure security arrangements are in place.', 1, '2026-05-23 01:16:41', 'read', 0, 0, NULL, '2026-05-22 21:17:05', 0, 0),
(15, 5, 4, 4, 8, 6, 'Reminder sent to all staff. Security confirmed. Have a good holiday!', 1, '2026-05-23 01:15:13', 'read', 0, 0, NULL, '2026-05-22 21:22:05', 0, 0),
(16, 12, 1, 5, 21, 1, 'Admin, digital title deed system is 75% complete. Launch scheduled for September 2024.', 1, '2026-05-23 13:52:52', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(17, 1, 12, 5, 1, 21, 'Excellent progress! Please arrange a demo for the management team next week.', 1, '2026-05-24 01:24:12', 'read', 0, 0, NULL, '2026-05-22 21:19:05', 1, 0),
(18, 12, 1, 5, 21, 1, 'Demo scheduled for Tuesday at 2 PM. Boardroom 2. Will send invitation.', 1, '2026-05-23 13:52:52', 'sent', 0, 0, NULL, '2026-05-22 21:24:05', 0, 0),
(19, 6, 11, 6, 10, 20, 'Construction team, your order of 100,000 bricks is ready for delivery. When should we send?', 1, '2026-05-23 14:59:29', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(20, 11, 6, 6, 20, 10, 'Please deliver on Monday morning. We need them urgently for the residential estate project.', 1, '2026-05-23 17:55:26', 'read', 0, 0, NULL, '2026-05-22 21:18:05', 0, 0),
(21, 6, 11, 6, 10, 20, 'Delivery scheduled for Monday at 8 AM. 5 trucks will be dispatched. Total cost TZS 25M.', 1, '2026-05-23 14:59:29', 'sent', 0, 0, NULL, '2026-05-22 21:22:05', 0, 0),
(22, 11, 6, 6, 20, 10, 'Confirmed. Payment will be processed upon delivery. Thank you for the update.', 1, '2026-05-23 17:55:26', 'read', 0, 0, NULL, '2026-05-22 21:27:05', 0, 0),
(23, 9, 8, 7, 16, 14, 'Town Planning team, please review the architectural designs for the new city center project. Attached.', 1, '2026-05-24 15:36:16', 'read', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(24, 8, 9, 7, 14, 16, 'Designs received. We will review and provide feedback by end of week. Looks promising!', 1, '2026-05-23 14:50:21', 'sent', 0, 0, NULL, '2026-05-22 21:32:05', 0, 0),
(25, 9, 8, 7, 16, 14, 'Thank you. Please note the project timeline is tight. We appreciate your prompt review.', 1, '2026-05-24 15:36:16', 'read', 0, 0, NULL, '2026-05-22 21:37:05', 0, 0),
(26, 8, 9, 7, 14, 16, 'Feedback sent via email. Minor changes needed. Overall design approved!', 1, '2026-05-23 14:50:21', 'sent', 0, 0, NULL, '2026-05-22 23:12:05', 0, 0),
(27, 10, 12, 8, 18, 21, 'Hatimiliki team, survey reports for 25 plots are ready for title deed processing.', 1, '2026-05-24 01:23:20', 'read', 0, 0, NULL, '2026-05-22 21:12:05', 1, 0),
(28, 12, 10, 8, 21, 18, 'Received. We will start processing immediately. Can you send the digital files?', 1, '2026-05-24 02:10:45', 'read', 0, 0, NULL, '2026-05-22 21:20:05', 0, 0),
(29, 10, 12, 8, 18, 21, 'Digital files uploaded to the shared drive. Access granted. Let us know if you need anything else.', 1, '2026-05-24 01:23:20', 'read', 0, 0, NULL, '2026-05-22 21:24:05', 1, 0),
(30, 12, 10, 8, 21, 18, 'Files received. Processing will take 5 working days. We will notify you upon completion.', 1, '2026-05-24 02:10:45', 'read', 0, 0, NULL, '2026-05-22 21:42:05', 0, 0),
(31, 4, 2, 9, 6, 2, 'Finance, please prepare Q3 budget breakdown for all departments. Need by Friday.', 1, '2026-05-23 16:48:40', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(32, 2, 4, 9, 2, 6, 'Budget templates sent to all department heads. Please ensure submissions by Wednesday.', 1, '2026-05-23 14:21:01', 'sent', 0, 0, NULL, '2026-05-22 21:27:05', 0, 0),
(33, 4, 2, 9, 6, 2, 'Most departments have submitted. Construction department pending. Will follow up.', 1, '2026-05-23 16:48:40', 'sent', 0, 0, NULL, '2026-05-22 22:12:05', 0, 0),
(34, 2, 4, 9, 2, 6, 'Budget consolidation in progress. Preliminary numbers look good. Final by Friday as requested.', 1, '2026-05-23 14:21:01', 'sent', 0, 0, NULL, '2026-05-22 23:12:05', 0, 0),
(35, 5, 4, 10, 8, 6, 'Manager, 3 new staff will join next Monday. Please arrange orientation and workspace setup.', 1, '2026-05-23 14:21:16', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(36, 4, 5, 10, 6, 8, 'Noted. Please prepare welcome packages and coordinate with IT for computer setup.', 1, '2026-05-23 01:16:37', 'read', 0, 0, NULL, '2026-05-22 21:22:05', 0, 0),
(37, 5, 4, 10, 8, 6, 'Welcome packages ready. IT notified. Orientation schedule prepared for Monday at 9 AM.', 1, '2026-05-23 14:21:16', 'sent', 0, 0, NULL, '2026-05-22 21:32:05', 0, 0),
(38, 3, 7, 11, 4, 12, 'Aluminium team, we have a bulk order for 10,000 window frames from a government project.', 1, '2026-05-23 14:46:22', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(39, 7, 3, 11, 12, 4, 'That is great! What is the timeline? Our current capacity is 5,000 units per month.', 1, '2026-05-23 01:20:48', 'read', 0, 0, NULL, '2026-05-22 21:17:05', 0, 0),
(40, 3, 7, 11, 4, 12, 'They need delivery within 3 months. Can we increase capacity?', 1, '2026-05-23 14:46:22', 'sent', 0, 0, NULL, '2026-05-22 21:22:05', 0, 0),
(41, 7, 3, 11, 12, 4, 'We can add a night shift to increase production to 7,000 units per month. Should be feasible.', 1, '2026-05-23 01:20:48', 'read', 0, 0, NULL, '2026-05-22 21:32:05', 0, 0),
(42, 3, 7, 11, 4, 12, 'Perfect! Please prepare a quotation. We will present to the client tomorrow.', 1, '2026-05-23 14:46:22', 'sent', 0, 0, NULL, '2026-05-22 21:37:05', 0, 0),
(43, 11, 6, 12, 20, 10, 'Bricks team, we need an additional 50,000 bricks for the residential estate project. Urgent!', 0, NULL, 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(44, 6, 11, 12, 10, 20, 'We can supply by end of this week. Current stock: 80,000 units. Price: TZS 250 per brick.', 1, '2026-05-23 14:59:25', 'sent', 0, 0, NULL, '2026-05-22 21:17:05', 0, 0),
(45, 11, 6, 12, 20, 10, 'Confirmed. Please deliver as soon as possible. PO will be issued today.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-22 21:20:05', 0, 0),
(46, 6, 11, 12, 10, 20, 'Order confirmed. Delivery scheduled for Thursday. Thank you for your business!', 1, '2026-05-23 14:59:25', 'sent', 0, 0, NULL, '2026-05-22 21:24:05', 0, 0),
(47, 1, 4, 13, 1, 6, 'Manager, please communicate to all departments: Company strategy meeting on July 10, 2024. Attendance mandatory.', 1, '2026-05-23 14:12:00', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 1, 0),
(48, 4, 1, 13, 6, 1, 'Message relayed to all departments. Conference room reserved for 50 people.', 1, '2026-05-23 13:52:55', 'sent', 0, 0, NULL, '2026-05-22 21:27:05', 0, 0),
(49, 1, 4, 13, 1, 6, 'Excellent. Agenda will be shared by end of week. Please prepare department presentations.', 1, '2026-05-23 14:12:00', 'sent', 0, 0, NULL, '2026-05-22 21:32:05', 1, 0),
(50, 4, 1, 13, 6, 1, 'Noted. All departments are preparing. Looking forward to the strategy meeting.', 1, '2026-05-23 13:52:55', 'sent', 0, 0, NULL, '2026-05-22 21:42:05', 0, 0),
(51, 2, 4, 14, 2, 6, 'Manager, financial year-end closing will begin July 15. All departments must submit expense reports by July 10.', 1, '2026-05-23 14:21:14', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(52, 4, 2, 14, 6, 2, 'Message communicated to all departments. Will ensure timely submissions.', 1, '2026-05-23 17:00:50', 'sent', 0, 0, NULL, '2026-05-22 21:22:05', 0, 0),
(53, 2, 4, 14, 2, 6, 'Thank you. Late submissions will affect budget allocation for next year.', 1, '2026-05-23 14:21:14', 'sent', 0, 0, NULL, '2026-05-22 21:27:05', 0, 0),
(54, 8, 9, 15, 14, 16, 'Architectural team, we have a new urban development project. Need your design expertise.', 1, '2026-05-23 14:50:23', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(55, 9, 8, 15, 16, 14, 'We are interested. Please share project details and timeline. We have capacity starting August.', 1, '2026-05-24 15:36:18', 'read', 0, 0, NULL, '2026-05-22 21:22:05', 0, 0),
(56, 8, 9, 15, 14, 16, 'Project involves 500 housing units. Timeline: 6 months. Budget: TZS 2 Billion.', 1, '2026-05-23 14:50:23', 'sent', 0, 0, NULL, '2026-05-22 21:32:05', 0, 0),
(57, 9, 8, 15, 16, 14, 'We can allocate 5 architects to this project. Let\'s schedule a kickoff meeting.', 1, '2026-05-24 15:36:18', 'read', 0, 0, NULL, '2026-05-22 21:42:05', 0, 0),
(58, 10, 11, 16, 18, 20, 'Construction team, we need to conduct site survey for the new hospital project. When can we access the site?', 1, '2026-05-23 14:59:24', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(59, 11, 10, 16, 20, 18, 'Site is ready. You can start tomorrow at 8 AM. Gate pass will be arranged.', 1, '2026-05-24 02:10:43', 'read', 0, 0, NULL, '2026-05-22 21:20:05', 0, 0),
(60, 10, 11, 16, 18, 20, 'Perfect. Survey team of 3 people will arrive tomorrow. Expected duration: 2 days.', 1, '2026-05-23 14:59:24', 'sent', 0, 0, NULL, '2026-05-22 21:24:05', 0, 0),
(61, 12, 8, 17, 21, 14, 'Town Planning, please verify land use status for plot #1234 in Kigamboni. Urgent title deed application.', 1, '2026-05-24 15:36:24', 'read', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(62, 8, 12, 17, 14, 21, 'Checking records... Plot #1234 is designated for residential use. No restrictions.', 1, '2026-05-24 01:24:16', 'read', 0, 0, NULL, '2026-05-22 21:27:05', 0, 0),
(63, 12, 8, 17, 21, 14, 'Thank you for the quick response. Title deed processing will proceed.', 1, '2026-05-24 15:36:24', 'read', 0, 0, NULL, '2026-05-22 21:32:05', 0, 0),
(64, 5, 4, 18, 8, 6, 'Manager, Eid holiday schedule: July 16-17, 2024. Office closed both days.', 1, '2026-05-23 14:21:09', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(65, 4, 5, 18, 6, 8, 'Noted. Please ensure staff are informed. Arrange skeleton staff for urgent matters.', 1, '2026-05-23 01:16:50', 'read', 0, 0, NULL, '2026-05-22 21:22:05', 0, 0),
(66, 5, 4, 18, 8, 6, 'All staff notified. Emergency contacts shared. Security confirmed.', 1, '2026-05-23 14:21:09', 'sent', 0, 0, NULL, '2026-05-22 21:27:05', 0, 0),
(67, 7, 11, 19, 12, 20, 'Construction team, your order of aluminium windows is ready. Delivery tomorrow morning.', 1, '2026-05-23 14:59:16', 'sent', 0, 1, '2026-05-23 23:45:18', '2026-05-22 21:12:05', 1, 0),
(68, 11, 7, 19, 20, 12, 'Great! Please send 3 trucks. Site will be ready to receive at 9 AM.', 1, '2026-05-23 14:46:10', 'sent', 1, 0, '2026-05-23 23:45:18', '2026-05-22 21:17:05', 0, 0),
(69, 7, 11, 19, 12, 20, 'Confirmed. Delivery scheduled for 9 AM. Installation team will arrive on Thursday.', 1, '2026-05-23 14:59:16', 'sent', 0, 1, '2026-05-23 23:45:18', '2026-05-22 21:22:05', 1, 0),
(70, 4, 2, 20, 6, 2, 'Finance, please prepare staff performance review templates. Quarterly reviews due by July 20.', 1, '2026-05-23 22:28:14', 'sent', 0, 0, NULL, '2026-05-22 21:12:05', 0, 0),
(71, 2, 4, 20, 2, 6, 'Templates ready and shared with all department heads. Deadline: July 18 for submissions.', 1, '2026-05-23 14:21:18', 'sent', 0, 0, NULL, '2026-05-22 21:32:05', 0, 0),
(72, 4, 2, 20, 6, 2, 'Thank you. Please remind all departments to submit on time.', 1, '2026-05-23 22:28:14', 'sent', 0, 0, NULL, '2026-05-22 21:37:05', 0, 0),
(74, 1, 2, 1, 1, 2, 'Dear Finance Team, please submit Q2 financial report by end of this week.', 1, NULL, 'read', 0, 0, NULL, '2024-06-01 06:00:00', 1, 0),
(75, 2, 1, 1, 2, 1, 'Noted Admin. We will prepare and submit by Friday.', 1, NULL, 'read', 0, 0, NULL, '2024-06-01 07:30:00', 0, 0),
(76, 1, 2, 1, 1, 2, 'Thank you. Also please review the budget for Q3.', 1, NULL, 'read', 0, 0, NULL, '2024-06-02 08:00:00', 1, 0),
(77, 2, 1, 1, 2, 1, 'Q2 report is ready. We have sent it for your review.', 1, NULL, 'read', 0, 0, NULL, '2024-06-28 11:30:00', 0, 0),
(78, 1, 2, 1, 1, 2, 'Received. Excellent work Finance team!', 1, NULL, 'read', 0, 0, NULL, '2024-06-28 12:00:00', 1, 0),
(79, 2, 1, 1, 2, 1, 'Q3 budget draft is ready for review.', 1, '2026-05-23 13:14:23', 'sent', 0, 0, NULL, '2024-06-30 07:00:00', 0, 0),
(80, 1, 3, 2, 1, 4, 'Sales team, please provide monthly sales report.', 1, NULL, 'read', 0, 0, NULL, '2024-06-02 07:30:00', 0, 0),
(81, 3, 1, 2, 4, 1, 'Admin, sales have increased by 15% this month.', 1, NULL, 'read', 0, 0, NULL, '2024-06-03 06:00:00', 0, 0),
(82, 1, 3, 2, 1, 4, 'Great performance! Keep up the good work.', 1, NULL, 'read', 0, 0, NULL, '2024-06-03 11:00:00', 0, 0),
(83, 3, 1, 2, 4, 1, 'We have secured 3 new major clients this month.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 11:15:00', 0, 0),
(84, 1, 3, 2, 1, 4, 'Excellent! Schedule a meeting to discuss strategy.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 08:30:00', 0, 0),
(85, 1, 4, 3, 1, 6, 'Manager, please prepare department performance review.', 1, NULL, 'read', 0, 0, NULL, '2024-06-03 08:00:00', 0, 0),
(86, 4, 1, 3, 6, 1, 'Noted Admin. We are compiling all department reports.', 1, NULL, 'read', 0, 0, NULL, '2024-06-04 06:30:00', 0, 0),
(87, 1, 4, 3, 1, 6, 'Annual strategy meeting scheduled for July 15.', 1, NULL, 'read', 0, 0, NULL, '2024-06-05 07:00:00', 0, 0),
(88, 4, 1, 3, 6, 1, 'All departments are preparing their presentations.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 12:30:00', 0, 0),
(89, 1, 4, 3, 1, 6, 'Confirmed. Looking forward to the presentations.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 06:45:00', 0, 0),
(90, 1, 5, 4, 1, 8, 'Secretary, please arrange meeting for department heads.', 1, NULL, 'read', 0, 0, NULL, '2024-06-04 05:15:00', 0, 0),
(91, 5, 1, 4, 8, 1, 'Admin, meeting room booked for Friday at 10 AM.', 1, NULL, 'read', 0, 0, NULL, '2024-06-05 06:00:00', 0, 0),
(92, 1, 5, 4, 1, 8, 'Thank you. Please send invitations to all HODs.', 1, NULL, 'read', 0, 0, NULL, '2024-06-05 11:30:00', 0, 0),
(93, 5, 1, 4, 8, 1, 'Invitations sent. All HODs confirmed attendance.', 1, NULL, 'read', 0, 0, NULL, '2024-06-28 13:20:00', 0, 0),
(94, 1, 5, 4, 1, 8, 'Perfect. Please prepare minutes template.', 1, '2026-05-23 01:16:41', 'read', 0, 0, NULL, '2024-06-30 05:00:00', 0, 0),
(95, 1, 6, 5, 1, 10, 'Bricks team, what is current production capacity?', 1, NULL, 'read', 0, 0, NULL, '2024-06-05 10:00:00', 1, 0),
(96, 6, 1, 5, 10, 1, 'Admin, current capacity is 250,000 bricks per month.', 1, NULL, 'read', 0, 0, NULL, '2024-06-06 05:00:00', 0, 0),
(97, 1, 6, 5, 1, 10, 'Can you increase to 300,000 by Q3?', 1, NULL, 'read', 0, 0, NULL, '2024-06-06 07:30:00', 1, 0),
(98, 6, 1, 5, 10, 1, 'Yes, we can add night shift to increase capacity.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 08:30:00', 0, 0),
(99, 1, 6, 5, 1, 10, 'Approved. Submit the plan for night shift operation.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 11:00:00', 1, 0),
(100, 1, 7, 6, 1, 12, 'Aluminium team, update on new product line?', 1, NULL, 'read', 0, 0, NULL, '2024-06-06 11:30:00', 0, 0),
(101, 7, 1, 6, 12, 1, 'Admin, new premium product line launching in August.', 1, NULL, 'read', 0, 0, NULL, '2024-06-07 06:00:00', 0, 0),
(102, 1, 7, 6, 1, 12, 'Please prepare product catalog and pricing.', 1, NULL, 'read', 0, 0, NULL, '2024-06-08 07:00:00', 0, 0),
(103, 7, 1, 6, 12, 1, 'Catalog ready. 15% lower than market average.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 12:00:00', 0, 0),
(104, 1, 7, 6, 1, 12, 'Great! Marketing will start campaign in July.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 07:00:00', 0, 0),
(105, 1, 8, 7, 1, 14, 'Town Planning, master plan progress update?', 1, NULL, 'read', 0, 0, NULL, '2024-06-07 06:45:00', 0, 0),
(106, 8, 1, 7, 14, 1, 'Admin, master plan is 65% complete.', 1, NULL, 'read', 0, 0, NULL, '2024-06-08 08:00:00', 0, 0),
(107, 1, 8, 7, 1, 14, 'When will it be ready for council submission?', 1, NULL, 'read', 0, 0, NULL, '2024-06-09 11:30:00', 0, 0),
(108, 8, 1, 7, 14, 1, 'Expected by September 2024.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 12:45:00', 0, 0),
(109, 1, 8, 7, 1, 14, 'Keep up the good progress. Need any resources?', 1, '2026-05-24 15:36:16', 'read', 0, 0, NULL, '2024-06-30 06:30:00', 0, 0),
(110, 1, 9, 8, 1, 16, 'Architectural, design status for city center project?', 1, NULL, 'read', 0, 0, NULL, '2024-06-08 07:15:00', 0, 0),
(111, 9, 1, 8, 16, 1, 'Admin, schematic design is 85% complete.', 1, NULL, 'read', 0, 0, NULL, '2024-06-09 06:00:00', 0, 0),
(112, 1, 9, 8, 1, 16, 'Client is requesting presentation next week.', 1, NULL, 'read', 0, 0, NULL, '2024-06-10 08:00:00', 0, 0),
(113, 9, 1, 8, 16, 1, 'We will be ready for presentation on Wednesday.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 13:00:00', 0, 0),
(114, 1, 9, 8, 1, 16, 'Excellent. I will attend the presentation.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 05:30:00', 0, 0),
(115, 1, 10, 9, 1, 18, 'Survey team, equipment status report needed.', 1, NULL, 'read', 0, 0, NULL, '2024-06-09 08:30:00', 0, 0),
(116, 10, 1, 9, 18, 1, 'Admin, all equipment operational except one total station.', 1, NULL, 'read', 0, 0, NULL, '2024-06-10 05:00:00', 0, 0),
(117, 1, 10, 9, 1, 18, 'Request approved for new total station purchase.', 1, NULL, 'read', 0, 0, NULL, '2024-06-11 07:00:00', 0, 0),
(118, 10, 1, 9, 18, 1, 'Thank you Admin. We will order immediately.', 1, NULL, 'read', 0, 0, NULL, '2024-06-28 10:20:00', 0, 0),
(119, 1, 10, 9, 1, 18, 'Keep me updated on delivery timeline.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 08:00:00', 0, 0),
(120, 1, 11, 10, 1, 20, 'Construction, monthly progress report required.', 1, NULL, 'read', 0, 0, NULL, '2024-06-10 05:00:00', 0, 0),
(121, 11, 1, 10, 20, 1, 'Admin, Modern Villa at 85%, Commercial at 60%.', 1, NULL, 'read', 0, 0, NULL, '2024-06-11 11:00:00', 0, 0),
(122, 1, 11, 10, 1, 20, 'Any major issues or delays?', 1, NULL, 'read', 0, 0, NULL, '2024-06-12 06:30:00', 0, 0),
(123, 11, 1, 10, 20, 1, 'Minor material delays, but overall on track.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 13:00:00', 0, 0),
(124, 1, 11, 10, 1, 20, 'Good. Ensure safety protocols are followed.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 12:00:00', 0, 0),
(125, 1, 12, 11, 1, 21, 'Hatimiliki, digital title deed system progress?', 1, NULL, 'read', 0, 0, NULL, '2024-06-11 06:20:00', 0, 0),
(126, 12, 1, 11, 21, 1, 'Admin, system is 75% complete.', 1, NULL, 'read', 0, 0, NULL, '2024-06-12 07:00:00', 0, 0),
(127, 1, 12, 11, 1, 21, 'When is the expected launch date?', 1, NULL, 'read', 0, 0, NULL, '2024-06-13 11:30:00', 0, 0),
(128, 12, 1, 11, 21, 1, 'Launch scheduled for September 2024.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 12:30:00', 0, 0),
(129, 1, 12, 11, 1, 21, 'Please arrange demo for management next week.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 11:30:00', 0, 0),
(130, 2, 3, 12, 2, 4, 'Marketing team, please submit Q3 budget requirements.', 1, NULL, 'read', 0, 0, NULL, '2024-06-12 07:00:00', 0, 0),
(131, 3, 2, 12, 4, 2, 'Finance, our Q3 budget is TZS 45M for campaigns.', 1, NULL, 'read', 0, 0, NULL, '2024-06-13 06:00:00', 0, 0),
(132, 2, 3, 12, 2, 4, 'Budget approved. Will allocate by July 1.', 1, NULL, 'read', 0, 0, NULL, '2024-06-14 08:00:00', 0, 0),
(133, 3, 2, 12, 4, 2, 'Thank you. We will prepare campaign plan.', 1, NULL, 'read', 0, 0, NULL, '2024-06-28 08:15:00', 0, 0),
(134, 2, 3, 12, 2, 4, 'Please ensure expenses are properly documented.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 10:00:00', 0, 0),
(135, 2, 4, 13, 2, 6, 'Manager, Q2 financial report is ready for review.', 1, NULL, 'read', 0, 0, NULL, '2024-06-13 06:30:00', 0, 0),
(136, 4, 2, 13, 6, 2, 'Please send it for review. I will provide feedback.', 1, NULL, 'read', 0, 0, NULL, '2024-06-14 07:00:00', 0, 0),
(137, 2, 4, 13, 2, 6, 'Report sent. Please review by June 20.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 11:30:00', 0, 0),
(138, 4, 2, 13, 6, 2, 'Report looks good. Approved for submission.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 07:00:00', 0, 0),
(139, 2, 6, 14, 2, 10, 'Bricks team, payment for raw materials is pending.', 1, NULL, 'read', 0, 0, NULL, '2024-06-14 08:45:00', 0, 0),
(140, 6, 2, 14, 10, 2, 'Finance, when will the payment be processed?', 1, NULL, 'read', 0, 0, NULL, '2024-06-15 05:00:00', 0, 0),
(141, 2, 6, 14, 2, 10, 'Payment will be processed on Tuesday.', 1, NULL, 'read', 0, 0, NULL, '2024-06-30 06:00:00', 0, 0),
(142, 6, 2, 14, 10, 2, 'Thank you for the update.', 1, '2026-05-23 17:00:50', 'sent', 0, 0, NULL, '2024-06-30 11:30:00', 0, 0),
(143, 2, 7, 15, 2, 12, 'Aluminium team, invoice #AL-2024-045 has been paid.', 1, NULL, 'read', 0, 0, NULL, '2024-06-15 11:00:00', 0, 0),
(144, 7, 2, 15, 12, 2, 'Received. Thank you Finance.', 1, NULL, 'read', 0, 0, NULL, '2024-06-16 06:00:00', 0, 0),
(145, 2, 7, 15, 2, 12, 'Please ensure all future invoices have PO numbers.', 1, NULL, 'read', 0, 0, NULL, '2024-06-28 12:30:00', 0, 0),
(146, 7, 2, 15, 12, 2, 'Noted. Will include PO numbers going forward.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 08:00:00', 0, 0),
(147, 3, 7, 16, 4, 12, 'Aluminium team, we have a bulk order for 10,000 units.', 1, NULL, 'read', 0, 0, NULL, '2024-06-18 06:00:00', 0, 0),
(148, 7, 3, 16, 12, 4, 'Great! What is the timeline?', 1, NULL, 'read', 0, 0, NULL, '2024-06-19 07:00:00', 0, 0),
(149, 3, 7, 16, 4, 12, 'Delivery needed within 3 months.', 1, NULL, 'read', 0, 0, NULL, '2024-06-28 11:45:00', 0, 0),
(150, 7, 3, 16, 12, 4, 'We can meet the deadline with night shift.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 06:00:00', 0, 0),
(151, 4, 2, 17, 6, 2, 'Finance team, prepare staff salary review report.', 1, NULL, 'read', 0, 0, NULL, '2024-06-21 05:45:00', 0, 0),
(152, 2, 4, 17, 2, 6, 'Manager, report is being compiled.', 1, NULL, 'read', 0, 0, NULL, '2024-06-22 06:00:00', 0, 0),
(153, 4, 2, 17, 6, 2, 'Please include performance metrics.', 1, NULL, 'read', 0, 0, NULL, '2024-06-28 13:15:00', 0, 0),
(154, 2, 4, 17, 2, 6, 'Will do. Report ready by July 5.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 05:30:00', 0, 0),
(155, 6, 11, 18, 10, 20, 'Construction team, your order of 100,000 bricks is ready.', 1, NULL, 'read', 0, 0, NULL, '2024-06-25 04:30:00', 0, 0),
(156, 11, 6, 18, 20, 10, 'Please deliver on Monday morning.', 1, NULL, 'read', 0, 0, NULL, '2024-06-26 05:00:00', 0, 0),
(157, 6, 11, 18, 10, 20, 'Delivery scheduled for Monday at 8 AM.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 12:00:00', 0, 0),
(158, 6, 11, 18, 10, 20, 'Confirmed. 5 trucks will be dispatched.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 09:00:00', 0, 0),
(159, 7, 11, 19, 12, 20, 'Construction team, window frames for Modern Villa ready.', 1, NULL, 'read', 0, 1, '2026-05-23 23:45:18', '2024-06-27 06:00:00', 1, 0),
(160, 11, 7, 19, 20, 12, 'Great! When can you deliver?', 1, NULL, 'read', 1, 0, '2026-05-23 23:45:18', '2024-06-28 07:30:00', 0, 0),
(161, 7, 11, 19, 12, 20, 'Delivery scheduled for Thursday.', 1, NULL, 'read', 0, 1, '2026-05-23 23:45:18', '2024-06-28 10:45:00', 1, 0),
(162, 7, 11, 19, 12, 20, 'Installation team will arrive Friday.', 1, '2026-05-23 14:59:16', 'sent', 0, 1, '2026-05-23 23:45:18', '2024-06-30 07:30:00', 1, 0),
(163, 10, 12, 20, 18, 21, 'Hatimiliki team, survey reports for 25 plots are ready.', 1, NULL, 'read', 0, 0, NULL, '2024-06-28 07:00:00', 0, 0),
(164, 12, 10, 20, 21, 18, 'Received. We will process title deeds.', 1, NULL, 'read', 0, 0, NULL, '2024-06-29 06:45:00', 0, 0),
(165, 12, 10, 20, 21, 18, 'Processing will take 5 working days.', 1, NULL, 'read', 0, 0, NULL, '2024-06-30 07:00:00', 0, 0),
(166, 10, 12, 20, 18, 21, 'Noted. Keep us updated on progress.', 0, NULL, 'sent', 0, 0, NULL, '2024-06-30 12:30:00', 0, 0),
(167, 1, 2, 1, 0, 0, 'Finance team, please submit the Q2 budget report by end of this week.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:04:03', 1, 0),
(168, 2, 1, 1, 0, 0, 'Noted Admin. We will prepare and submit by Friday.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:04:03', 0, 0),
(169, 1, 2, 1, 0, 0, 'Thank you. Also please include the Q3 budget projections.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 1, 0),
(170, 1, 3, 2, 0, 0, 'Sales team, what is the status of this month\'s targets?', 1, NULL, 'sent', 0, 0, NULL, '2026-05-20 10:04:03', 0, 0),
(171, 3, 1, 2, 0, 0, 'Admin, we have achieved 85% of the target so far. Expecting to hit 100% by month end.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:04:03', 0, 0),
(172, 1, 3, 2, 0, 0, 'Great work! Keep pushing. Any new major clients?', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(173, 11, 1, 3, 0, 0, 'Admin, Modern Villa project update: 85% complete. On track for July completion.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:04:03', 0, 0),
(174, 1, 11, 3, 0, 0, 'Excellent progress. Any issues or delays we should be aware of?', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:04:03', 0, 0),
(175, 11, 1, 3, 0, 0, 'Minor material delays but overall on schedule.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(176, 1, 7, 4, 0, 0, 'Aluminium team, the government order for 10,000 window frames is confirmed.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-20 10:04:03', 0, 0),
(177, 7, 1, 4, 0, 0, 'Confirmed Admin. We have the capacity to deliver within 3 months.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:04:03', 0, 0),
(178, 1, 7, 4, 0, 0, 'Perfect. Please prepare a production schedule.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(179, 12, 1, 5, 0, 0, 'Admin, digital title deed system is 75% complete.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:04:03', 0, 0),
(180, 1, 12, 5, 0, 0, 'Great progress! When is the expected launch date?', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:04:03', 1, 0),
(181, 12, 1, 5, 0, 0, 'Launch scheduled for September 2024. We will arrange a demo for management.', 1, '2026-05-23 13:52:52', 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(182, 1, 4, 6, 0, 0, 'Manager, please prepare Q2 performance reports for all departments.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:04:03', 0, 0),
(183, 4, 1, 6, 0, 0, 'Noted Admin. We are compiling the reports.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:04:03', 0, 0),
(184, 1, 4, 6, 0, 0, 'Please ensure all departments submit by July 10th.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(185, 6, 1, 7, 0, 0, 'Admin, brick production has reached 250,000 units this month.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:04:03', 0, 0),
(186, 1, 6, 7, 0, 0, 'Great achievement! What is the target for next month?', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:04:03', 0, 0),
(187, 6, 1, 7, 0, 0, 'Target for July is 280,000 units. We have added a night shift.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(188, 5, 1, 8, 0, 0, 'Admin, office closure notice: Friday is a public holiday.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:04:03', 0, 0),
(189, 1, 5, 8, 0, 0, 'Noted. Please ensure all staff are informed.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(190, 10, 1, 9, 0, 0, 'Admin, all survey equipment is operational now.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:04:03', 0, 0),
(191, 1, 10, 9, 0, 0, 'Good to hear. Keep up the good work.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(192, 9, 1, 10, 0, 0, 'Admin, architectural designs for the city center project are ready for review.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:04:03', 0, 0),
(193, 1, 9, 10, 0, 0, 'Please send them for my review. I will provide feedback by Wednesday.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(194, 8, 1, 11, 0, 0, 'Admin, the Dar es Salaam master plan is 65% complete.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:04:03', 0, 0),
(195, 1, 8, 11, 0, 0, 'Great progress. Keep me updated.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(196, 2, 1, 12, 0, 0, 'Admin, the Q2 financial report is ready for your review.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:04:03', 0, 0),
(197, 1, 2, 12, 0, 0, 'Please upload it to the shared drive. I will review it tomorrow.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 10:04:03', 0, 0),
(198, 1, 3, 1, 0, 0, 'Sales team, we have a major lead from a construction company worth TZS 500M. They want to discuss potential partnership.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:13:13', 1, 0),
(199, 3, 1, 1, 0, 0, 'Admin, that is excellent news! Please schedule a meeting with them next week.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(200, 1, 3, 1, 0, 0, 'Meeting scheduled for Tuesday at 10 AM. Please prepare a presentation about our capabilities.', 1, '2026-05-24 01:33:23', 'read', 0, 0, NULL, '2026-05-23 10:13:13', 1, 0),
(201, 12, 1, 5, 0, 0, 'Admin, digital title deed system is 75% complete. Launch scheduled for September 2024.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-20 10:13:13', 0, 0),
(202, 1, 12, 5, 0, 0, 'Excellent progress! Please arrange a demo for the management team next week.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:13:13', 1, 0),
(203, 12, 1, 5, 0, 0, 'Demo scheduled for Tuesday at 2 PM. Boardroom 2. Will send invitation.', 1, '2026-05-23 13:52:52', 'sent', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(204, 1, 4, 13, 0, 0, 'Manager, please communicate to all departments: Company strategy meeting on July 10, 2024. Attendance mandatory.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:13:13', 1, 0),
(205, 4, 1, 13, 0, 0, 'Message relayed to all departments. Conference room reserved for 50 people.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(206, 1, 4, 13, 0, 0, 'Excellent. Agenda will be shared by end of week. Please prepare department presentations.', 1, '2026-05-23 14:12:00', 'sent', 0, 0, NULL, '2026-05-23 10:13:13', 1, 0),
(207, 1, 4, 36, 0, 0, 'Manager, annual strategy meeting scheduled for July 15. Please prepare department reports.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-20 10:13:13', 0, 0),
(208, 4, 1, 36, 0, 0, 'Noted Admin. All departments are preparing their presentations.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-21 10:13:13', 0, 0),
(209, 1, 4, 36, 0, 0, 'Confirmed. Looking forward to the presentations.', 1, '2026-05-23 14:21:06', 'sent', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(210, 1, 2, 74, 0, 0, 'Finance team, please submit the Q2 budget report by end of this week.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(211, 2, 1, 74, 0, 0, 'Noted Admin. We will prepare and submit by Friday.', 1, '2026-05-23 13:14:34', 'sent', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(212, 1, 3, 75, 0, 0, 'Sales team, what is the status of this month\'s targets?', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(213, 3, 1, 75, 0, 0, 'Admin, we have achieved 85% of the target so far. Expecting to hit 100% by month end.', 1, '2026-05-23 13:14:41', 'sent', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(214, 11, 1, 76, 0, 0, 'Admin, Modern Villa project update: 85% complete. On track for July completion.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(215, 1, 11, 76, 0, 0, 'Excellent progress. Any issues or delays we should be aware of?', 1, '2026-05-23 14:59:28', 'sent', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(216, 1, 7, 77, 0, 0, 'Aluminium team, the government order for 10,000 window frames is confirmed.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(217, 7, 1, 77, 0, 0, 'Confirmed Admin. We have the capacity to deliver within 3 months.', 1, '2026-05-23 13:14:50', 'sent', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(218, 12, 1, 78, 0, 0, 'Admin, digital title deed system development is progressing well.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(219, 1, 12, 78, 0, 0, 'Great! Keep me updated on the progress.', 1, '2026-05-24 01:24:06', 'read', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(220, 1, 4, 79, 0, 0, 'Manager, please prepare Q2 performance reports for all departments.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(221, 4, 1, 79, 0, 0, 'Noted Admin. We are compiling the reports.', 1, '2026-05-23 13:14:52', 'sent', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(222, 6, 1, 80, 0, 0, 'Admin, brick production has reached 250,000 units this month.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(223, 1, 6, 80, 0, 0, 'Great achievement! What is the target for next month?', 1, '2026-05-23 17:35:15', 'read', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(224, 5, 1, 81, 0, 0, 'Admin, office closure notice: Friday is a public holiday.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(225, 1, 5, 81, 0, 0, 'Noted. Please ensure all staff are informed.', 1, '2026-05-23 14:56:31', 'read', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(226, 10, 1, 82, 0, 0, 'Admin, all survey equipment is operational now.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(227, 1, 10, 82, 0, 0, 'Good to hear. Keep up the good work.', 1, '2026-05-24 02:10:30', 'read', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(228, 9, 1, 83, 0, 0, 'Admin, architectural designs for the city center project are ready for review.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(229, 1, 9, 83, 0, 0, 'Please send them for my review. I will provide feedback by Wednesday.', 1, '2026-05-23 14:48:18', 'sent', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(230, 8, 1, 84, 0, 0, 'Admin, the Dar es Salaam master plan is 65% complete.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(231, 1, 8, 84, 0, 0, 'Great progress. Keep me updated.', 1, '2026-05-24 15:36:07', 'read', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(232, 2, 1, 85, 0, 0, 'Admin, the Q2 financial report is ready for your review.', 1, NULL, 'sent', 0, 0, NULL, '2026-05-22 10:13:13', 0, 0),
(233, 1, 2, 85, 0, 0, 'Please upload it to the shared drive. I will review it tomorrow.', 1, '2026-05-23 16:25:44', 'sent', 0, 0, NULL, '2026-05-23 10:13:13', 0, 0),
(234, 1, 2, 34, 0, 0, 'Test message from Super Admin', 1, '2026-05-23 16:08:48', 'sent', 0, 0, NULL, '2026-05-23 10:17:05', 1, 0),
(235, 1, 3, 1, 0, 0, 'admin', 1, '2026-05-24 01:33:23', 'read', 0, 0, NULL, '2026-05-23 10:17:43', 1, 0),
(236, 6, 1, 38, 0, 0, 'sawa', 1, '2026-05-23 23:29:41', 'sent', 0, 0, NULL, '2026-05-23 10:39:31', 0, 0),
(237, 6, 1, 38, 0, 0, 'sawa', 1, '2026-05-23 23:29:41', 'sent', 0, 0, NULL, '2026-05-23 10:39:37', 0, 0),
(238, 6, 1, 38, 0, 0, 'sawa', 1, '2026-05-23 23:29:41', 'sent', 0, 0, NULL, '2026-05-23 10:43:54', 0, 0),
(239, 1, 2, 34, 1, 2, 'Test message to Finance', 1, '2026-05-23 16:08:48', 'sent', 0, 0, NULL, '2026-05-23 10:49:30', 1, 0),
(240, 1, 2, 34, 1, 2, 'Test message', 1, '2026-05-23 16:08:48', 'sent', 0, 0, NULL, '2026-05-23 11:03:14', 1, 0),
(241, 2, 1, 34, 0, 0, 'Hello Super Admin', 1, '2026-05-23 14:11:31', 'sent', 0, 0, NULL, '2026-05-23 11:11:00', 0, 0),
(242, 4, 1, 13, 0, 0, 'sawa', 1, '2026-05-23 14:17:13', 'sent', 0, 0, NULL, '2026-05-23 11:11:56', 0, 0),
(243, 4, 1, 13, 0, 0, 'sawa', 1, '2026-05-23 14:17:13', 'sent', 0, 0, NULL, '2026-05-23 11:12:26', 0, 0),
(244, 1, 4, 13, 0, 0, 'sawa sawa', 1, '2026-05-23 14:20:12', 'sent', 0, 0, NULL, '2026-05-23 11:19:53', 1, 0),
(245, 4, 1, 13, 0, 0, 'hakuna shida yoyote', 1, '2026-05-23 14:24:37', 'sent', 0, 0, NULL, '2026-05-23 11:20:27', 0, 0),
(246, 1, 4, 13, 0, 0, 'sawa', 1, '2026-05-23 14:25:07', 'sent', 0, 0, NULL, '2026-05-23 11:24:43', 1, 0),
(247, 1, 4, 13, 0, 0, 'kazi imeisha ?', 1, '2026-05-23 14:25:07', 'sent', 0, 0, NULL, '2026-05-23 11:25:02', 1, 0),
(248, 1, 4, 13, 0, 0, '1900000', 1, '2026-05-23 14:30:12', 'sent', 0, 0, NULL, '2026-05-23 11:29:48', 1, 0),
(249, 1, 4, 13, 0, 0, 'sawa', 1, '2026-05-23 14:32:34', 'sent', 0, 0, NULL, '2026-05-23 11:32:25', 1, 0),
(250, 4, 1, 13, 0, 0, 'm', 1, '2026-05-23 14:38:46', 'sent', 0, 0, NULL, '2026-05-23 11:38:34', 0, 0),
(251, 1, 4, 13, 0, 0, 'sawa', 1, '2026-05-23 14:39:00', 'sent', 1, 0, '2026-05-23 14:38:56', '2026-05-23 11:38:51', 1, 0),
(252, 1, 4, 13, 0, 0, 'bado kidogo', 1, '2026-05-23 14:39:31', 'sent', 0, 0, NULL, '2026-05-23 11:39:19', 1, 0),
(253, 4, 1, 13, 0, 0, 'nn', 1, '2026-05-23 14:43:11', 'sent', 0, 0, NULL, '2026-05-23 11:43:04', 0, 0),
(254, 1, 4, 13, 0, 0, 'sawa', 1, '2026-05-23 14:43:21', 'sent', 0, 0, NULL, '2026-05-23 11:43:17', 1, 0),
(255, 4, 1, 13, 0, 0, 'tutakuja wote', 1, '2026-05-23 14:43:51', 'sent', 0, 0, NULL, '2026-05-23 11:43:29', 0, 0),
(256, 7, 1, 77, 0, 0, 'sawa', 1, '2026-05-23 14:45:42', 'sent', 0, 0, NULL, '2026-05-23 11:45:37', 0, 0),
(257, 1, 7, 77, 0, 0, 'okay kazi nyema', 1, '2026-05-23 14:45:59', 'sent', 0, 0, NULL, '2026-05-23 11:45:50', 0, 0),
(258, 7, 1, 77, 0, 0, 'sawa boss', 1, '2026-05-23 14:46:30', 'sent', 0, 0, NULL, '2026-05-23 11:46:08', 0, 0),
(259, 3, 1, 1, 0, 0, 'sawa', 1, '2026-05-23 15:47:00', 'sent', 0, 0, NULL, '2026-05-23 11:56:02', 0, 0),
(260, 7, 1, 39, 0, 0, 'sawa', 1, '2026-05-23 23:29:47', 'sent', 0, 0, NULL, '2026-05-23 11:57:13', 0, 0),
(261, 7, 11, 19, 0, 0, 'okay', 1, '2026-05-23 14:59:16', 'sent', 0, 1, '2026-05-23 23:45:18', '2026-05-23 11:57:24', 1, 0),
(262, 8, 1, 84, 0, 0, 'sawa', 1, '2026-05-23 15:46:58', 'sent', 0, 0, NULL, '2026-05-23 11:58:23', 0, 0),
(263, 2, 1, 34, 0, 0, 'sawa', 1, '2026-05-23 15:45:54', 'sent', 0, 0, NULL, '2026-05-23 12:11:42', 0, 0),
(264, 3, 35, 86, 0, 0, 'sawa', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 12:12:05', 0, 0),
(265, 3, 35, 86, 0, 0, 'sawa', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 12:12:36', 0, 0),
(266, 3, 53, 87, 0, 0, 'sawa', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 12:12:53', 0, 0),
(267, 3, 52, 88, 0, 0, 'sawa', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 12:12:56', 0, 0),
(268, 3, 45, 89, 0, 0, 'sawa', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 12:13:11', 0, 0),
(269, 3, 7, 3, 0, 0, 'sawa', 1, '2026-05-24 01:03:04', 'sent', 0, 0, NULL, '2026-05-23 12:13:22', 0, 0),
(270, 3, 89, 90, 0, 0, 'sawa', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 12:13:27', 0, 0),
(271, 1, 2, 34, 0, 0, 'OO', 1, '2026-05-23 16:08:48', 'sent', 0, 0, NULL, '2026-05-23 12:48:24', 1, 0),
(272, 1, 4, 13, 0, 0, 'SAWA', 1, '2026-05-23 15:48:50', 'sent', 0, 0, NULL, '2026-05-23 12:48:40', 1, 0),
(273, 4, 1, 13, 0, 0, 'POA', 1, '2026-05-23 17:38:09', 'sent', 0, 0, NULL, '2026-05-23 12:48:57', 0, 0),
(274, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 17:14:09', 'sent', 0, 0, NULL, '2026-05-23 13:08:57', 0, 0),
(275, 2, 1, 85, 0, 0, 'SAWA', 1, '2026-05-23 17:38:10', 'sent', 0, 0, NULL, '2026-05-23 13:25:51', 0, 0),
(276, 2, 4, 54, 0, 0, 'SAWA', 1, '2026-05-24 00:59:37', 'sent', 0, 0, NULL, '2026-05-23 13:44:36', 0, 0),
(277, 9, 1, 83, 0, 0, 'SAWA', 1, '2026-05-23 17:14:14', 'sent', 0, 0, NULL, '2026-05-23 13:52:15', 0, 0),
(278, 2, 4, 54, 0, 0, 'SAWA', 1, '2026-05-24 00:59:37', 'sent', 0, 0, NULL, '2026-05-23 13:54:24', 0, 0),
(279, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 17:14:09', 'sent', 0, 0, NULL, '2026-05-23 13:55:10', 0, 0),
(280, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 17:14:09', 'sent', 0, 0, NULL, '2026-05-23 14:01:11', 0, 0),
(281, 2, 4, 14, 0, 0, 'SAWA', 1, '2026-05-24 00:49:18', 'sent', 0, 0, NULL, '2026-05-23 14:06:37', 0, 0),
(282, 2, 1, 34, 0, 0, 'NIKO LIVE', 1, '2026-05-23 17:14:09', 'sent', 0, 0, NULL, '2026-05-23 14:06:51', 0, 0),
(283, 2, 1, 34, 0, 0, 'MIM', 1, '2026-05-23 17:14:09', 'sent', 0, 0, NULL, '2026-05-23 14:08:53', 0, 0),
(284, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 17:14:09', 'sent', 0, 0, NULL, '2026-05-23 14:10:33', 0, 0),
(285, 2, 5, 59, 0, 0, 'SAWA', 1, '2026-05-24 14:17:29', 'read', 0, 0, NULL, '2026-05-23 14:10:48', 0, 0),
(286, 2, 5, 59, 0, 0, 'POA', 1, '2026-05-24 14:17:29', 'read', 0, 0, NULL, '2026-05-23 14:10:54', 0, 0),
(287, 9, 2, 91, 0, 0, 'SAWA', 1, '2026-05-23 17:13:36', 'sent', 0, 0, NULL, '2026-05-23 14:13:30', 0, 0),
(288, 1, 9, 83, 0, 0, 'POA', 1, '2026-05-23 17:14:26', 'sent', 0, 0, NULL, '2026-05-23 14:14:18', 0, 0),
(289, 6, 4, 92, 0, 0, 'SAWA', 1, '2026-05-24 00:49:22', 'sent', 0, 0, NULL, '2026-05-23 14:23:48', 0, 0),
(290, 2, 12, 50, 0, 0, 'SAWA', 1, '2026-05-24 01:24:03', 'read', 0, 0, NULL, '2026-05-23 14:27:33', 0, 0),
(291, 2, 1, 34, 0, 0, 'FINANCE', 1, '2026-05-23 17:38:08', 'sent', 0, 0, NULL, '2026-05-23 14:27:48', 0, 0),
(292, 2, 1, 34, 0, 0, 'M', 1, '2026-05-23 17:38:08', 'sent', 0, 0, NULL, '2026-05-23 14:31:54', 0, 0),
(293, 6, 1, 80, 0, 0, 'SAWA', 1, '2026-05-23 17:38:06', 'sent', 0, 0, NULL, '2026-05-23 14:35:20', 0, 0),
(294, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 18:01:42', 'sent', 0, 0, NULL, '2026-05-23 14:48:51', 0, 0),
(295, 6, 11, 60, 0, 0, 'SAWA', 1, '2026-05-23 22:54:19', 'sent', 0, 0, NULL, '2026-05-23 14:55:04', 0, 0),
(296, 6, 1, 80, 0, 0, 'SAWA FINAL', 1, '2026-05-23 18:01:44', 'sent', 0, 0, NULL, '2026-05-23 14:56:18', 0, 0),
(297, 6, 1, 80, 0, 0, 'F', 1, '2026-05-23 18:01:44', 'sent', 0, 0, NULL, '2026-05-23 14:56:45', 0, 0),
(298, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 18:01:42', 'sent', 0, 0, NULL, '2026-05-23 15:01:00', 0, 0),
(299, 1, 2, 34, 0, 0, 'SAWA', 1, '2026-05-23 18:20:29', 'sent', 0, 0, NULL, '2026-05-23 15:01:56', 1, 0),
(300, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 21:47:38', 'sent', 0, 0, NULL, '2026-05-23 15:02:22', 0, 0),
(301, 2, 3, 45, 0, 0, 'SAWA', 1, '2026-05-24 12:38:35', 'read', 0, 0, NULL, '2026-05-23 15:17:29', 0, 0),
(302, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 21:47:38', 'sent', 0, 0, NULL, '2026-05-23 15:20:29', 0, 0),
(303, 6, 1, 80, 0, 0, 'sawa', 1, '2026-05-23 21:47:43', 'sent', 0, 0, NULL, '2026-05-23 15:36:48', 0, 0),
(304, 6, 4, 92, 0, 0, 'saw', 1, '2026-05-24 00:49:22', 'sent', 0, 0, NULL, '2026-05-23 15:48:02', 0, 0),
(305, 6, 1, 80, 0, 0, 'sawa', 1, '2026-05-23 21:47:43', 'sent', 0, 0, NULL, '2026-05-23 15:59:34', 0, 0),
(306, 6, 1, 80, 0, 0, 'DAA', 1, '2026-05-23 21:47:43', 'sent', 0, 0, NULL, '2026-05-23 16:07:55', 0, 0),
(307, 2, 1, 34, 0, 0, 'SAW', 1, '2026-05-23 21:47:38', 'sent', 0, 0, NULL, '2026-05-23 16:08:43', 0, 0),
(308, 6, 1, 80, 0, 0, 'SAWA', 1, '2026-05-23 21:47:43', 'sent', 0, 0, NULL, '2026-05-23 16:17:07', 0, 0),
(309, 6, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 21:47:38', 'sent', 0, 0, NULL, '2026-05-23 16:17:28', 0, 0),
(310, 5, 80, 93, 0, 0, 'POA', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 16:18:36', 0, 0),
(311, 2, 4, 14, 2, 6, 'Hello from Finance! This is a test message.', 1, '2026-05-24 00:49:18', 'sent', 0, 0, NULL, '2026-05-23 16:38:56', 0, 0),
(312, 4, 2, 14, 6, 2, 'Hello Finance! Message received. Thank you.', 1, '2026-05-23 22:22:34', 'sent', 0, 0, NULL, '2026-05-23 16:38:56', 0, 0),
(313, 2, 4, 14, 2, 6, 'When can we schedule the budget review meeting?', 1, '2026-05-24 00:49:18', 'sent', 1, 0, '2026-05-23 22:23:01', '2026-05-23 16:38:56', 0, 0),
(314, 4, 2, 9, 6, 2, 'Finance, please prepare Q3 budget report', 1, '2026-05-23 22:22:27', 'sent', 0, 0, NULL, '2026-05-23 16:39:14', 0, 0),
(315, 2, 4, 9, 2, 6, 'Will do. Expect it by Friday.', 1, '2026-05-24 00:49:19', 'sent', 0, 0, NULL, '2026-05-23 16:39:14', 0, 0),
(316, 2, 1, 85, 2, 1, 'Super Admin, Q2 financial report is ready for review', 1, '2026-05-23 21:48:02', 'sent', 0, 0, NULL, '2026-05-23 16:39:30', 0, 0),
(317, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 21:47:38', 'sent', 0, 0, NULL, '2026-05-23 16:41:38', 0, 0),
(318, 2, 4, 14, 2, 6, 'Test message from Finance to Manager', 1, '2026-05-24 00:49:18', 'sent', 0, 0, NULL, '2026-05-23 16:44:13', 0, 0),
(319, 2, 6, 80, 0, 0, 'SAWA', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 16:47:18', 0, 0),
(320, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 21:47:38', 'sent', 0, 0, NULL, '2026-05-23 16:54:57', 0, 0),
(321, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 21:47:38', 'sent', 0, 0, NULL, '2026-05-23 16:58:10', 0, 0),
(322, 2, 6, 80, 0, 0, 'SAWA', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 17:02:18', 0, 0),
(323, 2, 1, 34, 0, 0, 'SAWA', 1, '2026-05-23 21:47:38', 'sent', 0, 0, NULL, '2026-05-23 17:02:36', 0, 0),
(324, 2, 1, 34, 0, 0, 'vip boss', 1, '2026-05-23 23:29:32', 'sent', 0, 0, NULL, '2026-05-23 19:23:21', 0, 0),
(325, 2, 4, 46, 0, 0, 'sawa', 1, '2026-05-24 00:59:32', 'sent', 0, 0, NULL, '2026-05-23 19:27:30', 0, 0),
(326, 2, 1, 34, 0, 0, 'admin', 1, '2026-05-23 23:29:32', 'sent', 0, 0, NULL, '2026-05-23 19:27:47', 0, 0),
(327, 2, 4, 20, 0, 0, 'sawa', 1, '2026-05-24 00:49:16', 'sent', 0, 0, NULL, '2026-05-23 19:28:21', 0, 0),
(328, 2, 11, 2, 0, 0, 'tuko wote', 1, '2026-05-23 22:54:06', 'sent', 0, 0, NULL, '2026-05-23 19:28:35', 0, 0),
(329, 2, 1, 74, 0, 0, 'yes boss', 1, '2026-05-23 22:31:59', 'sent', 0, 0, NULL, '2026-05-23 19:28:47', 0, 0),
(330, 1, 2, 74, 0, 0, 'MJE WOTE', 1, '2026-05-23 22:32:19', 'sent', 0, 0, NULL, '2026-05-23 19:32:06', 0, 0),
(331, 2, 1, 74, 0, 0, 'SAWA', 1, '2026-05-23 22:32:33', 'sent', 0, 0, NULL, '2026-05-23 19:32:27', 0, 0),
(332, 11, 2, 2, 0, 0, 'SAWA', 1, '2026-05-23 22:54:42', 'sent', 0, 0, NULL, '2026-05-23 19:54:14', 0, 0),
(333, 11, 2, 2, 0, 0, 'TUKO PAMOJA', 1, '2026-05-23 22:54:42', 'sent', 0, 0, NULL, '2026-05-23 19:54:33', 0, 0),
(334, 2, 11, 2, 0, 0, 'POA', 1, '2026-05-23 22:54:52', 'sent', 0, 0, NULL, '2026-05-23 19:54:47', 0, 0),
(335, 11, 1, 76, 0, 0, 'SAWA BOSS', 1, '2026-05-23 23:25:21', 'sent', 0, 0, NULL, '2026-05-23 19:55:31', 0, 0),
(336, 11, 1, 76, 0, 0, 'ADMIN', 1, '2026-05-23 23:25:21', 'sent', 0, 0, NULL, '2026-05-23 20:01:28', 0, 0),
(337, 11, 1, 76, 0, 0, 'YES', 1, '2026-05-23 23:25:21', 'sent', 0, 0, NULL, '2026-05-23 20:07:26', 0, 0),
(338, 11, 1, 76, 0, 0, 'SAWA', 1, '2026-05-23 23:25:21', 'sent', 0, 0, NULL, '2026-05-23 20:15:23', 0, 0),
(339, 11, 1, 76, 0, 0, 'G', 1, '2026-05-23 23:25:21', 'sent', 0, 0, NULL, '2026-05-23 20:23:09', 0, 0),
(340, 1, 11, 76, 0, 0, 'MN', 1, '2026-05-23 23:29:08', 'sent', 0, 0, NULL, '2026-05-23 20:25:26', 0, 0),
(341, 1, 2, 34, 0, 0, 'POA', 1, '2026-05-24 12:23:08', 'sent', 0, 0, NULL, '2026-05-23 20:29:39', 1, 0),
(342, 2, 11, 2, 0, 0, 'KARIBU', 1, '2026-05-23 23:31:42', 'sent', 0, 0, NULL, '2026-05-23 20:30:13', 0, 0),
(343, 2, 4, 9, 0, 0, 'MANAGER', 1, '2026-05-24 00:49:19', 'sent', 0, 0, NULL, '2026-05-23 20:30:57', 0, 0),
(344, 2, 4, 9, 0, 0, 'SAWA', 1, '2026-05-24 00:49:19', 'sent', 0, 0, NULL, '2026-05-23 20:31:35', 0, 0),
(345, 11, 2, 2, 0, 0, 'POA', 1, '2026-05-23 23:38:00', 'sent', 0, 0, NULL, '2026-05-23 20:31:48', 0, 0),
(346, 11, 2, 2, 0, 0, 'HWL', 1, '2026-05-23 23:38:00', 'sent', 0, 0, NULL, '2026-05-23 20:33:41', 0, 0),
(347, 11, 1, 76, 0, 0, 'HI', 1, '2026-05-23 23:38:45', 'sent', 0, 0, NULL, '2026-05-23 20:37:12', 0, 0),
(348, 11, 1, 76, 0, 0, 'HH', 1, '2026-05-23 23:38:45', 'sent', 0, 0, NULL, '2026-05-23 20:37:54', 0, 0),
(349, 2, 4, 9, 0, 0, 'MM', 1, '2026-05-24 00:49:19', 'sent', 0, 0, NULL, '2026-05-23 20:38:30', 0, 0),
(350, 1, 2, 34, 0, 0, 'S', 1, '2026-05-24 12:23:08', 'sent', 0, 0, NULL, '2026-05-23 20:40:00', 1, 0),
(351, 1, 2, 34, 0, 0, 'BB', 1, '2026-05-24 12:23:08', 'sent', 0, 0, NULL, '2026-05-23 20:40:20', 1, 0),
(352, 11, 3, 52, 0, 0, 'HII', 1, '2026-05-24 12:39:03', 'read', 0, 0, NULL, '2026-05-23 20:45:00', 0, 0),
(353, 11, 1, 76, 0, 0, 'SUPER ADMIN', 1, '2026-05-24 00:59:45', 'sent', 0, 0, NULL, '2026-05-23 20:45:11', 0, 0),
(354, 11, 1, 76, 0, 0, 'WELCM', 1, '2026-05-24 00:59:45', 'sent', 0, 0, NULL, '2026-05-23 20:46:12', 0, 0),
(355, 11, 1, 43, 11, 1, 'HII', 1, '2026-05-23 23:51:14', 'sent', 0, 0, NULL, '2026-05-23 20:50:37', 0, 0),
(356, 1, 2, 34, 0, 0, 'MNG', 1, '2026-05-24 12:23:08', 'sent', 0, 0, NULL, '2026-05-23 20:52:00', 1, 0),
(357, 11, 2, 49, 0, 0, 'HH', 1, '2026-05-24 12:23:10', 'sent', 0, 0, NULL, '2026-05-23 20:52:34', 0, 0),
(358, 11, 6, 6, 0, 0, 'HII', 1, '2026-05-24 00:12:11', 'read', 0, 0, NULL, '2026-05-23 20:53:12', 0, 0),
(359, 11, 6, 6, 0, 0, 'HII', 1, '2026-05-24 00:12:11', 'read', 0, 0, NULL, '2026-05-23 20:53:41', 0, 0),
(360, 2, 1, 94, 2, 1, 'HII', 1, '2026-05-23 23:57:20', 'sent', 0, 0, NULL, '2026-05-23 20:57:08', 0, 0),
(361, 2, 9, 95, 2, 9, 'ARTCH', 1, '2026-05-24 00:14:58', 'sent', 0, 0, NULL, '2026-05-23 20:58:06', 0, 0),
(362, 11, 9, 96, 11, 9, 'ARCHT', 1, '2026-05-24 01:00:36', 'sent', 0, 0, NULL, '2026-05-23 20:58:24', 0, 0),
(363, 1, 4, 97, 1, 4, 'MNG', 1, '2026-05-24 00:49:13', 'sent', 0, 0, NULL, '2026-05-23 21:02:09', 0, 0),
(364, 6, 4, 98, 6, 4, 'SAWA', 1, '2026-05-24 00:49:04', 'sent', 0, 0, NULL, '2026-05-23 21:08:23', 0, 0),
(365, 6, 9, 99, 6, 9, 'SAWA', 1, '2026-05-24 00:14:02', 'sent', 0, 0, NULL, '2026-05-23 21:08:45', 0, 0),
(366, 6, 11, 100, 6, 11, 'YES', 1, '2026-05-24 01:24:41', 'sent', 0, 0, NULL, '2026-05-23 21:12:18', 0, 0),
(367, 6, 2, 101, 6, 2, 'BRK', 1, '2026-05-24 12:23:05', 'sent', 0, 0, NULL, '2026-05-23 21:12:43', 0, 0);
INSERT INTO `messages` (`id`, `sender_dept`, `receiver_dept`, `conversation_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `read_at`, `status`, `sender_deleted`, `receiver_deleted`, `deleted_at`, `created_at`, `deleted_by_sender`, `deleted_by_receiver`) VALUES
(368, 9, 6, 102, 9, 6, 'BORA AJE MWENYEW', 1, '2026-05-24 20:08:45', 'read', 0, 0, NULL, '2026-05-23 21:14:16', 0, 0),
(369, 9, 2, 103, 9, 2, 'OKAY', 1, '2026-05-24 12:23:03', 'sent', 0, 0, NULL, '2026-05-23 21:15:06', 0, 0),
(370, 9, 1, 104, 9, 1, 'hii', 1, '2026-05-24 00:21:00', 'sent', 0, 0, NULL, '2026-05-23 21:20:33', 0, 0),
(371, 9, 3, 105, 9, 3, 'mm', 1, '2026-05-24 12:38:38', 'read', 0, 0, NULL, '2026-05-23 21:20:47', 0, 0),
(372, 1, 9, 106, 1, 9, 'yes', 1, '2026-05-24 00:21:23', 'sent', 0, 0, NULL, '2026-05-23 21:21:05', 0, 0),
(373, 9, 1, 107, 9, 1, 'sawa', 1, '2026-05-24 00:21:36', 'sent', 0, 0, NULL, '2026-05-23 21:21:28', 0, 0),
(374, 1, 9, 108, 1, 9, 'okay', 1, '2026-05-24 00:26:27', 'sent', 0, 0, NULL, '2026-05-23 21:26:01', 0, 0),
(375, 1, 9, 109, 1, 9, 'sawa', 1, '2026-05-24 00:26:20', 'sent', 0, 0, NULL, '2026-05-23 21:26:11', 0, 0),
(376, 9, 1, 110, 9, 1, 'okay', 1, '2026-05-24 00:26:55', 'sent', 0, 0, NULL, '2026-05-23 21:26:48', 0, 0),
(377, 1, 9, 111, 1, 9, 'mm', 1, '2026-05-24 01:00:57', 'sent', 0, 0, NULL, '2026-05-23 21:27:31', 0, 0),
(378, 1, 9, 112, 1, 9, 'v', 1, '2026-05-24 01:00:45', 'sent', 0, 0, NULL, '2026-05-23 21:27:43', 0, 0),
(379, 1, 9, 113, 1, 9, 'c', 1, '2026-05-24 01:00:31', 'sent', 0, 0, NULL, '2026-05-23 21:27:50', 0, 0),
(380, 1, 9, 114, 1, 9, 'c', 1, '2026-05-24 01:00:48', 'sent', 0, 0, NULL, '2026-05-23 21:28:13', 0, 0),
(381, 1, 9, 115, 1, 9, 'c', 1, '2026-05-24 01:00:30', 'sent', 0, 0, NULL, '2026-05-23 21:28:21', 0, 0),
(382, 1, 9, 116, 1, 9, 'c', 1, '2026-05-24 00:29:27', 'sent', 0, 0, NULL, '2026-05-23 21:28:29', 0, 0),
(383, 1, 9, 117, 1, 9, 'c', 1, '2026-05-24 01:00:33', 'sent', 0, 0, NULL, '2026-05-23 21:28:37', 0, 0),
(384, 1, 9, 118, 1, 9, 'c', 1, '2026-05-24 01:00:50', 'sent', 0, 0, NULL, '2026-05-23 21:28:43', 0, 0),
(385, 1, 9, 119, 1, 9, 'dd', 1, '2026-05-24 01:00:27', 'sent', 0, 0, NULL, '2026-05-23 21:30:26', 0, 0),
(386, 1, 9, 120, 1, 9, 'yes', 1, '2026-05-24 00:35:27', 'sent', 0, 0, NULL, '2026-05-23 21:35:08', 0, 0),
(387, 9, 1, 121, 9, 1, 'admni', 1, '2026-05-24 00:35:56', 'sent', 0, 0, NULL, '2026-05-23 21:35:33', 0, 0),
(388, 1, 9, 122, 1, 9, 'yes', 1, '2026-05-24 00:39:33', 'sent', 0, 0, NULL, '2026-05-23 21:36:05', 0, 0),
(389, 9, 1, 123, 9, 1, 'hellow', 1, '2026-05-24 00:39:45', 'sent', 0, 0, NULL, '2026-05-23 21:39:41', 0, 0),
(390, 1, 9, 124, 1, 9, 'yes', 1, '2026-05-24 01:00:52', 'sent', 0, 0, NULL, '2026-05-23 21:39:50', 0, 0),
(391, 1, 9, 125, 1, 9, 'hii', 1, '2026-05-24 01:00:54', 'sent', 0, 0, NULL, '2026-05-23 21:40:12', 0, 0),
(392, 1, 9, 41, 1, 9, 'mm', 1, '2026-05-24 00:42:23', 'sent', 0, 0, NULL, '2026-05-23 21:41:23', 1, 0),
(393, 1, 9, 41, 1, 9, 'hi', 1, '2026-05-24 00:42:23', 'sent', 0, 0, NULL, '2026-05-23 21:41:58', 1, 0),
(394, 9, 1, 41, 9, 1, 'admin', 1, '2026-05-24 00:42:37', 'sent', 0, 0, NULL, '2026-05-23 21:42:23', 1, 0),
(395, 1, 9, 41, 1, 9, 'sawa', 1, '2026-05-24 00:42:50', 'sent', 0, 0, NULL, '2026-05-23 21:42:42', 1, 0),
(396, 1, 9, 41, 1, 9, 'a', 1, '2026-05-24 00:48:00', 'sent', 1, 0, '2026-05-24 00:47:27', '2026-05-23 21:43:03', 1, 0),
(397, 1, 9, 41, 1, 9, 'a', 1, '2026-05-24 00:48:00', 'sent', 1, 0, '2026-05-24 00:47:30', '2026-05-23 21:43:10', 1, 0),
(398, 1, 9, 41, 1, 9, 'c', 1, '2026-05-24 00:48:00', 'sent', 1, 0, '2026-05-24 00:47:25', '2026-05-23 21:43:17', 1, 0),
(399, 1, 9, 41, 1, 9, 'c', 1, '2026-05-24 00:48:00', 'sent', 1, 0, '2026-05-24 00:47:33', '2026-05-23 21:43:23', 1, 0),
(400, 1, 9, 41, 1, 9, 'cc', 1, '2026-05-24 00:48:00', 'sent', 1, 0, '2026-05-24 00:47:22', '2026-05-23 21:43:32', 1, 0),
(401, 1, 9, 41, 1, 9, 's', 1, '2026-05-24 00:48:00', 'sent', 1, 0, '2026-05-24 00:47:37', '2026-05-23 21:44:14', 1, 0),
(402, 1, 9, 41, 1, 9, 'c', 1, '2026-05-24 00:48:00', 'sent', 1, 0, '2026-05-24 00:47:19', '2026-05-23 21:44:39', 1, 0),
(403, 1, 9, 41, 1, 9, 'Admn', 1, '2026-05-24 00:48:00', 'sent', 0, 0, NULL, '2026-05-23 21:47:48', 1, 0),
(404, 1, 9, 41, 1, 9, 'art', 1, '2026-05-24 00:48:34', 'sent', 0, 0, NULL, '2026-05-23 21:48:28', 1, 0),
(405, 9, 4, 57, 9, 4, 'hi', 1, '2026-05-24 00:50:15', 'sent', 0, 0, NULL, '2026-05-23 21:48:48', 0, 0),
(406, 4, 6, 98, 4, 6, 'okay', 1, '2026-05-24 20:09:00', 'read', 0, 0, NULL, '2026-05-23 21:49:10', 0, 0),
(407, 9, 4, 57, 9, 4, 'manager', 1, '2026-05-24 00:50:15', 'sent', 0, 0, NULL, '2026-05-23 21:49:43', 0, 0),
(408, 4, 9, 57, 4, 9, 'hii', 1, '2026-05-24 00:50:33', 'sent', 0, 0, NULL, '2026-05-23 21:50:13', 0, 0),
(409, 9, 4, 57, 9, 4, 'sawa', 1, '2026-05-24 00:50:40', 'sent', 0, 0, NULL, '2026-05-23 21:50:26', 0, 0),
(410, 9, 4, 57, 9, 4, 'tuje kesh?', 1, '2026-05-24 00:55:37', 'sent', 0, 0, NULL, '2026-05-23 21:50:55', 0, 0),
(411, 9, 4, 57, 9, 4, 'sawa', 1, '2026-05-24 00:55:37', 'sent', 0, 0, NULL, '2026-05-23 21:55:19', 0, 0),
(412, 4, 9, 57, 4, 9, 'sawa', 1, '2026-05-24 01:00:25', 'sent', 0, 0, NULL, '2026-05-23 21:55:36', 0, 0),
(413, 9, 4, 57, 9, 4, 'poa', 1, '2026-05-24 00:59:28', 'sent', 0, 0, NULL, '2026-05-23 21:55:56', 0, 0),
(414, 9, 4, 57, 9, 4, 'sawa', 1, '2026-05-24 01:01:34', 'sent', 0, 0, NULL, '2026-05-23 22:01:28', 0, 0),
(415, 9, 7, 63, 9, 7, 'TUMA TENA', 1, '2026-05-24 01:03:35', 'sent', 0, 0, NULL, '2026-05-23 22:03:25', 0, 0),
(416, 7, 9, 67, 7, 9, 'sawa', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 22:19:25', 0, 0),
(417, 7, 1, 39, 7, 1, 'sawa', 1, '2026-05-24 01:19:46', 'sent', 0, 0, NULL, '2026-05-23 22:19:39', 0, 0),
(418, 1, 7, 39, 1, 7, 'aswa', 1, '2026-05-24 01:20:45', 'sent', 0, 0, NULL, '2026-05-23 22:20:33', 1, 0),
(419, 7, 1, 39, 7, 1, 'sawasawa', 1, '2026-05-24 01:20:56', 'sent', 0, 0, NULL, '2026-05-23 22:20:52', 0, 0),
(420, 12, 2, 73, 12, 2, 'sawa', 1, '2026-05-24 12:23:01', 'sent', 0, 0, NULL, '2026-05-23 22:22:41', 0, 0),
(421, 12, 11, 126, 12, 11, 'duh', 1, '2026-05-24 01:24:37', 'sent', 0, 0, NULL, '2026-05-23 22:23:58', 0, 0),
(422, 11, 12, 126, 11, 12, 'sawa', 1, '2026-05-24 01:25:22', 'read', 0, 0, NULL, '2026-05-23 22:25:14', 0, 0),
(423, 12, 11, 126, 12, 11, 'okay', 1, '2026-05-24 01:25:33', 'sent', 0, 0, NULL, '2026-05-23 22:25:28', 0, 0),
(424, 3, 1, 1, 3, 1, 'hii', 1, '2026-05-24 02:24:30', 'sent', 0, 0, NULL, '2026-05-23 22:31:00', 0, 0),
(425, 3, 3, 1, 3, 3, 'sawa', 1, '2026-05-24 01:33:41', 'read', 0, 0, NULL, '2026-05-23 22:33:30', 0, 0),
(426, 3, 1, 1, 3, 1, 'sawa', 1, '2026-05-24 02:24:30', 'sent', 0, 0, NULL, '2026-05-23 22:33:40', 0, 0),
(427, 3, 1, 1, 3, 1, 'mm', 1, '2026-05-24 02:24:30', 'sent', 0, 0, NULL, '2026-05-23 22:38:50', 0, 0),
(428, 3, 2, 45, 3, 2, 'sawa', 1, '2026-05-24 12:22:55', 'sent', 0, 0, NULL, '2026-05-23 22:42:31', 0, 0),
(429, 5, 1, 37, 5, 1, 'sawa', 1, '2026-05-24 02:24:27', 'sent', 1, 0, '2026-05-24 14:41:40', '2026-05-23 22:43:11', 0, 0),
(430, 5, 4, 97, 5, 4, 'hii', 1, '2026-05-24 01:48:32', 'sent', 0, 0, NULL, '2026-05-23 22:48:09', 0, 0),
(431, 4, 1, 97, 4, 1, 'yes', 1, '2026-05-24 02:24:32', 'sent', 0, 0, NULL, '2026-05-23 22:48:37', 0, 0),
(432, 5, 1, 37, 5, 1, 'hii', 1, '2026-05-24 02:24:27', 'sent', 0, 0, NULL, '2026-05-23 23:02:12', 0, 0),
(433, 5, 97, 127, 5, 97, 'sawa', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 23:34:56', 0, 0),
(434, 5, 97, 127, 5, 97, 'poa', 0, NULL, 'sent', 0, 0, NULL, '2026-05-23 23:35:04', 0, 0),
(435, 4, 5, 18, 4, 5, 'sawa', 1, '2026-05-24 14:17:25', 'read', 0, 0, NULL, '2026-05-23 23:35:28', 0, 0),
(436, 1, 4, 97, 1, 4, 'sawa', 1, '2026-05-24 02:35:48', 'sent', 0, 0, NULL, '2026-05-23 23:35:42', 0, 0),
(437, 4, 5, 18, 4, 5, 'Manager', 1, '2026-05-24 14:17:25', 'read', 0, 0, NULL, '2026-05-24 08:25:49', 0, 0),
(438, 4, 1, 13, 4, 1, 'admin', 1, '2026-05-24 11:37:09', 'sent', 0, 0, NULL, '2026-05-24 08:36:16', 0, 0),
(439, 4, 1, 13, 4, 1, 'uko sawa?', 1, '2026-05-24 11:37:09', 'sent', 0, 0, NULL, '2026-05-24 08:36:28', 0, 0),
(440, 4, 1, 13, 4, 1, 'sawa', 1, '2026-05-24 11:37:09', 'sent', 0, 0, NULL, '2026-05-24 08:36:39', 0, 0),
(441, 4, 5, 18, 4, 5, 'hii', 1, '2026-05-24 14:17:25', 'read', 0, 0, NULL, '2026-05-24 08:47:21', 0, 0),
(442, 4, 5, 18, 4, 5, 'poa', 1, '2026-05-24 14:17:25', 'read', 0, 0, NULL, '2026-05-24 08:52:02', 0, 0),
(443, 4, 1, 13, 4, 1, 'super admin', 1, '2026-05-24 11:52:22', 'sent', 0, 0, NULL, '2026-05-24 08:52:15', 0, 0),
(444, 1, 4, 13, 1, 4, 'yes', 1, '2026-05-24 11:52:31', 'sent', 0, 0, NULL, '2026-05-24 08:52:28', 1, 0),
(445, 3, 1, 1, 3, 1, 'SUPER', 1, '2026-05-24 12:12:28', 'sent', 0, 0, NULL, '2026-05-24 08:58:05', 0, 0),
(446, 3, 1, 1, 3, 1, 'SAWA', 1, '2026-05-24 12:12:28', 'sent', 0, 0, NULL, '2026-05-24 09:12:23', 0, 0),
(447, 3, 1, 1, 3, 1, 'HII', 1, '2026-05-24 12:22:37', 'sent', 0, 0, NULL, '2026-05-24 09:20:50', 0, 0),
(448, 1, 3, 1, 0, 0, 'YES', 1, '2026-05-24 12:38:14', 'read', 0, 0, NULL, '2026-05-24 09:22:42', 1, 0),
(449, 2, 3, 45, 0, 0, 'POA', 1, '2026-05-24 12:38:35', 'read', 0, 0, NULL, '2026-05-24 09:23:37', 0, 0),
(450, 3, 1, 1, 0, 0, 'OKAY', 1, '2026-05-24 13:48:16', 'sent', 0, 0, NULL, '2026-05-24 09:24:02', 0, 0),
(451, 3, 1, 1, 0, 0, 'SAWA', 1, '2026-05-24 13:48:16', 'sent', 0, 0, NULL, '2026-05-24 09:38:20', 0, 0),
(452, 3, 11, 52, 0, 0, 'YES', 1, '2026-05-24 20:05:18', 'sent', 0, 0, NULL, '2026-05-24 09:39:07', 0, 0),
(453, 3, 1, 1, 0, 0, '📊 REPORT FROM SALES & MARKETING:\n\nTitle: Competitor Analysis Report\nPeriod: quarterly\n\nQ2 2024 Competitor Analysis\n\nMain Competitors:\n1. ABC Construction Ltd\n   - Market Share: 25%\n   - Strengths: Low prices, Fast delivery\n   - Weaknesses: Quality issues, Poor customer service\n\n2. XYZ Building Solutions\n   - Market Share: 18%\n   - Strengths: Innovative products, Strong brand\n   - Weaknesses: High prices, Limited reach\n\n3. BuildTech Ltd\n   - Market Share: 15%\n   - Strengths: Good reputation, Experienced team\n   - Weaknesses: Slow response, Outdated technology\n\nGeoTraverse Market Sh', 1, '2026-05-24 13:48:16', 'sent', 0, 0, NULL, '2026-05-24 10:05:16', 0, 0),
(454, 2, 1, 34, 2, 1, '📊 REPORT FROM FINANCE DEPARTMENT:\n\nTitle: MAIN\nPeriod: weekly\n\nFINANCE\n\n---\nGenerated from Finance Department Dashboard', 1, '2026-05-24 13:48:05', 'sent', 0, 0, NULL, '2026-05-24 10:47:53', 0, 0),
(455, 3, 1, 1, 3, 1, 'WANAKUJA', 1, '2026-05-24 13:49:50', 'sent', 0, 0, NULL, '2026-05-24 10:48:42', 0, 0),
(456, 5, 1, 37, 5, 1, 'SAWA', 1, '2026-05-24 14:41:58', 'sent', 0, 0, NULL, '2026-05-24 10:58:38', 0, 0),
(457, 5, 37, 128, 5, 37, 'POA', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 11:17:37', 0, 0),
(458, 5, 1, 37, 5, 1, 'OK', 1, '2026-05-24 14:41:58', 'sent', 0, 0, NULL, '2026-05-24 11:41:47', 0, 0),
(459, 5, 1, 37, 5, 1, 'SAWA', 1, '2026-05-24 14:42:41', 'sent', 0, 0, NULL, '2026-05-24 11:42:37', 0, 0),
(460, 1, 5, 37, 1, 5, 'SAWA', 1, '2026-05-24 14:49:42', 'read', 0, 0, NULL, '2026-05-24 11:42:59', 1, 0),
(461, 1, 5, 37, 1, 5, 'OKAY', 1, '2026-05-24 14:49:42', 'read', 0, 0, NULL, '2026-05-24 11:43:06', 1, 0),
(462, 1, 5, 37, 1, 5, 'poa', 1, '2026-05-24 14:49:42', 'read', 0, 0, NULL, '2026-05-24 11:49:33', 1, 0),
(463, 5, 1, 37, 5, 1, 'ikija takupigia', 1, '2026-05-24 14:49:56', 'sent', 0, 0, NULL, '2026-05-24 11:49:53', 0, 0),
(464, 5, 1, 37, 5, 1, 'sawa boss', 1, '2026-05-24 14:53:04', 'sent', 0, 0, NULL, '2026-05-24 11:52:58', 0, 0),
(465, 1, 5, 37, 1, 5, 'poa', 1, '2026-05-24 14:58:27', 'read', 0, 0, NULL, '2026-05-24 11:53:10', 1, 0),
(466, 5, 1, 37, 5, 1, 'sawa', 1, '2026-05-24 14:58:35', 'read', 0, 0, NULL, '2026-05-24 11:58:31', 0, 0),
(467, 1, 5, 37, 1, 5, 'okay', 1, '2026-05-24 15:00:54', 'read', 0, 0, NULL, '2026-05-24 11:58:41', 1, 0),
(468, 1, 5, 37, 1, 5, 'hi', 1, '2026-05-24 15:00:54', 'read', 0, 0, NULL, '2026-05-24 11:58:54', 1, 0),
(469, 3, 1, 1, 3, 1, 'poa', 1, '2026-05-24 14:59:29', 'read', 0, 0, NULL, '2026-05-24 11:59:23', 0, 0),
(470, 1, 3, 1, 1, 3, 'okay', 1, '2026-05-24 15:01:29', 'read', 0, 0, NULL, '2026-05-24 11:59:36', 1, 0),
(471, 5, 3, 129, 5, 3, 'hi', 1, '2026-05-24 15:01:16', 'read', 0, 0, NULL, '2026-05-24 12:01:11', 0, 0),
(472, 3, 5, 129, 3, 5, 'yes', 1, '2026-05-24 15:08:33', 'read', 0, 0, NULL, '2026-05-24 12:01:21', 0, 0),
(473, 1, 5, 37, 0, 0, 'yes', 1, '2026-05-24 15:08:39', 'read', 0, 0, NULL, '2026-05-24 12:08:27', 1, 0),
(474, 5, 3, 129, 0, 0, 'sawa', 1, '2026-05-24 20:08:07', 'read', 0, 0, NULL, '2026-05-24 12:08:37', 0, 0),
(475, 5, 1, 37, 0, 0, 'uje', 1, '2026-05-24 15:08:57', 'read', 0, 0, NULL, '2026-05-24 12:08:54', 0, 0),
(476, 1, 5, 37, 0, 0, 'sawa', 1, '2026-05-24 20:08:33', 'read', 0, 0, NULL, '2026-05-24 12:09:01', 1, 0),
(477, 10, 12, 72, 0, 0, 'SAWA', 1, '2026-05-24 15:24:06', 'read', 0, 0, NULL, '2026-05-24 12:23:52', 1, 0),
(478, 12, 10, 72, 0, 0, 'POAPOA', 1, '2026-05-24 15:24:15', 'read', 0, 0, NULL, '2026-05-24 12:24:11', 0, 0),
(479, 10, 1, 42, 0, 0, 'ASANTE BOSS', 1, '2026-05-24 15:27:57', 'read', 0, 0, NULL, '2026-05-24 12:27:48', 0, 0),
(480, 1, 10, 42, 0, 0, 'POA', 1, '2026-05-24 15:28:15', 'read', 0, 0, NULL, '2026-05-24 12:28:08', 1, 0),
(481, 8, 10, 65, 0, 0, 'OKAY', 1, '2026-05-24 20:10:12', 'read', 0, 0, NULL, '2026-05-24 12:35:36', 0, 0),
(482, 8, 1, 84, 0, 0, 'POA BOSS', 1, '2026-05-24 15:46:21', 'read', 0, 0, NULL, '2026-05-24 12:36:13', 0, 0),
(483, 8, 1, 40, 0, 0, '📋 PROJECT FROM TOWN PLANNING:\n\nProject: Dar es Salaam Master Plan\nClient: Dar City Council\nAmount: TZS 350,000,000\nStatus: in_progress\nProgress: 65%\nLocation: Dar es Salaam\n\nPlease review this project.', 1, '2026-05-24 15:46:17', 'read', 0, 0, NULL, '2026-05-24 12:46:03', 1, 0),
(484, 8, 4, 130, 0, 0, '📋 PROJECT FROM TOWN PLANNING:\n\nProject: Smart City Initiative\nClient: ICT Commission\nAmount: TZS 280,000,000\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this project.', 1, '2026-05-24 15:47:41', 'sent', 0, 0, NULL, '2026-05-24 12:47:22', 1, 0),
(485, 8, 1, 40, 0, 0, '📊 REPORT FROM TOWN PLANNING:\n\nTitle: Town Planning Applications Report - June\nPeriod: monthly\n\n📋 TOWN PLANNING APPLICATIONS REPORT - JUNE 2024\r\n\r\nApplications Received: 95\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\nBy Type:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Building Permits: 52 (55%)\r\n• Zoning Changes: 18 (19%)\r\n• Subdivisions: 15 (16%)\r\n• Land Use Changes: 10 (10%)\r\n\r\nBy Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Approved: 42\r\n• Under Review: 35\r\n• Pending: 12\r\n• Rejected: 6\r\n\r\nProcessing Times:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Building Permits: 14 days (Targe', 1, '2026-05-24 15:48:08', 'read', 0, 0, NULL, '2026-05-24 12:47:54', 1, 0),
(486, 8, 4, 130, 0, 0, '📊 REPORT FROM TOWN PLANNING:\n\nTitle: Town Planning Applications Report - June\nPeriod: monthly\n\n📋 TOWN PLANNING APPLICATIONS REPORT - JUNE 2024\r\n\r\nApplications Received: 95\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\nBy Type:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Building Permits: 52 (55%)\r\n• Zoning Changes: 18 (19%)\r\n• Subdivisions: 15 (16%)\r\n• Land Use Changes: 10 (10%)\r\n\r\nBy Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Approved: 42\r\n• Under Review: 35\r\n• Pending: 12\r\n• Rejected: 6\r\n\r\nProcessing Times:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Building Permits: 14 days (Targe', 1, '2026-05-24 15:48:15', 'sent', 0, 0, NULL, '2026-05-24 12:48:00', 1, 0),
(487, 4, 1, 13, 0, 0, '📋 PROJECT FROM MANAGER:\nTitle: Risk Management Framework\nClient: Internal\nAmount: TZS 40,000,000\nStatus: in_progress\nProgress: 45%', 1, '2026-05-24 15:52:46', 'read', 0, 0, NULL, '2026-05-24 12:52:34', 0, 0),
(488, 4, 8, 130, 0, 0, '📋 PROJECT FROM MANAGER:\nTitle: Risk Management Framework\nClient: Internal\nAmount: TZS 40,000,000\nStatus: in_progress\nProgress: 45%', 1, '2026-05-24 15:53:08', 'read', 0, 0, NULL, '2026-05-24 12:53:00', 0, 0),
(489, 4, 1, 13, 0, 0, '📋 PROJECT FROM MANAGER:\nTitle: Risk Management Framework\nClient: Internal\nAmount: TZS 40,000,000\nStatus: in_progress\nProgress: 45%', 1, '2026-05-24 16:09:45', 'read', 0, 0, NULL, '2026-05-24 13:06:58', 0, 0),
(490, 4, 1, 13, 0, 0, '📊 REPORT FROM MANAGER:\nTitle: Risk Assessment Report\nPeriod: monthly\n\nMonthly risk assessment for June 2024\n\nIdentified Risks:\n1. Supply chain disruption - High\n2. Currency fluctuation - Medium\n3. Staff retention - Medium\n4. Regulatory changes - Low\n\nMitigation Strategies:\n- Diversify suppliers\n- Hedging strategy\n- Improve employee benefits\n- Regular compliance audits', 1, '2026-05-24 16:09:45', 'read', 0, 0, NULL, '2026-05-24 13:09:38', 0, 0),
(491, 4, 8, 130, 0, 0, '📊 REPORT FROM MANAGER:\nTitle: may Town Planning Report\nPeriod: weekly\n\nMANAGER', 1, '2026-05-24 16:10:08', 'read', 0, 0, NULL, '2026-05-24 13:10:01', 0, 0),
(492, 4, 9, 57, 0, 0, '📊 REPORT FROM MANAGER:\nTitle: Annual Performance Review 2024\nPeriod: annual\n\nGeoTraverse Annual Performance Report 2024\n\nRevenue: TZS 2.5 Billion (↑12%)\nExpenses: TZS 1.8 Billion\nNet Profit: TZS 700 Million\n\nDepartment Performance:\n- Construction: Best performer\n- Aluminium: Above target\n- Sales: Met target\n- Others: On track\n\nStaff Satisfaction: 85%\nCustomer Satisfaction: 9', 1, '2026-05-24 16:10:33', 'sent', 0, 0, NULL, '2026-05-24 13:10:22', 0, 0),
(493, 1, 9, 41, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: Risk Assessment Report\nPeriod: monthly\n\nMonthly risk assessment for June 2024\n\nIdentified Risks:\n1. Supply chain disruption - High\n2. Currency fluctuation - Medium\n3. Staff retention - Medium\n4. Regulatory changes - Low\n\nMitigation Strategies:\n- Diversify suppliers\n- Hedging strategy\n- Improve employee benefits\n- Regular compliance audits\n\nRisk Score: 65/100 (Moderate)\nActions Required: 8 high priority items\n\n--- End of Report ---', 1, '2026-05-24 16:19:11', 'sent', 0, 0, NULL, '2026-05-24 13:18:52', 1, 0),
(494, 1, 4, 13, 0, 0, '📋 PROJECT FROM SUPER ADMIN:\n\nTitle: Eco-Friendly Housing Design\nClient: Green Building Council\nAmount: TZS 75,000,000\nStatus: completed\nProgress: 100%\nLocation: Arusha\n\nPlease review this project.', 1, '2026-05-24 20:08:17', 'sent', 0, 0, NULL, '2026-05-24 17:03:33', 1, 0),
(495, 1, 7, 39, 0, 0, '📋 PROJECT FROM SUPER ADMIN:\n\nTitle: Eco-Friendly Housing Design\nClient: Green Building Council\nAmount: TZS 75,000,000\nStatus: completed\nProgress: 100%\nLocation: Arusha\n\nPlease review this project.', 1, '2026-05-24 20:09:26', 'sent', 0, 0, NULL, '2026-05-24 17:03:38', 1, 0),
(496, 1, 8, 40, 0, 0, '📋 PROJECT FROM SUPER ADMIN:\n\nTitle: Eco-Friendly Housing Design\nClient: Green Building Council\nAmount: TZS 75,000,000\nStatus: completed\nProgress: 100%\nLocation: Arusha\n\nPlease review this project.', 1, '2026-05-24 20:09:40', 'read', 0, 0, NULL, '2026-05-24 17:03:42', 1, 0),
(497, 1, 9, 41, 0, 0, '📋 PROJECT FROM SUPER ADMIN:\n\nTitle: Eco-Friendly Housing Design\nClient: Green Building Council\nAmount: TZS 75,000,000\nStatus: completed\nProgress: 100%\nLocation: Arusha\n\nPlease review this project.', 1, '2026-05-24 20:09:59', 'sent', 0, 0, NULL, '2026-05-24 17:03:45', 1, 0),
(498, 1, 10, 42, 0, 0, '📋 PROJECT FROM SUPER ADMIN:\n\nTitle: Eco-Friendly Housing Design\nClient: Green Building Council\nAmount: TZS 75,000,000\nStatus: completed\nProgress: 100%\nLocation: Arusha\n\nPlease review this project.', 1, '2026-05-24 20:10:09', 'read', 0, 0, NULL, '2026-05-24 17:03:48', 1, 0),
(499, 1, 11, 43, 0, 0, '📋 PROJECT FROM SUPER ADMIN:\n\nTitle: Eco-Friendly Housing Design\nClient: Green Building Council\nAmount: TZS 75,000,000\nStatus: completed\nProgress: 100%\nLocation: Arusha\n\nPlease review this project.', 1, '2026-05-24 20:05:15', 'sent', 0, 0, NULL, '2026-05-24 17:03:51', 1, 0),
(500, 1, 12, 5, 0, 0, '📋 PROJECT FROM SUPER ADMIN:\n\nTitle: Eco-Friendly Housing Design\nClient: Green Building Council\nAmount: TZS 75,000,000\nStatus: completed\nProgress: 100%\nLocation: Arusha\n\nPlease review this project.', 1, '2026-05-24 20:11:03', 'read', 0, 0, NULL, '2026-05-24 17:03:54', 1, 0),
(501, 11, 1, 43, 0, 0, '📋 PROJECT FROM CONSTRUCTION:\nProject: Sports Stadium Renovation\nClient: Ministry of Sports\nAmount: TZS 350,000,000\nStatus: pending\nProgress: 20%', 1, '2026-05-24 20:05:57', 'read', 0, 0, NULL, '2026-05-24 17:05:43', 0, 0),
(502, 1, 2, 34, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: Weekly Design Team Update - Week 26\nPeriod: weekly\n\n✏️ ARCHITECTURAL DESIGN TEAM UPDATE - WEEK 26\r\n\r\nTeam Productivity:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hours Worked: 480 hours\r\n• Overtime: 45 hours\r\n• Designs Completed: 3 full designs\r\n• Drawings Produced: 85 sheets\r\n\r\nProject Deadlines:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Modern Villa Kigamboni - On track\r\n✓ Commercial Complex - Ahead by 2 days\r\n⚠️ School Design - Behind by 3 days\r\n✓ Luxury Apartments - On track\r\n\r\nNew Design Requests:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1\n\n--- End of Report ---', 1, '2026-05-24 20:07:42', 'sent', 0, 0, NULL, '2026-05-24 17:06:14', 1, 0),
(503, 1, 3, 1, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: MAIN myula 9999\nPeriod: weekly\n\n190,000,000\n\n--- End of Report ---', 1, '2026-05-24 20:08:02', 'read', 0, 0, NULL, '2026-05-24 17:06:20', 0, 0),
(504, 1, 4, 13, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: MAIN myula 9999\nPeriod: weekly\n\n190,000,000\n\n--- End of Report ---', 1, '2026-05-24 20:08:17', 'sent', 0, 0, NULL, '2026-05-24 17:06:26', 1, 0),
(505, 1, 5, 37, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: MAIN myula 9999\nPeriod: weekly\n\n190,000,000\n\n--- End of Report ---', 1, '2026-05-24 20:08:33', 'read', 0, 0, NULL, '2026-05-24 17:06:30', 1, 0),
(506, 1, 6, 38, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: MAIN myula 9999\nPeriod: weekly\n\n190,000,000\n\n--- End of Report ---', 1, '2026-05-24 20:08:42', 'read', 0, 0, NULL, '2026-05-24 17:06:35', 1, 0),
(507, 1, 7, 39, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: MAIN myula 9999\nPeriod: weekly\n\n190,000,000\n\n--- End of Report ---', 1, '2026-05-24 20:09:26', 'sent', 0, 0, NULL, '2026-05-24 17:06:41', 1, 0),
(508, 1, 8, 40, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: MAIN myula 9999\nPeriod: weekly\n\n190,000,000\n\n--- End of Report ---', 1, '2026-05-24 20:09:41', 'read', 0, 0, NULL, '2026-05-24 17:06:48', 1, 0),
(509, 1, 9, 41, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: MAIN myula 9999\nPeriod: weekly\n\n190,000,000\n\n--- End of Report ---', 1, '2026-05-24 20:09:59', 'sent', 0, 0, NULL, '2026-05-24 17:06:54', 1, 0),
(510, 1, 10, 42, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: INNOCENT\nPeriod: weekly\n\nADMN\n\n--- End of Report ---', 1, '2026-05-24 20:10:09', 'read', 0, 0, NULL, '2026-05-24 17:07:00', 1, 0),
(511, 1, 11, 43, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: INNOCENT\nPeriod: weekly\n\nADMN\n\n--- End of Report ---', 1, '2026-05-24 20:07:29', 'sent', 0, 0, NULL, '2026-05-24 17:07:07', 1, 0),
(512, 1, 12, 5, 0, 0, '📊 REPORT FROM SUPER ADMIN:\n\nTitle: INNOCENT\nPeriod: weekly\n\nADMN\n\n--- End of Report ---', 1, '2026-05-24 20:11:03', 'read', 0, 0, NULL, '2026-05-24 17:07:12', 1, 0),
(513, 12, 1, 5, 0, 0, '📋 TITLE DEED FROM HATIMILIKI:\n\nReference: Title Deed Digitization\nOwner: Internal\nAmount: TZS 120,000,000\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this title deed.', 1, '2026-05-24 20:14:14', 'read', 0, 0, NULL, '2026-05-24 17:14:08', 0, 0),
(514, 12, 1, 5, 0, 0, '📋 TITLE DEED FROM HATIMILIKI:\n\nReference: Mass Land Titling Program\nOwner: Ministry of Lands\nAmount: TZS 600,000,000\nStatus: in_progress\nProgress: 30%\nLocation: Nationwide\n\nPlease review this title deed.', 1, '2026-05-24 20:16:29', 'read', 0, 0, NULL, '2026-05-24 17:16:18', 0, 0),
(515, 4, 1, 13, 0, 0, '📋 PROJECT FROM MANAGER:\nTitle: Data Center Expansion\nClient: Telecom Company\nAmount: TZS 450,000,000\nStatus: completed\nProgress: 100%', 1, '2026-05-24 20:18:51', 'read', 0, 0, NULL, '2026-05-24 17:18:43', 0, 0),
(516, 4, 8, 130, 0, 0, '📋 PROJECT FROM MANAGER:\nTitle: Data Center Expansion\nClient: Telecom Company\nAmount: TZS 450,000,000\nStatus: completed\nProgress: 100%', 1, '2026-05-24 20:25:05', 'read', 0, 0, NULL, '2026-05-24 17:24:53', 0, 0),
(517, 8, 4, 130, 0, 0, '📋 PROJECT FROM TOWN PLANNING:\n\nProject: Coastal Zone Management Plan\nClient: Environmental Agency\nAmount: TZS 95,000,000\nStatus: in_progress\nProgress: 45%\nLocation: Coast Region\n\nPlease review this project.', 1, '2026-05-24 20:25:25', 'sent', 0, 0, NULL, '2026-05-24 17:25:17', 0, 0),
(518, 10, 1, 42, 0, 0, '📋 PROJECT FROM SURVEY:\n\nProject: GIS Mapping Project\nClient: Municipal Council\nAmount: TZS 95,000,000\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this project.', 1, '2026-05-24 20:47:27', 'sent', 0, 0, NULL, '2026-05-24 17:47:13', 0, 0),
(519, 10, 4, 131, 0, 0, '📋 PROJECT FROM SURVEY:\n\nProject: GIS Mapping Project\nClient: Municipal Council\nAmount: TZS 95,000,000\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this project.', 1, '2026-05-24 20:54:21', 'sent', 0, 0, NULL, '2026-05-24 17:54:12', 0, 0),
(520, 7, 9, 63, 0, 0, '📋 **PROJECT SENT**\n\n**Project:** Window Frame Supply - Housing Estate\n**Client:** Real Estate Developer\n**Amount:** 180,000,000 TZS\n**Status:** completed\n**Progress:** 100%\n\nThe full project details have been shared with your department. Please check the Projects section.', 1, '2026-05-24 21:04:39', 'sent', 0, 0, NULL, '2026-05-24 18:04:26', 0, 0),
(521, 7, 9, 63, 0, 0, '📋 **PROJECT SENT**\n\n**Project:** Aluminium Extrusion Line\n**Client:** Internal\n**Amount:** 200,000,000 TZS\n**Status:** in_progress\n**Progress:** 55%\n\nThe full project details have been shared with your department. Please check the Projects section.', 1, '2026-05-24 21:05:58', 'sent', 0, 0, NULL, '2026-05-24 18:05:02', 0, 0),
(522, 9, 7, 63, 0, 0, '📋 **PROJECT SENT**\n\n**Project:** Luxury Apartment Complex\n**Client:** Real Estate Developer\n**Amount:** 450,000,000 TZS\n**Status:** in_progress\n**Progress:** 70%\n\nThe full project details have been shared with your department. Please check the Projects section.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 18:05:30', 0, 0),
(523, 9, 11, 96, 0, 0, '📋 **PROJECT SENT**\n\n**Project:** Aluminium Extrusion Line\n**Client:** Internal\n**Amount:** 200,000,000 TZS\n**Status:** \n**Progress:** 55%\n\nThe full project details have been shared with your department. Please check the Projects section.', 1, '2026-05-24 21:09:01', 'sent', 0, 0, NULL, '2026-05-24 18:08:51', 1, 0),
(524, 9, 1, 41, 0, 0, '📋 **PROJECT SENT**\n\n**Project:** Aluminium Extrusion Line\n**Client:** Internal\n**Amount:** 200,000,000 TZS\n**Status:** \n**Progress:** 55%\n\nThe full project details have been shared with your department. Please check the Projects section.', 1, '2026-05-24 21:10:28', 'sent', 0, 0, NULL, '2026-05-24 18:10:19', 1, 0),
(525, 11, 1, 43, 0, 0, '📋 PROJECT FROM CONSTRUCTION:\nProject: Bridge Construction - Kigamboni Phase 2\nClient: TANROADS\nAmount: TZS 500,000,000\nStatus: in_progress\nProgress: 25%', 1, '2026-05-24 21:13:20', 'read', 0, 0, NULL, '2026-05-24 18:12:55', 0, 0),
(526, 12, 1, 5, 0, 0, '📋 TITLE DEED FROM HATIMILIKI:\n\nReference: Title Deed Digitization\nOwner: Internal\nAmount: TZS 120,000,000\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this title deed.', 1, '2026-05-24 21:15:47', 'read', 0, 0, NULL, '2026-05-24 18:15:30', 0, 0),
(527, 8, 1, 40, 0, 0, '📋 PROJECT FROM TOWN PLANNING:\n\nProject: Data Center Expansion\nClient: Telecom Company\nAmount: TZS 450,000,000\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this project.', 1, '2026-05-24 21:17:23', 'read', 0, 0, NULL, '2026-05-24 18:16:05', 0, 0),
(528, 11, 1, 43, 0, 0, '📋 PROJECT FROM CONSTRUCTION:\nProject: Bridge Construction - Kigamboni Phase 2\nClient: TANROADS\nAmount: TZS 500,000,000\nStatus: in_progress\nProgress: 25%', 1, '2026-05-24 21:17:20', 'read', 0, 0, NULL, '2026-05-24 18:17:09', 0, 0),
(529, 11, 9, 96, 0, 0, '📋 PROJECT FROM CONSTRUCTION:\nProject: Bridge Construction - Kigamboni Phase 2\nClient: TANROADS\nAmount: TZS 500,000,000\nStatus: in_progress\nProgress: 25%', 1, '2026-05-24 21:18:09', 'sent', 0, 0, NULL, '2026-05-24 18:17:55', 0, 0),
(530, 11, 4, 132, 0, 0, '📋 PROJECT FROM CONSTRUCTION:\nProject: Bridge Construction - Kigamboni Phase 2\nClient: TANROADS\nAmount: TZS 500,000,000\nStatus: in_progress\nProgress: 25%', 1, '2026-05-24 21:20:00', 'sent', 0, 0, NULL, '2026-05-24 18:19:48', 0, 0),
(531, 11, 4, 132, 0, 0, '📋 PROJECT FROM CONSTRUCTION:\nProject: Bridge Construction - Kigamboni Phase 2\nClient: TANROADS\nAmount: TZS 500,000,000\nStatus: in_progress\nProgress: 25%', 1, '2026-05-24 21:20:17', 'sent', 0, 0, NULL, '2026-05-24 18:20:12', 0, 0),
(532, 4, 11, 132, 0, 0, 'sawa', 1, '2026-05-24 21:20:28', 'sent', 0, 0, NULL, '2026-05-24 18:20:23', 0, 0),
(533, 11, 4, 132, 0, 0, 'okay', 1, '2026-05-24 21:20:41', 'sent', 0, 0, NULL, '2026-05-24 18:20:33', 0, 0),
(534, 7, 1, 39, 0, 0, '📋 PROJECT FROM Aluminium Department:\n\nProject: Window Frame Supply - Housing Estate\nClient: Real Estate Developer\nAmount: TZS 180,000,000.00\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this project in the Projects section.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 22:23:40', 0, 0),
(535, 7, 9, 63, 0, 0, '📋 PROJECT FROM Aluminium Department:\n\nProject: Window Frame Supply - Housing Estate\nClient: Real Estate Developer\nAmount: TZS 180,000,000.00\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this project in the Projects section.', 1, '2026-05-25 01:24:18', 'sent', 0, 0, NULL, '2026-05-24 22:24:10', 0, 0),
(536, 7, 1, 39, 0, 0, '📊 REPORT FROM Aluminium Department:\n\nTitle: Aluminium Production Report - June\nPeriod: monthly\nContent: ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4%\n\n--- End of Report ---', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 22:24:38', 0, 0),
(537, 9, 1, 83, 0, 0, '📋 PROJECT FROM Architectural Department:\n\nProject: Window Frame Supply - Housing Estate\nClient: Real Estate Developer\nAmount: TZS 180,000,000.00\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this project in the Projects section.', 1, '2026-05-25 02:51:47', 'read', 0, 0, NULL, '2026-05-24 22:30:10', 0, 0),
(538, 9, 7, 63, 0, 0, '📋 PROJECT FROM Architectural Department:\n\nProject: Window Frame Supply - Housing Estate\nClient: Real Estate Developer\nAmount: TZS 180,000,000.00\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this project in the Projects section.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 22:30:19', 0, 0),
(539, 9, 7, 63, 0, 0, '📋 PROJECT FROM Architectural Department:\n\nProject: Aluminium Extrusion Line\nClient: Internal\nAmount: TZS 200,000,000.00\nStatus: \nProgress: 55%\nLocation: Pwani Region\n\nPlease review this project in the Projects section.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 22:30:34', 0, 0),
(540, 9, 7, 63, 0, 0, '📊 REPORT FROM Architectural Department:\n\nTitle: Weekly Design Team Update - Week 26\nPeriod: weekly\nContent: ✏️ ARCHITECTURAL DESIGN TEAM UPDATE - WEEK 26\r\n\r\nTeam Productivity:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hours Worked: 480 hours\r\n• Overtime: 45 hours\r\n• Designs Completed: 3 full designs\r\n• Drawings Produced: 85 sheets\r\n\r\nProject Deadlines:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Modern Villa Kigamboni - On track\r\n✓ Comm\n\n--- End of Report ---', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 22:30:51', 0, 0),
(541, 7, 9, 63, 0, 0, '📊 REPORT FROM Aluminium Department:\n\nTitle: Aluminium Production Report - June\nPeriod: monthly\nContent: ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4%\n\n--- End of Report ---', 1, '2026-05-25 01:34:29', 'sent', 0, 0, NULL, '2026-05-24 22:31:08', 0, 0),
(542, 7, 9, 63, 0, 0, '📋 PROJECT FROM Aluminium Department:\n\nProject: Window Frame Supply - Housing Estate\nClient: Real Estate Developer\nAmount: TZS 180,000,000.00\nStatus: completed\nProgress: 100%\nLocation: Dar es Salaam\n\nPlease review this project in the Projects section.', 1, '2026-05-25 01:34:29', 'sent', 0, 0, NULL, '2026-05-24 22:31:31', 0, 0),
(543, 7, 9, 63, 0, 0, '📊 REPORT FROM Aluminium Department:\n\nTitle: Weekly Production Summary - Week 26\nPeriod: weekly\nContent: 📊 WEEKLY ALUMINIUM PRODUCTION SUMMARY (Week 26)\r\n\r\nDaily Production:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: 620 units\r\n• Tuesday: 680 units\r\n• Wednesday: 710 units\r\n• Thursday: 690 units\r\n• Friday: 650 units\r\n• Saturday: 450 units\r\n\r\nTotal Weekly: 3,800 units\r\n\r\nOrders Processed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\n--- End of Report ---', 1, '2026-05-25 01:35:01', 'sent', 0, 0, NULL, '2026-05-24 22:34:48', 0, 0),
(544, 9, 7, 63, 0, 0, '📊 REPORT FROM Architectural Department:\n\nTitle: Weekly Production Summary - Week 26\nPeriod: weekly\nContent: 📊 WEEKLY ALUMINIUM PRODUCTION SUMMARY (Week 26)\r\n\r\nDaily Production:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: 620 units\r\n• Tuesday: 680 units\r\n• Wednesday: 710 units\r\n• Thursday: 690 units\r\n• Friday: 650 units\r\n• Saturday: 450 units\r\n\r\nTotal Weekly: 3,800 units\r\n\r\nOrders Processed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\n--- End of Report ---', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 22:35:21', 0, 0),
(545, 7, 6, 133, 0, 0, '📊 REPORT FROM Aluminium Department:\n\nTitle: Aluminium Production Report - June\nPeriod: monthly\nContent: ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4%\n\n--- End of Report ---', 1, '2026-05-25 01:36:05', 'read', 0, 0, NULL, '2026-05-24 22:35:54', 0, 0),
(546, 9, 7, 63, 0, 0, '📊 REPORT FROM ARCHITECTURAL:\n\nTitle: Weekly Production Summary - Week 26\nPeriod: weekly\n\n📊 WEEKLY ALUMINIUM PRODUCTION SUMMARY (Week 26)\r\n\r\nDaily Production:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: 620 units\r\n• Tuesday: 680 units\r\n• Wednesday: 710 units\r\n• Thursday: 690 units\r\n• Friday: 650 units\r\n• Saturday: 450 units\r\n\r\nTotal Weekly: 3,800 units\r\n\r\nOrders Processed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Completed: 8 orders\r\n• Shipped: 7 orders\r\n• In Queue: 5 orders\r\n\r\nQuality Check Results:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Passed: 3,650 units (96.1%)\r\n• ', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 22:39:24', 0, 0),
(547, 7, 9, 63, 0, 0, '📊 NEW REPORT: Aluminium Production Report - June 2024 (monthly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 01:40:20', 'sent', 0, 0, NULL, '2026-05-24 22:39:41', 0, 0),
(548, 7, 9, 63, 0, 0, '📊 NEW REPORT: Aluminium Inventory Status (weekly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 01:40:20', 'sent', 0, 0, NULL, '2026-05-24 22:40:12', 0, 0),
(549, 9, 6, 99, 0, 0, '📊 NEW REPORT: Aluminium Inventory Status (weekly)\n\nFrom: Architectural Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 01:54:32', 'read', 0, 0, NULL, '2026-05-24 22:49:38', 0, 0),
(550, 9, 6, 99, 0, 0, '📊 REPORT FROM ARCHITECTURAL:\n\nTitle: Aluminium Inventory Status\nPeriod: weekly\n\nALUMINIUM INVENTORY - WEEK 26, 2024\n\nCurrent Stock:\n- Raw Aluminium Sheets: 8,500 kg\n- Glass Panels: 1,200 pcs\n- Hardware Accessories: 2,500 sets\n- Powder Coating: 500 kg\n\nMinimum Stock Levels:\n- Aluminium sheets: 5,000 kg (Current: 8,500 kg - OK)\n- Glass panels: 800 pcs (Current: 1,200 pcs - OK)\n- Hardware: 1,500 sets (Current: 2,500 sets - OK)\n\nProducts Ready for Dispatch:\n- Window frames: 450 units\n- Door frames: 200 units\n- Custom orders: 25 units\n\nPending Orders: 40 (Value: TZS 12M)\nProduct', 1, '2026-05-25 01:56:22', 'read', 0, 0, NULL, '2026-05-24 22:56:08', 0, 0),
(551, 11, 7, 19, 0, 0, '📊 NEW REPORT: Weekly Construction Progress - Week 26 (weekly)\n\nFrom: Construction Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 01:57:04', 'sent', 0, 0, NULL, '2026-05-24 22:56:52', 0, 0),
(552, 7, 11, 19, 0, 0, '📊 NEW REPORT: Aluminium Production Report - June (monthly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 02:04:27', 'sent', 0, 0, NULL, '2026-05-24 22:57:18', 1, 0),
(553, 7, 11, 19, 0, 0, '📋 NEW PROJECT: Window Frame Supply - Housing Estate\n\nFrom: Aluminium Department\nClient: Real Estate Developer\nAmount: TZS 180,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:04:27', 'sent', 0, 0, NULL, '2026-05-24 22:58:22', 1, 0),
(554, 7, 11, 19, 0, 0, '📋 NEW PROJECT: Aluminium Curtain Wall System\n\nFrom: Aluminium Department\nClient: Highrise Construction\nAmount: TZS 350,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:04:27', 'sent', 0, 0, NULL, '2026-05-24 22:58:50', 1, 0),
(555, 11, 7, 19, 0, 0, '📋 PROJECT FROM CONSTRUCTION:\nProject: Bridge Construction - Kigamboni Phase 2\nClient: TANROADS\nAmount: TZS 500,000,000\nStatus: in_progress\nProgress: 25%', 1, '2026-05-25 01:59:41', 'sent', 0, 0, NULL, '2026-05-24 22:59:11', 0, 0),
(556, 11, 9, 96, 0, 0, '📋 PROJECT FROM CONSTRUCTION:\nProject: Bridge Construction - Kigamboni Phase 2\nClient: TANROADS\nAmount: TZS 500,000,000\nStatus: in_progress\nProgress: 25%', 1, '2026-05-25 02:00:19', 'sent', 0, 0, NULL, '2026-05-24 23:00:11', 0, 0),
(557, 9, 7, 63, 0, 0, '📋 PROJECT FROM ARCHITECTURAL:\n\nProject: Window Frame Supply - Housing Estate\nClient: Real Estate Developer\nAmount: TZS 180,000,000\nStatus: completed\nProgress: 100%', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 23:00:32', 0, 0),
(558, 9, 7, 63, 0, 0, '📋 NEW PROJECT: Luxury Apartment Complex\n\nFrom: Architectural Department\nClient: Real Estate Developer\nAmount: TZS 450,000,000.00\nPlease check the Projects section to view full details.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 23:01:02', 0, 0),
(559, 11, 7, 19, 0, 0, '📋 PROJECT FROM CONSTRUCTION:\nProject: Hospital Construction - Kigamboni\nClient: Ministry of Health\nAmount: TZS 400,000,000\nStatus: completed\nProgress: 100%', 1, '2026-05-25 02:01:25', 'sent', 0, 0, NULL, '2026-05-24 23:01:15', 0, 0),
(560, 7, 11, 19, 0, 0, '📋 NEW PROJECT: Custom Aluminium Fabrication\n\nFrom: Aluminium Department\nClient: Luxury Homes\nAmount: TZS 95,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-06-02 14:37:52', 'sent', 0, 0, NULL, '2026-05-24 23:04:46', 1, 0),
(561, 11, 7, 19, 0, 0, '📋 NEW PROJECT: Shopping Mall Construction\n\nFrom: Construction Department\nClient: Retail Group\nAmount: TZS 650,000,000.00\nPlease check the Projects section to view full details.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-24 23:05:00', 0, 0),
(562, 2, 7, 48, 0, 0, '📊 REPORT FROM FINANCE DEPARTMENT:\n\nTitle: Budget vs Actual Analysis\nPeriod: quarterly\n\nQ2 2024 Budget vs Actual Analysis\n\nBudget: TZS 500 Million\nActual: TZS 485 Million\nVariance: -3% (Favorable)\n\nDepartment Analysis:\n- Construction: Under budget by 5%\n- Aluminium: Over budget by 2%\n- Sales: Under budget by 8%\n- Admin: On budget\n\nMajor Variances:\n- Materials cost lower than expected: -TZS 15M\n- Labor cost higher: +TZS 8M\n- Marketing savings: -TZS 5M\n\nRecommendations:\n- Continue cost control measures\n- Investigate Aluminium overspend\n- Reallocate savings to R&D\n\n---\nGenerated from Finance Department Dashboard', 1, '2026-05-25 02:06:02', 'sent', 0, 0, NULL, '2026-05-24 23:05:52', 0, 0),
(563, 7, 2, 48, 0, 0, '📊 NEW REPORT: Weekly Design Team Update - Week 26 (weekly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 02:07:28', 'sent', 0, 0, NULL, '2026-05-24 23:06:22', 0, 0),
(564, 7, 2, 48, 0, 0, '📊 NEW REPORT: Weekly Production Summary - Week 26 (weekly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 09:07:40', 'sent', 0, 0, NULL, '2026-05-24 23:17:48', 0, 0),
(565, 7, 12, 134, 0, 0, '📋 NEW PROJECT: Shopping Mall Construction\n\nFrom: Aluminium Department\nClient: Retail Group\nAmount: TZS 650,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:22:06', 'read', 0, 0, NULL, '2026-05-24 23:18:52', 0, 0),
(566, 12, 7, 134, 0, 0, '📋 TITLE DEED FROM HATIMILIKI:\n\nReference: Digital Title Deed System\nOwner: Ministry of Lands\nAmount: TZS 300,000,000\nStatus: in_progress\nProgress: 55%\nLocation: Dar es Salaam\n\nPlease review this title deed.', 1, '2026-05-25 02:19:39', 'sent', 0, 0, NULL, '2026-05-24 23:19:21', 0, 0),
(567, 7, 12, 134, 0, 0, '📊 NEW REPORT: Aluminium Production Report - June (monthly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 02:22:06', 'read', 0, 0, NULL, '2026-05-24 23:21:54', 0, 0),
(568, 12, 7, 134, 0, 0, '📋 NEW PROJECT: Title Deed Digitization\n\nFrom: Hatimiliki Department\nClient: Internal\nAmount: TZS 120,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:28:29', 'sent', 0, 0, NULL, '2026-05-24 23:22:17', 0, 0),
(569, 7, 12, 134, 0, 0, '📋 NEW PROJECT: Aluminium Extrusion Line\n\nFrom: Aluminium Department\nClient: Internal\nAmount: TZS 200,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:27:01', 'read', 0, 0, NULL, '2026-05-24 23:26:38', 0, 0),
(570, 7, 4, 135, 0, 0, '📋 NEW PROJECT: Luxury Apartment Complex\n\nFrom: Aluminium Department\nClient: Real Estate Developer\nAmount: TZS 450,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:27:43', 'sent', 0, 0, NULL, '2026-05-24 23:27:34', 0, 0),
(571, 7, 4, 135, 0, 0, '📋 NEW PROJECT: Custom Aluminium Fabrication\n\nFrom: Aluminium Department\nClient: Luxury Homes\nAmount: TZS 95,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:32:35', 'sent', 0, 0, NULL, '2026-05-24 23:28:00', 0, 0),
(572, 4, 7, 135, 0, 0, '📋 PROJECT FROM MANAGER:\nTitle: Operations Excellence Program\nClient: Internal\nAmount: TZS 80,000,000\nStatus: in_progress\nProgress: 65%', 1, '2026-05-25 02:28:27', 'sent', 0, 0, NULL, '2026-05-24 23:28:12', 0, 0),
(573, 4, 7, 135, 0, 0, '📋 PROJECT FROM MANAGER:\nTitle: Operations Excellence Program\nClient: Internal\nAmount: TZS 80,000,000\nStatus: in_progress\nProgress: 65%', 1, '2026-05-25 02:31:48', 'sent', 0, 0, NULL, '2026-05-24 23:31:41', 0, 0),
(574, 4, 7, 135, 0, 0, '📋 NEW PROJECT: Custom Aluminium Fabrication\n\nFrom: Manager Department\nClient: Luxury Homes\nAmount: TZS 95,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:34:24', 'sent', 0, 0, NULL, '2026-05-24 23:32:58', 0, 0),
(575, 4, 7, 135, 0, 0, '📋 PROJECT FROM MANAGER:\n\nProject: Custom Aluminium Fabrication\nClient: Luxury Homes\nAmount: TZS 95,000,000\nStatus: pending\nProgress: 15%', 1, '2026-05-25 02:34:24', 'sent', 0, 0, NULL, '2026-05-24 23:34:12', 0, 0),
(576, 4, 7, 135, 0, 0, '📋 NEW PROJECT: Operations Excellence Program\n\nFrom: Manager Department\nClient: Internal\nAmount: TZS 80,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:36:36', 'sent', 0, 0, NULL, '2026-05-24 23:36:28', 0, 0),
(577, 7, 4, 135, 0, 0, '📋 NEW PROJECT: Aluminium Curtain Wall System\n\nFrom: Aluminium Department\nClient: Highrise Construction\nAmount: TZS 350,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:37:46', 'sent', 0, 0, NULL, '2026-05-24 23:36:56', 0, 0),
(578, 7, 4, 135, 0, 0, '📊 NEW REPORT: Aluminium Production Report - June (monthly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 02:37:46', 'sent', 0, 0, NULL, '2026-05-24 23:37:09', 0, 0),
(579, 4, 7, 135, 0, 0, '📊 NEW REPORT: Risk Assessment Report (monthly)\n\nFrom: Manager Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 02:37:31', 'sent', 0, 0, NULL, '2026-05-24 23:37:22', 0, 0),
(580, 3, 7, 3, 0, 0, '📊 REPORT FROM SALES & MARKETING:\n\nTitle: Sales Performance Report - June\nPeriod: monthly\n\nSALES PERFORMANCE REPORT - JUNE 2024\n\nTotal Sales: TZS 250,000,000\nTarget: TZS 230,000,000\nAchievement: 108% (EXCEEDED)\n\nSales by Category:\n- Construction Services: TZS 120M (48%)\n- Aluminium Products: TZS 65M (26%)\n- Bricks & Timber: TZS 40M (16%)\n- Consulting: TZS 25M (10%)\n\nTop Sales Rep:\n1. John Mwita: TZS 85M\n2. Sarah Kijazi: TZS 70M\n3. James Ndege: TZS 55M\n\nNew Clients Acquired: 12\nLead Conversion Rate: 32%\n\nForecast for July: TZS 260M (target: TZS 240M)', 1, '2026-05-25 02:38:02', 'sent', 0, 0, NULL, '2026-05-24 23:37:58', 0, 0),
(581, 7, 3, 3, 0, 0, '📊 NEW REPORT: Aluminium Inventory Status (weekly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 02:41:35', 'read', 0, 0, NULL, '2026-05-24 23:38:12', 0, 0),
(582, 3, 7, 3, 0, 0, '📊 REPORT FROM SALES & MARKETING:\n\nTitle: Sales Performance Report - June\nPeriod: monthly\n\nSALES PERFORMANCE REPORT - JUNE 2024\n\nTotal Sales: TZS 250,000,000\nTarget: TZS 230,000,000\nAchievement: 108% (EXCEEDED)\n\nSales by Category:\n- Construction Services: TZS 120M (48%)\n- Aluminium Products: TZS 65M (26%)\n- Bricks & Timber: TZS 40M (16%)\n- Consulting: TZS 25M (10%)\n\nTop Sales Rep:\n1. John Mwita: TZS 85M\n2. Sarah Kijazi: TZS 70M\n3. James Ndege: TZS 55M\n\nNew Clients Acquired: 12\nLead Conversion Rate: 32%\n\nForecast for July: TZS 260M (target: TZS 240M)', 1, '2026-05-25 02:41:19', 'sent', 0, 0, NULL, '2026-05-24 23:41:07', 0, 0),
(583, 3, 7, 3, 0, 0, '📊 NEW REPORT: Customer Satisfaction Survey (monthly)\n\nFrom: Sales & Marketing\nPlease check the Reports section to view the full report.', 1, '2026-05-25 02:47:31', 'sent', 0, 0, NULL, '2026-05-24 23:41:47', 0, 0),
(584, 5, 7, 136, 0, 0, '📊 REPORT FROM SECRETARY OFFICE:\n\nTitle: Monthly Visitor Statistics - June 2024\nPeriod: monthly\n\nVISITOR STATISTICS - JUNE 2024\n\nTotal Visitors: 245\nNew Visitors: 178\nReturning Visitors: 67\n\nVisitors by Department:\n- Manager: 52 (21%)\n- Finance: 45 (18%)\n- Construction: 38 (16%)\n- Sales: 35 (14%)\n- Aluminium: 28 (11%)\n- Other Departments: 47 (20%)\n\nPeak Days: Monday (65), Tuesday (58)\nPeak Hours: 10 AM - 12 PM (85 visitors)\n\nAverage Wait Time: 12 minutes\nVisitor Satisfaction: 92%\n\nAppointments Scheduled: 180\nNo-shows: 15 (8%)\n\nIssues Resolved: 230\nPending Issues: 15\n\nRecommendations:\n- Add', 1, '2026-05-25 02:47:28', 'sent', 0, 0, NULL, '2026-05-24 23:45:39', 0, 0),
(585, 7, 5, 136, 0, 0, '📊 NEW REPORT: Weekly Design Team Update - Week 26 (weekly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 02:46:09', 'read', 0, 0, NULL, '2026-05-24 23:45:53', 0, 0),
(586, 7, 5, 136, 0, 0, '📊 NEW REPORT: Aluminium Production Report - June (monthly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 13:00:25', 'read', 0, 0, NULL, '2026-05-24 23:46:27', 0, 0),
(587, 1, 7, 39, 0, 0, '📋 NEW PROJECT: Window Frame Supply - Housing Estate\n\nFrom: Super Admin\nClient: Real Estate Developer\nAmount: TZS 180,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:47:26', 'sent', 0, 0, NULL, '2026-05-24 23:47:10', 0, 0),
(588, 1, 7, 39, 0, 0, '📊 NEW REPORT: Aluminium Production Report - June (monthly)\n\nFrom: Super Admin\nPlease check the Reports section to view the full report.', 1, '2026-05-25 02:47:26', 'sent', 0, 0, NULL, '2026-05-24 23:47:20', 0, 0),
(589, 10, 8, 65, 0, 0, '📋 NEW PROJECT: GIS Mapping Project\n\nFrom: Survey Department\nClient: Municipal Council\nAmount: TZS 95,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 12:48:27', 'read', 0, 0, NULL, '2026-05-24 23:52:10', 0, 0),
(590, 8, 10, 65, 0, 0, '📋 NEW PROJECT: Data Center Expansion\n\nFrom: Town Planning\nClient: Telecom Company\nAmount: TZS 450,000,000.00\nPlease check the Projects section to view full details.', 1, '2026-05-25 02:52:51', 'read', 0, 0, NULL, '2026-05-24 23:52:33', 0, 0),
(591, 10, 8, 65, 0, 0, '📊 NEW REPORT: Equipment Maintenance & Calibration Report (monthly)\n\nFrom: Survey Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 12:48:27', 'read', 0, 0, NULL, '2026-05-24 23:53:05', 0, 0),
(592, 8, 10, 65, 0, 0, '📊 NEW REPORT: Town Planning Applications Report - June (monthly)\n\nFrom: Town Planning\nPlease check the Reports section to view the full report.', 1, '2026-05-25 02:53:28', 'read', 0, 0, NULL, '2026-05-24 23:53:18', 0, 0),
(593, 6, 7, 133, 0, 0, '📊 NEW REPORT: Aluminium Inventory Status (weekly)\n\nFrom: Bricks & Timber\nPlease check the Reports section to view the full report.', 1, '2026-05-25 03:03:22', 'sent', 0, 0, NULL, '2026-05-25 00:03:10', 0, 0);
INSERT INTO `messages` (`id`, `sender_dept`, `receiver_dept`, `conversation_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `read_at`, `status`, `sender_deleted`, `receiver_deleted`, `deleted_at`, `created_at`, `deleted_by_sender`, `deleted_by_receiver`) VALUES
(594, 7, 6, 133, 0, 0, '📊 NEW REPORT: Weekly Production Summary - Week 26 (weekly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-25 00:03:36', 0, 0),
(595, 7, 6, 133, 0, 0, '📊 NEW REPORT: Aluminium Production Report - June 2024 (monthly)\n\nFrom: Aluminium Department\nPlease check the Reports section to view the full report.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-25 00:03:55', 0, 0),
(596, 2, 1, 34, 0, 0, '📊 REPORT FROM FINANCE DEPARTMENT:\n\nTitle: KIWANJA MJI MPYA\nPeriod: weekly\n\nIMEANDALIWA NA MIMI FINANCE\n\n---\nGenerated from Finance Department Dashboard', 1, '2026-05-25 09:11:12', 'read', 0, 0, NULL, '2026-05-25 06:10:56', 0, 0),
(597, 2, 3, 45, 0, 0, '📊 REPORT FROM FINANCE DEPARTMENT:\n\nTitle: KIWANJA MJI MPYA\nPeriod: weekly\n\nIMEANDALIWA NA MIMI FINANCE\n\n---\nGenerated from Finance Department Dashboard', 1, '2026-05-25 09:11:50', 'read', 0, 0, NULL, '2026-05-25 06:11:40', 0, 0),
(598, 3, 1, 1, 0, 0, '📊 NEW REPORT: Sales Performance Report - June (monthly)\n\nFrom: Sales & Marketing\nPlease check the Reports section to view the full report.', 1, '2026-05-25 09:12:13', 'read', 0, 0, NULL, '2026-05-25 06:12:00', 0, 0),
(599, 3, 1, 1, 0, 0, '📊 NEW REPORT: Customer Satisfaction Survey (monthly)\n\nFrom: Sales & Marketing\nPlease check the Reports section to view the full report.', 1, '2026-05-25 09:14:19', 'read', 0, 0, NULL, '2026-05-25 06:12:28', 0, 0),
(600, 2, 1, 34, 0, 0, '📊 NEW REPORT: KIWANJA MJI MPYA (weekly)\n\nFrom: Finance Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 09:18:06', 'read', 0, 0, NULL, '2026-05-25 06:17:54', 0, 0),
(601, 2, 1, 34, 0, 0, 'hii', 1, '2026-05-25 12:07:16', 'read', 0, 0, NULL, '2026-05-25 09:06:53', 0, 0),
(602, 2, 1, 34, 0, 0, '📊 NEW REPORT: June 2024 Financial Statement (monthly)\n\nFrom: Finance Department\nPlease check the Reports section to view the full report.', 1, '2026-05-25 12:07:54', 'read', 0, 0, NULL, '2026-05-25 09:07:34', 0, 0),
(603, 8, 1, 40, 0, 0, '📋 NEW PROJECT: Coastal Zone Management Plan\n\nFrom: Town Planning\nClient: Environmental Agency\nAmount: TZS 95,000,000.00\nPlease check the Projects section to view full details.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-25 09:44:21', 0, 0),
(604, 8, 2, 137, 0, 0, 'sawa', 0, NULL, 'sent', 0, 0, NULL, '2026-05-25 09:48:38', 0, 0),
(605, 8, 4, 130, 0, 0, '📊 NEW REPORT: Equipment Maintenance & Calibration Report (monthly)\n\nFrom: Town Planning\nPlease check the Reports section to view the full report.', 1, '2026-05-25 12:53:12', 'sent', 0, 0, NULL, '2026-05-25 09:49:45', 0, 0),
(606, 4, 6, 56, 0, 0, '📊 NEW REPORT: Equipment Maintenance & Calibration Report (monthly)\n\nFrom: Manager Department\nPlease check the Reports section to view the full report.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-25 09:53:09', 0, 0),
(607, 5, 3, 129, 0, 0, 'mm', 1, '2026-05-25 13:05:32', 'read', 0, 0, NULL, '2026-05-25 10:00:54', 0, 0),
(608, 3, 5, 129, 0, 0, 'sawa', 1, '2026-05-25 13:05:52', 'read', 0, 0, NULL, '2026-05-25 10:05:38', 0, 0),
(609, 10, 1, 42, 0, 0, '📊 NEW REPORT: john (weekly)\n\nFrom: Survey Department\nPlease check the Reports section to view the full report.', 0, NULL, 'sent', 0, 0, NULL, '2026-05-25 10:12:51', 0, 0),
(610, 7, 1, 39, 0, 0, '📋 NEW PROJECT: Operations Excellence Program\n\nFrom: Aluminium Department\nClient: Internal\nAmount: TZS 80,000,000.00\nPlease check the Projects section to view full details.', 0, NULL, 'sent', 0, 0, NULL, '2026-06-05 13:02:20', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(1, 'jacksonmyula773@gmail.com', 'rXO-OkM49bEgDWM-UXI7ajCBOwpCcpmIojN80wOEoCY', '2026-05-25 10:34:56', '2026-05-25 07:34:56');

-- --------------------------------------------------------

--
-- Table structure for table `planning_applications`
--

CREATE TABLE `planning_applications` (
  `id` int(11) NOT NULL,
  `application_no` varchar(100) NOT NULL,
  `applicant_name` varchar(200) NOT NULL,
  `applicant_phone` varchar(50) DEFAULT NULL,
  `property_location` varchar(200) DEFAULT NULL,
  `application_type` enum('building_permit','zoning_change','subdivision','development','land_use') DEFAULT 'building_permit',
  `description` text DEFAULT NULL,
  `application_date` date DEFAULT NULL,
  `decision_date` date DEFAULT NULL,
  `decision_notes` text DEFAULT NULL,
  `status` enum('pending','under_review','approved','rejected','completed') DEFAULT 'pending',
  `department_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_by_department` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_user_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `planning_applications`
--

INSERT INTO `planning_applications` (`id`, `application_no`, `applicant_name`, `applicant_phone`, `property_location`, `application_type`, `description`, `application_date`, `decision_date`, `decision_notes`, `status`, `department_id`, `created_by`, `created_at`, `updated_at`, `deleted_by_department`, `deleted_by_admin`, `deleted_by_user_id`, `deleted_at`) VALUES
(1, 'TP/2024/001', 'John Mwita', NULL, 'Kigamboni, Dar es Salaam', 'building_permit', NULL, '2024-05-01', NULL, NULL, 'under_review', 8, 14, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(2, 'TP/2024/002', 'Sarah Kijazi', NULL, 'Mbezi Beach', 'subdivision', NULL, '2024-05-10', NULL, NULL, 'approved', 8, 14, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(3, 'TP/2024/003', 'TANROADS', NULL, 'Pwani Region', 'development', NULL, '2024-05-15', NULL, NULL, 'pending', 8, 14, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `client_name` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','approved') DEFAULT 'pending',
  `progress` int(11) DEFAULT 0,
  `location` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_viewed_by_admin` tinyint(1) DEFAULT 0,
  `is_viewed_by_department` tinyint(4) DEFAULT 0,
  `sent_from_dept` int(11) DEFAULT NULL,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_department` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_receiver` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `sent_to_department` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `client_name`, `amount`, `status`, `progress`, `location`, `description`, `image`, `start_date`, `end_date`, `department_id`, `created_by`, `created_at`, `is_viewed_by_admin`, `is_viewed_by_department`, `sent_from_dept`, `deleted_by_admin`, `deleted_by_department`, `deleted_by_receiver`, `deleted_at`, `sent_to_department`, `is_deleted`) VALUES
(1, 'GeoTraverse HQ Construction', 'GeoTraverse Company', 850000000.00, 'in_progress', 45, 'Dar es Salaam CBD', 'Main headquarters building construction - 12 floors', NULL, '2024-06-01', '2025-06-30', 1, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(2, 'National IT Infrastructure', 'Ministry of ICT', 1200000000.00, 'in_progress', 10, 'Dodoma', 'National IT infrastructure upgrade project', NULL, '2024-07-01', '2025-12-31', 1, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(3, 'Corporate Training Center', 'Private Sector', 250000000.00, 'in_progress', 30, 'Arusha', 'Corporate training facility construction', NULL, '2024-05-15', '2025-03-31', 1, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(4, 'Data Center Expansion', 'Telecom Company', 450000000.00, 'approved', 100, 'Dar es Salaam', 'Data center expansion and upgrade', 'project_1779626800_8176.png', '2024-01-10', '2024-12-20', 1, NULL, '2026-05-22 21:09:31', 0, 1, 1, 0, 0, 0, NULL, 8, 0),
(5, 'Operations Excellence Program', 'Internal', 80000000.00, 'in_progress', 65, 'Dar es Salaam', 'Operational efficiency improvement program', NULL, '2024-03-01', '2024-08-31', 4, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(6, 'Strategic Planning 2025', 'Board of Directors', 50000000.00, 'pending', 20, 'Dar es Salaam', '5-year strategic planning initiative', NULL, '2024-06-01', '2024-11-30', 4, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(7, 'Project Management Office', 'Internal', 60000000.00, 'completed', 100, 'Dar es Salaam', 'Establishment of PMO department', NULL, '2024-01-01', '2024-05-31', 4, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(8, 'Risk Management Framework', 'Internal', 40000000.00, 'in_progress', 45, 'Dar es Salaam', 'Enterprise risk management implementation', 'project_1779628011_5236.png', '2024-04-15', '2024-09-15', 4, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(9, 'Aluminium Curtain Wall System', 'Highrise Construction', 350000000.00, 'in_progress', 40, 'Dar es Salaam', 'Curtain wall installation for 20-story building', NULL, '2024-04-01', '2024-11-30', 7, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(10, 'Custom Aluminium Fabrication', 'Luxury Homes', 95000000.00, 'pending', 15, 'Dar es Salaam', 'Custom aluminium doors and windows', NULL, '2024-06-01', '2024-09-30', 7, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(11, 'Aluminium Extrusion Line', 'Internal', 200000000.00, 'in_progress', 55, 'Pwani Region', 'New aluminium extrusion production line', NULL, '2024-02-01', '2024-10-31', 7, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(12, 'Window Frame Supply - Housing Estate', 'Real Estate Developer', 180000000.00, 'completed', 100, 'Dar es Salaam', 'Supply of 5000 window frames', NULL, '2024-01-10', '2024-06-30', 7, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(13, 'Dar es Salaam Master Plan', 'Dar City Council', 350000000.00, 'in_progress', 65, 'Dar es Salaam', 'Comprehensive city master plan', NULL, '2024-01-01', '2024-12-31', 8, NULL, '2026-05-22 21:09:31', 0, 1, NULL, 0, 0, 0, NULL, NULL, 0),
(14, 'Zoning Regulation Revision', 'Ministry of Lands', 120000000.00, 'pending', 20, 'Dodoma', 'National zoning regulation update', NULL, '2024-05-01', '2024-10-31', 8, NULL, '2026-05-22 21:09:31', 0, 1, NULL, 0, 0, 0, NULL, NULL, 0),
(15, 'Coastal Zone Management Plan', 'Environmental Agency', 95000000.00, 'in_progress', 45, 'Coast Region', 'Coastal area development plan', 'project_1779702225_3341.png', '2024-03-15', '2024-09-30', 8, NULL, '2026-05-22 21:09:31', 0, 1, NULL, 0, 0, 0, NULL, NULL, 0),
(16, 'Smart City Initiative', 'ICT Commission', 280000000.00, 'completed', 100, 'Dar es Salaam', 'Smart city pilot project', 'project_1779626238_8620.png', '2024-01-15', '2024-06-30', 8, NULL, '2026-05-22 21:09:31', 1, 1, NULL, 0, 0, 0, NULL, NULL, 0),
(17, 'Luxury Apartment Complex', 'Real Estate Developer', 450000000.00, 'in_progress', 70, 'Dar es Salaam', '20-storey luxury apartment building', NULL, '2024-02-01', '2024-12-31', 9, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(18, 'Hospital Design Project', 'Ministry of Health', 180000000.00, 'in_progress', 30, 'Dodoma', 'Regional hospital architectural design', NULL, '2024-05-01', '2024-11-30', 1, NULL, '2026-05-22 21:09:31', 0, 0, 1, 0, 0, 0, NULL, NULL, 0),
(19, 'University Campus Master Plan', 'University', 220000000.00, 'in_progress', 50, 'Morogoro', 'University campus architectural planning', NULL, '2024-03-01', '2024-10-31', 9, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(20, 'Eco-Friendly Housing Design', 'Green Building Council', 75000000.00, 'completed', 100, 'Arusha', 'Sustainable housing architectural designs', 'project_1779544319_5340.png', '2024-01-10', '2024-05-31', 1, NULL, '2026-05-22 21:09:31', 0, 0, 1, 0, 0, 0, NULL, 1, 0),
(21, 'National Land Survey', 'Ministry of Lands', 500000000.00, 'in_progress', 40, 'Nationwide', 'Nationwide land survey project', NULL, '2024-01-15', '2024-12-31', 10, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(22, 'Boundary Demarcation - Coast Region', 'TANROADS', 85000000.00, 'pending', 15, 'Coast Region', 'Road boundary demarcation project', NULL, '2024-06-01', '2024-09-30', 10, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(23, 'Topographic Survey - New City', 'Urban Development', 150000000.00, 'in_progress', 55, 'Pwani Region', 'Topographic survey for new city development', NULL, '2024-03-01', '2024-08-31', 10, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(24, 'GIS Mapping Project', 'Municipal Council', 95000000.00, 'completed', 100, 'Dar es Salaam', 'Digital GIS mapping of city', 'project_1779625645_4163.png', '2024-01-10', '2024-06-30', 10, NULL, '2026-05-22 21:09:31', 1, 1, NULL, 0, 0, 0, NULL, NULL, 0),
(25, 'International Airport Terminal', 'Airports Authority', 2500000000.00, 'in_progress', 35, 'Dar es Salaam', 'New international airport terminal construction', NULL, '2024-01-01', '2025-12-31', 11, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(26, 'Sports Stadium Renovation', 'Ministry of Sports', 350000000.00, 'pending', 20, 'Dar es Salaam', 'National stadium renovation project', NULL, '2024-06-01', '2025-03-31', 11, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(27, 'Shopping Mall Construction', 'Retail Group', 650000000.00, 'in_progress', 60, 'Arusha', 'Modern shopping mall construction', NULL, '2024-02-15', '2024-12-31', 11, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(28, 'Hospital Construction - Kigamboni', 'Ministry of Health', 400000000.00, 'completed', 100, 'Dar es Salaam', 'District hospital construction', NULL, '2024-01-01', '2024-06-30', 11, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(29, 'Bridge Construction - Kigamboni Phase 2', 'TANROADS', 500000000.00, 'in_progress', 25, 'Dar es Salaam', 'Second bridge connecting Kigamboni', NULL, '2024-04-01', '2025-06-30', 11, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(30, 'University Dormitory Construction', 'University', 180000000.00, 'pending', 10, 'Morogoro', 'Student dormitory complex', 'project_1779567810_2265.png', '2024-07-01', '2025-02-28', 11, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 1, 0, '2026-05-24 21:09:22', NULL, 0),
(31, 'Digital Title Deed System', 'Ministry of Lands', 300000000.00, 'in_progress', 55, 'Dar es Salaam', 'Blockchain-based digital title deed system', 'project_1780911059_6297.png', '2024-02-01', '2024-12-31', 1, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(32, 'Land Registry Modernization', 'World Bank', 450000000.00, 'pending', 15, 'Dodoma', 'Modernization of land registry', NULL, '2024-05-01', '2025-04-30', 1, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(33, 'Mass Land Titling Program', 'Ministry of Lands', 600000000.00, 'in_progress', 30, 'Nationwide', 'Mass land titling for rural areas', 'project_1780856091_2505.png', '2024-01-15', '2025-06-30', 1, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(34, 'Title Deed Digitization', 'Internal', 120000000.00, 'completed', 100, 'Dar es Salaam', 'Digitization of historical title deeds', 'project_1780681194_1782.png', '2024-01-01', '2024-05-31', 1, NULL, '2026-05-22 21:09:31', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(38, 'Data Center Expansion', 'Telecom Company', 450000000.00, 'completed', 100, 'Dar es Salaam', 'Data center expansion and upgrade', 'project_1779626800_8176.png', '2024-01-10', '2024-12-20', 4, NULL, '2026-05-24 13:07:14', 0, 1, 1, 0, 1, 0, '2026-05-24 16:07:38', 4, 0),
(39, 'Data Center Expansion', 'Telecom Company', 450000000.00, 'completed', 100, 'Dar es Salaam', 'Data center expansion and upgrade', 'project_1779626800_8176.png', '2024-01-10', '2024-12-20', 8, NULL, '2026-05-24 13:08:04', 0, 1, 1, 0, 0, 0, NULL, 8, 0),
(40, 'Eco-Friendly Housing Design', 'Green Building Council', 75000000.00, 'completed', 100, 'Arusha', 'Sustainable housing architectural designs', 'project_1779544319_5340.png', '2024-01-10', '2024-05-31', 1, NULL, '2026-05-24 13:17:33', 1, 0, 9, 0, 0, 0, NULL, 1, 0),
(45, 'Window Frame Supply - Housing Estate', 'Real Estate Developer', 180000000.00, '', 100, 'Dar es Salaam', '0', NULL, '2024-01-10', '0000-00-00', 1, NULL, '2026-05-24 18:04:26', 0, 0, 1, 0, 0, 0, NULL, NULL, 0),
(46, 'Aluminium Extrusion Line', 'Internal', 200000000.00, '', 55, 'Pwani Region', '0', NULL, '2024-02-01', '0000-00-00', 9, NULL, '2026-05-24 18:05:02', 0, 0, 7, 0, 1, 0, '2026-06-03 13:47:47', NULL, 0),
(47, 'Luxury Apartment Complex', 'Real Estate Developer', 450000000.00, '', 70, 'Dar es Salaam', '20', NULL, '2024-02-01', '0000-00-00', 1, NULL, '2026-05-24 18:05:30', 0, 1, 1, 0, 0, 0, NULL, NULL, 0),
(48, 'Aluminium Extrusion Line', 'Internal', 200000000.00, '', 55, 'Pwani Region', '0', NULL, '2024-02-01', '0000-00-00', 11, NULL, '2026-05-24 18:08:51', 0, 0, 9, 0, 1, 0, '2026-05-24 21:09:18', NULL, 0),
(49, 'Aluminium Extrusion Line', 'Internal', 200000000.00, '', 55, 'Pwani Region', '0', NULL, '2024-02-01', '0000-00-00', 1, NULL, '2026-05-24 18:10:19', 1, 0, 9, 1, 0, 0, '2026-05-24 21:13:45', NULL, 0),
(50, 'Window Frame Supply - Housing Estate', 'Real Estate Developer', 180000000.00, 'completed', 100, 'Dar es Salaam', 'Supply of 5000 window frames', 'project_1780414252_8636.png', '2024-01-10', '2024-06-30', 1, NULL, '2026-05-24 22:23:40', 1, 0, 7, 0, 0, 0, NULL, 1, 0),
(51, 'Window Frame Supply - Housing Estate', 'Real Estate Developer', 180000000.00, 'completed', 100, 'Dar es Salaam', 'Supply of 5000 window frames', NULL, '2024-01-10', '2024-06-30', 9, NULL, '2026-05-24 22:24:10', 0, 0, 7, 0, 0, 0, NULL, 9, 0),
(52, 'Window Frame Supply - Housing Estate', 'Real Estate Developer', 180000000.00, 'completed', 100, 'Dar es Salaam', 'Supply of 5000 window frames', NULL, '2024-01-10', '2024-06-30', 1, NULL, '2026-05-24 22:30:10', 1, 0, 9, 1, 0, 0, '2026-05-25 03:44:49', 1, 0),
(53, 'Window Frame Supply - Housing Estate', 'Real Estate Developer', 180000000.00, 'completed', 100, 'Dar es Salaam', 'Supply of 5000 window frames', NULL, '2024-01-10', '2024-06-30', 7, NULL, '2026-05-24 22:30:19', 1, 0, 9, 0, 0, 0, NULL, 7, 0),
(54, 'Aluminium Extrusion Line', 'Internal', 200000000.00, '', 55, 'Pwani Region', '0', NULL, '2024-02-01', '0000-00-00', 7, NULL, '2026-05-24 22:30:34', 1, 0, 9, 0, 0, 0, NULL, 7, 0),
(55, 'Window Frame Supply - Housing Estate', 'Real Estate Developer', 180000000.00, 'completed', 100, 'Dar es Salaam', 'Supply of 5000 window frames', 'project_1780589993_3715.png', '2024-01-10', '2024-06-30', 9, NULL, '2026-05-24 22:31:31', 0, 0, 7, 0, 0, 0, NULL, 9, 0),
(56, 'Window Frame Supply - Housing Estate', 'Real Estate Developer', 180000000.00, 'completed', 100, 'Dar es Salaam', 'Supply of 5000 window frames', NULL, '2024-01-10', '2024-06-30', 11, NULL, '2026-05-24 22:58:22', 0, 0, 7, 0, 0, 0, NULL, 11, 0),
(57, 'Aluminium Curtain Wall System', 'Highrise Construction', 350000000.00, 'in_progress', 40, 'Dar es Salaam', 'Curtain wall installation for 20-story building', NULL, '2024-04-01', '2024-11-30', 11, NULL, '2026-05-24 22:58:50', 0, 0, 7, 0, 0, 0, NULL, 11, 0),
(58, 'Luxury Apartment Complex', 'Real Estate Developer', 450000000.00, 'in_progress', 70, 'Dar es Salaam', '20-storey luxury apartment building', NULL, '2024-02-01', '2024-12-31', 7, NULL, '2026-05-24 23:01:02', 1, 0, 9, 0, 0, 0, NULL, 7, 0),
(59, 'Custom Aluminium Fabrication', 'Luxury Homes', 95000000.00, 'pending', 15, 'Dar es Salaam', 'Custom aluminium doors and windows', 'project_1780855029_6745.png', '2024-06-01', '2024-09-30', 11, NULL, '2026-05-24 23:04:46', 0, 0, 7, 0, 0, 0, NULL, 11, 0),
(60, 'Shopping Mall Construction', 'Retail Group', 650000000.00, 'in_progress', 60, 'Arusha', 'Modern shopping mall construction', NULL, '2024-02-15', '2024-12-31', 7, NULL, '2026-05-24 23:05:00', 1, 0, 11, 0, 0, 0, NULL, 7, 0),
(61, 'Shopping Mall Construction', 'Retail Group', 650000000.00, 'in_progress', 60, 'Arusha', 'Modern shopping mall construction', 'project_1780679378_8886.png', '2024-02-15', '2024-12-31', 1, NULL, '2026-05-24 23:18:52', 1, 0, 7, 0, 0, 0, NULL, 12, 0),
(62, 'Title Deed Digitization', 'Internal', 120000000.00, 'completed', 100, 'Dar es Salaam', 'Digitization of historical title deeds', NULL, '2024-01-01', '2024-05-31', 1, NULL, '2026-05-24 23:22:17', 0, 0, 12, 0, 0, 0, NULL, 7, 0),
(63, 'Aluminium Extrusion Line', 'Internal', 200000000.00, '', 55, 'Pwani Region', '0', 'project_1780678234_7509.png', '2024-02-01', '0000-00-00', 1, NULL, '2026-05-24 23:26:38', 0, 0, 7, 0, 0, 0, NULL, 12, 0),
(64, 'Luxury Apartment Complex', 'Real Estate Developer', 450000000.00, 'in_progress', 70, 'Dar es Salaam', '20-storey luxury apartment building', NULL, '2024-02-01', '2024-12-31', 4, NULL, '2026-05-24 23:27:34', 0, 0, 7, 0, 0, 0, NULL, 4, 0),
(65, 'Custom Aluminium Fabrication', 'Luxury Homes', 95000000.00, 'pending', 15, 'Dar es Salaam', 'Custom aluminium doors and windows', NULL, '2024-06-01', '2024-09-30', 4, NULL, '2026-05-24 23:28:00', 1, 0, 7, 0, 0, 0, NULL, 4, 0),
(66, 'Custom Aluminium Fabrication', 'Luxury Homes', 95000000.00, 'pending', 15, 'Dar es Salaam', 'Custom aluminium doors and windows', NULL, '2024-06-01', '2024-09-30', 1, NULL, '2026-05-24 23:32:58', 0, 0, 4, 0, 0, 0, NULL, 7, 0),
(67, 'Operations Excellence Program', 'Internal', 80000000.00, 'in_progress', 65, 'Dar es Salaam', 'Operational efficiency improvement program', NULL, '2024-03-01', '2024-08-31', 1, NULL, '2026-05-24 23:36:28', 0, 0, 4, 0, 0, 0, NULL, 7, 0),
(68, 'Aluminium Curtain Wall System', 'Highrise Construction', 350000000.00, 'in_progress', 40, 'Dar es Salaam', 'Curtain wall installation for 20-story building', 'project_1780913798_2892.png', '2024-04-01', '2024-11-30', 4, NULL, '2026-05-24 23:36:56', 1, 0, 7, 0, 0, 0, NULL, 4, 0),
(69, 'Window Frame Supply - Housing Estate', 'Real Estate Developer', 180000000.00, 'completed', 100, 'Dar es Salaam', 'Supply of 5000 window frames', NULL, '2024-01-10', '2024-06-30', 1, NULL, '2026-05-24 23:47:10', 1, 0, 1, 0, 0, 0, NULL, 7, 0),
(70, 'GIS Mapping Project', 'Municipal Council', 95000000.00, 'completed', 100, 'Dar es Salaam', 'Digital GIS mapping of city', 'project_1779625645_4163.png', '2024-01-10', '2024-06-30', 8, NULL, '2026-05-24 23:52:10', 1, 0, 10, 0, 0, 0, NULL, 8, 0),
(71, 'Data Center Expansion', 'Telecom Company', 450000000.00, 'completed', 100, 'Dar es Salaam', 'Data center expansion and upgrade', 'project_1779626800_8176.png', '2024-01-10', '2024-12-20', 10, NULL, '2026-05-24 23:52:33', 1, 0, 8, 0, 0, 0, NULL, 10, 0),
(72, 'School Construction', 'Ministry of Education', 180000000.00, 'completed', 0, 'Kinondoni', '', 'project_1780767211_6406.png', '2026-05-25', NULL, 1, 1, '2026-05-25 00:44:34', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(73, 'Coastal Zone Management Plan', 'Environmental Agency', 95000000.00, 'in_progress', 45, 'Coast Region', 'Coastal area development plan', 'project_1780657274_4042.png', '2024-03-15', '2024-09-30', 1, NULL, '2026-05-25 09:44:21', 1, 0, 8, 0, 0, 0, NULL, 1, 0),
(74, 'Boundary Demarcation - Coast Region', 'John Mwita', 120000000.00, 'in_progress', 25, 'Kigamboni', '', 'project_1780851821_8215.png', '2026-05-25', NULL, 8, 1, '2026-05-25 09:46:16', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(75, 'Boundary Demarcation - Coast Region', 'John Mwita', 120000000.00, 'in_progress', 59, 'Kinondoni', '', 'project_1780851840_9834.png', '2026-05-25', NULL, 10, 1, '2026-05-25 10:08:54', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(76, 'kanisa', 'mussa ', 0.00, 'pending', 0, '', '📎 UPLOADED DOCUMENT: report_84.pdf\nType: application/pdf\nSize: 98.23 KB\nUploaded by: Manager User\nUploaded: 03/06/2026, 20:23:20', NULL, '2026-06-03', NULL, 4, 1, '2026-06-03 17:23:20', 0, 0, NULL, 0, 1, 0, '2026-06-03 20:25:40', NULL, 0),
(77, 'kanisa', 'mussa ', 0.00, 'pending', 0, '', '📎 UPLOADED DOCUMENT: report_104.pdf\nType: application/pdf\nSize: 72.88 KB\nUploaded by: Manager User\nUploaded: 03/06/2026, 20:25:56', NULL, '2026-06-03', NULL, 4, 1, '2026-06-03 17:25:56', 0, 0, NULL, 0, 1, 0, '2026-06-03 20:26:24', NULL, 0),
(78, 'kanisa', 'mussa ', 0.00, 'pending', 0, '', '📎 UPLOADED DOCUMENT: report_84.pdf\nType: application/pdf\nSize: 98.23 KB\nUploaded by: Manager User\nUploaded: 03/06/2026, 20:26:35', NULL, '2026-06-03', NULL, 4, 1, '2026-06-03 17:26:35', 0, 0, NULL, 0, 1, 0, '2026-06-03 20:42:06', NULL, 0),
(79, 'Operations Excellence Program', 'Internal', 80000000.00, 'in_progress', 65, 'Dar es Salaam', 'Operational efficiency improvement program', 'project_1780824294_6781.png', '2024-03-01', '2024-08-31', 1, NULL, '2026-06-05 13:02:20', 0, 0, 7, 0, 0, 0, NULL, 1, 0),
(80, 'Boundary Demarcation - Coast Region', 'John Mwita', 120000000.00, 'pending', 0, 'Nationwide', '', 'project_1780911342_6a268cee4adb7.png', '2026-06-08', NULL, 12, 1, '2026-06-08 09:35:42', 0, 0, NULL, 0, 0, 0, NULL, NULL, 0),
(81, 'School Construction', 'Ministry of Education', 180000000.00, 'pending', 0, 'Kigamboni', '', 'project_1780925674_4113.png', '2026-06-08', NULL, 6, 1, '2026-06-08 13:33:45', 0, 0, NULL, 0, 1, 0, '2026-06-08 16:35:15', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `project_documents`
--

CREATE TABLE `project_documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT 1,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_documents`
--

INSERT INTO `project_documents` (`id`, `title`, `description`, `project_id`, `department_id`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_by`, `created_at`, `is_deleted`, `deleted_at`) VALUES
(2, 'nn', '', NULL, 1, '1780583127_6a218ad792a99.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780583127_6a218ad792a99.pdf', 'application/pdf', 100589, 'Super Admin', '2026-06-04 17:25:27', 0, NULL),
(4, 'nn', '', NULL, 1, '1780584675_6a2190e341a0b.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780584675_6a2190e341a0b.pdf', 'application/pdf', 100589, 'Super Admin', '2026-06-04 17:51:15', 0, NULL),
(5, 'nn', '', NULL, 1, '1 (4).jpg', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780587631_6a219c6f31fda.jpg', 'image/jpeg', 78584, 'Super Admin', '2026-06-04 18:40:31', 0, NULL),
(6, 'nn', '', NULL, 1, 'report_104.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780587660_6a219c8c16281.pdf', 'application/pdf', 74628, 'Super Admin', '2026-06-04 18:41:00', 0, NULL),
(7, 'WORK', '', NULL, 1, 'WORK CONTRACT 2.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780589021_6a21a1ddad767.pdf', 'application/pdf', 24542, 'Super Admin', '2026-06-04 19:03:41', 0, NULL),
(8, 'WORK', '', NULL, 4, 'WORK CONTRACT 2.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780592691_6a21b033d61f1.pdf', 'application/pdf', 24542, 'Manager User', '2026-06-04 20:04:51', 0, NULL),
(9, 'WORK', '', NULL, 11, 'report_84.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780656060_6a22a7bc35fc1.pdf', 'application/pdf', 100589, 'Construction Manager', '2026-06-05 13:41:00', 0, NULL),
(11, 'WORK', '', NULL, 7, 'download.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780662010_6a22befad2236.pdf', 'application/pdf', 100589, 'Aluminium Manager', '2026-06-05 15:20:10', 0, NULL),
(12, 'nn', '', NULL, 8, 'report_104.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780845719_6a258c97938ac.pdf', 'application/pdf', 74628, 'Town Planning Manager', '2026-06-07 18:21:59', 0, NULL),
(13, 'WORK', '', NULL, 10, 'report_104.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780846814_6a2590de0a7b0.pdf', 'application/pdf', 74628, 'Survey Manager', '2026-06-07 18:40:14', 0, NULL),
(14, 'nn', '', NULL, 9, 'report_104.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780855438_6a25b28e86e99.pdf', 'application/pdf', 74628, 'Architectural Manager', '2026-06-07 21:03:58', 0, NULL),
(15, 'nn', '', NULL, 4, '100project.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780911645_6a268e1d9080f.pdf', 'application/pdf', 465678, 'Manager User', '2026-06-08 12:40:45', 0, NULL),
(16, 'nn', '', NULL, 6, '100project.pdf', '/geotraverse/frontend/assets/uploads/projects/projects_documents/1780925699_6a26c50304892.pdf', 'application/pdf', 465678, 'Bricks & Timber Manager', '2026-06-08 16:34:59', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `recycle_bin`
--

CREATE TABLE `recycle_bin` (
  `id` int(11) NOT NULL,
  `original_table` varchar(100) NOT NULL COMMENT 'Jina la table asili (employees, projects, reports, transactions, daily_work, visitors, marketing_campaigns, planning_applications, design_projects, survey_requests, tasks, title_deeds)',
  `original_id` int(11) NOT NULL COMMENT 'ID ya record kwenye table asili',
  `deleted_data` longtext NOT NULL COMMENT 'Data yote ya record kabla ya kufutwa (JSON format)',
  `deleted_by_department_id` int(11) DEFAULT NULL COMMENT 'Department iliyofuta (kama ni department user)',
  `deleted_by_user_id` int(11) DEFAULT NULL COMMENT 'User ID aliyefuta',
  `deleted_by_admin` tinyint(1) DEFAULT 0 COMMENT '1 = Super Admin aliyefuta',
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `restored_at` datetime DEFAULT NULL,
  `restored_by` int(11) DEFAULT NULL,
  `permanently_deleted` tinyint(1) DEFAULT 0 COMMENT '1 = imefutwa kabisa (empty recycle bin)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recycle_bin`
--

INSERT INTO `recycle_bin` (`id`, `original_table`, `original_id`, `deleted_data`, `deleted_by_department_id`, `deleted_by_user_id`, `deleted_by_admin`, `deleted_at`, `restored_at`, `restored_by`, `permanently_deleted`) VALUES
(8, 'conversations', 33, '{\"id\":33,\"user_id\":12,\"admin_id\":1,\"sender_dept\":7,\"receiver_dept\":1,\"subject\":\"Message from Department 7\",\"status\":\"active\",\"created_at\":\"2026-05-20 15:57:59\",\"updated_at\":\"2026-05-20 15:57:59\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', 1, NULL, 0, '2026-05-20 15:58:33', NULL, NULL, 0),
(9, 'conversations', 33, '{\"id\":33,\"user_id\":12,\"admin_id\":1,\"sender_dept\":7,\"receiver_dept\":1,\"subject\":\"Message from Department 7\",\"status\":\"active\",\"created_at\":\"2026-05-20 15:57:59\",\"updated_at\":\"2026-05-20 15:58:33\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":1,\"deleted_at\":\"2026-05-20 15:58:33\"}', NULL, 1, 0, '2026-05-20 16:14:21', NULL, NULL, 0),
(10, 'conversations', 2, '{\"id\":2,\"user_id\":4,\"admin_id\":1,\"sender_dept\":4,\"receiver_dept\":1,\"subject\":\"Sales Inquiry\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:34:40', NULL, NULL, 0),
(11, 'conversations', 27, '{\"id\":27,\"user_id\":3,\"admin_id\":1,\"sender_dept\":null,\"receiver_dept\":null,\"subject\":\"New Message\",\"status\":\"active\",\"created_at\":\"2026-05-14 19:07:50\",\"updated_at\":\"2026-05-20 15:58:17\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:34:54', NULL, NULL, 0),
(12, 'conversations', 6, '{\"id\":6,\"user_id\":14,\"admin_id\":1,\"sender_dept\":14,\"receiver_dept\":1,\"subject\":\"Town Planning Report\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:34:56', NULL, NULL, 0),
(13, 'conversations', 3, '{\"id\":3,\"user_id\":6,\"admin_id\":1,\"sender_dept\":6,\"receiver_dept\":1,\"subject\":\"Project Status Update\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":1,\"deleted_by_department\":0,\"deleted_at\":\"2026-05-14 17:28:44\"}', NULL, 1, 0, '2026-05-20 16:34:57', NULL, NULL, 0),
(14, 'conversations', 31, '{\"id\":31,\"user_id\":8,\"admin_id\":1,\"sender_dept\":5,\"receiver_dept\":1,\"subject\":\"Secretary - Admin Queries\",\"status\":\"active\",\"created_at\":\"2026-05-19 16:49:51\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:00', NULL, NULL, 0),
(15, 'conversations', 28, '{\"id\":28,\"user_id\":8,\"admin_id\":1,\"sender_dept\":5,\"receiver_dept\":2,\"subject\":\"Secretary - Finance Communication\",\"status\":\"active\",\"created_at\":\"2026-05-19 16:49:51\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:02', NULL, NULL, 0),
(16, 'conversations', 7, '{\"id\":7,\"user_id\":16,\"admin_id\":1,\"sender_dept\":16,\"receiver_dept\":1,\"subject\":\"Architectural Design Review\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:07', NULL, NULL, 0),
(17, 'conversations', 29, '{\"id\":29,\"user_id\":8,\"admin_id\":1,\"sender_dept\":5,\"receiver_dept\":4,\"subject\":\"Secretary - Manager Communication\",\"status\":\"active\",\"created_at\":\"2026-05-19 16:49:51\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:09', NULL, NULL, 0),
(18, 'conversations', 30, '{\"id\":30,\"user_id\":8,\"admin_id\":1,\"sender_dept\":5,\"receiver_dept\":11,\"subject\":\"Secretary - Construction Updates\",\"status\":\"active\",\"created_at\":\"2026-05-19 16:49:51\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:12', NULL, NULL, 0),
(19, 'conversations', 8, '{\"id\":8,\"user_id\":18,\"admin_id\":1,\"sender_dept\":18,\"receiver_dept\":1,\"subject\":\"Survey Request\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:13', NULL, NULL, 0),
(20, 'conversations', 4, '{\"id\":4,\"user_id\":8,\"admin_id\":1,\"sender_dept\":8,\"receiver_dept\":1,\"subject\":\"Office Administration\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:15', NULL, NULL, 0),
(21, 'conversations', 5, '{\"id\":5,\"user_id\":12,\"admin_id\":1,\"sender_dept\":12,\"receiver_dept\":1,\"subject\":\"Aluminium Production\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-20 15:25:50\",\"deleted_by_user_id\":null,\"deleted_by_admin\":1,\"deleted_by_department\":0,\"deleted_at\":\"2026-05-14 17:32:18\"}', NULL, 1, 0, '2026-05-20 16:35:17', NULL, NULL, 0),
(22, 'conversations', 12, '{\"id\":12,\"user_id\":5,\"admin_id\":1,\"sender_dept\":null,\"receiver_dept\":null,\"subject\":\"Marketing Campaign\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-10 19:46:59\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:19', NULL, NULL, 0),
(23, 'conversations', 13, '{\"id\":13,\"user_id\":7,\"admin_id\":1,\"sender_dept\":null,\"receiver_dept\":null,\"subject\":\"Staff Meeting\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-10 19:46:59\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:22', NULL, NULL, 0),
(24, 'conversations', 14, '{\"id\":14,\"user_id\":10,\"admin_id\":1,\"sender_dept\":null,\"receiver_dept\":null,\"subject\":\"Bricks Production\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-10 19:46:59\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:23', NULL, NULL, 0),
(25, 'conversations', 15, '{\"id\":15,\"user_id\":15,\"admin_id\":1,\"sender_dept\":null,\"receiver_dept\":null,\"subject\":\"Urban Planning\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-10 19:46:59\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 16:35:25', NULL, NULL, 0),
(26, 'reports', 38, '{\"id\":38,\"title\":\"Test from Finance\",\"period\":\"monthly\",\"content\":\"This is a test report from Finance to Admin\",\"status\":\"sent\",\"department_id\":2,\"created_at\":\"2026-05-15 00:28:27\",\"is_viewed_by_admin\":1,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":1,\"is_viewed_by_department\":1,\"sent_from_department\":null}', 2, 3, 0, '2026-05-20 16:42:36', NULL, NULL, 0),
(27, 'reports', 37, '{\"id\":37,\"title\":\"Test Admin Report\",\"period\":\"monthly\",\"content\":\"This is a test report from Super Admin\",\"status\":\"sent\",\"department_id\":1,\"created_at\":\"2026-05-15 00:07:23\",\"is_viewed_by_admin\":1,\"sent_from_dept\":null,\"deleted_by_admin\":1,\"deleted_by_department\":0,\"deleted_at\":\"2026-05-20 16:38:08\",\"sent_to_department\":2,\"is_viewed_by_department\":1,\"sent_from_department\":1}', 2, 3, 0, '2026-05-20 16:42:39', NULL, NULL, 0),
(28, 'reports', 23, '{\"id\":23,\"title\":\"Test Report Finance\",\"period\":\"monthly\",\"content\":\"This is a test report for Finance department\",\"status\":\"sent\",\"department_id\":2,\"created_at\":\"2026-05-14 14:28:55\",\"is_viewed_by_admin\":1,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":1,\"is_viewed_by_department\":1,\"sent_from_department\":2}', 2, 3, 0, '2026-05-20 16:46:58', NULL, NULL, 0),
(29, 'reports', 52, '{\"id\":52,\"title\":\"MAIN MYULA HOTEL\",\"period\":\"weekly\",\"content\":\"IMELIPWA 10000000\",\"status\":\"draft\",\"department_id\":2,\"created_at\":\"2026-05-20 16:45:47\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":null,\"is_viewed_by_department\":0,\"sent_from_department\":null}', 2, 3, 0, '2026-05-20 17:00:59', NULL, NULL, 0),
(30, 'reports', 55, '{\"id\":55,\"title\":\"April Aluminium Production\",\"period\":\"weekly\",\"content\":\"MPYA\",\"status\":\"sent\",\"department_id\":7,\"created_at\":\"2026-05-20 16:58:13\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":7,\"is_viewed_by_department\":0,\"sent_from_department\":2}', 2, 3, 0, '2026-05-20 17:01:03', NULL, NULL, 0),
(31, 'reports', 54, '{\"id\":54,\"title\":\"April Aluminium Production\",\"period\":\"weekly\",\"content\":\"MPYA\",\"status\":\"sent\",\"department_id\":1,\"created_at\":\"2026-05-20 16:47:43\",\"is_viewed_by_admin\":1,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":1,\"is_viewed_by_department\":0,\"sent_from_department\":2}', 2, 3, 0, '2026-05-20 17:01:05', NULL, NULL, 0),
(32, 'reports', 53, '{\"id\":53,\"title\":\"April Aluminium Production\",\"period\":\"weekly\",\"content\":\"MPYA\",\"status\":\"sent\",\"department_id\":2,\"created_at\":\"2026-05-20 16:47:15\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":7,\"is_viewed_by_department\":0,\"sent_from_department\":null}', 2, 3, 0, '2026-05-20 17:01:08', NULL, NULL, 0),
(33, 'reports', 60, '{\"id\":60,\"title\":\"mm\",\"period\":\"weekly\",\"content\":\"mm\",\"status\":\"draft\",\"department_id\":3,\"created_at\":\"2026-05-20 17:24:19\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":null,\"is_viewed_by_department\":0,\"sent_from_department\":null}', 3, NULL, 0, '2026-05-20 17:24:28', NULL, NULL, 0),
(34, 'reports', 56, '{\"id\":56,\"title\":\"April Aluminium Production\",\"period\":\"monthly\",\"content\":\"April 2024 Aluminium Production Report:\\n\\nProduction Summary:\\n- Window frames: 2,800 units\\n- Door frames: 1,400 units\\n- Sliding doors: 450 units\\n- Custom fabrications: 60 items\\n\\nOrders Completed: 28\\nOrders In Progress: 12\\nRevenue: TZS 32,500,000\\nExpenses: TZS 18,000,000\\nProfit: TZS 14,500,000\\n\\nMaterials Used:\\n- Aluminium sheets: 18,000 kg\\n- Glass panels: 3,000 pcs\\n- Hardware: 3,800 sets\\n\\nEfficiency: 94%\\nWaste Reduction: 7% (improved from 12%)\\n\\nStaff overtime: 55 hours\\n\\nClient Complaints: 1 (resolved)\",\"status\":\"sent\",\"department_id\":7,\"created_at\":\"2026-05-20 17:01:17\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":7,\"is_viewed_by_department\":1,\"sent_from_department\":2}', 7, NULL, 0, '2026-05-20 17:38:20', NULL, NULL, 0),
(35, 'reports', 61, '{\"id\":61,\"title\":\"mm\",\"period\":\"weekly\",\"content\":\"sales 1000000\",\"status\":\"draft\",\"department_id\":3,\"created_at\":\"2026-05-20 17:24:47\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":null,\"is_viewed_by_department\":0,\"sent_from_department\":null}', 3, NULL, 0, '2026-05-20 18:10:23', NULL, NULL, 0),
(36, 'reports', 62, '{\"id\":62,\"title\":\"MAIN\",\"period\":\"weekly\",\"content\":\"HII\",\"status\":\"draft\",\"department_id\":2,\"created_at\":\"2026-05-20 17:58:13\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":null,\"is_viewed_by_department\":0,\"sent_from_department\":null}', 2, 3, 0, '2026-05-20 18:59:41', NULL, NULL, 0),
(37, 'conversations', 26, '{\"id\":26,\"user_id\":3,\"admin_id\":4,\"sender_dept\":null,\"receiver_dept\":null,\"subject\":\"New Message\",\"status\":\"active\",\"created_at\":\"2026-05-14 19:03:43\",\"updated_at\":\"2026-05-15 01:10:54\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 3, 0, '2026-05-20 19:02:51', NULL, NULL, 0),
(38, 'reports', 65, '{\"id\":65,\"title\":\"ANDREW\",\"period\":\"weekly\",\"content\":\"ITAKAMILIKA SOON\",\"status\":\"sent\",\"department_id\":5,\"created_at\":\"2026-05-20 19:04:22\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":5,\"is_viewed_by_department\":0,\"sent_from_department\":7}', 5, NULL, 0, '2026-05-20 19:04:50', NULL, NULL, 0),
(39, 'reports', 66, '{\"id\":66,\"title\":\"ANDREW\",\"period\":\"weekly\",\"content\":\"ITAKAMILIKA SOON\",\"status\":\"sent\",\"department_id\":6,\"created_at\":\"2026-05-20 19:07:32\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":6,\"is_viewed_by_department\":0,\"sent_from_department\":7}', 6, NULL, 0, '2026-05-20 19:07:53', NULL, NULL, 0),
(40, 'reports', 67, '{\"id\":67,\"title\":\"ANDREW\",\"period\":\"weekly\",\"content\":\"ITAKAMILIKA SOON\",\"status\":\"sent\",\"department_id\":8,\"created_at\":\"2026-05-20 19:17:08\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":8,\"is_viewed_by_department\":0,\"sent_from_department\":7}', 8, NULL, 0, '2026-05-20 19:17:24', NULL, NULL, 0),
(41, 'conversations', 34, '{\"id\":34,\"user_id\":2,\"admin_id\":1,\"sender_dept\":2,\"receiver_dept\":1,\"subject\":\"Report: April Aluminium Production\",\"status\":\"active\",\"created_at\":\"2026-05-20 16:47:43\",\"updated_at\":\"2026-05-20 16:47:43\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-20 19:18:58', NULL, NULL, 0),
(42, 'conversations', 23, '{\"id\":23,\"user_id\":21,\"admin_id\":1,\"sender_dept\":null,\"receiver_dept\":null,\"subject\":\"New Message\",\"status\":\"active\",\"created_at\":\"2026-05-12 11:55:15\",\"updated_at\":\"2026-05-14 17:32:15\",\"deleted_by_user_id\":null,\"deleted_by_admin\":1,\"deleted_by_department\":0,\"deleted_at\":\"2026-05-14 17:32:15\"}', NULL, 1, 0, '2026-05-20 19:19:00', NULL, NULL, 0),
(43, 'reports', 69, '{\"id\":69,\"title\":\"manager\",\"period\":\"weekly\",\"content\":\"BNB MPYA NALA\",\"status\":\"draft\",\"department_id\":4,\"created_at\":\"2026-05-20 19:31:27\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":null,\"is_viewed_by_department\":0,\"sent_from_department\":null}', 4, NULL, 0, '2026-05-20 19:31:43', NULL, NULL, 0),
(44, 'reports', 68, '{\"id\":68,\"title\":\"manager\",\"period\":\"weekly\",\"content\":\"BNB MPYA NALA\",\"status\":\"draft\",\"department_id\":4,\"created_at\":\"2026-05-20 19:31:24\",\"is_viewed_by_admin\":0,\"sent_from_dept\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null,\"sent_to_department\":null,\"is_viewed_by_department\":0,\"sent_from_department\":null}', 4, NULL, 0, '2026-05-20 19:31:46', NULL, NULL, 0),
(45, 'conversations', 36, '{\"id\":36,\"user_id\":3,\"admin_id\":12,\"sender_dept\":2,\"receiver_dept\":7,\"subject\":\"Message from Finance\",\"status\":\"active\",\"created_at\":\"2026-05-20 16:56:36\",\"updated_at\":\"2026-05-20 16:56:36\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 3, 0, '2026-05-20 22:50:39', NULL, NULL, 0),
(46, 'conversations', 35, '{\"id\":35,\"user_id\":12,\"admin_id\":2,\"sender_dept\":7,\"receiver_dept\":2,\"subject\":\"Message from Department 7\",\"status\":\"active\",\"created_at\":\"2026-05-20 16:56:02\",\"updated_at\":\"2026-05-20 16:56:02\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 2, 0, '2026-05-21 14:00:41', NULL, NULL, 0),
(47, 'conversations', 2, '{\"id\":2,\"user_id\":4,\"admin_id\":1,\"sender_dept\":4,\"receiver_dept\":1,\"subject\":\"Sales Inquiry\",\"status\":\"active\",\"created_at\":\"2026-05-10 19:46:59\",\"updated_at\":\"2026-05-20 16:34:40\",\"deleted_by_user_id\":1,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":\"2026-05-20 16:34:40\"}', 4, NULL, 0, '2026-05-21 20:55:45', NULL, NULL, 0),
(48, 'conversations', 37, '{\"id\":37,\"user_id\":10,\"admin_id\":2,\"sender_dept\":6,\"receiver_dept\":2,\"subject\":\"Message from Department 6\",\"status\":\"active\",\"created_at\":\"2026-05-20 19:08:24\",\"updated_at\":\"2026-05-20 19:08:24\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 2, 0, '2026-05-22 00:43:40', NULL, NULL, 0),
(49, 'messages', 137, '{\"id\":137,\"sender_dept\":5,\"receiver_dept\":4,\"conversation_id\":29,\"sender_id\":8,\"receiver_id\":4,\"message\":\"Meeting room is prepared. Refreshments are ready\",\"is_read\":1,\"read_at\":null,\"status\":\"sent\",\"sender_deleted\":0,\"receiver_deleted\":0,\"deleted_at\":\"2026-05-20 16:35:09\",\"created_at\":\"2026-05-14 09:40:00\"}', 5, NULL, 0, '2026-05-22 01:39:29', NULL, NULL, 0),
(50, 'messages', 136, '{\"id\":136,\"sender_dept\":4,\"receiver_dept\":5,\"conversation_id\":29,\"sender_id\":4,\"receiver_id\":8,\"message\":\"Please send them to the conference room in 10 minutes\",\"is_read\":1,\"read_at\":null,\"status\":\"sent\",\"sender_deleted\":0,\"receiver_deleted\":0,\"deleted_at\":\"2026-05-20 16:35:09\",\"created_at\":\"2026-05-14 09:35:00\"}', 5, NULL, 0, '2026-05-22 01:39:33', NULL, NULL, 0),
(51, 'messages', 138, '{\"id\":138,\"sender_dept\":4,\"receiver_dept\":5,\"conversation_id\":29,\"sender_id\":4,\"receiver_id\":8,\"message\":\"Excellent work! The client was impressed\",\"is_read\":1,\"read_at\":\"2026-05-22 01:39:24\",\"status\":\"sent\",\"sender_deleted\":0,\"receiver_deleted\":0,\"deleted_at\":\"2026-05-20 16:35:09\",\"created_at\":\"2026-05-14 12:00:00\"}', 5, NULL, 0, '2026-05-22 01:39:36', NULL, NULL, 0),
(52, 'messages', 135, '{\"id\":135,\"sender_dept\":5,\"receiver_dept\":4,\"conversation_id\":29,\"sender_id\":8,\"receiver_id\":4,\"message\":\"Manager, visitors are waiting for the board meeting\",\"is_read\":1,\"read_at\":null,\"status\":\"sent\",\"sender_deleted\":0,\"receiver_deleted\":0,\"deleted_at\":\"2026-05-20 16:35:09\",\"created_at\":\"2026-05-14 09:30:00\"}', 5, NULL, 0, '2026-05-22 01:39:39', NULL, NULL, 0),
(53, 'messages', 139, '{\"id\":139,\"sender_dept\":5,\"receiver_dept\":4,\"conversation_id\":29,\"sender_id\":8,\"receiver_id\":4,\"message\":\"Thank you! I will schedule the follow-up meeting\",\"is_read\":1,\"read_at\":\"2026-05-21 21:09:31\",\"status\":\"sent\",\"sender_deleted\":0,\"receiver_deleted\":0,\"deleted_at\":\"2026-05-20 16:35:09\",\"created_at\":\"2026-05-14 14:30:00\"}', 5, NULL, 0, '2026-05-22 01:39:42', NULL, NULL, 0),
(54, 'messages', 133, '{\"id\":133,\"sender_dept\":5,\"receiver_dept\":2,\"conversation_id\":28,\"sender_id\":8,\"receiver_id\":2,\"message\":\"Thank you. I have reviewed it. When is the approval meeting?\",\"is_read\":1,\"read_at\":null,\"status\":\"sent\",\"sender_deleted\":0,\"receiver_deleted\":0,\"deleted_at\":\"2026-05-20 16:35:02\",\"created_at\":\"2026-05-15 11:15:00\"}', 5, NULL, 0, '2026-05-22 02:00:41', NULL, NULL, 0),
(55, 'messages', 131, '{\"id\":131,\"sender_dept\":5,\"receiver_dept\":2,\"conversation_id\":28,\"sender_id\":8,\"receiver_id\":2,\"message\":\"Finance team, please share the Q2 budget report\",\"is_read\":1,\"read_at\":null,\"status\":\"sent\",\"sender_deleted\":0,\"receiver_deleted\":0,\"deleted_at\":\"2026-05-20 16:35:02\",\"created_at\":\"2026-05-15 09:00:00\"}', 5, NULL, 0, '2026-05-22 02:09:20', NULL, NULL, 0),
(56, 'messages', 132, '{\"id\":132,\"sender_dept\":2,\"receiver_dept\":5,\"conversation_id\":28,\"sender_id\":2,\"receiver_id\":8,\"message\":\"Here is the Q2 budget report. Total allocation TZS 85M\",\"is_read\":1,\"read_at\":null,\"status\":\"sent\",\"sender_deleted\":0,\"receiver_deleted\":0,\"deleted_at\":\"2026-05-20 16:35:02\",\"created_at\":\"2026-05-15 10:30:00\"}', 5, NULL, 0, '2026-05-22 02:09:25', NULL, NULL, 0),
(57, 'messages', 134, '{\"id\":134,\"sender_dept\":2,\"receiver_dept\":5,\"conversation_id\":28,\"sender_id\":2,\"receiver_id\":8,\"message\":\"Approval meeting scheduled for Friday at 2 PM\",\"is_read\":1,\"read_at\":\"2026-05-22 01:39:01\",\"status\":\"sent\",\"sender_deleted\":0,\"receiver_deleted\":0,\"deleted_at\":\"2026-05-20 16:35:02\",\"created_at\":\"2026-05-16 09:45:00\"}', 5, NULL, 0, '2026-05-22 02:09:28', NULL, NULL, 0),
(58, 'conversations', 57, '{\"id\":57,\"user_id\":6,\"admin_id\":1,\"sender_dept\":4,\"receiver_dept\":9,\"subject\":\"Architectural Project Priorities\",\"status\":\"active\",\"created_at\":\"2024-06-24 13:30:00\",\"updated_at\":\"2024-06-28 14:30:00\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', 4, NULL, 0, '2026-05-23 01:15:28', NULL, NULL, 0),
(59, 'conversations', 54, '{\"id\":54,\"user_id\":6,\"admin_id\":1,\"sender_dept\":4,\"receiver_dept\":2,\"subject\":\"Staff Salary Review\",\"status\":\"active\",\"created_at\":\"2024-06-21 08:45:00\",\"updated_at\":\"2024-06-28 16:15:00\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', 4, NULL, 0, '2026-05-23 01:15:32', NULL, NULL, 0),
(60, 'conversations', 55, '{\"id\":55,\"user_id\":6,\"admin_id\":1,\"sender_dept\":4,\"receiver_dept\":5,\"subject\":\"Secretary Appointment Schedule\",\"status\":\"active\",\"created_at\":\"2024-06-22 09:15:00\",\"updated_at\":\"2024-06-29 11:00:00\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', 4, NULL, 0, '2026-05-23 01:15:35', NULL, NULL, 0),
(61, 'conversations', 58, '{\"id\":58,\"user_id\":8,\"admin_id\":1,\"sender_dept\":5,\"receiver_dept\":4,\"subject\":\"Meeting Room Booking\",\"status\":\"active\",\"created_at\":\"2024-06-15 09:00:00\",\"updated_at\":\"2024-06-29 10:30:00\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', 4, NULL, 0, '2026-05-23 01:15:39', NULL, NULL, 0),
(62, 'conversations', 46, '{\"id\":46,\"user_id\":2,\"admin_id\":1,\"sender_dept\":2,\"receiver_dept\":4,\"subject\":\"Quarterly Financial Report\",\"status\":\"active\",\"created_at\":\"2024-06-13 09:30:00\",\"updated_at\":\"2024-06-29 14:30:00\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', 4, NULL, 0, '2026-05-23 01:15:42', NULL, NULL, 0),
(63, 'conversations', 36, '{\"id\":36,\"user_id\":1,\"admin_id\":1,\"sender_dept\":1,\"receiver_dept\":4,\"subject\":\"Managerial Strategy Meeting\",\"status\":\"active\",\"created_at\":\"2024-06-03 11:00:00\",\"updated_at\":\"2024-06-30 09:45:00\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', 4, NULL, 0, '2026-05-23 01:15:49', NULL, NULL, 0),
(64, 'conversations', 56, '{\"id\":56,\"user_id\":6,\"admin_id\":1,\"sender_dept\":4,\"receiver_dept\":6,\"subject\":\"Production Capacity Planning\",\"status\":\"active\",\"created_at\":\"2024-06-23 10:00:00\",\"updated_at\":\"2024-06-30 08:45:00\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', 4, NULL, 0, '2026-05-23 01:15:53', NULL, NULL, 0),
(65, 'conversations', 42, '{\"id\":42,\"user_id\":1,\"admin_id\":1,\"sender_dept\":1,\"receiver_dept\":10,\"subject\":\"Survey Equipment Status\",\"status\":\"active\",\"created_at\":\"2024-06-09 11:30:00\",\"updated_at\":\"2024-06-28 13:20:00\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-23 13:10:15', NULL, NULL, 0),
(66, 'conversations', 37, '{\"id\":37,\"user_id\":1,\"admin_id\":1,\"sender_dept\":1,\"receiver_dept\":5,\"subject\":\"Secretary Office Procedures\",\"status\":\"active\",\"created_at\":\"2024-06-04 08:15:00\",\"updated_at\":\"2024-06-28 16:20:00\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-23 13:10:21', NULL, NULL, 0),
(67, 'conversations', 38, '{\"id\":38,\"user_id\":1,\"admin_id\":1,\"sender_dept\":1,\"receiver_dept\":6,\"subject\":\"Bricks Production Targets\",\"status\":\"active\",\"created_at\":\"2024-06-05 13:00:00\",\"updated_at\":\"2024-06-29 11:30:00\",\"deleted_by_user_id\":null,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":null}', NULL, 1, 0, '2026-05-23 13:10:26', NULL, NULL, 0),
(68, 'conversations', 38, '{\"id\":38,\"user_id\":1,\"admin_id\":1,\"sender_dept\":1,\"receiver_dept\":6,\"subject\":\"Bricks Production Targets\",\"status\":\"active\",\"created_at\":\"2024-06-05 13:00:00\",\"updated_at\":\"2026-05-23 13:10:26\",\"deleted_by_user_id\":1,\"deleted_by_admin\":0,\"deleted_by_department\":0,\"deleted_at\":\"2026-05-23 13:10:26\"}', NULL, 1, 0, '2026-05-23 13:14:45', NULL, NULL, 0),
(69, 'conversations', 38, '{\"conversation_id\":38,\"sender_dept\":1,\"receiver_dept\":6,\"subject\":\"Bricks Production Targets\",\"deleted_by\":\"Super Admin\",\"deleted_at\":\"2026-05-23 12:23:02\"}', NULL, NULL, 1, '2026-05-23 13:23:02', NULL, NULL, 0),
(70, 'conversations', 38, '{\"conversation_id\":38,\"sender_dept\":1,\"receiver_dept\":6,\"subject\":\"Bricks Production Targets\",\"deleted_by\":\"Super Admin\",\"deleted_at\":\"2026-05-23 12:23:09\"}', NULL, NULL, 1, '2026-05-23 13:23:09', NULL, NULL, 0),
(71, 'conversations', 38, '{\"conversation_id\":38,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 13:29:04', NULL, NULL, 0),
(72, 'conversations', 35, '{\"conversation_id\":35,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 13:33:16', NULL, NULL, 0),
(73, 'conversations', 1, '{\"conversation_id\":1,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-23 13:40:21', NULL, NULL, 0),
(74, 'conversations', 75, '{\"conversation_id\":75,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-23 13:40:26', NULL, NULL, 0),
(75, 'conversations', 43, '{\"conversation_id\":43,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 13:52:48', NULL, NULL, 0),
(76, 'conversations', 56, '{\"conversation_id\":56,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-23 13:57:07', NULL, NULL, 0),
(77, 'conversations', 46, '{\"conversation_id\":46,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-23 13:57:12', NULL, NULL, 0),
(78, 'conversations', 57, '{\"conversation_id\":57,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-23 13:57:17', NULL, NULL, 0),
(79, 'conversations', 79, '{\"conversation_id\":79,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-23 14:20:42', NULL, NULL, 0),
(80, 'conversations', 58, '{\"conversation_id\":58,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-23 14:20:46', NULL, NULL, 0),
(81, 'conversations', 4, '{\"conversation_id\":4,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-23 14:20:47', NULL, NULL, 0),
(82, 'conversations', 54, '{\"conversation_id\":54,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-23 14:20:52', NULL, NULL, 0),
(83, 'conversations', 55, '{\"conversation_id\":55,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-23 14:20:57', NULL, NULL, 0),
(84, 'conversations', 79, '{\"conversation_id\":79,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 14:24:52', NULL, NULL, 0),
(85, 'messages', 251, '{\"message\": \"sawa\", \"sender_dept\": 1, \"receiver_dept\": 4}', 1, NULL, 1, '2026-05-23 14:38:56', NULL, NULL, 0),
(86, 'conversations', 63, '{\"conversation_id\":63,\"deleted_by_department\":7,\"deleted_by_name\":\"Unknown\"}', 7, NULL, 0, '2026-05-23 14:46:14', NULL, NULL, 0),
(87, 'conversations', 48, '{\"conversation_id\":48,\"deleted_by_department\":7,\"deleted_by_name\":\"Unknown\"}', 7, NULL, 0, '2026-05-23 14:46:17', NULL, NULL, 0),
(88, 'conversations', 71, '{\"conversation_id\":71,\"deleted_by_department\":7,\"deleted_by_name\":\"Unknown\"}', 7, NULL, 0, '2026-05-23 14:46:19', NULL, NULL, 0),
(89, 'conversations', 37, '{\"conversation_id\":37,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 14:46:36', NULL, NULL, 0),
(90, 'conversations', 40, '{\"conversation_id\":40,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 14:46:38', NULL, NULL, 0),
(91, 'conversations', 78, '{\"conversation_id\":78,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 14:46:41', NULL, NULL, 0),
(92, 'conversations', 42, '{\"conversation_id\":42,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 14:46:45', NULL, NULL, 0),
(93, 'conversations', 39, '{\"conversation_id\":39,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 14:46:50', NULL, NULL, 0),
(94, 'conversations', 44, '{\"conversation_id\":44,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 14:46:52', NULL, NULL, 0),
(95, 'conversations', 41, '{\"conversation_id\":41,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 14:46:55', NULL, NULL, 0),
(96, 'conversations', 41, '{\"conversation_id\":41,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-23 14:48:13', NULL, NULL, 0),
(97, 'conversations', 63, '{\"conversation_id\":63,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-23 14:48:15', NULL, NULL, 0),
(98, 'conversations', 57, '{\"conversation_id\":57,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-23 14:48:17', NULL, NULL, 0),
(99, 'conversations', 86, '{\"conversation_id\":86,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-23 15:12:32', NULL, NULL, 0),
(100, 'conversations', 3, '{\"conversation_id\":3,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-23 15:12:48', NULL, NULL, 0),
(101, 'conversations', 11, '{\"conversation_id\":11,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-23 15:12:51', NULL, NULL, 0),
(102, 'conversations', 88, '{\"conversation_id\":88,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-23 15:13:02', NULL, NULL, 0),
(103, 'conversations', 87, '{\"conversation_id\":87,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-23 15:13:04', NULL, NULL, 0),
(104, 'conversations', 51, '{\"conversation_id\":51,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-23 15:13:08', NULL, NULL, 0),
(105, 'conversations', 46, '{\"conversation_id\":46,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:48:46', NULL, NULL, 0),
(106, 'conversations', 85, '{\"conversation_id\":85,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:48:56', NULL, NULL, 0),
(107, 'conversations', 54, '{\"conversation_id\":54,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:54:36', NULL, NULL, 0),
(108, 'conversations', 34, '{\"conversation_id\":34,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:54:38', NULL, NULL, 0),
(109, 'conversations', 74, '{\"conversation_id\":74,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:54:47', NULL, NULL, 0),
(110, 'conversations', 48, '{\"conversation_id\":48,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:54:49', NULL, NULL, 0),
(111, 'conversations', 9, '{\"conversation_id\":9,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:54:50', NULL, NULL, 0),
(112, 'conversations', 2, '{\"conversation_id\":2,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:54:52', NULL, NULL, 0),
(113, 'conversations', 20, '{\"conversation_id\":20,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:54:56', NULL, NULL, 0),
(114, 'conversations', 49, '{\"conversation_id\":49,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:54:59', NULL, NULL, 0),
(115, 'conversations', 45, '{\"conversation_id\":45,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 16:55:02', NULL, NULL, 0),
(116, 'conversations', 12, '{\"conversation_id\":12,\"deleted_by_department\":6,\"deleted_by_name\":\"Unknown\"}', 6, NULL, 0, '2026-05-23 17:55:48', NULL, NULL, 0),
(117, 'conversations', 60, '{\"conversation_id\":60,\"deleted_by_department\":6,\"deleted_by_name\":\"Unknown\"}', 6, NULL, 0, '2026-05-23 17:55:50', NULL, NULL, 0),
(118, 'conversations', 80, '{\"conversation_id\":80,\"deleted_by_department\":6,\"deleted_by_name\":\"Unknown\"}', 6, NULL, 0, '2026-05-23 17:55:51', NULL, NULL, 0),
(119, 'conversations', 92, '{\"conversation_id\":92,\"deleted_by_department\":6,\"deleted_by_name\":\"Unknown\"}', 6, NULL, 0, '2026-05-23 17:55:53', NULL, NULL, 0),
(120, 'conversations', 56, '{\"conversation_id\":56,\"deleted_by_department\":6,\"deleted_by_name\":\"Unknown\"}', 6, NULL, 0, '2026-05-23 17:55:55', NULL, NULL, 0),
(121, 'conversations', 38, '{\"conversation_id\":38,\"deleted_by_department\":6,\"deleted_by_name\":\"Unknown\"}', 6, NULL, 0, '2026-05-23 17:55:58', NULL, NULL, 0),
(122, 'conversations', 47, '{\"conversation_id\":47,\"deleted_by_department\":6,\"deleted_by_name\":\"Unknown\"}', 6, NULL, 0, '2026-05-23 17:56:03', NULL, NULL, 0),
(123, 'conversations', 47, '{\"conversation_id\":47,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 18:00:51', NULL, NULL, 0),
(124, 'conversations', 14, '{\"conversation_id\":14,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 18:02:04', NULL, NULL, 0),
(125, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 18:02:08', NULL, NULL, 0),
(126, 'conversations', 91, '{\"conversation_id\":91,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 18:02:10', NULL, NULL, 0),
(127, 'conversations', 59, '{\"conversation_id\":59,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 18:02:11', NULL, NULL, 0),
(128, 'conversations', 73, '{\"conversation_id\":73,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 18:02:13', NULL, NULL, 0),
(129, 'conversations', 61, '{\"conversation_id\":61,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 18:02:15', NULL, NULL, 0),
(130, 'conversations', 80, '{\"conversation_id\":80,\"deleted_by_department\":6,\"deleted_by_name\":\"Unknown\"}', 6, NULL, 0, '2026-05-23 19:17:18', NULL, NULL, 0),
(131, 'conversations', 80, '{\"conversation_id\":80,\"deleted_by_department\":6,\"deleted_by_name\":\"Unknown\"}', 6, NULL, 0, '2026-05-23 19:17:21', NULL, NULL, 0),
(132, 'conversations', 74, '{\"conversation_id\":74,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 19:27:36', NULL, NULL, 0),
(133, 'conversations', 85, '{\"conversation_id\":85,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 19:27:42', NULL, NULL, 0),
(134, 'conversations', 34, '{\"conversation_id\":34,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 19:47:08', NULL, NULL, 0),
(135, 'conversations', 85, '{\"conversation_id\":85,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 19:47:12', NULL, NULL, 0),
(136, 'conversations', 34, '{\"conversation_id\":34,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 19:55:26', NULL, NULL, 0),
(137, 'conversations', 34, '{\"conversation_id\":34,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 19:57:05', NULL, NULL, 0),
(138, 'conversations', 34, '{\"conversation_id\":34,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 19:57:50', NULL, NULL, 0),
(139, 'conversations', 34, '{\"conversation_id\":34,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 20:02:10', NULL, NULL, 0),
(140, 'conversations', 75, '{\"conversation_id\":75,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 21:51:38', NULL, NULL, 0),
(141, 'conversations', 34, '{\"conversation_id\":34,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 21:56:25', NULL, NULL, 0),
(142, 'conversations', 85, '{\"conversation_id\":85,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 21:57:14', NULL, NULL, 0),
(143, 'conversations', 9, '{\"conversation_id\":9,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 22:23:35', NULL, NULL, 0),
(144, 'conversations', 46, '{\"conversation_id\":46,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 22:28:00', NULL, NULL, 0),
(145, 'conversations', 47, '{\"conversation_id\":47,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 22:28:02', NULL, NULL, 0),
(146, 'conversations', 48, '{\"conversation_id\":48,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 22:28:04', NULL, NULL, 0),
(147, 'conversations', 49, '{\"conversation_id\":49,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 22:28:07', NULL, NULL, 0),
(148, 'conversations', 61, '{\"conversation_id\":61,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 22:28:09', NULL, NULL, 0),
(149, 'conversations', 73, '{\"conversation_id\":73,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 22:28:12', NULL, NULL, 0),
(150, 'conversations', 34, '{\"conversation_id\":34,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 22:32:41', NULL, NULL, 0),
(151, 'conversations', 85, '{\"conversation_id\":85,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 22:32:44', NULL, NULL, 0),
(152, 'conversations', 43, '{\"conversation_id\":43,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 22:55:00', NULL, NULL, 0),
(153, 'conversations', 71, '{\"conversation_id\":71,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 22:55:04', NULL, NULL, 0),
(154, 'conversations', 49, '{\"conversation_id\":49,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 22:55:08', NULL, NULL, 0),
(155, 'conversations', 60, '{\"conversation_id\":60,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 22:55:11', NULL, NULL, 0),
(156, 'conversations', 19, '{\"conversation_id\":19,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 22:55:13', NULL, NULL, 0),
(157, 'conversations', 76, '{\"conversation_id\":76,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 22:55:17', NULL, NULL, 0),
(158, 'conversations', 62, '{\"conversation_id\":62,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 22:55:22', NULL, NULL, 0),
(159, 'conversations', 54, '{\"conversation_id\":54,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 23:30:38', NULL, NULL, 0),
(160, 'conversations', 20, '{\"conversation_id\":20,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 23:30:42', NULL, NULL, 0),
(161, 'conversations', 14, '{\"conversation_id\":14,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 23:30:46', NULL, NULL, 0),
(162, 'conversations', 2, '{\"conversation_id\":2,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:33:25', NULL, NULL, 0),
(163, 'conversations', 49, '{\"conversation_id\":49,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:33:28', NULL, NULL, 0),
(164, 'conversations', 43, '{\"conversation_id\":43,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:36:29', NULL, NULL, 0),
(165, 'conversations', 43, '{\"conversation_id\":43,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:36:32', NULL, NULL, 0),
(166, 'conversations', 62, '{\"conversation_id\":62,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:36:36', NULL, NULL, 0),
(167, 'conversations', 71, '{\"conversation_id\":71,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:36:39', NULL, NULL, 0),
(168, 'conversations', 76, '{\"conversation_id\":76,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:36:43', NULL, NULL, 0),
(169, 'conversations', 60, '{\"conversation_id\":60,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:36:49', NULL, NULL, 0),
(170, 'conversations', 6, '{\"conversation_id\":6,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:36:52', NULL, NULL, 0),
(171, 'conversations', 16, '{\"conversation_id\":16,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:36:55', NULL, NULL, 0),
(172, 'conversations', 12, '{\"conversation_id\":12,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:36:58', NULL, NULL, 0),
(173, 'conversations', 12, '{\"conversation_id\":12,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:37:02', NULL, NULL, 0),
(174, 'conversations', 70, '{\"conversation_id\":70,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:37:05', NULL, NULL, 0),
(175, 'conversations', 43, '{\"conversation_id\":43,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:38:54', NULL, NULL, 0),
(176, 'conversations', 43, '{\"conversation_id\":43,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:38:58', NULL, NULL, 0),
(177, 'conversations', 43, '{\"conversation_id\":43,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:39:02', NULL, NULL, 0),
(178, 'conversations', 43, '{\"conversation_id\":43,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:39:06', NULL, NULL, 0),
(179, 'conversations', 76, '{\"conversation_id\":76,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:39:11', NULL, NULL, 0),
(180, 'conversations', 43, '{\"conversation_id\":43,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:39:16', NULL, NULL, 0),
(181, 'conversations', 74, '{\"conversation_id\":74,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:39:21', NULL, NULL, 0),
(182, 'conversations', 36, '{\"conversation_id\":36,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:39:27', NULL, NULL, 0),
(183, 'conversations', 80, '{\"conversation_id\":80,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:39:48', NULL, NULL, 0),
(184, 'conversations', 81, '{\"conversation_id\":81,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:40:10', NULL, NULL, 0),
(185, 'conversations', 82, '{\"conversation_id\":82,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-23 23:40:12', NULL, NULL, 0),
(186, 'conversation', 19, '{\"conversation_id\":19,\"deleted_by_department\":11,\"messages_count\":8}', 11, NULL, 0, '2026-05-23 23:45:18', NULL, NULL, 0),
(187, 'conversations', 6, '{\"conversation_id\":6,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:52:43', NULL, NULL, 0),
(188, 'conversations', 12, '{\"conversation_id\":12,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:52:47', NULL, NULL, 0),
(189, 'conversations', 16, '{\"conversation_id\":16,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:52:50', NULL, NULL, 0),
(190, 'conversations', 70, '{\"conversation_id\":70,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:52:53', NULL, NULL, 0),
(191, 'conversations', 60, '{\"conversation_id\":60,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-23 23:52:56', NULL, NULL, 0),
(192, 'conversations', 91, '{\"conversation_id\":91,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-23 23:57:16', NULL, NULL, 0),
(193, 'conversations', 61, '{\"conversation_id\":61,\"deleted_by_department\":6,\"deleted_by_name\":\"Unknown\"}', 6, NULL, 0, '2026-05-24 00:12:28', NULL, NULL, 0),
(194, 'conversations', 96, '{\"conversation_id\":96,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:14:26', NULL, NULL, 0),
(195, 'conversations', 83, '{\"conversation_id\":83,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:14:42', NULL, NULL, 0),
(196, 'conversations', 7, '{\"conversation_id\":7,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:14:45', NULL, NULL, 0),
(197, 'conversations', 15, '{\"conversation_id\":15,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:14:48', NULL, NULL, 0),
(198, 'conversations', 66, '{\"conversation_id\":66,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:14:51', NULL, NULL, 0),
(199, 'conversations', 67, '{\"conversation_id\":67,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:14:53', NULL, NULL, 0),
(200, 'conversations', 64, '{\"conversation_id\":64,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:15:16', NULL, NULL, 0),
(201, 'conversations', 91, '{\"conversation_id\":91,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:15:18', NULL, NULL, 0),
(202, 'conversations', 99, '{\"conversation_id\":99,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:15:20', NULL, NULL, 0),
(203, 'conversations', 102, '{\"conversation_id\":102,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:15:23', NULL, NULL, 0),
(204, 'conversations', 118, '{\"conversation_id\":118,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:16', NULL, NULL, 0),
(205, 'conversations', 118, '{\"conversation_id\":118,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:19', NULL, NULL, 0),
(206, 'conversations', 117, '{\"conversation_id\":117,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:22', NULL, NULL, 0),
(207, 'conversations', 117, '{\"conversation_id\":117,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:25', NULL, NULL, 0),
(208, 'conversations', 115, '{\"conversation_id\":115,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:33', NULL, NULL, 0),
(209, 'conversations', 114, '{\"conversation_id\":114,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:35', NULL, NULL, 0),
(210, 'conversations', 113, '{\"conversation_id\":113,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:38', NULL, NULL, 0),
(211, 'conversations', 112, '{\"conversation_id\":112,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:40', NULL, NULL, 0);
INSERT INTO `recycle_bin` (`id`, `original_table`, `original_id`, `deleted_data`, `deleted_by_department_id`, `deleted_by_user_id`, `deleted_by_admin`, `deleted_at`, `restored_at`, `restored_by`, `permanently_deleted`) VALUES
(212, 'conversations', 111, '{\"conversation_id\":111,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:43', NULL, NULL, 0),
(213, 'conversations', 116, '{\"conversation_id\":116,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:45', NULL, NULL, 0),
(214, 'conversations', 110, '{\"conversation_id\":110,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:49', NULL, NULL, 0),
(215, 'conversations', 109, '{\"conversation_id\":109,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:51', NULL, NULL, 0),
(216, 'conversations', 109, '{\"conversation_id\":109,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:53', NULL, NULL, 0),
(217, 'conversations', 108, '{\"conversation_id\":108,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:55', NULL, NULL, 0),
(218, 'conversations', 107, '{\"conversation_id\":107,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:29:57', NULL, NULL, 0),
(219, 'conversations', 106, '{\"conversation_id\":106,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:30:00', NULL, NULL, 0),
(220, 'conversations', 119, '{\"conversation_id\":119,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:30', NULL, NULL, 0),
(221, 'conversations', 118, '{\"conversation_id\":118,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:32', NULL, NULL, 0),
(222, 'conversations', 117, '{\"conversation_id\":117,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:34', NULL, NULL, 0),
(223, 'conversations', 116, '{\"conversation_id\":116,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:36', NULL, NULL, 0),
(224, 'conversations', 115, '{\"conversation_id\":115,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:38', NULL, NULL, 0),
(225, 'conversations', 114, '{\"conversation_id\":114,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:42', NULL, NULL, 0),
(226, 'conversations', 113, '{\"conversation_id\":113,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:44', NULL, NULL, 0),
(227, 'conversations', 112, '{\"conversation_id\":112,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:47', NULL, NULL, 0),
(228, 'conversations', 111, '{\"conversation_id\":111,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:49', NULL, NULL, 0),
(229, 'conversations', 110, '{\"conversation_id\":110,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:51', NULL, NULL, 0),
(230, 'conversations', 109, '{\"conversation_id\":109,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:53', NULL, NULL, 0),
(231, 'conversations', 108, '{\"conversation_id\":108,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:56', NULL, NULL, 0),
(232, 'conversations', 107, '{\"conversation_id\":107,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:34:59', NULL, NULL, 0),
(233, 'conversations', 106, '{\"conversation_id\":106,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:35:01', NULL, NULL, 0),
(234, 'conversations', 104, '{\"conversation_id\":104,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:35:22', NULL, NULL, 0),
(235, 'conversations', 119, '{\"conversation_id\":119,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:35:40', NULL, NULL, 0),
(236, 'conversations', 121, '{\"conversation_id\":121,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:35:43', NULL, NULL, 0),
(237, 'conversations', 120, '{\"conversation_id\":120,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:35:46', NULL, NULL, 0),
(238, 'conversations', 120, '{\"conversation_id\":120,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:35:49', NULL, NULL, 0),
(239, 'conversations', 104, '{\"conversation_id\":104,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:35:53', NULL, NULL, 0),
(240, 'conversations', 123, '{\"conversation_id\":123,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:39:55', NULL, NULL, 0),
(241, 'conversations', 122, '{\"conversation_id\":122,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:39:57', NULL, NULL, 0),
(242, 'conversations', 121, '{\"conversation_id\":121,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:39:59', NULL, NULL, 0),
(243, 'conversations', 120, '{\"conversation_id\":120,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:40:02', NULL, NULL, 0),
(244, 'conversations', 125, '{\"conversation_id\":125,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:41:10', NULL, NULL, 0),
(245, 'conversations', 124, '{\"conversation_id\":124,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:41:12', NULL, NULL, 0),
(246, 'conversations', 125, '{\"conversation_id\":125,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:41:33', NULL, NULL, 0),
(247, 'conversations', 124, '{\"conversation_id\":124,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:41:36', NULL, NULL, 0),
(248, 'conversations', 124, '{\"conversation_id\":124,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:41:39', NULL, NULL, 0),
(249, 'conversations', 123, '{\"conversation_id\":123,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:41:42', NULL, NULL, 0),
(250, 'conversations', 123, '{\"conversation_id\":123,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:41:45', NULL, NULL, 0),
(251, 'conversations', 122, '{\"conversation_id\":122,\"deleted_by_department\":9,\"deleted_by_name\":\"Unknown\"}', 9, NULL, 0, '2026-05-24 00:41:47', NULL, NULL, 0),
(252, 'messages', 402, '{\"message\": \"c\", \"sender_dept\": 1, \"receiver_dept\": 9}', 1, NULL, 1, '2026-05-24 00:47:19', NULL, NULL, 0),
(253, 'messages', 400, '{\"message\": \"cc\", \"sender_dept\": 1, \"receiver_dept\": 9}', 1, NULL, 1, '2026-05-24 00:47:22', NULL, NULL, 0),
(254, 'messages', 398, '{\"message\": \"c\", \"sender_dept\": 1, \"receiver_dept\": 9}', 1, NULL, 1, '2026-05-24 00:47:25', NULL, NULL, 0),
(255, 'messages', 396, '{\"message\": \"a\", \"sender_dept\": 1, \"receiver_dept\": 9}', 1, NULL, 1, '2026-05-24 00:47:27', NULL, NULL, 0),
(256, 'messages', 397, '{\"message\": \"a\", \"sender_dept\": 1, \"receiver_dept\": 9}', 1, NULL, 1, '2026-05-24 00:47:30', NULL, NULL, 0),
(257, 'messages', 399, '{\"message\": \"c\", \"sender_dept\": 1, \"receiver_dept\": 9}', 1, NULL, 1, '2026-05-24 00:47:34', NULL, NULL, 0),
(258, 'messages', 401, '{\"message\": \"s\", \"sender_dept\": 1, \"receiver_dept\": 9}', 1, NULL, 1, '2026-05-24 00:47:37', NULL, NULL, 0),
(259, 'conversations', 41, '{\"conversation_id\":41,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 00:48:17', NULL, NULL, 0),
(260, 'conversations', 57, '{\"conversation_id\":57,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-24 00:50:45', NULL, NULL, 0),
(261, 'conversations', 57, '{\"conversation_id\":57,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-24 00:55:49', NULL, NULL, 0),
(262, 'conversations', 41, '{\"conversation_id\":41,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 01:00:18', NULL, NULL, 0),
(263, 'conversations', 77, '{\"conversation_id\":77,\"deleted_by_department\":7,\"deleted_by_name\":\"Unknown\"}', 7, NULL, 0, '2026-05-24 01:19:31', NULL, NULL, 0),
(264, 'conversations', 39, '{\"conversation_id\":39,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 01:20:42', NULL, NULL, 0),
(265, 'conversations', 73, '{\"conversation_id\":73,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:22:46', NULL, NULL, 0),
(266, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:22:48', NULL, NULL, 0),
(267, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:22:51', NULL, NULL, 0),
(268, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:22:53', NULL, NULL, 0),
(269, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:22:57', NULL, NULL, 0),
(270, 'conversations', 44, '{\"conversation_id\":44,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:00', NULL, NULL, 0),
(271, 'conversations', 78, '{\"conversation_id\":78,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:03', NULL, NULL, 0),
(272, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:06', NULL, NULL, 0),
(273, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:10', NULL, NULL, 0),
(274, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:14', NULL, NULL, 0),
(275, 'conversations', 8, '{\"conversation_id\":8,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:18', NULL, NULL, 0),
(276, 'conversations', 72, '{\"conversation_id\":72,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:25', NULL, NULL, 0),
(277, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:28', NULL, NULL, 0),
(278, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:30', NULL, NULL, 0),
(279, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:32', NULL, NULL, 0),
(280, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":12,\"deleted_by_name\":\"Unknown\"}', 12, NULL, 0, '2026-05-24 01:23:35', NULL, NULL, 0),
(281, 'conversations', 19, '{\"conversation_id\":19,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-24 01:25:40', NULL, NULL, 0),
(282, 'conversations', 19, '{\"conversation_id\":19,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-24 01:25:43', NULL, NULL, 0),
(283, 'conversations', 2, '{\"conversation_id\":2,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-24 01:25:51', NULL, NULL, 0),
(284, 'conversations', 2, '{\"conversation_id\":2,\"deleted_by_department\":11,\"deleted_by_name\":\"Unknown\"}', 11, NULL, 0, '2026-05-24 01:25:56', NULL, NULL, 0),
(285, 'conversations', 1, '{\"conversation_id\":1,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 01:33:47', NULL, NULL, 0),
(286, 'conversations', 1, '{\"conversation_id\":1,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 01:38:43', NULL, NULL, 0),
(287, 'conversations', 1, '{\"conversation_id\":1,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 01:42:24', NULL, NULL, 0),
(288, 'conversations', 97, '{\"conversation_id\":97,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-24 01:48:49', NULL, NULL, 0),
(289, 'conversations', 68, '{\"conversation_id\":68,\"deleted_by_department\":10,\"deleted_by_name\":\"Unknown\"}', 10, NULL, 0, '2026-05-24 02:10:52', NULL, NULL, 0),
(290, 'conversations', 13, '{\"conversation_id\":13,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-24 11:36:04', NULL, NULL, 0),
(291, 'conversations', 36, '{\"conversation_id\":36,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-24 11:36:07', NULL, NULL, 0),
(292, 'conversations', 97, '{\"conversation_id\":97,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 11:37:27', NULL, NULL, 0),
(293, 'conversations', 13, '{\"conversation_id\":13,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-24 11:41:36', NULL, NULL, 0),
(294, 'conversations', 13, '{\"conversation_id\":13,\"deleted_by_department\":4,\"deleted_by_name\":\"Unknown\"}', 4, NULL, 0, '2026-05-24 11:46:14', NULL, NULL, 0),
(295, 'conversations', 105, '{\"conversation_id\":105,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 12:38:27', NULL, NULL, 0),
(296, 'conversations', 105, '{\"conversation_id\":105,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 12:38:33', NULL, NULL, 0),
(297, 'conversations', 53, '{\"conversation_id\":53,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 12:39:13', NULL, NULL, 0),
(298, 'conversations', 11, '{\"conversation_id\":11,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 12:39:19', NULL, NULL, 0),
(299, 'conversations', 53, '{\"conversation_id\":53,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 13:05:06', NULL, NULL, 0),
(300, 'conversations', 1, '{\"conversation_id\":1,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 13:10:27', NULL, NULL, 0),
(301, 'conversations', 1, '{\"conversation_id\":1,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 13:22:10', NULL, NULL, 0),
(302, 'conversations', 54, '{\"conversation_id\":54,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-24 13:38:21', NULL, NULL, 0),
(303, 'conversations', 54, '{\"conversation_id\":54,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-24 13:38:55', NULL, NULL, 0),
(304, 'conversations', 50, '{\"conversation_id\":50,\"deleted_by_department\":2,\"deleted_by_name\":\"Unknown\"}', 2, NULL, 0, '2026-05-24 13:38:59', NULL, NULL, 0),
(305, 'conversations', 52, '{\"conversation_id\":52,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 13:48:28', NULL, NULL, 0),
(306, 'conversations', 75, '{\"conversation_id\":75,\"deleted_by_department\":3,\"deleted_by_name\":\"Unknown\"}', 3, NULL, 0, '2026-05-24 13:48:34', NULL, NULL, 0),
(307, 'conversations', 128, '{\"conversation_id\":128,\"deleted_by_department\":5,\"deleted_by_name\":\"Unknown\"}', 5, NULL, 0, '2026-05-24 14:31:52', NULL, NULL, 0),
(308, 'conversations', 10, '{\"conversation_id\":10,\"deleted_by_department\":5,\"deleted_by_name\":\"Unknown\"}', 5, NULL, 0, '2026-05-24 14:41:53', NULL, NULL, 0),
(309, 'conversations', 37, '{\"conversation_id\":37,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 14:49:39', NULL, NULL, 0),
(310, 'conversations', 1, '{\"conversation_id\":1,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 14:59:09', NULL, NULL, 0),
(311, 'conversations', 127, '{\"conversation_id\":127,\"deleted_by_department\":5,\"deleted_by_name\":\"Unknown\"}', 5, NULL, 0, '2026-05-24 15:01:03', NULL, NULL, 0),
(312, 'conversations', 13, '{\"conversation_id\":13,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 15:05:48', NULL, NULL, 0),
(313, 'conversations', 1, '{\"conversation_id\":1,\"deleted_by_department\":1,\"deleted_by_name\":\"Unknown\"}', NULL, NULL, 1, '2026-05-24 15:05:52', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `period` enum('daily','weekly','monthly','quarterly','annual') DEFAULT 'monthly',
  `content` text DEFAULT NULL,
  `status` enum('draft','sent') DEFAULT 'draft',
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `is_viewed_by_admin` tinyint(1) DEFAULT 0,
  `sent_from_dept` int(11) DEFAULT NULL,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_department` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `sent_to_department` int(11) DEFAULT NULL,
  `is_viewed_by_department` tinyint(4) DEFAULT 0,
  `sent_from_department` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT 'System',
  `file_size` int(11) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `title`, `period`, `content`, `status`, `department_id`, `created_at`, `updated_at`, `is_viewed_by_admin`, `sent_from_dept`, `deleted_by_admin`, `deleted_by_department`, `deleted_at`, `sent_to_department`, `is_viewed_by_department`, `sent_from_department`, `file_path`, `file_type`, `file_name`, `created_by`, `file_size`, `updated_by`, `is_deleted`) VALUES
(1, 'Q2 2024 Executive Summary', 'quarterly', 'Executive summary for Q2 2024 showing overall company performance. Revenue increased by 15% compared to Q1. All departments performed well. Key achievements: Modern Villa completion, New client acquisition (5 major clients), Digital transformation 60% complete. Challenges: Supply chain disruptions, Staff turnover in IT department. Recommendations: Increase marketing budget by 20%, Hire 5 additional IT staff, Implement remote work policy.', 'sent', 1, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(2, 'Annual Performance Review 2024', 'annual', 'GeoTraverse Annual Performance Report 2024\n\nRevenue: TZS 2.5 Billion (↑12%)\nExpenses: TZS 1.8 Billion\nNet Profit: TZS 700 Million\n\nDepartment Performance:\n- Construction: Best performer\n- Aluminium: Above target\n- Sales: Met target\n- Others: On track\n\nStaff Satisfaction: 85%\nCustomer Satisfaction: 92%\n\nProjects Completed: 24\nNew Projects: 18\n\nOutlook for 2025: Positive with projected growth of 20%', 'sent', 1, '2026-05-22 21:10:41', '2026-05-24 13:09:18', 0, NULL, 0, 0, NULL, 4, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(3, 'Risk Assessment Report', 'monthly', 'Monthly risk assessment for June 2024\n\nIdentified Risks:\n1. Supply chain disruption - High\n2. Currency fluctuation - Medium\n3. Staff retention - Medium\n4. Regulatory changes - Low\n\nMitigation Strategies:\n- Diversify suppliers\n- Hedging strategy\n- Improve employee benefits\n- Regular compliance audits\n\nRisk Score: 65/100 (Moderate)\nActions Required: 8 high priority items', 'sent', 1, '2026-05-22 21:10:41', '2026-05-24 13:08:54', 0, NULL, 0, 0, NULL, 4, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(4, 'Digital Transformation Progress', 'monthly', 'Digital Transformation Progress Report - June 2024\n\nOverall Progress: 65%\n\nCompleted:\n- ERP Implementation (80%)\n- CRM System (100%)\n- Document Management (60%)\n\nIn Progress:\n- Mobile App Development (40%)\n- Cloud Migration (30%)\n- Cybersecurity Enhancement (50%)\n\nBudget Utilization: TZS 450M / TZS 600M (75%)\n\nNext Milestones: Complete ERP by August, Launch mobile app by September', 'sent', 1, '2026-05-22 21:10:41', NULL, 0, NULL, 1, 0, '2026-05-23 00:08:33', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(5, 'June 2024 Financial Statement', 'monthly', 'FINANCIAL STATEMENT - JUNE 2024\n\nIncome Statement:\nRevenue: TZS 185,000,000\nCOGS: TZS 95,000,000\nGross Profit: TZS 90,000,000\nOperating Expenses: TZS 35,000,000\nNet Profit: TZS 55,000,000\n\nBalance Sheet:\nAssets: TZS 1.2 Billion\nLiabilities: TZS 450 Million\nEquity: TZS 750 Million\n\nCash Flow: Positive TZS 120 Million\n\nKey Ratios:\nCurrent Ratio: 2.5\nQuick Ratio: 1.8\nDebt-to-Equity: 0.6\nROE: 7.3%', 'sent', 2, '2026-05-22 21:10:41', '2026-05-24 12:47:35', 0, NULL, 0, 0, NULL, 4, 1, 2, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(6, 'Budget vs Actual Analysis', 'quarterly', 'Q2 2024 Budget vs Actual Analysis\n\nBudget: TZS 500 Million\nActual: TZS 485 Million\nVariance: -3% (Favorable)\n\nDepartment Analysis:\n- Construction: Under budget by 5%\n- Aluminium: Over budget by 2%\n- Sales: Under budget by 8%\n- Admin: On budget\n\nMajor Variances:\n- Materials cost lower than expected: -TZS 15M\n- Labor cost higher: +TZS 8M\n- Marketing savings: -TZS 5M\n\nRecommendations:\n- Continue cost control measures\n- Investigate Aluminium overspend\n- Reallocate savings to R&D', 'sent', 2, '2026-05-22 21:10:41', '2026-05-24 23:17:40', 0, NULL, 0, 1, '2026-05-25 02:17:40', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(7, 'Cash Flow Forecast Q3 2024', 'monthly', 'CASH FLOW FORECAST - Q3 2024\n\nJuly: +TZS 50M (Expected receipts: TZS 180M, Payments: TZS 130M)\nAugust: +TZS 35M (Receipts: TZS 160M, Payments: TZS 125M)\nSeptember: +TZS 45M (Receipts: TZS 175M, Payments: TZS 130M)\n\nTotal Q3 Projection: +TZS 130M\n\nLarge Payments Due:\n- Supplier payments: TZS 200M\n- Salaries: TZS 90M\n- Tax payments: TZS 45M\n- Loan repayment: TZS 30M\n\nLiquidity Position: Strong\nRecommended: Invest excess cash in short-term deposits', 'draft', 2, '2026-05-22 21:10:41', '2026-05-24 23:17:33', 0, NULL, 0, 1, '2026-05-25 02:17:33', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(8, 'Tax Compliance Report', 'quarterly', 'Q2 2024 Tax Compliance Report\n\nPAYE Remitted: TZS 25,000,000\nVAT Remitted: TZS 15,000,000\nCorporate Tax Estimate: TZS 40,000,000\nWithholding Tax: TZS 5,000,000\n\nTotal Taxes Paid: TZS 85,000,000\n\nCompliance Status:\n- All filings submitted on time\n- No penalties incurred\n- Audit ready\n\nUpcoming Tax Deadlines:\n- July 20: VAT return\n- July 30: PAYE\n- August 15: Corporate tax installment', 'sent', 2, '2026-05-22 21:10:41', '2026-05-24 23:17:37', 0, NULL, 0, 1, '2026-05-25 02:17:37', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(9, 'Sales Performance Report - June', 'monthly', 'SALES PERFORMANCE REPORT - JUNE 2024\n\nTotal Sales: TZS 250,000,000\nTarget: TZS 230,000,000\nAchievement: 108% (EXCEEDED)\n\nSales by Category:\n- Construction Services: TZS 120M (48%)\n- Aluminium Products: TZS 65M (26%)\n- Bricks & Timber: TZS 40M (16%)\n- Consulting: TZS 25M (10%)\n\nTop Sales Rep:\n1. John Mwita: TZS 85M\n2. Sarah Kijazi: TZS 70M\n3. James Ndege: TZS 55M\n\nNew Clients Acquired: 12\nLead Conversion Rate: 32%\n\nForecast for July: TZS 260M (target: TZS 240M)', 'sent', 3, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(10, 'Marketing Campaign ROI Analysis', 'quarterly', 'Q2 2024 Marketing Campaign ROI Analysis\n\nTotal Campaign Spend: TZS 45,000,000\nRevenue Generated: TZS 180,000,000\nROI: 300%\n\nCampaign Performance:\n1. Digital Ads:\n   - Spend: TZS 15M\n   - Revenue: TZS 80M\n   - ROI: 433%\n\n2. TV/Radio:\n   - Spend: TZS 12M\n   - Revenue: TZS 45M\n   - ROI: 275%\n\n3. Events:\n   - Spend: TZS 10M\n   - Revenue: TZS 35M\n   - ROI: 250%\n\n4. Print Media:\n   - Spend: TZS 8M\n   - Revenue: TZS 20M\n   - ROI: 150%\n\nRecommendations:\n- Increase digital ad spend by 50%\n- Reduce print media by 30%\n- Add social media influencers', 'sent', 3, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(11, 'Customer Satisfaction Survey', 'monthly', 'Customer Satisfaction Survey - June 2024\n\nSurvey Sample: 200 customers\nResponse Rate: 65%\n\nOverall Satisfaction: 4.6/5 (↑0.3 from May)\n\nBreakdown:\n- Product Quality: 4.7/5\n- Delivery Time: 4.4/5\n- Customer Service: 4.8/5\n- Pricing: 4.3/5\n- Communication: 4.5/5\n\nNet Promoter Score (NPS): +72 (Excellent)\n\nCustomer Complaints: 8 (↓5 from May)\n- Delivery delays: 3\n- Quality issues: 2\n- Billing errors: 2\n- Communication: 1\n\nAll complaints resolved within 48 hours', 'sent', 3, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(12, 'Competitor Analysis Report', 'quarterly', 'Q2 2024 Competitor Analysis\n\nMain Competitors:\n1. ABC Construction Ltd\n   - Market Share: 25%\n   - Strengths: Low prices, Fast delivery\n   - Weaknesses: Quality issues, Poor customer service\n\n2. XYZ Building Solutions\n   - Market Share: 18%\n   - Strengths: Innovative products, Strong brand\n   - Weaknesses: High prices, Limited reach\n\n3. BuildTech Ltd\n   - Market Share: 15%\n   - Strengths: Good reputation, Experienced team\n   - Weaknesses: Slow response, Outdated technology\n\nGeoTraverse Market Share: 22% (↑3%)\n\nOpportunities:\n- Expand to upcountry regions\n- Introduce new product lines\n- Enhance digital presence\n\nThreats:\n- New entrants from China\n- Rising material costs', 'draft', 3, '2026-05-22 21:10:41', '2026-06-03 18:06:56', 0, NULL, 0, 1, '2026-06-03 21:06:56', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(13, 'Operations Performance Dashboard', 'monthly', 'OPERATIONS DASHBOARD - JUNE 2024\n\nProject Completion Rate: 85% (Target: 80%)\nOn-Time Delivery: 92% (Target: 90%)\nQuality Score: 4.5/5 (Target: 4.3)\n\nResource Utilization:\n- Human Resources: 88%\n- Equipment: 75%\n- Vehicles: 82%\n- Office Space: 90%\n\nEfficiency Metrics:\n- Average Project Duration: 45 days (↓5 days)\n- Cost per Project: TZS 2.5M (↓0.3M)\n- Staff Productivity: ↑12%\n\nAreas for Improvement:\n- Equipment maintenance schedule\n- Staff training on new software\n- Communication between departments', 'sent', 4, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(14, 'Staff Performance Review - Q2', 'quarterly', 'STAFF PERFORMANCE REVIEW - Q2 2024\n\nDepartment Performance:\n⭐⭐⭐⭐⭐ Construction (Top performer)\n⭐⭐⭐⭐ Aluminium (Excellent)\n⭐⭐⭐⭐ Sales (Good)\n⭐⭐⭐ Finance (Satisfactory)\n⭐⭐⭐ Admin (Needs improvement)\n\nTop Performers:\n1. John Mwita (Sales) - Exceeded target by 25%\n2. Ali Hassan (Aluminium) - Zero defects\n3. Peter Tabora (Bricks) - Increased production by 30%\n\nTraining Needs:\n- Project management (12 staff)\n- Customer service (8 staff)\n- Technical skills (15 staff)\n\nStaff Turnover Rate: 8% (Industry avg: 12%)\nEmployee Satisfaction: 82% (↑5%)', 'sent', 4, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(15, 'Strategic Initiatives Progress', 'monthly', 'Strategic Initiatives Progress Report - June 2024\n\nInitiative 1: Digital Transformation\nProgress: 65% (On track)\nBudget: TZS 500M / TZS 600M\nNext milestone: ERP completion by August\n\nInitiative 2: Market Expansion\nProgress: 40% (Behind schedule)\nBudget: TZS 200M / TZS 300M\nChallenge: Regulatory approvals delayed\nAction: Expedite permit applications\n\nInitiative 3: Talent Development\nProgress: 55% (On track)\nBudget: TZS 80M / TZS 120M\nCompleted: Training programs for 45 staff\n\nInitiative 4: Sustainability\nProgress: 30% (Just started)\nBudget: TZS 50M / TZS 150M\nPlan: Implement green practices by Q4', 'draft', 4, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(16, 'Risk Management Update', 'monthly', 'Risk Management Report - June 2024\n\nRisk Register Updates:\n\nHIGH RISKS (3):\n1. Supply chain disruption\n   - Mitigation: Diversified suppliers\n   - Status: Monitoring\n\n2. Staff turnover in key positions\n   - Mitigation: Retention bonuses\n   - Status: Improving\n\n3. Project delays\n   - Mitigation: Buffer time added\n   - Status: Under control\n\nMEDIUM RISKS (5):\n- Currency fluctuation\n- Regulatory changes\n- Technology obsolescence\n- Competitive pressure\n- Cybersecurity threats\n\nLOW RISKS (4):\n- Office security\n- Minor accidents\n- IT downtime\n- Supplier disputes\n\nOverall Risk Score: 45/100 (Moderate)\nAction Items: 8 (3 high priority)', 'sent', 4, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(17, 'Monthly Visitor Statistics - June 2024', 'monthly', 'VISITOR STATISTICS - JUNE 2024\n\nTotal Visitors: 245\nNew Visitors: 178\nReturning Visitors: 67\n\nVisitors by Department:\n- Manager: 52 (21%)\n- Finance: 45 (18%)\n- Construction: 38 (16%)\n- Sales: 35 (14%)\n- Aluminium: 28 (11%)\n- Other Departments: 47 (20%)\n\nPeak Days: Monday (65), Tuesday (58)\nPeak Hours: 10 AM - 12 PM (85 visitors)\n\nAverage Wait Time: 12 minutes\nVisitor Satisfaction: 92%\n\nAppointments Scheduled: 180\nNo-shows: 15 (8%)\n\nIssues Resolved: 230\nPending Issues: 15\n\nRecommendations:\n- Add more reception staff during peak hours\n- Implement appointment reminder SMS\n- Expand waiting area', 'sent', 5, '2026-05-22 21:10:41', '2026-06-03 13:01:19', 0, NULL, 0, 1, '2026-06-03 16:01:19', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(18, 'Office Supplies Inventory Report', 'monthly', 'Office Supplies Inventory - June 2024\n\nCurrent Stock Levels:\n- A4 Paper: 45 boxes (Good)\n- Printer Toner: 12 units (Low - Reorder)\n- Pens: 200 pcs (Good)\n- Folders: 85 pcs (Good)\n- Envelopes: 150 pcs (Low)\n- Staplers: 15 pcs (Good)\n\nMonthly Consumption:\n- Paper: 15 boxes (TZS 750,000)\n- Toner: 8 units (TZS 1,200,000)\n- Pens: 80 pcs (TZS 40,000)\n\nTotal Supplies Cost: TZS 2,150,000\nBudget: TZS 2,500,000\nVariance: -TZS 350,000 (Favorable)\n\nReorder Recommendations:\n- Toner: Order 20 units (TZS 3,000,000)\n- Envelopes: Order 200 pcs (TZS 100,000)\n\nSupplier Performance:\n- Delivery time: 2 days (Good)\n- Quality: Excellent\n- Pricing: Competitive', 'draft', 5, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(19, 'Meeting Minutes Summary', 'weekly', 'WEEKLY MEETING MINUTES - Week 25, 2024\n\nDate: June 24, 2024\nAttendees: Department Heads (12)\n\nKey Decisions:\n1. Approved Q3 budget of TZS 650M\n2. New project management software to be implemented\n3. Staff training scheduled for July 15-20\n\nAction Items:\n1. Finance to release Q2 reports by July 5 (Assigned: Finance)\n2. IT to set up new software by July 30 (Assigned: IT)\n3. HR to organize training logistics (Assigned: HR)\n\nDiscussions:\n- Modern Villa project 85% complete\n- New client acquisition strategy approved\n- Office renovation planned for August\n\nNext Meeting: July 1, 2024 at 9:00 AM\n\nMinutes Prepared By: Executive Secretary\nApproved By: General Manager', 'sent', 5, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(20, 'Document Processing Report', 'monthly', 'DOCUMENT PROCESSING REPORT - JUNE 2024\n\nDocuments Processed: 1,250\n- Incoming: 680\n- Outgoing: 570\n\nDocument Types:\n- Contracts: 85\n- Invoices: 320\n- Reports: 150\n- Letters: 250\n- Memos: 180\n- Other: 265\n\nAverage Processing Time: 4 hours\nTurnaround Time: 24 hours (Target: 48 hours)\n\nDigital vs Physical:\n- Digital: 850 (68%)\n- Physical: 400 (32%)\n\nArchiving:\n- Archived: 1,050 documents\n- Pending archiving: 200\n\nDigital Storage Used: 45 GB / 100 GB\n\nRecommendations:\n- Increase digital adoption target to 80%\n- Implement document tracking system\n- Schedule regular archiving', 'sent', 5, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(21, 'Aluminium Production Report - June', 'monthly', 'ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4% (↓1%)\n- Customer returns: 2 (↓50%)\n\nEfficiency: 96% (↑2%)\nStaff Overtime: 45 hours\n\nCertifications:\n- ISO 9001 audit scheduled\n- TBS certification renewed', 'sent', 7, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(22, 'Aluminium Inventory Status', 'weekly', 'ALUMINIUM INVENTORY - WEEK 26, 2024\n\nCurrent Stock:\n- Raw Aluminium Sheets: 8,500 kg\n- Glass Panels: 1,200 pcs\n- Hardware Accessories: 2,500 sets\n- Powder Coating: 500 kg\n\nMinimum Stock Levels:\n- Aluminium sheets: 5,000 kg (Current: 8,500 kg - OK)\n- Glass panels: 800 pcs (Current: 1,200 pcs - OK)\n- Hardware: 1,500 sets (Current: 2,500 sets - OK)\n\nProducts Ready for Dispatch:\n- Window frames: 450 units\n- Door frames: 200 units\n- Custom orders: 25 units\n\nPending Orders: 40 (Value: TZS 12M)\nProduction Capacity Used: 85%\n\nReorder Recommendations:\n- Order 10,000 kg aluminium sheets\n- Order 2,000 pcs glass panels', 'draft', 7, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(23, 'Weekly Construction Progress - Week 26', 'weekly', 'CONSTRUCTION PROGRESS REPORT - WEEK 26, 2024\n\nProject: Modern Villa - Kigamboni\nProgress: 85% (↑5%)\nCompleted: Structure, Roofing, Plumbing\nIn Progress: Electrical, Finishing, Landscaping\nNext Week: Painting, Tiling\n\nProject: Commercial Complex - Pwani\nProgress: 60% (↑10%)\nCompleted: Ground floor to Floor 3\nIn Progress: Floor 4, 5 construction\n\nProject: Residential Estate - Mbezi\nProgress: 25% (↑10%)\nCompleted: Site clearing, Foundation\nIn Progress: Ground floor framing\n\nSafety Report:\n- Incidents: 0\n- Near misses: 2 (reported)\n- Safety training completed: 45 workers\n\nWorkers on Site: 185\nEquipment: All operational\nMaterials: Adequate stock\n\nBudget Status: On track\nNext Milestone: Complete Modern Villa by July 15', 'sent', 11, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(24, 'Construction Materials Report', 'monthly', 'CONSTRUCTION MATERIALS REPORT - JUNE 2024\n\nMaterials Consumed:\n- Cement: 5,000 bags (TZS 75M)\n- Steel: 150 tons (TZS 225M)\n- Sand: 800 tons (TZS 40M)\n- Gravel: 600 tons (TZS 30M)\n- Bricks: 200,000 units (TZS 50M)\n- Timber: 500 pieces (TZS 25M)\n\nTotal Materials Cost: TZS 445M\nBudget: TZS 450M\nVariance: -TZS 5M (Favorable)\n\nSupplier Performance:\n- ABC Cement: On time, Good quality\n- XYZ Steel: Delayed 2 days, Acceptable quality\n- Local Sand Suppliers: Good\n\nInventory Levels:\n- Cement: 800 bags (15 days supply)\n- Steel: 25 tons (20 days supply)\n- Sand: 120 tons (10 days supply)\n\nRecommended Orders:\n- Cement: Order 1,500 bags\n- Steel: Order 30 tons\n- Sand: Order 100 tons', 'draft', 11, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(25, 'Title Deeds Processing Report - June', 'monthly', 'TITLE DEEDS REPORT - JUNE 2024\n\nNew Title Deeds Issued: 45\nTitle Transfers Processed: 25\nLand Surveys Completed: 18\nBoundary Adjustments: 12\n\nTotal Revenue: TZS 28,500,000\n\nProcessing Times:\n- New deeds: 5 days (Target: 7 days)\n- Transfers: 3 days (Target: 5 days)\n- Surveys: 7 days (Target: 10 days)\n\nPending Applications: 20\n- New deeds: 12\n- Transfers: 5\n- Surveys: 3\n\nCustomer Satisfaction: 94%\n\nDigital System Usage: 75%\n- Online applications: 150\n- Digital approvals: 120\n\nStaff Performance:\n- Applications processed per staff: 35\n- Accuracy rate: 98%\n\nChallenges:\n- System downtime (2 hours)\n- Staff training needed\n\nRecommendations:\n- Increase server capacity\n- Schedule refresher training', 'sent', 12, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(26, 'Land Registry Modernization Progress', 'quarterly', 'LAND REGISTRY MODERNIZATION - Q2 2024\n\nOverall Progress: 45%\n\nCompleted:\n- Digital platform design (100%)\n- Hardware procurement (80%)\n- Staff training (60%)\n\nIn Progress:\n- System development (40%)\n- Data migration (30%)\n- Testing phase (20%)\n\nBudget Utilization:\n- Allocated: TZS 450M\n- Spent: TZS 280M\n- Remaining: TZS 170M\n\nMilestones Achieved:\n1. Digital application portal launched\n2. Document scanning completed\n3. Integration with banks\n\nUpcoming Milestones:\n- System testing (July)\n- User acceptance (August)\n- Go-live (September)\n\nRisks:\n- Data migration delays\n- User resistance\n- Technical issues\n\nContingency Plan: Extended timeline to October', 'draft', 12, '2026-05-22 21:10:41', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(27, 'Bricks Production Report - June 2024', 'monthly', '📊 BRICKS PRODUCTION REPORT - JUNE 2024\r\n\r\nProduction Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Bricks Produced: 245,000 units\r\n• Standard Bricks: 180,000 units\r\n• Hollow Bricks: 45,000 units\r\n• Pavement Blocks: 20,000 units\r\n\r\nSales Performance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Bricks Sold: 210,000 units\r\n• Revenue: TZS 52,500,000\r\n• Top Customer: Mbezi Residential Estate (85,000 units)\r\n\r\nProduction Efficiency:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Target: 250,000 units\r\n• Achievement: 98%\r\n• Waste Rate: 3.2% (improved from 4.5%)\r\n\r\nRaw Materials Usage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Clay: 850 tons\r\n• Cement: 120 bags\r\n• Sand: 400 tons\r\n\r\nFinancial Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Production Cost: TZS 35,200,000\r\n• Revenue: TZS 52,500,000\r\n• Profit: TZS 17,300,000\r\n\r\nStaff Performance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Production Staff: 25\r\n• Machine Operators: 8\r\n• Quality Control: 4\r\n• Overtime Hours: 120 hours\r\n\r\nChallenges & Recommendations:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Clay supply shortage - Need new supplier\r\n2. Machine maintenance required\r\n3. Recommend hiring 2 additional workers\r\n\r\nNext Month Target: 280,000 units\r\n', 'sent', 6, '2024-06-30 07:00:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(28, 'Timber Processing Report - June 2024', 'monthly', '🌲 TIMBER PROCESSING REPORT - JUNE 2024\r\n\r\nProcessing Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Raw Logs Received: 450 pieces\r\n• Timber Processed: 380 pieces\r\n• Waste/Sawdust: 70 pieces\r\n\r\nTimber Products:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hardwood Planks: 180 pieces (TZS 18,000,000)\r\n• Softwood Planks: 120 pieces (TZS 8,400,000)\r\n• Treated Timber: 50 pieces (TZS 6,000,000)\r\n• Wood Beams: 30 pieces (TZS 3,600,000)\r\n\r\nInventory Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Current Stock: 250 pieces\r\n• Pending Orders: 180 pieces\r\n• Reorder Level: 100 pieces\r\n\r\nFinancial Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Processing Cost: TZS 15,200,000\r\n• Revenue: TZS 36,000,000\r\n• Net Profit: TZS 20,800,000\r\n\r\nSupplier Performance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Morogoro Timber Ltd: 60% supply\r\n• Iringa Wood Supply: 25% supply\r\n• Other Suppliers: 15% supply\r\n\r\nQuality Metrics:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Grade A: 75%\r\n• Grade B: 20%\r\n• Grade C: 5%\r\n• Customer Returns: 3 pieces (0.8%)\r\n\r\nSustainability Report:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Trees Planted: 500\r\n• Reforestation Area: 2.5 hectares\r\n• Recycling Rate: 85%\r\n\r\nPlans for July:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Increase processing capacity by 20%\r\n• New treatment facility installation\r\n', 'sent', 6, '2024-06-30 08:00:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(29, 'Weekly Bricks Production - Week 26', 'weekly', '🏭 WEEKLY BRICKS PRODUCTION REPORT (Week 26)\r\n\r\nDaily Production:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: 8,500 units\r\n• Tuesday: 9,200 units\r\n• Wednesday: 9,800 units\r\n• Thursday: 9,500 units\r\n• Friday: 8,900 units\r\n• Saturday: 7,500 units\r\n\r\nTotal Weekly: 53,400 units\r\n\r\nSales This Week:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Orders Completed: 12\r\n• Bricks Delivered: 48,000 units\r\n• Revenue Collected: TZS 12,000,000\r\n• Outstanding Payments: TZS 2,400,000\r\n\r\nEquipment Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Brick Machine 1: Operational\r\n• Brick Machine 2: Under maintenance (3 days)\r\n• Mixer: Operational\r\n• Kiln: Operational\r\n\r\nStaff Attendance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Present: 28/32 (87.5%)\r\n• Absent: 4 (sick leave)\r\n\r\nIssues Resolved:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Clay quality issue resolved\r\n✓ New moulds installed\r\n✓ Safety training completed\r\n\r\nNext Week Focus: Machine #2 repair completion\r\n', 'sent', 6, '2024-06-28 13:30:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(30, 'Aluminium Production Report - June 2024', 'monthly', '🔧 ALUMINIUM PRODUCTION REPORT - JUNE 2024\r\n\r\nProduction Volume:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Window Frames: 3,200 units (↑12%)\r\n• Door Frames: 1,800 units (↑15%)\r\n• Sliding Doors: 520 units (↑18%)\r\n• Curtain Walls: 45 units (↑25%)\r\n• Custom Fabrications: 85 units\r\n\r\nOrders Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Orders Completed: 42\r\n• Orders In Progress: 18\r\n• New Orders Received: 35\r\n• Order Value: TZS 48,500,000\r\n\r\nFinancial Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Revenue: TZS 42,500,000\r\n• Expenses: TZS 28,200,000\r\n• Profit: TZS 14,300,000\r\n• Profit Margin: 33.6%\r\n\r\nMaterials Consumption:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium Sheets: 22,500 kg\r\n• Glass Panels: 3,800 pieces\r\n• Hardware Sets: 4,200 sets\r\n• Powder Coating: 580 kg\r\n• Packaging Materials: 320 units\r\n\r\nQuality Control:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Defect Rate: 2.8% (target: 3%)\r\n• Rework Rate: 3.5%\r\n• Customer Satisfaction: 94%\r\n• Warranty Claims: 2\r\n\r\nEquipment Performance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Cutting Machines: 96% uptime\r\n• Welding Equipment: 92% uptime\r\n• Powder Coating Line: 100% uptime\r\n• Assembly Stations: 98% uptime\r\n\r\nInventory Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Raw Aluminium: 8,500 kg (15 days supply)\r\n• Glass: 1,200 pieces (20 days)\r\n• Hardware: 2,800 sets (25 days)\r\n\r\nProjects Completed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Modern Villa Kigamboni - 250 units\r\n2. Commercial Complex Pwani - 180 units\r\n3. School Project Kinondoni - 120 units\r\n\r\nStaff Performance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Production Staff: 18\r\n• Quality Control: 4\r\n• Installers: 12\r\n• Overtime Hours: 85 hours\r\n\r\nNext Month Projections:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Target Production: 4,000 units\r\n• New Equipment: 2 cutting machines\r\n• Staff Training: Advanced fabrication\r\n', 'sent', 7, '2024-06-30 06:30:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(31, 'Aluminium Inventory Report - June 2024', 'monthly', '📦 ALUMINIUM INVENTORY REPORT - JUNE 2024\r\n\r\nRaw Materials Inventory:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\nOpening Stock: 12,000 kg\r\nPurchases: 15,000 kg\r\nConsumption: 22,500 kg\r\nClosing Stock: 4,500 kg\r\n\r\nGlass Inventory:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\nOpening Stock: 2,500 pieces\r\nPurchases: 3,000 pieces\r\nConsumption: 3,800 pieces\r\nClosing Stock: 1,700 pieces\r\n\r\nHardware Inventory:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hinges: 3,500 sets (2,800 used)\r\n• Handles: 4,200 sets (3,500 used)\r\n• Locks: 2,800 sets (2,200 used)\r\n• Screws: 15,000 pieces (12,000 used)\r\n\r\nFinished Goods Inventory:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Window Frames: 450 units\r\n• Door Frames: 200 units\r\n• Sliding Doors: 80 units\r\n• Custom Orders: 25 units\r\n\r\nSlow Moving Items:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Bronze finish handles: 300 sets (overstock)\r\n• 2m glass panels: 150 pieces\r\n\r\nReorder Recommendations:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium sheets: Order 10,000 kg\r\n• Glass panels: Order 1,500 pieces\r\n• Hardware sets: Order 2,000 sets\r\n\r\nStorage Capacity:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Current Usage: 75%\r\n• Available Space: 25%\r\n• New warehouse needed by Q3\r\n\r\nInventory Value:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Raw Materials: TZS 18,500,000\r\n• Finished Goods: TZS 12,200,000\r\n• Total Inventory Value: TZS 30,700,000\r\n\r\nTurnover Rate: 2.8x per month (Good)\r\n', 'sent', 7, '2024-06-30 11:00:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(32, 'Weekly Production Summary - Week 26', 'weekly', '📊 WEEKLY ALUMINIUM PRODUCTION SUMMARY (Week 26)\r\n\r\nDaily Production:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: 620 units\r\n• Tuesday: 680 units\r\n• Wednesday: 710 units\r\n• Thursday: 690 units\r\n• Friday: 650 units\r\n• Saturday: 450 units\r\n\r\nTotal Weekly: 3,800 units\r\n\r\nOrders Processed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Completed: 8 orders\r\n• Shipped: 7 orders\r\n• In Queue: 5 orders\r\n\r\nQuality Check Results:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Passed: 3,650 units (96.1%)\r\n• Failed: 150 units (3.9%)\r\n• Reworked: 120 units\r\n\r\nMaterial Usage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium: 4,200 kg\r\n• Glass: 720 pieces\r\n• Hardware: 850 sets\r\n\r\nEnergy Consumption:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Electricity: 4,500 kWh\r\n• Water: 8,000 liters\r\n\r\nWaste Management:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium Scrap: 320 kg (recycled)\r\n• Glass Waste: 45 pieces\r\n• Packaging Waste: 120 kg\r\n\r\nSpecial Achievements:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Completed urgent government order\r\n✓ New quality control process implemented\r\n✓ 2 new trainees joined team\r\n\r\nNext Week Plan:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Target: 4,000 units\r\n• Focus on custom orders\r\n• Maintenance on Saturday\r\n', 'sent', 7, '2024-06-28 14:00:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(33, 'Dar es Salaam Master Plan Progress', 'monthly', '🏙️ DAR ES SALAAM MASTER PLAN - PROGRESS REPORT\r\n\r\nOverall Progress: 65%\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\nCompleted Milestones:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Land Use Survey (100%)\r\n✓ Infrastructure Assessment (85%)\r\n✓ Population Analysis (100%)\r\n✓ Stakeholder Consultations (90%)\r\n✓ Environmental Impact Study (75%)\r\n\r\nZoning Updates:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Residential Zones: 45% updated\r\n• Commercial Zones: 60% updated\r\n• Industrial Zones: 40% updated\r\n• Mixed-Use Zones: 55% updated\r\n• Green Spaces: 30% updated\r\n\r\nPlanning Applications Processed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Building Permits: 125 (↑15%)\r\n• Zoning Changes: 35\r\n• Subdivision Applications: 48\r\n• Land Use Changes: 22\r\n\r\nPublic Consultations:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Meetings Held: 8\r\n• Total Attendees: 450\r\n• Feedback Received: 280 responses\r\n• Satisfaction Rate: 82%\r\n\r\nKey Decisions:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. New CBD expansion approved\r\n2. Kigamboni Bridge area rezoned\r\n3. Mbezi Beach conservation area established\r\n\r\nGIS Mapping Progress:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Area Mapped: 350 sq km\r\n• Digital Layers: 12\r\n• 3D Models: 8 zones\r\n\r\nStaff Workload:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Planners: 8\r\n• GIS Specialists: 4\r\n• Surveyors: 6\r\n• Interns: 3\r\n\r\nChallenges:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n⚠️ Slow approval process from council\r\n⚠️ Data integration issues\r\n⚠️ Public participation low in some areas\r\n\r\nNext Month Targets:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Complete Infrastructure Assessment\r\n• Finalize 50% of zoning maps\r\n• Submit draft to council\r\n', 'sent', 8, '2024-06-30 10:00:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(34, 'Town Planning Applications Report - June', 'monthly', '📋 TOWN PLANNING APPLICATIONS REPORT - JUNE 2024\r\n\r\nApplications Received: 95\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\nBy Type:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Building Permits: 52 (55%)\r\n• Zoning Changes: 18 (19%)\r\n• Subdivisions: 15 (16%)\r\n• Land Use Changes: 10 (10%)\r\n\r\nBy Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Approved: 42\r\n• Under Review: 35\r\n• Pending: 12\r\n• Rejected: 6\r\n\r\nProcessing Times:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Building Permits: 14 days (Target: 10)\r\n• Zoning Changes: 21 days (Target: 14)\r\n• Subdivisions: 18 days (Target: 14)\r\n• Overall Average: 17 days\r\n\r\nTop Locations for Applications:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Kigamboni: 28 applications\r\n2. Mbezi Beach: 22 applications\r\n3. Kinondoni: 18 applications\r\n4. Tegeta: 15 applications\r\n5. Other areas: 12 applications\r\n\r\nRevenue from Fees:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Application Fees: TZS 4,200,000\r\n• Processing Fees: TZS 2,800,000\r\n• Inspection Fees: TZS 1,500,000\r\n• Total: TZS 8,500,000\r\n\r\nCommon Reasons for Rejection:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Incomplete documentation (40%)\r\n2. Non-compliance with zoning (30%)\r\n3. Environmental concerns (20%)\r\n4. Other reasons (10%)\r\n\r\nPublic Feedback Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Positive: 65%\r\n• Neutral: 20%\r\n• Negative: 15%\r\n\r\nStaff Recommendations:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Increase digital application options\r\n• Reduce processing time by 20%\r\n• Additional staff for peak season\r\n', 'sent', 8, '2024-06-30 12:30:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(35, 'Urban Development Projects Update', 'weekly', '🏗️ URBAN DEVELOPMENT PROJECTS UPDATE - WEEK 26\r\n\r\nActive Development Projects: 24\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\nMajor Projects Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Kigamboni New City\r\n   • Progress: 45%\r\n   • Timeline: On track\r\n   • Issues: None\r\n\r\n2. Mwenge Commercial Hub\r\n   • Progress: 70%\r\n   • Timeline: Ahead of schedule\r\n   • Issues: Traffic management\r\n\r\n3. Pwani Economic Zone\r\n   • Progress: 25%\r\n   • Timeline: Delayed (2 weeks)\r\n   • Issues: Land acquisition\r\n\r\n4. Bagamoyo Port Development\r\n   • Progress: 15%\r\n   • Timeline: On track\r\n   • Issues: Environmental permits\r\n\r\nInfrastructure Projects:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Road Expansion: 12 km completed\r\n• Drainage Systems: 8 km\r\n• Water Supply: 15 km pipeline\r\n• Sewage Systems: 10 km\r\n\r\nGreen Space Development:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Parks Created: 2 (5 hectares)\r\n• Tree Planting: 1,200 trees\r\n• Conservation Areas: 3 designated\r\n\r\nPublic Facilities Planning:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Schools: 3 planned\r\n• Hospitals: 1 planned\r\n• Markets: 2 planned\r\n• Police Posts: 2 planned\r\n\r\nStakeholder Meetings:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Developers: 5 meetings\r\n• Community Leaders: 3 meetings\r\n• Government Agencies: 4 meetings\r\n\r\nRisk Assessment:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• High: Land disputes (2 projects)\r\n• Medium: Funding delays\r\n• Low: Environmental impact\r\n\r\nNext Week Focus:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Resolve Bagamoyo land issues\r\n• Complete drainage designs\r\n• Community meeting for Pwani zone\r\n', 'sent', 8, '2024-06-28 07:00:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(36, 'Architectural Projects Report - June 2024', 'monthly', '🏛️ ARCHITECTURAL PROJECTS REPORT - JUNE 2024\r\n\r\nActive Projects: 18\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\nProject Status Breakdown:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Concept Design: 4 projects\r\n• Schematic Design: 5 projects\r\n• Design Development: 4 projects\r\n• Construction Documents: 3 projects\r\n• Completed: 2 projects\r\n\r\nMajor Projects:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Modern Villa Kigamboni\r\n   • Stage: Design Development (85%)\r\n   • Client: John Mwita\r\n   • Budget: TZS 250M\r\n\r\n2. Commercial Complex Pwani\r\n   • Stage: Construction Docs (70%)\r\n   • Client: Pwani Region\r\n   • Budget: TZS 450M\r\n\r\n3. School Design Kinondoni\r\n   • Stage: Completed\r\n   • Client: Ministry of Education\r\n   • Budget: TZS 180M\r\n\r\n4. Luxury Apartments - Mbezi\r\n   • Stage: Schematic (40%)\r\n   • Client: Real Estate Group\r\n   • Budget: TZS 350M\r\n\r\nDesign Output (June):\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Floor Plans: 45 sheets\r\n• Elevations: 32 sheets\r\n• Sections: 28 sheets\r\n• 3D Renderings: 120 images\r\n• BIM Models: 8 projects\r\n\r\nSoftware Usage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• AutoCAD: 100% of projects\r\n• Revit: 65% of projects\r\n• SketchUp: 40% of projects\r\n• Lumion: 25% of projects\r\n\r\nStaff Utilization:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Lead Architects: 3\r\n• CAD Designers: 5\r\n• BIM Specialists: 2\r\n• 3D Visualizers: 2\r\n\r\nClient Feedback:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Design Quality: 4.7/5\r\n• Communication: 4.5/5\r\n• Timeline: 4.3/5\r\n• Budget Management: 4.4/5\r\n\r\nSustainability Features:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Energy-efficient designs: 8 projects\r\n• Green building materials: 5 projects\r\n• Solar integration: 6 projects\r\n• Rainwater harvesting: 4 projects\r\n\r\nUpcoming Projects:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hospital Design - Dodoma (starting July)\r\n• University Campus - Morogoro\r\n• Shopping Mall - Arusha\r\n', 'sent', 9, '2024-06-30 09:00:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(37, 'Architectural Design Review Report', 'monthly', '📐 ARCHITECTURAL DESIGN REVIEW REPORT - JUNE 2024\r\n\r\nDesigns Submitted for Review: 12\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\nReview Outcomes:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Approved: 5 (42%)\r\n• Conditional Approval: 4 (33%)\r\n• Revision Required: 3 (25%)\r\n• Rejected: 0\r\n\r\nDesign Quality Metrics:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aesthetics: 4.6/5\r\n• Functionality: 4.5/5\r\n• Code Compliance: 4.7/5\r\n• Innovation: 4.3/5\r\n• Sustainability: 4.4/5\r\n\r\nCommon Revision Items:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Building code compliance (40%)\r\n2. Accessibility features (30%)\r\n3. Structural coordination (20%)\r\n4. Material specifications (10%)\r\n\r\nPermits Obtained:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Building Permits: 6\r\n• Zoning Approvals: 4\r\n• Environmental Clearance: 3\r\n\r\nClient Review Meetings:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Meetings Held: 15\r\n• Virtual Meetings: 8\r\n• On-site Meetings: 7\r\n• Client Attendance Rate: 92%\r\n\r\nCollaboration with Other Departments:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Town Planning: 8 projects\r\n• Survey: 6 projects\r\n• Construction: 4 projects\r\n• Aluminium: 3 projects\r\n\r\nDesign Innovations:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Parametric facade design implemented\r\n• BIM 360 cloud collaboration started\r\n• VR presentations for clients\r\n\r\nTraining Completed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Revit Advanced: 5 staff\r\n• Sustainable Design: 3 staff\r\n• VR Technology: 2 staff\r\n\r\nQuality Improvement Plan:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Implement peer review system\r\n• Weekly design critiques\r\n• Update design standards\r\n', 'sent', 9, '2024-06-29 06:00:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(38, 'Weekly Design Team Update - Week 26', 'weekly', '✏️ ARCHITECTURAL DESIGN TEAM UPDATE - WEEK 26\r\n\r\nTeam Productivity:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hours Worked: 480 hours\r\n• Overtime: 45 hours\r\n• Designs Completed: 3 full designs\r\n• Drawings Produced: 85 sheets\r\n\r\nProject Deadlines:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Modern Villa Kigamboni - On track\r\n✓ Commercial Complex - Ahead by 2 days\r\n⚠️ School Design - Behind by 3 days\r\n✓ Luxury Apartments - On track\r\n\r\nNew Design Requests:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Residential house - Tegeta\r\n2. Office building - CBD\r\n3. Warehouse - Pwani\r\n\r\nDesign Changes Implemented:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Kigamboni Villa: Kitchen layout revised\r\n• Commercial Complex: Parking increased\r\n• School: Playground redesigned\r\n\r\nClient Communication:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Client Meetings: 4\r\n• Emails Exchanged: 28\r\n• Phone Calls: 12\r\n• Site Visits: 3\r\n\r\nResource Allocation:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Available: 85%\r\n• Overloaded: 10% (3 staff)\r\n• Underutilized: 5% (1 staff)\r\n\r\nEquipment Issues:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Workstation: 2 need upgrade\r\n• Plotter: Service required\r\n• Software licenses: All current\r\n\r\nTraining This Week:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• BIM coordination workshop\r\n• Green building certification\r\n\r\nNext Week Priorities:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Complete School design\r\n• Submit Commercial Complex for permit\r\n• Start Hospital design\r\n', 'sent', 9, '2024-06-28 12:00:00', '2026-05-24 13:17:57', 0, NULL, 0, 0, NULL, 1, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(39, 'Survey Department Report - June 2024', 'monthly', '🗺️ SURVEY DEPARTMENT REPORT - JUNE 2024\r\n\r\nSurvey Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Surveys Completed: 45\r\n• Boundary Surveys: 18\r\n• Topographic Surveys: 12\r\n• Subdivision Surveys: 8\r\n• Construction Staking: 5\r\n• GPS Mapping: 2\r\n\r\nArea Covered:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Area Surveyed: 350 hectares\r\n• Urban Areas: 220 hectares\r\n• Rural Areas: 130 hectares\r\n\r\nEquipment Utilization:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Station: 85% usage\r\n• GPS Equipment: 75% usage\r\n• Drone: 40% usage (new)\r\n• Levels: 60% usage\r\n\r\nSurvey Accuracy:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Horizontal Accuracy: ±2cm\r\n• Vertical Accuracy: ±3cm\r\n• GPS Accuracy: ±5cm\r\n• Drone Accuracy: ±10cm\r\n\r\nFinancial Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Survey Fees: TZS 15,800,000\r\n• Equipment Costs: TZS 3,200,000\r\n• Staff Costs: TZS 4,500,000\r\n• Net Profit: TZS 8,100,000\r\n\r\nStaff Performance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Surveyors: 6\r\n• Assistants: 8\r\n• GIS Specialists: 2\r\n• Drone Operators: 1\r\n\r\nMajor Projects Completed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. National Land Survey Phase 2 (40%)\r\n2. Kigamboni Boundary Demarcation (100%)\r\n3. Pwani Topographic Survey (100%)\r\n4. Mbezi Beach Subdivision (85%)\r\n\r\nSurvey Equipment Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Station 1: Operational\r\n• Total Station 2: Under repair\r\n• GPS Base Station: Operational\r\n• Drones: 1 operational, 1 in maintenance\r\n\r\nGIS Mapping Progress:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Digital Maps Created: 28\r\n• GIS Database Updated: 45 entries\r\n• 3D Terrain Models: 8\r\n\r\nClient Satisfaction:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Timeliness: 4.4/5\r\n• Accuracy: 4.7/5\r\n• Reporting: 4.5/5\r\n• Overall: 4.5/5\r\n\r\nChallenges:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n⚠️ Weather delays (5 days lost)\r\n⚠️ Equipment maintenance\r\n⚠️ Difficult terrain in some areas\r\n\r\nNext Month Goals:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Complete 50 surveys\r\n• Train 2 new assistants\r\n• Implement drone program fully\r\n', 'sent', 10, '2024-06-30 08:30:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(40, 'Weekly Survey Operations - Week 26', 'weekly', '📏 WEEKLY SURVEY OPERATIONS REPORT (Week 26)\r\n\r\nDaily Activities:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: Boundary survey - Kigamboni (5 plots)\r\n• Tuesday: Topographic - Pwani (15 hectares)\r\n• Wednesday: Construction staking - School (3 buildings)\r\n• Thursday: Subdivision - Mbezi (8 plots)\r\n• Friday: GPS mapping - Kinondoni (20 hectares)\r\n• Saturday: Data processing and reporting\r\n\r\nTeam Deployment:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Team 1: Boundary survey (4 members)\r\n• Team 2: Topographic (3 members)\r\n• Team 3: Construction (2 members)\r\n• Office: Data processing (3 members)\r\n\r\nEquipment Used:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Station: 2 units\r\n• GPS Receivers: 4 units\r\n• Drone: 1 unit (tested)\r\n• Laptop: 3 units\r\n\r\nSurvey Data Processed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Raw Data: 25 GB\r\n• Processed Data: 8 GB\r\n• Maps Generated: 12\r\n• Reports Produced: 8\r\n\r\nField Conditions:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Weather: 3 days good, 2 days light rain\r\n• Terrain: Mostly accessible\r\n• Access Issues: 2 sites (resolved)\r\n\r\nHealth & Safety:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Incidents: 0\r\n• Near Misses: 1 (reported)\r\n• Safety Briefings: 5\r\n\r\nClient Interactions:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• New Clients: 3\r\n• Follow-ups: 5\r\n• Complaints: 0\r\n• Compliments: 2\r\n\r\nEquipment Issues:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Station battery issue (resolved)\r\n• GPS signal interference (monitoring)\r\n\r\nNext Week Schedule:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: Continue Kigamboni boundary\r\n• Tuesday: New project - Tegeta\r\n• Wednesday: Data verification\r\n• Thursday: Client presentations\r\n• Friday: GPS training for new staff\r\n', 'sent', 10, '2024-06-28 11:00:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(41, 'Equipment Maintenance & Calibration Report', 'monthly', '🔧 SURVEY EQUIPMENT MAINTENANCE REPORT - JUNE 2024\r\n\r\nEquipment Inventory:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Stations: 4 units\r\n• GPS Receivers: 6 units\r\n• Levels: 3 units\r\n• Drones: 2 units\r\n• Tripods: 12 units\r\n• Prisms: 15 units\r\n\r\nMaintenance Performed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Total Station 2: Calibrated\r\n✓ GPS Base Station: Firmware updated\r\n✓ Drone 1: Battery replaced\r\n✓ All prisms: Cleaned and checked\r\n\r\nEquipment Requiring Service:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n⚠️ Total Station 3: Calibration due\r\n⚠️ Level 2: Need adjustment\r\n⚠️ Drone 2: Software update pending\r\n\r\nCalibration Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Stations: 75% calibrated\r\n• GPS Equipment: 100% calibrated\r\n• Levels: 66% calibrated\r\n\r\nCosts Incurred:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Calibration Services: TZS 450,000\r\n• Repairs: TZS 280,000\r\n• Parts: TZS 120,000\r\n• Total: TZS 850,000\r\n\r\nUpcoming Equipment Purchases:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• New Total Station (Budget: TZS 8M)\r\n• RTK GPS System (Budget: TZS 5M)\r\n• Extra batteries (Budget: TZS 200,000)\r\n\r\nSoftware Updates:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ CAD Software: Updated to 2024 version\r\n✓ GIS Software: Updated\r\n✓ Data Processing Software: Updated\r\n\r\nTraining on Equipment:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Station operation: 2 staff\r\n• Drone operation: 1 staff (certified)\r\n• GPS data processing: 3 staff\r\n\r\nEquipment Storage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• All equipment properly stored\r\n• Climate control functional\r\n• Security system operational\r\n\r\nRecommendations:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Schedule regular calibration every 3 months\r\n2. Purchase backup batteries\r\n3. Implement equipment tracking system\r\n', 'sent', 10, '2024-06-29 13:00:00', NULL, 0, NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(42, 'INNOCENT', 'weekly', 'ADMN', 'draft', 1, '2026-05-22 22:11:22', '2026-05-23 09:38:03', 0, NULL, 1, 0, '2026-05-23 11:38:03', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(43, 'MAIN', 'weekly', 'FINANCE', 'sent', 2, '2026-05-22 22:12:23', '2026-05-24 23:17:13', 0, 2, 0, 1, '2026-05-25 02:17:13', 1, 0, 2, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(44, 'may Town Planning Report', 'weekly', 'MANAGER', 'draft', 4, '2026-05-22 22:16:19', '2026-05-22 22:16:19', 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(45, 'MAIN', 'weekly', '190,000,000', 'draft', 1, '2026-05-23 09:40:01', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(46, 'MAIN myula 9999', 'weekly', '190,000,000', 'draft', 1, '2026-05-23 09:40:14', '2026-05-23 09:41:00', 0, NULL, 1, 0, '2026-05-23 12:41:00', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(47, 'may Town Planning Report', 'weekly', '900,000,000', 'draft', 1, '2026-05-23 09:40:26', '2026-05-23 09:40:52', 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(48, 'Risk Assessment Report', 'monthly', 'Monthly risk assessment for June 2024\n\nIdentified Risks:\n1. Supply chain disruption - High\n2. Currency fluctuation - Medium\n3. Staff retention - Medium\n4. Regulatory changes - Low\n\nMitigation Strategies:\n- Diversify suppliers\n- Hedging strategy\n- Improve employee benefits\n- Regular compliance audits\n\nRisk Score: 65/100 (Moderate)\nActions Required: 8 high priority items', 'sent', 4, '2026-05-24 13:08:54', '2026-05-24 13:09:06', 0, NULL, 0, 0, NULL, 4, 1, 1, NULL, NULL, NULL, 'System', NULL, NULL, 0);
INSERT INTO `reports` (`id`, `title`, `period`, `content`, `status`, `department_id`, `created_at`, `updated_at`, `is_viewed_by_admin`, `sent_from_dept`, `deleted_by_admin`, `deleted_by_department`, `deleted_at`, `sent_to_department`, `is_viewed_by_department`, `sent_from_department`, `file_path`, `file_type`, `file_name`, `created_by`, `file_size`, `updated_by`, `is_deleted`) VALUES
(49, 'Annual Performance Review 2024', 'annual', 'GeoTraverse Annual Performance Report 2024\n\nRevenue: TZS 2.5 Billion (↑12%)\nExpenses: TZS 1.8 Billion\nNet Profit: TZS 700 Million\n\nDepartment Performance:\n- Construction: Best performer\n- Aluminium: Above target\n- Sales: Met target\n- Others: On track\n\nStaff Satisfaction: 85%\nCustomer Satisfaction: 92%\n\nProjects Completed: 24\nNew Projects: 18\n\nOutlook for 2025: Positive with projected growth of 20%', 'sent', 4, '2026-05-24 13:09:18', '2026-05-24 13:09:28', 0, NULL, 0, 0, NULL, 4, 1, 1, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(50, 'Weekly Design Team Update - Week 26', 'weekly', '✏️ ARCHITECTURAL DESIGN TEAM UPDATE - WEEK 26\r\n\r\nTeam Productivity:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hours Worked: 480 hours\r\n• Overtime: 45 hours\r\n• Designs Completed: 3 full designs\r\n• Drawings Produced: 85 sheets\r\n\r\nProject Deadlines:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Modern Villa Kigamboni - On track\r\n✓ Commercial Complex - Ahead by 2 days\r\n⚠️ School Design - Behind by 3 days\r\n✓ Luxury Apartments - On track\r\n\r\nNew Design Requests:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Residential house - Tegeta\r\n2. Office building - CBD\r\n3. Warehouse - Pwani\r\n\r\nDesign Changes Implemented:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Kigamboni Villa: Kitchen layout revised\r\n• Commercial Complex: Parking increased\r\n• School: Playground redesigned\r\n\r\nClient Communication:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Client Meetings: 4\r\n• Emails Exchanged: 28\r\n• Phone Calls: 12\r\n• Site Visits: 3\r\n\r\nResource Allocation:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Available: 85%\r\n• Overloaded: 10% (3 staff)\r\n• Underutilized: 5% (1 staff)\r\n\r\nEquipment Issues:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Workstation: 2 need upgrade\r\n• Plotter: Service required\r\n• Software licenses: All current\r\n\r\nTraining This Week:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• BIM coordination workshop\r\n• Green building certification\r\n\r\nNext Week Priorities:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Complete School design\r\n• Submit Commercial Complex for permit\r\n• Start Hospital design\r\n', 'sent', 4, '2026-05-24 13:10:46', '2026-05-24 13:10:53', 0, NULL, 0, 0, NULL, 4, 1, 9, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(51, 'Weekly Design Team Update - Week 26', 'weekly', '✏️ ARCHITECTURAL DESIGN TEAM UPDATE - WEEK 26\r\n\r\nTeam Productivity:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hours Worked: 480 hours\r\n• Overtime: 45 hours\r\n• Designs Completed: 3 full designs\r\n• Drawings Produced: 85 sheets\r\n\r\nProject Deadlines:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Modern Villa Kigamboni - On track\r\n✓ Commercial Complex - Ahead by 2 days\r\n⚠️ School Design - Behind by 3 days\r\n✓ Luxury Apartments - On track\r\n\r\nNew Design Requests:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Residential house - Tegeta\r\n2. Office building - CBD\r\n3. Warehouse - Pwani\r\n\r\nDesign Changes Implemented:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Kigamboni Villa: Kitchen layout revised\r\n• Commercial Complex: Parking increased\r\n• School: Playground redesigned\r\n\r\nClient Communication:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Client Meetings: 4\r\n• Emails Exchanged: 28\r\n• Phone Calls: 12\r\n• Site Visits: 3\r\n\r\nResource Allocation:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Available: 85%\r\n• Overloaded: 10% (3 staff)\r\n• Underutilized: 5% (1 staff)\r\n\r\nEquipment Issues:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Workstation: 2 need upgrade\r\n• Plotter: Service required\r\n• Software licenses: All current\r\n\r\nTraining This Week:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• BIM coordination workshop\r\n• Green building certification\r\n\r\nNext Week Priorities:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Complete School design\r\n• Submit Commercial Complex for permit\r\n• Start Hospital design\r\n', 'sent', 1, '2026-05-24 13:17:57', '2026-05-24 18:12:28', 1, NULL, 0, 0, NULL, 1, 0, 9, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(53, 'Aluminium Production Report - June', 'monthly', 'ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4% (↓1%)\n- Customer returns: 2 (↓50%)\n\nEfficiency: 96% (↑2%)\nStaff Overtime: 45 hours\n\nCertifications:\n- ISO 9001 audit scheduled\n- TBS certification renewed', 'sent', 1, '2026-05-24 22:24:38', '2026-05-24 22:24:48', 1, NULL, 0, 0, NULL, 1, 0, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(54, 'Weekly Design Team Update - Week 26', 'weekly', '✏️ ARCHITECTURAL DESIGN TEAM UPDATE - WEEK 26\r\n\r\nTeam Productivity:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hours Worked: 480 hours\r\n• Overtime: 45 hours\r\n• Designs Completed: 3 full designs\r\n• Drawings Produced: 85 sheets\r\n\r\nProject Deadlines:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Modern Villa Kigamboni - On track\r\n✓ Commercial Complex - Ahead by 2 days\r\n⚠️ School Design - Behind by 3 days\r\n✓ Luxury Apartments - On track\r\n\r\nNew Design Requests:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Residential house - Tegeta\r\n2. Office building - CBD\r\n3. Warehouse - Pwani\r\n\r\nDesign Changes Implemented:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Kigamboni Villa: Kitchen layout revised\r\n• Commercial Complex: Parking increased\r\n• School: Playground redesigned\r\n\r\nClient Communication:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Client Meetings: 4\r\n• Emails Exchanged: 28\r\n• Phone Calls: 12\r\n• Site Visits: 3\r\n\r\nResource Allocation:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Available: 85%\r\n• Overloaded: 10% (3 staff)\r\n• Underutilized: 5% (1 staff)\r\n\r\nEquipment Issues:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Workstation: 2 need upgrade\r\n• Plotter: Service required\r\n• Software licenses: All current\r\n\r\nTraining This Week:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• BIM coordination workshop\r\n• Green building certification\r\n\r\nNext Week Priorities:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Complete School design\r\n• Submit Commercial Complex for permit\r\n• Start Hospital design\r\n', 'sent', 7, '2026-05-24 22:30:51', '2026-05-24 22:34:38', 0, NULL, 0, 0, NULL, 7, 1, 9, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(55, 'Aluminium Production Report - June', 'monthly', 'ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4% (↓1%)\n- Customer returns: 2 (↓50%)\n\nEfficiency: 96% (↑2%)\nStaff Overtime: 45 hours\n\nCertifications:\n- ISO 9001 audit scheduled\n- TBS certification renewed', 'sent', 9, '2026-05-24 22:31:08', NULL, 0, NULL, 0, 0, NULL, 9, 0, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(56, 'Weekly Production Summary - Week 26', 'weekly', '📊 WEEKLY ALUMINIUM PRODUCTION SUMMARY (Week 26)\r\n\r\nDaily Production:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: 620 units\r\n• Tuesday: 680 units\r\n• Wednesday: 710 units\r\n• Thursday: 690 units\r\n• Friday: 650 units\r\n• Saturday: 450 units\r\n\r\nTotal Weekly: 3,800 units\r\n\r\nOrders Processed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Completed: 8 orders\r\n• Shipped: 7 orders\r\n• In Queue: 5 orders\r\n\r\nQuality Check Results:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Passed: 3,650 units (96.1%)\r\n• Failed: 150 units (3.9%)\r\n• Reworked: 120 units\r\n\r\nMaterial Usage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium: 4,200 kg\r\n• Glass: 720 pieces\r\n• Hardware: 850 sets\r\n\r\nEnergy Consumption:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Electricity: 4,500 kWh\r\n• Water: 8,000 liters\r\n\r\nWaste Management:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium Scrap: 320 kg (recycled)\r\n• Glass Waste: 45 pieces\r\n• Packaging Waste: 120 kg\r\n\r\nSpecial Achievements:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Completed urgent government order\r\n✓ New quality control process implemented\r\n✓ 2 new trainees joined team\r\n\r\nNext Week Plan:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Target: 4,000 units\r\n• Focus on custom orders\r\n• Maintenance on Saturday\r\n', 'sent', 9, '2026-05-24 22:34:48', NULL, 0, NULL, 0, 0, NULL, 9, 0, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(57, 'Weekly Production Summary - Week 26', 'weekly', '📊 WEEKLY ALUMINIUM PRODUCTION SUMMARY (Week 26)\r\n\r\nDaily Production:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: 620 units\r\n• Tuesday: 680 units\r\n• Wednesday: 710 units\r\n• Thursday: 690 units\r\n• Friday: 650 units\r\n• Saturday: 450 units\r\n\r\nTotal Weekly: 3,800 units\r\n\r\nOrders Processed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Completed: 8 orders\r\n• Shipped: 7 orders\r\n• In Queue: 5 orders\r\n\r\nQuality Check Results:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Passed: 3,650 units (96.1%)\r\n• Failed: 150 units (3.9%)\r\n• Reworked: 120 units\r\n\r\nMaterial Usage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium: 4,200 kg\r\n• Glass: 720 pieces\r\n• Hardware: 850 sets\r\n\r\nEnergy Consumption:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Electricity: 4,500 kWh\r\n• Water: 8,000 liters\r\n\r\nWaste Management:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium Scrap: 320 kg (recycled)\r\n• Glass Waste: 45 pieces\r\n• Packaging Waste: 120 kg\r\n\r\nSpecial Achievements:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Completed urgent government order\r\n✓ New quality control process implemented\r\n✓ 2 new trainees joined team\r\n\r\nNext Week Plan:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Target: 4,000 units\r\n• Focus on custom orders\r\n• Maintenance on Saturday\r\n', 'sent', 7, '2026-05-24 22:35:21', '2026-05-24 22:39:32', 0, NULL, 0, 0, NULL, 7, 1, 9, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(58, 'Aluminium Production Report - June', 'monthly', 'ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4% (↓1%)\n- Customer returns: 2 (↓50%)\n\nEfficiency: 96% (↑2%)\nStaff Overtime: 45 hours\n\nCertifications:\n- ISO 9001 audit scheduled\n- TBS certification renewed', 'sent', 6, '2026-05-24 22:35:54', NULL, 0, NULL, 0, 0, NULL, 6, 0, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(59, 'Aluminium Production Report - June 2024', 'monthly', '🔧 ALUMINIUM PRODUCTION REPORT - JUNE 2024\r\n\r\nProduction Volume:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Window Frames: 3,200 units (↑12%)\r\n• Door Frames: 1,800 units (↑15%)\r\n• Sliding Doors: 520 units (↑18%)\r\n• Curtain Walls: 45 units (↑25%)\r\n• Custom Fabrications: 85 units\r\n\r\nOrders Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Orders Completed: 42\r\n• Orders In Progress: 18\r\n• New Orders Received: 35\r\n• Order Value: TZS 48,500,000\r\n\r\nFinancial Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Revenue: TZS 42,500,000\r\n• Expenses: TZS 28,200,000\r\n• Profit: TZS 14,300,000\r\n• Profit Margin: 33.6%\r\n\r\nMaterials Consumption:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium Sheets: 22,500 kg\r\n• Glass Panels: 3,800 pieces\r\n• Hardware Sets: 4,200 sets\r\n• Powder Coating: 580 kg\r\n• Packaging Materials: 320 units\r\n\r\nQuality Control:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Defect Rate: 2.8% (target: 3%)\r\n• Rework Rate: 3.5%\r\n• Customer Satisfaction: 94%\r\n• Warranty Claims: 2\r\n\r\nEquipment Performance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Cutting Machines: 96% uptime\r\n• Welding Equipment: 92% uptime\r\n• Powder Coating Line: 100% uptime\r\n• Assembly Stations: 98% uptime\r\n\r\nInventory Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Raw Aluminium: 8,500 kg (15 days supply)\r\n• Glass: 1,200 pieces (20 days)\r\n• Hardware: 2,800 sets (25 days)\r\n\r\nProjects Completed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Modern Villa Kigamboni - 250 units\r\n2. Commercial Complex Pwani - 180 units\r\n3. School Project Kinondoni - 120 units\r\n\r\nStaff Performance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Production Staff: 18\r\n• Quality Control: 4\r\n• Installers: 12\r\n• Overtime Hours: 85 hours\r\n\r\nNext Month Projections:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Target Production: 4,000 units\r\n• New Equipment: 2 cutting machines\r\n• Staff Training: Advanced fabrication\r\n', 'sent', 9, '2026-05-24 22:39:41', NULL, 0, NULL, 0, 0, NULL, 9, 0, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(60, 'Aluminium Inventory Status', 'weekly', 'ALUMINIUM INVENTORY - WEEK 26, 2024\n\nCurrent Stock:\n- Raw Aluminium Sheets: 8,500 kg\n- Glass Panels: 1,200 pcs\n- Hardware Accessories: 2,500 sets\n- Powder Coating: 500 kg\n\nMinimum Stock Levels:\n- Aluminium sheets: 5,000 kg (Current: 8,500 kg - OK)\n- Glass panels: 800 pcs (Current: 1,200 pcs - OK)\n- Hardware: 1,500 sets (Current: 2,500 sets - OK)\n\nProducts Ready for Dispatch:\n- Window frames: 450 units\n- Door frames: 200 units\n- Custom orders: 25 units\n\nPending Orders: 40 (Value: TZS 12M)\nProduction Capacity Used: 85%\n\nReorder Recommendations:\n- Order 10,000 kg aluminium sheets\n- Order 2,000 pcs glass panels', 'sent', 9, '2026-05-24 22:40:12', NULL, 0, NULL, 0, 0, NULL, 9, 0, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(61, 'Aluminium Inventory Status', 'weekly', 'ALUMINIUM INVENTORY - WEEK 26, 2024\n\nCurrent Stock:\n- Raw Aluminium Sheets: 8,500 kg\n- Glass Panels: 1,200 pcs\n- Hardware Accessories: 2,500 sets\n- Powder Coating: 500 kg\n\nMinimum Stock Levels:\n- Aluminium sheets: 5,000 kg (Current: 8,500 kg - OK)\n- Glass panels: 800 pcs (Current: 1,200 pcs - OK)\n- Hardware: 1,500 sets (Current: 2,500 sets - OK)\n\nProducts Ready for Dispatch:\n- Window frames: 450 units\n- Door frames: 200 units\n- Custom orders: 25 units\n\nPending Orders: 40 (Value: TZS 12M)\nProduction Capacity Used: 85%\n\nReorder Recommendations:\n- Order 10,000 kg aluminium sheets\n- Order 2,000 pcs glass panels', 'sent', 6, '2026-05-24 22:49:38', NULL, 0, NULL, 0, 0, NULL, 6, 0, 9, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(62, 'Weekly Construction Progress - Week 26', 'weekly', 'CONSTRUCTION PROGRESS REPORT - WEEK 26, 2024\n\nProject: Modern Villa - Kigamboni\nProgress: 85% (↑5%)\nCompleted: Structure, Roofing, Plumbing\nIn Progress: Electrical, Finishing, Landscaping\nNext Week: Painting, Tiling\n\nProject: Commercial Complex - Pwani\nProgress: 60% (↑10%)\nCompleted: Ground floor to Floor 3\nIn Progress: Floor 4, 5 construction\n\nProject: Residential Estate - Mbezi\nProgress: 25% (↑10%)\nCompleted: Site clearing, Foundation\nIn Progress: Ground floor framing\n\nSafety Report:\n- Incidents: 0\n- Near misses: 2 (reported)\n- Safety training completed: 45 workers\n\nWorkers on Site: 185\nEquipment: All operational\nMaterials: Adequate stock\n\nBudget Status: On track\nNext Milestone: Complete Modern Villa by July 15', 'sent', 7, '2026-05-24 22:56:52', '2026-05-24 23:17:22', 0, NULL, 0, 1, '2026-05-25 02:17:22', 7, 1, 11, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(63, 'Aluminium Production Report - June', 'monthly', 'ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4% (↓1%)\n- Customer returns: 2 (↓50%)\n\nEfficiency: 96% (↑2%)\nStaff Overtime: 45 hours\n\nCertifications:\n- ISO 9001 audit scheduled\n- TBS certification renewed', 'sent', 11, '2026-05-24 22:57:18', NULL, 0, NULL, 0, 0, NULL, 11, 0, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(64, 'Weekly Design Team Update - Week 26', 'weekly', '✏️ ARCHITECTURAL DESIGN TEAM UPDATE - WEEK 26\r\n\r\nTeam Productivity:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hours Worked: 480 hours\r\n• Overtime: 45 hours\r\n• Designs Completed: 3 full designs\r\n• Drawings Produced: 85 sheets\r\n\r\nProject Deadlines:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Modern Villa Kigamboni - On track\r\n✓ Commercial Complex - Ahead by 2 days\r\n⚠️ School Design - Behind by 3 days\r\n✓ Luxury Apartments - On track\r\n\r\nNew Design Requests:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Residential house - Tegeta\r\n2. Office building - CBD\r\n3. Warehouse - Pwani\r\n\r\nDesign Changes Implemented:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Kigamboni Villa: Kitchen layout revised\r\n• Commercial Complex: Parking increased\r\n• School: Playground redesigned\r\n\r\nClient Communication:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Client Meetings: 4\r\n• Emails Exchanged: 28\r\n• Phone Calls: 12\r\n• Site Visits: 3\r\n\r\nResource Allocation:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Available: 85%\r\n• Overloaded: 10% (3 staff)\r\n• Underutilized: 5% (1 staff)\r\n\r\nEquipment Issues:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Workstation: 2 need upgrade\r\n• Plotter: Service required\r\n• Software licenses: All current\r\n\r\nTraining This Week:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• BIM coordination workshop\r\n• Green building certification\r\n\r\nNext Week Priorities:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Complete School design\r\n• Submit Commercial Complex for permit\r\n• Start Hospital design\r\n', 'sent', 2, '2026-05-24 23:06:22', '2026-05-24 23:06:28', 0, NULL, 0, 0, NULL, 2, 1, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(65, 'Weekly Production Summary - Week 26', 'weekly', '📊 WEEKLY ALUMINIUM PRODUCTION SUMMARY (Week 26)\r\n\r\nDaily Production:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: 620 units\r\n• Tuesday: 680 units\r\n• Wednesday: 710 units\r\n• Thursday: 690 units\r\n• Friday: 650 units\r\n• Saturday: 450 units\r\n\r\nTotal Weekly: 3,800 units\r\n\r\nOrders Processed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Completed: 8 orders\r\n• Shipped: 7 orders\r\n• In Queue: 5 orders\r\n\r\nQuality Check Results:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Passed: 3,650 units (96.1%)\r\n• Failed: 150 units (3.9%)\r\n• Reworked: 120 units\r\n\r\nMaterial Usage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium: 4,200 kg\r\n• Glass: 720 pieces\r\n• Hardware: 850 sets\r\n\r\nEnergy Consumption:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Electricity: 4,500 kWh\r\n• Water: 8,000 liters\r\n\r\nWaste Management:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium Scrap: 320 kg (recycled)\r\n• Glass Waste: 45 pieces\r\n• Packaging Waste: 120 kg\r\n\r\nSpecial Achievements:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Completed urgent government order\r\n✓ New quality control process implemented\r\n✓ 2 new trainees joined team\r\n\r\nNext Week Plan:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Target: 4,000 units\r\n• Focus on custom orders\r\n• Maintenance on Saturday\r\n', 'sent', 2, '2026-05-24 23:17:48', '2026-05-24 23:17:51', 0, NULL, 0, 0, NULL, 2, 1, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(66, 'Aluminium Production Report - June', 'monthly', 'ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4% (↓1%)\n- Customer returns: 2 (↓50%)\n\nEfficiency: 96% (↑2%)\nStaff Overtime: 45 hours\n\nCertifications:\n- ISO 9001 audit scheduled\n- TBS certification renewed', 'sent', 12, '2026-05-24 23:21:54', '2026-05-24 23:22:01', 0, NULL, 0, 0, NULL, 12, 1, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(67, 'Aluminium Production Report - June', 'monthly', 'ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4% (↓1%)\n- Customer returns: 2 (↓50%)\n\nEfficiency: 96% (↑2%)\nStaff Overtime: 45 hours\n\nCertifications:\n- ISO 9001 audit scheduled\n- TBS certification renewed', 'sent', 4, '2026-05-24 23:37:09', '2026-06-03 17:19:53', 0, NULL, 0, 1, '2026-06-03 20:19:53', 4, 1, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(68, 'Risk Assessment Report', 'monthly', 'Monthly risk assessment for June 2024\n\nIdentified Risks:\n1. Supply chain disruption - High\n2. Currency fluctuation - Medium\n3. Staff retention - Medium\n4. Regulatory changes - Low\n\nMitigation Strategies:\n- Diversify suppliers\n- Hedging strategy\n- Improve employee benefits\n- Regular compliance audits\n\nRisk Score: 65/100 (Moderate)\nActions Required: 8 high priority items', 'sent', 7, '2026-05-24 23:37:22', '2026-05-24 23:37:28', 0, NULL, 0, 0, NULL, 7, 1, 4, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(69, 'Aluminium Inventory Status', 'weekly', 'ALUMINIUM INVENTORY - WEEK 26, 2024\n\nCurrent Stock:\n- Raw Aluminium Sheets: 8,500 kg\n- Glass Panels: 1,200 pcs\n- Hardware Accessories: 2,500 sets\n- Powder Coating: 500 kg\n\nMinimum Stock Levels:\n- Aluminium sheets: 5,000 kg (Current: 8,500 kg - OK)\n- Glass panels: 800 pcs (Current: 1,200 pcs - OK)\n- Hardware: 1,500 sets (Current: 2,500 sets - OK)\n\nProducts Ready for Dispatch:\n- Window frames: 450 units\n- Door frames: 200 units\n- Custom orders: 25 units\n\nPending Orders: 40 (Value: TZS 12M)\nProduction Capacity Used: 85%\n\nReorder Recommendations:\n- Order 10,000 kg aluminium sheets\n- Order 2,000 pcs glass panels', 'sent', 3, '2026-05-24 23:38:12', '2026-05-24 23:41:41', 0, NULL, 0, 0, NULL, 3, 1, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(70, 'Customer Satisfaction Survey', 'monthly', 'Customer Satisfaction Survey - June 2024\n\nSurvey Sample: 200 customers\nResponse Rate: 65%\n\nOverall Satisfaction: 4.6/5 (↑0.3 from May)\n\nBreakdown:\n- Product Quality: 4.7/5\n- Delivery Time: 4.4/5\n- Customer Service: 4.8/5\n- Pricing: 4.3/5\n- Communication: 4.5/5\n\nNet Promoter Score (NPS): +72 (Excellent)\n\nCustomer Complaints: 8 (↓5 from May)\n- Delivery delays: 3\n- Quality issues: 2\n- Billing errors: 2\n- Communication: 1\n\nAll complaints resolved within 48 hours', 'sent', 7, '2026-05-24 23:41:47', '2026-05-24 23:47:37', 0, NULL, 0, 0, NULL, 7, 1, 3, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(71, 'Weekly Design Team Update - Week 26', 'weekly', '✏️ ARCHITECTURAL DESIGN TEAM UPDATE - WEEK 26\r\n\r\nTeam Productivity:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Hours Worked: 480 hours\r\n• Overtime: 45 hours\r\n• Designs Completed: 3 full designs\r\n• Drawings Produced: 85 sheets\r\n\r\nProject Deadlines:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Modern Villa Kigamboni - On track\r\n✓ Commercial Complex - Ahead by 2 days\r\n⚠️ School Design - Behind by 3 days\r\n✓ Luxury Apartments - On track\r\n\r\nNew Design Requests:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Residential house - Tegeta\r\n2. Office building - CBD\r\n3. Warehouse - Pwani\r\n\r\nDesign Changes Implemented:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Kigamboni Villa: Kitchen layout revised\r\n• Commercial Complex: Parking increased\r\n• School: Playground redesigned\r\n\r\nClient Communication:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Client Meetings: 4\r\n• Emails Exchanged: 28\r\n• Phone Calls: 12\r\n• Site Visits: 3\r\n\r\nResource Allocation:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Available: 85%\r\n• Overloaded: 10% (3 staff)\r\n• Underutilized: 5% (1 staff)\r\n\r\nEquipment Issues:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Workstation: 2 need upgrade\r\n• Plotter: Service required\r\n• Software licenses: All current\r\n\r\nTraining This Week:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• BIM coordination workshop\r\n• Green building certification\r\n\r\nNext Week Priorities:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Complete School design\r\n• Submit Commercial Complex for permit\r\n• Start Hospital design\r\n', 'sent', 5, '2026-05-24 23:45:53', '2026-06-03 12:43:02', 0, NULL, 0, 1, '2026-06-03 15:43:02', 5, 0, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(72, 'Aluminium Production Report - June', 'monthly', 'ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4% (↓1%)\n- Customer returns: 2 (↓50%)\n\nEfficiency: 96% (↑2%)\nStaff Overtime: 45 hours\n\nCertifications:\n- ISO 9001 audit scheduled\n- TBS certification renewed', 'sent', 5, '2026-05-24 23:46:27', '2026-06-03 12:42:59', 0, NULL, 0, 1, '2026-06-03 15:42:59', 5, 0, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(73, 'Aluminium Production Report - June', 'monthly', 'ALUMINIUM PRODUCTION REPORT - JUNE 2024\n\nProduction Volume:\n- Window frames: 3,200 units (↑15%)\n- Door frames: 1,600 units (↑14%)\n- Sliding doors: 520 units (↑16%)\n- Custom fabrications: 75 units (↑25%)\n\nOrders Completed: 35\nOrders In Progress: 15\n\nRevenue: TZS 38,500,000 (↑18%)\nExpenses: TZS 21,000,000\nProfit: TZS 17,500,000\n\nMaterials Usage:\n- Aluminium sheets: 21,000 kg\n- Glass panels: 3,500 pcs\n- Hardware: 4,200 sets\n\nQuality Metrics:\n- Defect rate: 2.5% (↓0.5%)\n- Rework rate: 4% (↓1%)\n- Customer returns: 2 (↓50%)\n\nEfficiency: 96% (↑2%)\nStaff Overtime: 45 hours\n\nCertifications:\n- ISO 9001 audit scheduled\n- TBS certification renewed', 'sent', 7, '2026-05-24 23:47:20', '2026-05-24 23:47:35', 0, NULL, 0, 0, NULL, 7, 1, 1, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(74, 'Equipment Maintenance & Calibration Report', 'monthly', '🔧 SURVEY EQUIPMENT MAINTENANCE REPORT - JUNE 2024\r\n\r\nEquipment Inventory:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Stations: 4 units\r\n• GPS Receivers: 6 units\r\n• Levels: 3 units\r\n• Drones: 2 units\r\n• Tripods: 12 units\r\n• Prisms: 15 units\r\n\r\nMaintenance Performed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Total Station 2: Calibrated\r\n✓ GPS Base Station: Firmware updated\r\n✓ Drone 1: Battery replaced\r\n✓ All prisms: Cleaned and checked\r\n\r\nEquipment Requiring Service:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n⚠️ Total Station 3: Calibration due\r\n⚠️ Level 2: Need adjustment\r\n⚠️ Drone 2: Software update pending\r\n\r\nCalibration Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Stations: 75% calibrated\r\n• GPS Equipment: 100% calibrated\r\n• Levels: 66% calibrated\r\n\r\nCosts Incurred:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Calibration Services: TZS 450,000\r\n• Repairs: TZS 280,000\r\n• Parts: TZS 120,000\r\n• Total: TZS 850,000\r\n\r\nUpcoming Equipment Purchases:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• New Total Station (Budget: TZS 8M)\r\n• RTK GPS System (Budget: TZS 5M)\r\n• Extra batteries (Budget: TZS 200,000)\r\n\r\nSoftware Updates:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ CAD Software: Updated to 2024 version\r\n✓ GIS Software: Updated\r\n✓ Data Processing Software: Updated\r\n\r\nTraining on Equipment:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Station operation: 2 staff\r\n• Drone operation: 1 staff (certified)\r\n• GPS data processing: 3 staff\r\n\r\nEquipment Storage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• All equipment properly stored\r\n• Climate control functional\r\n• Security system operational\r\n\r\nRecommendations:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Schedule regular calibration every 3 months\r\n2. Purchase backup batteries\r\n3. Implement equipment tracking system\r\n', 'sent', 8, '2026-05-24 23:53:05', '2026-06-07 15:25:10', 0, NULL, 0, 1, '2026-06-07 18:25:10', 8, 0, 10, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(75, 'Town Planning Applications Report - June', 'monthly', '📋 TOWN PLANNING APPLICATIONS REPORT - JUNE 2024\r\n\r\nApplications Received: 95\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\nBy Type:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Building Permits: 52 (55%)\r\n• Zoning Changes: 18 (19%)\r\n• Subdivisions: 15 (16%)\r\n• Land Use Changes: 10 (10%)\r\n\r\nBy Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Approved: 42\r\n• Under Review: 35\r\n• Pending: 12\r\n• Rejected: 6\r\n\r\nProcessing Times:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Building Permits: 14 days (Target: 10)\r\n• Zoning Changes: 21 days (Target: 14)\r\n• Subdivisions: 18 days (Target: 14)\r\n• Overall Average: 17 days\r\n\r\nTop Locations for Applications:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Kigamboni: 28 applications\r\n2. Mbezi Beach: 22 applications\r\n3. Kinondoni: 18 applications\r\n4. Tegeta: 15 applications\r\n5. Other areas: 12 applications\r\n\r\nRevenue from Fees:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Application Fees: TZS 4,200,000\r\n• Processing Fees: TZS 2,800,000\r\n• Inspection Fees: TZS 1,500,000\r\n• Total: TZS 8,500,000\r\n\r\nCommon Reasons for Rejection:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Incomplete documentation (40%)\r\n2. Non-compliance with zoning (30%)\r\n3. Environmental concerns (20%)\r\n4. Other reasons (10%)\r\n\r\nPublic Feedback Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Positive: 65%\r\n• Neutral: 20%\r\n• Negative: 15%\r\n\r\nStaff Recommendations:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Increase digital application options\r\n• Reduce processing time by 20%\r\n• Additional staff for peak season\r\n', 'sent', 10, '2026-05-24 23:53:18', '2026-05-24 23:53:23', 0, NULL, 0, 0, NULL, 10, 1, 8, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(76, 'Aluminium Inventory Status', 'weekly', 'ALUMINIUM INVENTORY - WEEK 26, 2024\n\nCurrent Stock:\n- Raw Aluminium Sheets: 8,500 kg\n- Glass Panels: 1,200 pcs\n- Hardware Accessories: 2,500 sets\n- Powder Coating: 500 kg\n\nMinimum Stock Levels:\n- Aluminium sheets: 5,000 kg (Current: 8,500 kg - OK)\n- Glass panels: 800 pcs (Current: 1,200 pcs - OK)\n- Hardware: 1,500 sets (Current: 2,500 sets - OK)\n\nProducts Ready for Dispatch:\n- Window frames: 450 units\n- Door frames: 200 units\n- Custom orders: 25 units\n\nPending Orders: 40 (Value: TZS 12M)\nProduction Capacity Used: 85%\n\nReorder Recommendations:\n- Order 10,000 kg aluminium sheets\n- Order 2,000 pcs glass panels', 'sent', 7, '2026-05-25 00:03:10', '2026-05-25 00:03:18', 0, NULL, 0, 0, NULL, 7, 1, 6, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(77, 'Weekly Production Summary - Week 26', 'weekly', '📊 WEEKLY ALUMINIUM PRODUCTION SUMMARY (Week 26)\r\n\r\nDaily Production:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Monday: 620 units\r\n• Tuesday: 680 units\r\n• Wednesday: 710 units\r\n• Thursday: 690 units\r\n• Friday: 650 units\r\n• Saturday: 450 units\r\n\r\nTotal Weekly: 3,800 units\r\n\r\nOrders Processed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Completed: 8 orders\r\n• Shipped: 7 orders\r\n• In Queue: 5 orders\r\n\r\nQuality Check Results:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Passed: 3,650 units (96.1%)\r\n• Failed: 150 units (3.9%)\r\n• Reworked: 120 units\r\n\r\nMaterial Usage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium: 4,200 kg\r\n• Glass: 720 pieces\r\n• Hardware: 850 sets\r\n\r\nEnergy Consumption:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Electricity: 4,500 kWh\r\n• Water: 8,000 liters\r\n\r\nWaste Management:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium Scrap: 320 kg (recycled)\r\n• Glass Waste: 45 pieces\r\n• Packaging Waste: 120 kg\r\n\r\nSpecial Achievements:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Completed urgent government order\r\n✓ New quality control process implemented\r\n✓ 2 new trainees joined team\r\n\r\nNext Week Plan:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Target: 4,000 units\r\n• Focus on custom orders\r\n• Maintenance on Saturday\r\n', 'sent', 6, '2026-05-25 00:03:36', NULL, 0, NULL, 0, 0, NULL, 6, 0, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(78, 'Aluminium Production Report - June 2024', 'monthly', '🔧 ALUMINIUM PRODUCTION REPORT - JUNE 2024\r\n\r\nProduction Volume:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Window Frames: 3,200 units (↑12%)\r\n• Door Frames: 1,800 units (↑15%)\r\n• Sliding Doors: 520 units (↑18%)\r\n• Curtain Walls: 45 units (↑25%)\r\n• Custom Fabrications: 85 units\r\n\r\nOrders Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Orders Completed: 42\r\n• Orders In Progress: 18\r\n• New Orders Received: 35\r\n• Order Value: TZS 48,500,000\r\n\r\nFinancial Summary:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Revenue: TZS 42,500,000\r\n• Expenses: TZS 28,200,000\r\n• Profit: TZS 14,300,000\r\n• Profit Margin: 33.6%\r\n\r\nMaterials Consumption:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Aluminium Sheets: 22,500 kg\r\n• Glass Panels: 3,800 pieces\r\n• Hardware Sets: 4,200 sets\r\n• Powder Coating: 580 kg\r\n• Packaging Materials: 320 units\r\n\r\nQuality Control:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Defect Rate: 2.8% (target: 3%)\r\n• Rework Rate: 3.5%\r\n• Customer Satisfaction: 94%\r\n• Warranty Claims: 2\r\n\r\nEquipment Performance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Cutting Machines: 96% uptime\r\n• Welding Equipment: 92% uptime\r\n• Powder Coating Line: 100% uptime\r\n• Assembly Stations: 98% uptime\r\n\r\nInventory Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Raw Aluminium: 8,500 kg (15 days supply)\r\n• Glass: 1,200 pieces (20 days)\r\n• Hardware: 2,800 sets (25 days)\r\n\r\nProjects Completed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Modern Villa Kigamboni - 250 units\r\n2. Commercial Complex Pwani - 180 units\r\n3. School Project Kinondoni - 120 units\r\n\r\nStaff Performance:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Production Staff: 18\r\n• Quality Control: 4\r\n• Installers: 12\r\n• Overtime Hours: 85 hours\r\n\r\nNext Month Projections:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Target Production: 4,000 units\r\n• New Equipment: 2 cutting machines\r\n• Staff Training: Advanced fabrication\r\n', 'sent', 6, '2026-05-25 00:03:55', '2026-06-08 14:34:01', 0, NULL, 0, 0, NULL, 6, 1, 7, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(79, 'KIWANJA MJI MPYA', 'weekly', 'IMEANDALIWA NA MIMI FINANCE', 'draft', 2, '2026-05-25 06:10:48', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(80, 'Sales Performance Report - June', 'monthly', 'SALES PERFORMANCE REPORT - JUNE 2024\n\nTotal Sales: TZS 250,000,000\nTarget: TZS 230,000,000\nAchievement: 108% (EXCEEDED)\n\nSales by Category:\n- Construction Services: TZS 120M (48%)\n- Aluminium Products: TZS 65M (26%)\n- Bricks & Timber: TZS 40M (16%)\n- Consulting: TZS 25M (10%)\n\nTop Sales Rep:\n1. John Mwita: TZS 85M\n2. Sarah Kijazi: TZS 70M\n3. James Ndege: TZS 55M\n\nNew Clients Acquired: 12\nLead Conversion Rate: 32%\n\nForecast for July: TZS 260M (target: TZS 240M)', 'sent', 1, '2026-05-25 06:12:00', '2026-05-25 06:12:08', 1, NULL, 0, 0, NULL, 1, 0, 3, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(81, 'Customer Satisfaction Survey', 'monthly', 'Customer Satisfaction Survey - June 2024\n\nSurvey Sample: 200 customers\nResponse Rate: 65%\n\nOverall Satisfaction: 4.6/5 (↑0.3 from May)\n\nBreakdown:\n- Product Quality: 4.7/5\n- Delivery Time: 4.4/5\n- Customer Service: 4.8/5\n- Pricing: 4.3/5\n- Communication: 4.5/5\n\nNet Promoter Score (NPS): +72 (Excellent)\n\nCustomer Complaints: 8 (↓5 from May)\n- Delivery delays: 3\n- Quality issues: 2\n- Billing errors: 2\n- Communication: 1\n\nAll complaints resolved within 48 hours', 'sent', 1, '2026-05-25 06:12:28', '2026-05-25 06:14:13', 1, NULL, 0, 0, NULL, 1, 0, 3, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(82, 'KIWANJA MJI MPYA', 'weekly', 'IMEANDALIWA NA MIMI FINANCE', 'sent', 1, '2026-05-25 06:17:54', '2026-05-25 09:08:13', 1, NULL, 1, 0, '2026-05-25 12:08:13', 1, 0, 2, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(83, 'June 2024 Financial Statement', 'monthly', 'FINANCIAL STATEMENT - JUNE 2024\n\nIncome Statement:\nRevenue: TZS 185,000,000\nCOGS: TZS 95,000,000\nGross Profit: TZS 90,000,000\nOperating Expenses: TZS 35,000,000\nNet Profit: TZS 55,000,000\n\nBalance Sheet:\nAssets: TZS 1.2 Billion\nLiabilities: TZS 450 Million\nEquity: TZS 750 Million\n\nCash Flow: Positive TZS 120 Million\n\nKey Ratios:\nCurrent Ratio: 2.5\nQuick Ratio: 1.8\nDebt-to-Equity: 0.6\nROE: 7.3%', 'sent', 1, '2026-05-25 09:07:34', '2026-05-25 09:08:05', 1, NULL, 0, 0, NULL, 1, 0, 2, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(84, 'Equipment Maintenance & Calibration Report', 'monthly', '🔧 SURVEY EQUIPMENT MAINTENANCE REPORT - JUNE 2024\r\n\r\nEquipment Inventory:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Stations: 4 units\r\n• GPS Receivers: 6 units\r\n• Levels: 3 units\r\n• Drones: 2 units\r\n• Tripods: 12 units\r\n• Prisms: 15 units\r\n\r\nMaintenance Performed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Total Station 2: Calibrated\r\n✓ GPS Base Station: Firmware updated\r\n✓ Drone 1: Battery replaced\r\n✓ All prisms: Cleaned and checked\r\n\r\nEquipment Requiring Service:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n⚠️ Total Station 3: Calibration due\r\n⚠️ Level 2: Need adjustment\r\n⚠️ Drone 2: Software update pending\r\n\r\nCalibration Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Stations: 75% calibrated\r\n• GPS Equipment: 100% calibrated\r\n• Levels: 66% calibrated\r\n\r\nCosts Incurred:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Calibration Services: TZS 450,000\r\n• Repairs: TZS 280,000\r\n• Parts: TZS 120,000\r\n• Total: TZS 850,000\r\n\r\nUpcoming Equipment Purchases:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• New Total Station (Budget: TZS 8M)\r\n• RTK GPS System (Budget: TZS 5M)\r\n• Extra batteries (Budget: TZS 200,000)\r\n\r\nSoftware Updates:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ CAD Software: Updated to 2024 version\r\n✓ GIS Software: Updated\r\n✓ Data Processing Software: Updated\r\n\r\nTraining on Equipment:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Station operation: 2 staff\r\n• Drone operation: 1 staff (certified)\r\n• GPS data processing: 3 staff\r\n\r\nEquipment Storage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• All equipment properly stored\r\n• Climate control functional\r\n• Security system operational\r\n\r\nRecommendations:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Schedule regular calibration every 3 months\r\n2. Purchase backup batteries\r\n3. Implement equipment tracking system\r\n', 'sent', 4, '2026-05-25 09:49:45', '2026-06-05 11:38:28', 0, NULL, 0, 1, '2026-06-05 14:38:28', 4, 1, 8, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(85, 'Equipment Maintenance & Calibration Report', 'monthly', '🔧 SURVEY EQUIPMENT MAINTENANCE REPORT - JUNE 2024\r\n\r\nEquipment Inventory:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Stations: 4 units\r\n• GPS Receivers: 6 units\r\n• Levels: 3 units\r\n• Drones: 2 units\r\n• Tripods: 12 units\r\n• Prisms: 15 units\r\n\r\nMaintenance Performed:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ Total Station 2: Calibrated\r\n✓ GPS Base Station: Firmware updated\r\n✓ Drone 1: Battery replaced\r\n✓ All prisms: Cleaned and checked\r\n\r\nEquipment Requiring Service:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n⚠️ Total Station 3: Calibration due\r\n⚠️ Level 2: Need adjustment\r\n⚠️ Drone 2: Software update pending\r\n\r\nCalibration Status:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Stations: 75% calibrated\r\n• GPS Equipment: 100% calibrated\r\n• Levels: 66% calibrated\r\n\r\nCosts Incurred:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Calibration Services: TZS 450,000\r\n• Repairs: TZS 280,000\r\n• Parts: TZS 120,000\r\n• Total: TZS 850,000\r\n\r\nUpcoming Equipment Purchases:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• New Total Station (Budget: TZS 8M)\r\n• RTK GPS System (Budget: TZS 5M)\r\n• Extra batteries (Budget: TZS 200,000)\r\n\r\nSoftware Updates:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n✓ CAD Software: Updated to 2024 version\r\n✓ GIS Software: Updated\r\n✓ Data Processing Software: Updated\r\n\r\nTraining on Equipment:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Total Station operation: 2 staff\r\n• Drone operation: 1 staff (certified)\r\n• GPS data processing: 3 staff\r\n\r\nEquipment Storage:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• All equipment properly stored\r\n• Climate control functional\r\n• Security system operational\r\n\r\nRecommendations:\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n1. Schedule regular calibration every 3 months\r\n2. Purchase backup batteries\r\n3. Implement equipment tracking system\r\n', 'sent', 6, '2026-05-25 09:53:09', '2026-06-08 13:54:34', 0, NULL, 0, 1, '2026-06-08 16:54:34', 6, 1, 4, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(86, 'john', 'weekly', 'MALIPO 120,000,000 ILA KALIPA 80,000,000 IMEBAKI 40,000,000', 'draft', 10, '2026-05-25 10:12:39', '2026-05-25 10:16:45', 0, NULL, 0, 1, '2026-05-25 13:16:45', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(87, 'john', 'weekly', 'MALIPO 120,000,000 ILA KALIPA 80,000,000 IMEBAKI 40,000,000', 'sent', 1, '2026-05-25 10:12:51', '2026-05-25 10:13:02', 1, NULL, 0, 0, NULL, 1, 0, 10, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(88, 'Daily Report - 02/06/2026', 'daily', '===========================================\nGEO TRAVERSE - DAILY REPORT\n===========================================\nGenerated: 02/06/2026, 18:25:21\nDepartment: Super Admin\n===========================================\n\n📊 PROJECT SUMMARY\n===========================================\nTotal Projects: 12\n├── Pending: 2\n├── In Progress: 3\n├── Completed: 5\n└── Approved: 0\n\n💰 FINANCIAL SUMMARY\n===========================================\nTotal Income: TZS 1,233,000,000\nTotal Expenses: TZS 16,250,000\nNet Profit: TZS 1,216,750,000\nPending Payments: TZS 250,000,000\n\n👥 EMPLOYEES SUMMARY\n===========================================\nTotal Employees: 26\nActive Staff: 26\n\n📋 PROJECTS BREAKDOWN\n===========================================\n\n• Coastal Zone Management Plan\n   Client: Environmental Agency\n   Amount: TZS 95,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Coast Region\n\n• School Construction\n   Client: Ministry of Education\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 0%\n   Location: Kinondoni\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Luxury Apartment Complex\n   Client: Real Estate Developer\n   Amount: TZS 450,000,000\n   Status: \n   Progress: 70%\n   Location: Dar es Salaam\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: \n   Progress: 100%\n   Location: Dar es Salaam\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n• Hospital Design Project\n   Client: Ministry of Health\n   Amount: TZS 180,000,000\n   Status: pending\n   Progress: 30%\n   Location: Dodoma\n\n• Data Center Expansion\n   Client: Telecom Company\n   Amount: TZS 450,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Corporate Training Center\n   Client: Private Sector\n   Amount: TZS 250,000,000\n   Status: in_progress\n   Progress: 30%\n   Location: Arusha\n\n• National IT Infrastructure\n   Client: Ministry of ICT\n   Amount: TZS 1,200,000,000\n   Status: pending\n   Progress: 10%\n   Location: Dodoma\n\n• GeoTraverse HQ Construction\n   Client: GeoTraverse Company\n   Amount: TZS 850,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Dar es Salaam CBD\n\n===========================================\nEnd of Report\n===========================================', 'sent', 1, '2026-06-02 15:25:21', '2026-06-02 16:25:17', 0, NULL, 1, 0, '2026-06-02 19:25:17', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(89, 'Daily Report - 02/06/2026', 'daily', '===========================================\nGEO TRAVERSE - DAILY REPORT\n===========================================\nGenerated: 02/06/2026, 18:25:52\nDepartment: Super Admin\n===========================================\n\n📊 PROJECT SUMMARY\n===========================================\nTotal Projects: 12\n├── Pending: 2\n├── In Progress: 3\n├── Completed: 5\n└── Approved: 0\n\n💰 FINANCIAL SUMMARY\n===========================================\nTotal Income: TZS 1,233,000,000\nTotal Expenses: TZS 16,250,000\nNet Profit: TZS 1,216,750,000\nPending Payments: TZS 250,000,000\n\n👥 EMPLOYEES SUMMARY\n===========================================\nTotal Employees: 26\nActive Staff: 26\n\n📋 PROJECTS BREAKDOWN\n===========================================\n\n• Coastal Zone Management Plan\n   Client: Environmental Agency\n   Amount: TZS 95,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Coast Region\n\n• School Construction\n   Client: Ministry of Education\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 0%\n   Location: Kinondoni\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Luxury Apartment Complex\n   Client: Real Estate Developer\n   Amount: TZS 450,000,000\n   Status: \n   Progress: 70%\n   Location: Dar es Salaam\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: \n   Progress: 100%\n   Location: Dar es Salaam\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n• GeoTraverse HQ Construction\n   Client: GeoTraverse Company\n   Amount: TZS 850,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Dar es Salaam CBD\n\n• National IT Infrastructure\n   Client: Ministry of ICT\n   Amount: TZS 1,200,000,000\n   Status: pending\n   Progress: 10%\n   Location: Dodoma\n\n• Corporate Training Center\n   Client: Private Sector\n   Amount: TZS 250,000,000\n   Status: in_progress\n   Progress: 30%\n   Location: Arusha\n\n• Data Center Expansion\n   Client: Telecom Company\n   Amount: TZS 450,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Hospital Design Project\n   Client: Ministry of Health\n   Amount: TZS 180,000,000\n   Status: pending\n   Progress: 30%\n   Location: Dodoma\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n===========================================\nEnd of Report\n===========================================', 'sent', 1, '2026-06-02 15:25:52', '2026-06-02 16:25:14', 0, NULL, 1, 0, '2026-06-02 19:25:14', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0);
INSERT INTO `reports` (`id`, `title`, `period`, `content`, `status`, `department_id`, `created_at`, `updated_at`, `is_viewed_by_admin`, `sent_from_dept`, `deleted_by_admin`, `deleted_by_department`, `deleted_at`, `sent_to_department`, `is_viewed_by_department`, `sent_from_department`, `file_path`, `file_type`, `file_name`, `created_by`, `file_size`, `updated_by`, `is_deleted`) VALUES
(90, 'Daily Report - 02/06/2026', 'daily', '===========================================\nGEO TRAVERSE - DAILY REPORT (LAST 24 HOURS)\n===========================================\nGenerated: 02/06/2026, 19:25:23\nDepartment: Super Admin\nReport Period: 01/06/2026 - 02/06/2026\n===========================================\n\n📊 PROJECT SUMMARY\n===========================================\nTotal Projects: 12\n├── Pending: 2\n├── In Progress: 3\n├── Completed: 5\n└── Approved: 0\n\n💰 FINANCIAL SUMMARY\n===========================================\nTotal Income: TZS 1,233,000,000\nTotal Expenses: TZS 16,250,000\nNet Profit: TZS 1,216,750,000\nPending Payments: TZS 250,000,000\n\n📋 DAILY WORK SUMMARY (Daily Report (Last 24 Hours))\n===========================================\nTotal Daily Work Records: 0\nTotal Budget: TZS 0\nTotal Paid Amount: TZS 0\n\n👥 EMPLOYEES SUMMARY\n===========================================\nTotal Employees: 26\nActive Staff: 26\n\n📋 PROJECTS BREAKDOWN\n===========================================\n\n• Coastal Zone Management Plan\n   Client: Environmental Agency\n   Amount: TZS 95,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Coast Region\n\n• School Construction\n   Client: Ministry of Education\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 0%\n   Location: Kinondoni\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Luxury Apartment Complex\n   Client: Real Estate Developer\n   Amount: TZS 450,000,000\n   Status: \n   Progress: 70%\n   Location: Dar es Salaam\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: \n   Progress: 100%\n   Location: Dar es Salaam\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n• GeoTraverse HQ Construction\n   Client: GeoTraverse Company\n   Amount: TZS 850,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Dar es Salaam CBD\n\n• National IT Infrastructure\n   Client: Ministry of ICT\n   Amount: TZS 1,200,000,000\n   Status: pending\n   Progress: 10%\n   Location: Dodoma\n\n• Corporate Training Center\n   Client: Private Sector\n   Amount: TZS 250,000,000\n   Status: in_progress\n   Progress: 30%\n   Location: Arusha\n\n• Data Center Expansion\n   Client: Telecom Company\n   Amount: TZS 450,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Hospital Design Project\n   Client: Ministry of Health\n   Amount: TZS 180,000,000\n   Status: pending\n   Progress: 30%\n   Location: Dodoma\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n===========================================\nEnd of Report\nGenerated by GeoTraverse ERP System\n===========================================', 'sent', 1, '2026-06-02 16:25:23', '2026-06-02 17:09:23', 0, NULL, 1, 0, '2026-06-02 20:09:23', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(91, 'Weekly Report - 02/06/2026', 'weekly', '===========================================\nGEO TRAVERSE - WEEKLY REPORT (LAST 7 DAYS)\n===========================================\nGenerated: 02/06/2026, 19:25:52\nDepartment: Super Admin\nReport Period: 26/05/2026 - 02/06/2026\n===========================================\n\n📊 PROJECT SUMMARY\n===========================================\nTotal Projects: 12\n├── Pending: 2\n├── In Progress: 3\n├── Completed: 5\n└── Approved: 0\n\n💰 FINANCIAL SUMMARY\n===========================================\nTotal Income: TZS 1,233,000,000\nTotal Expenses: TZS 16,250,000\nNet Profit: TZS 1,216,750,000\nPending Payments: TZS 250,000,000\n\n📋 DAILY WORK SUMMARY (Weekly Report (Last 7 Days))\n===========================================\nTotal Daily Work Records: 0\nTotal Budget: TZS 0\nTotal Paid Amount: TZS 0\n\n👥 EMPLOYEES SUMMARY\n===========================================\nTotal Employees: 26\nActive Staff: 26\n\n📋 PROJECTS BREAKDOWN\n===========================================\n\n• Coastal Zone Management Plan\n   Client: Environmental Agency\n   Amount: TZS 95,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Coast Region\n\n• School Construction\n   Client: Ministry of Education\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 0%\n   Location: Kinondoni\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Luxury Apartment Complex\n   Client: Real Estate Developer\n   Amount: TZS 450,000,000\n   Status: \n   Progress: 70%\n   Location: Dar es Salaam\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: \n   Progress: 100%\n   Location: Dar es Salaam\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n• GeoTraverse HQ Construction\n   Client: GeoTraverse Company\n   Amount: TZS 850,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Dar es Salaam CBD\n\n• National IT Infrastructure\n   Client: Ministry of ICT\n   Amount: TZS 1,200,000,000\n   Status: pending\n   Progress: 10%\n   Location: Dodoma\n\n• Corporate Training Center\n   Client: Private Sector\n   Amount: TZS 250,000,000\n   Status: in_progress\n   Progress: 30%\n   Location: Arusha\n\n• Data Center Expansion\n   Client: Telecom Company\n   Amount: TZS 450,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Hospital Design Project\n   Client: Ministry of Health\n   Amount: TZS 180,000,000\n   Status: pending\n   Progress: 30%\n   Location: Dodoma\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n===========================================\nEnd of Report\nGenerated by GeoTraverse ERP System\n===========================================', 'sent', 1, '2026-06-02 16:25:52', '2026-06-02 17:09:19', 0, NULL, 1, 0, '2026-06-02 20:09:19', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(92, 'Daily Report - 02/06/2026', 'daily', '===========================================\nGEO TRAVERSE - DAILY REPORT (LAST 24 HOURS)\n===========================================\nGenerated: 02/06/2026, 20:09:31\nDepartment: Super Admin\nReport Period: 01/06/2026 - 02/06/2026\n===========================================\n\n📊 PROJECT SUMMARY\n===========================================\nTotal Projects: 12\n├── Pending: 2\n├── In Progress: 3\n├── Completed: 5\n└── Approved: 0\n\n💰 FINANCIAL SUMMARY\n===========================================\nTotal Income: TZS 1,233,000,000\nTotal Expenses: TZS 16,250,000\nNet Profit: TZS 1,216,750,000\nPending Payments: TZS 250,000,000\n\n📋 DAILY WORK SUMMARY (Daily Report (Last 24 Hours))\n===========================================\nPeriod: 01/06/2026 - 02/06/2026\nTotal Daily Work Records in this period: 0\nTotal Budget in this period: TZS 0\nTotal Paid Amount in this period: TZS 0\n\n👥 EMPLOYEES SUMMARY\n===========================================\nTotal Employees: 26\nActive Staff: 26\n\n📋 PROJECTS BREAKDOWN\n===========================================\n\n• Coastal Zone Management Plan\n   Client: Environmental Agency\n   Amount: TZS 95,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Coast Region\n\n• School Construction\n   Client: Ministry of Education\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 0%\n   Location: Kinondoni\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Luxury Apartment Complex\n   Client: Real Estate Developer\n   Amount: TZS 450,000,000\n   Status: \n   Progress: 70%\n   Location: Dar es Salaam\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: \n   Progress: 100%\n   Location: Dar es Salaam\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n• GeoTraverse HQ Construction\n   Client: GeoTraverse Company\n   Amount: TZS 850,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Dar es Salaam CBD\n\n• National IT Infrastructure\n   Client: Ministry of ICT\n   Amount: TZS 1,200,000,000\n   Status: pending\n   Progress: 10%\n   Location: Dodoma\n\n• Corporate Training Center\n   Client: Private Sector\n   Amount: TZS 250,000,000\n   Status: in_progress\n   Progress: 30%\n   Location: Arusha\n\n• Data Center Expansion\n   Client: Telecom Company\n   Amount: TZS 450,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Hospital Design Project\n   Client: Ministry of Health\n   Amount: TZS 180,000,000\n   Status: pending\n   Progress: 30%\n   Location: Dodoma\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n📋 DAILY WORK RECORDS\n===========================================\nNo daily work records found for the period 01/06/2026 - 02/06/2026\n\n===========================================\nEnd of Report\nGenerated by GeoTraverse ERP System\n===========================================', 'sent', 1, '2026-06-02 17:09:31', '2026-06-02 17:21:30', 0, NULL, 1, 0, '2026-06-02 20:21:30', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(93, 'Quarterly Report - 02/06/2026', 'quarterly', '===========================================\nGEO TRAVERSE - QUARTERLY REPORT (LAST 3 MONTHS)\n===========================================\nGenerated: 02/06/2026, 20:09:53\nDepartment: Super Admin\nReport Period: 02/03/2026 - 02/06/2026\n===========================================\n\n📊 PROJECT SUMMARY\n===========================================\nTotal Projects: 12\n├── Pending: 2\n├── In Progress: 3\n├── Completed: 5\n└── Approved: 0\n\n💰 FINANCIAL SUMMARY\n===========================================\nTotal Income: TZS 1,233,000,000\nTotal Expenses: TZS 16,250,000\nNet Profit: TZS 1,216,750,000\nPending Payments: TZS 250,000,000\n\n📋 DAILY WORK SUMMARY (Quarterly Report (Last 3 Months))\n===========================================\nPeriod: 02/03/2026 - 02/06/2026\nTotal Daily Work Records in this period: 0\nTotal Budget in this period: TZS 0\nTotal Paid Amount in this period: TZS 0\n\n👥 EMPLOYEES SUMMARY\n===========================================\nTotal Employees: 26\nActive Staff: 26\n\n📋 PROJECTS BREAKDOWN\n===========================================\n\n• Coastal Zone Management Plan\n   Client: Environmental Agency\n   Amount: TZS 95,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Coast Region\n\n• School Construction\n   Client: Ministry of Education\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 0%\n   Location: Kinondoni\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Luxury Apartment Complex\n   Client: Real Estate Developer\n   Amount: TZS 450,000,000\n   Status: \n   Progress: 70%\n   Location: Dar es Salaam\n\n• Window Frame Supply - Housing Estate\n   Client: Real Estate Developer\n   Amount: TZS 180,000,000\n   Status: \n   Progress: 100%\n   Location: Dar es Salaam\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n• GeoTraverse HQ Construction\n   Client: GeoTraverse Company\n   Amount: TZS 850,000,000\n   Status: in_progress\n   Progress: 45%\n   Location: Dar es Salaam CBD\n\n• National IT Infrastructure\n   Client: Ministry of ICT\n   Amount: TZS 1,200,000,000\n   Status: pending\n   Progress: 10%\n   Location: Dodoma\n\n• Corporate Training Center\n   Client: Private Sector\n   Amount: TZS 250,000,000\n   Status: in_progress\n   Progress: 30%\n   Location: Arusha\n\n• Data Center Expansion\n   Client: Telecom Company\n   Amount: TZS 450,000,000\n   Status: completed\n   Progress: 100%\n   Location: Dar es Salaam\n\n• Hospital Design Project\n   Client: Ministry of Health\n   Amount: TZS 180,000,000\n   Status: pending\n   Progress: 30%\n   Location: Dodoma\n\n• Eco-Friendly Housing Design\n   Client: Green Building Council\n   Amount: TZS 75,000,000\n   Status: completed\n   Progress: 100%\n   Location: Arusha\n\n📋 DAILY WORK RECORDS\n===========================================\nNo daily work records found for the period 02/03/2026 - 02/06/2026\n\n===========================================\nEnd of Report\nGenerated by GeoTraverse ERP System\n===========================================', 'sent', 1, '2026-06-02 17:09:53', '2026-06-02 17:21:28', 0, NULL, 1, 0, '2026-06-02 20:21:28', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(94, 'leo', 'daily', 'DAILY REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated: 02/06/2026, 20:21:42\nDate: 2026-06-02\nDepartment: Super Admin\n\n📈 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 0\nTotal Expenses: TZS 0\nNet Profit: TZS 0\n\n📋 PROJECT STATUS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Projects: 12\nPending: 2\nIn Progress: 3\nCompleted: 5\nApproved: 0\n\n📅 DAILY WORK SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Budget: TZS 0\nTotal Amount: TZS 0\nVariance: TZS 0\nRecords: 3\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'draft', 1, '2026-06-02 17:21:42', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(95, 'mwaka', 'annual', 'ANNUAL REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated: 02/06/2026, 20:22:40\nPeriod: 2026-01-02 to 2026-12-02\nDepartment: Super Admin\n\n📈 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 233,000,000\nTotal Expenses: TZS 16,250,000\nNet Profit: TZS 216,750,000\n\n📋 PROJECT STATUS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Projects: 12\nPending: 2\nIn Progress: 3\nCompleted: 5\nApproved: 0\n\n📅 DAILY WORK SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Budget: TZS 0\nTotal Amount: TZS 0\nVariance: TZS 0\nRecords: 8\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'draft', 1, '2026-06-02 17:22:40', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(96, 'leo', 'daily', 'DAILY REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated: 03/06/2026, 11:19:57\nDate: 2026-06-03\nDepartment: Super Admin\n\n📈 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 0\nTotal Expenses: TZS 0\nNet Profit: TZS 0\n\n📋 PROJECT STATUS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Projects: 12\nPending: 2\nIn Progress: 3\nCompleted: 5\nApproved: 0\n\n📅 DAILY WORK SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Budget: TZS 0\nTotal Amount: TZS 0\nVariance: TZS 0\nRecords: 0\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'draft', 1, '2026-06-03 08:19:57', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(97, 'leo', 'daily', 'DAILY REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated: 03/06/2026, 13:03:39\nPeriod: 03/06/2026 to 03/06/2026\nDepartment: Architectural\n\n📈 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 0\nTotal Expenses: TZS 0\nNet Profit: TZS 0\n\n📋 PROJECT STATUS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Projects: 5\nPending: 0\nIn Progress: 2\nCompleted: 2\n\n📅 DAILY WORK SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Budget: TZS 0\nTotal Amount: TZS 0\nVariance: TZS 0\nRecords: 0', 'draft', 9, '2026-06-03 10:03:39', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(98, 'mwaka', 'annual', 'ANNUAL REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated: 03/06/2026, 13:58:10\nPeriod: 03/06/2025 to 03/06/2026\nDepartment: Super Admin\nCreated by: Super Admin\nEmail: admin@geotraverse.com\n\n📈 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 233,000,000\nTotal Expenses: TZS 16,250,000\nNet Profit: TZS 216,750,000\n\n📋 PROJECT STATUS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Projects: 12\nPending: 0\nIn Progress: 5\nCompleted: 4\nApproved: 1\n\n📅 DAILY WORK SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Budget: TZS 0\nTotal Amount: TZS 0\nVariance: TZS 0\nRecords: 8\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nReport generated by: Super Admin\nDate: 03/06/2026, 13:58:10', 'draft', 1, '2026-06-03 10:58:10', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(99, 'mwaka', 'weekly', 'WEEKLY REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated by: Executive Secretary\nGenerated: 03/06/2026, 15:43:16\nPeriod: 27/05/2026 to 03/06/2026\nDepartment: Secretary Office\n\n📈 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 0\nTotal Expenses: TZS 0\nNet Profit: TZS 0\n\n📋 VISITORS SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Visitors: 20\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nReport generated by: Executive Secretary\nDate: 03/06/2026, 15:43:16', 'draft', 5, '2026-06-03 12:43:16', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(100, 'ANNUALY', 'annual', 'ANNUAL REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated by: Executive Secretary\nGenerated: 03/06/2026, 17:22:10\nPeriod: 03/06/2025 to 03/06/2026\nDepartment: Secretary Office\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n📊 VISITORS SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Visitors: 1\nDaily Average: 0.0\n\n📋 Visitors Details:\n• jackson - Manager (2026-05-25 01:58)\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n💰 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 2,500,000\nTotal Expenses: TZS 250,000\nNet Profit: TZS 2,250,000\n\n📋 REQUESTS SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Requests: 4\nPending: 1\nApproved: 0\nCancelled: 0\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nReport generated by: Executive Secretary\nDate: 03/06/2026, 17:22:10', 'draft', 5, '2026-06-03 14:22:10', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(101, 'leo', 'daily', 'DAILY REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated by: Sales Manager\nGenerated: 03/06/2026, 18:02:39\nPeriod: 03/06/2026 to 03/06/2026\nDepartment: Sales & Marketing\n\n📊 CAMPAIGN SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nActive Campaigns: 6\nCompleted Campaigns: 2\nTotal Budget: TZS 67,800,000\nTotal Spent: TZS 32,270,000\n\n💰 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 0\nTotal Expenses: TZS 0\nNet Profit: TZS 0\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nReport generated by: Sales Manager\nDate: 03/06/2026, 18:02:39', 'draft', 3, '2026-06-03 15:02:39', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(102, 'employee', 'weekly', '📎 UPLOADED DOCUMENT: report_84.pdf\nType: application/pdf\nSize: 98.23 KB\nUploaded by: Sales Manager\nUploaded: 03/06/2026, 20:31:28\n\n[Document uploaded - click Preview to view]', 'draft', 3, '2026-06-03 17:31:28', '2026-06-03 17:31:42', 0, NULL, 0, 1, '2026-06-03 20:31:42', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(103, 'employee', 'monthly', 'No content', 'draft', 3, '2026-06-03 17:56:53', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(104, 'MYULA', 'monthly', '📎 UPLOADED DOCUMENT: report_84.pdf\nType: application/pdf\nSize: 98.23 KB\nUploaded by: Sales Manager\nUploaded: 03/06/2026, 21:07:12\n\n📁 File stored in system - Click the document name to preview/download.', 'draft', 3, '2026-06-03 18:07:12', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(105, 'employee', 'monthly', '📎 UPLOADED DOCUMENT: report_84.pdf\nType: application/pdf\nSize: 98.23 KB\nUploaded by: Sales Manager\nUploaded: 04/06/2026, 11:14:20\n\n📁 File stored in system - Click the document name to preview/download.', 'draft', 3, '2026-06-04 08:14:20', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(106, 'MYULA', 'monthly', '', 'draft', 1, '2026-06-04 14:25:50', '2026-06-06 18:29:39', 0, NULL, 0, 1, '2026-06-06 21:29:39', NULL, 0, NULL, '../uploads/reports/1780583150_6a218aeea0a3e.pdf', 'application/pdf', '1780583150_6a218aeea0a3e.pdf', 'Super Admin', 100589, NULL, 0),
(107, 'leo', 'daily', 'DAILY REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated by: Construction Manager\nGenerated: 05/06/2026, 12:35:34\nPeriod: 05/06/2026 to 06/06/2026\nDepartment: Construction\n\n📈 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 0\nTotal Expenses: TZS 0\nNet Profit: TZS 0\n\n📋 PROJECT STATUS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Projects: 8\nPending: 2\nIn Progress: 4\nCompleted: 2\n\n📅 DAILY WORK SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Budget: TZS 0\nTotal Amount: TZS 0\nVariance: TZS 0\nRecords: 0\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nReport generated by: Construction Manager\nDate: 05/06/2026, 12:35:34', 'draft', 11, '2026-06-05 09:35:34', '2026-06-05 11:06:22', 0, NULL, 0, 1, '2026-06-05 14:06:22', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(108, 'employee', 'monthly', '📎 UPLOADED DOCUMENT: WORK CONTRACT 2.pdf\nType: application/pdf\nSize: 23.97 KB\nUploaded by: Executive Secretary\nUploaded: 05/06/2026, 13:01:11\n\n[Document uploaded - click View to preview]', 'draft', 5, '2026-06-05 10:01:11', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(109, 'employee', 'monthly', '📎 UPLOADED DOCUMENT: WORK CONTRACT AGREEMENT.pdf\nType: application/pdf\nSize: 23.81 KB\nUploaded by: Executive Secretary\nUploaded: 05/06/2026, 13:20:32\n\n[Document uploaded]', 'draft', 5, '2026-06-05 10:20:32', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(110, 'moden new', 'monthly', '📎 UPLOADED DOCUMENT: report_104.pdf\nType: application/pdf\nSize: 72.88 KB\nUploaded by: Manager User\nUploaded: 05/06/2026, 14:04:04\n\n[Document uploaded - click Preview to view]', 'draft', 4, '2026-06-05 11:04:04', '2026-06-05 11:38:22', 0, NULL, 0, 1, '2026-06-05 14:38:22', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(111, 'leo', 'daily', 'DAILY REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated by: Construction Manager\nGenerated: 05/06/2026, 14:06:19\nPeriod: 05/06/2026 to 06/06/2026\nDepartment: Construction\n\n📈 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 0\nTotal Expenses: TZS 0\nNet Profit: TZS 0\n\n📋 PROJECT STATUS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Projects: 8\nPending: 2\nIn Progress: 4\nCompleted: 2\n\n📋 REQUESTS SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Requests: 12\nPending: 0\nApproved: 0\nCancelled: 0\n\n📅 DAILY WORK SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Budget: TZS 0\nTotal Amount: TZS 0\nVariance: TZS 0\nRecords: 0\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nReport generated by: Construction Manager\nDate: 05/06/2026, 14:06:19', 'draft', 11, '2026-06-05 11:06:19', '2026-06-05 11:06:24', 0, NULL, 0, 1, '2026-06-05 14:06:24', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(112, 'employee', 'monthly', '📎 UPLOADED DOCUMENT: report_104.pdf\nType: application/pdf\nSize: 72.88 KB\nUploaded by: Manager User\nUploaded: 05/06/2026, 14:36:48\n\n[Document uploaded - click Preview to view]', 'draft', 4, '2026-06-05 11:36:48', '2026-06-05 11:38:25', 0, NULL, 0, 1, '2026-06-05 14:38:25', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(113, 'moden new', 'monthly', 'No content', 'draft', 4, '2026-06-05 11:37:08', '2026-06-05 11:38:20', 0, NULL, 0, 1, '2026-06-05 14:38:20', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(114, 'MYULA', 'monthly', '📎 UPLOADED DOCUMENT: all_reports.pdf\nType: application/pdf\nSize: 872.27 KB\nUploaded by: Manager User\nUploaded: 05/06/2026, 14:38:13\n\n[Document uploaded - click Preview to view]', 'draft', 4, '2026-06-05 11:38:13', '2026-06-05 11:38:18', 0, NULL, 0, 1, '2026-06-05 14:38:18', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(115, 'employee', 'monthly', '📎 UPLOADED DOCUMENT: 1 (3).jpg\nType: image/jpeg\nSize: 103.46 KB\nUploaded by: Town Planning Manager\nUploaded: 05/06/2026, 16:32:02\n\n[Document uploaded - click Preview to view]', 'draft', 8, '2026-06-05 13:32:02', '2026-06-07 15:25:05', 0, NULL, 0, 1, '2026-06-07 18:25:05', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(116, 'employee', 'monthly', '📎 UPLOADED REPORT\nTitle: employee\nFile: download.pdf\nUploaded: 2026-06-06 20:22:01', 'draft', 2, '2026-06-06 18:22:01', '2026-06-06 18:22:34', 0, NULL, 0, 1, '2026-06-06 21:22:34', NULL, 0, NULL, '/geotraverse/frontend/assets/uploads/reports/report_1780770121_6a246549cd82d.pdf', 'application/pdf', 'download.pdf', 'Finance Manager', 100589, NULL, 0),
(117, 'moden new', 'monthly', '📎 UPLOADED REPORT\nTitle: moden new\nFile: report_104.pdf\nUploaded: 2026-06-06 20:29:30', 'draft', 1, '2026-06-06 18:29:30', '2026-06-06 18:29:36', 0, NULL, 0, 1, '2026-06-06 21:29:36', NULL, 0, NULL, '/geotraverse/frontend/assets/uploads/reports/report_1780770570_6a24670a178f6.pdf', 'application/pdf', 'report_104.pdf', 'Super Admin', 74628, NULL, 0),
(118, 'employee', 'monthly', '📎 UPLOADED REPORT\nTitle: employee\nFile: alumian.pdf\nUploaded: 2026-06-06 20:37:28', 'draft', 2, '2026-06-06 18:37:28', '2026-06-06 18:37:33', 0, NULL, 0, 1, '2026-06-06 21:37:33', NULL, 0, NULL, '/geotraverse/frontend/assets/uploads/reports/report_1780771048_6a2468e8a6bea.pdf', 'application/pdf', 'alumian.pdf', 'Finance Manager', 67065, NULL, 0),
(119, 'MYULA', 'monthly', '📎 UPLOADED REPORT\nTitle: MYULA\nFile: download.pdf\nUploaded: 2026-06-06 21:10:05', 'draft', 2, '2026-06-06 19:10:05', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, '/geotraverse/frontend/assets/uploads/reports/report_1780773005_6a24708ddb055.pdf', 'application/pdf', 'download.pdf', 'Finance Manager', 100589, NULL, 0),
(120, 'ANNUALY', 'annual', 'ANNUAL REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated: 07/06/2026, 13:52:31\nPeriod: 07/06/2025 to 07/06/2026\nDepartment: Super Admin\nCreated by: Super Admin\n\n📈 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 233,000,000\nTotal Expenses: TZS 16,250,000\nNet Profit: TZS 216,750,000\n\n📋 PROJECT STATUS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Projects: 19\nPending: 1\nIn Progress: 8\nCompleted: 6\nApproved: 1\n\n📅 DAILY WORK SUMMARY (Period)\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nRecords in period: 3\nTotal Budget: TZS 0\nTotal Amount: TZS 0\nVariance: TZS 0', 'draft', 1, '2026-06-07 10:52:31', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(121, 'ISAAC', 'daily', 'DAILY REPORT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nGenerated: 07/06/2026, 15:24:20\nPeriod: 07/06/2026 to 07/06/2026\nDepartment: Super Admin\nCreated by: Super Admin\n\n📈 FINANCIAL SUMMARY\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Income: TZS 0\nTotal Expenses: TZS 0\nNet Profit: TZS 0\n\n📋 PROJECT STATUS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nTotal Projects: 19\nPending: 1\nIn Progress: 8\nCompleted: 6\nApproved: 1\n\n📅 DAILY WORK SUMMARY (Period)\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nRecords in period: 0\nTotal Budget: TZS 0\nTotal Amount: TZS 0\nVariance: TZS 0', 'draft', 1, '2026-06-07 12:24:20', NULL, 0, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0),
(122, 'employee', 'monthly', '📎 UPLOADED DOCUMENT: 1 (2).jpg\nType: image/jpeg\nSize: 82.76 KB\nUploaded by: Executive Secretary\nUploaded: 08/06/2026, 16:49:50\n\n[Document uploaded]', 'draft', 5, '2026-06-08 13:49:50', '2026-06-08 13:49:55', 0, NULL, 0, 1, '2026-06-08 16:49:55', NULL, 0, NULL, NULL, NULL, NULL, 'System', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `report_documents`
--

CREATE TABLE `report_documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `period` enum('daily','weekly','monthly','quarterly','annual') DEFAULT 'monthly',
  `department_id` int(11) DEFAULT 1,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Super Administrator', 'Full system access - can manage everything'),
(2, 'Administrator', 'Can manage users and view all data'),
(3, 'Manager', 'Can manage department employees and projects'),
(4, 'Finance Manager', 'Manages financial transactions and budgets'),
(5, 'Accountant', 'Handles accounting and financial reporting'),
(6, 'HR Manager', 'Manages employee records and recruitment'),
(7, 'Department Head', 'Manages department operations'),
(8, 'Team Leader', 'Leads a team within department'),
(9, 'Staff', 'Regular employee with basic access'),
(10, 'Intern', 'Temporary staff with limited access');

-- --------------------------------------------------------

--
-- Table structure for table `survey_requests`
--

CREATE TABLE `survey_requests` (
  `id` int(11) NOT NULL,
  `request_no` varchar(100) NOT NULL,
  `client_name` varchar(200) NOT NULL,
  `client_phone` varchar(50) DEFAULT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `property_location` varchar(200) NOT NULL,
  `survey_type` enum('boundary','topographic','subdivision','construction_staking','land_valuation','gis_mapping') DEFAULT 'boundary',
  `area_size` decimal(10,2) DEFAULT 0.00,
  `request_date` date DEFAULT NULL,
  `survey_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `estimated_cost` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','scheduled','in_progress','completed','cancelled','invoiced') DEFAULT 'pending',
  `department_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_by_department` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_user_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `survey_requests`
--

INSERT INTO `survey_requests` (`id`, `request_no`, `client_name`, `client_phone`, `client_email`, `property_location`, `survey_type`, `area_size`, `request_date`, `survey_date`, `description`, `estimated_cost`, `status`, `department_id`, `created_by`, `assigned_to`, `created_at`, `updated_at`, `deleted_by_department`, `deleted_by_admin`, `deleted_by_user_id`, `deleted_at`) VALUES
(1, 'SRV/2024/001', 'James Ndege', NULL, NULL, 'Mbezi Beach', 'boundary', 2.50, '2024-05-01', NULL, NULL, 0.00, 'completed', 10, 18, NULL, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(2, 'SRV/2024/002', 'TANROADS', NULL, NULL, 'Kigamboni', 'topographic', 15.00, '2024-05-10', NULL, NULL, 0.00, 'in_progress', 10, 18, NULL, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(3, 'SRV/2024/003', 'Kinondoni Municipal', NULL, NULL, 'Kinondoni', 'subdivision', 50.00, '2024-05-15', NULL, NULL, 0.00, 'scheduled', 10, 18, NULL, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `department_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_by_department` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_user_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `priority`, `due_date`, `completed_date`, `status`, `department_id`, `created_by`, `created_at`, `updated_at`, `deleted_by_department`, `deleted_by_admin`, `deleted_by_user_id`, `deleted_at`) VALUES
(1, 'Project Review Meeting', 'Weekly project status review with all departments', 6, 'high', '2024-05-20', NULL, 'pending', 4, 6, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(2, 'Budget Approval', 'Review and approve Q2 budget for all departments', 2, 'urgent', '2024-05-18', NULL, 'in_progress', 4, 6, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(3, 'Client Presentation', 'Present Modern Villa design to client', 7, 'high', '2024-05-22', NULL, 'pending', 4, 6, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `title_deeds`
--

CREATE TABLE `title_deeds` (
  `id` int(11) NOT NULL,
  `deed_no` varchar(100) NOT NULL,
  `owner_name` varchar(200) NOT NULL,
  `owner_phone` varchar(50) DEFAULT NULL,
  `owner_email` varchar(100) DEFAULT NULL,
  `property_location` varchar(200) NOT NULL,
  `plot_number` varchar(100) DEFAULT NULL,
  `block_number` varchar(100) DEFAULT NULL,
  `land_area` decimal(12,2) DEFAULT 0.00,
  `deed_type` enum('certificate_of_occupancy','granted_right_of_occupancy','residential_license','customary_right') DEFAULT 'certificate_of_occupancy',
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `amount_paid` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `document_file` varchar(500) DEFAULT NULL,
  `status` enum('processing','ready','issued','expired','cancelled') DEFAULT 'processing',
  `department_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_by_department` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_user_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `title_deeds`
--

INSERT INTO `title_deeds` (`id`, `deed_no`, `owner_name`, `owner_phone`, `owner_email`, `property_location`, `plot_number`, `block_number`, `land_area`, `deed_type`, `issue_date`, `expiry_date`, `amount_paid`, `description`, `document_file`, `status`, `department_id`, `created_by`, `created_at`, `updated_at`, `deleted_by_department`, `deleted_by_admin`, `deleted_by_user_id`, `deleted_at`) VALUES
(1, 'TD/2024/001', 'John Mwita', NULL, NULL, 'Kigamboni', 'Plot A123', NULL, 0.50, 'certificate_of_occupancy', '2024-05-01', NULL, 2500000.00, NULL, NULL, 'issued', 12, 21, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(2, 'TD/2024/002', 'Sarah Kijazi', NULL, NULL, 'Mbezi Beach', 'Plot B456', NULL, 1.20, 'granted_right_of_occupancy', '2024-05-10', NULL, 5000000.00, NULL, NULL, 'issued', 12, 21, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL),
(3, 'TD/2024/003', 'James Ndege', NULL, NULL, 'Pwani', 'Plot C789', NULL, 2.00, 'certificate_of_occupancy', NULL, NULL, 3500000.00, NULL, NULL, 'processing', 12, 21, '2026-05-18 08:01:33', '2026-05-18 08:01:33', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `source` varchar(200) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `transaction_date` date NOT NULL,
  `status` enum('paid','pending','partial') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_user_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `type`, `source`, `amount`, `paid_amount`, `transaction_date`, `status`, `description`, `department_id`, `created_at`, `is_deleted`, `deleted_by_admin`, `deleted_by_user_id`, `deleted_at`) VALUES
(1, 'income', 'TBL Payment - Construction', 450000000.00, 450000000.00, '2024-04-01', 'paid', 'Construction project payment - School building', 2, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(2, 'expense', 'Salaries - March', 15000000.00, 15000000.00, '2024-04-05', 'paid', 'Staff salaries for March 2024', 2, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(3, 'income', 'Consultancy Fee - Town Planning', 25000000.00, 25000000.00, '2024-04-10', 'paid', 'Urban planning consultancy services', 2, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(4, 'expense', 'Office Rent', 5000000.00, 5000000.00, '2024-04-12', 'paid', 'Office rent for April 2024', 2, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(5, 'income', 'Utilities', 100000000.00, 100000000.00, '2024-04-15', 'paid', 'Electricity and water bills for April', 2, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(6, 'income', 'Aluminium Sales - Window Frames', 8500000.00, 8500000.00, '2024-05-01', 'paid', 'Aluminium window frames sales for Modern Villa', 7, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(7, 'expense', 'Raw Materials - Aluminium Sheets', 2500000.00, 500000.00, '2024-05-02', 'partial', 'Aluminium sheets purchase for fabrication', 7, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(8, 'income', 'Brick Sales', 22500000.00, 22500000.00, '2024-05-03', 'paid', 'Sale of 500,000 bricks to Residential Estate', 6, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(9, 'expense', 'Raw Materials - Clay and Cement', 18000000.00, 18000000.00, '2024-05-03', 'paid', 'Raw materials for brick production', 6, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(10, 'income', 'Timber Sales', 25000000.00, 25000000.00, '2024-05-04', 'paid', 'Timber sold to furniture manufacturers', 6, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(11, 'income', 'Title Deed Fees', 15000000.00, 15000000.00, '2024-05-01', 'paid', 'Title deed processing fees - 45 deeds', 12, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(12, 'expense', 'Land Survey Costs', 5000000.00, 5000000.00, '2024-05-02', 'paid', 'Survey and valuation costs for land registration', 12, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(13, 'income', 'Survey Fees - Boundary Demarcation', 15000000.00, 15000000.00, '2024-05-01', 'paid', 'Topographic survey fees for Coast Region project', 10, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(14, 'expense', 'Equipment - GPS and Total Station', 5000000.00, 5000000.00, '2024-05-02', 'paid', 'GPS and total station equipment purchase', 10, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(15, 'income', 'Town Planning Consultancy', 12000000.00, 12000000.00, '2024-05-01', 'paid', 'Urban planning consultancy for master plan', 8, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(16, 'expense', 'Software Licenses - GIS', 3000000.00, 3000000.00, '2024-05-02', 'paid', 'GIS and planning software licenses', 8, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(17, 'income', 'Architectural Design Fees', 18000000.00, 18000000.00, '2024-05-03', 'paid', 'Architectural design fees for city center', 9, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(18, 'expense', 'CAD Software Licenses', 2500000.00, 2500000.00, '2024-05-04', 'paid', 'AutoCAD and Revit software licenses', 9, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(19, 'income', 'Construction Payment - Modern Villa', 50000000.00, 50000000.00, '2024-05-05', 'paid', 'Second installment for Modern Villa', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(20, 'expense', 'Construction Materials', 30000000.00, 30000000.00, '2024-05-05', 'paid', 'Cement, steel, and construction materials', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(21, 'income', 'Client Payment - Commercial Complex', 75000000.00, 75000000.00, '2024-05-10', 'paid', 'First installment for Commercial Complex', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(22, 'expense', 'Labor Costs - May', 12000000.00, 12000000.00, '2024-05-15', 'paid', 'Construction workers salaries for May', 11, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(23, 'income', 'Aluminium Doors Payment', 28000000.00, 0.00, '2024-05-12', 'pending', 'Payment for aluminium doors - Office Complex', 7, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(24, 'expense', 'Glass Panels Purchase', 1800000.00, 1800000.00, '2024-05-08', 'paid', 'Glass panels for windows and doors', 7, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(25, 'income', 'Land Registration Fees', 12000000.00, 12000000.00, '2024-05-15', 'paid', 'Bulk land registration fees - 500 plots', 12, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(26, 'expense', 'Printing and Stationery', 800000.00, 800000.00, '2024-05-10', 'paid', 'Title deed printing and office supplies', 12, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(27, 'income', 'Survey - Kinondoni Plots', 25000000.00, 25000000.00, '2024-05-14', 'paid', 'Survey fees for 1,000 plots', 10, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(28, 'expense', 'Field Equipment', 2000000.00, 2000000.00, '2024-05-12', 'paid', 'Survey field equipment and accessories', 10, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(29, 'income', 'Marketing Campaign Revenue', 15000000.00, 15000000.00, '2024-05-08', 'paid', 'Revenue from Q2 marketing campaign', 3, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(30, 'expense', 'Advertising Costs', 8000000.00, 8000000.00, '2024-05-06', 'paid', 'Digital and print advertising', 3, '2026-05-10 16:46:59', 0, 0, NULL, NULL),
(39, 'income', 'Consultancy - Town Planning', 45000000.00, 45000000.00, '2026-05-11', 'paid', '', 1, '2026-05-11 12:31:33', 0, 0, NULL, NULL),
(40, 'income', 'Timber Sales', 100000000.00, 100000000.00, '2026-05-12', 'paid', 'italipwa badae', 10, '2026-05-12 08:38:50', 0, 0, NULL, NULL),
(42, 'income', 'Timber Sales', 25000000.00, 25000000.00, '2026-05-18', 'paid', '', 1, '2026-05-18 09:48:04', 0, 0, NULL, NULL),
(44, 'income', 'CAD Software Licenses', 60000000.00, 60000000.00, '2026-05-18', 'paid', '', 9, '2026-05-18 11:06:20', 0, 0, NULL, NULL),
(46, 'expense', 'Labor Costs', 10000000.00, 7000000.00, '2026-05-19', 'partial', 'bado 3M', 4, '2026-05-19 07:47:10', 0, 0, NULL, NULL),
(48, 'expense', 'Staff Salaries - April', 8500000.00, 8500000.00, '2024-05-01', 'paid', 'Monthly salaries for manager office staff - 5 employees', 4, '2026-05-19 09:38:32', 0, 0, NULL, NULL),
(49, 'expense', 'Office Supplies & Stationery', 450000.00, 450000.00, '2024-05-05', 'paid', 'Printer cartridges, A4 papers, pens, notebooks, desk organizers', 4, '2026-05-19 09:38:32', 0, 0, NULL, NULL),
(50, 'income', 'Consultation Fee - ABC Company', 2000000.00, 2000000.00, '2024-05-10', 'paid', 'Strategic planning consultation for ABC Company - 2 hours', 4, '2026-05-19 09:38:32', 0, 0, NULL, NULL),
(51, 'expense', 'Travel & Transport Allowance', 1200000.00, 800000.00, '2024-05-15', 'partial', 'Staff travel reimbursements for site visits - Pending TZS 400,000', 4, '2026-05-19 09:38:32', 0, 0, NULL, NULL),
(52, 'expense', 'Training & Workshop', 750000.00, 750000.00, '2024-05-20', 'paid', 'Managerial training workshop - Leadership skills', 4, '2026-05-19 09:38:32', 0, 0, NULL, NULL),
(53, 'income', 'Department Performance Bonus - Q2', 3500000.00, 2000000.00, '2024-05-25', 'partial', 'Q2 performance bonus based on department achievements - Pending TZS 1,500,000', 4, '2026-05-19 09:38:32', 0, 0, NULL, NULL),
(54, 'expense', 'Meeting & Conference Expenses', 300000.00, 300000.00, '2024-05-28', 'paid', 'Stakeholder meeting catering and venue', 4, '2026-05-19 09:38:32', 0, 0, NULL, NULL),
(55, 'income', 'Project Management Fee - TANROADS', 5000000.00, 5000000.00, '2024-05-30', 'paid', 'Project management fees for bridge construction project', 4, '2026-05-19 09:38:32', 0, 0, NULL, NULL),
(56, 'expense', 'Project Management Software Licenses', 600000.00, 0.00, '2024-06-01', 'pending', 'Annual subscription for project management tools - Not yet paid', 4, '2026-05-19 09:38:32', 0, 0, NULL, NULL),
(57, 'income', 'Management Consultancy - Ministry of Works', 8000000.00, 5000000.00, '2024-06-05', 'partial', 'Construction project management consultancy - Pending TZS 3,000,000', 4, '2026-05-19 09:38:32', 0, 0, NULL, NULL),
(60, 'income', 'Visitor Registration Fees', 50000.00, 50000.00, '2024-05-03', 'paid', 'Visitor ID card fees', 5, '2026-05-19 13:32:43', 0, 0, NULL, NULL),
(67, 'expense', 'Cleaning Supplies', 80000.00, 80000.00, '2024-05-18', 'paid', 'Office cleaning materials', 5, '2026-05-19 13:32:43', 0, 0, NULL, NULL),
(68, 'income', 'Training Materials', 180000.00, 180000.00, '2024-05-20', 'paid', 'Sales of training booklets', 5, '2026-05-19 13:32:43', 0, 0, NULL, NULL),
(69, 'expense', 'Phone Credit', 100000.00, 100000.00, '2024-05-22', 'paid', 'Office phone credit', 5, '2026-05-19 13:32:43', 0, 0, NULL, NULL),
(70, 'income', 'Event Organization', 750000.00, 500000.00, '2024-05-25', 'partial', 'Event planning services - paid 500k, balance 250k', 5, '2026-05-19 13:32:43', 0, 0, NULL, NULL),
(73, 'income', 'Timber Sales', 40000000.00, 40000000.00, '2026-05-19', 'paid', '\n📊 Production: Produced 800 units, Sold 900 units', 6, '2026-05-19 13:46:57', 0, 0, NULL, NULL),
(74, 'income', 'CAD Software Licenses', 60000000.00, 60000000.00, '2026-05-19', 'paid', '', 9, '2026-05-19 15:46:36', 0, 0, NULL, NULL),
(75, 'income', 'Timber Sales', 100000000.00, 50000000.00, '2026-05-19', 'partial', '', 7, '2026-05-19 17:08:50', 0, 0, NULL, NULL),
(77, 'expense', 'Labor Costs', 10000000.00, 10000000.00, '2026-05-19', 'paid', '', 1, '2026-05-19 19:29:17', 0, 0, NULL, NULL),
(78, 'income', 'myula house', 10000000.00, 10000000.00, '2026-05-19', 'paid', '', 1, '2026-05-19 19:29:40', 0, 0, NULL, NULL),
(79, 'income', 'HOSPITAL', 800000000.00, 700000000.00, '2026-05-19', 'partial', '', 1, '2026-05-19 19:30:55', 0, 0, NULL, NULL),
(80, 'income', 'Timber Sales', 50000000.00, 0.00, '2026-05-20', 'pending', '', 3, '2026-05-20 14:50:58', 0, 0, NULL, NULL),
(81, 'income', 'Timber Sales', 40000000.00, 35000000.00, '2026-05-20', 'partial', '', 3, '2026-05-20 15:09:30', 0, 0, NULL, NULL),
(82, 'income', 'HOSPITAL', 700000000.00, 700000000.00, '2026-05-20', 'paid', '', 2, '2026-05-20 16:00:25', 0, 0, NULL, NULL),
(83, 'income', 'Timber Sales', 450000000.00, 300000000.00, '2026-05-20', 'partial', '', 1, '2026-05-20 19:53:58', 0, 0, NULL, NULL),
(84, 'income', 'Timber Sales', 60000000.00, 60000000.00, '2026-05-21', 'paid', '', 3, '2026-05-21 10:14:34', 0, 0, NULL, NULL),
(85, 'income', 'myula house', 100000000.00, 100000000.00, '2026-05-21', 'paid', '', 1, '2026-05-21 17:08:48', 0, 0, NULL, NULL),
(86, 'income', 'Timber Sales', 40000000.00, 40000000.00, '2026-05-22', 'paid', '', 10, '2026-05-22 00:15:25', 0, 0, NULL, NULL),
(87, 'income', 'Land Registration Fees', 55000000.00, 45000000.00, '2026-05-22', 'partial', '', 12, '2026-05-22 06:51:04', 0, 0, NULL, NULL),
(88, 'income', 'Timber Sales', 700000000.00, 700000000.00, '2026-05-22', 'paid', '', 8, '2026-05-22 07:01:21', 0, 0, NULL, NULL),
(89, 'income', 'Timber Sales', 700000000.00, 700000000.00, '2026-05-23', 'paid', '', 9, '2026-05-23 14:15:59', 0, 0, NULL, NULL),
(90, 'income', 'Consultancy Services - Master Plan', 25000000.00, 25000000.00, '2026-05-01', 'paid', 'Master plan consultancy for Dar es Salaam City Council', 1, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(91, 'expense', 'Office Equipment Purchase', 3500000.00, 3500000.00, '2026-05-02', 'paid', 'New computers and printers for admin office', 1, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(92, 'income', 'Training Fees - Staff Development', 5000000.00, 5000000.00, '2026-05-03', 'paid', 'Staff training on new ERP system', 1, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(93, 'expense', 'Electricity Bill', 850000.00, 850000.00, '2026-05-04', 'paid', 'Monthly electricity for HQ', 1, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(94, 'income', 'Rental Income - Office Space', 12000000.00, 12000000.00, '2026-05-05', 'paid', 'Rent from tenants in commercial building', 1, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(95, 'expense', 'Water Bill', 250000.00, 250000.00, '2026-05-06', 'paid', 'Water utilities for all departments', 1, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(96, 'income', 'Software Licensing', 8000000.00, 8000000.00, '2026-05-07', 'paid', 'Annual software licenses sold to partners', 1, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(97, 'expense', 'Internet & Network', 450000.00, 450000.00, '2026-05-08', 'paid', 'Fiber internet connection', 1, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(98, 'income', 'Annual General Meeting Fees', 3000000.00, 3000000.00, '2026-05-09', 'paid', 'Registration fees for AGM', 1, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(99, 'expense', 'Security Services', 1200000.00, 1200000.00, '2026-05-10', 'paid', 'Monthly security contract', 1, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(100, 'income', 'Financial Audit Services', 15000000.00, 15000000.00, '2026-05-01', 'paid', 'External audit for Q2 2024', 2, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(101, 'expense', 'Accounting Software Subscription', 2500000.00, 2500000.00, '2026-05-02', 'paid', 'QuickBooks annual subscription', 2, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(102, 'income', 'Tax Consulting Fees', 8000000.00, 8000000.00, '2026-05-03', 'paid', 'Tax advisory services for clients', 2, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(103, 'expense', 'Bank Charges', 150000.00, 150000.00, '2026-05-04', 'paid', 'Monthly bank transaction fees', 2, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(104, 'income', 'Financial Report Preparation', 5000000.00, 3000000.00, '2026-05-05', 'partial', 'Financial statements for 5 clients - Received TZS 3M', 2, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(105, 'expense', 'Stationery & Printing', 350000.00, 350000.00, '2026-05-06', 'paid', 'Ink cartridges, A4 papers, folders', 2, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(106, 'income', 'Loan Interest Income', 2500000.00, 0.00, '2026-05-07', 'pending', 'Interest from staff loans - Not yet paid', 2, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(107, 'expense', 'Training - Tax Updates', 500000.00, 500000.00, '2026-05-08', 'paid', 'Workshop on new tax regulations', 2, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(108, 'income', 'Digital Marketing Campaign', 25000000.00, 25000000.00, '2026-05-01', 'paid', 'Social media ads for new housing project', 3, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(109, 'expense', 'Google Ads', 8000000.00, 8000000.00, '2026-05-02', 'paid', 'PPC campaigns for May', 3, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(110, 'income', 'Lead Generation Services', 12000000.00, 8000000.00, '2026-05-03', 'partial', 'Lead generation for real estate - Paid TZS 8M', 3, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(111, 'expense', 'Facebook Advertising', 5000000.00, 5000000.00, '2026-05-04', 'paid', 'Facebook/Instagram ad campaigns', 3, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(114, 'income', 'Email Marketing', 3000000.00, 0.00, '2026-05-07', 'pending', 'Monthly newsletter subscriptions', 3, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(115, 'expense', 'TV Advertisement', 12000000.00, 6000000.00, '2026-05-08', 'partial', 'Cloud FM ads - 50% paid', 3, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(116, 'income', 'Management Consultancy', 18000000.00, 18000000.00, '2026-05-01', 'paid', 'Consultancy services for ABC Company', 4, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(117, 'expense', 'Staff Salaries - May', 25000000.00, 25000000.00, '2026-05-05', 'paid', 'Monthly salaries for management team', 4, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(118, 'income', 'Performance Review Fees', 5000000.00, 5000000.00, '2026-05-10', 'paid', 'Staff performance evaluation services', 4, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(119, 'expense', 'Travel Allowances', 1500000.00, 1500000.00, '2026-05-12', 'paid', 'Site visits and client meetings', 4, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(120, 'income', 'Project Management Fees', 25000000.00, 15000000.00, '2026-05-15', 'partial', 'Management fees for Modern Villa - Paid TZS 15M', 4, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(121, 'expense', 'Meeting Expenses', 800000.00, 800000.00, '2026-05-18', 'paid', 'Catering and venue for stakeholders meeting', 4, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(124, 'income', 'Meeting Room Rental', 2000000.00, 2000000.00, '2026-05-05', 'paid', 'Conference room rental for external clients', 5, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(125, 'expense', 'Courier Services', 250000.00, 250000.00, '2026-05-08', 'paid', 'Document delivery within Dar es Salaam', 5, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(126, 'income', 'Visitor Registration Fees', 500000.00, 500000.00, '2026-05-10', 'paid', 'Visitor ID cards and registration', 5, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(128, 'income', 'Brick Sales - Residential Estate', 52500000.00, 52500000.00, '2026-05-01', 'paid', '210,000 bricks sold to Mbezi Estate', 6, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(129, 'expense', 'Clay Purchase', 18000000.00, 18000000.00, '2026-05-02', 'paid', 'Raw clay from Morogoro supplier', 6, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(130, 'income', 'Timber Sales - Hardwood', 18000000.00, 18000000.00, '2026-05-03', 'paid', '180 hardwood planks sold', 6, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(131, 'expense', 'Cement Purchase', 3500000.00, 3500000.00, '2026-05-04', 'paid', '120 bags of cement for brick production', 6, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(132, 'income', 'Hollow Bricks Sales', 11250000.00, 8000000.00, '2026-05-05', 'partial', '45,000 hollow bricks - Paid TZS 8M', 6, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(133, 'expense', 'Machine Maintenance', 1500000.00, 1500000.00, '2026-05-06', 'paid', 'Brick machine repair and servicing', 6, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(134, 'income', 'Softwood Planks', 8400000.00, 8400000.00, '2026-05-07', 'paid', '120 softwood planks sold', 6, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(135, 'expense', 'Fuel for Machinery', 800000.00, 800000.00, '2026-05-08', 'paid', 'Diesel for brick making machines', 6, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(136, 'income', 'Window Frame Sales', 42500000.00, 42500000.00, '2026-05-01', 'paid', '3,200 window frames delivered', 7, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(137, 'expense', 'Aluminium Sheets Purchase', 15000000.00, 15000000.00, '2026-05-02', 'paid', '22,500 kg aluminium sheets', 7, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(138, 'income', 'Door Frame Sales', 21600000.00, 21600000.00, '2026-05-03', 'paid', '1,800 door frames sold', 7, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(139, 'expense', 'Glass Panels Purchase', 5000000.00, 3000000.00, '2026-05-04', 'partial', 'Glass for windows - Paid TZS 3M', 7, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(140, 'income', 'Sliding Doors Sales', 15600000.00, 15600000.00, '2026-05-05', 'paid', '520 sliding doors installed', 7, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(141, 'expense', 'Hardware Purchase', 3500000.00, 3500000.00, '2026-05-06', 'paid', 'Handles, hinges, locks', 7, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(142, 'income', 'Custom Fabrications', 21250000.00, 10000000.00, '2026-05-07', 'partial', '85 custom orders - Paid TZS 10M', 7, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(143, 'expense', 'Powder Coating', 2500000.00, 2500000.00, '2026-05-08', 'paid', 'Color coating for window frames', 7, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(144, 'income', 'Building Permit Fees', 4200000.00, 4200000.00, '2026-05-01', 'paid', '52 building permits processed', 8, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(145, 'expense', 'GIS Software License', 2000000.00, 2000000.00, '2026-05-02', 'paid', 'ArcGIS annual subscription', 8, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(146, 'income', 'Zoning Change Applications', 3150000.00, 3150000.00, '2026-05-03', 'paid', '18 zoning change applications approved', 8, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(147, 'expense', 'Printing Maps', 500000.00, 500000.00, '2026-05-04', 'paid', 'Large format map printing', 8, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(148, 'income', 'Subdivision Applications', 2250000.00, 2250000.00, '2026-05-05', 'paid', '15 subdivision applications - Paid TZS 1.5M', 8, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(149, 'expense', 'Survey Equipment', 3000000.00, 3000000.00, '2026-05-06', 'paid', 'GPS and total station', 8, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(150, 'income', 'Architectural Design Fees', 45000000.00, 45000000.00, '2026-05-01', 'paid', 'Design for Modern Villa Kigamboni', 9, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(151, 'expense', 'CAD Software Licenses', 3500000.00, 3500000.00, '2026-05-02', 'paid', 'AutoCAD and Revit licenses', 9, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(152, 'income', '3D Rendering Services', 12000000.00, 12000000.00, '2026-05-03', 'paid', '120 3D renderings for clients', 9, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(153, 'expense', 'Plotter Paper & Ink', 800000.00, 800000.00, '2026-05-04', 'paid', 'Large format printing supplies', 9, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(154, 'income', 'Building Plans Approval', 8000000.00, 5000000.00, '2026-05-05', 'partial', 'Building plans for 8 projects - Paid TZS 5M', 9, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(155, 'expense', 'Workstation Upgrades', 2000000.00, 2000000.00, '2026-05-06', 'paid', 'New computers for design team', 9, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(156, 'income', 'Boundary Survey Fees', 5400000.00, 5400000.00, '2026-05-01', 'paid', '18 boundary surveys completed', 10, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(157, 'expense', 'Total Station Purchase', 8000000.00, 8000000.00, '2026-05-02', 'paid', 'New surveying instrument', 10, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(158, 'income', 'Topographic Survey', 3600000.00, 3600000.00, '2026-05-03', 'paid', '12 topographic surveys', 10, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(159, 'expense', 'GPS Equipment', 5000000.00, 3000000.00, '2026-05-04', 'partial', 'RTK GPS system - Paid TZS 3M', 10, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(160, 'income', 'Subdivision Surveys', 2400000.00, 2400000.00, '2026-05-05', 'paid', '8 subdivision surveys completed', 10, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(161, 'expense', 'Drone Purchase', 6000000.00, 6000000.00, '2026-05-06', 'paid', 'DJI drone for aerial mapping', 10, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(162, 'income', 'Construction Progress Payment', 75000000.00, 75000000.00, '2026-05-01', 'paid', 'Second installment - Commercial Complex', 11, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(163, 'expense', 'Cement Purchase', 75000000.00, 75000000.00, '2026-05-02', 'paid', '5,000 bags of cement', 11, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(164, 'income', 'Site Work Payment', 50000000.00, 50000000.00, '2026-05-03', 'paid', 'Foundation and excavation - Modern Villa', 11, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(165, 'expense', 'Steel Reinforcement', 225000000.00, 150000000.00, '2026-05-04', 'partial', '150 tons of steel - Paid TZS 150M', 11, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(166, 'income', 'Roofing Installation', 55000000.00, 30000000.00, '2026-05-05', 'partial', 'Roofing work - Paid TZS 30M', 11, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(167, 'expense', 'Sand Purchase', 40000000.00, 40000000.00, '2026-05-06', 'paid', '800 tons of building sand', 11, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(168, 'income', 'Electrical Installation', 35000000.00, 35000000.00, '2026-05-07', 'paid', 'Electrical work for Modern Villa', 11, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(169, 'expense', 'Gravel Purchase', 30000000.00, 30000000.00, '2026-05-08', 'paid', '600 tons of gravel', 11, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(170, 'income', 'Title Deed Issuance', 22500000.00, 22500000.00, '2026-05-01', 'paid', '45 new title deeds issued', 12, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(171, 'expense', 'Document Printing', 500000.00, 500000.00, '2026-05-02', 'paid', 'Title deed printing materials', 12, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(172, 'income', 'Title Transfer Fees', 12500000.00, 12500000.00, '2026-05-03', 'paid', '25 title transfers processed', 12, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(173, 'expense', 'Legal Fees', 3000000.00, 3000000.00, '2026-05-04', 'paid', 'Legal consultation for land disputes', 12, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(174, 'income', 'Land Survey Integration', 9000000.00, 6000000.00, '2026-05-05', 'partial', '18 land surveys integrated - Paid TZS 6M', 12, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(175, 'expense', 'Software Development', 5000000.00, 5000000.00, '2026-05-06', 'paid', 'Digital title deed system', 12, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(176, 'income', 'Boundary Adjustment', 3600000.00, 3600000.00, '2026-05-07', 'paid', '12 boundary adjustments completed', 12, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(177, 'expense', 'Server Maintenance', 1500000.00, 1500000.00, '2026-05-08', 'paid', 'Digital platform server costs', 12, '2026-05-25 00:49:58', 0, 0, NULL, NULL),
(179, 'income', 'Timber Sales', 700000000.00, 700000000.00, '2026-05-25', 'paid', '\n📊 Production: Produced 900 units, Sold 300 units', 6, '2026-05-25 09:56:14', 0, 0, NULL, NULL),
(180, 'income', 'baundaries', 120000000.00, 90000000.00, '2026-05-25', 'partial', 'jonny mwita kjkhhgyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyysdzsssaaAsdfg', 10, '2026-05-25 10:10:05', 0, 0, NULL, NULL),
(181, 'expense', 'office', 3444444.00, 3444444.00, '2026-06-03', 'paid', 'kuchimba', 2, '2026-06-03 08:26:35', 0, 0, NULL, NULL),
(182, 'expense', 'Office', 8000000.00, 0.00, '2026-06-03', 'pending', '', 5, '2026-06-03 14:20:30', 0, 0, NULL, NULL),
(183, 'expense', 'Office', 8000000.00, 0.00, '2026-06-05', 'pending', '', 11, '2026-06-05 11:10:20', 0, 0, NULL, NULL),
(184, 'expense', 'Office', 8000000.00, 0.00, '2026-06-05', 'pending', '', 11, '2026-06-05 11:10:31', 0, 0, NULL, NULL),
(188, 'expense', 'Office', 2000000.00, 0.00, '2026-06-07', 'pending', '', 4, '2026-06-07 14:05:38', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `department_id`, `role`, `salary`, `join_date`, `is_active`, `created_at`, `profile_picture`, `bio`, `is_deleted`, `reset_token`, `reset_expires`) VALUES
(1, 'JACKSON MYULA', 'jacksonmyula773@gmail.com', '$2y$10$W0PrhtfjGJ.bOxcfJ/MUMey6XulPfI3zIdaerp8dKfDrhaF4LEtcS', '0623693303', 1, 'Super Administrator', 5000000.00, '2024-01-01', 1, '2026-05-10 16:46:59', NULL, NULL, 0, 'rXO-OkM49bEgDWM-UXI7ajCBOwpCcpmIojN80wOEoCY', '2026-05-25 10:34:56'),
(2, 'John Mwita', 'john.mwita@geotraverse.com', '$2y$10$KOIMaD7hOeEkjI7cZhsMGOOZkNK1FnEWv3ZtTX6PuTn2p/2MVw6YS', '+255 712 345 678', 2, 'Finance Manager', 2500000.00, '2024-01-15', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(3, 'Grace Peter', 'grace.peter@geotraverse.com', '$2y$10$b63e4i6a/9HKx/RnYduNOOXBZ9ii8ITdK7kIRG3aqGZcpCNZoOQSS', '+255 712 345 679', 2, 'Accountant', 1500000.00, '2024-02-01', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(4, 'Sarah Kijazi', 'sarah.kijazi@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 713 456 789', 3, 'Sales Manager', 2200000.00, '2024-01-10', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(5, 'Hassan Juma', 'hassan.juma@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 713 456 790', 3, 'Marketing Officer', 1200000.00, '2024-02-15', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(6, 'James Ndege', 'james.ndege@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 714 567 890', 4, 'General Manager', 3500000.00, '2024-01-05', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(7, 'Asha Salim', 'asha.salim@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 714 567 891', 4, 'Assistant Manager', 1800000.00, '2024-02-10', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(8, 'Mary Kilimanjaro', 'mary.kilimanjaro@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 715 678 901', 5, 'Executive Secretary', 1200000.00, '2024-01-20', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(9, 'Fatma Omar', 'fatma.omar@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 715 678 902', 5, 'Receptionist', 800000.00, '2024-03-01', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(10, 'Peter Tabora', 'peter.tabora@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 716 789 012', 6, 'Production Manager', 2000000.00, '2024-01-12', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(11, 'John Bosco', 'john.bosco@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 716 789 013', 6, 'Supervisor', 1000000.00, '2024-02-20', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(12, 'Ali Hassan', 'ali.hassan@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 717 890 124', 7, 'Production Manager', 1800000.00, '2024-01-15', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(13, 'Hamza Rashid', 'hamza.rashid@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 717 890 125', 7, 'Technician', 900000.00, '2024-03-05', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(14, 'Daniel Singida', 'daniel.singida@geotraverse.com', '$2y$10$AJaxpYyS3aUqcB1JlLrJPe/X5bk67dhfzqKdUBmtIHnyjx8PXqpwu', '+255 718 901 234', 8, '', 2200000.00, '2024-01-22', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(15, 'Elizabeth John', 'elizabeth.john@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 718 901 235', 8, 'Urban Planner', 1400000.00, '2024-02-18', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(16, 'Michael Angelo', 'michael.angelo@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 719 012 346', 9, 'Architect', 1800000.00, '2024-01-10', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(17, 'Monica Mrema', 'monica.mrema@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 719 012 347', 9, 'CAD Designer', 1300000.00, '2024-02-22', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(18, 'William Ruvuma', 'william.ruvuma@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 720 123 456', 10, 'Survey Manager', 2100000.00, '2024-01-28', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(19, 'Prosper John', 'prosper.john@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 720 123 457', 10, 'Surveyor', 1200000.00, '2024-03-10', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL),
(20, 'Hamisi Ramadhan', 'hamisi.ramadhan@geotraverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+255 721 234 568', 11, 'Site Supervisor', 1200000.00, '2024-02-25', 1, '2026-05-10 16:46:59', NULL, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_department_visibility`
--

CREATE TABLE `user_department_visibility` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `visible_department_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `department_to_visit` varchar(100) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `visit_time` time DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `name`, `phone`, `department_to_visit`, `visit_date`, `visit_time`, `purpose`, `department_id`, `created_at`) VALUES
(1, 'John Mwita', '+255 712 345 678', 'Manager Department', '2024-05-15', '10:30:00', 'Meeting about modern villa project construction updates and budget approval', 5, '2026-05-19 13:58:22'),
(2, 'Sarah Kijazi', '+255 713 456 789', 'Finance Department', '2024-05-16', '14:00:00', 'Payment inquiry for commercial complex project and invoice submission', 5, '2026-05-19 13:58:22'),
(3, 'James Ndege', '+255 714 567 890', 'Construction Department', '2024-05-17', '11:15:00', 'Site visit coordination and material delivery schedule review', 5, '2026-05-19 13:58:22'),
(4, 'Peter Tabora', '+255 715 678 901', 'Bricks & Timber Department', '2024-05-18', '09:45:00', 'Brick supply contract discussion for residential estate project', 5, '2026-05-19 13:58:22'),
(5, 'Ali Hassan', '+255 716 789 012', 'Aluminium Department', '2024-05-20', '13:30:00', 'Window fabrication order for Modern Villa - 250 units', 5, '2026-05-19 13:58:22'),
(6, 'TANROADS Official', '+255 717 890 123', 'Manager Department', '2024-05-21', '10:00:00', 'Bridge project meeting and progress review with stakeholders', 5, '2026-05-19 13:58:22'),
(7, 'Ministry of Lands', '+255 718 901 234', 'Hatimiliki Department', '2024-05-22', '11:00:00', 'Title deed system review and digital platform discussion', 5, '2026-05-19 13:58:22'),
(8, 'Architectural Consultant', '+255 719 012 345', 'Architectural Department', '2024-05-23', '14:30:00', 'City center design presentation and final approval', 5, '2026-05-19 13:58:22'),
(9, 'Survey Team', '+255 720 123 456', 'Survey Department', '2024-05-24', '09:00:00', 'Boundary demarcation report submission and review', 5, '2026-05-19 13:58:22'),
(10, 'Town Planner', '+255 721 234 567', 'Town Planning Department', '2024-05-25', '15:00:00', 'Master plan consultation and zoning updates for new area', 5, '2026-05-19 13:58:22'),
(11, 'Grace Peter', '+255 722 345 678', 'Finance Department', '2024-05-26', '10:30:00', 'Budget review and financial reporting for Q2', 5, '2026-05-19 13:58:22'),
(12, 'Michael Angelo', '+255 723 456 789', 'Architectural Department', '2024-05-27', '11:45:00', 'Architectural design review meeting for commercial complex', 5, '2026-05-19 13:58:22'),
(13, 'Daniel Singida', '+255 724 567 890', 'Town Planning Department', '2024-05-28', '13:15:00', 'Urban planning permit application submission', 5, '2026-05-19 13:58:22'),
(14, 'Richard Arusha', '+255 725 678 901', 'Hatimiliki Department', '2024-05-29', '15:30:00', 'Title deed application status inquiry and document verification', 5, '2026-05-19 13:58:22'),
(15, 'Hamisi Ramadhan', '+255 726 789 012', 'Construction Department', '2024-05-30', '08:45:00', 'Construction site safety inspection report and compliance check', 5, '2026-05-19 13:58:22'),
(16, 'Mary Kilimanjaro', '+255 727 890 123', 'Secretary Department', '2024-05-31', '09:30:00', 'Office supplies order and inventory management', 5, '2026-05-19 13:58:22'),
(17, 'William Ruvuma', '+255 728 901 234', 'Survey Department', '2024-06-01', '14:00:00', 'New survey project proposal and cost estimation', 5, '2026-05-19 13:58:22'),
(18, 'Asha Salim', '+255 729 012 345', 'Manager Department', '2024-06-02', '11:00:00', 'Staff performance review and HR matters', 5, '2026-05-19 13:58:22'),
(19, 'John Bosco', '+255 730 123 456', 'Bricks & Timber', '2024-06-03', '10:15:00', 'Timber supply agreement and pricing negotiation', 5, '2026-05-19 13:58:22'),
(21, 'jackson', '07564567', 'Manager', '2026-05-25', '01:58:00', '', 5, '2026-05-25 09:59:21');

-- --------------------------------------------------------

--
-- Structure for view `budget_utilization_view`
--
DROP TABLE IF EXISTS `budget_utilization_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `budget_utilization_view`  AS SELECT `ba`.`id` AS `budget_id`, `ba`.`department_id` AS `department_id`, `d`.`name` AS `department_name`, `ba`.`category` AS `category`, `ba`.`allocated_amount` AS `allocated_amount`, coalesce(sum(`bt`.`amount_used`),0) AS `used_amount`, `ba`.`allocated_amount`- coalesce(sum(`bt`.`amount_used`),0) AS `remaining_amount`, round(coalesce(sum(`bt`.`amount_used`),0) / `ba`.`allocated_amount` * 100,2) AS `utilization_percentage` FROM ((`budget_allocations` `ba` join `departments` `d` on(`ba`.`department_id` = `d`.`id`)) left join `budget_tracking` `bt` on(`ba`.`id` = `bt`.`budget_id`)) GROUP BY `ba`.`id`, `ba`.`department_id`, `d`.`name`, `ba`.`category`, `ba`.`allocated_amount` ;

-- --------------------------------------------------------

--
-- Structure for view `dashboard_summary`
--
DROP TABLE IF EXISTS `dashboard_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `dashboard_summary`  AS SELECT (select count(0) from `users` where `users`.`is_active` = 1) AS `total_employees`, (select count(0) from `projects`) AS `total_projects`, (select count(0) from `projects` where `projects`.`status` = 'pending') AS `pending_projects`, (select count(0) from `projects` where `projects`.`status` = 'in_progress') AS `in_progress_projects`, (select count(0) from `projects` where `projects`.`status` = 'completed') AS `completed_projects`, (select sum(`transactions`.`amount`) from `transactions` where `transactions`.`type` = 'income' and `transactions`.`status` = 'paid') AS `total_income`, (select sum(`transactions`.`amount`) from `transactions` where `transactions`.`type` = 'expense' and `transactions`.`status` = 'paid') AS `total_expenses`, (select sum(`daily_work`.`income`) from `daily_work`) AS `daily_income`, (select sum(`daily_work`.`expenses`) from `daily_work`) AS `daily_expenses`, (select count(0) from `reports` where `reports`.`status` = 'draft') AS `draft_reports`, (select count(0) from `reports` where `reports`.`status` = 'sent') AS `sent_reports`, (select count(0) from `messages` where `messages`.`is_read` = 0) AS `unread_messages` ;

-- --------------------------------------------------------

--
-- Structure for view `department_summary_view`
--
DROP TABLE IF EXISTS `department_summary_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `department_summary_view`  AS SELECT `d`.`id` AS `department_id`, `d`.`name` AS `department_name`, (select count(0) from `projects` `p` where `p`.`department_id` = `d`.`id` and `p`.`deleted_by_department` = 0 and `p`.`deleted_by_admin` = 0) AS `total_projects`, (select count(0) from `projects` `p` where `p`.`department_id` = `d`.`id` and `p`.`status` = 'in_progress' and `p`.`deleted_by_department` = 0 and `p`.`deleted_by_admin` = 0) AS `active_projects`, (select count(0) from `projects` `p` where `p`.`department_id` = `d`.`id` and `p`.`status` = 'completed' and `p`.`deleted_by_department` = 0 and `p`.`deleted_by_admin` = 0) AS `completed_projects`, (select sum(`t`.`amount`) from `transactions` `t` where `t`.`department_id` = `d`.`id` and `t`.`type` = 'income' and `t`.`is_deleted` = 0) AS `total_income`, (select sum(`t`.`amount`) from `transactions` `t` where `t`.`department_id` = `d`.`id` and `t`.`type` = 'expense' and `t`.`is_deleted` = 0) AS `total_expense`, (select count(0) from `messages` `m` where (`m`.`sender_dept` = `d`.`id` or `m`.`receiver_dept` = `d`.`id`) and `m`.`sender_deleted` = 0 and `m`.`receiver_deleted` = 0) AS `total_messages`, (select count(0) from `messages` `m` where `m`.`receiver_dept` = `d`.`id` and `m`.`is_read` = 0 and `m`.`receiver_deleted` = 0) AS `unread_messages`, (select count(0) from `reports` `r` where `r`.`department_id` = `d`.`id` and `r`.`deleted_by_department` = 0 and `r`.`deleted_by_admin` = 0) AS `total_reports` FROM `departments` AS `d` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `budget_tracking`
--
ALTER TABLE `budget_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_id` (`budget_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_conversations_status` (`status`),
  ADD KEY `idx_conversations_created` (`created_at`),
  ADD KEY `idx_deleted_by_user` (`deleted_by_user_id`),
  ADD KEY `idx_conversations_deleted_by_user_id` (`deleted_by_user_id`),
  ADD KEY `idx_conversations_deleted_by_admin` (`deleted_by_admin`),
  ADD KEY `idx_conversations_deleted_by_department` (`deleted_by_department`),
  ADD KEY `idx_sender_dept` (`sender_dept`),
  ADD KEY `idx_receiver_dept` (`receiver_dept`);

--
-- Indexes for table `daily_work`
--
ALTER TABLE `daily_work`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_dailywork_date` (`date`),
  ADD KEY `idx_dailywork_department` (`department_id`),
  ADD KEY `idx_daily_work_is_deleted` (`is_deleted`),
  ADD KEY `idx_daily_work_department_id` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `department_permissions`
--
ALTER TABLE `department_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_permission` (`viewer_department_id`,`target_department_id`),
  ADD KEY `target_department_id` (`target_department_id`);

--
-- Indexes for table `design_projects`
--
ALTER TABLE `design_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_design_deleted` (`deleted_by_department`,`deleted_by_admin`);

--
-- Indexes for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_marketing_deleted` (`deleted_by_department`,`deleted_by_admin`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `idx_messages_created` (`created_at`),
  ADD KEY `idx_messages_is_read` (`is_read`),
  ADD KEY `idx_messages_conversation` (`conversation_id`),
  ADD KEY `idx_sender_deleted` (`sender_deleted`),
  ADD KEY `idx_receiver_deleted` (`receiver_deleted`),
  ADD KEY `idx_messages_sender_receiver` (`sender_id`,`receiver_id`,`sender_deleted`,`receiver_deleted`),
  ADD KEY `idx_conversation_deleted` (`conversation_id`,`sender_deleted`,`receiver_deleted`),
  ADD KEY `idx_messages_sender_deleted` (`sender_deleted`),
  ADD KEY `idx_messages_receiver_deleted` (`receiver_deleted`),
  ADD KEY `idx_messages_conversation_id` (`conversation_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `planning_applications`
--
ALTER TABLE `planning_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_planning_deleted` (`deleted_by_department`,`deleted_by_admin`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_projects_status` (`status`),
  ADD KEY `idx_projects_department` (`department_id`),
  ADD KEY `idx_projects_deleted` (`deleted_by_admin`,`deleted_by_department`),
  ADD KEY `idx_projects_deleted_by_admin` (`deleted_by_admin`),
  ADD KEY `idx_projects_sent_to_department` (`sent_to_department`),
  ADD KEY `idx_projects_department_id` (`department_id`);

--
-- Indexes for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `recycle_bin`
--
ALTER TABLE `recycle_bin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_original_table_id` (`original_table`,`original_id`),
  ADD KEY `idx_deleted_by_department` (`deleted_by_department_id`),
  ADD KEY `idx_deleted_by_user` (`deleted_by_user_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `idx_permanently_deleted` (`permanently_deleted`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_reports_created` (`created_at`),
  ADD KEY `idx_reports_department` (`department_id`),
  ADD KEY `idx_reports_deleted` (`deleted_by_admin`,`deleted_by_department`),
  ADD KEY `idx_reports_deleted_by_admin` (`deleted_by_admin`),
  ADD KEY `idx_reports_deleted_by_department` (`deleted_by_department`),
  ADD KEY `idx_reports_sent_to_department` (`sent_to_department`),
  ADD KEY `idx_reports_department_id` (`department_id`),
  ADD KEY `idx_sent_from_department` (`sent_from_department`),
  ADD KEY `idx_sent_to_department` (`sent_to_department`),
  ADD KEY `idx_is_viewed_by_department` (`is_viewed_by_department`),
  ADD KEY `idx_is_viewed_by_admin` (`is_viewed_by_admin`);

--
-- Indexes for table `report_documents`
--
ALTER TABLE `report_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `survey_requests`
--
ALTER TABLE `survey_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_survey_deleted` (`deleted_by_department`,`deleted_by_admin`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `title_deeds`
--
ALTER TABLE `title_deeds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deed_no_unique` (`deed_no`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_titledeed_deleted` (`deleted_by_department`,`deleted_by_admin`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_transactions_date` (`transaction_date`),
  ADD KEY `idx_transactions_type` (`type`),
  ADD KEY `idx_transactions_status` (`status`),
  ADD KEY `idx_transactions_is_deleted` (`is_deleted`),
  ADD KEY `idx_transactions_department_id` (`department_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_users_department` (`department_id`);

--
-- Indexes for table `user_department_visibility`
--
ALTER TABLE `user_department_visibility`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_visibility` (`user_id`,`visible_department_id`),
  ADD KEY `visible_department_id` (`visible_department_id`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `budget_tracking`
--
ALTER TABLE `budget_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `daily_work`
--
ALTER TABLE `daily_work`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `department_permissions`
--
ALTER TABLE `department_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `design_projects`
--
ALTER TABLE `design_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=611;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `planning_applications`
--
ALTER TABLE `planning_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `recycle_bin`
--
ALTER TABLE `recycle_bin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=314;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `report_documents`
--
ALTER TABLE `report_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `survey_requests`
--
ALTER TABLE `survey_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `title_deeds`
--
ALTER TABLE `title_deeds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `user_department_visibility`
--
ALTER TABLE `user_department_visibility`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  ADD CONSTRAINT `budget_allocations_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `budget_tracking`
--
ALTER TABLE `budget_tracking`
  ADD CONSTRAINT `budget_tracking_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budget_allocations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_tracking_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `daily_work`
--
ALTER TABLE `daily_work`
  ADD CONSTRAINT `daily_work_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
