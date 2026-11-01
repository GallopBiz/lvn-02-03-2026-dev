<?php

namespace Tests\Feature;

use App\Services\PermissionService;
use App\Http\Middleware\CheckMenuPermission;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\View\Compilers\BladeCompiler;
use Tests\TestCase;

class RoleMenuPermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create('role_menus', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id');
            $table->text('menu');
        });

        PermissionService::forgetRolePermissions(5);
    }

    public function test_view_permission_does_not_grant_create_edit_or_delete(): void
    {
        $this->storeRoleMenu([
            'Academic > Examination & Grading > Class 11-12 Marksheet' => [
                'view' => true,
                'add' => false,
                'edit' => false,
                'delete' => false,
            ],
        ]);

        $staff = (object) ['role' => 'Academic', 'type' => 's'];

        $this->assertTrue(PermissionService::can('view', 'senior-marksheets', $staff));
        $this->assertFalse(PermissionService::can('add', 'senior-marksheets', $staff));
        $this->assertFalse(PermissionService::can('edit', 'senior-marksheets', $staff));
        $this->assertFalse(PermissionService::can('delete', 'senior-marksheets', $staff));
    }

    public function test_staff_can_view_only_their_own_leave_requests_without_role_menu_setup(): void
    {
        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
            public $employee_id = 42;
        };
        $staff->setAttribute('id', 7);
        $this->actingAs($staff, 'staff')->withSession(['auth_portal' => 'staff']);

        $request = Request::create('/leaverequests-staff', 'GET');
        $route = Route::getRoutes()->getByName('leaverequests.staff');
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);

        $this->assertTrue(PermissionService::can('view', 'HRMS > Leave Management > My Leave Requests', $staff));
        $this->assertFalse(PermissionService::can('edit', 'HRMS > Leave Management > My Leave Requests', $staff));
        $this->assertContains(
            'My Leave Requests',
            $this->flattenMenuTitles(PermissionService::filterSidebarForUser(config('sidebar'), $staff))
        );

        $request->setMethod('POST');
        $this->assertFalse(PermissionService::can('view', 'HRMS > Leave Management > My Leave Requests', $staff));

        $adminRequest = Request::create('/leaverequests', 'GET');
        $adminRoute = Route::getRoutes()->getByName('leaverequests');
        $adminRequest->setRouteResolver(fn () => $adminRoute);
        $this->app->instance('request', $adminRequest);
        $this->assertFalse(PermissionService::can('view', 'HRMS > Leave Management > Leave Requests', $staff));
    }

    public function test_staff_leave_page_bypasses_role_check_only_on_its_authenticated_staff_route(): void
    {
        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
            public $employee_id = 42;
        };
        $staff->setAttribute('id', 7);
        Auth::guard('staff')->setUser($staff);

        $request = Request::create('/leaverequests-staff', 'GET');
        $route = Route::getRoutes()->getByName('leaverequests.staff');
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);

        $response = (new CheckMenuPermission())->handle(
            $request,
            fn () => response('allowed'),
            'staff'
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('allowed', $response->getContent());
    }

    public function test_staff_can_view_only_their_own_comp_off_requests_without_role_menu_setup(): void
    {
        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
            public $employee_id = 42;
        };
        $staff->setAttribute('id', 7);
        $this->actingAs($staff, 'staff')->withSession(['auth_portal' => 'staff']);

        $request = Request::create('/compoffrequests-staff', 'GET');
        $route = Route::getRoutes()->getByName('compoffrequests.staff');
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);

        $this->assertTrue(PermissionService::can('view', 'HRMS > Comp Off > My Comp Off Requests', $staff));
        $this->assertFalse(PermissionService::can('edit', 'HRMS > Comp Off > My Comp Off Requests', $staff));
        $this->assertContains(
            'My Comp Off Requests',
            $this->flattenMenuTitles(PermissionService::filterSidebarForUser(config('sidebar'), $staff))
        );

        $response = (new CheckMenuPermission())->handle(
            $request,
            fn () => response('allowed'),
            'staff'
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('allowed', $response->getContent());
    }

    public function test_all_crud_permission_combinations_control_actions_and_sidebar_visibility(): void
    {
        $menuKey = 'Academic > Examination & Grading > Class 11-12 Marksheet';
        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
        };
        $staff->setAttribute('id', 1);
        $this->actingAs($staff, 'staff')->withSession(['auth_portal' => 'staff']);

        Route::middleware('web')->group(function () use ($menuKey) {
            Route::get('/permission-matrix/view', function () {
                return response('allowed');
            })->middleware('check.permission:view,' . $menuKey);
            Route::post('/permission-matrix/add', function () {
                return response('allowed');
            })->middleware('check.permission:add,' . $menuKey);
            Route::put('/permission-matrix/edit', function () {
                return response('allowed');
            })->middleware('check.permission:edit,' . $menuKey);
            Route::delete('/permission-matrix/delete', function () {
                return response('allowed');
            })->middleware('check.permission:delete,' . $menuKey);
        });

        $cases = [
            ['view' => true, 'add' => false, 'edit' => false, 'delete' => false],
            ['view' => true, 'add' => true, 'edit' => false, 'delete' => false],
            ['view' => true, 'add' => false, 'edit' => true, 'delete' => false],
            ['view' => true, 'add' => false, 'edit' => false, 'delete' => true],
            ['view' => true, 'add' => true, 'edit' => true, 'delete' => false],
            ['view' => true, 'add' => true, 'edit' => false, 'delete' => true],
            ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
            ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
        ];

        foreach ($cases as $permissions) {
            $this->storeRoleMenu([$menuKey => $permissions]);

            foreach (['view', 'add', 'edit', 'delete'] as $action) {
                $this->assertSame(
                    $permissions[$action],
                    PermissionService::can($action, 'senior-marksheets', $staff),
                    'Permission mismatch for ' . $action . ' with ' . json_encode($permissions)
                );
            }

            $visibleTitles = $this->flattenMenuTitles(
                PermissionService::filterSidebarForUser(config('sidebar'), $staff)
            );
            $this->assertSame(
                $permissions['view'],
                in_array('Class 11-12 Marksheet', $visibleTitles, true),
                'Sidebar visibility mismatch with ' . json_encode($permissions)
            );

            foreach ([
                'view' => fn () => $this->get('/permission-matrix/view'),
                'add' => fn () => $this->post('/permission-matrix/add'),
                'edit' => fn () => $this->put('/permission-matrix/edit'),
                'delete' => fn () => $this->delete('/permission-matrix/delete'),
            ] as $action => $request) {
                $response = $request();
                $this->assertSame(
                    $permissions[$action] ? 200 : 403,
                    $response->getStatusCode(),
                    'Backend mismatch for ' . $action . ' with ' . json_encode($permissions)
                );
            }
        }
    }

    public function test_action_blade_directive_hides_and_shows_controls_from_role_permissions(): void
    {
        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
        };
        $staff->setAttribute('id', 1);
        $this->actingAs($staff, 'staff')->withSession(['auth_portal' => 'staff']);

        $this->storeRoleMenu([
            'Academic > Examination & Grading > Class 11-12 Marksheet' => [
                'view' => true,
                'add' => false,
                'edit' => false,
                'delete' => false,
            ],
        ]);
        $viewOnly = BladeCompiler::render(
            "@canDelete('senior-marksheets')<button>Delete</button>@endcanDelete"
        );
        $this->assertStringNotContainsString('Delete', $viewOnly);

        $this->storeRoleMenu([
            'Academic > Examination & Grading > Class 11-12 Marksheet' => [
                'view' => true,
                'add' => false,
                'edit' => false,
                'delete' => true,
            ],
        ]);
        $withDelete = BladeCompiler::render(
            "@canDelete('senior-marksheets')<button>Delete</button>@endcanDelete"
        );
        $this->assertStringContainsString('Delete', $withDelete);

        $this->storeRoleMenu([
            'Academic > Attendance > Daily Attendance' => [
                'view' => true,
                'add' => false,
                'edit' => false,
                'delete' => false,
            ],
        ]);
        $dailyAttendanceViewOnly = BladeCompiler::render(
            "@canDelete('Academic > Attendance > Daily Attendance')<button>Delete</button>@endcanDelete"
        );
        $this->assertStringNotContainsString('Delete', $dailyAttendanceViewOnly);
    }

    public function test_legacy_menu_list_grants_visibility_only(): void
    {
        $this->storeRoleMenu([
            'Academic > Examination & Grading > Class 11-12 Marksheet',
        ]);

        $staff = (object) ['role' => 'Academic', 'type' => 's'];

        $this->assertTrue(PermissionService::can('view', 'senior-marksheets', $staff));
        $this->assertFalse(PermissionService::can('delete', 'senior-marksheets', $staff));
    }

    public function test_senior_marksheet_routes_resolve_to_independent_actions(): void
    {
        $this->assertSame(
            ['module' => 'Academic > Examination & Grading > Class 11-12 Marksheet', 'action' => 'view'],
            PermissionService::detectModuleAndActionFromRequest($this->requestFor('GET', 'academic/senior-marksheets'))
        );
        $this->assertSame(
            ['module' => 'Academic > Examination & Grading > Class 11-12 Marksheet', 'action' => 'add'],
            PermissionService::detectModuleAndActionFromRequest($this->requestFor('POST', 'academic/senior-marksheets/generate'))
        );
        $this->assertSame(
            ['module' => 'Academic > Examination & Grading > Class 11-12 Marksheet', 'action' => 'edit'],
            PermissionService::detectModuleAndActionFromRequest($this->requestFor('PUT', 'academic/senior-marksheets/records/1'))
        );
        $this->assertSame(
            ['module' => 'Academic > Examination & Grading > Class 11-12 Marksheet', 'action' => 'delete'],
            PermissionService::detectModuleAndActionFromRequest($this->requestFor('POST', 'academic/senior-marksheets/generated/bulk-delete'))
        );
    }

    public function test_legacy_crud_route_uses_its_unique_sidebar_controller_module(): void
    {
        $request = Request::create('/save-subject', 'POST');
        $route = Route::getRoutes()->match($request);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $this->assertSame(
            ['module' => 'Academic > Subjects & Teachers > Subject Master', 'action' => 'add'],
            PermissionService::detectModuleAndActionFromRequest($request)
        );
    }

    public function test_explicit_middleware_module_metadata_is_used_for_ambiguous_routes(): void
    {
        $this->assertSame(
            ['module' => 'Fees > Collection > Fees Receipt Challan', 'action' => 'delete'],
            PermissionService::detectModuleAndActionFromRequest(
                $this->requestFor('POST', 'legacy-delete', [
                    'check.permission:delete,Fees > Collection > Fees Receipt Challan',
                ])
            )
        );
    }

    public function test_staff_marks_assignment_lookup_routes_require_marks_view_permission(): void
    {
        $route = Route::getRoutes()->getByName('staff.getteachersandsubject');
        $request = Request::create('/staff/getteachersandsubject', 'POST');
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $this->assertSame(
            [
                'module' => 'Academic > Examination & Grading > Enter Marks',
                'action' => 'view',
            ],
            PermissionService::detectModuleAndActionFromRequest($request)
        );
    }

    public function test_staff_monthly_attendance_routes_share_summary_and_lock_permissions(): void
    {
        $lockMenu = 'Academic > Attendance > Lock Monthly Attendance';
        $this->storeRoleMenu([
            $lockMenu => [
                'view' => true,
                'add' => false,
                'edit' => false,
                'delete' => false,
            ],
        ]);
        $staff = (object) ['role' => 'Academic', 'type' => 's'];

        $reportRoute = Route::getRoutes()->getByName('staff.attendance.monthly-report');
        $reportRequest = Request::create('/staff/attendance/monthly-report', 'GET');
        $reportRequest->setRouteResolver(fn () => $reportRoute);
        $detected = PermissionService::detectModuleAndActionFromRequest($reportRequest);

        $this->assertSame('view', $detected['action']);
        $this->assertTrue(PermissionService::can($detected['action'], $detected['module'], $staff));

        $lockPageRoute = Route::getRoutes()->getByName('staff.attendance.monthly-lock');
        $lockPageRequest = Request::create('/staff/attendance/monthly-lock', 'GET');
        $lockPageRequest->setRouteResolver(fn () => $lockPageRoute);
        $detected = PermissionService::detectModuleAndActionFromRequest($lockPageRequest);

        $this->assertSame('view', $detected['action']);
        $this->assertTrue(PermissionService::can($detected['action'], $detected['module'], $staff));

        $this->storeRoleMenu([
            $lockMenu => [
                'view' => false,
                'add' => false,
                'edit' => true,
                'delete' => false,
            ],
        ]);
        $lockRoute = Route::getRoutes()->getByName('staff.attendance.monthly-lock.store');
        $lockRequest = Request::create('/staff/attendance/monthly-lock', 'POST');
        $lockRequest->setRouteResolver(fn () => $lockRoute);
        $detected = PermissionService::detectModuleAndActionFromRequest($lockRequest);

        $this->assertSame('edit', $detected['action']);
        $this->assertTrue(PermissionService::can($detected['action'], $detected['module'], $staff));
    }

    public function test_staff_lock_marks_routes_require_marks_edit_permission(): void
    {
        $menuKey = 'Academic > Examination & Grading > Enter Marks';
        $this->storeRoleMenu([
            $menuKey => [
                'view' => true,
                'add' => false,
                'edit' => true,
                'delete' => false,
            ],
        ]);
        $staff = (object) ['role' => 'Academic', 'type' => 's'];

        foreach ([
            'staff.lock-marks-entry' => '/staff/lock-marks-entry/123',
            'staff.lock-current-marks-entry' => '/staff/lock-current-marks-entry',
        ] as $routeName => $path) {
            $route = Route::getRoutes()->getByName($routeName);
            $request = Request::create($path, 'POST');
            $request->setRouteResolver(function () use ($route) {
                return $route;
            });

            $detected = PermissionService::detectModuleAndActionFromRequest($request);
            $this->assertSame(['module' => $menuKey, 'action' => 'edit'], $detected);
            $this->assertTrue(PermissionService::can($detected['action'], $detected['module'], $staff));
        }
    }

    public function test_staff_bulk_lock_route_requires_marks_edit_permission(): void
    {
        $menuKey = 'Academic > Examination & Grading > Enter Marks';
        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
        };
        $staff->setAttribute('id', 1);
        $this->actingAs($staff, 'staff')->withSession(['auth_portal' => 'staff']);

        $this->storeRoleMenu([
            $menuKey => [
                'view' => true,
                'add' => false,
                'edit' => true,
                'delete' => false,
            ],
        ]);
        $this->postJson('/staff/bulk-lock-marks-entries', [])->assertStatus(422);

        $this->storeRoleMenu([
            $menuKey => [
                'view' => true,
                'add' => false,
                'edit' => false,
                'delete' => false,
            ],
        ]);
        $this->postJson('/staff/bulk-lock-marks-entries', [])->assertForbidden();
    }

    public function test_admin_bulk_unlock_route_is_registered_and_validates_selected_ids(): void
    {
        $admin = new class extends Authenticatable
        {
            public $role = 'Admin';
            public $type = 'a';
        };
        $admin->setAttribute('id', 1);

        $this->actingAs($admin, 'web')
            ->postJson('/bulk-unlock-marks-entries', [])
            ->assertStatus(422);
    }

    public function test_staff_can_bulk_lock_owned_marks_entries_without_partial_updates(): void
    {
        $this->createDynamicMarksTable();
        DB::connection('dynamic')->table('previosly_saved_marks_entry')->insert([
            ['id' => 1, 'teacher_id' => 112, 'is_delete' => 0, 'is_locked' => 0],
            ['id' => 2, 'teacher_id' => 112, 'is_delete' => 0, 'is_locked' => 0],
            ['id' => 3, 'teacher_id' => 113, 'is_delete' => 0, 'is_locked' => 0],
        ]);
        $this->storeRoleMenu([
            'Academic > Examination & Grading > Enter Marks' => [
                'view' => true,
                'add' => false,
                'edit' => true,
                'delete' => false,
            ],
        ]);

        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
            public $employee_id = 112;
        };
        $staff->setAttribute('id', 7);
        $this->actingAs($staff, 'staff')->withSession(['auth_portal' => 'staff']);

        $this->postJson('/staff/bulk-lock-marks-entries', ['marks_ids' => [1, 2]])
            ->assertOk()
            ->assertJsonCount(2, 'updated_ids');

        $lockedEntries = DB::connection('dynamic')
            ->table('previosly_saved_marks_entry')
            ->whereIn('id', [1, 2])
            ->get();
        $this->assertCount(2, $lockedEntries);
        foreach ($lockedEntries as $entry) {
            $this->assertSame(1, (int) $entry->is_locked);
            $this->assertSame(7, (int) $entry->locked_by);
        }

        $this->postJson('/staff/bulk-lock-marks-entries', ['marks_ids' => [2, 3]])
            ->assertForbidden();
        $this->assertSame(
            0,
            (int) DB::connection('dynamic')->table('previosly_saved_marks_entry')->where('id', 3)->value('is_locked')
        );
    }

    public function test_admin_can_bulk_unlock_marks_entries(): void
    {
        $this->createDynamicMarksTable();
        DB::connection('dynamic')->table('previosly_saved_marks_entry')->insert([
            ['id' => 1, 'teacher_id' => 112, 'is_delete' => 0, 'is_locked' => 1, 'locked_by' => 7],
            ['id' => 2, 'teacher_id' => 113, 'is_delete' => 0, 'is_locked' => 1, 'locked_by' => 8],
        ]);
        $admin = new class extends Authenticatable
        {
            public $role = 'Admin';
            public $type = 'a';
        };
        $admin->setAttribute('id', 1);

        $this->actingAs($admin, 'web')
            ->postJson('/bulk-unlock-marks-entries', ['marks_ids' => [1, 2]])
            ->assertOk()
            ->assertJsonCount(2, 'updated_ids');

        $unlockedEntries = DB::connection('dynamic')
            ->table('previosly_saved_marks_entry')
            ->whereIn('id', [1, 2])
            ->get();
        $this->assertCount(2, $unlockedEntries);
        foreach ($unlockedEntries as $entry) {
            $this->assertSame(0, (int) $entry->is_locked);
            $this->assertNull($entry->locked_at);
            $this->assertNull($entry->locked_by);
        }
    }

    public function test_staff_cannot_delete_a_marksheet_with_view_permission_only(): void
    {
        $this->storeRoleMenu([
            'Academic > Examination & Grading > Class 11-12 Marksheet' => [
                'view' => true,
                'add' => false,
                'edit' => false,
                'delete' => false,
            ],
        ]);

        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
        };
        $staff->setAttribute('id', 1);

        $this->actingAs($staff, 'staff')
            ->withSession(['auth_portal' => 'staff'])
            ->delete('/academic/senior-marksheets/generated/123')
            ->assertForbidden();
    }

    public function test_staff_cannot_access_admin_dashboard_without_dashboard_permission(): void
    {
        $this->storeRoleMenu([
            'Academic > Examination & Grading > Class 11-12 Marksheet' => [
                'view' => true,
                'add' => false,
                'edit' => false,
                'delete' => false,
            ],
        ]);

        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
        };
        $staff->setAttribute('id', 1);

        $this->actingAs($staff, 'staff')
            ->withSession(['auth_portal' => 'staff'])
            ->get('/admin-dashboard')
            ->assertForbidden();
    }

    public function test_staff_portal_marker_does_not_fall_back_to_an_authenticated_web_user(): void
    {
        $admin = new class extends Authenticatable
        {
            public $role = 'Admin';
            public $type = 'a';
        };
        $admin->setAttribute('id', 1);

        $this->actingAs($admin, 'web')
            ->withSession(['auth_portal' => 'staff'])
            ->get('/academic/senior-marksheets')
            ->assertRedirect(route('staff.login'));
    }

    public function test_web_admin_keeps_web_context_when_both_guards_are_authenticated(): void
    {
        $admin = new class extends Authenticatable
        {
            public $role = 'Admin';
            public $type = 'a';
        };
        $admin->setAttribute('id', 1);
        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
        };
        $staff->setAttribute('id', 2);

        Route::middleware(['web', 'auth:web,staff'])->get('/permission-matrix/dual-guard', function () {
            return response(Auth::guard('web')->check() ? 'web' : 'staff');
        });

        $this->actingAs($admin, 'web');
        Auth::guard('staff')->setUser($staff);

        $this->get('/permission-matrix/dual-guard')->assertOk()->assertSeeText('web');
    }

    public function test_staff_ajax_mutations_return_forbidden_json_without_permission(): void
    {
        $this->storeRoleMenu([
            'Academic > Examination & Grading > Class 11-12 Marksheet' => [
                'view' => true,
                'add' => false,
                'edit' => false,
                'delete' => false,
            ],
        ]);
        $staff = new class extends Authenticatable
        {
            public $role = 'Academic';
            public $type = 's';
        };
        $staff->setAttribute('id', 1);

        $this->actingAs($staff, 'staff')
            ->withSession(['auth_portal' => 'staff'])
            ->postJson('/academic/senior-marksheets/generated/bulk-delete', ['marksheet_ids' => [123]])
            ->assertForbidden()
            ->assertJsonStructure(['error']);
    }

    public function test_admin_and_student_types_do_not_inherit_staff_privileges(): void
    {
        $admin = (object) ['role' => 'Academic', 'type' => 'a'];
        $student = (object) ['role' => 'Academic', 'type' => 's'];
        $this->assertTrue(PermissionService::isAdmin($admin));
        $this->assertFalse(PermissionService::isAdmin($student));
    }

    public function test_staff_role_named_admin_does_not_inherit_the_admin_bypass(): void
    {
        DB::table('roles')->insert(['id' => 6, 'name' => 'Admin']);
        DB::table('role_menus')->insert(['role_id' => 6, 'menu' => json_encode([])]);
        $staff = new class extends Authenticatable
        {
            public $role = 'Admin';
            public $type = 's';
        };
        $staff->setAttribute('id', 1);

        $this->actingAs($staff, 'staff')->withSession(['auth_portal' => 'staff']);

        $this->assertFalse(PermissionService::isAdmin($staff));
        $this->assertFalse(PermissionService::can('view', 'senior-marksheets', $staff));
    }

    public function test_staff_portal_urls_redirect_to_staff_login_when_session_expires(): void
    {
        $this->withSession(['auth_portal' => 'staff'])
            ->get('/academic/senior-marksheets')
            ->assertRedirect(route('staff.login'));
    }

    private function storeRoleMenu(array $menu): void
    {
        DB::table('roles')->updateOrInsert(['id' => 5], ['name' => 'Academic']);
        DB::table('role_menus')->updateOrInsert(
            ['role_id' => 5],
            ['menu' => json_encode($menu)]
        );
        PermissionService::forgetRolePermissions(5);
    }

    private function createDynamicMarksTable(): void
    {
        config([
            'database.connections.dynamic' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('dynamic');

        Schema::connection('dynamic')->create('previosly_saved_marks_entry', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('teacher_id');
            $table->boolean('is_delete')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->dateTime('locked_at')->nullable();
            $table->unsignedInteger('locked_by')->nullable();
            $table->timestamps();
        });
    }

    private function flattenMenuTitles(array $items): array
    {
        $titles = [];
        foreach ($items as $item) {
            $titles[] = $item['title'];
            if (!empty($item['children'])) {
                $titles = array_merge($titles, $this->flattenMenuTitles($item['children']));
            }
        }

        return $titles;
    }

    private function requestFor(string $method, string $path, array $middleware = []): Request
    {
        $request = Request::create('/' . $path, $method);
        $request->setRouteResolver(function () use ($middleware) {
            return new class($middleware)
            {
                private array $middleware;

                public function __construct(array $middleware)
                {
                    $this->middleware = $middleware;
                }

                public function getName(): string
                {
                    return '';
                }

                public function getActionMethod(): string
                {
                    return '';
                }

                public function getAction(string $key = null)
                {
                    return null;
                }

                public function gatherMiddleware(): array
                {
                    return $this->middleware;
                }

                public function getControllerClass(): ?string
                {
                    return null;
                }
            };
        });

        return $request;
    }
}
