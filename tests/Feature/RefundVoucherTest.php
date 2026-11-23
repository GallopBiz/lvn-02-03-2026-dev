<?php

namespace Tests\Feature;

use App\Http\Controllers\backend\FeesTypesMasterController;
use App\Http\Controllers\backend\RefundVoucherController;
use App\Models\RefundVoucher;
use App\Models\Student_registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RefundVoucherTest extends TestCase
{
    protected string $tempDbFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDbFile = tempnam(sys_get_temp_dir(), 'test_rv_');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->tempDbFile,
            'database.connections.sqlite.prefix' => '',
            'database.connections.dynamic' => [
                'driver' => 'sqlite',
                'database' => $this->tempDbFile,
                'prefix' => '',
            ],
        ]);
        DB::purge('sqlite');
        DB::purge('dynamic');
        DB::setDefaultConnection('sqlite');

        \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag());

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('role')->nullable();
            $table->string('type')->default('a');
            $table->timestamps();
        });

        Schema::create('student_registration', function (Blueprint $table) {
            $table->increments('id');
            $table->string('application_for')->nullable();
            $table->string('form_number')->nullable();
            $table->string('scholar_no', 50)->nullable();
            $table->string('date_of_birth', 50)->nullable();
            $table->string('class_name', 50)->nullable();
            $table->unsignedInteger('class_id')->nullable();
            $table->string('section_name', 50)->nullable();
            $table->string('student_name', 150)->nullable();
            $table->string('session_name', 50)->nullable();
            $table->text('json_str')->nullable();
            $table->string('staff_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('inq_mode')->nullable();
            $table->string('driver')->nullable();
            $table->string('status', 10)->default('r');
            $table->integer('next_year')->default(0);
            $table->string('type', 10)->default('r');
            $table->string('student_image_url')->nullable();
            $table->string('password')->nullable();
            $table->date('registration_date')->nullable();
            $table->timestamps();
        });

        Schema::create('totalnextyear', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fees_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('totalnextyear', 10, 2)->default(0);
            $table->string('account_name', 100);
            $table->decimal('fees', 10, 2)->default(0);
            $table->string('scholar_no', 50)->nullable();
            $table->string('received_type', 50)->nullable();
            $table->string('reference_number')->nullable();
            $table->string('receipt_number')->nullable();
            $table->integer('is_delete')->default(0);
            $table->timestamps();
        });

        Schema::create('feesreceiptchallan', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('student_id');
            $table->string('student_dob')->nullable();
            $table->string('recpt_chain')->nullable();
            $table->string('due_upto')->nullable();
            $table->string('name_student')->nullable();
            $table->text('str_json')->nullable();
            $table->string('class_name')->nullable();
            $table->integer('is_delete')->default(0);
            $table->string('old_fee_rec_no')->nullable();
            $table->timestamps();
        });

        Schema::create('generate_duechartstatus', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('student_id')->nullable();
            $table->string('scholar_no', 50)->nullable();
            $table->text('json_str')->nullable();
            $table->integer('is_delete')->default(0);
            $table->timestamps();
        });

        Schema::create('tc_certificates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('certificate_no', 60)->unique();
            $table->unsignedInteger('student_id');
            $table->string('scholar_no', 50);
            $table->string('student_name', 150);
            $table->string('session_name', 50)->nullable();
            $table->string('class_name', 50)->nullable();
            $table->string('section_name', 50)->nullable();
            $table->date('issue_date');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('refund_vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('voucher_no', 60)->unique();
            $table->date('voucher_date');
            $table->unsignedBigInteger('student_id')->index();
            $table->string('scholar_no', 50)->index();
            $table->string('student_name', 150);
            $table->string('class_name', 50)->nullable();
            $table->string('section_name', 50)->nullable();
            $table->string('father_name', 150)->nullable();
            $table->unsignedBigInteger('tc_id')->nullable()->index();
            $table->boolean('is_discontinued')->default(false);
            $table->string('refund_head', 100)->default('CAUTION MONEY');
            $table->decimal('refundable_amount', 10, 2)->default(0.00);
            $table->decimal('refund_amount', 10, 2)->default(0.00);
            $table->string('payment_mode', 50)->default('Cheque');
            $table->string('cheque_no', 50)->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('bank_name', 150)->nullable();
            $table->string('cash_account', 150)->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 30)->default('completed');
            $table->text('cancel_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_name', 150)->nullable();
            $table->string('session_name', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
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
            $table->index(['model_id', 'model_type']);
        });

        Schema::create('vehicel', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vehicelno')->nullable();
            $table->timestamps();
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(0);
            $table->timestamps();
        });

        Schema::create('student_fees_exempted', function (Blueprint $table) {
            $table->increments('id');
            $table->string('scholar_no')->nullable();
            $table->string('head_name')->nullable();
            $table->decimal('exempt_per', 5, 2)->default(0);
            $table->timestamps();
        });

        $this->withoutMiddleware([
            \App\Http\Middleware\CheckMenuPermission::class,
            \App\Http\Middleware\EnsureStaffReportAccess::class,
        ]);

        $admin = User::create([
            'id' => 1,
            'name' => 'Super Admin',
            'email' => 'admin@lvn.edu.in',
            'role' => 'Admin',
            'type' => 'a',
        ]);
        DB::table('roles')->insert(['id' => 1, 'name' => 'Admin', 'guard_name' => 'web']);
        DB::table('model_has_roles')->insert(['role_id' => 1, 'model_type' => User::class, 'model_id' => 1]);
        Auth::login($admin);
    }

    protected function tearDown(): void
    {
        if (isset($this->tempDbFile) && file_exists($this->tempDbFile)) {
            @unlink($this->tempDbFile);
        }
        parent::tearDown();
    }

    public function test_refund_voucher_index_page_loads_successfully()
    {
        $response = $this->get(route('refund-vouchers.index'));
        $response->assertStatus(200);
        $response->assertSee('Refund Vouchers Register');
    }

    public function test_refund_voucher_create_page_loads_with_suggested_voucher_no()
    {
        $response = $this->get(route('refund-vouchers.create'));
        $response->assertStatus(200);
        $response->assertSee('Generate Refund Voucher');
        $response->assertSee('RV-');
    }

    public function test_student_balance_calculation_defaults_to_3000_caution_money()
    {
        $student = DB::table('student_registration')->insertGetId([
            'scholar_no' => '5418',
            'student_name' => 'Yuvraj Ranawat',
            'class_name' => 'X',
            'section_name' => 'Ramanujan',
            'session_name' => '2026-2027',
            'json_str' => json_encode(['student_father_name' => 'Anil Ranawat']),
        ]);

        $controller = new RefundVoucherController();
        $balanceInfo = $controller->calculateStudentRefundableBalance($student, 'CAUTION MONEY');

        $this->assertEquals(3000.00, $balanceInfo['original_refundable']);
        $this->assertEquals(0.00, $balanceInfo['already_refunded']);
        $this->assertEquals(3000.00, $balanceInfo['available_balance']);
    }

    public function test_validation_prevents_refund_exceeding_available_balance()
    {
        $student = DB::table('student_registration')->insertGetId([
            'scholar_no' => '5418',
            'student_name' => 'Yuvraj Ranawat',
            'class_name' => 'X',
            'section_name' => 'Ramanujan',
            'json_str' => json_encode(['student_father_name' => 'Anil Ranawat']),
        ]);

        // Attempt to refund 5000 when available is 3000
        $response = $this->post(route('refund-vouchers.store'), [
            'voucher_no' => 'RV-2026-9999',
            'voucher_date' => '2026-11-20',
            'student_id' => $student,
            'refund_head' => 'CAUTION MONEY',
            'refund_amount' => 5000.00,
            'payment_mode' => 'Cheque',
            'cheque_no' => '123456',
            'cheque_date' => '2026-11-20',
            'bank_name' => 'STATE BANK OF INDIA',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('refund_vouchers', [
            'voucher_no' => 'RV-2026-9999',
        ]);
    }

    public function test_store_creates_refund_voucher_and_reduces_balance()
    {
        $student = DB::table('student_registration')->insertGetId([
            'scholar_no' => '5418',
            'student_name' => 'Yuvraj Ranawat',
            'class_name' => 'X',
            'section_name' => 'Ramanujan',
            'json_str' => json_encode(['student_father_name' => 'Anil Ranawat']),
        ]);

        $response = $this->post(route('refund-vouchers.store'), [
            'voucher_no' => 'RV-2026-0001',
            'voucher_date' => '2026-11-20',
            'student_id' => $student,
            'refund_head' => 'CAUTION MONEY',
            'refund_amount' => 3000.00,
            'payment_mode' => 'Cheque',
            'cheque_no' => '444444',
            'cheque_date' => '2026-11-20',
            'bank_name' => 'CHEQUE RETURN A/C',
            'remarks' => 'Full caution money refund on T.C.',
        ]);

        $this->assertDatabaseHas('refund_vouchers', [
            'voucher_no' => 'RV-2026-0001',
            'student_id' => $student,
            'refund_amount' => 3000.00,
            'status' => 'completed',
        ]);

        // Balance should now be 0.00
        $controller = new RefundVoucherController();
        $balanceInfo = $controller->calculateStudentRefundableBalance($student, 'CAUTION MONEY');
        $this->assertEquals(0.00, $balanceInfo['available_balance']);
        $this->assertEquals(3000.00, $balanceInfo['already_refunded']);
    }

    public function test_partial_refund_allows_subsequent_refund_up_to_remaining_balance()
    {
        $student = DB::table('student_registration')->insertGetId([
            'scholar_no' => '7874',
            'student_name' => 'Rahul Sharma',
            'class_name' => 'IX',
            'section_name' => 'Aryabhatta',
            'json_str' => json_encode(['student_father_name' => 'Manoj Sharma']),
        ]);

        // First partial refund: 1000.00
        $this->post(route('refund-vouchers.store'), [
            'voucher_no' => 'RV-2026-0010',
            'voucher_date' => '2026-11-20',
            'student_id' => $student,
            'refund_head' => 'CAUTION MONEY',
            'refund_amount' => 1000.00,
            'payment_mode' => 'Cash',
            'cash_account' => 'CASH A/C',
        ]);

        $controller = new RefundVoucherController();
        $balanceInfo = $controller->calculateStudentRefundableBalance($student, 'CAUTION MONEY');
        $this->assertEquals(2000.00, $balanceInfo['available_balance']);

        // Second partial refund: 2000.00 (remaining balance)
        $this->post(route('refund-vouchers.store'), [
            'voucher_no' => 'RV-2026-0011',
            'voucher_date' => '2026-11-20',
            'student_id' => $student,
            'refund_head' => 'CAUTION MONEY',
            'refund_amount' => 2000.00,
            'payment_mode' => 'Cheque',
            'cheque_no' => '555555',
            'cheque_date' => '2026-11-20',
            'bank_name' => 'HDFC BANK',
        ]);

        $balanceInfo2 = $controller->calculateStudentRefundableBalance($student, 'CAUTION MONEY');
        $this->assertEquals(0.00, $balanceInfo2['available_balance']);
        $this->assertEquals(3000.00, $balanceInfo2['already_refunded']);

        // Third refund should fail because balance is 0
        $response3 = $this->post(route('refund-vouchers.store'), [
            'voucher_no' => 'RV-2026-0012',
            'voucher_date' => '2026-11-20',
            'student_id' => $student,
            'refund_head' => 'CAUTION MONEY',
            'refund_amount' => 500.00,
            'payment_mode' => 'Cash',
        ]);
        $response3->assertSessionHas('error');
    }

    public function test_student_ledger_integration_reflects_refundable_dr()
    {
        $studentId = DB::table('student_registration')->insertGetId([
            'scholar_no' => '8001',
            'student_name' => 'Amit Verma',
            'class_name' => 'XI',
            'section_name' => 'Kautilya',
            'json_str' => json_encode(['batch' => '2026_2027']),
        ]);

        // Create completed refund voucher
        RefundVoucher::create([
            'voucher_no' => 'RV-2026-0099',
            'voucher_date' => '2026-11-20',
            'student_id' => $studentId,
            'scholar_no' => '8001',
            'student_name' => 'Amit Verma',
            'class_name' => 'XI',
            'refund_head' => 'CAUTION MONEY',
            'refundable_amount' => 3000.00,
            'refund_amount' => 3000.00,
            'payment_mode' => 'Cheque',
            'cheque_no' => '888888',
            'bank_name' => 'CHEQUE RETURN A/C',
            'status' => 'completed',
        ]);

        unset($_COOKIE['selectedYear']);
        config(['database.connections.dynamic.database' => $this->tempDbFile]);
        DB::purge('dynamic');

        $studentData = DB::table('student_registration')->where('id', $studentId)->first();
        $controller = new FeesTypesMasterController(request());

        config(['database.connections.dynamic.database' => $this->tempDbFile]);
        DB::purge('dynamic');

        // Use reflection to test private mergeLedger method
        $reflection = new \ReflectionClass(FeesTypesMasterController::class);
        $method = $reflection->getMethod('mergeLedger');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [], [], $studentData, collect());
        $mergedRows = $result['mergedRows'];

        // Assert that mergedRows contains the refund voucher row with refundable_dr = 3000
        $voucherRows = array_filter($mergedRows, fn ($row) => ($row['source'] ?? '') === 'refund_voucher');
        $this->assertNotEmpty($voucherRows);

        $voucherRow = reset($voucherRows);
        $this->assertEquals(3000.00, $voucherRow['refundable_dr']);
        $this->assertEquals('RV-2026-0099', $voucherRow['voucher_no']);
        $this->assertStringContainsString('CAUTION MONEY REFUND', $voucherRow['remarks']);
        $this->assertStringContainsString('888888', $voucherRow['remarks']);
    }

    public function test_cancelling_refund_voucher_restores_balance()
    {
        $student = DB::table('student_registration')->insertGetId([
            'scholar_no' => '9001',
            'student_name' => 'Pooja Patil',
            'class_name' => 'XII',
            'json_str' => json_encode([]),
        ]);

        $voucher = RefundVoucher::create([
            'voucher_no' => 'RV-2026-0150',
            'voucher_date' => '2026-11-20',
            'student_id' => $student,
            'scholar_no' => '9001',
            'student_name' => 'Pooja Patil',
            'refund_head' => 'CAUTION MONEY',
            'refundable_amount' => 3000.00,
            'refund_amount' => 3000.00,
            'payment_mode' => 'Cheque',
            'cheque_no' => '777777',
            'status' => 'completed',
        ]);

        $controller = new RefundVoucherController();
        $this->assertEquals(0.00, $controller->calculateStudentRefundableBalance($student)['available_balance']);

        // Cancel the voucher
        $response = $this->post(route('refund-vouchers.cancel', $voucher->id), [
            'cancel_reason' => 'Cheque bounced/returned',
        ]);

        $response->assertRedirect(route('refund-vouchers.index'));
        $this->assertDatabaseHas('refund_vouchers', [
            'id' => $voucher->id,
            'status' => 'cancelled',
            'cancel_reason' => 'Cheque bounced/returned',
        ]);

        // Balance must be restored to 3000.00
        $this->assertEquals(3000.00, $controller->calculateStudentRefundableBalance($student)['available_balance']);
    }

    public function test_export_csv_streams_records()
    {
        RefundVoucher::create([
            'voucher_no' => 'RV-2026-0200',
            'voucher_date' => '2026-11-20',
            'student_id' => 1234,
            'scholar_no' => '1234',
            'student_name' => 'Export Student',
            'refund_head' => 'CAUTION MONEY',
            'refund_amount' => 3000.00,
            'payment_mode' => 'Cash',
            'status' => 'completed',
        ]);

        $response = $this->get(route('refund-vouchers.index', ['export' => 'csv']));
        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    public function test_get_student_head_wise_balances_loads_advance_and_dues()
    {
        $student = DB::table('student_registration')->insertGetId([
            'scholar_no' => '5418',
            'student_name' => 'Yuvraj Ranasara',
            'class_name' => '09',
            'section_name' => 'Ramanujan',
            'session_name' => '2026_2027',
            'json_str' => json_encode(['student_father_name' => 'Anil Ranasara']),
            'status' => 'r',
        ]);

        // 1. Advance / Caution Money
        DB::table('totalnextyear')->insert([
            'scholar_no' => '5418',
            'account_name' => 'CAUTION MONEY',
            'fees' => 3000.00,
            'fees_date' => '2026-04-01',
            'is_delete' => 0,
        ]);

        // 2. Fee Structure with Tuition installments
        $structureJson = json_encode([
            [
                'json_str' => json_encode([
                    'fees_date' => ['01-04-2026', '01-07-2026', '01-10-2026', '01-01-2027'],
                    'account_name' => ['TUITION FEES', 'TUITION FEES', 'TUITION FEES', 'TUITION FEES'],
                    'fees' => [13000, 13000, 13000, 11700],
                    'due_date' => ['05-05-2026', '05-08-2026', '05-11-2026', '05-02-2027'],
                    'term' => ['1st', '2nd', '3rd', '4th'],
                ]),
            ],
        ]);
        DB::table('generate_duechartstatus')->insert([
            'student_id' => $student,
            'scholar_no' => '5418',
            'json_str' => $structureJson,
            'is_delete' => 0,
        ]);

        // 3. Receipt paying 1st installment
        DB::table('feesreceiptchallan')->insert([
            'student_id' => $student,
            'str_json' => json_encode([
                'head_name' => 'TUITION FEES',
                'head_rec_ammount' => '13000',
                'term_str' => '1st',
            ]),
            'is_delete' => 0,
        ]);

        $controller = new RefundVoucherController();
        $headWise = $controller->getStudentHeadWiseBalances($student, 'CAUTION MONEY', '5418');

        $this->assertEquals(3000.00, $headWise['total_advance']);
        // 2nd (13000) + 3rd (13000) + 4th (11700) = 37700 unpaid dues
        $this->assertEquals(37700.00, $headWise['total_dues']);
        $this->assertEquals(3000.00 - 37700.00, $headWise['net_balance']);
        $this->assertTrue($headWise['has_outstanding_dues']);

        // Check details rows
        $advanceRows = array_filter($headWise['head_wise_details'], fn($r) => $r['type'] === 'advance');
        $dueRows = array_filter($headWise['head_wise_details'], fn($r) => $r['type'] === 'due');

        $this->assertCount(1, $advanceRows);
        $this->assertCount(3, $dueRows);

        $firstAdv = array_values($advanceRows)[0];
        $this->assertEquals('CAUTION MONEY', $firstAdv['head_name']);
        $this->assertStringContainsString('badge-success', $firstAdv['badge_class']);

        $firstDue = array_values($dueRows)[0];
        $this->assertEquals('TUITION FEES', $firstDue['head_name']);
        $this->assertStringContainsString('badge-danger', $firstDue['badge_class']);
        $this->assertStringContainsString('-', $firstDue['balance_display']);
    }

    public function test_get_student_balance_api_returns_head_wise_breakdown()
    {
        $student = DB::table('student_registration')->insertGetId([
            'scholar_no' => '9999',
            'student_name' => 'Clear Student',
            'class_name' => 'KG1',
            'section_name' => 'Aryabhatta',
            'session_name' => '2026_2027',
            'status' => 'r',
        ]);

        DB::table('totalnextyear')->insert([
            'scholar_no' => '9999',
            'account_name' => 'CAUTION MONEY',
            'fees' => 3000.00,
            'is_delete' => 0,
        ]);

        $response = $this->get(url('refund-vouchers/student-balance/' . $student . '?head=CAUTION MONEY'));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(3000.00, $data['available_balance']);
        $this->assertEquals(3000.00, $data['total_advance']);
        $this->assertEquals(0.00, $data['total_dues']);
        $this->assertFalse($data['has_outstanding_dues']);
        $this->assertNotEmpty($data['head_wise_details']);
        $this->assertEquals('Aryabhatta', $data['section_name']);
    }

    public function test_resolve_student_section_and_father_name_fallbacks()
    {
        $controller = new RefundVoucherController();

        // 1. With explicit section and father name in JSON
        $s1 = (object)[
            'scholar_no' => '1001',
            'student_name' => 'Test Student',
            'section_name' => '',
            'json_str' => json_encode([
                'fathername' => 'Mr. Parent',
                'section_name' => 'Kautilya',
            ]),
        ];
        $this->assertEquals('Kautilya', $controller->resolveStudentSection($s1));
        $this->assertEquals('Mr. Parent', $controller->resolveStudentFatherName($s1));

        // 2. With completely empty section -> returns 'N/A'
        $s2 = (object)[
            'scholar_no' => '1002',
            'student_name' => 'Empty Student',
            'section_name' => null,
            'json_str' => json_encode([
                'student_father_name' => 'Father Two',
                'section_name' => '',
            ]),
        ];
        $this->assertEquals('N/A', $controller->resolveStudentSection($s2));
        $this->assertEquals('Father Two', $controller->resolveStudentFatherName($s2));
    }
}
