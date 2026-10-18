# School ERP: New Academic Session Database Migration & Dependency Report

**Document Title:** New Academic Session Database Architecture, Table Dependencies, and Data Migration Manual  
**Target System:** Multi-Database CBSE School Management System (LVN)  
**Reference Databases:** `2025_2026` (Source Session) -> `2026_2027` (New Session)  
**Total Tables Analyzed:** 169 Tables  
**Saved Location:** `NEW_SESSION_DATABASE_MIGRATION_REPORT.md`

---

## 1. Executive Summary & Architecture Overview

The LVN School Management System utilizes a **Database-Per-Session (Year-Wise Database)** architecture. Each academic year is hosted in an isolated MySQL database schema (e.g., `2024_2025`, `2025_2026`, `2026_2027`).

```
                              +----------------------------------+
                              |     School ERP Application       |
                              +-----------------+----------------+
                                                | Dynamic Reconnection
                                                v
                     +--------------------------+--------------------------+
                     |                                                     |
                     v                                                     v
        +-------------------------+                           +-------------------------+
        |  Database: `2025_2026`  |                           |  Database: `2026_2027`  |
        |    (Current Session)    |                           |      (New Session)      |
        +------------+------------+                           +------------^------------+
                     |                                                     |
                     +--------------- [Promotion / Migration] -------------+
                                      - Active Students Promoted
                                      - Previous Year Dues Carried
                                      - Transactions Reset to Zero
```

### Key Technical Characteristics:
1. **Dynamic Connection Binding:**
   The application switches databases at runtime by modifying `database.connections.dynamic` and re-calling `DB::reconnect('dynamic')` using the session name from `$_COOKIE['selectedYear']`, `session('selectedYear')`, or `$request->session_name`.
2. **Inter-Session Student Promotion:**
   The `StudentTransferController` relies on two simultaneous connections: `dynamic` (the source session database) and `next_session_db` (the target new session database).
3. **Database Independence:**
   Because all queries run against the active session database, **the new database must contain the full schema definition (DDL)** for all tables, but with selective data retention.

---

## 2. Dependency Hierarchy (What Tables Must Exist First)

Before any administrative, academic, or accounting tasks can be performed in the new session, tables must be populated in the following strict dependency order:

| Sequence | Layer | Required Tables | Failure Risk if Missing |
| :---: | :--- | :--- | :--- |
| **Step 1** | **System & Access** | `users`, `roles`, `permissions`, `model_has_roles`, `role_has_permissions`, `role_menus` | Admin/Staff cannot log in or switch to the session; HTTP 500 error on authentication. |
| **Step 2** | **Academic Master** | `class_name`, `classes`, `sections`, `streams`, `subjectmaster`, `academic_class_subject`, `caste_name`, `states`, `cities` | Dropdowns fail to load; student admission and transfer forms crash. |
| **Step 3** | **Staff & HRMS** | `hrms_departments`, `hrms_positions`, `hrms_shifts`, `hrms_employees`, `teachers`, `hrms_employee_salaries` | Cannot assign class teachers; biometric sync fails; teacher dashboard inaccessible. |
| **Step 4** | **Fee Setup** | `fees_types_master`, `course_fees_head_master`, `course_fees_structure_master`, `schedulemaster`, `late_fees_master` | Cannot generate fee due charts; cashier cannot create fee installment records. |
| **Step 5** | **Transport Setup** | `vehicel`, `busstop`, `routemaster`, `routeareabusmaster`, `busfees`, `busstaff` | Cannot allocate students to bus routes or compute transport fees. |
| **Step 6** | **Student Master** | `student_registration` | Central dependency: promotion fails; fees, attendance, roll numbers cannot link. |

---

## 3. Data Classification: Copy As-Is vs. Promote vs. Fresh

All 169 tables in the schema fall into one of four distinct operational categories:

---

### Category A: Tables to COPY AS-IT-IS (Master / Static Configuration)
These tables contain structural definitions, profiles, and configuration settings that remain valid from session to session. Copy both **schema and data**.

#### 1. System, Users & Access Control
- `users`: Administrator and staff login accounts.
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `role_menus`: Access control and menu permissions.

#### 2. Academic Core Configurations
- `class_name`, `classes`: List of school classes (Nursery, KG1, KG2, 01 through 12).
- `sections`: Class sections (e.g., Aryabhatta, Ramanujan, etc.).
- `streams`: Senior secondary streams (Science, Commerce, Arts/Humanities).
- `subjectmaster`: All subjects taught across classes.
- `subject_combinations`, `combination_subject`, `academic_class_subject`: Subject packages and class-subject mappings.
- `grademaster`, `grades`: Grading scales (e.g., A1, A2, B1 for CBSE).
- `terms`: Examination terms (Term 1, Term 2 / Half-Yearly, Annual).
- `academic_remarksmaster`: Pre-configured teacher report card remarks.

