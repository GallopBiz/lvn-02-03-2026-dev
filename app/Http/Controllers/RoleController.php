<?php
    
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

const Scholars_functions = [
    ['label' => 'filter_followup','functionname' => 'filter_followup'],
    ['label' => 'admin_preenquiryform','functionname' => 'admin_preenquiryform'],
    ['label' => 'adminpreenquiryform','functionname' => 'adminpreenquiryform'],
    ['label' => 'preenquiryviewlist','functionname' => 'preenquiryviewlist'],
    ['label' => 'enquiryform','functionname' => 'enquiryform'],
    ['label' => 'adminenquirylist','functionname' => 'adminenquirylist'],
    ['label' => 'enquiryviewlist','functionname' => 'enquiryviewlist'],
    ['label' => 'enquiryeditlist','functionname' => 'enquiryeditlist'],
    ['label' => 'index(enquiryrecipt)','functionname' => 'index'],
    ['label' => 'followupdate','functionname' => 'followupdate'],
    ['label' => 'selection_process','functionname' => 'selection_process'],
    ['label' => 'add_student_registrations','functionname' => 'add_student_registrations'],
    ['label' => 'student_registrations','functionname' => 'student_registrations'],
    ['label' => 'registrationviewlist','functionname' => 'registrationviewlist'],
    ['label' => 'registrationeditlist','functionname' => 'registrationeditlist'],
    
    // ['label' => 'registrationeditlist','functionname' => 'registrationeditlist'],
    // ['label' => 'registrationeditlist','functionname' => 'registrationeditlist'],
    // ['label' => 'registrationeditlist','functionname' => 'registrationeditlist'],
    // ['label' => 'registrationeditlist','functionname' => 'registrationeditlist'],
    // ['label' => 'registrationeditlist','functionname' => 'registrationeditlist'],
    
];

const Fees_functions = [
    ['label' => 'index(fees-types-master)','functionname' => 'index'],
    ['label' => 'edit(fees-types-master-edit)','functionname' => 'edit'],
    ['label' => 'index(bus-fees-master)','functionname' => 'index'],
    ['label' => 'edit(bus-fees-master-edit)','functionname' => 'edit'],
    ['label' => 'student_feesmaster','functionname' => 'student_feesmaster'],
    ['label' => 'adminpreenquiryfeeslist','functionname' => 'adminpreenquiryfeeslist'],
    ['label' => 'preenquiryviewlist','functionname' => 'preenquiryviewlist'],
    ['label' => 'course_fees_structure_master_list','functionname' => 'course_fees_structure_master_list'],
    ['label' => 'course_fees_head_orders_list','functionname' => 'course_fees_head_orders_list'],
    ['label' => 'late_fees_master','functionname' => 'late_fees_master'],
    ['label' => 'late_fees_master_edit','functionname' => 'late_fees_master_edit'],
    ['label' => 'generate_due_chart','functionname' => 'generate_due_chart'],
    ['label' => 'index(fees_receipt_challan)','functionname' => 'index'],
    ['label' => 'student_ledger','functionname' => 'student_ledger'],
    ['label' => 'student_ledger_delete','functionname' => 'student_ledger_delete'],
    ['label' => 'search_cancle_student_ledger','functionname' => 'search_cancle_student_ledger'],
    ['label' => 'index(defaulters_list)','functionname' => 'index'],
    ['label' => 'view(view_defaulters_list)','functionname' => 'view'],
    ['label' => 'index(classes)','functionname' => 'index'],
    ['label' => 'view(classes)','functionname' => 'view'],
    ['label' => 'store(classes)','functionname' => 'store'],
    ['label' => 'classname_delete','functionname' => 'classname_delete'],
    ['label' => 'classes_delete','functionname' => 'classes_delete'],
    ['label' => 'editclasses','functionname' => 'editclasses'],
    ['label' => 'delete(classes)','functionname' => 'delete'],
    ['label' => 'storeg','functionname' => 'storeg'],
    ['label' => 'editg','functionname' => 'editg'],
    ['label' => 'index(collection)','functionname' => 'index'],
];

