<?php

namespace Tests\Feature;

use App\Http\Controllers\backend\InquiryEntryController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InquiryEnquiryNumberTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Carbon\Carbon::setTestNow('2026-11-02 12:00:00');

        config([
            'database.connections.dynamic' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('dynamic');

        Schema::connection('dynamic')->create('states', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
        });
        Schema::connection('dynamic')->create('inquiry_registration', function (Blueprint $table) {
            $table->increments('id');
            $table->string('application_for')->nullable();
            $table->integer('form_number')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('class_name')->nullable();
            $table->string('student_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('session_name')->nullable();
            $table->text('json_str')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('inq_mode')->nullable();
            $table->string('next_year')->nullable();
            $table->string('status')->nullable();
            $table->integer('is_delete')->nullable()->default(0);
        });
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_next_year_saves_allocate_the_latest_sequence_instead_of_trusting_stale_form_values(): void
    {
        $controller = $this->controllerWithoutConstructor();

        $controller->save_student_preinquiryentry($this->nextYearRequest('First Student', 1));
        $controller->save_student_preinquiryentry($this->nextYearRequest('Second Student', 1));

        $this->assertSame(
            [1, 2],
            DB::connection('dynamic')->table('inquiry_registration')
                ->orderBy('id')
                ->pluck('form_number')
                ->all()
        );
        $this->assertSame(
            ['2026_2027', '2026_2027'],
            DB::connection('dynamic')->table('inquiry_registration')
                ->orderBy('id')
                ->pluck('session_name')
                ->all()
        );
    }

    public function test_current_sequence_continues_from_next_year_enquiries_carried_into_the_new_session(): void
    {
        DB::connection('dynamic')->table('inquiry_registration')->insert([
            ['form_number' => 1, 'next_year' => 0, 'is_delete' => 0],
            ['form_number' => 2, 'next_year' => 0, 'is_delete' => 0],
        ]);

        $controller = $this->controllerWithoutConstructor();
        $calculateMethod = new \ReflectionMethod($controller, 'calculateEnquiryNumbers');
        $calculateMethod->setAccessible(true);

        $this->assertSame(3, $calculateMethod->invoke($controller)['currentEnquiryNo']);
    }

    public function test_academic_session_changes_on_april_first_without_hardcoded_years(): void
    {
        $controller = $this->controllerWithoutConstructor();
        $sessionMethod = new \ReflectionMethod($controller, 'academicSessionForDate');
        $sessionMethod->setAccessible(true);

        $this->assertSame('2026_2027', $sessionMethod->invoke($controller, \Carbon\Carbon::parse('2027-03-31')));
        $this->assertSame('2027_2028', $sessionMethod->invoke($controller, \Carbon\Carbon::parse('2027-04-01')));
        $this->assertSame('2028_2029', $sessionMethod->invoke($controller, \Carbon\Carbon::parse('2028-04-01')));

        $recentSessionsMethod = new \ReflectionMethod($controller, 'recentAcademicSessionDatabases');
        $recentSessionsMethod->setAccessible(true);
        $this->assertSame(
            ['2026_2027', '2025_2026', '2024_2025'],
            $recentSessionsMethod->invoke($controller)
        );
    }

    private function controllerWithoutConstructor(): InquiryEntryController
    {
        return (new \ReflectionClass(InquiryEntryController::class))->newInstanceWithoutConstructor();
    }

    private function nextYearRequest(string $studentName, int $submittedNumber): Request
    {
        return Request::create('/save_student_preinquiryentry', 'POST', [
            'next_year' => 1,
            'formno' => $submittedNumber,
            'formno2' => $submittedNumber,
            'session' => '2025_2026',
            'studentname' => $studentName,
        ]);
    }
}