#### 3. Standard Lookup Lists & Geography
- `caste_name`: Caste categories (General, OBC, SC, ST) used for admissions and RTE.
- `states`, `cities`: Geographic reference tables.
- `presentlyschool`: School entity and affiliation metadata.

#### 4. Transport Fleet & Route Configurations
- `vehicel`: School vehicle registry (buses, vans, registration numbers).
- `busstop`: Stoppage points with landmarks.
- `routemaster`, `areamaster`, `routeareabusmaster`: Bus routes and area coverage links.
- `busfees`: Standard distance/slab bus fee charges.
- `busstaff`: Drivers and conductors.
- `fuel_stations`: Authorized petrol/diesel pumps.
- `rtopaper`: Vehicle insurance, permit, and fitness documentation tracking.

#### 5. HRMS & Staff Master Records
- `hrms_departments`, `departments`: Department master (Teaching, Admin, Transport, Accounts).
- `hrms_positions`, `positions`: Designations (Principal, PGT, TGT, PRT, Clerk).
- `hrms_shifts`, `hrms_shift_types`, `shift`, `shift_type`: Attendance shift timings.
- `hrms_staff_type`, `staff_type`: Employment types (Permanent, Probation, Contractual).
- `hrms_leave_types`: Configured leave categories (CL, SL, EL, Maternity Leave).
- `hrms_employees`, `teachers`: Active staff records.
- **Employee Profile Tables:**
  - `hrms_employee_addresses`
  - `hrms_employee_bank_details`
  - `hrms_employee_biometric_details`
  - `hrms_employee_documents`
  - `hrms_employee_educations`
  - `hrms_employee_emergency_contacts`
  - `hrms_employee_experiences`
  - `hrms_employee_statutory_info` (PF, ESI, UAN, PAN)
  - `hrms_employee_salaries` (Base salary packages)
- `hrms_payroll_attendance_configuration`, `hrms_payroll_department_attendance_configuration`, `hrms_payroll_staff_attendance_configuration`: Payroll calculation formulas.

#### 6. Accounting & Maintenance Masters
- `maintenancegroupmaster`, `maintenanceheadmaster`: Vehicle and building maintenance heads.
- `course_fees_head_master`: Fee heads (Tuition Fee, Lab Fee, Sports Fee, Exam Fee).
- `fee_exemption_master`: Exemption categories (Sibling discount, Staff ward, RTE).
- `late_fees_master`: Late fine calculation rules.
- `abvance_nextyear_fees`: Advance fee deposit master.

---

### Category B: Tables Requiring PROMOTION or SELECTIVE MIGRATION
These tables handle active entities that continue into the new year, but their **data values change** (e.g., student grade level, unpaid balances, loan schedules).

#### 1. Student Promotion (`student_registration`)
> **Important:** Never copy `student_registration` directly using a plain `INSERT INTO ... SELECT *` from the previous session. Students must undergo academic promotion to reflect their new class.

* **Class Promotion Rules:**
  - Nursery -> KG1
  - KG1 -> KG2
  - KG2 -> 01
  - 01 -> 02 ... -> 11 -> 12
  - **Class 12:** Must be **excluded** (graduated/passed out).
* **Transfer Certificate (TC) Exclusion:**
  - Students with `status = 't'` or `transfer_status = 'transferred'` or who have a record in `tc_certificates` must be **excluded**.
* **Detained / Repeating Students:**
  - Students flagged with `next_year == 1` in inquiry/exam evaluation remain in their existing class.
* **Metadata Update:**
  - `session_name` must be set to the new session (e.g., `'2026_2027'`).

#### 2. Previous Year Unpaid Fee Dues (`student_fee_balances`)
- Students with outstanding balances from the previous session should have their net balance carried forward into `student_fee_balances` under the new session database as **Opening Balance (Old Dues)**.
- Paid students start with zero balance.

#### 3. Active Staff Loans & Security Deposits
- `hrms_employee_loans` & `hrms_employee_loan_emis`:
  - **Completed Loans:** Discard (do not migrate).
  - **Active Loans:** Copy the master loan record and only the **remaining unpaid EMIs**.
- `hrms_employee_security_deposits` & `hrms_employee_security_deposit_emis`:
  - Carry forward active ongoing deductions.

