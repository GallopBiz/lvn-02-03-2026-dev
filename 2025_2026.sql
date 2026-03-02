-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 20, 2026 at 11:30 AM
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
-- Database: `2025_2026`
--

-- --------------------------------------------------------

--
-- Table structure for table `abvance_nextyear_fees`
--

CREATE TABLE `abvance_nextyear_fees` (
  `id` int(20) NOT NULL,
  `amount` varchar(200) DEFAULT NULL,
  `deleted_at` int(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `academic_class_subject`
--

CREATE TABLE `academic_class_subject` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `stream_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `academic_exam_subject_marks`
--

CREATE TABLE `academic_exam_subject_marks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `min_marks` int(11) NOT NULL,
  `max_marks` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `academic_student_daily_attendance`
--

CREATE TABLE `academic_student_daily_attendance` (
  `id` bigint(20) NOT NULL,
  `student_id` bigint(20) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_annual_defaulter_student`
--

CREATE TABLE `account_annual_defaulter_student` (
  `id` int(11) NOT NULL,
  `scholar_no` varchar(50) NOT NULL,
  `class` varchar(50) NOT NULL,
  `section` varchar(50) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `admission_fees` decimal(10,2) DEFAULT NULL,
  `alumni_fees` decimal(10,2) DEFAULT NULL,
  `bus_fees` decimal(10,2) DEFAULT NULL,
  `caution_money` decimal(10,2) DEFAULT NULL,
  `lunch_fees` decimal(10,2) DEFAULT NULL,
  `tuition_fees` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `AttendanceID` bigint(20) NOT NULL,
  `EmployeeID` bigint(20) NOT NULL DEFAULT 0,
  `Date` text DEFAULT NULL,
  `Status` varchar(200) NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 none, 1 delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `emergency_contact_no` varchar(50) DEFAULT NULL,
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
-- Table structure for table `bus_maintenance`
--

CREATE TABLE `bus_maintenance` (
  `id` int(11) NOT NULL,
  `vehicle_no` varchar(50) DEFAULT NULL,
  `maintenance_date` date DEFAULT NULL,
  `maintenance_group_id` int(11) NOT NULL,
  `maintenance_head_id` int(11) NOT NULL,
  `amount` decimal(10,0) NOT NULL,
  `remarks` text DEFAULT NULL,
  `invoice_file` varchar(50) DEFAULT NULL,
  `invoice_date` date NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
-- Table structure for table `classasigntoteacher`
--

CREATE TABLE `classasigntoteacher` (
  `id` int(11) NOT NULL,
  `Class` varchar(200) NOT NULL,
  `Section` varchar(200) NOT NULL,
  `Teacher_1` varchar(200) DEFAULT NULL,
  `Teacher_2` varchar(200) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0-none, 1-delete',
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `teacher_namee` varchar(50) DEFAULT NULL,
  `teacher_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `section_name` text DEFAULT NULL,
  `start_time` varchar(20) DEFAULT NULL,
  `end_time` varchar(20) DEFAULT NULL,
  `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `combination_subject`
--

CREATE TABLE `combination_subject` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_combination_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `subject_order` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `Teacher_id` varchar(200) DEFAULT NULL,
  `section_name` varchar(200) DEFAULT NULL,
  `Attandence_date` varchar(200) DEFAULT NULL,
  `class_id` varchar(200) DEFAULT NULL,
  `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '0 none, 1 delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
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
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `DepartmentID` bigint(20) NOT NULL DEFAULT 0,
  `DepartmentName` varchar(200) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 none, 1 delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employeeleaves`
--

CREATE TABLE `employeeleaves` (
  `Emp_ID` int(11) NOT NULL,
  `CL_Balance` text DEFAULT NULL,
  `ML_Balance` text DEFAULT NULL,
  `EL_Balance` text DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 for none, 1 for delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `EmployeeID` bigint(20) NOT NULL,
  `FirstName` varchar(200) DEFAULT NULL,
  `LastName` varchar(200) DEFAULT NULL,
  `Father_Name` text DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Phone` text DEFAULT NULL,
  `mobile` text DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `JoiningDate` date DEFAULT NULL,
  `DepartureDate` date DEFAULT NULL,
  `DepartmentID` bigint(20) DEFAULT 0,
  `PositionID` text DEFAULT '0',
  `ess_emp_code` text DEFAULT NULL,
  `DeviceCode` text DEFAULT NULL,
  `Company` text DEFAULT NULL,
  `Location` text DEFAULT NULL,
  `Designation` text DEFAULT NULL,
  `Grade` text DEFAULT NULL,
  `Team` text DEFAULT NULL,
  `Category` text DEFAULT NULL,
  `EmploymentType` text DEFAULT NULL,
  `Gender` text DEFAULT NULL,
  `DOJ` date DEFAULT NULL,
  `DOC` date DEFAULT NULL,
  `CardNumber` text DEFAULT NULL,
  `ShiftRoaster` text DEFAULT NULL,
  `Status` text DEFAULT NULL,
  `City` text DEFAULT NULL,
  `Taluka` text DEFAULT NULL,
  `District` text DEFAULT NULL,
  `PIN_Code` text DEFAULT NULL,
  `Experience` text DEFAULT NULL,
  `Educational_Qualification` text DEFAULT NULL,
  `Previous_Employer` text DEFAULT NULL,
  `Prevoius_Designation` text DEFAULT NULL,
  `Previous_Salary` text DEFAULT NULL,
  `Duration` text DEFAULT NULL,
  `Maratial_Status` text DEFAULT NULL,
  `Spouse_Name` text DEFAULT NULL,
  `Spouse_Contact_No` text DEFAULT NULL,
  `No_of_Childern` text DEFAULT NULL,
  `Serial_No` text DEFAULT NULL,
  `Joining_Designation` text DEFAULT NULL,
  `Joining_Grade` text DEFAULT NULL,
  `Current_Grade` text DEFAULT NULL,
  `Working_Shift` text DEFAULT NULL,
  `Default_In_Time` text DEFAULT NULL,
  `Default_Out_Time` text DEFAULT NULL,
  `Default_Total_Time` text DEFAULT NULL,
  `Bank_Name` text DEFAULT NULL,
  `Bank_Branch_Name` text DEFAULT NULL,
  `Account_No` text DEFAULT NULL,
  `Do_Not_Apply_EPF_Limit` text DEFAULT NULL,
  `ESI_No` text DEFAULT NULL,
  `PAN_No` text DEFAULT NULL,
  `Ward_Study_in_Institute` text DEFAULT NULL,
  `Is_Discontinued` text DEFAULT NULL,
  `Leaving_Date` text DEFAULT NULL,
  `Adhar_CardNo` text DEFAULT NULL,
  `UAN_No` text DEFAULT NULL,
  `IFSCCODE` text DEFAULT NULL,
  `AyushmanNo` text DEFAULT NULL,
  `IsFirstDoseVaccinated` text DEFAULT NULL,
  `FirstDoseVaccinatedDate` text DEFAULT NULL,
  `IsSecondDoseVaccinated` text DEFAULT NULL,
  `SecondDoseVaccinatedDate` text DEFAULT NULL,
  `IsBoosterDoseVaccinated` text DEFAULT NULL,
  `BoosterDoseVaccinatedDate` text DEFAULT NULL,
  `samagra_id` text DEFAULT NULL,
  `Police_Verification` text DEFAULT NULL,
  `EmpName_as_on_Adhaar` text DEFAULT NULL,
  `PersonalEmail` text DEFAULT NULL,
  `staff_type` text DEFAULT NULL,
  `shift_type` text DEFAULT NULL,
  `is_vacatation_staff` text DEFAULT '0',
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 for none, 1 for delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_thumb_attendance`
--

CREATE TABLE `employee_thumb_attendance` (
  `id` int(11) NOT NULL,
  `ess_emp_code` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `in_time` datetime NOT NULL,
  `out_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exammasters`
--

CREATE TABLE `exammasters` (
  `id` int(11) NOT NULL,
  `exam_name` text DEFAULT NULL,
  `exam_type` text DEFAULT NULL,
  `class_id` text DEFAULT NULL,
  `stream_id` text DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `examtypes`
--

CREATE TABLE `examtypes` (
  `id` int(11) NOT NULL,
  `examtype` text DEFAULT NULL,
  `is_delete` int(222) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `class_name` text DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `old_fee_rec_no` varchar(100) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feesreceiptchallan_cancel`
--

CREATE TABLE `feesreceiptchallan_cancel` (
  `id` int(20) NOT NULL,
  `student_id` int(20) DEFAULT NULL,
  `student_dob` varchar(100) DEFAULT NULL,
  `recpt_chain` varchar(200) DEFAULT NULL,
  `due_upto` varchar(200) DEFAULT NULL,
  `name_student` varchar(200) DEFAULT NULL,
  `str_json` longtext DEFAULT NULL,
  `class_name` text DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `old_fee_rec_no` varchar(100) NOT NULL DEFAULT '0'
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
  `remark` varchar(255) DEFAULT NULL,
  `session` varchar(255) NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Deleted',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_exemption_master`
--

CREATE TABLE `fee_exemption_master` (
  `id` int(50) NOT NULL,
  `exmeption_type` varchar(50) NOT NULL,
  `description` varchar(50) NOT NULL,
  `percentage` int(20) NOT NULL,
  `head_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Final-employees`
--

CREATE TABLE `Final-employees` (
  `EmployeeID` smallint(6) DEFAULT NULL,
  `FirstName` varchar(16) DEFAULT NULL,
  `LastName` varchar(13) DEFAULT NULL,
  `Email` varchar(0) DEFAULT NULL,
  `Phone` varchar(0) DEFAULT NULL,
  `Address` varchar(0) DEFAULT NULL,
  `DateOfBirth` varchar(0) DEFAULT NULL,
  `JoiningDate` varchar(0) DEFAULT NULL,
  `DepartureDate` varchar(0) DEFAULT NULL,
  `DepartmentID` varchar(14) DEFAULT NULL,
  `PositionID` varchar(0) DEFAULT NULL,
  `ess_emp_code` smallint(6) DEFAULT NULL,
  `DeviceCode` smallint(6) DEFAULT NULL,
  `Company` varchar(7) DEFAULT NULL,
  `Location` varchar(6) DEFAULT NULL,
  `Designation` varchar(24) DEFAULT NULL,
  `Grade` varchar(7) DEFAULT NULL,
  `Team` varchar(7) DEFAULT NULL,
  `Category` varchar(7) DEFAULT NULL,
  `EmploymentType` varchar(9) DEFAULT NULL,
  `Gender` varchar(6) DEFAULT NULL,
  `DOJ` varchar(10) DEFAULT NULL,
  `DOC` varchar(10) DEFAULT NULL,
  `CardNumber` varchar(4) DEFAULT NULL,
  `ShiftRoaster` varchar(0) DEFAULT NULL,
  `Status` varchar(8) DEFAULT NULL,
  `is_delete` varchar(0) DEFAULT NULL,
  `created_at` varchar(0) DEFAULT NULL,
  `updated_at` varchar(0) DEFAULT NULL
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
  `session_name` varchar(50) DEFAULT NULL,
  `json_str` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`json_str`)),
  `is_rte` varchar(20) DEFAULT NULL,
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
-- Table structure for table `gps_data1`
--

CREATE TABLE `gps_data1` (
  `id` int(20) NOT NULL,
  `vehicle_name` varchar(100) DEFAULT NULL,
  `gps` varchar(10) DEFAULT NULL,
  `vehicle_no` varchar(20) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `vehicletype` varchar(10) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `speed` varchar(10) DEFAULT NULL,
  `ign` varchar(10) DEFAULT NULL,
  `battery_percentage` varchar(10) DEFAULT NULL,
  `power` varchar(10) DEFAULT NULL,
  `location` text DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `bus_url` varchar(225) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : delete',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grademaster`
--

CREATE TABLE `grademaster` (
  `id` int(11) NOT NULL,
  `grading_name` text DEFAULT NULL,
  `applicable` text DEFAULT NULL,
  `min_per` text DEFAULT NULL,
  `max_per` text DEFAULT NULL,
  `grade` text DEFAULT NULL,
  `groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`groups`)),
  `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `termigradecoscholasticareas` text DEFAULT NULL,
  `termiigradecoscholasticareas` text DEFAULT NULL,
  `termigradedicipline` text DEFAULT NULL,
  `termiigradedicipline` text DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete	',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
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
-- Table structure for table `headmaster`
--

CREATE TABLE `headmaster` (
  `id` int(11) NOT NULL,
  `class_name` text DEFAULT NULL,
  `group_name` text DEFAULT NULL,
  `head_name` text DEFAULT NULL,
  `display_order` text DEFAULT NULL,
  `applicable_to` text DEFAULT NULL,
  `is_elective` text DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
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
-- Table structure for table `hrms_attendance_locks`
--

CREATE TABLE `hrms_attendance_locks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` int(10) UNSIGNED NOT NULL,
  `month` tinyint(3) UNSIGNED NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_basic_deductions`
--

CREATE TABLE `hrms_basic_deductions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_compoff_requests`
--

CREATE TABLE `hrms_compoff_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `is_half_day` varchar(50) DEFAULT NULL,
  `half_day_type` varchar(50) DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_departments`
--

CREATE TABLE `hrms_departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employees`
--

CREATE TABLE `hrms_employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` varchar(255) NOT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `marital_status` varchar(255) NOT NULL,
  `spouse_name` varchar(255) DEFAULT NULL,
  `contact_number` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `date_of_joining` date NOT NULL,
  `confirmation_date` date DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `shift_id` bigint(20) UNSIGNED NOT NULL,
  `position_id` bigint(20) UNSIGNED NOT NULL,
  `is_vacation` tinyint(1) NOT NULL DEFAULT 0,
  `profile_picture` varchar(255) DEFAULT NULL,
  `gross_salary` decimal(10,2) DEFAULT NULL,
  `employee_status` varchar(50) DEFAULT NULL,
  `employee_status_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `staff_type_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_addresses`
--

CREATE TABLE `hrms_employee_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `address_type` varchar(255) DEFAULT NULL,
  `address_line` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `tehsil` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `pin_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_attendance`
--

CREATE TABLE `hrms_employee_attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ess_emp_code` varchar(255) DEFAULT NULL,
  `log_date` date NOT NULL,
  `in_time` time DEFAULT NULL,
  `out_time` time DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `entry_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_bank_details`
--

CREATE TABLE `hrms_employee_bank_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `ifsc_code` varchar(255) DEFAULT NULL,
  `branch_name` varchar(255) DEFAULT NULL,
  `pan_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_biometric_details`
--

CREATE TABLE `hrms_employee_biometric_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `card_number` varchar(255) DEFAULT NULL,
  `ess_emp_code` varchar(255) DEFAULT NULL,
  `device_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_childrens`
--

CREATE TABLE `hrms_employee_childrens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_amount` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `employee_salary_id` bigint(20) UNSIGNED NOT NULL,
  `emi_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_deductions`
--

CREATE TABLE `hrms_employee_deductions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `basic_deduction_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `employee_salary_id` bigint(20) UNSIGNED NOT NULL,
  `manual_amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_deduction_history`
--

CREATE TABLE `hrms_employee_deduction_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `employee_salary_id` bigint(20) UNSIGNED NOT NULL,
  `basic_deduction_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `month` int(11) NOT NULL,
  `year` varchar(120) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_documents`
--

CREATE TABLE `hrms_employee_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(255) DEFAULT NULL,
  `is_uploaded` tinyint(1) DEFAULT 0,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_educations`
--

CREATE TABLE `hrms_employee_educations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `institution_name` varchar(255) DEFAULT NULL,
  `year_of_graduation` year(4) DEFAULT NULL,
  `certification` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_emergency_contacts`
--

CREATE TABLE `hrms_employee_emergency_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `relationship` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `alternative_phone_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_experiences`
--

CREATE TABLE `hrms_employee_experiences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `roles_responsibilities` text DEFAULT NULL,
  `total_experience` varchar(25) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_leave_balances`
--

CREATE TABLE `hrms_employee_leave_balances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `balance` float NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_loans`
--

CREATE TABLE `hrms_employee_loans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `loan_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `emi_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `interest_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `duration_months` int(11) NOT NULL,
  `original_duration_months` int(11) NOT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `refund_amount` varchar(50) DEFAULT NULL,
  `refund_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_loan_emis`
--

CREATE TABLE `hrms_employee_loan_emis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `emi_date` date NOT NULL,
  `emi_amount` decimal(10,2) NOT NULL,
  `principal_component` decimal(10,2) NOT NULL,
  `interest_component` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','paused','recalculated') NOT NULL DEFAULT 'pending',
  `payment_mode` enum('salary','cash') DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_salaries`
--

CREATE TABLE `hrms_employee_salaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `health_insurance` varchar(255) DEFAULT NULL,
  `retirement_benefits` varchar(255) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_security_deposits`
--

CREATE TABLE `hrms_employee_security_deposits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `security_deposit` decimal(10,2) NOT NULL,
  `emi_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','completed','refund') NOT NULL DEFAULT 'pending',
  `duration_months` int(11) NOT NULL DEFAULT 0,
  `original_duration_months` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_security_deposit_emis`
--

CREATE TABLE `hrms_employee_security_deposit_emis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `security_deposit_id` bigint(20) UNSIGNED NOT NULL,
  `emi_date` date NOT NULL,
  `emi_amount` decimal(10,2) NOT NULL,
  `principal_component` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','paused') NOT NULL DEFAULT 'pending',
  `payment_mode` varchar(255) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_statutory_info`
--

CREATE TABLE `hrms_employee_statutory_info` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `esic_number` varchar(255) DEFAULT NULL,
  `epf_number` varchar(255) DEFAULT NULL,
  `uan_number` varchar(255) DEFAULT NULL,
  `samagra_id` varchar(255) DEFAULT NULL,
  `pan_number` varchar(255) DEFAULT NULL,
  `aadhar_number` varchar(255) DEFAULT NULL,
  `ayushman_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_employee_student_fee_emis`
--

CREATE TABLE `hrms_employee_student_fee_emis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_salary_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` int(11) NOT NULL,
  `employee_child_id` bigint(20) UNSIGNED NOT NULL,
  `emi_date` date NOT NULL,
  `emi_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','paused') NOT NULL DEFAULT 'pending',
  `paid_in_cash` decimal(10,2) DEFAULT NULL COMMENT 'Amount paid in cash if applicable',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_holidays`
--

CREATE TABLE `hrms_holidays` (
  `HolidayID` bigint(20) NOT NULL,
  `HolidayName` varchar(200) DEFAULT NULL,
  `HolidayStartDate` date DEFAULT NULL,
  `HolidayEndDate` date DEFAULT NULL,
  `HolidayDescription` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_leave_requests`
--

CREATE TABLE `hrms_leave_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `is_half_day` varchar(50) DEFAULT NULL,
  `half_day_type` varchar(50) DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rollback_done` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_leave_types`
--

CREATE TABLE `hrms_leave_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `annual_entitlement` int(11) DEFAULT NULL,
  `reset_month` tinyint(4) NOT NULL DEFAULT 6,
  `rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rules`)),
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `is_carry_forward` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `apply_before_days` int(11) DEFAULT NULL,
  `allow_backdate` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_payrolls`
--

CREATE TABLE `hrms_payrolls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `gross_salary` decimal(10,2) NOT NULL,
  `basic_salary` decimal(10,2) DEFAULT NULL,
  `net_salary` decimal(10,2) NOT NULL,
  `deductions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`deductions`)),
  `hra` decimal(10,2) DEFAULT NULL,
  `da` decimal(10,2) DEFAULT NULL,
  `earn_salary` varchar(25) DEFAULT NULL,
  `epfa` float(10,2) DEFAULT NULL,
  `esic` float(10,2) DEFAULT NULL,
  `approved_leaves` int(11) NOT NULL,
  `unapproved_leaves` int(11) NOT NULL,
  `lwp_days` int(11) NOT NULL,
  `late_deduction_amount` decimal(10,2) DEFAULT NULL,
  `month` varchar(255) NOT NULL,
  `year` varchar(255) NOT NULL,
  `generated_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_positions`
--

CREATE TABLE `hrms_positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `position_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_security_deposite_refunds`
--

CREATE TABLE `hrms_security_deposite_refunds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(50) NOT NULL,
  `loan_id` bigint(50) NOT NULL,
  `refund_amount` decimal(10,0) NOT NULL,
  `refund_date` date DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_shifts`
--

CREATE TABLE `hrms_shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shift_type_id` bigint(20) UNSIGNED NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `late_coming_threshold` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_shift_types`
--

CREATE TABLE `hrms_shift_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shift_type_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_staff_leave_allocation`
--

CREATE TABLE `hrms_staff_leave_allocation` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `hrms_staff_type_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT 1,
  `max_allowed` int(11) DEFAULT NULL,
  `max_per_instance` int(11) DEFAULT NULL,
  `is_vacation` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrms_staff_type`
--

CREATE TABLE `hrms_staff_type` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `staff_type_name` varchar(255) NOT NULL,
  `shift_type_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `initial_leave`
--

CREATE TABLE `initial_leave` (
  `id` int(11) NOT NULL,
  `staff_name` text DEFAULT NULL,
  `leave_name` int(11) DEFAULT 0,
  `total_allotted` int(11) DEFAULT 0,
  `assigned_to_employee` text DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '	0 none, 1 delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
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
  `next_year` varchar(5) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `save_status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `section_name` varchar(20) DEFAULT NULL,
  `assign_calling` varchar(20) DEFAULT NULL,
  `is_delete` int(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `leaverequests`
--

CREATE TABLE `leaverequests` (
  `LeaveRequestID` bigint(20) NOT NULL,
  `EmployeeID` bigint(20) DEFAULT 0,
  `LeaveStartDate` date DEFAULT NULL,
  `LeaveEndDate` date DEFAULT NULL,
  `LeaveType` varchar(200) DEFAULT NULL,
  `Status` varchar(200) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 none, 1 delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_allocation`
--

CREATE TABLE `leave_allocation` (
  `Leave_Allocation_ID` int(11) NOT NULL,
  `Staff_Type_ID` int(11) DEFAULT NULL,
  `CL_Allocation` text DEFAULT NULL,
  `ML_Allocation` text DEFAULT NULL,
  `EL_Allocation` text DEFAULT NULL,
  `Max_CL_At_Time` text DEFAULT NULL,
  `Max_EL_At_Time` text DEFAULT NULL,
  `Max_ML_At_Time` text DEFAULT NULL,
  `DepartmentName` text DEFAULT NULL,
  `PositionName` text DEFAULT NULL,
  `leave_name` text DEFAULT NULL,
  `short_name` text DEFAULT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT 1,
  `is_vacation` tinyint(1) NOT NULL DEFAULT 1,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 for none, 1 for delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` int(11) NOT NULL,
  `marks_id` varchar(255) DEFAULT NULL,
  `is_absent` text DEFAULT NULL,
  `is_absent_pr` text DEFAULT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `subject_marks` varchar(255) DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 none, 1 delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
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
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `PositionID` bigint(20) NOT NULL DEFAULT 0,
  `PositionName` varchar(200) NOT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 none, 1 delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presentlyschool`
--

CREATE TABLE `presentlyschool` (
  `id` int(10) NOT NULL,
  `school_name` varchar(225) DEFAULT NULL,
  `deleted_at` int(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `previosly_saved_marks_entry`
--

CREATE TABLE `previosly_saved_marks_entry` (
  `id` int(11) NOT NULL,
  `teacher_id` varchar(255) DEFAULT NULL,
  `exam_id` varchar(255) DEFAULT NULL,
  `class_id` varchar(255) DEFAULT NULL,
  `section_name` varchar(255) DEFAULT NULL,
  `Stream_id` varchar(255) DEFAULT NULL,
  `subject_id` varchar(255) DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `primarygroup`
--

CREATE TABLE `primarygroup` (
  `id` int(11) NOT NULL,
  `class_group` text DEFAULT NULL,
  `primary_group_name` text DEFAULT NULL,
  `display_order` text DEFAULT NULL,
  `visibility` text DEFAULT NULL,
  `is_delete` int(111) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remarksmaster`
--

CREATE TABLE `remarksmaster` (
  `id` int(10) NOT NULL,
  `not_show` varchar(20) DEFAULT NULL,
  `remark` varchar(20) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_delete` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `salaries`
--

CREATE TABLE `salaries` (
  `SalaryID` bigint(20) NOT NULL,
  `EmployeeID` bigint(20) DEFAULT 0,
  `SalaryAmount` text DEFAULT NULL,
  `EffectiveDate` text DEFAULT NULL,
  `AttendanceMonth` text DEFAULT NULL,
  `is_delete` int(1) DEFAULT 0 COMMENT '0 none, 1 delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `latLngInput` varchar(100) DEFAULT NULL,
  `studentaddcheck` varchar(200) DEFAULT NULL
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
-- Table structure for table `shift`
--

CREATE TABLE `shift` (
  `Shift_ID` int(11) NOT NULL,
  `Shift_Type_ID` int(11) DEFAULT NULL,
  `Start_Time` varchar(10) DEFAULT NULL,
  `End_Time` varchar(10) DEFAULT NULL,
  `Late_Coming_Threshold` varchar(10) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shift_type`
--

CREATE TABLE `shift_type` (
  `Shift_Type_ID` int(11) NOT NULL,
  `Type` text DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 none, 1 delete	',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_type`
--

CREATE TABLE `staff_type` (
  `Staff_Type_ID` int(11) NOT NULL,
  `Type` text DEFAULT NULL,
  `shift` text DEFAULT NULL,
  `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '0 none, 1 delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `student_bus_fees`
--

CREATE TABLE `student_bus_fees` (
  `id` int(11) NOT NULL,
  `class` varchar(10) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `scholar_no` varchar(50) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `father` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `bus_fees_type` varchar(50) DEFAULT NULL,
  `installment` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_bus_fees_balance`
--

CREATE TABLE `student_bus_fees_balance` (
  `id` int(11) NOT NULL,
  `scholar_no` varchar(50) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_fees_data`
--

CREATE TABLE `student_fees_data` (
  `id` int(11) NOT NULL,
  `FeeRecNo` int(11) NOT NULL,
  `StudentName` varchar(255) NOT NULL,
  `ScholarNo` int(11) NOT NULL,
  `Class` varchar(50) NOT NULL,
  `Section` varchar(50) NOT NULL,
  `AccountName` varchar(255) NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL,
  `PostedNoDt` varchar(255) NOT NULL,
  `Terms` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_fees_exempted`
--

CREATE TABLE `student_fees_exempted` (
  `student_id` int(11) NOT NULL,
  `scholar_no` int(11) NOT NULL,
  `exempt_per` int(11) DEFAULT NULL,
  `lum` tinyint(1) DEFAULT NULL,
  `lunch` tinyint(1) DEFAULT NULL,
  `lssm` tinyint(1) DEFAULT NULL,
  `sibling` tinyint(1) DEFAULT NULL,
  `staff` tinyint(1) DEFAULT NULL,
  `last_year_lssm` tinyint(1) DEFAULT NULL,
  `head_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_fees_receipt_breakdown`
--

CREATE TABLE `student_fees_receipt_breakdown` (
  `id` int(11) NOT NULL,
  `challan_id` int(11) NOT NULL,
  `fee_head` varchar(255) NOT NULL,
  `term_number` int(11) DEFAULT NULL,
  `total_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `net_total` decimal(10,2) NOT NULL,
  `allocated_amount` decimal(10,2) NOT NULL,
  `balance_amount` decimal(10,2) NOT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_fees_receipt_challan`
--

CREATE TABLE `student_fees_receipt_challan` (
  `challan_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `challan_date` varchar(10) DEFAULT NULL,
  `total_received` decimal(10,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `term_advance_balance` int(10) NOT NULL DEFAULT 0,
  `old_fee_rec_no` int(100) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_fee_balances`
--

CREATE TABLE `student_fee_balances` (
  `id` int(11) NOT NULL,
  `scholar_no` varchar(50) NOT NULL,
  `fee_type` varchar(50) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `term` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_fee_exemption`
--

CREATE TABLE `student_fee_exemption` (
  `id` int(50) NOT NULL,
  `scholar_no` int(50) NOT NULL,
  `head_name` varchar(50) NOT NULL,
  `exemption_type` varchar(50) NOT NULL,
  `percentage` int(20) NOT NULL,
  `remark` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_online_fee_payments`
--

CREATE TABLE `student_online_fee_payments` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_term` varchar(255) NOT NULL,
  `fee_account` varchar(255) NOT NULL,
  `fees_due` decimal(10,2) NOT NULL,
  `allocated_amount` decimal(10,2) DEFAULT 0.00,
  `remaining_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','refunded') DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `payment_gateway_transaction_id` varchar(150) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `late_fee` int(20) NOT NULL DEFAULT 0,
  `discount` int(20) NOT NULL DEFAULT 0,
  `lumsum` int(10) NOT NULL DEFAULT 0,
  `settlement_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `class_id` varchar(255) DEFAULT NULL,
  `section_name` varchar(255) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `session_name` varchar(255) DEFAULT NULL,
  `json_str` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'contains hold fields',
  `staff_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `inq_mode` varchar(5) DEFAULT NULL COMMENT 'on-online, off-offline',
  `driver` varchar(200) DEFAULT NULL,
  `status` varchar(1) DEFAULT NULL COMMENT 'p-pre inq, i-inquiry, r-registration',
  `type` varchar(100) DEFAULT NULL,
  `student_image_url` varchar(200) DEFAULT NULL,
  `password` varchar(225) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `registration_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_registration_bk`
--

CREATE TABLE `student_registration_bk` (
  `id` int(11) NOT NULL COMMENT 'uniqe_id',
  `application_for` varchar(255) DEFAULT NULL,
  `form_number` varchar(200) DEFAULT NULL,
  `scholar_no` varchar(50) DEFAULT NULL,
  `date_of_birth` varchar(255) DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `session_name` varchar(255) DEFAULT NULL,
  `json_str` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'contains hold fields',
  `staff_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `inq_mode` varchar(5) DEFAULT NULL COMMENT 'on-online, off-offline',
  `driver` varchar(200) DEFAULT NULL,
  `status` varchar(1) DEFAULT NULL COMMENT 'p-pre inq, i-inquiry, r-registration',
  `type` varchar(100) DEFAULT NULL,
  `password` varchar(225) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subhead`
--

CREATE TABLE `subhead` (
  `id` int(20) NOT NULL,
  `class_group` varchar(200) DEFAULT NULL,
  `head_name` varchar(200) DEFAULT NULL,
  `sub_head_name` varchar(200) DEFAULT NULL,
  `display_order` varchar(200) DEFAULT NULL,
  `visibility` varchar(200) DEFAULT NULL,
  `e1` varchar(20) DEFAULT NULL,
  `e2` varchar(20) DEFAULT NULL,
  `e3` varchar(20) DEFAULT NULL,
  `e4` varchar(20) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subheads`
--

CREATE TABLE `subheads` (
  `id` int(11) NOT NULL,
  `class_group` text DEFAULT NULL,
  `head_name` text DEFAULT NULL,
  `sub_head_name` text DEFAULT NULL,
  `display_order` text DEFAULT NULL,
  `visibility` text DEFAULT NULL,
  `entry_type_e1` text DEFAULT NULL,
  `entry_type_e2` text DEFAULT NULL,
  `entry_type_e3` text DEFAULT NULL,
  `entry_type_e4` text DEFAULT NULL,
  `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjectmaster`
--

CREATE TABLE `subjectmaster` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
-- Table structure for table `subject_assign_student`
--

CREATE TABLE `subject_assign_student` (
  `id` int(11) NOT NULL,
  `class_name` text DEFAULT NULL,
  `section_name` text DEFAULT NULL,
  `assign_this_combtoall` text DEFAULT NULL,
  `students_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`students_details`)),
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subject_combinations`
--

CREATE TABLE `subject_combinations` (
  `id` bigint(11) UNSIGNED NOT NULL,
  `combination_name` text DEFAULT NULL,
  `class_id` varchar(255) DEFAULT NULL,
  `is_academic_comb` text DEFAULT NULL,
  `alise_name` text DEFAULT NULL,
  `streams` text DEFAULT NULL,
  `combination_type` text DEFAULT NULL COMMENT 'a-Academic, n-non Academic',
  `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '0 : Active , 1 : delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
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
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `teacher_name` text DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subjects`
--

CREATE TABLE `teacher_subjects` (
  `id` int(11) NOT NULL,
  `class_id` bigint(20) DEFAULT NULL,
  `stream_id` bigint(20) DEFAULT NULL,
  `section_name` text DEFAULT NULL,
  `subject_id` bigint(20) DEFAULT NULL,
  `teacher_id` bigint(20) DEFAULT NULL,
  `session_name` varchar(255) DEFAULT NULL,
  `current_date` varchar(50) DEFAULT NULL,
  `is_delete` int(11) DEFAULT 0 COMMENT '0 : Active , 1 : Delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(35) NOT NULL
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
-- Table structure for table `tmp_admission_update`
--

CREATE TABLE `tmp_admission_update` (
  `scholar_no` varchar(50) DEFAULT NULL,
  `add_date` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `totalnextyear`
--

CREATE TABLE `totalnextyear` (
  `id` int(11) NOT NULL,
  `fees_date` varchar(155) NOT NULL,
  `due_date` varchar(155) NOT NULL,
  `totalnextyear` varchar(155) NOT NULL,
  `account_name` varchar(155) NOT NULL,
  `fees` varchar(155) NOT NULL,
  `scholar_no` int(20) DEFAULT NULL,
  `received_type` varchar(20) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `receipt_number` int(20) DEFAULT NULL,
  `is_delete` int(1) NOT NULL DEFAULT 0 COMMENT 'Active : 0 and Delete : 1',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
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
  `rtopaper` varchar(55) DEFAULT NULL,
  `validfrom` date DEFAULT NULL,
  `validto` date DEFAULT NULL,
  `rtopaper_file` varchar(50) DEFAULT NULL,
  `fitnesspaper` varchar(50) DEFAULT NULL,
  `fitness_validfrom` date DEFAULT NULL,
  `fitness_validto` date DEFAULT NULL,
  `fitnesspaper_file` varchar(50) DEFAULT NULL,
  `gps_tracking_url` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abvance_nextyear_fees`
--
ALTER TABLE `abvance_nextyear_fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `academic_class_subject`
--
ALTER TABLE `academic_class_subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_subject` (`subject_id`),
  ADD KEY `fk_class` (`class_id`);

--
-- Indexes for table `academic_exam_subject_marks`
--
ALTER TABLE `academic_exam_subject_marks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `academic_student_daily_attendance`
--
ALTER TABLE `academic_student_daily_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `account_annual_defaulter_student`
--
ALTER TABLE `account_annual_defaulter_student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scholar_no` (`scholar_no`);

--
-- Indexes for table `areamaster`
--
ALTER TABLE `areamaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`AttendanceID`);

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
-- Indexes for table `bus_maintenance`
--
ALTER TABLE `bus_maintenance`
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
-- Indexes for table `classasigntoteacher`
--
ALTER TABLE `classasigntoteacher`
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
-- Indexes for table `combination_subject`
--
ALTER TABLE `combination_subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_combination_subject_combination` (`subject_combination_id`),
  ADD KEY `fk_combination_subject_subject` (`subject_id`);

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
-- Indexes for table `employeeleaves`
--
ALTER TABLE `employeeleaves`
  ADD PRIMARY KEY (`Emp_ID`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`EmployeeID`);

--
-- Indexes for table `employee_thumb_attendance`
--
ALTER TABLE `employee_thumb_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_date` (`ess_emp_code`,`log_date`);

--
-- Indexes for table `exammasters`
--
ALTER TABLE `exammasters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `examtypes`
--
ALTER TABLE `examtypes`
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
-- Indexes for table `feesreceiptchallan_cancel`
--
ALTER TABLE `feesreceiptchallan_cancel`
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
-- Indexes for table `fee_exemption_master`
--
ALTER TABLE `fee_exemption_master`
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
-- Indexes for table `gps_data1`
--
ALTER TABLE `gps_data1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grademaster`
--
ALTER TABLE `grademaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groupmaster`
--
ALTER TABLE `groupmaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `headmaster`
--
ALTER TABLE `headmaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `holds_structure_row`
--
ALTER TABLE `holds_structure_row`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hrms_attendance_locks`
--
ALTER TABLE `hrms_attendance_locks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hrms_basic_deductions`
--
ALTER TABLE `hrms_basic_deductions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hrms_compoff_requests`
--
ALTER TABLE `hrms_compoff_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_leave_requests_employee_id_foreign` (`employee_id`),
  ADD KEY `hrms_leave_requests_leave_type_id_foreign` (`leave_type_id`),
  ADD KEY `hrms_leave_requests_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `hrms_departments`
--
ALTER TABLE `hrms_departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hrms_employees`
--
ALTER TABLE `hrms_employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employees_department_id_foreign` (`department_id`),
  ADD KEY `hrms_employees_shift_id_foreign` (`shift_id`),
  ADD KEY `hrms_employees_position_id_foreign` (`position_id`);

--
-- Indexes for table `hrms_employee_addresses`
--
ALTER TABLE `hrms_employee_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_addresses_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_attendance`
--
ALTER TABLE `hrms_employee_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_attendance_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_bank_details`
--
ALTER TABLE `hrms_employee_bank_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_bank_details_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_biometric_details`
--
ALTER TABLE `hrms_employee_biometric_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_biometric_details_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_childrens`
--
ALTER TABLE `hrms_employee_childrens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_childrens_employee_id_foreign` (`employee_id`),
  ADD KEY `hrms_employee_childrens_student_id_foreign` (`student_id`),
  ADD KEY `hrms_employee_childrens_employee_salary_id_foreign` (`employee_salary_id`);

--
-- Indexes for table `hrms_employee_deductions`
--
ALTER TABLE `hrms_employee_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_deductions_employee_id_foreign` (`employee_id`),
  ADD KEY `hrms_employee_deductions_basic_deduction_id_foreign` (`basic_deduction_id`),
  ADD KEY `hrms_employee_deductions_employee_salary_id_foreign` (`employee_salary_id`);

--
-- Indexes for table `hrms_employee_deduction_history`
--
ALTER TABLE `hrms_employee_deduction_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_deduction_history_employee_id_foreign` (`employee_id`),
  ADD KEY `hrms_employee_deduction_history_employee_salary_id_foreign` (`employee_salary_id`),
  ADD KEY `hrms_employee_deduction_history_basic_deduction_id_foreign` (`basic_deduction_id`);

--
-- Indexes for table `hrms_employee_documents`
--
ALTER TABLE `hrms_employee_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_document` (`employee_id`,`document_type`),
  ADD KEY `hrms_employee_documents_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_educations`
--
ALTER TABLE `hrms_employee_educations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_educations_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_emergency_contacts`
--
ALTER TABLE `hrms_employee_emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_emergency_contacts_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_experiences`
--
ALTER TABLE `hrms_employee_experiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_experiences_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_leave_balances`
--
ALTER TABLE `hrms_employee_leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_leave_balances_employee_id_foreign` (`employee_id`),
  ADD KEY `hrms_employee_leave_balances_leave_type_id_foreign` (`leave_type_id`);

--
-- Indexes for table `hrms_employee_loans`
--
ALTER TABLE `hrms_employee_loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_loans_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_loan_emis`
--
ALTER TABLE `hrms_employee_loan_emis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_loan_emis_loan_id_foreign` (`loan_id`);

--
-- Indexes for table `hrms_employee_salaries`
--
ALTER TABLE `hrms_employee_salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_salaries_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_security_deposits`
--
ALTER TABLE `hrms_employee_security_deposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `hrms_employee_security_deposit_emis`
--
ALTER TABLE `hrms_employee_security_deposit_emis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `security_deposit_id` (`security_deposit_id`);

--
-- Indexes for table `hrms_employee_statutory_info`
--
ALTER TABLE `hrms_employee_statutory_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_statutory_info_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_employee_student_fee_emis`
--
ALTER TABLE `hrms_employee_student_fee_emis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_employee_student_fee_emis_employee_salary_id_foreign` (`employee_salary_id`),
  ADD KEY `hrms_employee_student_fee_emis_student_id_foreign` (`student_id`),
  ADD KEY `hrms_employee_student_fee_emis_employee_child_id_foreign` (`employee_child_id`);

--
-- Indexes for table `hrms_holidays`
--
ALTER TABLE `hrms_holidays`
  ADD PRIMARY KEY (`HolidayID`);

--
-- Indexes for table `hrms_leave_requests`
--
ALTER TABLE `hrms_leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_leave_requests_employee_id_foreign` (`employee_id`),
  ADD KEY `hrms_leave_requests_leave_type_id_foreign` (`leave_type_id`),
  ADD KEY `hrms_leave_requests_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `hrms_leave_types`
--
ALTER TABLE `hrms_leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hrms_payrolls`
--
ALTER TABLE `hrms_payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_payrolls_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `hrms_positions`
--
ALTER TABLE `hrms_positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hrms_security_deposite_refunds`
--
ALTER TABLE `hrms_security_deposite_refunds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hrms_shifts`
--
ALTER TABLE `hrms_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_shifts_shift_type_id_foreign` (`shift_type_id`);

--
-- Indexes for table `hrms_shift_types`
--
ALTER TABLE `hrms_shift_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hrms_staff_leave_allocation`
--
ALTER TABLE `hrms_staff_leave_allocation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_staff_leave_allocation_hrms_staff_type_id_foreign` (`hrms_staff_type_id`),
  ADD KEY `hrms_staff_leave_allocation_leave_type_id_foreign` (`leave_type_id`);

--
-- Indexes for table `hrms_staff_type`
--
ALTER TABLE `hrms_staff_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hrms_staff_type_shift_type_id_foreign` (`shift_type_id`);

--
-- Indexes for table `initial_leave`
--
ALTER TABLE `initial_leave`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inquiry_registration`
--
ALTER TABLE `inquiry_registration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

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
-- Indexes for table `leaverequests`
--
ALTER TABLE `leaverequests`
  ADD PRIMARY KEY (`LeaveRequestID`);

--
-- Indexes for table `leave_allocation`
--
ALTER TABLE `leave_allocation`
  ADD PRIMARY KEY (`Leave_Allocation_ID`),
  ADD KEY `Staff_Type_ID` (`Staff_Type_ID`);

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
-- Indexes for table `marks`
--
ALTER TABLE `marks`
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
-- Indexes for table `presentlyschool`
--
ALTER TABLE `presentlyschool`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `previosly_saved_marks_entry`
--
ALTER TABLE `previosly_saved_marks_entry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `primarygroup`
--
ALTER TABLE `primarygroup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `remarksmaster`
--
ALTER TABLE `remarksmaster`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `salaries`
--
ALTER TABLE `salaries`
  ADD PRIMARY KEY (`SalaryID`);

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
-- Indexes for table `shift`
--
ALTER TABLE `shift`
  ADD PRIMARY KEY (`Shift_ID`),
  ADD KEY `Shift_Type_ID` (`Shift_Type_ID`);

--
-- Indexes for table `shift_type`
--
ALTER TABLE `shift_type`
  ADD PRIMARY KEY (`Shift_Type_ID`);

--
-- Indexes for table `staff_type`
--
ALTER TABLE `staff_type`
  ADD PRIMARY KEY (`Staff_Type_ID`);

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
-- Indexes for table `student_bus_fees`
--
ALTER TABLE `student_bus_fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_bus_fees_balance`
--
ALTER TABLE `student_bus_fees_balance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_fees_data`
--
ALTER TABLE `student_fees_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_fees_exempted`
--
ALTER TABLE `student_fees_exempted`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `scholar_no` (`scholar_no`);

--
-- Indexes for table `student_fees_receipt_breakdown`
--
ALTER TABLE `student_fees_receipt_breakdown`
  ADD PRIMARY KEY (`id`),
  ADD KEY `challan_id` (`challan_id`);

--
-- Indexes for table `student_fees_receipt_challan`
--
ALTER TABLE `student_fees_receipt_challan`
  ADD PRIMARY KEY (`challan_id`);

--
-- Indexes for table `student_fee_balances`
--
ALTER TABLE `student_fee_balances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_fee_exemption`
--
ALTER TABLE `student_fee_exemption`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_online_fee_payments`
--
ALTER TABLE `student_online_fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_student_id` (`student_id`);

--
-- Indexes for table `student_registration`
--
ALTER TABLE `student_registration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scholar_no` (`scholar_no`);

--
-- Indexes for table `student_registration_bk`
--
ALTER TABLE `student_registration_bk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subhead`
--
ALTER TABLE `subhead`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subheads`
--
ALTER TABLE `subheads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjectmaster`
--
ALTER TABLE `subjectmaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subject_assign_student`
--
ALTER TABLE `subject_assign_student`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subject_combinations`
--
ALTER TABLE `subject_combinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacherbusassign`
--
ALTER TABLE `teacherbusassign`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
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
-- Indexes for table `totalnextyear`
--
ALTER TABLE `totalnextyear`
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
-- AUTO_INCREMENT for table `abvance_nextyear_fees`
--
ALTER TABLE `abvance_nextyear_fees`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `academic_class_subject`
--
ALTER TABLE `academic_class_subject`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `academic_exam_subject_marks`
--
ALTER TABLE `academic_exam_subject_marks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `academic_student_daily_attendance`
--
ALTER TABLE `academic_student_daily_attendance`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_annual_defaulter_student`
--
ALTER TABLE `account_annual_defaulter_student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `areamaster`
--
ALTER TABLE `areamaster`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `AttendanceID` bigint(20) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `bus_maintenance`
--
ALTER TABLE `bus_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `classasigntoteacher`
--
ALTER TABLE `classasigntoteacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `combination_subject`
--
ALTER TABLE `combination_subject`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `employeeleaves`
--
ALTER TABLE `employeeleaves`
  MODIFY `Emp_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `EmployeeID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_thumb_attendance`
--
ALTER TABLE `employee_thumb_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exammasters`
--
ALTER TABLE `exammasters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `examtypes`
--
ALTER TABLE `examtypes`
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
-- AUTO_INCREMENT for table `feesreceiptchallan_cancel`
--
ALTER TABLE `feesreceiptchallan_cancel`
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
-- AUTO_INCREMENT for table `fee_exemption_master`
--
ALTER TABLE `fee_exemption_master`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `gps_data1`
--
ALTER TABLE `gps_data1`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grademaster`
--
ALTER TABLE `grademaster`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groupmaster`
--
ALTER TABLE `groupmaster`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `headmaster`
--
ALTER TABLE `headmaster`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holds_structure_row`
--
ALTER TABLE `holds_structure_row`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_attendance_locks`
--
ALTER TABLE `hrms_attendance_locks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_basic_deductions`
--
ALTER TABLE `hrms_basic_deductions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_compoff_requests`
--
ALTER TABLE `hrms_compoff_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_departments`
--
ALTER TABLE `hrms_departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employees`
--
ALTER TABLE `hrms_employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_addresses`
--
ALTER TABLE `hrms_employee_addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_attendance`
--
ALTER TABLE `hrms_employee_attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_bank_details`
--
ALTER TABLE `hrms_employee_bank_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_biometric_details`
--
ALTER TABLE `hrms_employee_biometric_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_childrens`
--
ALTER TABLE `hrms_employee_childrens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_deductions`
--
ALTER TABLE `hrms_employee_deductions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_deduction_history`
--
ALTER TABLE `hrms_employee_deduction_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_documents`
--
ALTER TABLE `hrms_employee_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_educations`
--
ALTER TABLE `hrms_employee_educations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_emergency_contacts`
--
ALTER TABLE `hrms_employee_emergency_contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_experiences`
--
ALTER TABLE `hrms_employee_experiences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_leave_balances`
--
ALTER TABLE `hrms_employee_leave_balances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_loans`
--
ALTER TABLE `hrms_employee_loans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_loan_emis`
--
ALTER TABLE `hrms_employee_loan_emis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_salaries`
--
ALTER TABLE `hrms_employee_salaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_security_deposits`
--
ALTER TABLE `hrms_employee_security_deposits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_security_deposit_emis`
--
ALTER TABLE `hrms_employee_security_deposit_emis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_statutory_info`
--
ALTER TABLE `hrms_employee_statutory_info`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_employee_student_fee_emis`
--
ALTER TABLE `hrms_employee_student_fee_emis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_holidays`
--
ALTER TABLE `hrms_holidays`
  MODIFY `HolidayID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_leave_requests`
--
ALTER TABLE `hrms_leave_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_leave_types`
--
ALTER TABLE `hrms_leave_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_payrolls`
--
ALTER TABLE `hrms_payrolls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_positions`
--
ALTER TABLE `hrms_positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_security_deposite_refunds`
--
ALTER TABLE `hrms_security_deposite_refunds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_shifts`
--
ALTER TABLE `hrms_shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_shift_types`
--
ALTER TABLE `hrms_shift_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_staff_leave_allocation`
--
ALTER TABLE `hrms_staff_leave_allocation`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrms_staff_type`
--
ALTER TABLE `hrms_staff_type`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `initial_leave`
--
ALTER TABLE `initial_leave`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inquiry_registration`
--
ALTER TABLE `inquiry_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'uniqe_id';

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `late_fees_master`
--
ALTER TABLE `late_fees_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaverequests`
--
ALTER TABLE `leaverequests`
  MODIFY `LeaveRequestID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_allocation`
--
ALTER TABLE `leave_allocation`
  MODIFY `Leave_Allocation_ID` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `presentlyschool`
--
ALTER TABLE `presentlyschool`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `previosly_saved_marks_entry`
--
ALTER TABLE `previosly_saved_marks_entry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `primarygroup`
--
ALTER TABLE `primarygroup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remarksmaster`
--
ALTER TABLE `remarksmaster`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `SalaryID` bigint(20) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `shift`
--
ALTER TABLE `shift`
  MODIFY `Shift_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shift_type`
--
ALTER TABLE `shift_type`
  MODIFY `Shift_Type_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_type`
--
ALTER TABLE `staff_type`
  MODIFY `Staff_Type_ID` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `student_bus_fees`
--
ALTER TABLE `student_bus_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_bus_fees_balance`
--
ALTER TABLE `student_bus_fees_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_fees_data`
--
ALTER TABLE `student_fees_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_fees_exempted`
--
ALTER TABLE `student_fees_exempted`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_fees_receipt_breakdown`
--
ALTER TABLE `student_fees_receipt_breakdown`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_fees_receipt_challan`
--
ALTER TABLE `student_fees_receipt_challan`
  MODIFY `challan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_fee_balances`
--
ALTER TABLE `student_fee_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_fee_exemption`
--
ALTER TABLE `student_fee_exemption`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_online_fee_payments`
--
ALTER TABLE `student_online_fee_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_registration`
--
ALTER TABLE `student_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'uniqe_id';

--
-- AUTO_INCREMENT for table `student_registration_bk`
--
ALTER TABLE `student_registration_bk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'uniqe_id';

--
-- AUTO_INCREMENT for table `subhead`
--
ALTER TABLE `subhead`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subheads`
--
ALTER TABLE `subheads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjectmaster`
--
ALTER TABLE `subjectmaster`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subject_assign_student`
--
ALTER TABLE `subject_assign_student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subject_combinations`
--
ALTER TABLE `subject_combinations`
  MODIFY `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `totalnextyear`
--
ALTER TABLE `totalnextyear`
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
-- Constraints for table `academic_class_subject`
--
ALTER TABLE `academic_class_subject`
  ADD CONSTRAINT `fk_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjectmaster` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `account_annual_defaulter_student`
--
ALTER TABLE `account_annual_defaulter_student`
  ADD CONSTRAINT `account_annual_defaulter_student_ibfk_1` FOREIGN KEY (`scholar_no`) REFERENCES `student_registration` (`scholar_no`) ON DELETE CASCADE;

--
-- Constraints for table `combination_subject`
--
ALTER TABLE `combination_subject`
  ADD CONSTRAINT `fk_combination_subject_combination` FOREIGN KEY (`subject_combination_id`) REFERENCES `subject_combinations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_combination_subject_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjectmaster` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hrms_employees`
--
ALTER TABLE `hrms_employees`
  ADD CONSTRAINT `hrms_employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `hrms_departments` (`id`),
  ADD CONSTRAINT `hrms_employees_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `hrms_positions` (`id`),
  ADD CONSTRAINT `hrms_employees_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `hrms_shifts` (`id`);

--
-- Constraints for table `hrms_employee_addresses`
--
ALTER TABLE `hrms_employee_addresses`
  ADD CONSTRAINT `hrms_employee_addresses_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_employee_bank_details`
--
ALTER TABLE `hrms_employee_bank_details`
  ADD CONSTRAINT `hrms_employee_bank_details_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_employee_biometric_details`
--
ALTER TABLE `hrms_employee_biometric_details`
  ADD CONSTRAINT `hrms_employee_biometric_details_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_employee_childrens`
--
ALTER TABLE `hrms_employee_childrens`
  ADD CONSTRAINT `hrms_employee_childrens_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`),
  ADD CONSTRAINT `hrms_employee_childrens_employee_salary_id_foreign` FOREIGN KEY (`employee_salary_id`) REFERENCES `hrms_employee_salaries` (`id`),
  ADD CONSTRAINT `hrms_employee_childrens_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student_registration_bk` (`id`);

--
-- Constraints for table `hrms_employee_deductions`
--
ALTER TABLE `hrms_employee_deductions`
  ADD CONSTRAINT `hrms_employee_deductions_basic_deduction_id_foreign` FOREIGN KEY (`basic_deduction_id`) REFERENCES `hrms_basic_deductions` (`id`),
  ADD CONSTRAINT `hrms_employee_deductions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`),
  ADD CONSTRAINT `hrms_employee_deductions_employee_salary_id_foreign` FOREIGN KEY (`employee_salary_id`) REFERENCES `hrms_employee_salaries` (`id`);

--
-- Constraints for table `hrms_employee_documents`
--
ALTER TABLE `hrms_employee_documents`
  ADD CONSTRAINT `hrms_employee_documents_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_employee_educations`
--
ALTER TABLE `hrms_employee_educations`
  ADD CONSTRAINT `hrms_employee_educations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_employee_emergency_contacts`
--
ALTER TABLE `hrms_employee_emergency_contacts`
  ADD CONSTRAINT `hrms_employee_emergency_contacts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_employee_experiences`
--
ALTER TABLE `hrms_employee_experiences`
  ADD CONSTRAINT `hrms_employee_experiences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_employee_leave_balances`
--
ALTER TABLE `hrms_employee_leave_balances`
  ADD CONSTRAINT `hrms_employee_leave_balances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`),
  ADD CONSTRAINT `hrms_employee_leave_balances_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `hrms_leave_types` (`id`);

--
-- Constraints for table `hrms_employee_loans`
--
ALTER TABLE `hrms_employee_loans`
  ADD CONSTRAINT `hrms_employee_loans_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_employee_loan_emis`
--
ALTER TABLE `hrms_employee_loan_emis`
  ADD CONSTRAINT `hrms_employee_loan_emis_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `hrms_employee_loans` (`id`);

--
-- Constraints for table `hrms_employee_salaries`
--
ALTER TABLE `hrms_employee_salaries`
  ADD CONSTRAINT `hrms_employee_salaries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_employee_security_deposits`
--
ALTER TABLE `hrms_employee_security_deposits`
  ADD CONSTRAINT `hrms_employee_security_deposits_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hrms_employee_security_deposit_emis`
--
ALTER TABLE `hrms_employee_security_deposit_emis`
  ADD CONSTRAINT `hrms_employee_security_deposit_emis_ibfk_1` FOREIGN KEY (`security_deposit_id`) REFERENCES `hrms_employee_security_deposits` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hrms_employee_statutory_info`
--
ALTER TABLE `hrms_employee_statutory_info`
  ADD CONSTRAINT `hrms_employee_statutory_info_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_employee_student_fee_emis`
--
ALTER TABLE `hrms_employee_student_fee_emis`
  ADD CONSTRAINT `hrms_employee_student_fee_emis_employee_child_id_foreign` FOREIGN KEY (`employee_child_id`) REFERENCES `hrms_employee_childrens` (`id`),
  ADD CONSTRAINT `hrms_employee_student_fee_emis_employee_salary_id_foreign` FOREIGN KEY (`employee_salary_id`) REFERENCES `hrms_employee_salaries` (`id`),
  ADD CONSTRAINT `hrms_employee_student_fee_emis_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student_registration_bk` (`id`);

--
-- Constraints for table `hrms_leave_requests`
--
ALTER TABLE `hrms_leave_requests`
  ADD CONSTRAINT `hrms_leave_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `hrms_employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `hrms_leave_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`),
  ADD CONSTRAINT `hrms_leave_requests_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `hrms_leave_types` (`id`);

--
-- Constraints for table `hrms_payrolls`
--
ALTER TABLE `hrms_payrolls`
  ADD CONSTRAINT `hrms_payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `hrms_employees` (`id`);

--
-- Constraints for table `hrms_shifts`
--
ALTER TABLE `hrms_shifts`
  ADD CONSTRAINT `hrms_shifts_shift_type_id_foreign` FOREIGN KEY (`shift_type_id`) REFERENCES `hrms_shift_types` (`id`);

--
-- Constraints for table `hrms_staff_leave_allocation`
--
ALTER TABLE `hrms_staff_leave_allocation`
  ADD CONSTRAINT `hrms_staff_leave_allocation_hrms_staff_type_id_foreign` FOREIGN KEY (`hrms_staff_type_id`) REFERENCES `hrms_staff_type` (`id`),
  ADD CONSTRAINT `hrms_staff_leave_allocation_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `hrms_leave_types` (`id`);

--
-- Constraints for table `hrms_staff_type`
--
ALTER TABLE `hrms_staff_type`
  ADD CONSTRAINT `hrms_staff_type_shift_type_id_foreign` FOREIGN KEY (`shift_type_id`) REFERENCES `hrms_shift_types` (`id`);

--
-- Constraints for table `student_fees_receipt_breakdown`
--
ALTER TABLE `student_fees_receipt_breakdown`
  ADD CONSTRAINT `student_fees_receipt_breakdown_ibfk_1` FOREIGN KEY (`challan_id`) REFERENCES `student_fees_receipt_challan` (`challan_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
