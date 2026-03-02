-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 20, 2026 at 11:31 AM
-- Server version: 10.6.15-MariaDB-1:10.6.15+maria~ubu2004
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lvnschool_software`
--

-- --------------------------------------------------------

--
-- Table structure for table `areamaster`
--

CREATE TABLE `areamaster` (
  `id` int(20) NOT NULL,
  `area_name` varchar(200) NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `busattandence`
--

CREATE TABLE `busattandence` (
  `id` int(11) NOT NULL,
  `DC_name` varchar(200) NOT NULL,
  `Bus_no` varchar(200) NOT NULL,
  `json_str` longtext NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 none, 1 delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `busdata`
--

CREATE TABLE `busdata` (
  `id` int(20) NOT NULL,
  `vehicle_name` varchar(100) NOT NULL,
  `gps` varchar(10) NOT NULL,
  `vehicle_no` varchar(20) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `vehicletype` varchar(10) NOT NULL,
  `status` varchar(10) NOT NULL,
  `speed` varchar(10) NOT NULL,
  `ign` varchar(10) NOT NULL,
  `battery_percentage` varchar(10) NOT NULL,
  `power` varchar(10) NOT NULL,
  `location` text NOT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `bus_url` varchar(225) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : delete',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `busfees`
--

CREATE TABLE `busfees` (
  `id` int(20) NOT NULL,
  `select_batch` varchar(20) DEFAULT NULL,
  `busfeestypename` varchar(50) DEFAULT NULL,
  `date` varchar(15) DEFAULT NULL,
  `amount` int(15) DEFAULT NULL,
  `select_option` varchar(50) DEFAULT NULL,
  `is_delete` int(1) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `busstaff`
--

CREATE TABLE `busstaff` (
  `id` int(50) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `ename` varchar(100) DEFAULT NULL,
  `mobile_number` varchar(100) DEFAULT NULL,
  `aadhar_number` varchar(50) DEFAULT NULL,
  `sssmid` varchar(100) DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `parmanent_address` text DEFAULT NULL,
  `license_no` varchar(100) DEFAULT NULL,
  `license_expire` varchar(20) DEFAULT NULL,
  `license_lssue` varchar(100) DEFAULT NULL,
  `voter_id_no` varchar(50) DEFAULT NULL,
  `joining_date` varchar(20) DEFAULT NULL,
  `leaving_date` varchar(20) DEFAULT NULL,
  `leaving_date1` varchar(10) DEFAULT NULL,
  `call_no` varchar(100) DEFAULT NULL,
  `offical_mobile_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `healthstatus` varchar(10) DEFAULT NULL,
  `is_delete` int(1) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `busstop`
--

