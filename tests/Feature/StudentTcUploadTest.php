<?php

namespace Tests\Feature;

use App\Models\StudentTcFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentTcUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    public function test_admin_can_upload_tc_for_manual_or_old_student()
    {
        $user = User::on('mysql')->first() ?: User::factory()->make();

        $session = config('database.connections.dynamic.database') ?: '2025_2026';
        Config::set('database.connections.dynamic.database', $session);
        DB::purge('dynamic');
        DB::reconnect('dynamic');

        $file = UploadedFile::fake()->createWithContent('tc_sample.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");

        $response = $this->actingAs($user)->post('/student-tc-files', [
            'scholar_no' => 'OLD-SCH-999',
            'student_name' => 'John Old Student',
            'class_name' => '10th',
            'section_name' => 'A',
            'tc_file' => $file,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('student_tc_files', [
            'scholar_no' => 'OLD-SCH-999',
            'student_name' => 'John Old Student',
            'class_name' => '10th',
            'section_name' => 'A',
            'session_name' => $session,
        ], 'dynamic');
    }
}
