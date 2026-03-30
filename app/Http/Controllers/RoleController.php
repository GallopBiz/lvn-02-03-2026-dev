<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

    
class RoleController extends Controller
{
    // Module Functions Arrays (must be inside class)
    protected static $Scholars_functions = [
        ['label' => 'filter_followup','functionname' => 'scholars-filter_followup'],
        ['label' => 'admin_preenquiryform','functionname' => 'scholars-admin_preenquiryform'],
        ['label' => 'adminpreenquiryform','functionname' => 'scholars-adminpreenquiryform'],
        ['label' => 'preenquiryviewlist','functionname' => 'scholars-preenquiryviewlist'],
        ['label' => 'enquiryform','functionname' => 'scholars-enquiryform'],
        ['label' => 'adminenquirylist','functionname' => 'scholars-adminenquirylist'],
        ['label' => 'enquiryviewlist','functionname' => 'scholars-enquiryviewlist'],
        ['label' => 'enquiryeditlist','functionname' => 'scholars-enquiryeditlist'],
        ['label' => 'index(enquiryrecipt)','functionname' => 'scholars-index'],
        ['label' => 'followupdate','functionname' => 'scholars-followupdate'],
        ['label' => 'selection_process','functionname' => 'scholars-selection_process'],
        ['label' => 'add_student_registrations','functionname' => 'scholars-add_student_registrations'],
        ['label' => 'student_registrations','functionname' => 'scholars-student_registrations'],
        ['label' => 'registrationviewlist','functionname' => 'scholars-registrationviewlist'],
        ['label' => 'registrationeditlist','functionname' => 'scholars-registrationeditlist'],
    ];

    protected static $Fees_functions = [
        ['label' => 'index(fees-types-master)','functionname' => 'fees-index'],
        ['label' => 'edit(fees-types-master-edit)','functionname' => 'fees-edit'],
        ['label' => 'index(bus-fees-master)','functionname' => 'fees-bus-index'],
        ['label' => 'edit(bus-fees-master-edit)','functionname' => 'fees-bus-edit'],
        ['label' => 'student_feesmaster','functionname' => 'fees-student_feesmaster'],
        ['label' => 'adminpreenquiryfeeslist','functionname' => 'fees-adminpreenquiryfeeslist'],
        ['label' => 'preenquiryviewlist','functionname' => 'fees-preenquiryviewlist'],
        ['label' => 'course_fees_structure_master_list','functionname' => 'fees-course_fees_structure_master_list'],
        ['label' => 'course_fees_head_orders_list','functionname' => 'fees-course_fees_head_orders_list'],
        ['label' => 'late_fees_master','functionname' => 'fees-late_fees_master'],
        ['label' => 'late_fees_master_edit','functionname' => 'fees-late_fees_master_edit'],
        ['label' => 'generate_due_chart','functionname' => 'fees-generate_due_chart'],
        ['label' => 'index(fees_receipt_challan)','functionname' => 'fees-receipt-index'],
        ['label' => 'student_ledger','functionname' => 'fees-student_ledger'],
        ['label' => 'student_ledger_delete','functionname' => 'fees-student_ledger_delete'],
        ['label' => 'search_cancle_student_ledger','functionname' => 'fees-search_cancle_student_ledger'],
        ['label' => 'index(defaulters_list)','functionname' => 'fees-defaulters-index'],
        ['label' => 'view(view_defaulters_list)','functionname' => 'fees-defaulters-view'],
        ['label' => 'index(classes)','functionname' => 'fees-classes-index'],
        ['label' => 'view(classes)','functionname' => 'fees-classes-view'],
        ['label' => 'store(classes)','functionname' => 'fees-classes-store'],
        ['label' => 'classname_delete','functionname' => 'fees-classname_delete'],
        ['label' => 'classes_delete','functionname' => 'fees-classes_delete'],
        ['label' => 'editclasses','functionname' => 'fees-editclasses'],
        ['label' => 'delete(classes)','functionname' => 'fees-classes-delete'],
        ['label' => 'storeg','functionname' => 'storeg'],
        ['label' => 'editg','functionname' => 'editg'],
        ['label' => 'index(collection)','functionname' => 'index'],
    ];

    protected static $Transport_functions = [
        ['label' => 'index(addvehical)','functionname' => 'transport-index-addvehical'],
        ['label' => 'addvehical','functionname' => 'transport-addvehical'],
        ['label' => 'list(addvehical)','functionname' => 'transport-list-addvehical'],   
        ['label' => 'view(addvehical)','functionname' => 'transport-view-addvehical'],
        ['label' => 'store(addvehical)','functionname' => 'transport-store-addvehical'],
    ];

