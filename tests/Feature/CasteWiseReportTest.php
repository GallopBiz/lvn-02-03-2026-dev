<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    private function addStudent(
        string $className,
        string $status,
        bool $archived,
        ?string $transferStatus,
        array $details
    ): void {
        DB::connection('dynamic')->table('student_registration')->insert([
            'class_name' => $className,
            'status' => $status,
            'transfer_status' => $transferStatus,
            'is_archived' => $archived,
            'json_str' => json_encode($details),
        ]);
    }
}