const Transport_functions = [
    ['label' => 'index(addvehical)','functionname' => 'index'],
    ['label' => 'addvehical','functionname' => 'addvehical'],
    ['label' => 'list(addvehical)','functionname' => 'list'],   
    ['label' => 'view(addvehical)','functionname' => 'view'],
    ['label' => 'store(addvehical)','functionname' => 'store'],
    ['label' => 'busstaff','functionname' => 'busstaff'],
    ['label' => 'addvehical_delete','functionname' => 'addvehical_delete'],
    ['label' => 'registerstaff','functionname' => 'registerstaff'],
    ['label' => 'index(area-master)','functionname' => 'index'],
    ['label' => 'view(area-master)','functionname' => 'view'],
    ['label' => 'store(area-master)','functionname' => 'store'],
    ['label' => 'delete(area-master)','functionname' => 'delete'],
    ['label' => 'index(bus-stop)','functionname' => 'index'],
    ['label' => 'store(bus-stop)','functionname' => 'store'],
    ['label' => 'view(bus-stop)','functionname' => 'view'],
    ['label' => 'create(bus-stop)','functionname' => 'create'],
    ['label' => 'busstop_delete','functionname' => 'busstop_delete'],
    ['label' => 'bs_soft_delete','functionname' => 'bs_soft_delete'],
    ['label' => 'delete(bus-stop)','functionname' => 'delete'],
    ['label' => 'index(bus-attandence-list)','functionname' => 'filter_followup'],
    ['label' => 'Attendance','functionname' => 'Attendance'],
    ['label' => 'list_busAttendence','functionname' => 'list_busAttendence'],
    ['label' => 'filter_Attendance','functionname' => 'filter_Attendance'],
    ['label' => 'index(NatureOfWork)','functionname' => 'index'],
    ['label' => 'create(NatureOfWork)','functionname' => 'create'],
    ['label' => 'view(NatureOfWork)','functionname' => 'create'],
    ['label' => 'store(NatureOfWork)','functionname' => 'store'],
    ['label' => 'Natureofwork_delete','functionname' => 'Natureofwork_delete'],
    ['label' => 'delete(NatureOfWork)','functionname' => 'delete'],
    ['label' => 'index(driver-conductor-master)','functionname' => 'index'],
    ['label' => 'create(driver-conductor-master)','functionname' => 'create'],
    ['label' => 'view(driver-conductor-master)','functionname' => 'view'],
    ['label' => 'store(driver-conductor-master)','functionname' => 'store'],
    ['label' => 'delete(driver-conductor-master)','functionname' => 'delete'],
    ['label' => 'busstaff_delete','functionname' => 'busstaff_delete'],
    ['label' => 'index(rtopaper)','functionname' => 'index'],
    ['label' => 'create(rtopaper)','functionname' => 'create'],
    ['label' => 'view(rtopaper)','functionname' => 'view'],
    ['label' => 'store(rtopaper)','functionname' => 'store'],
    ['label' => 'delete(rtopaper)','functionname' => 'delete'],
    ['label' => 'rtopaper_delete','functionname' => 'rtopaper_delete'],
    ['label' => 'index(maintenance-head-master)','functionname' => 'index'],
    ['label' => 'view(maintenance-head-master)','functionname' => 'view'],
    ['label' => 'editg(maintenance-head-master)','functionname' => 'editg'],
    ['label' => 'store(maintenance-head-master)','functionname' => 'store'],
    ['label' => 'storeg(maintenance-head-master)','functionname' => 'storeg'],
    ['label' => 'maintenancegroupmaster_delete','functionname' => 'maintenancegroupmaster_delete'],
    ['label' => 'maintenanceheadpmaster_delete','functionname' => 'maintenanceheadpmaster_delete'],
    ['label' => 'delete(maintenance-head-master)','functionname' => 'delete'],
    ['label' => 'index(route-master)','functionname' => 'index'],
    ['label' => 'view(route-master)','functionname' => 'view'],
    ['label' => 'store(route-master)','functionname' => 'store'],
    ['label' => 'delete(route-master)','functionname' => 'delete'],
    ['label' => 'route_name_delete','functionname' => 'route_name_delete'],
    ['label' => 'route_delete','functionname' => 'route_delete'],
    ['label' => 'view_bus','functionname' => 'view_bus'],
    ['label' => 'index(schedulemaster)','functionname' => 'index'],
    ['label' => 'store(schedulemaster)','functionname' => 'store'],
    ['label' => 'create(schedulemaster)','functionname' => 'create'],
    ['label' => 'index(list-party-master)','functionname' => 'index'],
    ['label' => 'list_party_master','functionname' => 'list_party_master'],
    ['label' => 'view(list-party-master)','functionname' => 'view'],
    ['label' => 'store(list_party_master)','functionname' => 'store'],
    ['label' => 'party_master_delete','functionname' => 'party_master_delete'],
    ['label' => 'delete(list_party_master)','functionname' => 'delete'],
    ['label' => 'index(scholarbusassign)','functionname' => 'index'],
    ['label' => 'create(scholarbusassign)','functionname' => 'create'],
    ['label' => 'view(scholarbusassign)','functionname' => 'view'],
    ['label' => 'store(scholarbusassign)','functionname' => 'store'],
    ['label' => 'delete(scholarbusassign)','functionname' => 'delete'],
    ['label' => 'busstaff_delete','functionname' => 'busstaff_delete'],
    ['label' => 'scholarbusassign_post_pickup','functionname' => 'scholarbusassign_post_pickup'],
    ['label' => 'scholarbusassign_post_drop','functionname' => 'scholarbusassign_post_drop'],
    ['label' => 'index(teacherbusassign)','functionname' => 'index'],
    ['label' => 'create(teacherbusassign)','functionname' => 'create'],
    ['label' => 'view(teacherbusassign)','functionname' => 'view'],
    ['label' => 'store(teacherbusassign)','functionname' => 'store'],
    ['label' => 'delete(teacherbusassign)','functionname' => 'delete'],
    ['label' => 'busstaff_delete(teacherbusassign)','functionname' => 'busstaff_delete'],
    ['label' => 'scholarbusassign_post_pickup(teacherbusassign)','functionname' => 'scholarbusassign_post_pickup'],
    ['label' => 'scholarbusassign_post_drop(teacherbusassign)','functionname' => 'scholarbusassign_post_drop'],
    ['label' => 'index(bus_data)','functionname' => 'index'],
    ['label' => 'bus_details','functionname' => 'bus_details'],
    ['label' => 'create(bus_data)','functionname' => 'create'],
    ['label' => 'view(bus_data)','functionname' => 'view'],
    ['label' => 'store(bus_data)','functionname' => 'store'],
    ['label' => 'delete(bus_data)','functionname' => 'delete'],
    ['label' => 'scholarbusassign_post_pickup','functionname' => 'scholarbusassign_post_pickup'],
    ['label' => 'busstaff_delete(bus_data)','functionname' => 'busstaff_delete'],
    ['label' => 'scholarbusassign_post_drop','functionname' => 'scholarbusassign_post_drop'],
    ['label' => 'data_foredit_pickup','functionname' => 'data_foredit_pickup']
];

