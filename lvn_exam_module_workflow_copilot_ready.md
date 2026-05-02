# LVN Academic Exam Module Workflow

## 📌 Overview
This document defines the complete workflow and requirements for building the Exam Module in a School Management System. It is designed for use with VS Code Copilot to assist in development.

---

## 1. Exam Creation (Centralized)

- All exams (Class 1 to Class 12) must be created from a **single centralized panel**.
- Exams should follow a predefined academic structure:

```
PT-1
Term 1
PT-2
Pre-Board (only for Class 10 & 12)
Term 2
```

### Requirements:
- Admin can create exams for one or multiple classes.
- Pre-Board exams should only be available for Class 10 and Class 12.
- Each exam must be linked with:
  - Class
  - Section
  - Subjects
  - Teachers
  - Session

---

## 2. Copy Exam from Previous Session

### Functionality:
- Ability to copy exams from a previous academic session.

### Flow:
- Select Source Session
- Select Target Session
- Copy exam structure and configurations

### Behavior:
- If no changes → reuse as-is
- If changes needed → allow editing after copy

---

## 3. Roll Number System

- Replace **Scholar Number** with **Roll Number** for exams.

### Requirements:
- Roll numbers must be:
  - System-generated
  - Unique per class/section/session
  - Independent of exam creation

### Usage:
- Seating arrangement
- Printing separately (Roll Number List)

---

## 4. Marks Entry System

- Marks should be entered **subject-wise**.

### Components to include:
- NB (Notebook)
- PF (Practical File)
- SE (Subject Enrichment)
- MAS (Multiple Assessment)

### Requirements:
- All components should be entered together in a single interface
- Subject-wise entry per student

---

## 5. Weightage Configuration

- Support dynamic/custom weightage configuration.

### Requirements:
- Admin can define weightage for:
  - Theory
  - PT
  - NB / PF / SE / MAS
- Must be configurable per class or exam type

---

## 6. PT Marks Conversion Logic

### Rule:
- PT marks should be converted to **5 marks scale** during final calculation.

### Mapping:
- PT-1 → Included in Term 1
- PT-2 → Included in Term 2

### Example:
- If student scores 40/50 → convert proportionally to 5

```
Converted Marks = (Obtained Marks / Total Marks) * 5
```

---

## 7. Final Marksheet Calculation

### Structure:
- 80 Marks → Main Exam (Term 1 + Term 2)
- 20 Marks → Internal Components
  - PT (converted)
  - NB / PF / SE / MAS

### Requirements:
- Automatic calculation
- Must follow defined weightage

---

## 8. Non-Academic Subjects

### Rules:
- Evaluated out of **5 marks**
- Managed via separate marking system

### Notes:
- Some subjects fall under **Co-Scholastic categories**
- Should not mix with main academic marks

---

## 9. Class-wise Marksheet Rules

### Nursery, KG1, KG2:
- Marksheet will remain **handwritten**
- No system generation required

### Class 1 to Class 8:
- Use **common marksheet format**

---

## 10. Reports (Consolidated Marksheet)

### Requirement:
- Generate a **Consolidated Marksheet Report**

### Features:
- Display marks of all students in a single view
- Useful for:
  - Analysis
  - Record keeping

---

## 🧠 Technical Notes

- System must be **session-based (session_id required in all tables)**
- Follow modular structure (Service/Repository pattern preferred)
- Ensure scalability and clean relationships
- Handle edge cases:
  - Missing subjects/teachers
  - Class structure changes

---

## ✅ Expected Development Output

- Database schema (tables & relations)
- Models and relationships
- Services and controllers
- Admin panel logic / APIs
- Reports generation module

---

**End of Document**

