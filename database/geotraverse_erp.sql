-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2026 at 07:10 PM
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
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dailywork`
--

CREATE TABLE `dailywork` (
  `id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT 'Date and time of the work',
  `work_description` text DEFAULT NULL COMMENT 'Description of work done',
  `work_type` varchar(50) DEFAULT 'general' COMMENT 'general, production, survey, etc',
  `project_id` int(11) DEFAULT NULL COMMENT 'Foreign key to projects table',
  `project_name` varchar(255) DEFAULT NULL COMMENT 'Project name (denormalized for quick access)',
  `campaign_id` int(11) DEFAULT NULL COMMENT 'Foreign key to campaigns table',
  `campaign_name` varchar(255) DEFAULT NULL COMMENT 'Campaign name (denormalized)',
  `department_id` int(11) NOT NULL COMMENT 'Department ID who created this record',
  `budget` decimal(15,2) DEFAULT 0.00 COMMENT 'Budget allocated for this work',
  `amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Amount spent/expenses for this work',
  `income` decimal(15,2) DEFAULT 0.00 COMMENT 'Income received from this work',
  `expenses` decimal(15,2) DEFAULT 0.00 COMMENT 'Expenses incurred',
  `profit` decimal(15,2) DEFAULT 0.00 COMMENT 'Profit = income - expenses',
  `quantity_produced` int(11) DEFAULT 0 COMMENT 'Number of units produced',
  `quantity_sold` int(11) DEFAULT 0 COMMENT 'Number of units sold',
  `price_per_unit` decimal(15,2) DEFAULT 0.00 COMMENT 'Price per unit sold',
  `total_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Total amount = quantity_sold * price_per_unit',
  `payment_status` enum('pending','partial','completed','paid') DEFAULT 'pending' COMMENT 'Payment status',
  `partial_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Partial payment amount',
  `amount_paid` decimal(15,2) DEFAULT 0.00 COMMENT 'Total amount paid so far',
  `status` enum('pending','in_progress','partial','completed','cancelled') DEFAULT 'pending' COMMENT 'Current status of this work',
  `created_by` varchar(100) DEFAULT NULL COMMENT 'User who created this record',
  `updated_by` varchar(100) DEFAULT NULL COMMENT 'User who last updated this record',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Creation timestamp',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update timestamp',
  `is_deleted` tinyint(4) DEFAULT 0 COMMENT 'Soft delete flag (0=active, 1=deleted)',
  `deleted_at` datetime DEFAULT NULL COMMENT 'When this record was deleted',
  `is_original` tinyint(4) DEFAULT 1,
  `is_sent_copy` tinyint(4) DEFAULT 0,
  `original_dailywork_id` int(11) DEFAULT NULL,
  `sent_from_dept` int(11) DEFAULT NULL,
  `sent_to_dept` int(11) DEFAULT NULL,
  `is_viewed_by_department` tinyint(1) DEFAULT 0,
  `is_sent` tinyint(1) DEFAULT 0,
  `sent_count` int(11) DEFAULT 0,
  `last_sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Daily work records for all departments';

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `email`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'System Administrator', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(2, 'Finance', 'Financial Management', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(3, 'Sales & Marketing', 'Sales and Marketing Department', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(4, 'Manager', 'Management Department', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(5, 'Secretary', 'Secretarial Services', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(6, 'Bricks & Timber', 'Bricks and Timber Production', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(7, 'Aluminium', 'Aluminium and Iron Department', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(8, 'Town Planning', 'Town Planning Department', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(9, 'Architectural', 'Architectural Department', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(10, 'Survey', 'Survey Department', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(11, 'Construction', 'Construction Department', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55'),
(12, 'Hatimiliki', 'Hatimiliki Department', NULL, NULL, '2026-07-01 17:09:55', '2026-07-01 17:09:55');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'Staff',
  `is_admin` tinyint(1) DEFAULT 0,
  `login_count` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT 0.00,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `phone`, `department_id`, `role`, `is_admin`, `login_count`, `last_login`, `salary`, `password`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'ISACK BEST', 'isaacbest92@gmail.com', '0763782274', 1, 'Super Administrator', 0, 0, NULL, 0.00, '1234', 1, 0, 'System', NULL, '2026-07-01 17:10:22', '2026-07-01 17:10:22'),
(2, 'JACKSON MYULA', 'jacksonmyula773@gmail.com', '0746526243', 1, 'Administrator', 0, 0, NULL, 0.00, '1234', 1, 0, 'System', NULL, '2026-07-01 17:10:22', '2026-07-01 17:10:22');

-- --------------------------------------------------------

--
-- Table structure for table `fund_requests`
--

CREATE TABLE `fund_requests` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `request_date` date NOT NULL,
  `type` enum('income','expense') NOT NULL DEFAULT 'expense',
  `source` varchar(200) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','cancelled','paid','partial') DEFAULT 'pending',
  `department_id` int(11) DEFAULT NULL,
  `requested_by` varchar(100) DEFAULT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `is_viewed_by_finance` tinyint(4) NOT NULL DEFAULT 0,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewed_by` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sent_from_department` int(11) DEFAULT NULL,
  `sent_to_department` int(11) DEFAULT NULL,
  `is_viewed_by_department` tinyint(1) DEFAULT 0,
  `is_sent` tinyint(1) DEFAULT 0,
  `sent_count` int(11) DEFAULT 0,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `deleted_by_department` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by` varchar(100) DEFAULT NULL,
  `deleted_by_department_id` int(11) DEFAULT NULL,
  `is_visible_to_finance` tinyint(1) NOT NULL DEFAULT 1,
  `is_visible_to_own_department` tinyint(1) NOT NULL DEFAULT 1,
  `is_visible_to_super_admin` tinyint(1) NOT NULL DEFAULT 1,
  `is_viewed` tinyint(1) DEFAULT 0,
  `is_viewed_by_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marketing_campaigns`
--

CREATE TABLE `marketing_campaigns` (
  `id` int(11) NOT NULL,
  `campaign_name` varchar(255) NOT NULL,
  `campaign_type` enum('digital','social_media','tv_radio','print','event') DEFAULT 'digital',
  `budget` decimal(15,2) DEFAULT 0.00,
  `spent` decimal(15,2) DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `target_audience` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('planned','active','completed','cancelled') DEFAULT 'planned',
  `department_id` int(11) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_by_department` tinyint(4) DEFAULT 0,
  `deleted_by_admin` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL COMMENT 'Department receiving notification',
  `item_type` enum('project','report','uploaded_report','document','fund_request','visitor','dailywork') NOT NULL COMMENT 'Type of item',
  `item_id` int(11) NOT NULL COMMENT 'ID of the item (project_id, report_id, etc.)',
  `from_department_id` int(11) DEFAULT NULL COMMENT 'Department that sent the item',
  `from_department_name` varchar(100) DEFAULT NULL,
  `item_title` varchar(255) DEFAULT NULL COMMENT 'Title/name of the item',
  `message` text DEFAULT NULL COMMENT 'Optional custom message',
  `action_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `is_viewed` tinyint(1) DEFAULT 0 COMMENT '0 = unviewed, 1 = viewed',
  `viewed_at` timestamp NULL DEFAULT NULL COMMENT 'When notification was viewed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When notification was created',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT 0.00,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed','approved') DEFAULT 'pending',
  `progress` int(11) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `image` longtext DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `project_type` varchar(50) DEFAULT 'general',
  `department_id` int(11) DEFAULT NULL,
  `sent_from_dept` int(11) DEFAULT NULL,
  `sent_to_dept` int(11) DEFAULT NULL,
  `sent_count` int(11) DEFAULT 0,
  `last_sent_at` datetime DEFAULT NULL,
  `is_sent` tinyint(1) DEFAULT 0,
  `is_viewed_by_department` tinyint(4) DEFAULT 0,
  `is_viewed_by_admin` tinyint(4) DEFAULT 0,
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_by_department` tinyint(4) DEFAULT 0,
  `deleted_by_admin` tinyint(4) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_by_department_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `is_original` tinyint(4) DEFAULT 1 COMMENT '1 = original, 0 = copy',
  `is_sent_copy` tinyint(4) DEFAULT 0 COMMENT '1 = this is a copy sent from another department',
  `original_project_id` int(11) DEFAULT NULL COMMENT 'ID of the original project if this is a copy'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_documents`
--

CREATE TABLE `project_documents` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL COMMENT 'Associated project ID (NULL for standalone)',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL COMMENT 'Original file name',
  `file_path` varchar(500) NOT NULL COMMENT 'Relative path: assets/uploads/projects/documents/filename.pdf',
  `file_size` int(11) DEFAULT 0 COMMENT 'File size in bytes',
  `file_type` varchar(100) DEFAULT NULL COMMENT 'MIME type',
  `uploaded_by` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `doc_type` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(100) DEFAULT NULL,
  `sent_from_department` int(11) DEFAULT NULL,
  `sent_to_department` int(11) DEFAULT NULL,
  `is_viewed_by_department` tinyint(4) DEFAULT 0,
  `sent_count` int(11) DEFAULT 0,
  `is_sent` tinyint(4) DEFAULT 0,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `is_original` tinyint(4) DEFAULT 1,
  `is_sent_copy` tinyint(4) DEFAULT 0,
  `original_document_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recycle_bin`
--

CREATE TABLE `recycle_bin` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_type` enum('project','project_document','budget_request','report','uploaded_report','daily_work','employee') NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `original_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`original_data`)),
  `deleted_by_department_id` int(11) DEFAULT NULL,
  `deleted_by_admin` tinyint(4) DEFAULT 0,
  `deleted_by_name` varchar(100) DEFAULT NULL,
  `restored` tinyint(4) DEFAULT 0,
  `restored_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `period` enum('daily','weekly','monthly','quarterly','annual') DEFAULT 'monthly',
  `content` longtext DEFAULT NULL,
  `status` enum('draft','sent') DEFAULT 'draft',
  `department_id` int(11) DEFAULT NULL,
  `sent_from_department` int(11) DEFAULT NULL,
  `sent_to_department` int(11) DEFAULT NULL,
  `is_viewed_by_department` tinyint(4) DEFAULT 0,
  `is_viewed_by_admin` tinyint(4) DEFAULT 0,
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_by_department` tinyint(4) DEFAULT 0,
  `deleted_by_admin` tinyint(4) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by_department_id` int(11) DEFAULT NULL,
  `is_original` tinyint(4) DEFAULT 1,
  `is_sent_copy` tinyint(4) DEFAULT 0,
  `original_report_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sent_dailywork`
--

CREATE TABLE `sent_dailywork` (
  `id` int(11) NOT NULL,
  `original_dailywork_id` int(11) NOT NULL COMMENT 'ID of the original daily work record',
  `copy_dailywork_id` int(11) DEFAULT NULL,
  `dailywork_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Full daily work data as JSON at time of sending' CHECK (json_valid(`dailywork_data`)),
  `from_department_id` int(11) NOT NULL COMMENT 'Department that sent the daily work',
  `to_department_id` int(11) NOT NULL COMMENT 'Department that received the daily work',
  `sent_by` varchar(100) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_viewed` tinyint(4) DEFAULT 0 COMMENT '0 = not viewed, 1 = viewed by recipient',
  `viewed_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sent_count` int(11) DEFAULT 0,
  `is_sent` tinyint(4) DEFAULT 0,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `from_department_name` varchar(100) DEFAULT NULL,
  `to_department_name` varchar(100) DEFAULT NULL,
  `dailywork_project_name` varchar(255) DEFAULT NULL,
  `dailywork_date` datetime DEFAULT NULL,
  `dailywork_amount` decimal(15,2) DEFAULT 0.00,
  `dailywork_budget` decimal(15,2) DEFAULT 0.00,
  `dailywork_status` varchar(50) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sent_documents`
--

CREATE TABLE `sent_documents` (
  `id` int(11) NOT NULL,
  `original_document_id` int(11) NOT NULL COMMENT 'ID of the original document',
  `document_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Full document data as JSON at time of sending' CHECK (json_valid(`document_data`)),
  `from_department_id` int(11) NOT NULL COMMENT 'Department that sent the document',
  `to_department_id` int(11) NOT NULL COMMENT 'Department that received the document',
  `sent_by` varchar(100) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_viewed` tinyint(4) DEFAULT 0 COMMENT '0 = not viewed, 1 = viewed by recipient',
  `is_viewed_by_department` tinyint(4) DEFAULT 0,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sent_count` int(11) DEFAULT 0,
  `is_sent` tinyint(4) DEFAULT 0,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `from_department_name` varchar(100) DEFAULT NULL,
  `to_department_name` varchar(100) DEFAULT NULL,
  `document_title` varchar(255) DEFAULT NULL,
  `document_type` varchar(50) DEFAULT NULL,
  `document_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sent_projects`
--

CREATE TABLE `sent_projects` (
  `id` int(11) NOT NULL,
  `original_project_id` int(11) NOT NULL COMMENT 'ID of the original project',
  `copy_project_id` int(11) DEFAULT NULL,
  `project_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Full project data as JSON at time of sending' CHECK (json_valid(`project_data`)),
  `from_department_id` int(11) NOT NULL COMMENT 'Department that sent the project',
  `to_department_id` int(11) NOT NULL COMMENT 'Department that received the project',
  `sent_by` varchar(100) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_viewed` tinyint(4) DEFAULT 0 COMMENT '0 = not viewed, 1 = viewed by recipient',
  `viewed_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sent_count` int(11) DEFAULT 0,
  `is_sent` tinyint(4) DEFAULT 0,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `from_department_name` varchar(100) DEFAULT NULL,
  `to_department_name` varchar(100) DEFAULT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `project_type` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT 0.00,
  `daily_work_count` int(11) DEFAULT 0,
  `daily_work_summary` longtext DEFAULT NULL,
  `is_sent_copy` tinyint(4) DEFAULT 0,
  `is_original` tinyint(4) DEFAULT 0,
  `is_received` tinyint(4) DEFAULT 0,
  `original_sender_id` int(11) DEFAULT NULL,
  `original_sender_name` varchar(100) DEFAULT NULL,
  `forward_count` int(11) DEFAULT 0,
  `last_forwarded_from` int(11) DEFAULT NULL,
  `last_forwarded_from_name` varchar(100) DEFAULT NULL,
  `is_forward` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sent_reports`
--

CREATE TABLE `sent_reports` (
  `id` int(11) NOT NULL,
  `original_report_id` int(11) NOT NULL COMMENT 'ID of the original report',
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Full report data as JSON at time of sending' CHECK (json_valid(`report_data`)),
  `from_department_id` int(11) NOT NULL COMMENT 'Department that sent the report',
  `to_department_id` int(11) NOT NULL COMMENT 'Department that received the report',
  `sent_by` varchar(100) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_viewed` tinyint(4) DEFAULT 0 COMMENT '0 = not viewed, 1 = viewed by recipient',
  `viewed_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sent_count` int(11) DEFAULT 0,
  `is_sent` tinyint(4) DEFAULT 0,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `from_department_name` varchar(100) DEFAULT NULL,
  `to_department_name` varchar(100) DEFAULT NULL,
  `report_title` varchar(255) DEFAULT NULL,
  `report_period` varchar(50) DEFAULT NULL,
  `report_status` varchar(50) DEFAULT NULL,
  `is_forward` tinyint(4) DEFAULT 0,
  `forward_count` int(11) DEFAULT 0,
  `original_sender_department` int(11) DEFAULT NULL,
  `is_original` tinyint(4) NOT NULL DEFAULT 0,
  `is_sent_copy` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sent_uploaded_reports`
--

CREATE TABLE `sent_uploaded_reports` (
  `id` int(11) NOT NULL,
  `original_uploaded_report_id` int(11) NOT NULL COMMENT 'ID of the original uploaded report',
  `copy_uploaded_report_id` int(11) DEFAULT NULL,
  `uploaded_report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Full uploaded report data as JSON at time of sending' CHECK (json_valid(`uploaded_report_data`)),
  `from_department_id` int(11) NOT NULL COMMENT 'Department that sent the uploaded report',
  `to_department_id` int(11) NOT NULL COMMENT 'Department that received the uploaded report',
  `sent_by` varchar(100) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_viewed` tinyint(4) DEFAULT 0 COMMENT '0 = not viewed, 1 = viewed by recipient',
  `viewed_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sent_count` int(11) DEFAULT 0,
  `is_sent` tinyint(4) DEFAULT 0,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `from_department_name` varchar(100) DEFAULT NULL,
  `to_department_name` varchar(100) DEFAULT NULL,
  `uploaded_report_title` varchar(255) DEFAULT NULL,
  `uploaded_report_period` varchar(50) DEFAULT NULL,
  `uploaded_report_file` varchar(255) DEFAULT NULL,
  `is_original` tinyint(4) NOT NULL DEFAULT 0,
  `is_sent_copy` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `source` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `transaction_date` date NOT NULL,
  `status` enum('paid','pending','partial') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pending_amount` decimal(15,2) DEFAULT 0.00,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_reports`
--

CREATE TABLE `uploaded_reports` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL COMMENT 'Original file name',
  `original_file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) NOT NULL COMMENT 'Relative path: assets/uploads/reports/filename.pdf',
  `file_name_only` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0 COMMENT 'File size in bytes',
  `file_type` varchar(100) DEFAULT NULL COMMENT 'MIME type',
  `period` enum('daily','weekly','monthly','quarterly','annual') DEFAULT 'monthly',
  `uploaded_by` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(4) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(100) DEFAULT NULL,
  `sent_from_department` int(11) DEFAULT NULL,
  `sent_to_department` int(11) DEFAULT NULL,
  `is_viewed_by_department` tinyint(4) DEFAULT 0,
  `sent_count` int(11) DEFAULT 0,
  `is_sent` tinyint(4) DEFAULT 0,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `is_original` tinyint(4) DEFAULT 1,
  `is_sent_copy` tinyint(4) DEFAULT 0,
  `original_uploaded_report_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'User',
  `is_admin` tinyint(1) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0,
  `login_count` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_to_visit` varchar(100) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `visit_time` time DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `dailywork`
--
ALTER TABLE `dailywork`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_id` (`project_id`),
  ADD KEY `idx_campaign_id` (`campaign_id`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_work_type` (`work_type`),
  ADD KEY `idx_is_deleted` (`is_deleted`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `fund_requests`
--
ALTER TABLE `fund_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_department` (`department_id`),
  ADD KEY `idx_deleted` (`is_deleted`),
  ADD KEY `idx_date` (`request_date`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_department` (`department_id`),
  ADD KEY `idx_item` (`item_type`,`item_id`),
  ADD KEY `idx_is_viewed` (`is_viewed`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_sent_to_dept` (`sent_to_dept`),
  ADD KEY `idx_is_viewed` (`is_viewed_by_department`);

--
-- Indexes for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `idx_department` (`department_id`),
  ADD KEY `idx_is_deleted` (`is_deleted`),
  ADD KEY `idx_sent_from` (`sent_from_department`),
  ADD KEY `idx_sent_to` (`sent_to_department`);

--
-- Indexes for table `recycle_bin`
--
ALTER TABLE `recycle_bin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deleted_by_department_id` (`deleted_by_department_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_sent_to_department` (`sent_to_department`),
  ADD KEY `idx_is_viewed_department` (`is_viewed_by_department`),
  ADD KEY `idx_dept_original` (`department_id`,`is_original`,`is_deleted`),
  ADD KEY `idx_sent_from` (`sent_from_department`,`is_deleted`),
  ADD KEY `idx_sent_to` (`sent_to_department`,`is_deleted`);

--
-- Indexes for table `sent_dailywork`
--
ALTER TABLE `sent_dailywork`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_original_dailywork` (`original_dailywork_id`),
  ADD KEY `idx_from_department` (`from_department_id`),
  ADD KEY `idx_to_department` (`to_department_id`),
  ADD KEY `idx_is_viewed` (`is_viewed`),
  ADD KEY `idx_is_deleted` (`is_deleted`),
  ADD KEY `idx_copy_dailywork_id` (`copy_dailywork_id`);

--
-- Indexes for table `sent_documents`
--
ALTER TABLE `sent_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_original_document` (`original_document_id`),
  ADD KEY `idx_from_department` (`from_department_id`),
  ADD KEY `idx_to_department` (`to_department_id`),
  ADD KEY `idx_is_viewed` (`is_viewed`),
  ADD KEY `idx_is_deleted` (`is_deleted`);

--
-- Indexes for table `sent_projects`
--
ALTER TABLE `sent_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_original_project` (`original_project_id`),
  ADD KEY `idx_from_department` (`from_department_id`),
  ADD KEY `idx_to_department` (`to_department_id`),
  ADD KEY `idx_is_viewed` (`is_viewed`),
  ADD KEY `idx_is_deleted` (`is_deleted`),
  ADD KEY `idx_copy_project_id` (`copy_project_id`),
  ADD KEY `idx_original_sender` (`original_sender_id`),
  ADD KEY `idx_forward_count` (`forward_count`);

--
-- Indexes for table `sent_reports`
--
ALTER TABLE `sent_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_original_report` (`original_report_id`),
  ADD KEY `idx_from_department` (`from_department_id`),
  ADD KEY `idx_to_department` (`to_department_id`),
  ADD KEY `idx_is_viewed` (`is_viewed`),
  ADD KEY `idx_is_deleted` (`is_deleted`),
  ADD KEY `idx_from_dept` (`from_department_id`,`is_deleted`),
  ADD KEY `idx_to_dept` (`to_department_id`,`is_deleted`),
  ADD KEY `idx_original` (`original_report_id`,`is_deleted`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `sent_uploaded_reports`
--
ALTER TABLE `sent_uploaded_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_original_uploaded_report` (`original_uploaded_report_id`),
  ADD KEY `idx_from_department` (`from_department_id`),
  ADD KEY `idx_to_department` (`to_department_id`),
  ADD KEY `idx_is_viewed` (`is_viewed`),
  ADD KEY `idx_is_deleted` (`is_deleted`),
  ADD KEY `idx_from_dept` (`from_department_id`,`is_deleted`),
  ADD KEY `idx_to_dept` (`to_department_id`,`is_deleted`),
  ADD KEY `idx_original` (`original_uploaded_report_id`,`is_deleted`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_transaction_source` (`source`,`amount`,`department_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `uploaded_reports`
--
ALTER TABLE `uploaded_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_department` (`department_id`),
  ADD KEY `idx_period` (`period`),
  ADD KEY `idx_is_deleted` (`is_deleted`),
  ADD KEY `idx_sent_from` (`sent_from_department`),
  ADD KEY `idx_sent_to` (`sent_to_department`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dailywork`
--
ALTER TABLE `dailywork`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fund_requests`
--
ALTER TABLE `fund_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recycle_bin`
--
ALTER TABLE `recycle_bin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sent_dailywork`
--
ALTER TABLE `sent_dailywork`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sent_documents`
--
ALTER TABLE `sent_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sent_projects`
--
ALTER TABLE `sent_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sent_reports`
--
ALTER TABLE `sent_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sent_uploaded_reports`
--
ALTER TABLE `sent_uploaded_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uploaded_reports`
--
ALTER TABLE `uploaded_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  ADD CONSTRAINT `marketing_campaigns_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `recycle_bin`
--
ALTER TABLE `recycle_bin`
  ADD CONSTRAINT `recycle_bin_ibfk_1` FOREIGN KEY (`deleted_by_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `visitors`
--
ALTER TABLE `visitors`
  ADD CONSTRAINT `visitors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
