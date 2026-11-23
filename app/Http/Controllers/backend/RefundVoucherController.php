<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\RefundVoucher;
use App\Models\StudentRegistration;
use App\Models\TransferCertificate;
use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class RefundVoucherController extends Controller
{
    /**
     * Standard pre-defined bank accounts for Cheque refunds
     */
    public const BANK_ACCOUNTS = [
        'CHEQUE RETURN A/C',
        'STATE BANK OF INDIA',
        'HDFC BANK',
        'ICICI BANK',
        'PUNJAB NATIONAL BANK',
        'BANK OF BARODA',
        'AXIS BANK',
        'CASH A/C',
    ];

    /**
     * Pre-defined refundable fee heads
     */
    public const REFUNDABLE_HEADS = [
        'CAUTION MONEY',
        'LIBRARY SECURITY',
        'HOSTEL SECURITY',
        'LABORATORY SECURITY',
        'OTHER REFUNDABLE',
    ];

    /**
     * Default caution money standard amount (₹3,000)
     */
    public const DEFAULT_CAUTION_MONEY = 3000.00;

    /**
     * Display list / register of refund vouchers
     */
    public function index(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

        $query = RefundVoucher::query()->orderByDesc('voucher_date')->orderByDesc('id');

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('voucher_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('voucher_date', '<=', $request->to_date);
        }

        // Voucher number filter
        if ($request->filled('voucher_no')) {
            $query->where('voucher_no', 'like', '%' . trim($request->voucher_no) . '%');
        }

        // Scholar ID / Student Name filter
        if ($request->filled('scholar_no')) {
            $search = trim($request->scholar_no);
            $query->where(function ($q) use ($search) {
                $q->where('scholar_no', 'like', "%{$search}%")
                    ->orWhere('student_name', 'like', "%{$search}%");
            });
        }

        // Class filter
        if ($request->filled('class_name')) {
            $query->where('class_name', $request->class_name);
        }

        // Refund Head filter
        if ($request->filled('refund_head')) {
            $query->where('refund_head', $request->refund_head);
        }

        // Payment Mode filter
        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        // Status filter (default: non-deleted)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Handle export request
        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->get());
        }

        // Compute KPI Summary on the filtered query (cloned before pagination)
        $kpiQuery = clone $query;
        $activeQuery = (clone $kpiQuery)->where('status', 'completed');
        $kpis = [
            'total_count' => (clone $kpiQuery)->count(),
            'total_amount' => (float) (clone $activeQuery)->sum('refund_amount'),
            'cheque_amount' => (float) (clone $activeQuery)->where('payment_mode', 'Cheque')->sum('refund_amount'),
            'cash_amount' => (float) (clone $activeQuery)->where('payment_mode', 'Cash')->sum('refund_amount'),
        ];

        $vouchers = $query->paginate(20)->withQueryString();

        // Distinct classes for filter dropdown
        $classes = DB::connection('dynamic')->table('refund_vouchers')
            ->whereNotNull('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        $heads = self::REFUNDABLE_HEADS;

        return view('backend.Fees.refund_voucher_list', compact('vouchers', 'kpis', 'classes', 'heads'));
    }

    /**
     * Show form to create a new refund voucher
     */
    public function create(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

        $sessionName = $this->resolveSelectedSessionYear($request);
        $suggestedVoucherNo = RefundVoucher::generateNextVoucherNo($sessionName);

        $selectedStudent = null;
        $studentBalance = null;

        // Pre-select student if student_id or scholar_no passed in query
        if ($request->filled('student_id')) {
            $selectedStudent = DB::connection('dynamic')
                ->table('student_registration')
                ->where('id', $request->student_id)
                ->first();
            if (!$selectedStudent) {
                $sessions = $this->getAvailableAcademicDatabases($request);
                foreach ($sessions as $db) {
                    if ($db) {
                        try {
                            $found = DB::table("$db.student_registration")->where('id', $request->student_id)->first();
                            if ($found) {
                                $selectedStudent = $found;
                                break;
                            }
                        } catch (\Throwable $e) {}
                    }
                }
            }
        } elseif ($request->filled('scholar_no')) {
            $selectedStudent = DB::connection('dynamic')
                ->table('student_registration')
                ->where('scholar_no', $request->scholar_no)
                ->first();
            if (!$selectedStudent) {
                $sessions = $this->getAvailableAcademicDatabases($request);
                foreach ($sessions as $db) {
                    if ($db) {
                        try {
                            $found = DB::table("$db.student_registration")->where('scholar_no', $request->scholar_no)->first();
                            if ($found) {
                                $selectedStudent = $found;
                                break;
                            }
                        } catch (\Throwable $e) {}
                    }
                }
            }
        }

        $sessions = $this->getAvailableAcademicDatabases($request);

        if ($selectedStudent) {
            $selectedStudent->father_name = $this->resolveStudentFatherName($selectedStudent);
            $selectedStudent->section_name = $this->resolveStudentSection($selectedStudent, $sessions);

            $studentBalance = $this->calculateStudentRefundableBalance($selectedStudent->id, 'CAUTION MONEY');
            $headWise = $this->getStudentHeadWiseBalances($selectedStudent->id, 'CAUTION MONEY', $selectedStudent->scholar_no ?? null);
            $studentBalance = array_merge($studentBalance, $headWise);
            $studentBalance['father_name'] = $selectedStudent->father_name;
            $studentBalance['section_name'] = $selectedStudent->section_name;
        }

        // Fetch all active/registered students for instant search
        $students = DB::connection('dynamic')
            ->table('student_registration')
            ->whereNotNull('student_name')
            ->where('student_name', '!=', '')
            ->orderBy('student_name')
            ->select('id', 'student_name', 'scholar_no', 'class_name', 'section_name', 'json_str')
            ->get()
            ->map(function ($s) use ($sessions) {
                $s->father_name = $this->resolveStudentFatherName($s);
                $s->section_name = $this->resolveStudentSection($s, $sessions);
                return $s;
            });

        $bankAccounts = self::BANK_ACCOUNTS;
        $refundHeads = self::REFUNDABLE_HEADS;

        return view('backend.Fees.refund_voucher_create', compact(
            'suggestedVoucherNo',
            'students',
            'selectedStudent',
            'studentBalance',
            'bankAccounts',
            'refundHeads',
            'sessionName'
        ));
    }

    /**
     * Store newly created refund voucher
     */
    public function store(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

        $validated = $request->validate([
            'voucher_no' => ['required', 'string', 'max:60', Rule::unique('refund_vouchers', 'voucher_no')],
            'voucher_date' => ['required', 'date'],
            'student_id' => ['required', 'integer'],
            'refund_head' => ['required', 'string', 'max:100'],
            'refund_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_mode' => ['required', 'in:Cheque,Cash,Online/NEFT'],
            'cheque_no' => ['nullable', 'required_if:payment_mode,Cheque', 'string', 'max:50'],
            'cheque_date' => ['nullable', 'required_if:payment_mode,Cheque', 'date'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'cash_account' => ['nullable', 'string', 'max:150'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'is_discontinued' => ['nullable', 'boolean'],
        ]);

        $student = DB::connection('dynamic')
            ->table('student_registration')
            ->where('id', $validated['student_id'])
            ->first();

        if (!$student) {
            $sessions = $this->getAvailableAcademicDatabases($request);
            foreach ($sessions as $db) {
                if ($db) {
                    try {
                        $found = DB::table("$db.student_registration")->where('id', $validated['student_id'])->first();
                        if ($found) {
                            $student = $found;
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        if (!$student) {
            return back()->withInput()->with('error', 'Selected student record not found.');
        }

        // Check available refundable balance
        $balanceInfo = $this->calculateStudentRefundableBalance($student->id, $validated['refund_head']);
        $availableBalance = $balanceInfo['available_balance'];

        if ((float) $validated['refund_amount'] > (float) $availableBalance) {
            return back()->withInput()->with('error', sprintf(
                'Refund amount (₹%s) exceeds available refundable balance (₹%s) for %s.',
                number_format($validated['refund_amount'], 2),
                number_format($availableBalance, 2),
                $validated['refund_head']
            ));
        }

        // Extract father name and section
        $sessions = $this->getAvailableAcademicDatabases($request);
        $fatherName = $this->resolveStudentFatherName($student);
        $sectionName = $this->resolveStudentSection($student, $sessions);

        // Check if student has a TC
        $tcId = null;
        try {
            if ($this->tableExists(null, 'tc_certificates')) {
                $tc = DB::connection('dynamic')->table('tc_certificates')
                    ->where('student_id', $student->id)
                    ->orWhere('scholar_no', $student->scholar_no)
                    ->first();
                if ($tc) {
                    $tcId = $tc->id;
                }
            }
        } catch (\Throwable $e) {}

        $sessionName = $student->session_name ?: $this->resolveSelectedSessionYear($request);
        $user = PermissionService::getCurrentUser();

        $voucher = DB::connection('dynamic')->transaction(function () use ($validated, $student, $fatherName, $sectionName, $tcId, $balanceInfo, $sessionName, $user) {
            return RefundVoucher::create([
                'voucher_no' => $validated['voucher_no'],
                'voucher_date' => $validated['voucher_date'],
                'student_id' => $student->id,
                'scholar_no' => $student->scholar_no,
                'student_name' => $student->student_name,
                'class_name' => $student->class_name,
                'section_name' => $sectionName,
                'father_name' => $fatherName,
                'tc_id' => $tcId,
                'is_discontinued' => (bool) ($validated['is_discontinued'] ?? false),
                'refund_head' => strtoupper($validated['refund_head']),
                'refundable_amount' => $balanceInfo['available_balance'],
                'refund_amount' => $validated['refund_amount'],
                'payment_mode' => $validated['payment_mode'],
                'cheque_no' => $validated['cheque_no'] ?? null,
                'cheque_date' => $validated['cheque_date'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'cash_account' => $validated['cash_account'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'completed',
                'created_by' => $user ? $user->id : null,
                'created_by_name' => $user ? ($user->name ?? $user->email) : 'Admin',
                'session_name' => $sessionName,
            ]);
        });

        return redirect()
            ->route('refund-vouchers.print', $voucher->id)
            ->with('success', "Refund Voucher #{$voucher->voucher_no} generated successfully. Student ledger updated automatically.");
    }

    /**
     * Show single refund voucher
     */
    public function show(Request $request, int $id)
    {
        $this->applySessionDatabaseBindings($request);

        $voucher = RefundVoucher::findOrFail($id);
        $balanceInfo = $this->calculateStudentRefundableBalance($voucher->student_id, $voucher->refund_head);

        return view('backend.Fees.refund_voucher_show', compact('voucher', 'balanceInfo'));
    }

    /**
     * Printable Voucher Slip
     */
    public function print(Request $request, int $id)
    {
        $this->applySessionDatabaseBindings($request);

        $voucher = RefundVoucher::findOrFail($id);

        return view('backend.Fees.refund_voucher_print', compact('voucher'));
    }

    /**
     * Printable Register / Report
     */
    public function printRegister(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

        $query = RefundVoucher::query()->orderBy('voucher_date')->orderBy('id');

        if ($request->filled('from_date')) {
            $query->whereDate('voucher_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('voucher_date', '<=', $request->to_date);
        }
        if ($request->filled('voucher_no')) {
            $query->where('voucher_no', 'like', '%' . trim($request->voucher_no) . '%');
        }
        if ($request->filled('scholar_no')) {
            $search = trim($request->scholar_no);
            $query->where(function ($q) use ($search) {
                $q->where('scholar_no', 'like', "%{$search}%")
                    ->orWhere('student_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('class_name')) {
            $query->where('class_name', $request->class_name);
        }
        if ($request->filled('refund_head')) {
            $query->where('refund_head', $request->refund_head);
        }
        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        $vouchers = $query->get();
        $totalAmount = $vouchers->where('status', 'completed')->sum('refund_amount');

        return view('backend.Fees.refund_voucher_register_print', compact('vouchers', 'totalAmount', 'request'));
    }

    /**
     * Cancel an existing refund voucher (restoring the balance)
     */
    public function cancel(Request $request, int $id)
    {
        $this->applySessionDatabaseBindings($request);

        $voucher = RefundVoucher::findOrFail($id);

        if ($voucher->status === 'cancelled') {
            return back()->with('error', 'This voucher is already cancelled.');
        }

        $reason = $request->input('cancel_reason', 'Cancelled by administrator');
        $user = PermissionService::getCurrentUser();

        $voucher->update([
            'status' => 'cancelled',
            'cancel_reason' => $reason,
            'cancelled_at' => now(),
            'cancelled_by' => $user ? $user->id : null,
        ]);

        return redirect()->route('refund-vouchers.index')
            ->with('success', "Refund Voucher #{$voucher->voucher_no} has been cancelled. Available balance is restored in student ledger.");
    }

    /**
     * AJAX endpoint to search students by Scholar No or Name
     */
    public function searchStudent(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

        $search = trim($request->get('q', ''));
        if (strlen($search) < 1) {
            return response()->json([]);
        }

        $students = DB::connection('dynamic')
            ->table('student_registration')
            ->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('scholar_no', 'like', "%{$search}%")
                    ->orWhere('form_number', 'like', "%{$search}%");
            })
            ->orderBy('student_name')
            ->limit(25)
            ->get(['id', 'student_name', 'scholar_no', 'class_name', 'section_name', 'json_str', 'status']);

        $isMysql = (Config::get('database.connections.dynamic.driver') === 'mysql' || Config::get('database.default') === 'mysql');
        if ($students->isEmpty() && $isMysql) {
            $sessions = $this->getAvailableAcademicDatabases($request);
            foreach ($sessions as $db) {
                if ($db) {
                    try {
                        $found = DB::table("$db.student_registration")
                            ->where(function ($q) use ($search) {
                                $q->where('student_name', 'like', "%{$search}%")
                                    ->orWhere('scholar_no', 'like', "%{$search}%")
                                    ->orWhere('form_number', 'like', "%{$search}%");
                            })
                            ->orderBy('student_name')
                            ->limit(25)
                            ->get(['id', 'student_name', 'scholar_no', 'class_name', 'section_name', 'json_str', 'status']);
                        if ($found->isNotEmpty()) {
                            $students = $found;
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        $head = $request->get('head', 'CAUTION MONEY');
        $sessions = $this->getAvailableAcademicDatabases($request);

        $results = $students->map(function ($student) use ($head, $sessions) {
            $fatherName = $this->resolveStudentFatherName($student);
            $section = $this->resolveStudentSection($student, $sessions);

            // Check TC / Discontinued status
            $hasTc = false;
            try {
                if ($this->tableExists(null, 'tc_certificates')) {
                    $hasTc = DB::connection('dynamic')->table('tc_certificates')
                        ->where('student_id', $student->id)
                        ->orWhere('scholar_no', $student->scholar_no)
                        ->exists();
                }
            } catch (\Throwable $e) {}

            $balanceInfo = $this->calculateStudentRefundableBalance($student->id, $head);

            return [
                'id' => $student->id,
                'scholar_no' => $student->scholar_no,
                'student_name' => $student->student_name,
                'class_name' => $student->class_name,
                'section_name' => $section,
                'father_name' => $fatherName,
                'has_tc' => $hasTc,
                'original_refundable' => $balanceInfo['original_refundable'],
                'already_refunded' => $balanceInfo['already_refunded'],
                'available_balance' => $balanceInfo['available_balance'],
                'text' => sprintf('%s - %s (%s%s)', $student->scholar_no, $student->student_name, $student->class_name ?? 'N/A', ($section && $section !== 'N/A') ? ' / ' . $section : ''),
            ];
        });

        return response()->json($results);
    }

    /**
     * AJAX endpoint to get student refundable balance
     */
    public function getStudentBalance(Request $request, int $studentId)
    {
        $this->applySessionDatabaseBindings($request);

        $head = $request->get('head', 'CAUTION MONEY');
        $student = DB::connection('dynamic')->table('student_registration')->where('id', $studentId)->first();
        $isMysql = (Config::get('database.connections.dynamic.driver') === 'mysql' || Config::get('database.default') === 'mysql');
        $sessions = $this->getAvailableAcademicDatabases($request);

        if (!$student && $isMysql) {
            foreach ($sessions as $db) {
                if ($db) {
                    try {
                        $found = DB::table("$db.student_registration")->where('id', $studentId)->first();
                        if ($found) {
                            $student = $found;
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        $balanceInfo = $this->calculateStudentRefundableBalance($studentId, $head);

        if ($student) {
            $balanceInfo['father_name'] = $this->resolveStudentFatherName($student);
            $balanceInfo['section_name'] = $this->resolveStudentSection($student, $sessions);
            $balanceInfo['class_name'] = $student->class_name ?? '';
            $balanceInfo['scholar_no'] = $student->scholar_no ?? '';
            $balanceInfo['student_name'] = $student->student_name ?? '';
            $hasTc = false;
            try {
                if ($this->tableExists(null, 'tc_certificates')) {
                    $hasTc = DB::connection('dynamic')->table('tc_certificates')
                        ->where('student_id', $student->id)
                        ->orWhere('scholar_no', $student->scholar_no)
                        ->exists();
                }
            } catch (\Throwable $e) {}
            $balanceInfo['has_tc'] = $hasTc;
        }

        // Add head-wise breakdown (Advance balances & Outstanding Dues from previous/current sessions)
        $headWise = $this->getStudentHeadWiseBalances($studentId, $head, $student ? $student->scholar_no : null);
        $balanceInfo = array_merge($balanceInfo, $headWise);

        return response()->json($balanceInfo);
    }

    /**
     * Resolve student section reliably across columns, JSON, and session databases
     */
    public function resolveStudentSection($student, array $sessions = []): string
    {
        if (!$student) {
            return 'N/A';
        }

        // 1. Direct section_name property
        if (!empty($student->section_name) && trim((string)$student->section_name) !== '') {
            return trim((string)$student->section_name);
        }

        // 2. Section in JSON str
        $json = json_decode($student->json_str ?? '{}', true) ?: [];
        if (!empty($json['section_name']) && trim((string)$json['section_name']) !== '') {
            return trim((string)$json['section_name']);
        }

        // 3. Next year section
        if (!empty($student->next_year_section) && trim((string)$student->next_year_section) !== '') {
            return trim((string)$student->next_year_section);
        }
        if (!empty($json['next_year_section']) && trim((string)$json['next_year_section']) !== '') {
            return trim((string)$json['next_year_section']);
        }

        // 4. Check across other session databases for scholar_no
        $scholarNo = $student->scholar_no ?? ($json['scholar_no'] ?? null);
        if (!empty($scholarNo) && !empty($sessions)) {
            foreach ($sessions as $db) {
                if ($db) {
                    try {
                        $other = DB::table("$db.student_registration")
                            ->where('scholar_no', $scholarNo)
                            ->whereNotNull('section_name')
                            ->where('section_name', '!=', '')
                            ->value('section_name');
                        if (!empty($other) && trim((string)$other) !== '') {
                            return trim((string)$other);
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        return 'N/A';
    }

    /**
     * Resolve student father name reliably across JSON and object properties
     */
    public function resolveStudentFatherName($student): string
    {
        if (!$student) {
            return '';
        }
        $json = json_decode($student->json_str ?? '{}', true) ?: [];
        $father = $json['student_father_name'] ?? $json['fathername'] ?? $student->father_name ?? '';
        return trim((string)$father);
    }

    /**
     * Get available academic session database names in chronological order
     */
    public function getAvailableAcademicDatabases(?Request $request = null): array
    {
        $currentSession = $this->resolveSelectedSessionYear($request) ?: (Config::get('database.connections.dynamic.database') ?: '2026_2027');
        $currStart = 2026;
        if (preg_match('/^(\d{4})[_-](\d{4})$/', (string)$currentSession, $m)) {
            $currStart = (int) $m[1];
        }
        $prevSession = ($currStart - 1) . '_' . ($currStart);
        $currSession = $currStart . '_' . ($currStart + 1);

        $isMysql = (Config::get('database.connections.dynamic.driver') === 'mysql' || Config::get('database.default') === 'mysql');
        $sessionsToCheck = [];

        if ($isMysql) {
            try {
                $availableDbs = array_map(function($d) {
                    return array_values((array)$d)[0];
                }, DB::select("SHOW DATABASES"));

                $matchingDbs = [];
                foreach ($availableDbs as $dName) {
                    if (preg_match('/^(\d{4})[_-](\d{4})$/', $dName, $dm)) {
                        $matchingDbs[$dName] = (int) $dm[1];
                    }
                }
                asort($matchingDbs);

                foreach ($matchingDbs as $dName => $sYear) {
                    if ($sYear <= $currStart || $dName === $currentSession) {
                        $sessionsToCheck[] = $dName;
                    }
                }
                if (in_array($prevSession, $availableDbs) && !in_array($prevSession, $sessionsToCheck)) {
                    $sessionsToCheck[] = $prevSession;
                }
                if (in_array($currSession, $availableDbs) && !in_array($currSession, $sessionsToCheck)) {
                    $sessionsToCheck[] = $currSession;
                }
                if (in_array($currentSession, $availableDbs) && !in_array($currentSession, $sessionsToCheck)) {
                    $sessionsToCheck[] = $currentSession;
                }
            } catch (\Throwable $e) {}
        }

        if (empty($sessionsToCheck)) {
            $sessionsToCheck[] = null; // null uses active dynamic connection
        }

        return array_values(array_unique($sessionsToCheck));
    }

    /**
     * Check if a table exists in dynamic connection or specific database
     */
    private function tableExists(?string $db, string $table): bool
    {
        if (!$db) {
            return Schema::connection('dynamic')->hasTable($table);
        }

        try {
            $res = DB::select("SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1", [$db, $table]);
            return !empty($res);
        } catch (\Throwable $e) {
            try {
                DB::select("SELECT 1 FROM `{$db}`.`{$table}` LIMIT 0");
                return true;
            } catch (\Throwable $e2) {
                return false;
            }
        }
    }

    /**
     * Calculate head-wise outstanding dues and advance balances across previous and current sessions
     */
    public function getStudentHeadWiseBalances(int $studentId, string $currentHead = 'CAUTION MONEY', ?string $scholarNo = null): array
    {
        $sessionsToCheck = $this->getAvailableAcademicDatabases();
        $isMysql = (Config::get('database.connections.dynamic.driver') === 'mysql' || Config::get('database.default') === 'mysql');

        // Locate student in dynamic connection or session databases
        $student = DB::connection('dynamic')->table('student_registration')->where('id', $studentId)->first();

        if (!$student && $isMysql) {
            foreach ($sessionsToCheck as $db) {
                if ($db) {
                    try {
                        $found = DB::table("$db.student_registration")->where('id', $studentId)->first();
                        if ($found) {
                            $student = $found;
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        if (!$student && !empty($scholarNo) && $isMysql) {
            foreach ($sessionsToCheck as $db) {
                if ($db) {
                    try {
                        $found = DB::table("$db.student_registration")->where('scholar_no', $scholarNo)->first();
                        if ($found) {
                            $student = $found;
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        if (!$student) {
            return [
                'head_wise_details' => [],
                'total_advance' => 0.0,
                'total_dues' => 0.0,
                'net_balance' => 0.0,
                'has_outstanding_dues' => false,
            ];
        }

        $scholarNo = $student->scholar_no;
        $headWiseDetails = [];
        $totalAdvance = 0.0;
        $totalDues = 0.0;

        // 1. ADVANCE / REFUNDABLE BALANCES (Caution Money and Advance Fees)
        $cautionMoneyAmount = 0.0;
        $cautionFound = false;

        foreach ($sessionsToCheck as $db) {
            try {
                if (!$this->tableExists($db, 'totalnextyear')) continue;

                $tnyQuery = $db ? DB::table("$db.totalnextyear") : DB::connection('dynamic')->table('totalnextyear');
                $tnyRows = $tnyQuery->where('scholar_no', $scholarNo)->where('is_delete', 0)->get();

                foreach ($tnyRows as $r) {
                    $acc = strtoupper(trim($r->account_name));
                    if (in_array($acc, ['CAUTION MONEY', 'CUATION MONEY'])) {
                        $cautionMoneyAmount = max($cautionMoneyAmount, (float) $r->fees);
                        $cautionFound = true;
                    }
                }
            } catch (\Throwable $e) {}
        }

        // If not found in totalnextyear, check receipts across sessions
        if (!$cautionFound) {
            foreach ($sessionsToCheck as $db) {
                try {
                    if (!$this->tableExists($db, 'feesreceiptchallan')) continue;

                    $chQuery = $db ? DB::table("$db.feesreceiptchallan") : DB::connection('dynamic')->table('feesreceiptchallan');
                    $challans = $chQuery->where('student_id', $student->id)->where('is_delete', 0)->get(['str_json']);

                    foreach ($challans as $ch) {
                        $cData = is_string($ch->str_json) ? (json_decode($ch->str_json, true) ?: []) : (array)$ch->str_json;
                        if (!empty($cData['head_name'])) {
                            $hNames = is_array($cData['head_name']) ? $cData['head_name'] : explode(',', (string)$cData['head_name']);
                            $hRecAmts = is_array($cData['head_rec_ammount'] ?? '') ? $cData['head_rec_ammount'] : explode(',', (string)($cData['head_rec_ammount'] ?? ''));
                            foreach ($hNames as $idx => $hName) {
                                if (in_array(strtoupper(trim($hName)), ['CAUTION MONEY', 'CUATION MONEY'])) {
                                    $cautionMoneyAmount += floatval($hRecAmts[$idx] ?? 0);
                                    $cautionFound = true;
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        if (!$cautionFound || $cautionMoneyAmount <= 0) {
            $cautionMoneyAmount = self::DEFAULT_CAUTION_MONEY;
        }

        // Deduct completed refund vouchers for this student
        $alreadyRefunded = 0.0;
        try {
            if ($this->tableExists(null, 'refund_vouchers')) {
                $alreadyRefunded = (float) DB::connection('dynamic')->table('refund_vouchers')
                    ->where(function ($q) use ($student, $scholarNo) {
                        $q->where('student_id', $student->id);
                        if (!empty($scholarNo)) {
                            $q->orWhere('scholar_no', $scholarNo);
                        }
                    })
                    ->whereIn('refund_head', ['CAUTION MONEY', 'CUATION MONEY'])
                    ->where('status', 'completed')
                    ->whereNull('deleted_at')
                    ->sum('refund_amount');
            }
        } catch (\Throwable $e) {}

        $netCautionMoney = max(0.0, $cautionMoneyAmount - $alreadyRefunded);
        $headWiseDetails[] = [
            'head_name' => 'CAUTION MONEY',
            'type' => 'advance',
            'type_label' => 'Advance / Refundable',
            'badge_class' => 'badge-success text-white',
            'session' => 'Opening / Security Deposit',
            'class_name' => $student->class_name ?: '-',
            'term' => 'Caution Money',
            'fee_amount' => (float)$cautionMoneyAmount,
            'paid_amount' => (float)$cautionMoneyAmount,
            'balance_amount' => (float)$netCautionMoney,
            'balance_display' => '+' . number_format($netCautionMoney, 2),
            'due_date' => '-',
            'remarks' => $alreadyRefunded > 0 ? "Already Refunded: ₹" . number_format($alreadyRefunded, 2) : "Available Refundable Balance",
        ];
        $totalAdvance += $netCautionMoney;

        // Check if totalnextyear has other advance credit heads
        foreach ($sessionsToCheck as $db) {
            try {
                if (!$this->tableExists($db, 'totalnextyear')) continue;

                $tnyQuery = $db ? DB::table("$db.totalnextyear") : DB::connection('dynamic')->table('totalnextyear');
                $tnyRows = $tnyQuery->where('scholar_no', $scholarNo)->where('is_delete', 0)->get();

                foreach ($tnyRows as $r) {
                    $acc = strtoupper(trim($r->account_name));
                    if (in_array($acc, ['CAUTION MONEY', 'CUATION MONEY', 'ADMISSION FEES', 'ALUMNI FEES'])) {
                        continue;
                    }
                    $advAmt = (float) $r->fees;
                    if ($advAmt > 0 && strtoupper(trim($r->received_type ?? '')) !== 'DUE') {
                        $headWiseDetails[] = [
                            'head_name' => $acc,
                            'type' => 'advance',
                            'type_label' => 'Advance Fee Credit',
                            'badge_class' => 'badge-success text-white',
                            'session' => $db ? str_replace('_', '-', $db) : 'Advance Account',
                            'class_name' => $student->class_name ?: '-',
                            'term' => 'Advance Deposit',
                            'fee_amount' => $advAmt,
                            'paid_amount' => $advAmt,
                            'balance_amount' => $advAmt,
                            'balance_display' => '+' . number_format($advAmt, 2),
                            'due_date' => $r->fees_date ?? '-',
                            'remarks' => 'Advance balance from totalnextyear',
                        ];
                        $totalAdvance += $advAmt;
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 2. OUTSTANDING DUES (Check each session database)
        foreach ($sessionsToCheck as $db) {
            $sessionDisplay = $db ? str_replace('_', '-', $db) : 'Current Session';

            try {
                // Find student record in this session
                $sessionStu = null;
                if ($db) {
                    if ($this->tableExists($db, 'student_registration')) {
                        $found = DB::table("$db.student_registration")->where('scholar_no', $scholarNo)->first();
                        if ($found) {
                            $sessionStu = $found;
                        } else {
                            // Student was not enrolled in this session database
                            continue;
                        }
                    }
                } else {
                    $sessionStu = $student;
                }

                if (!$sessionStu) {
                    continue;
                }

                $sId = $sessionStu->id;
                $sClass = $sessionStu->class_name ?: $student->class_name;

                // 2a. Check duechart / fee structure
                $hasDcs = $this->tableExists($db, 'generate_duechartstatus');
                $duechartRows = collect();
                if ($hasDcs) {
                    $duechartQuery = $db ? DB::table("$db.generate_duechartstatus") : DB::connection('dynamic')->table('generate_duechartstatus');
                    $duechartRows = $duechartQuery->where('student_id', $sId)->get();
                }

                if ($duechartRows->isEmpty() && !empty($sClass)) {
                    if ($this->tableExists($db, 'course_fees_structure_master')) {
                        $structQuery = $db ? DB::table("$db.course_fees_structure_master") : DB::connection('dynamic')->table('course_fees_structure_master');
                        $duechartRows = $structQuery->where('class_name', $sClass)->where('is_delete', 0)->get();
                    }
                }

                $decodedStructures = [];
                foreach ($duechartRows as $row) {
                    $rawJson = $row->json_str ?? null;
                    if (!$rawJson) continue;
                    $j = is_string($rawJson) ? json_decode($rawJson, true) : (array) $rawJson;

                    if (is_array($j)) {
                        if (isset($j[0]) && is_array($j[0]) && !empty($j[0]['json_str'])) {
                            $inner = is_string($j[0]['json_str']) ? json_decode($j[0]['json_str'], true) : $j[0]['json_str'];
                            if (is_array($inner) && isset($inner['account_name'])) {
                                $decodedStructures[] = $inner;
                            }
                        } elseif (isset($j['account_name'])) {
                            $decodedStructures[] = $j;
                        } elseif (isset($j[0]) && is_array($j[0]) && isset($j[0]['account_name'])) {
                            foreach ($j as $sub) {
                                if (is_array($sub) && isset($sub['account_name'])) {
                                    $decodedStructures[] = $sub;
                                }
                            }
                        }
                    }
                }

                // 2b. Payments made in this session
                $hasCh = $this->tableExists($db, 'feesreceiptchallan');
                $paidPerHeadTerm = [];
                $generalPaidPerHead = [];

                if ($hasCh) {
                    $chQuery = $db ? DB::table("$db.feesreceiptchallan") : DB::connection('dynamic')->table('feesreceiptchallan');
                    $challans = $chQuery->where('student_id', $sId)->where('is_delete', 0)->get();

                    foreach ($challans as $ch) {
                        $cData = is_string($ch->str_json) ? (json_decode($ch->str_json, true) ?: []) : (array)$ch->str_json;
                        if (!empty($cData['head_name'])) {
                            $hNames = is_array($cData['head_name']) ? $cData['head_name'] : explode(',', (string)$cData['head_name']);
                            $hAmts = is_array($cData['head_rec_ammount'] ?? '') ? $cData['head_rec_ammount'] : explode(',', (string)($cData['head_rec_ammount'] ?? ''));
                            $terms = is_array($cData['term_str'] ?? '') ? $cData['term_str'] : explode(',', (string)($cData['term_str'] ?? ''));

                            foreach ($hNames as $idx => $hn) {
                                $hUpper = strtoupper(trim($hn));
                                $tUpper = strtoupper(trim($terms[$idx] ?? ''));
                                $amt = floatval($hAmts[$idx] ?? 0);
                                if (!empty($tUpper)) {
                                    $key = $hUpper . '__' . $tUpper;
                                    $paidPerHeadTerm[$key] = ($paidPerHeadTerm[$key] ?? 0) + $amt;
                                } else {
                                    $generalPaidPerHead[$hUpper] = ($generalPaidPerHead[$hUpper] ?? 0) + $amt;
                                }
                            }
                        }
                    }
                }

                // 2c. Match installments and compute unpaid dues
                foreach ($decodedStructures as $struct) {
                    $accountNames = (array) ($struct['account_name'] ?? []);
                    $fees = (array) ($struct['fees'] ?? []);
                    $terms = (array) ($struct['term'] ?? []);
                    $dueDates = (array) ($struct['due_date'] ?? []);

                    foreach ($accountNames as $idx => $accName) {
                        $headUpper = strtoupper(trim($accName));
                        if (in_array($headUpper, ['CAUTION MONEY', 'CUATION MONEY', 'ADMISSION FEES', 'ALUMNI FEES'])) {
                            continue;
                        }

                        $termName = trim($terms[$idx] ?? ($idx + 1) . 'th');
                        $feeAmt = floatval($fees[$idx] ?? 0);
                        if ($feeAmt <= 0) continue;

                        $termUpper = strtoupper($termName);
                        $key = $headUpper . '__' . $termUpper;

                        $paidAmt = $paidPerHeadTerm[$key] ?? 0;
                        if ($paidAmt < $feeAmt && !empty($generalPaidPerHead[$headUpper]) && $generalPaidPerHead[$headUpper] > 0) {
                            $need = $feeAmt - $paidAmt;
                            $use = min($need, $generalPaidPerHead[$headUpper]);
                            $paidAmt += $use;
                            $generalPaidPerHead[$headUpper] -= $use;
                        }

                        $dueAmt = max(0, $feeAmt - $paidAmt);

                        if ($dueAmt > 0) {
                            $displayTerm = (stripos($termName, 'installment') !== false) ? $termName : $termName . ' Installment';
                            $headWiseDetails[] = [
                                'head_name' => $headUpper,
                                'type' => 'due',
                                'type_label' => 'Outstanding Due',
                                'badge_class' => 'badge-danger text-white',
                                'session' => $sessionDisplay,
                                'class_name' => $sClass ?: '-',
                                'term' => $displayTerm,
                                'fee_amount' => (float)$feeAmt,
                                'paid_amount' => (float)$paidAmt,
                                'balance_amount' => (float)(-$dueAmt),
                                'balance_display' => '-' . number_format($dueAmt, 2),
                                'due_date' => $dueDates[$idx] ?? '-',
                                'remarks' => "Unpaid {$termName} Installment (Due: " . ($dueDates[$idx] ?? '-') . ")",
                            ];
                            $totalDues += $dueAmt;
                        }
                    }
                }
            } catch (\Throwable $e) {}
        }

        $netBalance = $totalAdvance - $totalDues;

        return [
            'head_wise_details' => $headWiseDetails,
            'total_advance' => $totalAdvance,
            'total_dues' => $totalDues,
            'net_balance' => $netBalance,
            'has_outstanding_dues' => ($totalDues > 0),
        ];
    }

    /**
     * Calculate student's refundable balance for a specific head
     */
    public function calculateStudentRefundableBalance(int $studentId, string $head = 'CAUTION MONEY'): array
    {
        $normalizedHead = strtoupper(trim($head));
        $isCautionMoney = in_array($normalizedHead, ['CAUTION MONEY', 'CUATION MONEY']);

        $student = DB::connection('dynamic')->table('student_registration')->where('id', $studentId)->first();
        $isMysql = (Config::get('database.connections.dynamic.driver') === 'mysql' || Config::get('database.default') === 'mysql');

        $sessionsToCheck = $this->getAvailableAcademicDatabases();
        if (!$student && $isMysql) {
            foreach ($sessionsToCheck as $db) {
                if ($db) {
                    try {
                        $found = DB::table("$db.student_registration")->where('id', $studentId)->first();
                        if ($found) {
                            $student = $found;
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        if (!$student) {
            return [
                'original_refundable' => 0.00,
                'already_refunded' => 0.00,
                'available_balance' => 0.00,
            ];
        }

        $scholarNo = $student->scholar_no;
        $originalRefundable = 0.00;

        if ($isCautionMoney) {
            // 1. Check totalnextyear table across sessions for Caution Money
            foreach ($sessionsToCheck as $db) {
                try {
                    if (!$this->tableExists($db, 'totalnextyear')) continue;

                    $tnyQuery = $db ? DB::table("$db.totalnextyear") : DB::connection('dynamic')->table('totalnextyear');
                    $tnyVal = $tnyQuery
                        ->where('scholar_no', $scholarNo)
                        ->whereIn('account_name', ['CAUTION MONEY', 'CUATION MONEY'])
                        ->where('is_delete', 0)
                        ->sum('fees');
                    if ($tnyVal > 0) {
                        $originalRefundable = max($originalRefundable, (float) $tnyVal);
                    }
                } catch (\Throwable $e) {}
            }

            // 2. Check feesreceiptchallan across sessions if not found
            if ($originalRefundable <= 0) {
                foreach ($sessionsToCheck as $db) {
                    try {
                        if (!$this->tableExists($db, 'feesreceiptchallan')) continue;

                        $chQuery = $db ? DB::table("$db.feesreceiptchallan") : DB::connection('dynamic')->table('feesreceiptchallan');
                        $challans = $chQuery->where('student_id', $student->id)->where('is_delete', 0)->get(['str_json']);

                        foreach ($challans as $ch) {
                            $cData = is_string($ch->str_json) ? (json_decode($ch->str_json, true) ?: []) : (array) $ch->str_json;
                            if (!empty($cData['head_name'])) {
                                $hNames = is_array($cData['head_name']) ? $cData['head_name'] : explode(',', (string) $cData['head_name']);
                                $hRecAmts = is_array($cData['head_rec_ammount'] ?? '') ? $cData['head_rec_ammount'] : explode(',', (string) ($cData['head_rec_ammount'] ?? ''));
                                foreach ($hNames as $idx => $hName) {
                                    if (in_array(strtoupper(trim($hName)), ['CAUTION MONEY', 'CUATION MONEY'])) {
                                        $originalRefundable += floatval($hRecAmts[$idx] ?? 0);
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {}
                }

                // 3. If no explicit entry in totalnextyear or receipts, use standard Caution Money (₹3,000)
                if ($originalRefundable <= 0) {
                    $originalRefundable = self::DEFAULT_CAUTION_MONEY;
                }
            }
        } else {
            // For other refundable heads, check totalnextyear across sessions
            foreach ($sessionsToCheck as $db) {
                try {
                    if (!$this->tableExists($db, 'totalnextyear')) continue;

                    $tnyQuery = $db ? DB::table("$db.totalnextyear") : DB::connection('dynamic')->table('totalnextyear');
                    $tnyVal = $tnyQuery
                        ->where('scholar_no', $scholarNo)
                        ->where('account_name', $normalizedHead)
                        ->where('is_delete', 0)
                        ->sum('fees');
                    if ($tnyVal > 0) {
                        $originalRefundable = max($originalRefundable, (float) $tnyVal);
                    }
                } catch (\Throwable $e) {}
            }
        }

        // Calculate already refunded amount from active refund vouchers
        $alreadyRefunded = 0.00;
        try {
            if ($this->tableExists(null, 'refund_vouchers')) {
                $alreadyRefunded = (float) DB::connection('dynamic')->table('refund_vouchers')
                    ->where(function ($q) use ($student, $scholarNo) {
                        $q->where('student_id', $student->id);
                        if (!empty($scholarNo)) {
                            $q->orWhere('scholar_no', $scholarNo);
                        }
                    })
                    ->where('refund_head', $normalizedHead)
                    ->where('status', 'completed')
                    ->whereNull('deleted_at')
                    ->sum('refund_amount');
            }
        } catch (\Throwable $e) {}

        $availableBalance = max(0.00, $originalRefundable - $alreadyRefunded);

        return [
            'original_refundable' => round($originalRefundable, 2),
            'already_refunded' => round($alreadyRefunded, 2),
            'available_balance' => round($availableBalance, 2),
            'head' => $normalizedHead,
            'scholar_no' => $scholarNo,
            'student_name' => $student->student_name,
        ];
    }

    /**
     * Export refund vouchers to CSV
     */
    private function exportCsv($vouchers)
    {
        $filename = 'refund_vouchers_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($vouchers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Voucher No',
                'Date',
                'Scholar No',
                'Student Name',
                'Class',
                'Section',
                'Refund Head',
                'Available Refundable',
                'Refund Amount',
                'Payment Mode',
                'Cheque No',
                'Cheque Date',
                'Bank Name',
                'Cash Account',
                'Status',
                'Created By',
                'Remarks',
            ]);

            foreach ($vouchers as $v) {
                fputcsv($handle, [
                    $v->voucher_no,
                    $v->voucher_date ? $v->voucher_date->format('d-m-Y') : '',
                    $v->scholar_no,
                    $v->student_name,
                    $v->class_name,
                    $v->section_name,
                    $v->refund_head,
                    number_format($v->refundable_amount, 2),
                    number_format($v->refund_amount, 2),
                    $v->payment_mode,
                    $v->cheque_no,
                    $v->cheque_date ? $v->cheque_date->format('d-m-Y') : '',
                    $v->bank_name,
                    $v->cash_account,
                    $v->status,
                    $v->created_by_name,
                    $v->remarks,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Ensure the dynamic DB connection is pointed to the selected session
     */
    private function applySessionDatabaseBindings(Request $request): void
    {
        $selectedYear = $this->resolveSelectedSessionYear($request);
        if (!$selectedYear) {
            return;
        }

        Config::set('database.connections.dynamic.database', $selectedYear);
        Config::set('database.default', 'dynamic');
        DB::purge('dynamic');
        DB::reconnect('dynamic');
        DB::setDefaultConnection('dynamic');
    }

    private function resolveSelectedSessionYear(?Request $request = null): ?string
    {
        if (!$request) {
            return null;
        }

        $sessionYear = $request->hasSession() ? $request->session()->get('selectedYear') : null;
        $selectedYear = $sessionYear ?: $request->cookie('selectedYear') ?: $request->input('year');

        return is_string($selectedYear) ? $selectedYear : null;
    }
}
