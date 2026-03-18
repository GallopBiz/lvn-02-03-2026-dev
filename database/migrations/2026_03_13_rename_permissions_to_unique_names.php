<?php
// database/migrations/2026_03_13_rename_permissions_to_unique_names.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Scholars
        DB::table('permissions')->where('name', 'filter_followup')->update(['name' => 'scholars-filter_followup']);
        DB::table('permissions')->where('name', 'admin_preenquiryform')->update(['name' => 'scholars-admin_preenquiryform']);
        DB::table('permissions')->where('name', 'adminpreenquiryform')->update(['name' => 'scholars-adminpreenquiryform']);
        DB::table('permissions')->where('name', 'preenquiryviewlist')->update(['name' => 'scholars-preenquiryviewlist']);
        DB::table('permissions')->where('name', 'enquiryform')->update(['name' => 'scholars-enquiryform']);
        DB::table('permissions')->where('name', 'adminenquirylist')->update(['name' => 'scholars-adminenquirylist']);
        DB::table('permissions')->where('name', 'enquiryviewlist')->update(['name' => 'scholars-enquiryviewlist']);
        DB::table('permissions')->where('name', 'enquiryeditlist')->update(['name' => 'scholars-enquiryeditlist']);
        DB::table('permissions')->where('name', 'index')->update(['name' => 'scholars-index']);
        DB::table('permissions')->where('name', 'followupdate')->update(['name' => 'scholars-followupdate']);
        DB::table('permissions')->where('name', 'selection_process')->update(['name' => 'scholars-selection_process']);
        DB::table('permissions')->where('name', 'add_student_registrations')->update(['name' => 'scholars-add_student_registrations']);
        DB::table('permissions')->where('name', 'student_registrations')->update(['name' => 'scholars-student_registrations']);
        DB::table('permissions')->where('name', 'registrationviewlist')->update(['name' => 'scholars-registrationviewlist']);
        DB::table('permissions')->where('name', 'registrationeditlist')->update(['name' => 'scholars-registrationeditlist']);

        // Fees
        DB::table('permissions')->where('name', 'student_feesmaster')->update(['name' => 'fees-student_feesmaster']);
        DB::table('permissions')->where('name', 'adminpreenquiryfeeslist')->update(['name' => 'fees-adminpreenquiryfeeslist']);
        DB::table('permissions')->where('name', 'preenquiryviewlist')->update(['name' => 'fees-preenquiryviewlist']);
        DB::table('permissions')->where('name', 'course_fees_structure_master_list')->update(['name' => 'fees-course_fees_structure_master_list']);
        DB::table('permissions')->where('name', 'course_fees_head_orders_list')->update(['name' => 'fees-course_fees_head_orders_list']);
        DB::table('permissions')->where('name', 'late_fees_master')->update(['name' => 'fees-late_fees_master']);
        DB::table('permissions')->where('name', 'late_fees_master_edit')->update(['name' => 'fees-late_fees_master_edit']);
        DB::table('permissions')->where('name', 'generate_due_chart')->update(['name' => 'fees-generate_due_chart']);
        DB::table('permissions')->where('name', 'student_ledger')->update(['name' => 'fees-student_ledger']);
        DB::table('permissions')->where('name', 'student_ledger_delete')->update(['name' => 'fees-student_ledger_delete']);
        DB::table('permissions')->where('name', 'search_cancle_student_ledger')->update(['name' => 'fees-search_cancle_student_ledger']);
        DB::table('permissions')->where('name', 'classname_delete')->update(['name' => 'fees-classname_delete']);
        DB::table('permissions')->where('name', 'classes_delete')->update(['name' => 'fees-classes_delete']);
        DB::table('permissions')->where('name', 'editclasses')->update(['name' => 'fees-editclasses']);
        DB::table('permissions')->where('name', 'delete')->update(['name' => 'fees-classes-delete']);
        DB::table('permissions')->where('name', 'storeg')->update(['name' => 'fees-storeg']);
        DB::table('permissions')->where('name', 'editg')->update(['name' => 'fees-editg']);

        // Transport (sample, add all as needed)
        DB::table('permissions')->where('name', 'addvehical')->update(['name' => 'transport-addvehical']);
        DB::table('permissions')->where('name', 'busstaff')->update(['name' => 'transport-busstaff']);
        DB::table('permissions')->where('name', 'addvehical_delete')->update(['name' => 'transport-addvehical_delete']);
        DB::table('permissions')->where('name', 'registerstaff')->update(['name' => 'transport-registerstaff']);
        DB::table('permissions')->where('name', 'busstop_delete')->update(['name' => 'transport-busstop_delete']);
        DB::table('permissions')->where('name', 'bs_soft_delete')->update(['name' => 'transport-bs_soft_delete']);
        DB::table('permissions')->where('name', 'Attendance')->update(['name' => 'transport-Attendance']);
        DB::table('permissions')->where('name', 'list_busAttendence')->update(['name' => 'transport-list_busAttendence']);
        DB::table('permissions')->where('name', 'filter_Attendance')->update(['name' => 'transport-filter_Attendance']);
        DB::table('permissions')->where('name', 'Natureofwork_delete')->update(['name' => 'transport-Natureofwork_delete']);
        DB::table('permissions')->where('name', 'busstaff_delete')->update(['name' => 'transport-busstaff_delete']);
        DB::table('permissions')->where('name', 'rtopaper_delete')->update(['name' => 'transport-rtopaper_delete']);
        DB::table('permissions')->where('name', 'maintenancegroupmaster_delete')->update(['name' => 'transport-maintenancegroupmaster_delete']);
        DB::table('permissions')->where('name', 'maintenanceheadpmaster_delete')->update(['name' => 'transport-maintenanceheadpmaster_delete']);
        DB::table('permissions')->where('name', 'route_name_delete')->update(['name' => 'transport-route_name_delete']);
        DB::table('permissions')->where('name', 'route_delete')->update(['name' => 'transport-route_delete']);
        DB::table('permissions')->where('name', 'view_bus')->update(['name' => 'transport-view_bus']);
        DB::table('permissions')->where('name', 'list_party_master')->update(['name' => 'transport-list_party_master']);
        DB::table('permissions')->where('name', 'party_master_delete')->update(['name' => 'transport-party_master_delete']);
        DB::table('permissions')->where('name', 'scholarbusassign_post_pickup')->update(['name' => 'transport-scholarbusassign_post_pickup']);
        DB::table('permissions')->where('name', 'scholarbusassign_post_drop')->update(['name' => 'transport-scholarbusassign_post_drop']);
        DB::table('permissions')->where('name', 'bus_details')->update(['name' => 'transport-bus_details']);
        DB::table('permissions')->where('name', 'data_foredit_pickup')->update(['name' => 'transport-data_foredit_pickup']);

        // Academic (sample, add all as needed)
        DB::table('permissions')->where('name', 'exam_master_delete')->update(['name' => 'academic-exam_master_delete']);
        DB::table('permissions')->where('name', 'examtype_delete')->update(['name' => 'academic-examtype_delete']);
        DB::table('permissions')->where('name', 'teaches_delete')->update(['name' => 'academic-teaches_delete']);
        DB::table('permissions')->where('name', 'marks_delete')->update(['name' => 'academic-marks_delete']);
        DB::table('permissions')->where('name', 'classstudentdata')->update(['name' => 'academic-classstudentdata']);
        DB::table('permissions')->where('name', 'AssignSubject_delete')->update(['name' => 'academic-AssignSubject_delete']);
        DB::table('permissions')->where('name', 'student_combination_data')->update(['name' => 'academic-student_combination_data']);
        DB::table('permissions')->where('name', 'subjects_delete')->update(['name' => 'academic-subjects_delete']);
        DB::table('permissions')->where('name', 'teachersubject_delete')->update(['name' => 'academic-teachersubject_delete']);
        DB::table('permissions')->where('name', 'getteachersandsubject')->update(['name' => 'academic-getteachersandsubject']);
        DB::table('permissions')->where('name', 'getteachersdata')->update(['name' => 'academic-getteachersdata']);
        DB::table('permissions')->where('name', 'teachersubject_copy')->update(['name' => 'academic-teachersubject_copy']);
        DB::table('permissions')->where('name', 'classattandence')->update(['name' => 'academic-classattandence']);
        DB::table('permissions')->where('name', 'array_unique')->update(['name' => 'academic-array_unique']);
        DB::table('permissions')->where('name', 'Attendance')->update(['name' => 'academic-Attendance']);
        DB::table('permissions')->where('name', 'primarygroup_master_delete')->update(['name' => 'academic-primarygroup_master_delete']);
        DB::table('permissions')->where('name', 'groupmaster_delete')->update(['name' => 'academic-groupmaster_delete']);
        DB::table('permissions')->where('name', 'headmaster_delete')->update(['name' => 'academic-headmaster_delete']);
        DB::table('permissions')->where('name', 'subheadmaster_delete')->update(['name' => 'academic-subheadmaster_delete']);
        DB::table('permissions')->where('name', 'grade_master_delete')->update(['name' => 'academic-grade_master_delete']);
        DB::table('permissions')->where('name', 'grade_delete')->update(['name' => 'academic-grade_delete']);
        DB::table('permissions')->where('name', 'class_teacherdelete')->update(['name' => 'academic-class_teacherdelete']);
        DB::table('permissions')->where('name', 'stream_master_delete')->update(['name' => 'academic-stream_master_delete']);
        DB::table('permissions')->where('name', 'section_master_delete')->update(['name' => 'academic-section_master_delete']);
        DB::table('permissions')->where('name', 'remarkmaster_delete')->update(['name' => 'academic-remarkmaster_delete']);
        DB::table('permissions')->where('name', 'subjectcombinatio_master_delete')->update(['name' => 'academic-subjectcombinatio_master_delete']);
        DB::table('permissions')->where('name', 'subject_delete')->update(['name' => 'academic-subject_delete']);

        // HRMS (sample, add all as needed)
        DB::table('permissions')->where('name', 'employee_delete')->update(['name' => 'hrms-employee_delete']);
        DB::table('permissions')->where('name', 'department_delete')->update(['name' => 'hrms-department_delete']);
        DB::table('permissions')->where('name', 'position_delete')->update(['name' => 'hrms-position_delete']);
        DB::table('permissions')->where('name', 'attendance_delete')->update(['name' => 'hrms-attendance_delete']);
        DB::table('permissions')->where('name', 'holidays_delete')->update(['name' => 'hrms-holidays_delete']);
        DB::table('permissions')->where('name', 'salaries_delete')->update(['name' => 'hrms-salaries_delete']);
        DB::table('permissions')->where('name', 'leaverequests_delete')->update(['name' => 'hrms-leaverequests_delete']);
    }

    public function down(): void
    {
        // No down migration for renaming (manual rollback if needed)
    }
};
