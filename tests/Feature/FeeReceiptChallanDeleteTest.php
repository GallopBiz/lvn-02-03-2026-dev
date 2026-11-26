<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\Student_registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FeeReceiptChallanDeleteTest extends TestCase
{
    protected $student;
    protected $createdChallanIds = [];
    protected $adminUser;
    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Admin user
        $this->adminUser = User::where('type', 'a')->first();
        if (!$this->adminUser) {
            $this->adminUser = User::first();
            if ($this->adminUser) {
                $this->adminUser->type = 'a';
                $this->adminUser->role = 'admin';
                $this->adminUser->save();
            } else {
                $this->adminUser = User::create([
                    'student_name' => 'Admin User',
                    'form_number' => 'ADMIN_FORM_' . time(),
                    'password' => bcrypt('password'),
                    'type' => 'a',
                    'role' => 'admin',
                ]);
            }
        }

        // 2. Setup Staff user
        $this->staffUser = Staff::first();
        if (!$this->staffUser) {
            $this->staffUser = Staff::create([
                'employee_id' => 9991,
                'name' => 'Staff Tester',
                'email' => 'staff_tester_' . time() . '@example.com',
                'password' => bcrypt('secret'),
                'is_delete' => 0,
            ]);
        }

        // 3. Setup Test Student
        $this->student = Student_registration::create([
            'form_number' => 'TEST_FORM_' . time(),
            'scholar_no' => 'TEST_SCH_' . rand(10000, 99999),
            'student_name' => 'Test Ledger Student',
            'class_name' => '10',
            'section_name' => 'Aryabhatta',
            'registration_date' => '2026-04-01',
            'is_delete' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdChallanIds)) {
            DB::connection('dynamic')->table('feesreceiptchallan')
                ->whereIn('id', $this->createdChallanIds)
                ->delete();
        }

        if ($this->student) {
            $this->student->delete();
        }

        parent::tearDown();
    }

    public function test_admin_can_delete_manual_fee_receipt_entry()
    {
        $manualId = DB::connection('dynamic')->table('feesreceiptchallan')->insertGetId([
            'student_id' => $this->student->id,
            'name_student' => $this->student->student_name,
            'class_name' => $this->student->class_name,
            'due_upto' => '2026-11-30',
            'old_fee_rec_no' => 'MANUAL_REC_101',
            'is_delete' => 0,
            'str_json' => json_encode([
                'from_employee' => 'CASHIER_RAM',
                'payment_by_select' => 'CASH',
                'challan_id' => 'CH_MANUAL_101',
                'sub_total_received' => 3500,
                'head_name' => 'TUITION FEES',
                'head_due_amount' => '3500',
                'head_rec_ammount' => '3500',
                'term_str' => 'Term-1',
                'payment_date' => '2026-11-20',
                'by_cash' => '3500',
                'grand_total_due' => '3500',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdChallanIds[] = $manualId;

        $response = $this->actingAs($this->adminUser)
            ->delete(route('feesreceiptchallan.destroy', $manualId));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Fee receipt/challan #' . $manualId . ' has been deleted successfully.');

        $record = DB::connection('dynamic')->table('feesreceiptchallan')->where('id', $manualId)->first();
        $this->assertNotNull($record);
        $this->assertEquals(1, (int)$record->is_delete);
    }

    public function test_staff_cannot_delete_fee_receipt_entry()
    {
        $manualId = DB::connection('dynamic')->table('feesreceiptchallan')->insertGetId([
            'student_id' => $this->student->id,
            'name_student' => $this->student->student_name,
            'class_name' => $this->student->class_name,
            'due_upto' => '2026-11-30',
            'old_fee_rec_no' => 'MANUAL_REC_102',
            'is_delete' => 0,
            'str_json' => json_encode([
                'from_employee' => 'CASHIER_SHYAM',
                'payment_by_select' => 'CHEQUE',
                'challan_id' => 'CH_MANUAL_102',
                'sub_total_received' => 4500,
                'head_name' => 'TUITION FEES',
                'head_due_amount' => '4500',
                'head_rec_ammount' => '4500',
                'term_str' => 'Term-1',
                'payment_date' => '2026-11-20',
                'by_cash' => '4500',
                'grand_total_due' => '4500',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdChallanIds[] = $manualId;

        $response = $this->actingAs($this->staffUser, 'staff')
            ->delete(route('feesreceiptchallan.destroy', $manualId));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $errorMessage = session('error');
        $this->assertTrue(
            str_contains($errorMessage, 'Unauthorized') || str_contains($errorMessage, 'Permission Denied'),
            'Error message should indicate permission denial or unauthorized access.'
        );

        $record = DB::connection('dynamic')->table('feesreceiptchallan')->where('id', $manualId)->first();
        $this->assertNotNull($record);
        $this->assertEquals(0, (int)$record->is_delete);
    }

    public function test_admin_cannot_delete_online_razorpay_fee_receipt_entry()
    {
        $onlineId = DB::connection('dynamic')->table('feesreceiptchallan')->insertGetId([
            'student_id' => $this->student->id,
            'name_student' => $this->student->student_name,
            'class_name' => $this->student->class_name,
            'due_upto' => '2026-11-30',
            'old_fee_rec_no' => 'txn_2026_test__pay_abc123456789',
            'is_delete' => 0,
            'str_json' => json_encode([
                'from_employee' => 'RAZORPAY',
                'payment_by_select' => 'ONLINE',
                'challan_id' => 'txn_2026_test',
                'sub_total_received' => 15000,
                'head_name' => 'TUITION FEES',
                'head_due_amount' => '15000',
                'head_rec_ammount' => '15000',
                'term_str' => 'Term-1',
                'payment_date' => '2026-11-20',
                'by_cash' => '15000',
                'grand_total_due' => '15000',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdChallanIds[] = $onlineId;

        $response = $this->actingAs($this->adminUser)
            ->delete(route('feesreceiptchallan.destroy', $onlineId));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Online payment records (Razorpay) cannot be deleted. Only manually entered payment records can be deleted.');

        $record = DB::connection('dynamic')->table('feesreceiptchallan')->where('id', $onlineId)->first();
        $this->assertNotNull($record);
        $this->assertEquals(0, (int)$record->is_delete);
    }

    public function test_cannot_delete_already_deleted_fee_receipt()
    {
        $deletedId = DB::connection('dynamic')->table('feesreceiptchallan')->insertGetId([
            'student_id' => $this->student->id,
            'name_student' => $this->student->student_name,
            'class_name' => $this->student->class_name,
            'due_upto' => '2026-11-30',
            'old_fee_rec_no' => 'ALREADY_DEL',
            'is_delete' => 1,
            'str_json' => json_encode([
                'from_employee' => 'CASHIER',
                'payment_by_select' => 'CASH',
                'challan_id' => 'CH_DEL_001',
                'sub_total_received' => 2000,
                'head_name' => 'TUITION FEES',
                'head_due_amount' => '2000',
                'head_rec_ammount' => '2000',
                'term_str' => 'Term-1',
                'payment_date' => '2026-11-20',
                'by_cash' => '2000',
                'grand_total_due' => '2000',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdChallanIds[] = $deletedId;

        $response = $this->actingAs($this->adminUser)
            ->delete(route('feesreceiptchallan.destroy', $deletedId));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Fee receipt/challan entry is already deleted.');
    }

    public function test_deleted_receipt_is_excluded_from_student_ledger_view()
    {
        // 1. Create one manual active receipt
        $activeManualId = DB::connection('dynamic')->table('feesreceiptchallan')->insertGetId([
            'student_id' => $this->student->id,
            'name_student' => $this->student->student_name,
            'class_name' => $this->student->class_name,
            'due_upto' => '2026-05-31',
            'old_fee_rec_no' => 'MANUAL_ACTIVE',
            'is_delete' => 0,
            'str_json' => json_encode([
                'from_employee' => 'CASHIER_ACTIVE',
                'payment_by_select' => 'CASH',
                'challan_id' => 'CH_ACT_555',
                'sub_total_received' => 3000,
                'head_name' => 'TUITION FEES',
                'head_due_amount' => '3000',
                'head_rec_ammount' => '3000',
                'term_str' => 'Term-1',
                'payment_date' => '2026-05-15',
                'by_cash' => '3000',
                'grand_total_due' => '3000',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdChallanIds[] = $activeManualId;

        // 2. Create one deleted receipt
        $deletedId = DB::connection('dynamic')->table('feesreceiptchallan')->insertGetId([
            'student_id' => $this->student->id,
            'name_student' => $this->student->student_name,
            'class_name' => $this->student->class_name,
            'due_upto' => '2026-06-30',
            'old_fee_rec_no' => 'MANUAL_DELETED',
            'is_delete' => 1,
            'str_json' => json_encode([
                'from_employee' => 'CASHIER_DEL',
                'payment_by_select' => 'CASH',
                'challan_id' => 'CH_DEL_777',
                'sub_total_received' => 9999,
                'head_name' => 'TUITION FEES',
                'head_due_amount' => '9999',
                'head_rec_ammount' => '9999',
                'term_str' => 'Term-2',
                'payment_date' => '2026-06-15',
                'by_cash' => '9999',
                'grand_total_due' => '9999',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdChallanIds[] = $deletedId;

        // 3. Create one online active receipt
        $activeOnlineId = DB::connection('dynamic')->table('feesreceiptchallan')->insertGetId([
            'student_id' => $this->student->id,
            'name_student' => $this->student->student_name,
            'class_name' => $this->student->class_name,
            'due_upto' => '2026-07-31',
            'old_fee_rec_no' => 'pay_ONLINE_888',
            'is_delete' => 0,
            'str_json' => json_encode([
                'from_employee' => 'RAZORPAY',
                'payment_by_select' => 'ONLINE',
                'challan_id' => 'CH_ONL_888',
                'sub_total_received' => 7000,
                'head_name' => 'TUITION FEES',
                'head_due_amount' => '7000',
                'head_rec_ammount' => '7000',
                'term_str' => 'Term-3',
                'payment_date' => '2026-07-15',
                'by_cash' => '7000',
                'grand_total_due' => '7000',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdChallanIds[] = $activeOnlineId;

        // 4. Fetch the student ledger view as Admin
        $response = $this->actingAs($this->adminUser)
            ->get(route('view-student-ledger', $this->student->id));

        $response->assertStatus(200);

        // Deleted receipt amount should NOT appear in ledger table
        $response->assertDontSee('9,999.00');
        $response->assertDontSee('CH_DEL_777');

        // Active manual receipt should appear and have Delete button
        $response->assertSee('3,000.00');
        $response->assertSee(route('feesreceiptchallan.destroy', $activeManualId));

        // Active online receipt should appear and show Online locked badge instead of delete action
        $response->assertSee('7,000.00');
        $response->assertSee('Online');
        $response->assertDontSee(route('feesreceiptchallan.destroy', $activeOnlineId));
    }
}