#### 4. Staff Leave Allocations & Academic Calendar
- `hrms_staff_leave_allocation` & `hrms_employee_leave_balances`:
  - Reset leave balances for the new academic calendar year (fresh quota credited based on school leave policy).
- `hrms_holidays`:
  - Update with the **new academic year's holiday calendar**.

#### 5. Fee Structure & Due Dates
- `course_fees_structure_master`: Copy from previous year, but review and update if the school management implemented fee hikes for the new session.
- `schedulemaster`, `schedulemasterall`: Update the installment payment deadlines for the new session months.

---

### Category C: Tables That MUST BE COMPLETELY EMPTY (Structure Only, 0 Rows)
These tables store day-to-day transactions. In the new session database, the schema must exist, but the tables **must start with zero rows**.

```
+------------------------------------------------------------------------+
|                   FRESH / ZERO DATA TABLES CHECKLIST                   |
+------------------------------------------------------------------------+
| Fees & Billing:                                                        |
|   [ ] student_fees_data                 [ ] feesreceiptchallan         |
|   [ ] student_fees_receipt_challan      [ ] feesreceiptchallan_cancel  |
|   [ ] student_fees_receipt_breakdown    [ ] student_fees_exempted      |
|   [ ] student_bus_fees                  [ ] student_bus_fees_balance   |
|   [ ] student_online_fee_payments       [ ] generate_duechartstatus    |
|   [ ] defaulters_lists                  [ ] account_annual_defaulter   |
|                                                                        |
| Attendance & Operations:                                               |
|   [ ] academic_attendance               [ ] academic_attendance_details|
|   [ ] academic_attendance_collectives   [ ] academic_student_daily_att |
|   [ ] busattandence                     [ ] dailattandence             |
|   [ ] hrms_employee_attendance          [ ] employee_thumb_attendance  |
|   [ ] hrms_attendance_locks             [ ] hrms_leave_requests        |
|                                                                        |
| Examination & Student Performance:                                     |
|   [ ] marks                             [ ] academic_students_marks    |
|   [ ] academic_marksheets               [ ] previosly_saved_marks      |
|   [ ] academic_student_remarks          [ ] academic_teacher_remarks   |
|   [ ] academic_student_roll_no                                         |
|                                                                        |
| Yearly Session Mappings & New Intake:                                  |
|   [ ] classasigntoteacher               [ ] teacher_subjects           |
|   [ ] subject_assign_student            [ ] scholarbusassign           |
|   [ ] teacherbusassign                  [ ] inquiry_registration       |
|                                                                        |
| TC, Transport Daily, & Payroll:                                        |
|   [ ] tc_certificates                   [ ] tc_certificate_issues      |
|   [ ] tc_student_session_statuses       [ ] student_tc_files           |
|   [ ] tc_audit_logs                     [ ] fuel_entries               |
|   [ ] bus_maintenance                   [ ] gps_data / gps_data1       |
|   [ ] hrms_payrolls                     [ ] hrms_employee_deductions   |
+------------------------------------------------------------------------+
```

---

### Category D: Obsolete, Temporary, and Backup Tables (DO NOT Copy)
The following tables are temporary backups, scratch tables, or deprecated artifacts present in the existing dumps:
- `student_registration_bk`
- `student_registration_bk1`
- `student_registration_bk_05-10`
- `Final-employees`
- `userssss`
- `tmp_admission_update`
- `failed_jobs`, `jobs`, `password_resets`, `personal_access_tokens` (queue & session artifacts)

---

## 4. Step-by-Step Migration Execution Guide

Follow this standard operating procedure when spinning up a new session database:

### Step 1: Create the New Database Schema
Log in to MySQL (via phpMyAdmin or command line) and create the new schema:
```sql
CREATE DATABASE `2026_2027` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 2: Export Schema Only (DDL) from Old Database
Export all table definitions from the source database (`2025_2026`) without table data:
```bash
mysqldump -u root -p --no-data 2025_2026 > schema_only.sql
mysql -u root -p 2026_2027 < schema_only.sql
```

### Step 3: Copy Master Tables As-Is
Execute the following SQL queries to clone configuration data from `2025_2026` to `2026_2027`:

```sql
-- 1. Users, Roles & Permissions
INSERT INTO `2026_2027`.`users` SELECT * FROM `2025_2026`.`users`;
INSERT INTO `2026_2027`.`roles` SELECT * FROM `2025_2026`.`roles`;
INSERT INTO `2026_2027`.`permissions` SELECT * FROM `2025_2026`.`permissions`;
INSERT INTO `2026_2027`.`model_has_roles` SELECT * FROM `2025_2026`.`model_has_roles`;
INSERT INTO `2026_2027`.`role_has_permissions` SELECT * FROM `2025_2026`.`role_has_permissions`;
INSERT INTO `2026_2027`.`role_menus` SELECT * FROM `2025_2026`.`role_menus`;

