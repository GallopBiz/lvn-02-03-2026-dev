<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentTcFileRequest;
use App\Models\StudentTcFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentTcFileController extends Controller
{
    public function index(Request $request)
    {
        $session = $this->sessionName($request);
        $this->bindDatabase($session);
        $files = StudentTcFile::query()
            ->leftJoin('student_registration as students', 'students.scholar_no', '=', 'student_tc_files.scholar_no')
            ->select('student_tc_files.*', 'students.student_name', 'students.class_name', 'students.json_str as student_json')
            ->when($request->filled('search'), fn ($query) => $query->where('student_tc_files.scholar_no', 'like', '%' . $request->input('search') . '%'))
            ->latest('student_tc_files.uploaded_at')
            ->paginate(25)
            ->withQueryString();
        $files->getCollection()->transform(fn ($file) => $this->addStudentSection($file));
        return view('backend.student-tc-files.index', compact('files', 'session'));
    }

    public function store(StoreStudentTcFileRequest $request)
    {
        $data = $request->validated();
        $session = $this->sessionName($request);
        $this->bindDatabase($session);
        $student = DB::connection('dynamic')->table('student_registration')->where('scholar_no', $data['scholar_no'])->first();
        if (!$student) {
            throw ValidationException::withMessages(['scholar_no' => 'Select a valid Scholar No. from student registration.']);
        }

        $file = $request->file('tc_file');
        $path = 'student-tc/' . $session . '/' . Str::uuid() . '.pdf';
        Storage::disk('private')->putFileAs(dirname($path), $file, basename($path));

        $old = StudentTcFile::where('scholar_no', $data['scholar_no'])->where('session_name', $session)->first();
        $userId = Auth::guard('web')->id() ?: Auth::guard('staff')->id();

        StudentTcFile::updateOrCreate(
            ['scholar_no' => $data['scholar_no'], 'session_name' => $session],
            [
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
        $search = trim((string) $request->input('q'));
        if ($search === '') return response()->json([]);
        return response()->json(StudentTcFile::query()
            ->leftJoin('student_registration as students', 'students.scholar_no', '=', 'student_tc_files.scholar_no')
            ->where('student_tc_files.scholar_no', 'like', '%' . $search . '%')
            ->latest('student_tc_files.uploaded_at')->limit(25)
            ->get(['student_tc_files.id', 'student_tc_files.scholar_no', 'student_tc_files.session_name', 'student_tc_files.uploaded_at', 'students.student_name', 'students.class_name', 'students.json_str as student_json'])
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
        $details = json_decode($file->student_json ?? '{}', true) ?: [];
        $file->section_name = $details['section_name'] ?? '';
        unset($file->student_json);
        return $file;
    }
}