    protected static $Academic_functions = [
        ['label' => 'store(greadingmaster)','functionname' => 'academic-store-greadingmaster'],
        ['label' => 'grade_master_delete','functionname' => 'academic-grade_master_delete'],
        ['label' => 'delete(greadingmaster)','functionname' => 'academic-delete-greadingmaster'],
        ['label' => 'index(gread)','functionname' => 'academic-index-gread'],
        ['label' => 'create(gread)','functionname' => 'academic-create-gread'],
        ['label' => 'view(gread)','functionname' => 'academic-view-gread'],
        ['label' => 'store(gread)','functionname' => 'academic-store-gread'],
        ['label' => 'grade_delete','functionname' => 'academic-grade_delete'],
        ['label' => 'delete(gread)','functionname' => 'academic-delete-gread'],
        ['label' => 'index(calssese-assigne-to-teacher)','functionname' => 'academic-index-calssese-assigne-to-teacher'],
        ['label' => 'saveclassdata(calssese-assigne-to-teacher)','functionname' => 'academic-saveclassdata-calssese-assigne-to-teacher'],
        ['label' => 'view(calssese-assigne-to-teacher)','functionname' => 'academic-view-calssese-assigne-to-teacher'],
        ['label' => 'store(calssese-assigne-to-teacher)','functionname' => 'academic-store-calssese-assigne-to-teacher'],
        ['label' => 'class_teacherdelete','functionname' => 'academic-class_teacherdelete'],
        ['label' => 'index(streammaster)','functionname' => 'academic-index-streammaster'],
        ['label' => 'create(streammaster)','functionname' => 'academic-create-streammaster'],
        ['label' => 'view(streammaster)','functionname' => 'academic-view-streammaster'],
        ['label' => 'store(streammaster)','functionname' => 'academic-store-streammaster'],
        ['label' => 'stream_master_delete','functionname' => 'academic-stream_master_delete'],
        ['label' => 'delete(streammaster)','functionname' => 'academic-delete-streammaster'],
        ['label' => 'index(sectionmaster)','functionname' => 'academic-index-sectionmaster'],
        ['label' => 'create(sectionmaster)','functionname' => 'academic-create-sectionmaster'],
        ['label' => 'view(sectionmaster)','functionname' => 'academic-view-sectionmaster'],
        ['label' => 'store(sectionmaster)','functionname' => 'academic-store-sectionmaster'],
        ['label' => 'section_master_delete','functionname' => 'academic-section_master_delete'],
        ['label' => 'delete(sectionmaster)','functionname' => 'academic-delete-sectionmaster'],
        ['label' => 'index(remarkmaster)','functionname' => 'academic-index-remarkmaster'],
        ['label' => 'create(remarkmaster)','functionname' => 'academic-create-remarkmaster'],
        ['label' => 'view(remarkmaster)','functionname' => 'academic-view-remarkmaster'],
        ['label' => 'store(remarkmaster)','functionname' => 'academic-store-remarkmaster'],
        ['label' => 'remarkmaster_delete','functionname' => 'academic-remarkmaster_delete'],
        ['label' => 'delete(remarkmaster)','functionname' => 'academic-delete-remarkmaster'],
        ['label' => 'index(subjectcombinatiomaster)','functionname' => 'academic-index-subjectcombinatiomaster'],
        ['label' => 'create(subjectcombinatiomaster)','functionname' => 'academic-create-subjectcombinatiomaster'],
        ['label' => 'view(subjectcombinatiomaster)','functionname' => 'academic-view-subjectcombinatiomaster'],
        ['label' => 'store(subjectcombinatiomaster)','functionname' => 'academic-store-subjectcombinatiomaster'],
        ['label' => 'subjectcombinatio_master_delete','functionname' => 'academic-subjectcombinatio_master_delete'],
        ['label' => 'subject_delete','functionname' => 'academic-subject_delete'],
        ['label' => 'delete(subjectcombinatiomaster)','functionname' => 'academic-delete-subjectcombinatiomaster'],
    ];

