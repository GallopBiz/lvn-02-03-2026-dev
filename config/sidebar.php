<?php
return [
    [
        'title' => 'Dashboard',
        'route' => 'admin-dashboard',
        'icon' => 'i-Bar-Chart',
        'permission' => 'dashboard',
        'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'],
    ],
	[
        'title' => 'Scholars',
        'icon' => 'i-Student-Hat-2',
        'permission' => 'scholars',
        'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'],
        'children' => [
            [
                'title' => 'Pre-Admission',
                'icon' => 'i-File-Clipboard-Text--Image',
                'permission' => 'scholars',
                'children' => [
                    [ 'title' => 'Pre-Enquiry Entry', 'route' => 'admin-preenquiryform', 'permission' => 'scholars', 'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'] ],
                    [ 'title' => 'Pre-Enquiry List', 'route' => 'admin-pre-enquiryform', 'permission' => 'scholars', 'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'] ],
                    [ 'title' => 'Enquiry Entry', 'route' => 'admin-enquiryform', 'permission' => 'scholars', 'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'] ],
                    [ 'title' => 'Enquiry List', 'route' => 'adminenquirylist', 'permission' => 'scholars', 'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'] ],
                    [ 'title' => 'Follow-Up Scheduling', 'route' => 'followupdate', 'permission' => 'scholars', 'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'] ],
                    [ 'title' => 'Selection Process', 'route' => 'selection-process', 'permission' => 'scholars', 'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'] ],
                ],
            ],
            [
                'title' => 'Admission & Registration',
                'icon' => 'i-Add-User',
                'permission' => 'scholars',
                'children' => [
                    [ 'title' => 'Student Registration', 'route' => 'add-student-registrations', 'permission' => 'scholars', 'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'] ],
                    [ 'title' => 'Registration List', 'route' => 'student-registrations', 'permission' => 'scholars', 'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'] ],
                ],
            ],
            [
                'title' => 'Certificates',
                'icon' => 'i-Certificate',
                'children' => [
                    [ 'title' => 'Bonafide Certificate', 'route' => 'bonafide-certificate', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Reports',
                'icon' => 'i-Bar-Chart',
                'children' => [
                    [ 'title' => 'Inquiry Reports', 'route' => 'inquiry-report', 'roles' => ['Admin'] ],
                    [ 'title' => 'Form Fee Reports', 'route' => 'admin-pre-enquiryfeeslist', 'roles' => ['Admin'] ],
                    [ 'title' => 'Admission Fee Reports', 'route' => 'duestuamount', 'roles' => ['Admin'] ],
                ],
            ],
        ],
    ],
    [
        'title' => 'Fees',
        'route' => 'fees',
        'icon' => 'i-Money-2',
        'permission' => 'fees',
        'roles' => ['Admin', 'Student', 'Academic'],
        'children' => [
            [
                'title' => 'Collection',
                'icon' => 'i-Money-Bag',
                'children' => [
                    [ 'title' => 'Fees Receipt Challan', 'route' => 'fees_receipt_challan', 'roles' => ['Admin'] ],
                    [ 'title' => 'Online Collection', 'route' => 'student-online-fees', 'roles' => ['Admin'] ],
                    [ 'title' => 'Daily Online Fees Reconciliation', 'route' => 'student-daily-online-fees-reconcile', 'roles' => ['Admin'] ],
                    [ 'title' => 'Daily Online Fees Settlements', 'route' => 'student-daily-online-fees-settlement', 'roles' => ['Admin'] ],
                    [ 'title' => 'Daily / Yearly Collection', 'route' => 'daily-collection', 'roles' => ['Admin'] ],
                    [ 'title' => 'Student Ledger', 'route' => 'student_ledger', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Fee Master',
                'icon' => 'i-Receipt-4',
                'children' => [
                    [ 'title' => 'Student Fees Master', 'route' => 'fees-master-student', 'roles' => ['Admin'] ],
                    [ 'title' => 'Fees Type Master', 'route' => 'fees-types-master', 'roles' => ['Admin'] ],
                    [ 'title' => 'Bus Fees Master', 'route' => 'bus-fees-master', 'roles' => ['Admin'] ],
                    [ 'title' => 'Late Fees Master', 'route' => 'late-fees-master', 'roles' => ['Admin'] ],
                    [ 'title' => 'Advance Next Year Fees', 'route' => 'advance-next-year-fees', 'roles' => ['Admin'] ],
                    [ 'title' => 'Course Fees Head Order', 'route' => 'course-fees-head-orders', 'roles' => ['Admin'] ],
                    [ 'title' => 'Generate Fees Due Chart', 'route' => 'generate-due-chart', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Structure',
                'icon' => 'i-Checked-User',
                'children' => [
                    [ 'title' => 'Create Course Fees Structure', 'route' => 'create-course-fees-structure-master', 'roles' => ['Admin'] ],
                    [ 'title' => 'Course Fees Structure List', 'route' => 'course-fees-structure-master-list', 'roles' => ['Admin'] ],
                    [ 'title' => 'Terms Master', 'route' => 'terms', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Reports',
                'icon' => 'i-Bar-Chart',
                'children' => [
                    [ 'title' => 'Defaulters List', 'route' => 'defaulters', 'roles' => ['Admin'] ],
                    [ 'title' => 'Exemption Report', 'route' => 'exemptions', 'roles' => ['Admin'] ],
                ],
            ],
        ],
    ],
    [
        'title' => 'Transport',
        'route' => 'transport',
        'icon' => 'i-Jeep',
        'permission' => 'transport',
        'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'],
        'children' => [
            [
                'title' => 'Configuration',
                'icon' => 'i-Settings',
                'children' => [
                    [ 'title' => 'Route List', 'route' => 'route-vehicle-map', 'roles' => ['Admin'] ],
                    [ 'title' => 'Vehicle', 'route' => 'addvehical', 'roles' => ['Admin'] ],
                    [ 'title' => 'Area Master', 'route' => 'area-master', 'roles' => ['Admin'] ],
                    [ 'title' => 'Bus Stops', 'route' => 'bus-stop', 'roles' => ['Admin'] ],
                    [ 'title' => 'Driver / Conductor Master', 'route' => 'driver-conductor-master', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Maintenance',
                'icon' => 'i-Wrench',
                'children' => [
                    [ 'title' => 'Maintenance Head Master', 'route' => 'maintenance-head-master', 'roles' => ['Admin'] ],
                    [ 'title' => 'Vehicle Maintenance', 'route' => 'bus-maintenance-entry', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Schedule',
                'icon' => 'i-Clock',
                'children' => [
                    [ 'title' => 'Route Master', 'route' => 'route-master', 'roles' => ['Admin'] ],
                    [ 'title' => 'Schedule Master', 'route' => 'schedulemaster', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Assignments',
                'icon' => 'i-Checked-User',
                'children' => [
                    [ 'title' => 'Scholar Bus Assign', 'route' => 'scholarbusassign', 'roles' => ['Admin'] ],
                    [ 'title' => 'Teacher Bus Assign', 'route' => 'teacherbusassign', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Bus Live Location',
                'icon' => 'i-Bus',
                'children' => [
                    [ 'title' => 'Bus Data', 'route' => 'bus_data', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Route List',
                'icon' => 'i-File-Clipboard-Text--Image',
                'route' => 'route-vehicle-map',
                'roles' => ['Student']
            ],
        ],
    ],
    [
        'title' => 'Academic',
        'route' => 'Academic',
        'icon' => 'i-Book',
        'permission' => 'academic',
        'roles' => ['Admin', 'Academic Staff (Teacher)'],
        'children' => [
            [
                'title' => 'Session & Class Setup',
                'icon' => 'i-Calendar-4',
                'children' => [
                    [ 'title' => 'Make Session', 'route' => 'session', 'roles' => ['Admin'] ],
                    [ 'title' => 'Classes', 'route' => 'classes', 'roles' => ['Admin'] ],
                    [ 'title' => 'Stream Master', 'route' => 'streammaster', 'roles' => ['Admin'] ],
                    [ 'title' => 'Student Transfer', 'route' => 'student-transfer', 'roles' => ['Admin'] ],
                    [ 'title' => 'Assign Section to Student', 'route' => 'sectionAssign', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Subjects & Teachers',
                'icon' => 'i-Book',
                'children' => [
                    [ 'title' => 'Subject Master', 'route' => 'subjectmaster', 'roles' => ['Admin'] ],
                    [ 'title' => 'Subject Combination', 'route' => 'subjectcombinatiomaster', 'roles' => ['Admin'] ],
                    [ 'title' => 'Assign Subject to Student', 'route' => 'AssignSubject', 'roles' => ['Admin'] ],
                    // [ 'title' => 'Teachers', 'route' => 'teachers', 'roles' => ['Admin'] ],
                    [ 'title' => 'Teacher Subject Mapping', 'route' => 'teachersubject', 'roles' => ['Admin'] ],
                    [ 'title' => 'Assign Class to Teacher', 'route' => 'calssese-assigne-to-teacher', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Examination & Grading',
                'icon' => 'i-Check',
                'children' => [
                    [ 'title' => 'Exam Type', 'route' => 'examtype', 'roles' => ['Admin'] ],
                    // [ 'title' => 'Exam Master', 'route' => 'exammaster', 'roles' => ['Admin'] ],
                    [ 'title' => 'Exam Setup', 'route' => 'academic/exams/create', 'roles' => ['Admin'] ],
                    [ 'title' => 'Internal Assessment (IA)', 'route' => 'internal-assessment-master', 'roles' => ['Admin'] ],
                    [ 'title' => 'Roll No & Admit Card', 'route' => 'academic/roll-no-tools', 'roles' => ['Admin'] ],
                    [ 'title' => 'Enter Marks', 'route' => 'marks', 'staff_route' => 'staff/marks', 'roles' => ['Admin', 'Academic Staff (Teacher)', 'Staff'] ],
                    [ 'title' => 'Teacher Remark Entry', 'route' => 'teacher-remark-entry', 'staff_route' => 'staff/teacher-remark-entry', 'roles' => ['Admin', 'Academic Staff (Teacher)', 'Staff'] ],
                    [ 'title' => 'Report Marks', 'route' => 'show_report_marks', 'roles' => ['Admin'] ],
                    [ 'title' => 'Marksheet', 'route' => 'marksheet', 'roles' => ['Admin'] ],
                    [ 'title' => 'Consolidated Marksheet', 'route' => 'academic/consolidated-marksheets', 'roles' => ['Admin'] ],
                    [ 'title' => 'Grading Master', 'route' => 'greadingmaster', 'roles' => ['Admin'] ],
                    [ 'title' => 'Grades', 'route' => 'gread', 'roles' => ['Admin'] ],
                    [ 'title' => 'Remark Master', 'route' => 'remarkmaster', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Attendance',
                'icon' => 'i-Checked-User',
                'children' => [
                    [ 'title' => 'Daily Attendance', 'route' => 'dailyattandence', 'roles' => ['Admin', 'Academic Staff (Teacher)', 'Staff'] ],
                    [ 'title' => 'Student Collective Attendance', 'route' => 'academic/attendance-collective', 'roles' => ['Admin', 'Academic Staff (Teacher)', 'Staff'] ],
                    [ 'title' => 'Student-wise Attendance', 'route' => 'student-attandence-report', 'roles' => ['Admin'] ],
                    [ 'title' => 'Attendance Reports', 'route' => 'Attandencereports', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Heads & Groups',
                'icon' => 'i-Structure',
                'children' => [
                    [ 'title' => 'Primary Group', 'route' => 'primarygroup', 'roles' => ['Admin'] ],
                    [ 'title' => 'Group', 'route' => 'groupmaster', 'roles' => ['Admin'] ],
                    [ 'title' => 'Head', 'route' => 'headmaster', 'roles' => ['Admin'] ],
                    [ 'title' => 'Sub Head', 'route' => 'subheadmaster', 'roles' => ['Admin'] ],
                ],
            ],
        ],
    ],
    [
        'title' => 'HRMS',
        'route' => 'hrms',
        'icon' => 'i-Add-UserStar',
        'permission' => 'hrms',
        'roles' => ['Admin', 'Academic Staff (Teacher)'],
        'children' => [
            [
                'title' => 'Employee Management',
                'icon' => 'i-Add-User',
                'children' => [
                    [ 'title' => 'Employees', 'route' => 'employee', 'roles' => ['Admin'] ],
                    [ 'title' => 'Departments', 'route' => 'department', 'roles' => ['Admin'] ],
                    [ 'title' => 'Position', 'route' => 'position', 'roles' => ['Admin'] ],
                    [ 'title' => 'Staff Type', 'route' => 'stafftype', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Shift Management',
                'icon' => 'i-Clock-Back',
                'children' => [
                    [ 'title' => 'Shift Type', 'route' => 'shifttype', 'roles' => ['Admin'] ],
                    [ 'title' => 'Create Shift', 'route' => 'shifts', 'roles' => ['Admin'] ],
                    [ 'title' => 'Shift History', 'route' => 'shifts-history', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Attendance',
                'icon' => 'i-Checked-User',
                'children' => [
                    [ 'title' => 'Biometric Attendance', 'route' => 'employeeattendance', 'roles' => ['Admin'] ],
                    [ 'title' => 'Manual Attendance', 'route' => 'manual-attendance', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Leave Management',
                'icon' => 'i-Calendar',
                'children' => [
                    [ 'title' => 'Leave Type Master', 'route' => 'leave_Types', 'roles' => ['Admin'] ],
                    [ 'title' => 'Leave Staff Allocation', 'route' => 'leave_staff_allocation', 'roles' => ['Admin'] ],
                    [ 'title' => 'Employee Leave Balance', 'route' => 'employee-leave-balances', 'roles' => ['Admin'] ],
                    [ 'title' => 'Leave Requests', 'route' => 'leaverequests', 'roles' => ['Admin'] ],
                    [ 'title' => 'Admin Leave Requests', 'route' => 'employeeleaves', 'roles' => ['Admin'] ],
                    [ 'title' => 'Employee Wise Leave Report', 'route' => 'employee-leave-report', 'roles' => ['Admin'] ],
                    // Staff menu item
                    [ 'title' => 'My Leave Requests', 'route' => 'leaverequests-staff', 'roles' => ['Academic Staff (Teacher)', 'Staff'] ],
                ],
            ],
            [
                'title' => 'Comp Off',
                'icon' => 'i-Add-File',
                'children' => [
                    [ 'title' => 'Comp Off Requests', 'route' => 'compoff', 'roles' => ['Admin'] ],
                    [ 'title' => 'Admin Comp Off Requests', 'route' => 'employee-compoff-requests', 'roles' => ['Admin'] ],
                    [ 'title' => 'My Comp Off Requests', 'route' => 'compoffrequests-staff', 'roles' => ['Academic Staff (Teacher)', 'Staff'] ],
                ],
            ],
            [
                'title' => 'Manage Security Deposit',
                'icon' => 'i-Money-2',
                'children' => [
                    [ 'title' => 'Manage Security Deposit', 'route' => 'employeeloan', 'roles' => ['Admin'] ],
                    [ 'title' => 'Refund Security Deposit', 'route' => 'security-deposit-refund', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Payroll',
                'icon' => 'i-Money-2',
                'children' => [
                    [ 'title' => 'Manage Deductions', 'route' => 'basicDeduction', 'roles' => ['Admin'] ],
                    [ 'title' => 'Manage Security Deposit', 'route' => 'employeeloan', 'roles' => ['Admin'] ],
                    [ 'title' => 'Refund Security Deposit', 'route' => 'security-deposit-refund', 'roles' => ['Admin'] ],
                    [ 'title' => 'Salaries', 'route' => 'salaries', 'roles' => ['Admin'] ],
                    [ 'title' => 'Generate Payroll', 'route' => 'payroll', 'roles' => ['Admin'] ],
                    [ 'title' => 'Salary Report', 'route' => 'employee-salary-report', 'roles' => ['Admin'] ],
                ],
            ],
            [
                'title' => 'Holidays',
                'icon' => 'i-Calendar-3',
                'route' => 'holidays',
                'roles' => ['Admin']
            ],
            [
                'title' => 'Reports',
                'icon' => 'i-Money-2',
                'children' => [
                    [ 'title' => 'Employee Deduction Overview', 'route' => 'apply-report', 'roles' => ['Admin'] ],
                    [ 'title' => 'Employee Wise Deductions Report', 'route' => 'employee-deduction-report', 'roles' => ['Admin'] ],
                    [ 'title' => 'Employee Wise Leave Report', 'route' => 'employee-leave-report', 'roles' => ['Admin'] ],
                    [ 'title' => 'Date Wise Employee Leave Report', 'route' => 'employee-leave-report-date-wise', 'roles' => ['Admin'] ],
                ],
            ],
        ],
    ],
    [
        'title' => 'TC Module',
        'route' => 'transfer-certificate',
        'icon' => 'i-File-Clipboard-Text--Image',
        'permission' => 'tc-module',
        'roles' => ['Admin'],
        'children' => [
            [
                'title' => 'Transfer Certificate',
                'icon' => 'i-Certificate',
                'children' => [
                    [ 'title' => 'Generate T.C.', 'route' => 'transfer-certificate/create', 'roles' => ['Admin'] ],
                    [ 'title' => 'T.C. Reports', 'route' => 'transfer-certificate', 'roles' => ['Admin'] ],
                ],
            ],
        ],
    ],
    [
        'title' => 'Setting',
        'route' => 'setting',
        'icon' => 'i-Gear',
        'permission' => 'setting',
        'roles' => ['Admin', 'Academic Staff (Teacher)'],
        'children' => [
            [ 'title' => 'Users', 'route' => 'users', 'roles' => ['Admin'] ],
            [ 'title' => 'Roles', 'route' => 'roles', 'roles' => ['Admin'] ],
            [ 'title' => 'Permission', 'route' => 'permission', 'roles' => ['Admin'] ],
            [ 'title' => 'My profile', 'route' => 'staff/change-password', 'roles' => ['Admin'] ],
        ],
    ],
];
