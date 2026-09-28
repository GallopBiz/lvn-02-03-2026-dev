<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentTcFileRequest;
use App\Models\StudentTcFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentTcFileController extends Controller
{
    public function index(Request $request)
    {
        $session = $this->sessionName($request);
        $this->bindDatabase($session);
        $this->ensureTableColumnsExist();

        [$classes, $sections] = $this->getClassesAndSections();

        $files = StudentTcFile::query()
            ->leftJoin('student_registration as students', 'students.scholar_no', '=', 'student_tc_files.scholar_no')
            ->select(
                'student_tc_files.*',
                DB::raw("COALESCE(NULLIF(student_tc_files.student_name, ''), students.student_name) as student_name"),
                DB::raw("COALESCE(NULLIF(student_tc_files.class_name, ''), students.class_name) as class_name"),
                DB::raw("COALESCE(NULLIF(student_tc_files.section_name, ''), '') as tc_section_name"),
                'students.json_str as student_json'
            )
            ->when($request->filled('search'), fn ($query) => $query->where('student_tc_files.scholar_no', 'like', '%' . $request->input('search') . '%'))
            ->latest('student_tc_files.uploaded_at')
            ->paginate(25)
            ->withQueryString();

        $files->getCollection()->transform(fn ($file) => $this->addStudentSection($file));

        return view('backend.student-tc-files.index', compact('files', 'session', 'classes', 'sections'));
    }

    public function store(StoreStudentTcFileRequest $request)
    {
        $data = $request->validated();
        $session = $this->sessionName($request);
        $this->bindDatabase($session);
        $this->ensureTableColumnsExist();

        $file = $request->file('tc_file');
        $path = 'student-tc/' . $session . '/' . Str::uuid() . '.pdf';
        Storage::disk('private')->putFileAs(dirname($path), $file, basename($path));

        $old = StudentTcFile::where('scholar_no', $data['scholar_no'])->where('session_name', $session)->first();
        $userId = Auth::guard('web')->id() ?: Auth::guard('staff')->id();

        StudentTcFile::updateOrCreate(
            ['scholar_no' => $data['scholar_no'], 'session_name' => $session],
            [
                'student_name' => $data['student_name'],
                'class_name' => $data['class_name'],
                'section_name' => $data['section_name'] ?? null,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => 'application/pdf',
                'uploaded_by' => $userId,
                'uploaded_at' => now(),
            ]
        );

        if ($old && Storage::disk('private')->exists($old->file_path)) {
            Storage::disk('private')->delete($old->file_path);
        }

        Log::info('Admin uploaded/updated TC PDF', [
            'scholar_no' => $data['scholar_no'],
            'student_name' => $data['student_name'],
            'session' => $session,
            'user_id' => $userId,
            'original_filename' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', $old ? 'TC PDF replaced.' : 'TC PDF uploaded.');
    }

    public function studentSearch(Request $request)
    {
        $this->bindDatabase($this->sessionName($request));
        $query = trim((string) $request->input('q'));
        if ($query === '') return response()->json([]);

        $students = DB::connection('dynamic')->table('student_registration')
            ->where(function ($builder) use ($query) {
                $builder->where('scholar_no', 'like', $query . '%')
                    ->orWhere('student_name', 'like', '%' . $query . '%');
            })
            ->orderBy('student_name')->limit(15)
            ->get(['scholar_no', 'student_name', 'class_name', 'json_str']);

        return response()->json($students->map(function ($student) {
            $extra = json_decode($student->json_str ?? '{}', true) ?: [];
            return [
                'scholar_no' => $student->scholar_no,
                'student_name' => $student->student_name,
                'class_name' => $student->class_name,
                'section_name' => $extra['section_name'] ?? '',
            ];
        }));
    }

    public function download(Request $request, int $file)
    {
        $this->bindDatabase($this->sessionName($request));
        $file = StudentTcFile::findOrFail($file);
        $this->assertSafePath($file->file_path);

        Log::info('Admin downloaded TC PDF', [
            'file_id' => $file->id,
            'scholar_no' => $file->scholar_no,
            'user_id' => Auth::guard('web')->id() ?: Auth::guard('staff')->id(),
        ]);

        $downloadName = 'TC_Document_' . Str::random(16) . '.pdf';

        return Storage::disk('private')->download(
            $file->file_path,
            $downloadName,
            $this->securityHeaders(['Content-Type' => 'application/pdf'])
        );
    }

    public function view(Request $request, int $file)
    {
        $this->bindDatabase($this->sessionName($request));
        $file = StudentTcFile::findOrFail($file);
        $fullPath = $this->assertSafePath($file->file_path);

        Log::info('Admin viewed TC PDF', [
            'file_id' => $file->id,
            'scholar_no' => $file->scholar_no,
            'user_id' => Auth::guard('web')->id() ?: Auth::guard('staff')->id(),
        ]);

        return response()->file($fullPath, $this->securityHeaders([
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="transfer-certificate-' . $file->scholar_no . '.pdf"',
        ]));
    }

    public function recordSearch(Request $request)
    {
        $this->bindDatabase($this->sessionName($request));
        $this->ensureTableColumnsExist();
        $search = trim((string) $request->input('q'));
        if ($search === '') return response()->json([]);

        return response()->json(StudentTcFile::query()
            ->leftJoin('student_registration as students', 'students.scholar_no', '=', 'student_tc_files.scholar_no')
            ->where(function ($q) use ($search) {
                $q->where('student_tc_files.scholar_no', 'like', '%' . $search . '%')
                  ->orWhere('student_tc_files.student_name', 'like', '%' . $search . '%')
                  ->orWhere('students.student_name', 'like', '%' . $search . '%');
            })
            ->latest('student_tc_files.uploaded_at')->limit(25)
            ->get([
                'student_tc_files.id',
                'student_tc_files.scholar_no',
                'student_tc_files.session_name',
                'student_tc_files.uploaded_at',
                DB::raw("COALESCE(NULLIF(student_tc_files.student_name, ''), students.student_name) as student_name"),
                DB::raw("COALESCE(NULLIF(student_tc_files.class_name, ''), students.class_name) as class_name"),
                DB::raw("COALESCE(NULLIF(student_tc_files.section_name, ''), '') as tc_section_name"),
                'students.json_str as student_json'
            ])
            ->map(fn ($file) => $this->addStudentSection($file)));
    }

    public function destroy(Request $request, int $file)
    {
        $this->bindDatabase($this->sessionName($request));
        $file = StudentTcFile::findOrFail($file);
        if (Storage::disk('private')->exists($file->file_path)) {
            Storage::disk('private')->delete($file->file_path);
        }
        $file->delete();

        Log::info('Admin deleted TC PDF', [
            'file_id' => $file->id,
            'scholar_no' => $file->scholar_no,
            'user_id' => Auth::guard('web')->id() ?: Auth::guard('staff')->id(),
        ]);

        return back()->with('success', 'TC PDF deleted.');
    }

    private function ensureTableColumnsExist(): void
    {
        if (!Schema::connection('dynamic')->hasTable('student_tc_files')) {
            return;
        }

        Schema::connection('dynamic')->table('student_tc_files', function ($table) {
            if (!Schema::connection('dynamic')->hasColumn('student_tc_files', 'student_name')) {
                $table->string('student_name', 150)->nullable()->after('scholar_no');
            }
            if (!Schema::connection('dynamic')->hasColumn('student_tc_files', 'class_name')) {
                $table->string('class_name', 100)->nullable()->after('student_name');
            }
            if (!Schema::connection('dynamic')->hasColumn('student_tc_files', 'section_name')) {
                $table->string('section_name', 100)->nullable()->after('class_name');
            }
        });
    }

    private function getClassesAndSections(): array
    {
        $classes = collect();
        $sections = collect();

        if (Schema::connection('dynamic')->hasTable('classes')) {
            $classes = DB::connection('dynamic')->table('classes')
                ->whereNotNull('class_name')
                ->where('class_name', '!=', '')
                ->orderBy('class_name')
                ->pluck('class_name')
                ->unique()
                ->values();

            $sections = DB::connection('dynamic')->table('classes')
                ->whereNotNull('section_name')
                ->where('section_name', '!=', '')
                ->orderBy('section_name')
                ->pluck('section_name')
                ->unique()
                ->values();
        }

        if ($classes->isEmpty() && Schema::connection('dynamic')->hasTable('class_name')) {
            $classes = DB::connection('dynamic')->table('class_name')
                ->whereNotNull('class_name')
                ->where('class_name', '!=', '')
                ->orderBy('class_name')
                ->pluck('class_name')
                ->unique()
                ->values();
        }

        if ($sections->isEmpty() && Schema::connection('dynamic')->hasTable('sections')) {
            $sections = DB::connection('dynamic')->table('sections')
                ->whereNotNull('section')
                ->where('section', '!=', '')
                ->orderBy('section')
                ->pluck('section')
                ->unique()
                ->values();
        }

        return [$classes, $sections];
    }

    private function assertSafePath(string $filePath): string
    {
        abort_unless(Storage::disk('private')->exists($filePath), 404, 'TC File missing on disk.');

        $fullPath = Storage::disk('private')->path($filePath);
        $basePath = storage_path('app/private/student-tc');

        $realFile = realpath($fullPath);
        $realBase = realpath($basePath);

        if (!$realFile || !$realBase || !str_starts_with($realFile, $realBase)) {
            Log::alert('Security Alert: Path traversal attempt blocked on admin TC controller', [
                'file_path' => $filePath,
                'resolved' => $realFile,
            ]);
            abort(403, 'Unauthorized file path access.');
        }

        return $realFile;
    }

    private function securityHeaders(array $merge = []): array
    {
        return array_merge([
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ], $merge);
    }

    private function sessionName(Request $request): string
    {
        $session = $request->input('session_name')
            ?: $request->session()->get('selectedYear')
            ?: $request->cookie('selectedYear')
            ?: config('database.connections.dynamic.database');
        abort_unless(is_string($session) && preg_match('/^\d{4}_\d{4}$/', $session), 422, 'Select a valid academic session.');
        return $session;
    }

    private function bindDatabase(string $session): void
    {
        Config::set('database.connections.dynamic.database', $session);
        DB::purge('dynamic');
        DB::reconnect('dynamic');
        DB::setDefaultConnection('dynamic');
    }

    private function addStudentSection($file)
    {
        if (!empty($file->tc_section_name)) {
            $file->section_name = $file->tc_section_name;
        } else {
            $details = json_decode($file->student_json ?? '{}', true) ?: [];
            $file->section_name = $details['section_name'] ?? '';
        }
        unset($file->student_json, $file->tc_section_name);
        return $file;
    }
}
