# Transfer Certificate (TC) Module
## Multi-Session CBSE School Management System

### 1. Objective

Implement a robust Transfer Certificate (TC) module for a CBSE school management system that operates from **Class 1 to Class 12** and supports **multiple academic sessions**.

The TC module must:

- Generate a Transfer Certificate for a student.
- Keep the student's master record permanently available.
- Preserve all historical session-wise data.
- Prevent a TC-issued student from appearing in future active sessions.
- Allow administrators to view the student's complete historical records.
- Support re-printing an already generated TC.
- Prevent accidental deletion of student/session data.
- Work correctly with Fees, Attendance, Examination, Transport, Scholar, HRMS and other modules.

---

# 2. Core Principle

**Generating a TC must never delete the student from the database.**

The student master record must remain permanently available.

TC is a **session-level leaving event**, not a permanent deletion operation.

Example:

```text
Student
  |
  +-- 2024-25
  |     Class: 9-A
  |     Status: PROMOTED
  |
  +-- 2025-26
  |     Class: 10-A
  |     Status: TC
  |     TC Generated: YES
  |
  +-- 2026-27
        No enrollment record
        Student must not appear in active students
```

---

# 3. Recommended Database Architecture

## 3.1 Students Table

The `students` table should contain permanent/master student information.

Example:

```text
students
---------
id
admission_no
student_name
date_of_birth
gender
father_name
mother_name
address
...
```

Do not store session-specific class, section or TC information here.

The student record must not be deleted when TC is generated.

---

# 4. Student Session Table

Create or use a session-specific enrollment table such as:

```text
student_sessions
```

Recommended fields:

```text
id
student_id
session_id
class_id
section_id
roll_no
admission_date
joining_date
leaving_date
status
created_at
updated_at
```

### Recommended Status Values

```text
ACTIVE
PROMOTED
TC
LEFT
COMPLETED
```

The exact values can be adjusted according to the existing application, but they must be consistent throughout the system.

---

# 5. TC Table

Create a dedicated TC table using the existing `tc_` naming convention.

Recommended table:

```text
tc_records
```

Recommended fields:

```text
id
student_id
session_id
tc_number
tc_date
reason
last_class_id
last_section_id
last_attended_date
status
remarks
created_by
updated_by
created_at
updated_at
```

Optional fields can be added according to the school's existing TC format.

---

# 6. Relationship Between Student, Session and TC

The relationship should work as follows:

```text
students
    |
    | 1:N
    v
student_sessions
    |
    | 0:1 or 1:N
    v
tc_records
```

A student can have multiple historical session records.

A particular session can have a TC record.

The student master record remains intact.

---

# 7. TC Generation Workflow

When an administrator generates a TC:

### Step 1

Validate that the student has an enrollment record for the selected session.

```text
student_id
session_id
```

must exist.

### Step 2

Check whether a TC already exists for that student and session.

Example:

```sql
SELECT *
FROM tc_records
WHERE student_id = ?
AND session_id = ?
```

If a TC already exists, do not silently create another record.

The system should either:

- Open the existing TC for editing/reprinting, or
- Ask for confirmation before creating a duplicate, depending on existing business requirements.

### Step 3

Create the TC record.

```text
tc_records
-------------------------
student_id
session_id
tc_number
tc_date
status = GENERATED
```

### Step 4

Update the corresponding student session:

```text
student_sessions.status = TC
student_sessions.leaving_date = tc_date
```

### Step 5

Do NOT delete:

```text
students
student_sessions
fees
attendance
exam records
transport records
scholar records
HRMS records
```

Historical data must remain available.

---

# 8. Active Student Logic

The most important rule:

A student should appear in the active student list only when they have an active enrollment in the selected session.

Example:

```sql
WHERE student_sessions.session_id = :current_session
AND student_sessions.status = 'ACTIVE'
```

Do not determine active students only from the `students` table.

---

# 9. Next Session Logic

Suppose:

```text
Student: Rahul
Session: 2025-26
Class: 10-A
Status: TC
```

When the new session starts:

```text
2026-27
```

Rahul must not automatically appear in the new session.

There must be no active `student_sessions` record for Rahul in 2026-27.

Expected result:

```text
2025-26
Rahul
10-A
TC
```

```text
2026-27
Rahul
NOT ENROLLED
```

However, Rahul must still be searchable from the student's historical records.

---

# 10. Promotion Logic

TC and promotion are two different processes.

## Example: Class 11 Student

