<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BonafideCertificateController extends Controller
{
    protected $db_name;

    public function __construct(Request $request)
    {
        if (!empty($request->session)) {
            $this->db_name = $request->session;
        } elseif (!empty($request->session_name)) {
            $this->db_name = $request->session_name;
        } elseif (isset($_COOKIE['selectedYear'])) {
            $this->db_name = $_COOKIE['selectedYear'];
        } elseif (session()->has('db_names')) {
            $this->db_name = session('db_names');
        } else {
            $this->db_name = Config::get('database.connections.dynamic.database', env('DB_DATABASE', '2026_2027'));
        }

        $dynamicConfig = Config::get("database.connections.dynamic");
        $dynamicConfig['database'] = $this->db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
    }

    /**
     * Show the certificate form
     */
    public function showForm(Request $request)
    {
        $currentSession = $request->session_name
            ?? $request->session
            ?? session('db_names')
            ?? ($_COOKIE['selectedYear'] ?? $this->db_name);

        if ($currentSession && Config::get('database.connections.dynamic.database') !== $currentSession) {
            Config::set('database.connections.dynamic.database', $currentSession);
            DB::purge('dynamic');
            DB::reconnect('dynamic');
        }

        $data_student_name = DB::connection('dynamic')
            ->table('student_registration')
            ->select('student_name', 'id', 'scholar_no')
            ->where('status', '=', 'r')
            ->orderBy('student_name')
            ->get();

        return view('backend.bonafide', compact('data_student_name', 'currentSession'));
    }

    /**
     * Get students by session via AJAX
     */
    public function getStudentsBySession(Request $request)
    {
        $session = $request->session_name ?? $request->session ?? $_COOKIE['selectedYear'] ?? '2025_2026';
        try {
            $students = DB::table("{$session}.student_registration")
                ->select('student_name', 'id', 'scholar_no')
                ->where('status', '=', 'r')
                ->orderBy('student_name')
                ->get();

            return response()->json(['success' => true, 'students' => $students]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'students' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * Format raw session (e.g. 2025_2026) to standard full (2025–2026) and short (2025–26) displays
     */
    public function formatSession($session)
    {
        $session = trim((string) $session);
        if (preg_match('/(\d{4})[_\-\–\/\s]+(\d{2,4})/', $session, $matches)) {
            $startYear = $matches[1];
            $endYear = $matches[2];
            if (strlen($endYear) === 2) {
                $endYearFull = substr($startYear, 0, 2) . $endYear;
                $endYearShort = $endYear;
            } else {
                $endYearFull = $endYear;
                $endYearShort = substr($endYear, 2, 2);
            }
            return [
                'full' => "{$startYear}–{$endYearFull}",
                'short' => "{$startYear}–{$endYearShort}",
            ];
        }

        return [
            'full' => $session,
            'short' => $session,
        ];
    }

    /**
     * Generate the certificate based on student data
     */
    public function generateCertificate(Request $request)
    {
        if ($request->isMethod('get') && !$request->has('scholar_no')) {
            return redirect()->route('bonafide.showForm');
        }

        $request->validate([
            'scholar_no' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $requestedSession = $request->session_name ?? $request->session ?? null;
        if ($requestedSession) {
            Config::set('database.connections.dynamic.database', $requestedSession);
            DB::purge('dynamic');
            DB::reconnect('dynamic');
        }

        $student = DB::connection('dynamic')->table('student_registration')
            ->where('scholar_no', $request->scholar_no)
            ->first();

        $detectedSession = null;
        if (!$student) {
            $fallbackDbs = array_unique(array_filter([
                $requestedSession,
                session('db_names'),
                $_COOKIE['selectedYear'] ?? null,
                '2026_2027',
                '2025_2026',
                '2024_2025',
                '2023_2024',
            ]));
            foreach ($fallbackDbs as $fallbackDb) {
                try {
                    $found = DB::table("{$fallbackDb}.student_registration")
                        ->where('scholar_no', $request->scholar_no)
                        ->first();
                    if ($found) {
                        $student = $found;
                        $detectedSession = $fallbackDb;
                        break;
                    }
                } catch (\Exception $e) {}
            }
        }

        if (!$student) {
            return back()->with('error', 'Student not found.');
        }

        $json = json_decode($student->json_str, true);

        $gender = strtolower($json['gender'] ?? '');
        if ($gender === 'female') {
            $gender_word = 'her';
            $relation = 'D/O'; // daughter of
            $he_she = 'she';
            $his_her = 'her';
        } else {
            $gender_word = 'his';
            $relation = 'S/O'; // son of
            $he_she = 'he';
            $his_her = 'his';
        }

        // Determine session value: pick from batch in place of session, keeping label as Session
        $rawBatch = null;
        if (!empty($json['batch'])) {
            $rawBatch = trim((string) $json['batch']);
        } elseif (!empty($json['batch_no'])) {
            $rawBatch = trim((string) $json['batch_no']);
        } elseif (!empty($student->batch)) {
            $rawBatch = trim((string) $student->batch);
        } elseif (!empty($request->batch)) {
            $rawBatch = trim((string) $request->batch);
        } elseif (!empty($json['intended_session'])) {
            $rawBatch = trim((string) $json['intended_session']);
        } elseif (!empty($student->session_name)) {
            $rawBatch = trim((string) $student->session_name);
        } elseif (!empty($json['session_name'])) {
            $rawBatch = trim((string) $json['session_name']);
        } elseif (!empty($requestedSession)) {
            $rawBatch = trim((string) $requestedSession);
        } elseif (!empty($detectedSession)) {
            $rawBatch = trim((string) $detectedSession);
        } elseif (session()->has('db_names')) {
            $rawBatch = session('db_names');
        } elseif (isset($_COOKIE['selectedYear'])) {
            $rawBatch = $_COOKIE['selectedYear'];
        } else {
            $rawBatch = '2025_2026';
        }

        $sessionFormatted = $this->formatSession($rawBatch);

        $merged_data = [
            'student_name'        => $json['studentname'] ?? $student->student_name ?? '',
            'father_name'         => $json['fathername'] ?? '',
            'session'             => $sessionFormatted['full'],
            'session_full'        => $sessionFormatted['full'],
            'session_short'       => $sessionFormatted['short'],
            'class'               => $json['classname'] ?? $student->class_name ?? '',
            'section'             => $json['section_name'] ?? '',
            'scholar_no'          => $student->scholar_no ?? '',
            'dob'                 => $json['student_dob'] ?? $student->date_of_birth ?? '',
            'permanent_address'   => $json['permanent_address'] ?? '',
            'local_address'       => $json['present_address'] ?? '',
            'fees_paid'           => $json['fees_paid'] ?? '0',
            'gender_word'         => $gender_word,
            'relation'            => $relation,
            'he_she'              => $he_she,
            'his_her'             => $his_her,
        ];

        $remarks = $request->remarks ?: 'This certificate is being issued for passport application, visa processing, school admission, or any official requirement.';
        $current_date = now()->format('d-m-Y');

        return view('backend.bonafide.generated', compact('merged_data', 'current_date', 'remarks'));
    }

}