const Academic_functions = [
    ['label' => 'create(session)','functionname' => 'create'],
    ['label' => 'index(session)','functionname' => 'index'],
    ['label' => 'index(exammaste)','functionname' => 'index'],
    ['label' => 'create(exammaste)','functionname' => 'create'],
    ['label' => 'view(exammaste)','functionname' => 'view'],
    ['label' => 'store(exammaste)','functionname' => 'store'],
    ['label' => 'exam_master_delete','functionname' => 'exam_master_delete'],
    ['label' => 'delete(exammaste)','functionname' => 'delete'],

    ['label' => 'index(examtype)','functionname' => 'index'],
    ['label' => 'create(examtype)','functionname' => 'create'],
    ['label' => 'view(examtype)','functionname' => 'view'],
    ['label' => 'store(examtype)','functionname' => 'store'],
    ['label' => 'examtype_delete','functionname' => 'examtype_delete'],
    ['label' => 'delete(examtype)','functionname' => 'delete'],

    ['label' => 'index(teachers)','functionname' => 'index'],
    ['label' => 'create(teachers)','functionname' => 'create'],
    ['label' => 'view(teachers)','functionname' => 'view'],
    ['label' => 'store(teachers)','functionname' => 'store'],
    ['label' => 'teaches_delete','functionname' => 'teaches_delete'],
    ['label' => 'delete(teachers)','functionname' => 'delete'],
    ['label' => 'index(marksheet)','functionname' => 'index'],


    ['label' => 'index(marks)','functionname' => 'index'],
    ['label' => 'create(marks)','functionname' => 'create'],
    ['label' => 'view(marks)','functionname' => 'view'],
    ['label' => 'store(marks)','functionname' => 'store'],
    ['label' => 'marks_delete','functionname' => 'marks_delete'],
    ['label' => 'delete(marks)','functionname' => 'delete'],
    ['label' => 'classstudentdata','functionname' => 'classstudentdata'],

    ['label' => 'index(AssignSubject)','functionname' => 'index'],
    ['label' => 'create(AssignSubject)','functionname' => 'create'],
    ['label' => 'view(AssignSubject)','functionname' => 'view'],
    ['label' => 'store(AssignSubject)','functionname' => 'store'],
    ['label' => 'AssignSubject_delete','functionname' => 'AssignSubject_delete'],
    ['label' => 'delete(AssignSubject)','functionname' => 'delete'],

    ['label' => 'student_combination_data','functionname' => 'student_combination_data'],

    ['label' => 'index(subjectmaster)','functionname' => 'index'],
    ['label' => 'create(subjectmaster)','functionname' => 'create'],
    ['label' => 'view(subjectmaster)','functionname' => 'view'],
    ['label' => 'store(subjectmaster)','functionname' => 'store'],
    ['label' => 'subjects_delete','functionname' => 'subjects_delete'],
    
    ['label' => 'index(teachersubject)','functionname' => 'index'],
    ['label' => 'create(teachersubject)','functionname' => 'create'],
    ['label' => 'view(teachersubject)','functionname' => 'view'],
    ['label' => 'store(teachersubject)','functionname' => 'store'],
    ['label' => 'teachersubject_delete','functionname' => 'teachersubject_delete'],
    ['label' => 'delete(teachersubject)','functionname' => 'delete'],


    ['label' => 'getteachersandsubject','functionname' => 'getteachersandsubject'],
    ['label' => 'getteachersdata','functionname' => 'getteachersdata'],
    ['label' => 'teachersubject_copy','functionname' => 'teachersubject_copy'],
    ['label' => 'index(Attandencereports)','functionname' => 'index'],

    ['label' => 'classattandence','functionname' => 'classattandence'],


    ['label' => 'index(dailyattandence)','functionname' => 'index'],
    ['label' => 'array_unique','functionname' => 'array_unique'],
    ['label' => 'Attendance','functionname' => 'Attendance'],


    ['label' => 'index(primarygroup)','functionname' => 'index'],
    ['label' => 'create(primarygroup)','functionname' => 'create'],
    ['label' => 'view(primarygroup)','functionname' => 'view'],
    ['label' => 'store(primarygroup)','functionname' => 'store'],
    ['label' => 'primarygroup_master_delete','functionname' => 'primarygroup_master_delete'],
    ['label' => 'delete(primarygroup)','functionname' => 'delete'],

    ['label' => 'index(groupmaster)','functionname' => 'index'],
    ['label' => 'create(groupmaster)','functionname' => 'create'],
    ['label' => 'view(groupmaster)','functionname' => 'view'],
    ['label' => 'store(groupmaster)','functionname' => 'store'],
    ['label' => 'groupmaster_delete','functionname' => 'groupmaster_delete'],
    ['label' => 'delete(groupmaster)','functionname' => 'delete'],

    ['label' => 'index(headmaster)','functionname' => 'index'],
    ['label' => 'create(headmaster)','functionname' => 'create'],
    ['label' => 'view(headmaster)','functionname' => 'view'],
    ['label' => 'store(headmaster)','functionname' => 'store'],
    ['label' => 'headmaster_delete','functionname' => 'headmaster_delete'],
    ['label' => 'delete(headmaster)','functionname' => 'delete'],


    ['label' => 'index(subheadmaster)','functionname' => 'index'],
    ['label' => 'create(subheadmaster)','functionname' => 'create'],
    ['label' => 'view(subheadmaster)','functionname' => 'view'],
    ['label' => 'store(subheadmaster)','functionname' => 'store'],
    ['label' => 'subheadmaster_delete','functionname' => 'subheadmaster_delete'],
    ['label' => 'delete(subheadmaster)','functionname' => 'delete'],

    ['label' => 'index(greadingmaster)','functionname' => 'index'],
    ['label' => 'create(greadingmaster)','functionname' => 'create'],
    ['label' => 'view(greadingmaster)','functionname' => 'view'],
    ['label' => 'store(greadingmaster)','functionname' => 'store'],
    ['label' => 'grade_master_delete','functionname' => 'grade_master_delete'],
    ['label' => 'delete(greadingmaster)','functionname' => 'delete'],


    ['label' => 'index(gread)','functionname' => 'index'],
    ['label' => 'create(gread)','functionname' => 'create'],
    ['label' => 'view(gread)','functionname' => 'view'],
    ['label' => 'store(gread)','functionname' => 'store'],
    ['label' => 'grade_delete','functionname' => 'grade_delete'],
    ['label' => 'delete(gread)','functionname' => 'delete'],

    
    ['label' => 'index(calssese-assigne-to-teacher)','functionname' => 'index'],
    ['label' => 'saveclassdata(calssese-assigne-to-teacher)','functionname' => 'saveclassdata'],
    ['label' => 'view(calssese-assigne-to-teacher)','functionname' => 'view'],
    ['label' => 'store(calssese-assigne-to-teacher)','functionname' => 'store'],
    ['label' => 'class_teacherdelete','functionname' => 'class_teacherdelete'],


    ['label' => 'index(streammaster)','functionname' => 'index'],
    ['label' => 'create(streammaster)','functionname' => 'create'],
    ['label' => 'view(streammaster)','functionname' => 'view'],
    ['label' => 'store(streammaster)','functionname' => 'store'],
    ['label' => 'stream_master_delete','functionname' => 'stream_master_delete'],
    ['label' => 'delete(streammaster)','functionname' => 'delete'],

    ['label' => 'index(sectionmaster)','functionname' => 'index'],
    ['label' => 'create(sectionmaster)','functionname' => 'create'],
    ['label' => 'view(sectionmaster)','functionname' => 'view'],
    ['label' => 'store(sectionmaster)','functionname' => 'store'],
    ['label' => 'section_master_delete','functionname' => 'section_master_delete'],
    ['label' => 'delete(sectionmaster)','functionname' => 'delete'],

    ['label' => 'index(remarkmaster)','functionname' => 'index'],
    ['label' => 'create(remarkmaster)','functionname' => 'create'],
    ['label' => 'view(remarkmaster)','functionname' => 'view'],
    ['label' => 'store(remarkmaster)','functionname' => 'store'],
    ['label' => 'remarkmaster_delete','functionname' => 'remarkmaster_delete'],
    ['label' => 'delete(remarkmaster)','functionname' => 'delete'],


    ['label' => 'index(subjectcombinatiomaster)','functionname' => 'index'],
    ['label' => 'create(subjectcombinatiomaster)','functionname' => 'create'],
    ['label' => 'view(subjectcombinatiomaster)','functionname' => 'view'],
    ['label' => 'store(subjectcombinatiomaster)','functionname' => 'store'],
    ['label' => 'subjectcombinatio_master_delete','functionname' => 'subjectcombinatio_master_delete'],
    ['label' => 'subject_delete','functionname' => 'subject_delete'],
    ['label' => 'delete(subjectcombinatiomaster)','functionname' => 'delete'],
];

