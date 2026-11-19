<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\HrmsEmployee;
use App\Models\HrmsShift;
use App\Models\HrmsShiftType;
use App\Models\HrmsStaffType;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeTableColumnsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_COOKIE['selectedYear'] = '2025_2026';
        Config::set('database.connections.dynamic.database', '2025_2026');
        DB::purge('dynamic');

        $user = User::first();
        if ($user) {
            $this->actingAs($user, 'web');
        }
    }

    /**
     * Test employee table columns sequence and hidden fields
     */
    public function test_employee_table_has_correct_column_sequence_and_hidden_fields()
    {
        $response = $this->withSession(['selectedYear' => '2025_2026'])->get(route('employee'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // Must contain requested columns in table
        $this->assertStringContainsString('<th>Sr.</th>', $content);
        $this->assertStringContainsString('<th>First Name</th>', $content);
        $this->assertStringContainsString('<th>Last Name</th>', $content);
        $this->assertStringContainsString('<th>Ess Emp Code</th>', $content);
        $this->assertStringContainsString('<th>Phone</th>', $content);
        $this->assertStringContainsString('<th>Shift</th>', $content);
        $this->assertStringContainsString('<th>Staff Type</th>', $content);
        $this->assertStringContainsString('<th>Joining Date</th>', $content);
        $this->assertStringContainsString('<th>Status</th>', $content);
        $this->assertStringContainsString('<th>Action </th>', $content);

        // Extract thead section to verify hidden headers
        preg_match('/<table[^>]*id="zero_configuration_table"[^>]*>.*?<thead>(.*?)<\/thead>/s', $content, $matches);
        $this->assertNotEmpty($matches, 'zero_configuration_table thead found');

        $theadContent = $matches[1];

        // Address, Email, Date Of Birth must NOT be in thead
        $this->assertStringNotContainsString('<th>Address</th>', $theadContent);
        $this->assertStringNotContainsString('<th>Email</th>', $theadContent);
        $this->assertStringNotContainsString('<th>Date Of Birth</th>', $theadContent);

        // Verify column sequence order in thead
        $expectedColumns = [
            'Sr.',
            'First Name',
            'Last Name',
            'Ess Emp Code',
            'Phone',
            'Shift',
            'Staff Type',
            'Joining Date',
            'Status',
            'Action',
        ];

        $pattern = '/' . implode('.*?', array_map(function ($col) {
            return preg_quote($col, '/');
        }, $expectedColumns)) . '/s';

        $this->assertMatchesRegularExpression($pattern, $theadContent, 'Columns must appear in the exact requested sequence');
    }
}
