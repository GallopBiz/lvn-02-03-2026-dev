<?php

namespace App\Http\Controllers\backend;

use App\Academic\AcademicHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CharacterCertificateController extends Controller
{
    protected string $db_name;

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
            $this->db_name = Config::get('database.connections.dynamic.database', env('DB_DATABASE', '2025_2026'));
        }

        $dynamicConfig = Config::get('database.connections.dynamic');
        $dynamicConfig['database'] = $this->db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
    }

    /**
     * Show the Character Certificate creation form.
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

        // Fetch registered active students
        $students = DB::connection('dynamic')
            ->table('student_registration')
            ->select('id', 'scholar_no', 'student_name', 'class_name', 'section_name', 'date_of_birth', 'json_str')
            ->where('status', '=', 'r')
            ->orderBy('student_name')
            ->get();

        // Extract existing school classes from master table with Roman formatting
        $rawClasses = DB::connection('dynamic')->table('class_name')
            ->whereNotNull('class_name')
            ->select('class_name')
            ->distinct()
            ->pluck('class_name')
            ->all();

        if (empty($rawClasses)) {
            $rawClasses = DB::connection('dynamic')->table('classes')
                ->whereNotNull('class_name')
                ->select('class_name')
                ->distinct()
                ->pluck('class_name')
                ->all();
        }

        if (empty($rawClasses)) {
            $rawClasses = $students->pluck('class_name')->filter()->unique()->all();
        }

        $classes = [];
        foreach ($rawClasses as $c) {
            $trimmed = trim((string) $c);
            if (empty($trimmed) || in_array(strtolower($trimmed), ['test', '12th out'], true)) {
                continue;
            }
            $roman = AcademicHelper::toRoman($trimmed);
            $displayName = $roman ? "Class {$roman}" : $trimmed;
            if ($roman && $roman !== $trimmed && !str_contains($trimmed, 'Class')) {
                $displayName .= " ({$trimmed})";
            }
            $classes[] = [
                'value'   => $trimmed,
                'display' => $displayName,
            ];
        }

        // Format session as short e.g. 2025-26
        $sessionFormatted = $this->formatSessionShort($currentSession);

        // Pre-selected student if scholar_no or student_id passed in query string
        $preselectedStudent = null;
        if ($request->filled('scholar_no')) {
            $preselectedStudent = $this->resolveStudentDetails($request->scholar_no, $currentSession);
        } elseif ($request->filled('student_id')) {
            $row = $students->firstWhere('id', (int) $request->student_id);
            if ($row && !empty($row->scholar_no)) {
                $preselectedStudent = $this->resolveStudentDetails($row->scholar_no, $currentSession);
            }
        }

        return view('backend.character_certificate.form', compact(
            'students',
            'classes',
            'currentSession',
            'sessionFormatted',
            'preselectedStudent'
        ));
    }

    /**
     * AJAX endpoint to fetch student details by scholar_no.
     */
    public function getStudentDetails(Request $request)
    {
        $scholarNo = trim((string) $request->input('scholar_no'));
        if (empty($scholarNo)) {
            return response()->json(['success' => false, 'message' => 'Scholar number is required.']);
        }

        $session = $request->input('session_name') ?? $this->db_name;
        $studentDetails = $this->resolveStudentDetails($scholarNo, $session);

        if (!$studentDetails) {
            return response()->json(['success' => false, 'message' => 'Student not found.']);
        }

        return response()->json(['success' => true, 'student' => $studentDetails]);
    }

    /**
     * Generate / Print Character Certificate.
     */
    public function generateCertificate(Request $request)
    {
        $studentName = trim($request->input('student_name', ''));
        $fatherName  = trim($request->input('father_name', ''));
        $dob         = trim($request->input('dob', ''));
        $scholarNo   = trim($request->input('scholar_no', ''));

        // If accessed directly without student_name, auto-resolve from scholar_no or fallback to Aryan Borkar sample
        if (empty($studentName)) {
            $fallbackScholar = $scholarNo ?: '6884';
            $sample = $this->resolveStudentDetails($fallbackScholar, $this->db_name);
            if ($sample) {
                $studentName = $sample['student_name'];
                $fatherName  = $sample['father_name'];
                $dob         = $sample['dob'];
                $scholarNo   = $sample['scholar_no'];
            } else {
                $studentName = 'Aryan Borkar';
                $fatherName  = 'Sanjay Borkar';
                $dob         = '14-07-2008';
                $scholarNo   = '6884';
            }
        }

        $sessionYear        = $request->input('session_year', '2025-26');
        $certTitle          = $request->input('cert_title', 'CHARACTER - CERTIFICATE');
        $gender             = strtolower(trim($request->input('gender', 'male')));
        $pronounMode        = $request->input('pronoun_mode', 'both'); // 'both', 'boy', 'girl'
        $examName           = trim($request->input('exam_name', 'Senior Secondary School Certificate Examination 2026'));
        $blankHeaderHeight  = (int) $request->input('blank_header_height', 55); // in mm
        $includeBorder      = $request->boolean('include_border', true);
        $includeHeader      = $request->boolean('include_header', false);

        // Editable full paragraphs
        $paragraph1 = $request->input('paragraph_1');
        $paragraph2 = $request->input('paragraph_2');
        $paragraph3 = $request->input('paragraph_3');

        // If paragraph_1 is empty, generate default paragraph based on inputs
        if (empty($paragraph1)) {
            $paragraph1 = $this->buildDefaultParagraph1(
                $studentName,
                $fatherName,
                $dob,
                $scholarNo,
                $pronounMode
            );
        }

        if (empty($paragraph2)) {
            $heShe = ($pronounMode === 'boy') ? 'He' : (($pronounMode === 'girl') ? 'She' : 'He/ She');
            $paragraph2 = "{$heShe} passed {$examName} from this school.";
        }

        if (empty($paragraph3)) {
            $himHer = ($pronounMode === 'boy') ? 'him' : (($pronounMode === 'girl') ? 'her' : 'him/ her');
            $paragraph3 = "We wish {$himHer} bright future ahead.";
        }

        $certificate = [
            'session_year'        => $sessionYear,
            'cert_title'          => $certTitle,
            'student_name'        => $studentName,
            'father_name'         => $fatherName,
            'dob'                 => $dob,
            'scholar_no'          => $scholarNo,
            'exam_name'           => $examName,
            'paragraph_1'         => $paragraph1,
            'paragraph_2'         => $paragraph2,
            'paragraph_3'         => $paragraph3,
            'blank_header_height' => $blankHeaderHeight,
            'include_border'      => $includeBorder,
            'include_header'      => $includeHeader,
        ];

        return view('backend.character_certificate.print', compact('certificate'));
    }

    /**
     * Resolve student details with fallback across multiple sessions if needed.
     */
    protected function resolveStudentDetails(string $scholarNo, string $preferredDb): ?array
    {
        $databases = array_unique(array_filter([
            $preferredDb,
            $this->db_name,
            session('db_names'),
            $_COOKIE['selectedYear'] ?? null,
            '2025_2026',
            '2026_2027',
            '2024_2025',
        ]));

        $student = null;
        $foundDb = $preferredDb;

        foreach ($databases as $db) {
            try {
                $row = DB::table("{$db}.student_registration")
                    ->where('scholar_no', $scholarNo)
                    ->first();
                if ($row) {
                    $student = $row;
                    $foundDb = $db;
                    break;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if (!$student) {
            return null;
        }

        $json = json_decode($student->json_str ?? '{}', true) ?: [];

        $studentName = $json['studentname'] ?? $student->student_name ?? '';
        $fatherName  = $json['fathername'] ?? $student->father_name ?? '';
        $rawDob      = $json['student_dob'] ?? $student->date_of_birth ?? '';
        $dob         = $this->formatDob($rawDob);
        $gender      = strtolower($json['gender'] ?? 'male');
        $className   = $json['classname'] ?? $student->class_name ?? '';
        $sectionName = $json['section_name'] ?? $student->section_name ?? '';

        // Auto-determine suggested exam passed name based on class
        $suggestedExam = 'Senior Secondary School Certificate Examination 2026';
        if (in_array(strtolower($className), ['10', 'class 10', 'x'], true)) {
            $suggestedExam = 'Secondary School Examination 2026';
        }

        $roman = AcademicHelper::toRoman($className);
        $classDisplay = $roman ? "Class {$roman}" : $className;

        return [
            'scholar_no'      => $student->scholar_no,
            'student_name'    => $studentName,
            'father_name'     => $fatherName,
            'dob'             => $dob,
            'gender'          => $gender,
            'class_name'      => $className,
            'class_display'   => $classDisplay,
            'section_name'    => $sectionName,
            'session_short'   => $this->formatSessionShort($foundDb),
            'suggested_exam'  => $suggestedExam,
            'default_p1_both' => $this->buildDefaultParagraph1($studentName, $fatherName, $dob, $student->scholar_no, 'both'),
            'default_p1_boy'  => $this->buildDefaultParagraph1($studentName, $fatherName, $dob, $student->scholar_no, 'boy'),
            'default_p1_girl' => $this->buildDefaultParagraph1($studentName, $fatherName, $dob, $student->scholar_no, 'girl'),
        ];
    }

    /**
     * Build the default Paragraph 1 text matching the exact reference format.
     */
    public function buildDefaultParagraph1(
        string $studentName,
        string $fatherName,
        string $dob,
        string $scholarNo,
        string $pronounMode = 'both'
    ): string {
        if ($pronounMode === 'boy') {
            $salutation = 'Mast.';
            $relation   = 'Son of Mr.';
            $heShe      = 'He';
            $hisHer     = 'His';
            $hisHerDob  = 'His';
        } elseif ($pronounMode === 'girl') {
            $salutation = 'Ms';
            $relation   = 'Daughter of Mr.';
            $heShe      = 'She';
            $hisHer     = 'Her';
            $hisHerDob  = 'Her';
        } else {
            // Default slash format exactly as shown in reference image
            $salutation = 'Mast./Ms';
            $relation   = 'Son/ Daughter of Mr.';
            $heShe      = 'He/ She';
            $hisHer     = 'His/ Her';
            $hisHerDob  = 'His/Her';
        }

        $sNameHtml = !empty($studentName) ? "<u>{$studentName}</u>" : '<u>____________________</u>';
        $fNameHtml = !empty($fatherName)  ? "<u>{$fatherName}</u>"  : '<u>____________________</u>';
        $dobHtml   = !empty($dob)         ? "<u>{$dob}</u>"         : '<u>____________</u>';
        $schHtml   = !empty($scholarNo)   ? "<u>{$scholarNo}</u>"   : '<u>________</u>';

        return "This is to certify that {$salutation} {$sNameHtml} {$relation} {$fNameHtml} was a regular student of this institution. {$heShe} was very sincere in studies, took active part in various extra-curricular activities. {$hisHer} conduct was good, with positive attitude. {$hisHerDob} date of birth according to school record is {$dobHtml} and Scholar No. is {$schHtml}.";
    }

    /**
     * Format a session string (e.g. 2025_2026 or 2025-2026) to short form '2025-26'.
     */
    public function formatSessionShort($session): string
    {
        $session = trim((string) $session);
        if (preg_match('/(\d{4})[_\-\–\/\s]+(\d{2,4})/', $session, $matches)) {
            $startYear = $matches[1];
            $endYear = $matches[2];
            $endYearShort = (strlen($endYear) === 4) ? substr($endYear, 2, 2) : $endYear;
            return "{$startYear}-{$endYearShort}";
        }
        return '2025-26';
    }

    /**
     * Format date of birth to standard 'd-m-Y' (e.g., 14-07-2008).
     */
    public function formatDob($dob): string
    {
        if (empty($dob)) {
            return '';
        }

        $dob = trim((string) $dob);

        // Fix malformed year e.g. 14-07-0008 or 14-07-08
        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](00)?(\d{2})$/', $dob, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $y = (int) $m[4];
            $year = ($y < 50) ? (2000 + $y) : (1900 + $y);
            return "{$day}-{$month}-{$year}";
        }

        try {
            return Carbon::parse($dob)->format('d-m-Y');
        } catch (\Exception $e) {
            return $dob;
        }
    }
}

