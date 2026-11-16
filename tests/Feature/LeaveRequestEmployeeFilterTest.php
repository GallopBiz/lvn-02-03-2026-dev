<?php

namespace Tests\Feature;

use App\Models\HrmsEmployee;
use App\Models\HrmsLeaveRequest;
use App\Models\HrmsLeaveType;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeaveRequestEmployeeFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.dynamic' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);
        DB::purge('sqlite');
        DB::purge('dynamic');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('student_registration', function (Blueprint $table) {
            $table->increments('id');
            $table->string('student_name')->nullable();
            $table->timestamps();
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('role_id');
        });

        Schema::create('role_menus', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id');
            $table->text('menu');
        });

        foreach (['sqlite', 'dynamic'] as $conn) {
            Schema::connection($conn)->create('hrms_employees', function (Blueprint $table) {
                $table->increments('id');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('employee_status')->default('active');
                $table->unsignedInteger('staff_type_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection($conn)->create('hrms_leave_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->boolean('is_paid')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection($conn)->create('hrms_leave_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('employee_id');
                $table->unsignedInteger('leave_type_id');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status')->default('Pending');
                $table->boolean('is_half_day')->default(false);
                $table->string('half_day_type')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection($conn)->create('hrms_staff_leave_allocation', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('hrms_staff_type_id')->nullable();
                $table->unsignedInteger('leave_type_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection($conn)->create('hrms_employee_leave_balances', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('employee_id');
                $table->unsignedInteger('leave_type_id');
                $table->decimal('balance', 8, 2)->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Role::create(['name' => 'Admin', 'guard_name' => 'web']);

        $mockNotification = \Mockery::mock(\App\Services\NotificationService::class)->shouldIgnoreMissing();
        $this->app->instance(\App\Services\NotificationService::class, $mockNotification);

        $mockStaffNotification = \Mockery::mock(\App\Services\StaffNotificationService::class)->shouldIgnoreMissing();
        $this->app->instance(\App\Services\StaffNotificationService::class, $mockStaffNotification);
    }

    public function test_leave_requests_index_excludes_inactive_and_retired_employees(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\CheckMenuPermission::class]);

        $activeEmp = HrmsEmployee::create([
            'first_name' => 'John',
            'last_name' => 'Active',
            'employee_status' => 'active',
        ]);

        $inactiveEmp = HrmsEmployee::create([
            'first_name' => 'Jane',
            'last_name' => 'Inactive',
            'employee_status' => 'inactive',
        ]);

        $retiredEmp = HrmsEmployee::create([
            'first_name' => 'Bob',
            'last_name' => 'Retired',
            'employee_status' => 'retired',
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);
        $admin->assignRole('Admin');
        $this->actingAs($admin, 'web');

        $response = $this->get(route('leaverequests'));
        $response->assertStatus(200);

        $employeesInView = $response->viewData('employees');
        $employeeIds = $employeesInView->pluck('id')->all();

        $this->assertContains($activeEmp->id, $employeeIds);
        $this->assertNotContains($inactiveEmp->id, $employeeIds);
        $this->assertNotContains($retiredEmp->id, $employeeIds);
    }

    public function test_leave_requests_view_includes_active_employees_and_the_current_employee(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\CheckMenuPermission::class]);

        $activeEmp = HrmsEmployee::create([
            'first_name' => 'Active',
            'last_name' => 'User',
            'employee_status' => 'active',
            'staff_type_id' => 1,
        ]);

        $inactiveOwner = HrmsEmployee::create([
            'first_name' => 'Old',
            'last_name' => 'Worker',
            'employee_status' => 'inactive',
            'staff_type_id' => 1,
        ]);

        $otherInactive = HrmsEmployee::create([
            'first_name' => 'Another',
            'last_name' => 'Inactive',
            'employee_status' => 'inactive',
            'staff_type_id' => 1,
        ]);

        $leaveType = HrmsLeaveType::create([
            'name' => 'Casual Leave',
            'is_paid' => true,
        ]);

        $leaveRequest = HrmsLeaveRequest::create([
            'employee_id' => $inactiveOwner->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-02',
            'status' => 'Pending',
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);
        $admin->assignRole('Admin');
        $this->actingAs($admin, 'web');

        $response = $this->get(url('view-leaverequests/' . $leaveRequest->id));
        $response->assertStatus(200);

        $employeesInView = $response->viewData('employees');
        $employeeIds = $employeesInView->pluck('id')->all();

        $this->assertContains($activeEmp->id, $employeeIds);
        $this->assertContains($inactiveOwner->id, $employeeIds);
        $this->assertNotContains($otherInactive->id, $employeeIds);
    }

    public function test_leave_requests_view_blade_contains_select2_class_and_search_attribute(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\CheckMenuPermission::class]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);
        $admin->assignRole('Admin');
        $this->actingAs($admin, 'web');

        $response = $this->get(route('leaverequests'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('id="employee_id" class="form-control select2"', $content);
        $this->assertStringContainsString('data-placeholder="Select Employee"', $content);
    }
}