-- 2. Academic Masters
INSERT INTO `2026_2027`.`class_name` SELECT * FROM `2025_2026`.`class_name`;
INSERT INTO `2026_2027`.`classes` SELECT * FROM `2025_2026`.`classes`;
INSERT INTO `2026_2027`.`sections` SELECT * FROM `2025_2026`.`sections`;
INSERT INTO `2026_2027`.`streams` SELECT * FROM `2025_2026`.`streams`;
INSERT INTO `2026_2027`.`terms` SELECT * FROM `2025_2026`.`terms`;
INSERT INTO `2026_2027`.`subjectmaster` SELECT * FROM `2025_2026`.`subjectmaster`;
INSERT INTO `2026_2027`.`subject_combinations` SELECT * FROM `2025_2026`.`subject_combinations`;
INSERT INTO `2026_2027`.`combination_subject` SELECT * FROM `2025_2026`.`combination_subject`;
INSERT INTO `2026_2027`.`academic_class_subject` SELECT * FROM `2025_2026`.`academic_class_subject`;
INSERT INTO `2026_2027`.`grademaster` SELECT * FROM `2025_2026`.`grademaster`;
INSERT INTO `2026_2027`.`grades` SELECT * FROM `2025_2026`.`grades`;
INSERT INTO `2026_2027`.`academic_remarksmaster` SELECT * FROM `2025_2026`.`academic_remarksmaster`;
INSERT INTO `2026_2027`.`caste_name` SELECT * FROM `2025_2026`.`caste_name`;
INSERT INTO `2026_2027`.`states` SELECT * FROM `2025_2026`.`states`;
INSERT INTO `2026_2027`.`cities` SELECT * FROM `2025_2026`.`cities`;
INSERT INTO `2026_2027`.`presentlyschool` SELECT * FROM `2025_2026`.`presentlyschool`;

-- 3. Transport Masters
INSERT INTO `2026_2027`.`vehicel` SELECT * FROM `2025_2026`.`vehicel`;
INSERT INTO `2026_2027`.`busstop` SELECT * FROM `2025_2026`.`busstop`;
INSERT INTO `2026_2027`.`routemaster` SELECT * FROM `2025_2026`.`routemaster`;
INSERT INTO `2026_2027`.`routeareabusmaster` SELECT * FROM `2025_2026`.`routeareabusmaster`;
INSERT INTO `2026_2027`.`busfees` SELECT * FROM `2025_2026`.`busfees`;
INSERT INTO `2026_2027`.`busstaff` SELECT * FROM `2025_2026`.`busstaff`;
INSERT INTO `2026_2027`.`fuel_stations` SELECT * FROM `2025_2026`.`fuel_stations`;
INSERT INTO `2026_2027`.`rtopaper` SELECT * FROM `2025_2026`.`rtopaper`;
INSERT INTO `2026_2027`.`maintenancegroupmaster` SELECT * FROM `2025_2026`.`maintenancegroupmaster`;
INSERT INTO `2026_2027`.`maintenanceheadmaster` SELECT * FROM `2025_2026`.`maintenanceheadmaster`;