CREATE TABLE `busstop` (
  `id` int(50) NOT NULL,
  `area_name` varchar(200) NOT NULL,
  `bus_stop_name` varchar(200) NOT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `langitude` varchar(50) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `caste_name`
--

CREATE TABLE `caste_name` (
  `id` int(11) NOT NULL,
  `caste_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `state_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `section_name` text NOT NULL,
  `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_name`
--

CREATE TABLE `class_name` (
  `id` int(10) UNSIGNED NOT NULL,
  `class_name` text DEFAULT NULL,
  `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_fees_head_master`
--

CREATE TABLE `course_fees_head_master` (
  `id` int(11) NOT NULL,
  `ac_head_name` varchar(255) DEFAULT NULL,
  `remarks` varchar(555) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `is_delete` int(1) DEFAULT 0 COMMENT '0 none, 1 deleted',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_fees_structure_master`
--

CREATE TABLE `course_fees_structure_master` (
  `id` int(11) NOT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `session_name` varchar(255) DEFAULT NULL,
  `fees_type_name` varchar(255) DEFAULT NULL,
  `cast_category` varchar(255) DEFAULT NULL,
  `batch` varchar(255) DEFAULT NULL,
  `json_str` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`json_str`)),
  `total_above_fees` varchar(255) DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 none, 1 deleted',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dailattandence`
--

CREATE TABLE `dailattandence` (
  `id` int(11) NOT NULL,
  `Teacher_Name` varchar(200) NOT NULL,
  `json_str` longtext DEFAULT NULL,
  `period_meeting` varchar(200) DEFAULT NULL,
  `Section` varchar(200) DEFAULT NULL,
  `Attandence_Name` varchar(200) NOT NULL,
  `Class` varchar(200) NOT NULL,
  `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '0 none, 1 delete',
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defaulters_lists`
--

CREATE TABLE `defaulters_lists` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `year` text DEFAULT NULL,
  `date_type` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` text DEFAULT NULL,
  `ac_head_name` varchar(255) DEFAULT NULL,
  `next_yesr_fees` text DEFAULT NULL,
  `rte` text DEFAULT NULL,
  `staff_ward` text DEFAULT NULL,
  `session_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scholarship` text DEFAULT NULL,
  `scholar_no` text DEFAULT NULL,
  `enrollment_no` text DEFAULT NULL,
  `student_name` text DEFAULT NULL,
  `class_name` text DEFAULT NULL,
  `section_name` text DEFAULT NULL,
  `account_name` text DEFAULT NULL,
  `balance_amount` text DEFAULT NULL,
  `min_date` text DEFAULT NULL,
  `max_date` text DEFAULT NULL,
  `student` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exammasters`
--

CREATE TABLE `exammasters` (
  `id` int(11) NOT NULL,
  `exam_name` text DEFAULT NULL,
  `max_marks_theory` text DEFAULT NULL,
  `max_marks_practical` int(11) DEFAULT NULL,
  `fail_if` text DEFAULT NULL,
  `exam_type` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `class_name` text DEFAULT NULL,
  `is_ser` text DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feesreceiptchallan`
--

CREATE TABLE `feesreceiptchallan` (
  `id` int(20) NOT NULL,
  `student_id` int(20) DEFAULT NULL,
  `student_dob` varchar(100) DEFAULT NULL,
  `recpt_chain` varchar(200) DEFAULT NULL,
  `due_upto` varchar(200) DEFAULT NULL,
  `name_student` varchar(200) DEFAULT NULL,
  `str_json` longtext DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feestypes`
--

CREATE TABLE `feestypes` (
  `id` int(15) NOT NULL,
  `feestype` varchar(200) NOT NULL,
  `remarks` varchar(225) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fees_types_master`
--

CREATE TABLE `fees_types_master` (
  `id` int(11) NOT NULL,
  `fees_type` varchar(255) NOT NULL,
  `remark` varchar(255) NOT NULL,
  `session` varchar(255) NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Deleted',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `generate_duechartstatus`
--

CREATE TABLE `generate_duechartstatus` (
  `id` int(11) NOT NULL,
  `student_id` int(100) NOT NULL,
  `class_name` varchar(10) NOT NULL,
  `sectionname` varchar(50) DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  `amount` int(11) NOT NULL,
  `session_name` varchar(50) NOT NULL,
  `json_str` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`json_str`)),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gps_data`
--

CREATE TABLE `gps_data` (
  `id` int(20) NOT NULL,
  `gps_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groupmaster`
--

CREATE TABLE `groupmaster` (
  `id` int(11) NOT NULL,
  `class_group` text DEFAULT NULL,
  `primary_group_name` text DEFAULT NULL,
  `group_name` text DEFAULT NULL,
  `display_order` text DEFAULT NULL,
  `health_group` text DEFAULT NULL,
  `entry_type` text DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holds_structure_row`
--

CREATE TABLE `holds_structure_row` (
  `id` int(11) NOT NULL,
  `fees_date` varchar(155) NOT NULL,
  `due_date` varchar(155) NOT NULL,
  `term` varchar(155) NOT NULL,
  `account_name` varchar(155) NOT NULL,
  `fees` varchar(155) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inquiry_registration`
--

CREATE TABLE `inquiry_registration` (
  `id` int(11) NOT NULL COMMENT 'uniqe_id',
  `application_for` varchar(255) DEFAULT NULL,
  `form_number` int(10) DEFAULT NULL,
  `date_of_birth` varchar(255) DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `session_name` varchar(255) DEFAULT NULL,
  `json_str` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'contains hold fields',
  `phone_number` int(11) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `inq_mode` varchar(5) DEFAULT NULL COMMENT 'on-online, off-offline',
  `status` varchar(1) DEFAULT 'i' COMMENT 'p-pre inq, i-inquiry, r-registration',
  `type` varchar(100) DEFAULT NULL,
  `save_status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `late_fees_master`
--

CREATE TABLE `late_fees_master` (
  `id` int(11) NOT NULL,
  `late_fees_amount` varchar(155) DEFAULT NULL,
  `from_amount` varchar(155) DEFAULT NULL,
  `to_amount` varchar(155) DEFAULT NULL,
  `upto` varchar(155) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 no delete ,1 deleted',
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `late_fees_max_limit`
--

CREATE TABLE `late_fees_max_limit` (
  `id` int(11) NOT NULL,
  `from_this_no_of_days` varchar(255) NOT NULL,
  `to_this_no_of_days` varchar(255) NOT NULL,
  `max_late_fees` varchar(155) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenancegroupmaster`
--

CREATE TABLE `maintenancegroupmaster` (
  `id` int(20) NOT NULL,
  `maintenance_group_name` varchar(200) NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenanceheadmaster`
--

CREATE TABLE `maintenanceheadmaster` (
  `id` int(20) NOT NULL,
  `maintenance_group_name` varchar(100) NOT NULL,
  `maintenance_head_name` varchar(200) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL DEFAULT 'App\\Models\\User',
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `natureofwork`
--

CREATE TABLE `natureofwork` (
  `id` int(50) NOT NULL,
  `nature_of_work_name` varchar(200) NOT NULL,
  `nature_of_work_remarks` longtext NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partymaster`
--

CREATE TABLE `partymaster` (
  `id` int(25) NOT NULL,
  `Party_Name` varchar(100) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `Tax` varchar(100) NOT NULL,
  `City` varchar(100) NOT NULL,
  `State` varchar(100) NOT NULL,
  `PinCode` varchar(100) NOT NULL,
  `locality` varchar(100) NOT NULL,
  `STDCode` varchar(100) NOT NULL,
  `REsidence_ph_no_1` varchar(100) NOT NULL,
  `Office_ph_no_1` varchar(100) NOT NULL,
  `REsidence_ph_no_2` varchar(100) NOT NULL,
  `Office_ph_no_2` varchar(100) NOT NULL,
  `Mobile` varchar(100) NOT NULL,
  `emailId` varchar(100) NOT NULL,
  `Fax_no_` varchar(100) NOT NULL,
  `Service_Tax_no_` varchar(100) NOT NULL,
  `PAN_no_` varchar(100) NOT NULL,
  `CST_no_` varchar(100) NOT NULL,
  `TIN_no_` varchar(100) NOT NULL,
  `TAN_no_` varchar(100) NOT NULL,
  `GST_no_` varchar(100) NOT NULL,
  `Party_Flag` varchar(100) NOT NULL,
  `Contactif` varchar(225) NOT NULL,
  `validUpto` varchar(100) NOT NULL,
  `Remarks` varchar(200) NOT NULL,
  `Person_Name` varchar(100) NOT NULL,
  `Mobile_NO_` varchar(100) NOT NULL,
  `Department_` varchar(100) NOT NULL,
  `Post` varchar(100) NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 none, 1 delete',
  `updated_at` varchar(100) NOT NULL,
  `created_at` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `routeareabusmaster`
--

CREATE TABLE `routeareabusmaster` (
  `id` int(20) NOT NULL,
  `route_name` varchar(100) DEFAULT NULL,
  `area_name` varchar(100) DEFAULT NULL,
  `bus_stop_name` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_delete` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `routemaster`
--

CREATE TABLE `routemaster` (
  `id` int(20) NOT NULL,
  `route_name` varchar(200) NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rtopaper`
--

CREATE TABLE `rtopaper` (
  `id` int(50) NOT NULL,
  `rto_paper_name` varchar(200) NOT NULL,
  `remark` longtext DEFAULT NULL,
  `is_permit` varchar(3) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rto_paper`
--

CREATE TABLE `rto_paper` (
  `id` int(100) NOT NULL,
  `Renewal_Date` varchar(200) NOT NULL,
  `Next_Renewal_Date` varchar(200) NOT NULL,
  `Registration_Date` varchar(200) NOT NULL,
  `Vehicle` varchar(200) NOT NULL,
  `Transfer_date` varchar(200) NOT NULL,
  `RTO_paper_Name` varchar(200) NOT NULL,
  `Document` varchar(200) NOT NULL,
  `Reminder_Frequency` varchar(200) NOT NULL,
  `image` varchar(200) NOT NULL,
  `updated_at` varchar(200) NOT NULL,
  `created_at` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedulemaster`
--

CREATE TABLE `schedulemaster` (
  `id` int(20) NOT NULL,
  `schedule_name` varchar(200) NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Not Deleted , 1 : Deleted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedulemasterall`
--

CREATE TABLE `schedulemasterall` (
  `id` int(20) NOT NULL,
  `schedule_name` varchar(200) NOT NULL,
  `schedule_check_two` text NOT NULL,
  `schedule_check_one` longtext DEFAULT NULL,
  `schedule_date` date DEFAULT NULL,
  `schedule_time_from` varchar(20) DEFAULT NULL,
  `schedule_time_to` varchar(20) DEFAULT NULL,
  `schedule_print_option` varchar(20) DEFAULT NULL,
  `schedule_point` varchar(10) DEFAULT NULL,
  `schedule_order` int(2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scholarbusassign`
--

CREATE TABLE `scholarbusassign` (
  `id` int(20) NOT NULL,
  `student_id_select_p` int(20) NOT NULL,
  `pick_shedule_name` varchar(50) DEFAULT NULL,
  `pick_up_routes` varchar(50) DEFAULT NULL,
  `pickup_area_name` varchar(50) DEFAULT NULL,
  `pickup_bus_stop_names` varchar(50) DEFAULT NULL,
  `pickup_bus_no` varchar(50) DEFAULT NULL,
  `drop_shedule_name` varchar(50) DEFAULT NULL,
  `drop_up_route` varchar(50) DEFAULT NULL,
  `drop_area_name` varchar(50) DEFAULT NULL,
  `drop_bus_stop_name` varchar(50) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `section` text NOT NULL,
  `remark` text NOT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `country_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `streams`
--

CREATE TABLE `streams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `streams` text NOT NULL,
  `remark` text NOT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_registration`
--

CREATE TABLE `student_registration` (
  `id` int(11) NOT NULL COMMENT 'uniqe_id',
  `application_for` varchar(255) DEFAULT NULL,
  `form_number` varchar(200) DEFAULT NULL,
  `scholar_no` varchar(50) DEFAULT NULL,
  `date_of_birth` varchar(255) DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `session_name` varchar(255) DEFAULT NULL,
  `json_str` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'contains hold fields',
  `staff_name` text DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `inq_mode` varchar(5) DEFAULT NULL COMMENT 'on-online, off-offline',
  `status` varchar(1) DEFAULT NULL COMMENT 'p-pre inq, i-inquiry, r-registration',
  `driver` varchar(200) DEFAULT NULL,
  `password` varchar(225) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjectmaster`
--

CREATE TABLE `subjectmaster` (
  `id` int(20) NOT NULL,
  `subject_name` varchar(225) DEFAULT NULL,
  `subject_type` varchar(20) DEFAULT NULL,
  `evaluation` varchar(20) DEFAULT NULL,
  `practical` varchar(10) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacherbusassign`
--

CREATE TABLE `teacherbusassign` (
  `id` int(20) NOT NULL,
  `student_id_select_p` int(20) NOT NULL,
  `pick_shedule_name` varchar(100) DEFAULT NULL,
  `pick_up_routes` varchar(100) DEFAULT NULL,
  `pickup_area_name` varchar(100) DEFAULT NULL,
  `pickup_bus_stop_names` varchar(100) DEFAULT NULL,
  `pickup_bus_no` varchar(100) DEFAULT NULL,
  `drop_shedule_name` varchar(100) DEFAULT NULL,
  `drop_up_route` varchar(100) DEFAULT NULL,
  `drop_area_name` varchar(100) DEFAULT NULL,
  `drop_bus_stop_name` varchar(100) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 none, 1 delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subjects`
--

CREATE TABLE `teacher_subjects` (
  `id` int(11) NOT NULL,
  `class_name` text DEFAULT NULL,
  `section_name` text DEFAULT NULL,
  `subject_name` text DEFAULT NULL,
  `teacher_name` text DEFAULT NULL,
  `session_name` varchar(255) DEFAULT NULL,
  `current_date` varchar(50) DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE `terms` (
  `id` int(11) NOT NULL,
  `terms` varchar(200) NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 for none, 1 for delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `form_number` int(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userssss`
--

CREATE TABLE `userssss` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicel`
--

CREATE TABLE `vehicel` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `callno` varchar(100) DEFAULT NULL,
  `vehicelno` varchar(100) DEFAULT NULL,
  `vehiceltype` varchar(100) DEFAULT NULL,
  `nature` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `purchase` date DEFAULT NULL,
  `capacity` varchar(100) DEFAULT NULL,
  `standard` varchar(100) DEFAULT NULL,
  `imei` varchar(100) DEFAULT NULL,
  `machine` varchar(100) DEFAULT NULL,
  `studentrelated` varchar(100) DEFAULT NULL,
  `scrapped` varchar(100) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 none : 1 Delete',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `areamaster`
--
ALTER TABLE `areamaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `busattandence`
--
ALTER TABLE `busattandence`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `busdata`
--
ALTER TABLE `busdata`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `busfees`
--
ALTER TABLE `busfees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `busstaff`
--
ALTER TABLE `busstaff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `busstop`
--
ALTER TABLE `busstop`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `caste_name`
--
ALTER TABLE `caste_name`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_name`
--
ALTER TABLE `class_name`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_fees_head_master`
--
ALTER TABLE `course_fees_head_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_fees_structure_master`
--
ALTER TABLE `course_fees_structure_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dailattandence`
--
ALTER TABLE `dailattandence`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defaulters_lists`
--
ALTER TABLE `defaulters_lists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exammasters`
--
ALTER TABLE `exammasters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `feesreceiptchallan`
--
ALTER TABLE `feesreceiptchallan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feestypes`
--
ALTER TABLE `feestypes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fees_types_master`
--
ALTER TABLE `fees_types_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `generate_duechartstatus`
--
ALTER TABLE `generate_duechartstatus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gps_data`
--
ALTER TABLE `gps_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groupmaster`
--
ALTER TABLE `groupmaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `holds_structure_row`
--
ALTER TABLE `holds_structure_row`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inquiry_registration`
--
ALTER TABLE `inquiry_registration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `late_fees_master`
--
ALTER TABLE `late_fees_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `late_fees_max_limit`
--
ALTER TABLE `late_fees_max_limit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenancegroupmaster`
--
ALTER TABLE `maintenancegroupmaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenanceheadmaster`
--
ALTER TABLE `maintenanceheadmaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `natureofwork`
--
ALTER TABLE `natureofwork`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partymaster`
--
ALTER TABLE `partymaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `routeareabusmaster`
--
ALTER TABLE `routeareabusmaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `routemaster`
--
ALTER TABLE `routemaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rtopaper`
--
ALTER TABLE `rtopaper`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rto_paper`
--
ALTER TABLE `rto_paper`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedulemaster`
--
ALTER TABLE `schedulemaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedulemasterall`
--
ALTER TABLE `schedulemasterall`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scholarbusassign`
--
ALTER TABLE `scholarbusassign`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `streams`
--
ALTER TABLE `streams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_registration`
--
ALTER TABLE `student_registration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjectmaster`
--
ALTER TABLE `subjectmaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacherbusassign`
--
ALTER TABLE `teacherbusassign`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- Indexes for table `userssss`
--
ALTER TABLE `userssss`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicel`
--
ALTER TABLE `vehicel`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `areamaster`
--
ALTER TABLE `areamaster`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `busattandence`
--
ALTER TABLE `busattandence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `busdata`
--
ALTER TABLE `busdata`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `busfees`
--
ALTER TABLE `busfees`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `busstaff`
--
ALTER TABLE `busstaff`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `busstop`
--
ALTER TABLE `busstop`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `caste_name`
--
ALTER TABLE `caste_name`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_name`
--
ALTER TABLE `class_name`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_fees_head_master`
--
ALTER TABLE `course_fees_head_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_fees_structure_master`
--
ALTER TABLE `course_fees_structure_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dailattandence`
--
ALTER TABLE `dailattandence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defaulters_lists`
--
ALTER TABLE `defaulters_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exammasters`
--
ALTER TABLE `exammasters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feesreceiptchallan`
--
ALTER TABLE `feesreceiptchallan`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feestypes`
--
ALTER TABLE `feestypes`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fees_types_master`
--
ALTER TABLE `fees_types_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `generate_duechartstatus`
--
ALTER TABLE `generate_duechartstatus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gps_data`
--
ALTER TABLE `gps_data`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groupmaster`
--
ALTER TABLE `groupmaster`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holds_structure_row`
--
ALTER TABLE `holds_structure_row`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inquiry_registration`
--
ALTER TABLE `inquiry_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'uniqe_id';

--
-- AUTO_INCREMENT for table `late_fees_master`
--
ALTER TABLE `late_fees_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenancegroupmaster`
--
ALTER TABLE `maintenancegroupmaster`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenanceheadmaster`
--
ALTER TABLE `maintenanceheadmaster`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `natureofwork`
--
ALTER TABLE `natureofwork`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `partymaster`
--
ALTER TABLE `partymaster`
  MODIFY `id` int(25) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `routeareabusmaster`
--
ALTER TABLE `routeareabusmaster`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `routemaster`
--
ALTER TABLE `routemaster`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rtopaper`
--
ALTER TABLE `rtopaper`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rto_paper`
--
ALTER TABLE `rto_paper`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedulemaster`
--
ALTER TABLE `schedulemaster`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedulemasterall`
--
ALTER TABLE `schedulemasterall`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scholarbusassign`
--
ALTER TABLE `scholarbusassign`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `streams`
--
ALTER TABLE `streams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_registration`
--
ALTER TABLE `student_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'uniqe_id';

--
-- AUTO_INCREMENT for table `subjectmaster`
--
ALTER TABLE `subjectmaster`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userssss`
--
ALTER TABLE `userssss`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicel`
--
ALTER TABLE `vehicel`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
