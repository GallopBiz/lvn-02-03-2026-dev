<?php

namespace App\Http\Controllers;

use App\Models\StudentTcFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicStudentTcDownloadController extends Controller
{
    private const TOKEN_REGEX = '/^[a-zA-Z0-9]{64}$/';

    public function index()
    {
        return response()
            ->view('frontend.student-tc-download')
            ->withHeaders($this->securityHeaders());
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'scholar_no' => ['required', 'string', 'max:50'],
        ]);

        $scholarNo = trim($data['scholar_no']);
        $session = $this->activeSession();
        $this->bindDatabase($session);

        $file = StudentTcFile::where('scholar_no', $scholarNo)
            ->where('session_name', $session)
            ->first();

        if (!$file || !Storage::disk('private')->exists($file->file_path)) {
            Log::info('TC public search: Not found or file missing', [
                'scholar_no' => $scholarNo,
                'session' => $session,
                'ip' => $request->ip(),
            ]);
            return back()->withInput()->with('error', 'No Transfer Certificate is available for these details.');
        }

        $token = Str::random(64);
        $request->session()->put('student_tc_download.' . $token, [
            'file_id' => $file->id,
            'scholar_no' => $file->scholar_no,
            'session_name' => $session,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        Log::info('TC public search: Token generated', [
            'scholar_no' => $file->scholar_no,
            'session' => $session,
            'token_prefix' => substr($token, 0, 8) . '...',
            'ip' => $request->ip(),
        ]);

        $student = DB::connection('dynamic')->table('student_registration')
            ->where('scholar_no', $scholarNo)
            ->first(['student_name', 'class_name', 'json_str']);

        $details = json_decode($student?->json_str ?? '{}', true) ?: [];
        $studentDetails = [
            'name' => $student->student_name ?? '',
            'class' => $student->class_name ?? '',
            'section' => $details['section_name'] ?? '',
        ];

        return response()
            ->view('frontend.student-tc-download', compact('file', 'token', 'studentDetails'))
            ->withHeaders($this->securityHeaders());
    }

    public function view(Request $request, string $token)
    {
        $access = $this->validateAndAuthorizeToken($request, $token, false);

        $file = StudentTcFile::findOrFail($access['file_id']);
        $fullPath = $this->assertSafePath($file->file_path);

        Log::info('TC viewed successfully', [
            'scholar_no' => $file->scholar_no,
            'file_id' => $file->id,
            'ip' => $request->ip(),
        ]);

        return response()
            ->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="transfer-certificate-' . $file->scholar_no . '.pdf"',
            ])
            ->withHeaders($this->securityHeaders());
    }

    public function download(Request $request, string $token)
    {
        $access = $this->validateAndAuthorizeToken($request, $token, true);

        $file = StudentTcFile::findOrFail($access['file_id']);
        $this->assertSafePath($file->file_path);

        Log::info('TC downloaded successfully', [
            'scholar_no' => $file->scholar_no,
            'file_id' => $file->id,
            'ip' => $request->ip(),
        ]);

        $downloadName = 'TC_Document_' . Str::random(16) . '.pdf';

        return Storage::disk('private')
            ->download(
                $file->file_path,
                $downloadName,
                $this->securityHeaders(['Content-Type' => 'application/pdf'])
            );
    }

    private function validateAndAuthorizeToken(Request $request, string $token, bool $consume): array
    {
        if (!preg_match(self::TOKEN_REGEX, $token)) {
            Log::warning('TC token access attempt with invalid format', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            abort(404, 'Invalid document token.');
        }

        $sessionKey = 'student_tc_download.' . $token;
        $access = $consume ? $request->session()->pull($sessionKey) : $request->session()->get($sessionKey);

        if (!is_array($access) || ($access['expires_at'] ?? 0) < now()->timestamp) {
            Log::warning('TC token access denied: Expired or invalid token in session', [
                'token_prefix' => substr($token, 0, 8) . '...',
                'ip' => $request->ip(),
            ]);
            abort(403, 'Access token is invalid or has expired.');
        }

        $sessionName = $access['session_name'] ?? '';
        $this->bindDatabase($sessionName);

        // Revocation / Verification check: ensure DB record still exists and scholar_no matches
        $file = StudentTcFile::find($access['file_id'] ?? 0);
        if (!$file || $file->scholar_no !== ($access['scholar_no'] ?? '')) {
            Log::warning('TC token access denied: File record revoked or deleted', [
                'file_id' => $access['file_id'] ?? null,
                'scholar_no' => $access['scholar_no'] ?? null,
                'ip' => $request->ip(),
            ]);
            abort(403, 'The requested document is no longer active.');
        }

        return $access;
    }

    private function assertSafePath(string $filePath): string
    {
        abort_unless(Storage::disk('private')->exists($filePath), 404, 'Document file not found.');

        $fullPath = Storage::disk('private')->path($filePath);
        $basePath = storage_path('app/private/student-tc');

        $realFile = realpath($fullPath);
        $realBase = realpath($basePath);

        if (!$realFile || !$realBase || !str_starts_with($realFile, $realBase)) {
            Log::alert('Security Alert: Path traversal attempt blocked on TC system', [
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

    private function bindDatabase(string $session): void
    {
        abort_unless((bool) preg_match('/^\d{4}_\d{4}$/', $session), 404, 'Invalid academic session format.');
        Config::set('database.connections.dynamic.database', $session);
        DB::purge('dynamic');
        DB::reconnect('dynamic');
        DB::setDefaultConnection('dynamic');
    }

    private function activeSession(): string
    {
        $session = config('database.connections.dynamic.database');
        abort_unless(is_string($session) && preg_match('/^\d{4}_\d{4}$/', $session), 500, 'The active academic session is not configured.');
        return $session;
    }
}