-- 4. HRMS Masters & Active Employee Profiles
INSERT INTO `2026_2027`.`hrms_departments` SELECT * FROM `2025_2026`.`hrms_departments`;
INSERT INTO `2026_2027`.`departments` SELECT * FROM `2025_2026`.`departments`;
INSERT INTO `2026_2027`.`hrms_positions` SELECT * FROM `2025_2026`.`hrms_positions`;
INSERT INTO `2026_2027`.`hrms_shifts` SELECT * FROM `2025_2026`.`hrms_shifts`;
INSERT INTO `2026_2027`.`hrms_shift_types` SELECT * FROM `2025_2026`.`hrms_shift_types`;
INSERT INTO `2026_2027`.`hrms_staff_type` SELECT * FROM `2025_2026`.`hrms_staff_type`;
INSERT INTO `2026_2027`.`hrms_leave_types` SELECT * FROM `2025_2026`.`hrms_leave_types`;
INSERT INTO `2026_2027`.`hrms_payroll_attendance_configuration` SELECT * FROM `2025_2026`.`hrms_payroll_attendance_configuration`;
INSERT INTO `2026_2027`.`hrms_payroll_department_attendance_configuration` SELECT * FROM `2025_2026`.`hrms_payroll_department_attendance_configuration`;
INSERT INTO `2026_2027`.`hrms_payroll_staff_attendance_configuration` SELECT * FROM `2025_2026`.`hrms_payroll_staff_attendance_configuration`;
INSERT INTO `2026_2027`.`teachers` SELECT * FROM `2025_2026`.`teachers`;
INSERT INTO `2026_2027`.`hrms_employees` SELECT * FROM `2025_2026`.`hrms_employees`;
INSERT INTO `2026_2027`.`hrms_employee_addresses` SELECT * FROM `2025_2026`.`hrms_employee_addresses`;
INSERT INTO `2026_2027`.`hrms_employee_bank_details` SELECT * FROM `2025_2026`.`hrms_employee_bank_details`;
INSERT INTO `2026_2027`.`hrms_employee_biometric_details` SELECT * FROM `2025_2026`.`hrms_employee_biometric_details`;
INSERT INTO `2026_2027`.`hrms_employee_documents` SELECT * FROM `2025_2026`.`hrms_employee_documents`;
INSERT INTO `2026_2027`.`hrms_employee_educations` SELECT * FROM `2025_2026`.`hrms_employee_educations`;
INSERT INTO `2026_2027`.`hrms_employee_emergency_contacts` SELECT * FROM `2025_2026`.`hrms_employee_emergency_contacts`;
INSERT INTO `2026_2027`.`hrms_employee_experiences` SELECT * FROM `2025_2026`.`hrms_employee_experiences`;
INSERT INTO `2026_2027`.`hrms_employee_statutory_info` SELECT * FROM `2025_2026`.`hrms_employee_statutory_info`;
INSERT INTO `2026_2027`.`hrms_employee_salaries` SELECT * FROM `2025_2026`.`hrms_employee_salaries`;

-- 5. Fee Configuration Masters
INSERT INTO `2026_2027`.`fees_types_master` SELECT * FROM `2025_2026`.`fees_types_master`;
INSERT INTO `2026_2027`.`course_fees_head_master` SELECT * FROM `2025_2026`.`course_fees_head_master`;
INSERT INTO `2026_2027`.`late_fees_master` SELECT * FROM `2025_2026`.`late_fees_master`;
INSERT INTO `2026_2027`.`fee_exemption_master` SELECT * FROM `2025_2026`.`fee_exemption_master`;
INSERT INTO `2026_2027`.`abvance_nextyear_fees` SELECT * FROM `2025_2026`.`abvance_nextyear_fees`;
```

### Step 4: Configure `.env` for Student Promotion
Ensure the `.env` file points to the current active database and the target new session:
```env
DB_DATABASE=2025_2026
DB_DATABASE_NEXT=2026_2027
```

### Step 5: Execute Student Promotion via Web UI
Navigate to the **Student Transfer** module in the admin dashboard:
1. Select the current class (e.g., Class 01).
2. The system checks `nextSessionStudentTableExists()` and lists active students (automatically filtering out transferred/TC students).
3. Select students and click **Promote**.
4. The system increments the class name, preserves student master information, and sets `session_name = '2026_2027'`.
5. Repeat for each class up to Class 11. *(Class 12 students are skipped automatically)*.

### Step 6: Post-Promotion Operational Setup
Once students are in `2026_2027`:
1. **Fee Structures:** Verify and update `course_fees_structure_master` and `schedulemaster` for any fee changes.
2. **Carry Forward Fee Balances:** Migrate pending previous year arrears to `student_fee_balances`.
3. **Class Teachers:** Assign class teachers using `classasigntoteacher`.
4. **Roll Numbers:** Run the roll number generation utility (`academic_student_roll_no`).
5. **Fee Due Charts:** Run due chart generation to initialize `student_fees_data` for all students.

---

## 5. Verification Checklist

Before declaring the new session live for staff and students:

- [ ] **Admin Login Test:** Log in and switch to `2026_2027` via the header session switcher. Confirm that dashboard widgets load without database connection errors.
- [ ] **Student Count Verification:** Verify that the total active student count matches the expected number (total previous students minus Class 12 pass-outs minus TC holders).
- [ ] **TC Exclusion Check:** Confirm that no student who was issued a Transfer Certificate appears in active class lists.
- [ ] **Zero-Transaction Check:** Verify that `feesreceiptchallan`, `academic_attendance`, and `academic_students_marks` have exactly 0 rows.
- [ ] **Fee Due Chart Test:** Open a student profile in the new session and ensure their fee installment breakdown calculates accurately.