```text
2025-26
Class 11-A
Status: PROMOTED
```

After promotion:

```text
2026-27
Class 12-A
Status: ACTIVE
```

The student should appear in the new session.

## Example: Class 10 Student Leaving School

```text
2025-26
Class 10-A
Status: TC
```

After TC:

```text
2026-27
No enrollment
```

The student must not appear in the next session.

---

# 11. Class 12 Logic

Class 12 requires special handling because it is the final class of the school.

After completion of Class 12:

```text
2025-26
Class 12-A
```

The student should not be promoted to:

```text
2026-27
Class 13
```

There is no Class 13.

The session record should be marked appropriately, for example:

```text
COMPLETED
```

or:

```text
TC
```

depending on the school's official workflow.

The system must not create a Class 13 enrollment.

---

# 12. Historical Data Rule

Historical data must always remain accessible.

For example, if a student received TC in 2025-26:

```text
Student Profile
    |
    +-- 2023-24
    |     Fees
    |     Attendance
    |     Exams
    |     Transport
    |
    +-- 2024-25
    |     Fees
    |     Attendance
    |     Exams
    |     Transport
    |
    +-- 2025-26
          Fees
          Attendance
          Exams
          Transport
          TC
```

The administrator should be able to select the session and view the corresponding records.

---

# 13. Module Integration

TC generation must not break historical records in other modules.

The following modules must remain linked to the student's original session:

```text
Fees
Attendance
Examination
Transport
Scholar
HRMS
Academic
Library
Discipline
Medical
Other student-related modules
```

Every session-dependent module should use:

```text
student_id
session_id
```

where applicable.

Do not use only `student_id` to determine the student's historical class/session.

---

# 14. TC Print Logic

When printing a TC, do not fetch the student's current session information.

Fetch information from the session for which the TC was generated.

Example:

```text
Current Session: 2026-27
TC Session: 2025-26
```

The TC must display:

```text
Class: 10
Section: A
Session: 2025-26
```

and not the current session's information.

Use:

```text
tc_records.session_id
```

to determine the historical session.

---

# 15. Dynamic TC Data

TC information should be fetched dynamically from the relevant historical tables.

Example:

```text
Student Name
Father Name
Mother Name
Date of Birth
Admission Number
Admission Date
Last Class
Last Section
Last Attendance Date
Subjects
Academic Information
Conduct
Reason for Leaving
TC Number
TC Date
```

If any optional data is unavailable, display:

```text
--
```

instead of generating an error.

---

# 16. Re-Printing TC

Once a TC has been generated, administrators must be able to print it again.

Example:

```text
TC Number: TC/2026/00125
Status: GENERATED
```

Click:

```text
Print TC
```

The system should generate the same TC using the original historical session.

Do not change the student's current session data during re-printing.

---

# 17. Duplicate TC Handling

If the same student and session already have a TC:

```text
student_id = 125
session_id = 2025-26
```

the system should detect the existing record.

Recommended behavior:

```text
TC already exists.

[View TC]
[Print TC]
[Edit TC]
```

If the school requires multiple TC records for the same student/session, support this explicitly instead of creating duplicates accidentally.

---

# 18. Database Constraints

Recommended foreign keys:

```text
tc_records.student_id
    -> students.id

tc_records.session_id
    -> sessions.id

student_sessions.student_id
    -> students.id

student_sessions.session_id
    -> sessions.id
```

Recommended index:

```text
(student_id, session_id)
```

on:

```text
student_sessions
```

and:

```text
(student_id, session_id)
```

on:

```text
tc_records
```

If only one official TC is allowed per student per session, consider:

```text
UNIQUE(student_id, session_id)
```

on `tc_records`.

---

# 19. Transaction Safety

TC generation must use a database transaction.

Pseudo-flow:

```php
DB::transaction(function () {

    // 1. Validate student session

    // 2. Check existing TC

    // 3. Generate TC number

    // 4. Create TC

    // 5. Update student session

    // 6. Return TC

});
```

If any operation fails, all TC-related changes must be rolled back.

This prevents situations such as:

```text
TC created
BUT
student session not updated
```

---

# 20. Important Rules

### Rule 1

Never delete the student master record when TC is generated.

### Rule 2

Never delete historical session records.

### Rule 3

Never delete historical fees, exams, attendance or transport records because of TC.

### Rule 4

TC is session-specific.

### Rule 5

Active students must be determined from the selected session.

### Rule 6

