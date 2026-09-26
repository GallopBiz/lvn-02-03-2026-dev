<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentTcFileRequest;
use App\Models\StudentTcFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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
        StudentTcFile::updateOrCreate(['scholar_no' => $data['scholar_no'], 'session_name' => $session], [
            'file_path' => $path, 'original_filename' => $file->getClientOriginalName(), 'file_size' => $file->getSize(), 'mime_type' => 'application/pdf',
            'uploaded_by' => Auth::guard('web')->id() ?: Auth::guard('staff')->id(), 'uploaded_at' => now(),
        ]);
        if ($old) Storage::disk('private')->delete($old->file_path);
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
        $this->bindDatabase($this->sessionName($request)); $file = StudentTcFile::findOrFail($file);
        abort_unless(Storage::disk('private')->exists($file->file_path), 404);
        return Storage::disk('private')->download($file->file_path, 'transfer-certificate-' . $file->scholar_no . '.pdf', ['Content-Type' => 'application/pdf']);
    }

    public function view(Request $request, int $file)
    {
        $this->bindDatabase($this->sessionName($request)); $file = StudentTcFile::findOrFail($file);
        abort_unless(Storage::disk('private')->exists($file->file_path), 404);
        return response()->file(Storage::disk('private')->path($file->file_path), ['Content-Type' => 'application/pdf']);
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
        $this->bindDatabase($this->sessionName($request)); $file = StudentTcFile::findOrFail($file);
        Storage::disk('private')->delete($file->file_path); $file->delete();
        return back()->with('success', 'TC PDF deleted.');
    }

    private function sessionName(Request $request): string
    {
        // The normal admin flow sets selectedYear. When this standalone page is
        // opened directly, use Laravel's configured dynamic database instead.
        $session = $request->input('session_name')
            ?: $request->session()->get('selectedYear')
            ?: $request->cookie('selectedYear')
            ?: config('database.connections.dynamic.database');
        abort_unless(is_string($session) && preg_match('/^\d{4}_\d{4}$/', $session), 422, 'Select a valid academic session.');
        return $session;
    }
    private function bindDatabase(string $session): void
    {
        Config::set('database.connections.dynamic.database', $session); DB::purge('dynamic'); DB::reconnect('dynamic'); DB::setDefaultConnection('dynamic');
    }

    private function addStudentSection($file)
    {
        $details = json_decode($file->student_json ?? '{}', true) ?: [];
        $file->section_name = $details['section_name'] ?? '';
        unset($file->student_json);
        return $file;
    }
}
