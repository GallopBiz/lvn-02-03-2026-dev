<?php

namespace Tests\Feature;

use App\Models\StudentTcFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentTcSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    public function test_invalid_token_format_returns_404()
    {
        $response = $this->get('/download-tc/view/shorttoken');
        $response->assertStatus(404);

        $traversalResponse = $this->get('/download-tc/view/..%2F..%2Fetc%2Fpasswd');
        $traversalResponse->assertStatus(404);
    }

    public function test_token_access_without_session_payload_returns_403()
    {
        $fakeToken = Str::random(64);
        $response = $this->get('/download-tc/view/' . $fakeToken);
        $response->assertStatus(403);
    }

    public function test_expired_token_returns_403()
    {
        $token = Str::random(64);
        $sessionKey = 'student_tc_download.' . $token;

        $response = $this->withSession([
            $sessionKey => [
                'file_id' => 999,
                'scholar_no' => '1001',
                'session_name' => '2025_2026',
                'expires_at' => now()->subMinutes(5)->timestamp,
            ]
        ])->get('/download-tc/view/' . $token);

        $response->assertStatus(403);
    }

    public function test_revoked_or_missing_tc_record_returns_403()
    {
        $token = Str::random(64);
        $sessionKey = 'student_tc_download.' . $token;

        $response = $this->withSession([
            $sessionKey => [
                'file_id' => 999999,
                'scholar_no' => 'NONEXISTENT',
                'session_name' => config('database.connections.dynamic.database') ?: '2025_2026',
                'expires_at' => now()->addMinutes(15)->timestamp,
            ]
        ])->get('/download-tc/view/' . $token);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_tc_upload()
    {
        $response = $this->get('/student-tc-files');
        $this->assertTrue(in_array($response->status(), [302, 401]));
    }

    public function test_robots_txt_contains_disallow_for_tc_endpoints()
    {
        $robotsContent = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Disallow: /download-tc/', $robotsContent);
        $this->assertStringContainsString('Disallow: /student-tc-files/', $robotsContent);
    }

    public function test_private_storage_contains_htaccess_protection()
    {
        $this->assertFileExists(storage_path('app/private/.htaccess'));
        $this->assertFileExists(storage_path('app/private/student-tc/.htaccess'));
    }
}
