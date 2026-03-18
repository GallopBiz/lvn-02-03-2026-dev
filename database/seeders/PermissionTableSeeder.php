<?php
namespace Database\Seeders;use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;use Spatie\Permission\Models\Permission;class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Scholars
        $scholars = [
            'scholars-filter_followup','scholars-admin_preenquiryform','scholars-adminpreenquiryform','scholars-preenquiryviewlist','scholars-enquiryform','scholars-adminenquirylist','scholars-enquiryviewlist','scholars-enquiryeditlist','scholars-index','scholars-followupdate','scholars-selection_process','scholars-add_student_registrations','scholars-student_registrations','scholars-registrationviewlist','scholars-registrationeditlist'
        ];
        // Fees
        $fees = [
            'fees-index','fees-edit','fees-bus-index','fees-bus-edit','fees-student_feesmaster','fees-adminpreenquiryfeeslist','fees-preenquiryviewlist','fees-course_fees_structure_master_list','fees-course_fees_head_orders_list','fees-late_fees_master','fees-late_fees_master_edit','fees-generate_due_chart','fees-receipt-index','fees-student_ledger','fees-student_ledger_delete','fees-search_cancle_student_ledger','fees-defaulters-index','fees-defaulters-view','fees-classes-index','fees-classes-view','fees-classes-store','fees-classname_delete','fees-classes_delete','fees-editclasses','fees-classes-delete','storeg','editg','index'
        ];
        // Transport
        $transport = [
            'transport-index-addvehical','transport-addvehical','transport-list-addvehical','transport-view-addvehical','transport-store-addvehical'
        ];
        // Academic
        $academic = [
            'academic-store-greadingmaster','academic-grade_master_delete','academic-delete-greadingmaster','academic-index-gread','academic-create-gread','academic-view-gread','academic-store-gread','academic-grade_delete','academic-delete-gread','academic-index-calssese-assigne-to-teacher','academic-saveclassdata-calssese-assigne-to-teacher','academic-view-calssese-assigne-to-teacher','academic-store-calssese-assigne-to-teacher','academic-class_teacherdelete','academic-index-streammaster','academic-create-streammaster','academic-view-streammaster','academic-store-streammaster','academic-stream_master_delete','academic-delete-streammaster','academic-index-sectionmaster','academic-create-sectionmaster','academic-view-sectionmaster','academic-store-sectionmaster','academic-section_master_delete','academic-delete-sectionmaster','academic-index-remarkmaster','academic-create-remarkmaster','academic-view-remarkmaster','academic-store-remarkmaster','academic-remarkmaster_delete','academic-delete-remarkmaster','academic-index-subjectcombinatiomaster','academic-create-subjectcombinatiomaster','academic-view-subjectcombinatiomaster','academic-store-subjectcombinatiomaster','academic-subjectcombinatio_master_delete','academic-subject_delete','academic-delete-subjectcombinatiomaster'
        ];
        // HRMS
        $hrms = [
            'hrms-index-employee','hrms-create-employee','hrms-view-employee','hrms-store-employee','hrms-employee_delete','hrms-delete-employee','hrms-index-department','hrms-create-department','hrms-view-department','hrms-store-department','hrms-department_delete','hrms-delete-department','hrms-index-position','hrms-create-position','hrms-view-position','hrms-store-position','hrms-position_delete','hrms-delete-position','hrms-index-attendance','hrms-create-attendance','hrms-view-attendance','hrms-store-position','hrms-attendance_delete','hrms-delete-attendance','hrms-index-holidays','hrms-create-holidays','hrms-view-holidays','hrms-store-holidays','hrms-holidays_delete','hrms-delete-holidays','hrms-index-salaries','hrms-create-salaries','hrms-view-salaries','hrms-store-salaries','hrms-salaries_delete','hrms-delete-salaries','hrms-index-leaverequests','hrms-create-leaverequests','hrms-view-leaverequests','hrms-store-leaverequests','hrms-leaverequests_delete','hrms-delete-leaverequests'
        ];
        $general = [
            'role-list','role-create','role-edit','role-delete','product-list','product-create','product-edit','product-delete',
            'create','edit','delete','view','store','index','saveclassdata'
        ];
        $permissions = array_merge($scholars, $fees, $transport, $academic, $hrms, $general);
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
    }
}