<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Staff;
use App\Models\User;
use Tests\TestCase;

class CasteWiseReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.dynamic' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('dynamic');

        Schema::connection('dynamic')->create('student_registration', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_name')->nullable();
            $table->string('section_name')->nullable();
            $table->string('status')->nullable();
            $table->string('transfer_status')->nullable();
            $table->boolean('is_archived')->nullable();
            $table->text('json_str')->nullable();
        });
    }

    public function test_admin_can_view_classwise_caste_and_gender_totals_for_active_students(): void
    {
        $this->addStudent('06', 'r', false, null, ['gender' => 'Male', 'category' => 'SC']);
        $this->addStudent('06', 'r', false, null, ['gender' => 'Female', 'category' => 'OBC']);
        $this->addStudent('06', 'r', false, null, ['gender' => 'Other', 'category' => 'GEN']);
        $this->addStudent('06', 'r', false, null, ['gender' => 'Male', 'category' => 'UR']);
        $this->addStudent('07', 'r', false, null, ['gender' => 'Male', 'category' => 'UR']);
        $this->addStudent('06', 't', false, null, ['gender' => 'Male', 'category' => 'SC']);
        $this->addStudent('06', 'r', true, null, ['gender' => 'Male', 'category' => 'SC']);
        $this->addStudent('06', 'r', false, 'transferred', ['gender' => 'Male', 'category' => 'SC']);

        $admin = new class extends User
        {
            public $type = 'a';

            public function hasRole($roles, ?string $guard = null): bool
            {
                return false;
            }
        };

        $response = $this->actingAs($admin, 'web')
            ->get(route('academic.caste-wise-report'));

        $response->assertOk()
            ->assertViewIs('backend.AcademicsModules.caste_wise_report')
            ->assertDontSee('Other / N.S.')
            ->assertDontSee('Sem')
            ->assertViewHas('rows', function (array $rows) {
                $this->assertSame(['06', '07'], array_column($rows, 'class_name'));
                $this->assertSame([
                    'boys' => 2,
                    'girls' => 1,
                    'other' => 1,
                    'total' => 4,
                ], $rows[0]['overall']);
                $this->assertSame(1, $rows[0]['categories']['SC']['total']);
                $this->assertSame(1, $rows[0]['categories']['OBC']['total']);
                $this->assertSame(2, $rows[0]['categories']['GEN']['total']);
                $this->assertSame(1, $rows[1]['categories']['GEN']['total']);
                $this->assertSame(0, $rows[1]['categories']['OTHER']['total']);
                return true;
            });
    }

    public function test_caste_report_can_be_filtered_to_a_class(): void
    {
        $this->addStudent('06', 'r', false, null, ['gender' => 'Male', 'category' => 'SC']);
        $this->addStudent('07', 'r', false, null, ['gender' => 'Female', 'category' => 'OBC']);

        $admin = new class extends User
        {
            public $type = 'a';

            public function hasRole($roles, ?string $guard = null): bool
            {
                return false;
            }
        };

        $this->actingAs($admin, 'web')
            ->get(route('academic.caste-wise-report', ['class_name' => '07']))
            ->assertOk()
            ->assertViewHas('rows', function (array $rows) {
                $this->assertSame(['07'], array_column($rows, 'class_name'));
                $this->assertSame(1, $rows[0]['overall']['total']);
                $this->assertSame(1, $rows[0]['categories']['OBC']['total']);
                return true;
            });
    }

    public function test_numeric_class_names_are_displayed_as_roman_numerals_without_changing_filter_values(): void
    {
        $this->addStudent('Class 10', 'r', false, null, ['gender' => 'Male', 'category' => 'SC']);

        $admin = new class extends User
        {
            public $type = 'a';

            public function hasRole($roles, ?string $guard = null): bool
            {
                return false;
            }
        };

        $this->actingAs($admin, 'web')
            ->get(route('academic.caste-wise-report'))
            ->assertOk()
            ->assertSee('value="Class 10"', false)
            ->assertSee('>Class X</option>', false)
            ->assertSee('>Class X</td>', false);
    }

    public function test_non_admin_cannot_view_caste_report(): void
    {
        $staff = new class extends User
        {
            public $type = 's';

            public function hasRole($roles, ?string $guard = null): bool
            {
                return false;
            }
        };

        $this->actingAs($staff, 'web')
            ->get(route('academic.caste-wise-report'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_staff_only_sees_assigned_classes_in_caste_report(): void
    {
        $this->createStaffAssignmentTables();
        $this->addStaffClassAssignment(6, 123);
        $this->addStudent('06', 'r', false, null, ['gender' => 'Male', 'category' => 'SC'], 'A');
        $this->addStudent('07', 'r', false, null, ['gender' => 'Female', 'category' => 'OBC'], 'A');

        $staff = (new Staff())->forceFill(['employee_id' => 123]);
        $this->actingAs($staff, 'staff')
            ->get(route('staff.academic.caste-wise-report'))
            ->assertOk()
            ->assertViewHas('classOptions', ['06'])
            ->assertViewHas('rows', function (array $rows) {
                $this->assertSame(['06'], array_column($rows, 'class_name'));
                $this->assertSame(1, $rows[0]['overall']['total']);
                return true;
            });
    }

    public function test_staff_cannot_request_caste_report_for_unassigned_class(): void
    {
        $this->createStaffAssignmentTables();
        $this->addStaffClassAssignment(6, 123);
        $staff = (new Staff())->forceFill(['employee_id' => 123]);

        $this->actingAs($staff, 'staff')
            ->get(route('staff.academic.caste-wise-report', ['class_name' => '07']))
            ->assertForbidden();
    }

    private function createStaffAssignmentTables(): void
    {
        Schema::connection('dynamic')->create('class_name', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_name');
        });
        Schema::connection('dynamic')->create('classes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_name');
        });
        Schema::connection('dynamic')->create('teacher_subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('class_id');
            $table->string('section_name')->nullable();
            $table->string('subject_id')->nullable();
            $table->unsignedInteger('teacher_id');
            $table->string('role')->nullable();
            $table->boolean('is_delete')->default(false);
        });

        DB::connection('dynamic')->table('class_name')->insert([
            'id' => 6,
            'class_name' => '06',
        ]);
    }

    public function test_class_teacher_only_sees_students_in_assigned_class_and_section(): void
    {
        $this->createStaffAssignmentTables();
        $this->addStaffClassAssignment(6, 123, 'Kautilya');
        // Student in assigned class & assigned section
        $this->addStudent('06', 'r', false, null, ['gender' => 'Male', 'category' => 'SC'], 'Kautilya');
        // Student in assigned class but DIFFERENT section (should be excluded)
        $this->addStudent('06', 'r', false, null, ['gender' => 'Female', 'category' => 'OBC'], 'Ramanujan');
        // Student in DIFFERENT class (should be excluded)
        $this->addStudent('07', 'r', false, null, ['gender' => 'Male', 'category' => 'GEN'], 'Kautilya');

        $staff = (new Staff())->forceFill(['employee_id' => 123]);
        $response = $this->actingAs($staff, 'staff')
            ->get(route('staff.academic.caste-wise-report'));

        $response->assertOk()
            ->assertViewHas('classOptions', ['06'])
            ->assertViewHas('rows', function (array $rows) {
                $this->assertCount(1, $rows);
                $this->assertSame('06', $rows[0]['class_name']);
                $this->assertSame('Kautilya', $rows[0]['section_name']);
                $this->assertSame(1, $rows[0]['overall']['total']);
                $this->assertSame(1, $rows[0]['categories']['SC']['total']);
                $this->assertSame(0, $rows[0]['categories']['OBC']['total']);
                $this->assertSame(0, $rows[0]['categories']['GEN']['total']);
                return true;
            })
            ->assertViewHas('totals', function (array $totals) {
                $this->assertSame(1, $totals['overall']['total']);
                $this->assertSame(1, $totals['categories']['SC']['total']);
                $this->assertSame(0, $totals['categories']['OBC']['total']);
                return true;
            })
            ->assertSee('(Kautilya)')
            ->assertDontSee('(Ramanujan)');
    }

    public function test_staff_who_is_not_class_teacher_cannot_view_caste_report_records(): void
    {
        $this->createStaffAssignmentTables();
        // Staff 999 is assigned as Regular Teacher (NOT Class Teacher)
        DB::connection('dynamic')->table('teacher_subjects')->insert([
            'class_id' => 6,
            'section_name' => 'A',
            'subject_id' => '101',
            'teacher_id' => 999,
            'role' => 'Regular Teacher',
            'is_delete' => false,
        ]);
        $this->addStudent('06', 'r', false, null, ['gender' => 'Male', 'category' => 'SC'], 'A');

        $staff = (new Staff())->forceFill(['employee_id' => 999]);
        $response = $this->actingAs($staff, 'staff')
            ->get(route('staff.academic.caste-wise-report'));

        $response->assertOk()
            ->assertViewHas('classOptions', [])
            ->assertViewHas('rows', [])
            ->assertSee('You are not currently assigned as a Class Teacher');

        // Cannot filter by any class
        $this->actingAs($staff, 'staff')
            ->get(route('staff.academic.caste-wise-report', ['class_name' => '06']))
            ->assertForbidden();
    }

    public function test_admin_sees_all_classes_and_sections_without_restriction(): void
    {
        $this->createStaffAssignmentTables();
        $this->addStaffClassAssignment(6, 123, 'Kautilya');
        $this->addStudent('06', 'r', false, null, ['gender' => 'Male', 'category' => 'SC'], 'Kautilya');
        $this->addStudent('06', 'r', false, null, ['gender' => 'Female', 'category' => 'OBC'], 'Ramanujan');
        $this->addStudent('07', 'r', false, null, ['gender' => 'Male', 'category' => 'GEN'], 'Kautilya');

        $admin = new class extends User
        {
            public $type = 'a';

            public function hasRole($roles, ?string $guard = null): bool
            {
                return false;
            }
        };

        $response = $this->actingAs($admin, 'web')
            ->get(route('academic.caste-wise-report'));

        $response->assertOk()
            ->assertViewHas('rows', function (array $rows) {
                $this->assertSame(['06', '07'], array_column($rows, 'class_name'));
                // In Class 06, both Kautilya and Ramanujan students are counted (total = 2)
                $this->assertSame(2, $rows[0]['overall']['total']);
                $this->assertSame(1, $rows[0]['categories']['SC']['total']);
                $this->assertSame(1, $rows[0]['categories']['OBC']['total']);
                // In Class 07, student is counted (total = 1)
                $this->assertSame(1, $rows[1]['overall']['total']);
                return true;
            })
            ->assertViewHas('totals', function (array $totals) {
                $this->assertSame(3, $totals['overall']['total']);
                return true;
            });
    }

    private function addStaffClassAssignment(int $classId, int $employeeId, string $section = 'A'): void
    {
        DB::connection('dynamic')->table('teacher_subjects')->insert([
            'class_id' => $classId,
            'section_name' => $section,
            'subject_id' => null,
            'teacher_id' => $employeeId,
            'role' => 'Class Teacher',
            'is_delete' => false,
        ]);
    }

    private function addStudent(
        string $className,
        string $status,
        bool $archived,
        ?string $transferStatus,
        array $details,
        ?string $sectionName = null
    ): void {
        DB::connection('dynamic')->table('student_registration')->insert([
            'class_name' => $className,
            'section_name' => $sectionName,
            'status' => $status,
            'transfer_status' => $transferStatus,
            'is_archived' => $archived,
            'json_str' => json_encode($details),
        ]);
    }
}
