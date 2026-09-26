<?php

namespace App\Http\Controllers;

use App\Models\StudentTcFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicStudentTcDownloadController extends Controller
{
    public function index() { return view('frontend.student-tc-download'); }

    public function search(Request $request)
    {
        $data = $request->validate(['scholar_no' => ['required', 'string', 'max:50']]);
        $session = $this->activeSession();
        $this->bindDatabase($session);
        $file = StudentTcFile::where('scholar_no', $data['scholar_no'])->where('session_name', $session)->first();
        if (!$file) return back()->withInput()->with('error', 'No Transfer Certificate is available for these details.');
        $token = Str::random(64);
        $request->session()->put('student_tc_download.' . $token, ['file_id' => $file->id, 'session_name' => $session, 'expires_at' => now()->addMinutes(15)->timestamp]);
        $student = DB::connection('dynamic')->table('student_registration')->where('scholar_no', $data['scholar_no'])->first(['student_name', 'class_name', 'json_str']);
        $details = json_decode($student?->json_str ?? '{}', true) ?: [];
        $studentDetails = ['name' => $student->student_name ?? '', 'class' => $student->class_name ?? '', 'section' => $details['section_name'] ?? ''];
        return view('frontend.student-tc-download', compact('file', 'token', 'studentDetails'));
    }

    public function view(Request $request, string $token)
    {
        $access = $request->session()->get('student_tc_download.' . $token);
        abort_unless(is_array($access) && ($access['expires_at'] ?? 0) >= now()->timestamp, 403);
        $this->bindDatabase($access['session_name']); $file = StudentTcFile::findOrFail($access['file_id']);
        abort_unless(Storage::disk('private')->exists($file->file_path), 404);
        return response()->file(Storage::disk('private')->path($file->file_path), ['Content-Type' => 'application/pdf']);
    }

    public function download(Request $request, string $token)
    {
        $access = $request->session()->pull('student_tc_download.' . $token);
        abort_unless(is_array($access) && ($access['expires_at'] ?? 0) >= now()->timestamp, 403);
        $this->bindDatabase($access['session_name']); $file = StudentTcFile::findOrFail($access['file_id']);
        abort_unless(Storage::disk('private')->exists($file->file_path), 404);
        return Storage::disk('private')->download($file->file_path, 'transfer-certificate-' . $file->scholar_no . '.pdf', ['Content-Type' => 'application/pdf']);
    }

    private function bindDatabase(string $session): void
    {
        abort_unless((bool) preg_match('/^\d{4}_\d{4}$/', $session), 404);
        Config::set('database.connections.dynamic.database', $session); DB::purge('dynamic'); DB::reconnect('dynamic'); DB::setDefaultConnection('dynamic');
    }

    private function activeSession(): string
    {
        $session = config('database.connections.dynamic.database');
        abort_unless(is_string($session) && preg_match('/^\d{4}_\d{4}$/', $session), 500, 'The active academic session is not configured.');
        return $session;
    }
}
