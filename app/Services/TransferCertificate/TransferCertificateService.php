<?php

namespace App\Services\TransferCertificate;

use App\Models\TransferCertificate;
use App\Models\TransferCertificate\TransferCertificateAuditLog;
use App\Models\TransferCertificate\TransferCertificateIssue;
use App\Models\TransferCertificate\TransferCertificateStudentStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class TransferCertificateService
{
    public function issue(array $data, Request $request): TransferCertificate
    {
        $student = DB::connection('dynamic')
            ->table('student_registration')
            ->where('id', $data['student_id'])
            ->first();

        if (!$student) {
            throw ValidationException::withMessages(['student_id' => 'Selected student was not found.']);
        }

        $existing = TransferCertificate::where('student_id', $student->id)
            ->orWhere('scholar_no', $student->scholar_no)
            ->first();

        if ($existing && empty($data['reissue_reason'])) {
            throw ValidationException::withMessages([
                'reissue_reason' => 'This student already has a Transfer Certificate. Please enter a reason to generate a duplicate.',
            ]);
        }

        return DB::connection('dynamic')->transaction(function () use ($data, $student, $existing, $request) {
            $snapshot = $this->buildStudentSnapshot($student);
            $userId = $this->currentUserId();

            $formData = $data['tc_form_data'] ?? [];

            if ($existing) {
                $certificate = $existing;
                $certificate->fill([
                    'status' => 'issued',
                    'tc_form_data' => array_merge($certificate->tc_form_data ?? [], $formData),
                    'remarks' => $data['remarks'] ?? $certificate->remarks,
                ])->save();
            } else {
                $certificate = TransferCertificate::create([
                    'certificate_no' => !empty($data['certificate_no']) ? $data['certificate_no'] : $this->nextCertificateNo($data['session_name'] ?? $student->session_name),
                    'student_id' => $student->id,
                    'scholar_no' => $student->scholar_no,
                    'session_name' => $data['session_name'] ?? $student->session_name,
                    'class_name' => $student->class_name,
                    'section_name' => $this->valueFromJson($student->json_str, 'section_name') ?? $student->section_name,
                    'student_name' => $student->student_name,
                    'status' => 'issued',
                    'issue_date' => $data['issue_date'],
                    'leaving_date' => $data['leaving_date'] ?? null,
                    'reason_for_leaving' => $data['reason_for_leaving'] ?? null,
                    'conduct' => $data['conduct'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'tc_form_data' => $formData,
                    'snapshot' => $snapshot,
                    'generated_by' => $userId,
                ]);
            }

            $issueCount = TransferCertificateIssue::where('certificate_id', $certificate->id)->count();
            $issue = TransferCertificateIssue::create([
                'certificate_id' => $certificate->id,
                'issue_no' => $issueCount + 1,
                'is_duplicate' => $issueCount > 0,
                'duplicate_label' => $issueCount > 0 ? 'Duplicate Transfer Certificate' : null,
                'reason' => $issueCount > 0 ? $data['reissue_reason'] : ($data['reason_for_leaving'] ?? null),
                'issued_by' => $userId,
                'issued_at' => now(),
                'metadata' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            $certificate->last_issue_id = $issue->id;
            $certificate->save();

            $this->markStudentTransferred($certificate, $student, $data, $userId);
            $this->writeAudit($certificate, $student, $issue->is_duplicate ? 'duplicate_issued' : 'issued', $request, null, $certificate->toArray());

            return $certificate->fresh(['issues', 'latestIssue']);
        });
    }

    public function buildStudentSnapshot(object $student): array
    {
        $json = json_decode($student->json_str ?? '{}', true) ?: [];

        return [
            'student' => [
                'id' => $student->id,
                'scholar_no' => $student->scholar_no,
                'form_number' => $student->form_number,
                'student_name' => $student->student_name,
                'date_of_birth' => $student->date_of_birth,
                'class_name' => $student->class_name,
                'section_name' => $json['section_name'] ?? $student->section_name ?? null,
                'session_name' => $student->session_name,
                'registration_date' => $student->registration_date ?? null,
            ],
            'parents' => [
                'father_name' => $json['fathername'] ?? $json['father_name'] ?? null,
                'father_mobile' => $json['father_mobile'] ?? null,
                'mother_name' => $json['mothername'] ?? $json['mother_name'] ?? null,
                'mother_mobile' => $json['mother_mobile'] ?? null,
                'guardian_name' => $json['guardian_name'] ?? null,
            ],
            'contact' => [
                'phone_number' => $student->phone_number,
                'mobile_number' => $student->mobile_number,
                'address' => $json['address'] ?? $json['permanent_address'] ?? null,
            ],
            'admission' => [
                'application_for' => $student->application_for,
                'admission_no' => $json['admission_no'] ?? $student->scholar_no,
                'admission_date' => $json['admission_date'] ?? $student->registration_date ?? null,
            ],
            'raw_json' => $json,
        ];
    }

    public function writeAudit(?TransferCertificate $certificate, ?object $student, string $action, Request $request, ?array $oldValues = null, ?array $newValues = null): void
    {
        TransferCertificateAuditLog::create([
            'certificate_id' => $certificate?->id,
            'student_id' => $student?->id ?? $certificate?->student_id,
            'scholar_no' => $student?->scholar_no ?? $certificate?->scholar_no,
            'action' => $action,
            'description' => ucfirst(str_replace('_', ' ', $action)),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'performed_by' => $this->currentUserId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function markStudentTransferred(TransferCertificate $certificate, object $student, array $data, ?int $userId): void
    {
        $inactiveFromSession = $data['inactive_from_session'] ?? $this->nextSessionName($certificate->session_name);

        TransferCertificateStudentStatus::updateOrCreate(
            ['scholar_no' => $student->scholar_no, 'tc_session_name' => $certificate->session_name],
            [
                'student_id' => $student->id,
                'certificate_id' => $certificate->id,
                'inactive_from_session' => $inactiveFromSession,
                'status' => 'transferred',
                'reason' => $certificate->reason_for_leaving,
                'marked_by' => $userId,
                'marked_at' => now(),
            ]
        );

        $updates = ['updated_at' => now()];
        if (Schema::connection('dynamic')->hasColumn('student_registration', 'status')) {
            $updates['status'] = 't';
        }
        if (Schema::connection('dynamic')->hasColumn('student_registration', 'transfer_status')) {
            $updates['transfer_status'] = 'transferred';
        }
        if (Schema::connection('dynamic')->hasColumn('student_registration', 'transferred_at')) {
            $updates['transferred_at'] = now();
        }
        if (Schema::connection('dynamic')->hasColumn('student_registration', 'tc_certificate_id')) {
            $updates['tc_certificate_id'] = $certificate->id;
        }

        if (count($updates) > 1) {
            DB::connection('dynamic')->table('student_registration')
                ->where('id', $student->id)
                ->update($updates);
        }

        $this->markInNextSessionDatabase($student->scholar_no, $inactiveFromSession);
    }

    private function markInNextSessionDatabase(?string $scholarNo, ?string $nextSession): void
    {
        if (!$scholarNo || !$nextSession || !preg_match('/^\d{4}_\d{4}$/', $nextSession)) {
            return;
        }

        Config::set('database.connections.next_session_db.database', $nextSession);
        DB::purge('next_session_db');
        DB::reconnect('next_session_db');

        if (!Schema::connection('next_session_db')->hasTable('student_registration')) {
            return;
        }

        $updates = ['status' => 't', 'updated_at' => now()];
        if (Schema::connection('next_session_db')->hasColumn('student_registration', 'transfer_status')) {
            $updates['transfer_status'] = 'transferred';
        }
        if (Schema::connection('next_session_db')->hasColumn('student_registration', 'transferred_at')) {
            $updates['transferred_at'] = now();
        }
        if (Schema::connection('next_session_db')->hasColumn('student_registration', 'tc_certificate_id')) {
            $certificate = TransferCertificate::where('scholar_no', $scholarNo)->latest('id')->first();
            $updates['tc_certificate_id'] = $certificate?->id;
        }

        DB::connection('next_session_db')->table('student_registration')
            ->where('scholar_no', $scholarNo)
            ->update($updates);
    }

    private function nextCertificateNo(?string $sessionName): string
    {
        $sessionPart = $sessionName ?: now()->format('Y');
        $count = TransferCertificate::where('session_name', $sessionName)->count() + 1;

        return 'TC/' . str_replace('_', '-', $sessionPart) . '/' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    private function nextSessionName(?string $sessionName): ?string
    {
        if (!$sessionName || !preg_match('/^(\d{4})_(\d{4})$/', $sessionName, $matches)) {
            return null;
        }

        return ((int) $matches[1] + 1) . '_' . ((int) $matches[2] + 1);
    }

    private function valueFromJson(?string $json, string $key): ?string
    {
        $data = json_decode($json ?? '{}', true);

        return is_array($data) ? ($data[$key] ?? null) : null;
    }

    private function currentUserId(): ?int
    {
        return Auth::guard('web')->id() ?: Auth::guard('staff')->id();
    }
}
