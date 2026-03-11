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
        // Permissions from modules
        $scholars = [
            'filter_followup','admin_preenquiryform','adminpreenquiryform','preenquiryviewlist','enquiryform','adminenquirylist','enquiryviewlist','enquiryeditlist','index(enquiryrecipt)','followupdate','selection_process','add_student_registrations','student_registrations','registrationviewlist','registrationeditlist'
        ];
        $fees = [
            'index(fees-types-master)','edit(fees-types-master-edit)','index(bus-fees-master)','edit(bus-fees-master-edit)','student_feesmaster','adminpreenquiryfeeslist','preenquiryviewlist','course_fees_structure_master_list','course_fees_head_orders_list','late_fees_master','late_fees_master_edit','generate_due_chart','index(fees_receipt_challan)','student_ledger','student_ledger_delete','search_cancle_student_ledger','index(defaulters_list)','view(view_defaulters_list)','index(classes)','view(classes)','store(classes)','classname_delete','classes_delete','editclasses','delete(classes)','storeg','editg','index(collection)'
        ];
        $transport = [
            'index(addvehical)','addvehical','list(addvehical)','view(addvehical)','store(addvehical)','busstaff','addvehical_delete','registerstaff','index(area-master)','view(area-master)','store(area-master)','delete(area-master)','index(bus-stop)','store(bus-stop)','view(bus-stop)','create(bus-stop)','busstop_delete','bs_soft_delete','delete(bus-stop)','index(bus-attandence-list)','Attendance','list_busAttendence','filter_Attendance','index(NatureOfWork)','create(NatureOfWork)','view(NatureOfWork)','store(NatureOfWork)','Natureofwork_delete','delete(NatureOfWork)','index(driver-conductor-master)','create(driver-conductor-master)','view(driver-conductor-master)','store(driver-conductor-master)','delete(driver-conductor-master)','busstaff_delete','index(rtopaper)','create(rtopaper)','view(rtopaper)','store(rtopaper)','delete(rtopaper)','rtopaper_delete','index(maintenance-head-master)','view(maintenance-head-master)','editg(maintenance-head-master)','store(maintenance-head-master)','storeg(maintenance-head-master)','maintenancegroupmaster_delete','maintenanceheadpmaster_delete','delete(maintenance-head-master)','index(route-master)','view(route-master)','store(route-master)','delete(route-master)','route_name_delete','route_delete','view_bus','index(schedulemaster)','store(schedulemaster)','create(schedulemaster)','index(list-party-master)','list_party_master','view(list-party-master)','store(list_party_master)','party_master_delete','delete(list_party_master)','index(scholarbusassign)','create(scholarbusassign)','view(scholarbusassign)','store(scholarbusassign)','delete(scholarbusassign)','busstaff_delete','scholarbusassign_post_pickup','scholarbusassign_post_drop','index(teacherbusassign)','create(teacherbusassign)','view(teacherbusassign)','store(teacherbusassign)','delete(teacherbusassign)','busstaff_delete(teacherbusassign)','scholarbusassign_post_pickup(teacherbusassign)','scholarbusassign_post_drop(teacherbusassign)','index(bus_data)','bus_details','create(bus_data)','view(bus_data)','store(bus_data)','delete(bus_data)','scholarbusassign_post_pickup','busstaff_delete(bus_data)','scholarbusassign_post_drop','data_foredit_pickup'
        ];
        $academic = [
            'create(session)','index(session)','index(exammaste)','create(exammaste)','view(exammaste)','store(exammaste)','exam_master_delete','delete(exammaste)','index(examtype)','create(examtype)','view(examtype)','store(examtype)','examtype_delete','delete(examtype)','index(teachers)','create(teachers)','view(teachers)','store(teachers)','teaches_delete','delete(teachers)','index(marksheet)','index(marks)','create(marks)','view(marks)','store(marks)','marks_delete','delete(marks)','classstudentdata','index(AssignSubject)','create(AssignSubject)','view(AssignSubject)','store(AssignSubject)','AssignSubject_delete','delete(AssignSubject)','student_combination_data','index(subjectmaster)','create(subjectmaster)','view(subjectmaster)','store(subjectmaster)','subjects_delete','index(teachersubject)','create(teachersubject)','view(teachersubject)','store(teachersubject)','teachersubject_delete','delete(teachersubject)','getteachersandsubject','getteachersdata','teachersubject_copy','index(Attandencereports)','classattandence','index(dailyattandence)','array_unique','Attendance','index(primarygroup)','create(primarygroup)','view(primarygroup)','store(primarygroup)','primarygroup_master_delete','delete(primarygroup)','index(groupmaster)','create(groupmaster)','view(groupmaster)','store(groupmaster)','groupmaster_delete','delete(groupmaster)','index(headmaster)','create(headmaster)','view(headmaster)','store(headmaster)','headmaster_delete','delete(headmaster)','index(subheadmaster)','create(subheadmaster)','view(subheadmaster)','store(subheadmaster)','subheadmaster_delete','delete(subheadmaster)','index(greadingmaster)','create(greadingmaster)','view(greadingmaster)','store(greadingmaster)','grade_master_delete','delete(greadingmaster)','index(gread)','create(gread)','view(gread)','store(gread)','grade_delete','delete(gread)','index(calssese-assigne-to-teacher)','saveclassdata(calssese-assigne-to-teacher)','view(calssese-assigne-to-teacher)','store(calssese-assigne-to-teacher)','class_teacherdelete','index(streammaster)','create(streammaster)','view(streammaster)','store(streammaster)','stream_master_delete','delete(streammaster)','index(sectionmaster)','create(sectionmaster)','view(sectionmaster)','store(sectionmaster)','section_master_delete','delete(sectionmaster)','index(remarkmaster)','create(remarkmaster)','view(remarkmaster)','store(remarkmaster)','remarkmaster_delete','delete(remarkmaster)','index(subjectcombinatiomaster)','create(subjectcombinatiomaster)','view(subjectcombinatiomaster)','store(subjectcombinatiomaster)','subjectcombinatio_master_delete','subject_delete','delete(subjectcombinatiomaster)'
        ];
        $hrms = [
            'index(employee)','create(employee)','view(employee)','store(employee)','employee_delete','delete(employee)','index(department)','create(department)','view(department)','store(department)','department_delete','delete(department)','index(position)','create(position)','view(position)','store(position)','position_delete','delete(position)','index(attendance)','create(attendance)','view(attendance)','store(position)','attendance_delete','delete(attendance)','index(holidays)','create(holidays)','view(holidays)','store(holidays)','holidays_delete','delete(holidays)','index(salaries)','create(salaries)','view(salaries)','store(salaries)','salaries_delete','delete(salaries)','index(leaverequests)','create(leaverequests)','view(leaverequests)','store(leaverequests)','leaverequests_delete','delete(leaverequests)'
        ];
        $general = [
            'role-list','role-create','role-edit','role-delete','product-list','product-create','product-edit','product-delete',
            'create','edit','delete','view','store','index','saveclassdata'
        ];

        $permissions = array_merge($scholars, $fees, $transport, $academic, $hrms, $general);
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'staff'
                ]);
        }
    }
}