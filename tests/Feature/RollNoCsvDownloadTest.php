<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RollNoCsvDownloadTest extends TestCase
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
            $table->string('student_name')->nullable();
            $table->string('scholar_no')->nullable();
            $table->unsignedInteger('class_id')->nullable();
            $table->string('class_name')->nullable();
            $table->string('section_name')->nullable();
            $table->string('session_name')->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_archived')->nullable();
            $table->text('json_str')->nullable();
        });

        Schema::connection('dynamic')->create('classes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_name');
            $table->string('section_name')->nullable();
        });

        Schema::connection('dynamic')->create('subject_combinations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('combination_name')->nullable();
            $table->string('streams')->nullable();
        });

        Schema::connection('dynamic')->create('streams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('streams')->nullable();
        });

        Schema::connection('dynamic')->create('subject_assign_student', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_name')->nullable();
            $table->string('section_name')->nullable();
            $table->unsignedInteger('assign_this_combtoall')->nullable();
            $table->text('students_details')->nullable();
            $table->boolean('is_delete')->default(false);
        });

        Schema::connection('dynamic')->create('combination_subject', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('subject_combination_id')->nullable();
            $table->unsignedInteger('subject_id')->nullable();
        });

        Schema::connection('dynamic')->create('subjectmaster', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject_name')->nullable();
            $table->string('subject_type')->nullable();
            $table->boolean('is_delete')->default(false);
        });

        Schema::connection('dynamic')->create('academic_student_roll_no', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('student_id');
            $table->string('class_id');
            $table->string('section_id');
            $table->string('session_id');
            $table->unsignedInteger('exam_id')->nullable();
            $table->string('roll_no');
            $table->timestamps();
        });
    }

    public function test_download_rolls_csv_includes_subject_combination_for_class_11_and_12(): void
    {
        // 1. Create Class 11 and combination
        DB::connection('dynamic')->table('classes')->insert([
            'id' => 11,
            'class_name' => '11',
            'section_name' => 'Kautilya',
        ]);

        $combId = DB::connection('dynamic')->table('subject_combinations')->insertGetId([
            'combination_name' => 'PCM 11+ PE',
            'streams' => 'Science',
        ]);

        $studentId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'student_name' => 'Aarav Sharma',
            'scholar_no' => 'SCH-1101',
            'class_id' => 11,
            'class_name' => '11',
            'section_name' => 'Kautilya',
            'status' => 'r',
            'is_archived' => false,
            'json_str' => null,
        ]);

        // Assign subject combination to student
        DB::connection('dynamic')->table('subject_assign_student')->insert([
            'class_name' => '11',
            'section_name' => 'Kautilya',
            'assign_this_combtoall' => $combId,
            'students_details' => (string) $studentId,
            'is_delete' => false,
        ]);

        // Assign roll number
        DB::connection('dynamic')->table('academic_student_roll_no')->insert([
            'student_id' => $studentId,
            'class_id' => '11',
            'section_id' => 'Kautilya',
            'session_id' => '2025_2026',
            'exam_id' => 64,
            'roll_no' => '11201',
        ]);

        $admin = new class extends User {
            public $type = 'a';
            public function hasRole($roles, ?string $guard = null): bool {
                return false;
            }
        };

        $response = $this->actingAs($admin, 'web')
            ->get(route('academic.download-rolls', [
                'class' => '11',
                'section' => 'Kautilya',
                'session' => '2025_2026',
                'exam_id' => 64,
            ]));

        $response->assertOk();
        $this->assertTrue($response->headers->contains('content-type', 'text/csv; charset=UTF-8') || $response->headers->contains('content-type', 'text/csv'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $rows = array_map('str_getcsv', explode("\n", trim($content)));

        // Verify header includes 'Subject Combination'
        $expectedHeader = ['Sr. No.', 'Student Name', 'Scholar No.', 'Roll No.', 'Class', 'Section', 'Subject Combination'];
        $this->assertSame($expectedHeader, $rows[0]);

        // Verify row has the student's combination
        $this->assertSame('Aarav Sharma', $rows[1][1]);
        $this->assertSame('SCH-1101', $rows[1][2]);
        $this->assertSame('11201', $rows[1][3]);
        $this->assertSame('11', $rows[1][4]);
        $this->assertSame('Kautilya', $rows[1][5]);
        $this->assertSame('PCM 11+ PE', $rows[1][6]);
    }

    public function test_download_rolls_csv_excludes_subject_combination_for_junior_classes(): void
    {
        DB::connection('dynamic')->table('classes')->insert([
            'id' => 9,
            'class_name' => '09',
            'section_name' => 'Kautilya',
        ]);

        $studentId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'student_name' => 'Rohan Verma',
            'scholar_no' => 'SCH-901',
            'class_id' => 9,
            'class_name' => '09',
            'section_name' => 'Kautilya',
            'status' => 'r',
            'is_archived' => false,
            'json_str' => null,
        ]);

        DB::connection('dynamic')->table('academic_student_roll_no')->insert([
            'student_id' => $studentId,
            'class_id' => '09',
            'section_id' => 'Kautilya',
            'session_id' => '2025_2026',
            'exam_id' => 64,
            'roll_no' => '09201',
        ]);

        $admin = new class extends User {
            public $type = 'a';
            public function hasRole($roles, ?string $guard = null): bool {
                return false;
            }
        };

        $response = $this->actingAs($admin, 'web')
            ->get(route('academic.download-rolls', [
                'class' => '09',
                'section' => 'Kautilya',
                'session' => '2025_2026',
                'exam_id' => 64,
            ]));

        $response->assertOk();

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $rows = array_map('str_getcsv', explode("\n", trim($content)));

        // Verify junior class header does NOT include 'Subject Combination'
        $expectedHeader = ['Sr. No.', 'Student Name', 'Scholar No.', 'Roll No.', 'Class', 'Section'];
        $this->assertSame($expectedHeader, $rows[0]);

        $this->assertSame('Rohan Verma', $rows[1][1]);
        $this->assertSame('09201', $rows[1][3]);
        $this->assertCount(6, $rows[1]);
    }
}