TC students must not automatically appear in the next session.

### Rule 7

Class 12 students must never be promoted to Class 13.

### Rule 8

Historical TC data must always be printable.

### Rule 9

TC print must use the TC's session, not the current session.

### Rule 10

Missing optional information should display `--`.

---

# 21. Example Complete Scenario

### Student Admission

```text
Admission No: ADM-10025
Student: Rahul Sharma
```

### Session 2024-25

```text
Class: 9-A
Status: PROMOTED
```

### Session 2025-26

```text
Class: 10-A
Status: ACTIVE
```

Student leaves school on:

```text
31-03-2026
```

TC is generated:

```text
TC Number: TC/2026/00125
TC Date: 31-03-2026
Session: 2025-26
```

Update:

```text
student_sessions
-------------------------
student_id = 125
session_id = 2025-26
status = TC
leaving_date = 31-03-2026
```

Next session:

```text
2026-27
```

Do not create:

```text
student_sessions
student_id = 125
session_id = 2026-27
```

Result:

```text
2025-26 Active Students
    Rahul -> No

2026-27 Active Students
    Rahul -> No

Student Search
    Rahul -> Yes

Student Historical Profile
    2024-25 -> Available
    2025-26 -> Available
    TC      -> Available
```

---

# 22. Laravel Implementation Guidelines

Follow the existing Laravel project architecture.

Recommended structure:

```text
app/
├── Models/
│   ├── Student.php
│   ├── StudentSession.php
│   └── TC/
│       └── TcRecord.php
│
├── Http/
│   └── Controllers/
│       └── TransferCertificate/
│           └── TcController.php
│
└── Services/
    └── TransferCertificate/
        └── TcService.php
```

Views:

```text
resources/views/
└── Transfer Certificate/
    ├── index.blade.php
    ├── create.blade.php
    ├── edit.blade.php
    ├── show.blade.php
    └── print.blade.php
```

If the existing project already has a different folder structure, follow the existing project conventions instead of creating duplicate structures.

---

# 23. Service Layer

Put the main TC business logic in a dedicated service.

Example:

```php
class TcService
{
    public function generateTc(
        int $studentId,
        int $sessionId,
        array $data
    ) {
        // Validate session enrollment

        // Check existing TC

        // Generate TC number

        // Create TC

        // Update student session

        // Return TC
    }
}
```

The controller should remain thin.

Business rules should not be duplicated between controllers.

---

# 24. Required Tests

Implement tests for at least the following cases:

### Test 1

Student has active session and TC is generated successfully.

### Test 2

TC student does not appear in next session.

### Test 3

Student master record still exists after TC.

### Test 4

Historical session remains accessible.

### Test 5

Historical fees remain accessible.

### Test 6

Historical exam records remain accessible.

### Test 7

Historical transport records remain accessible.

### Test 8

Existing TC is detected.

### Test 9

TC can be reprinted.

### Test 10

Class 12 student is not promoted to Class 13.

### Test 11

Missing optional data displays `--`.

### Test 12

Failed TC generation rolls back the database transaction.

---

# 25. Copilot Implementation Instruction

Before writing code:

1. Inspect the existing database structure.
2. Inspect the existing `students` table.
3. Inspect the academic session table.
4. Inspect how student-session enrollment is currently stored.
5. Inspect existing Fees, Attendance, Exam and Transport relationships.
6. Inspect existing Transfer Certificate code, if any.
7. Do not create duplicate tables or models if equivalent structures already exist.
8. Reuse existing naming conventions.
9. Follow existing authentication and authorization rules.
10. Preserve all existing functionality.

Then implement the TC module according to this specification.

Do not make destructive database changes.

Do not delete student records when generating a TC.

Do not modify historical session data unless explicitly required by the TC workflow.

---

# 26. Final Expected Behavior

The final system must follow this principle:

```text
MASTER STUDENT
       |
       +-----------------------------+
       |                             |
       v                             v
HISTORICAL SESSIONS              CURRENT SESSION
       |                             |
       |                             |
       +--> Fees                     +--> Active
       +--> Attendance               +--> Promoted
       +--> Examination              +--> TC
       +--> Transport
       +--> Academic
       +--> HRMS
       +--> TC
```

**TC changes the student's enrollment status for a specific session. It does not delete the student and it does not destroy historical data.**

This architecture should support a CBSE school operating from **Class 1 through Class 12** with multiple academic sessions and maintain complete student history throughout the student's lifecycle.