    protected static $hrms_functions = [
        ['label' => 'index(employee)','functionname' => 'hrms-index-employee'],
        ['label' => 'create(employee)','functionname' => 'hrms-create-employee'],
        ['label' => 'view(employee)','functionname' => 'hrms-view-employee'],
        ['label' => 'store(employee)','functionname' => 'hrms-store-employee'],
        ['label' => 'employee_delete','functionname' => 'hrms-employee_delete'],
        ['label' => 'delete(employee)','functionname' => 'hrms-delete-employee'],
        ['label' => 'index(department)','functionname' => 'hrms-index-department'],
        ['label' => 'create(department)','functionname' => 'hrms-create-department'],
        ['label' => 'view(department)','functionname' => 'hrms-view-department'],
        ['label' => 'store(department)','functionname' => 'hrms-store-department'],
        ['label' => 'department_delete','functionname' => 'hrms-department_delete'],
        ['label' => 'delete(department)','functionname' => 'hrms-delete-department'],
        ['label' => 'index(position)','functionname' => 'hrms-index-position'],
        ['label' => 'create(position)','functionname' => 'hrms-create-position'],
        ['label' => 'view(position)','functionname' => 'hrms-view-position'],
        ['label' => 'store(position)','functionname' => 'hrms-store-position'],
        ['label' => 'position_delete','functionname' => 'hrms-position_delete'],
        ['label' => 'delete(position)','functionname' => 'hrms-delete-position'],
        ['label' => 'index(attendance)','functionname' => 'hrms-index-attendance'],
        ['label' => 'create(attendance)','functionname' => 'hrms-create-attendance'],
        ['label' => 'view(attendance)','functionname' => 'hrms-view-attendance'],
        ['label' => 'store(position)','functionname' => 'hrms-store-position'],
        ['label' => 'attendance_delete','functionname' => 'hrms-attendance_delete'],
        ['label' => 'delete(attendance)','functionname' => 'hrms-delete-attendance'],
        ['label' => 'index(holidays)','functionname' => 'hrms-index-holidays'],
        ['label' => 'create(holidays)','functionname' => 'hrms-create-holidays'],
        ['label' => 'view(holidays)','functionname' => 'hrms-view-holidays'],
        ['label' => 'store(holidays)','functionname' => 'hrms-store-holidays'],
        ['label' => 'holidays_delete','functionname' => 'hrms-holidays_delete'],
        ['label' => 'delete(holidays)','functionname' => 'hrms-delete-holidays'],
        ['label' => 'index(salaries)','functionname' => 'hrms-index-salaries'],
        ['label' => 'create(salaries)','functionname' => 'hrms-create-salaries'],
        ['label' => 'view(salaries)','functionname' => 'hrms-view-salaries'],
        ['label' => 'store(salaries)','functionname' => 'hrms-store-salaries'],
        ['label' => 'salaries_delete','functionname' => 'hrms-salaries_delete'],
        ['label' => 'delete(salaries)','functionname' => 'hrms-delete-salaries'],
        ['label' => 'index(leaverequests)','functionname' => 'hrms-index-leaverequests'],
        ['label' => 'create(leaverequests)','functionname' => 'hrms-create-leaverequests'],
        ['label' => 'view(leaverequests)','functionname' => 'hrms-view-leaverequests'],
        ['label' => 'store(leaverequests)','functionname' => 'hrms-store-leaverequests'],
        ['label' => 'leaverequests_delete','functionname' => 'hrms-leaverequests_delete'],
        ['label' => 'delete(leaverequests)','functionname' => 'hrms-delete-leaverequests'],
    ];
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

        // Use the full arrays defined as class constants below
        $Scholars_functions = self::$Scholars_functions;
        $Fees_functions = self::$Fees_functions;
        $Transport_functions = self::$Transport_functions;
        $Academic_functions = self::$Academic_functions;
        $hrms_functions = self::$hrms_functions;

        return view('roles.create', compact(
            'permission',
            'Scholars_functions',
            'Fees_functions',
            'Transport_functions',
            'Academic_functions',
            'hrms_functions'
        ));
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
        // Get assigned permission names for this role
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->pluck('name')
            ->toArray();

        // Pass module functions as variables
        $Scholars_functions = Scholars_functions;
        $Fees_functions = Fees_functions;
        $Transport_functions = Transport_functions;
        $Academic_functions = Academic_functions;
        $hrms_functions = hrms_functions;

        // Load menu config and saved menu for this role
        $menu = config('sidebar');
        $roleMenu = \App\Models\RoleMenu::where('role_id', $id)->first();
        $selectedMenu = $roleMenu ? json_decode($roleMenu->menu, true) : [];

        return view('roles.edit', compact(
            'role','permission','rolePermissions',
            'Scholars_functions', 'Fees_functions', 'Transport_functions', 'Academic_functions', 'hrms_functions',
            'menu', 'selectedMenu'
        ));
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
            'permission' => 'array', // allow empty
        ]);

        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();

        // Debug: log received permissions
        \Log::info('Role update permissions:', [
            'role_id' => $id,
            'permissions' => $request->input('permission', [])
        ]);

        // Debug: log role guard_name
        \Log::info('Role guard_name:', [
            'role_id' => $id,
            'guard_name' => $role->guard_name
        ]);

        // Debug: log permissions guard_name
        $permissionNames = $request->input('permission', []);
        $permissions = \Spatie\Permission\Models\Permission::whereIn('name', $permissionNames)->get();
        $permissionGuardNames = $permissions->pluck('guard_name')->unique()->toArray();
        \Log::info('Permissions guard_names:', [
            'permission_names' => $permissionNames,
            'guard_names' => $permissionGuardNames
        ]);


        // Extra debug: log permission IDs and SQL errors
        try {
            $permissionIds = $permissions->pluck('id')->toArray();
            \Log::info('Permission IDs to sync:', [
                'role_id' => $id,
                'permission_ids' => $permissionIds,
                'permission_names' => $permissionNames
            ]);
            // Always sync permissions, even if empty
            $role->syncPermissions($permissionNames);
        } catch (\Exception $e) {
            \Log::error('Error syncing permissions:', [
                'role_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Debug: log permissions in DB after update
        $dbPermissions = $role->permissions()->pluck('name')->toArray();
        \Log::info('Role DB permissions after update:', [
            'role_id' => $id,
            'db_permissions' => $dbPermissions
        ]);

        // Save sidebar menu selection as JSON in role_menus table
        $menuSelection = $request->input('menu', []);
        \App\Models\RoleMenu::updateOrCreate(
            ['role_id' => $id],
            ['menu' => json_encode($menuSelection)]
        );

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