const hrms_functions = [
    
    ['label' => 'index(employee)','functionname' => 'index'],
    ['label' => 'create(employee)','functionname' => 'create'],
    ['label' => 'view(employee)','functionname' => 'view'],
    ['label' => 'store(employee)','functionname' => 'store'],
    ['label' => 'employee_delete','functionname' => 'employee_delete'],
    ['label' => 'delete(employee)','functionname' => 'delete'],


    ['label' => 'index(department)','functionname' => 'index'],
    ['label' => 'create(department)','functionname' => 'create'],
    ['label' => 'view(department)','functionname' => 'view'],
    ['label' => 'store(department)','functionname' => 'store'],
    ['label' => 'department_delete','functionname' => 'department_delete'],
    ['label' => 'delete(department)','functionname' => 'delete'],

    ['label' => 'index(position)','functionname' => 'index'],
    ['label' => 'create(position)','functionname' => 'create'],
    ['label' => 'view(position)','functionname' => 'view'],
    ['label' => 'store(position)','functionname' => 'store'],
    ['label' => 'position_delete','functionname' => 'position_delete'],
    ['label' => 'delete(position)','functionname' => 'delete'],

    ['label' => 'index(attendance)','functionname' => 'index'],
    ['label' => 'create(attendance)','functionname' => 'create'],
    ['label' => 'view(attendance)','functionname' => 'view'],
    ['label' => 'store(position)','functionname' => 'store'],
    ['label' => 'attendance_delete','functionname' => 'attendance_delete'],
    ['label' => 'delete(attendance)','functionname' => 'delete'],


    ['label' => 'index(holidays)','functionname' => 'index'],
    ['label' => 'create(holidays)','functionname' => 'create'],
    ['label' => 'view(holidays)','functionname' => 'view'],
    ['label' => 'store(holidays)','functionname' => 'store'],
    ['label' => 'holidays_delete','functionname' => 'holidays_delete'],
    ['label' => 'delete(holidays)','functionname' => 'delete'],


    ['label' => 'index(salaries)','functionname' => 'index'],
    ['label' => 'create(salaries)','functionname' => 'create'],
    ['label' => 'view(salaries)','functionname' => 'view'],
    ['label' => 'store(salaries)','functionname' => 'store'],
    ['label' => 'salaries_delete','functionname' => 'salaries_delete'],
    ['label' => 'delete(salaries)','functionname' => 'delete'],

    ['label' => 'index(leaverequests)','functionname' => 'index'],
    ['label' => 'create(leaverequests)','functionname' => 'create'],
    ['label' => 'view(leaverequests)','functionname' => 'view'],
    ['label' => 'store(leaverequests)','functionname' => 'store'],
    ['label' => 'leaverequests_delete','functionname' => 'leaverequests_delete'],
    ['label' => 'delete(leaverequests)','functionname' => 'delete'],

];
    
class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
         $this->middleware('permission:role-create', ['only' => ['create','store']]);
         $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $roles = Role::orderBy('id','DESC')->paginate(5);
        return view('roles.index',compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permission = Permission::get();
        $Scholars_functions = Scholars_functions;
        $Fees_functions = Fees_functions;
        $Transport_functions = Transport_functions;
        $Academic_functions = Academic_functions;
        $hrms_functions = hrms_functions;
        return view('roles.create',compact('permission', 'Scholars_functions', 'Fees_functions', 'Transport_functions', 'Academic_functions', 'hrms_functions'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);
    
        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));
    
        return redirect()->route('roles.index')
                        ->with('success','Role created successfully');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->get();
    
        return view('roles.show',compact('role','rolePermissions'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();
    
        return view('roles.edit',compact('role','permission','rolePermissions'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);
    
        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();
    
        $role->syncPermissions($request->input('permission'));
    
        return redirect()->route('roles.index')
                        ->with('success','Role updated successfully');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table("roles")->where('id',$id)->delete();
        return redirect()->route('roles.index')
                        ->with('success','Role deleted successfully');
    }
}