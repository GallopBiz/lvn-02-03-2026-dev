<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('academic_exam', 'exam_group_id')) {
            Schema::table('academic_exam', function (Blueprint $table) {
                $table->uuid('exam_group_id')->nullable()->index()->after('id');
            });
        }

        // Backfill existing rows by grouping on current "group identity".
        // This is needed so duplicates can be edited/deleted independently.
        $rows = DB::table('academic_exam')
            ->select(
                'id',
                'exam_name',
                'exam_type',
                'session_year',
                'max_marks_theory',
                'max_marks_practical',
                'fail_percent',
                'is_ser',
                'created_by',
                'exam_group_id'
            )
            ->orderBy('id')
            ->get();

        $groups = [];
        foreach ($rows as $r) {
            if (!empty($r->exam_group_id)) {
                continue;
            }
            $key = implode('|', [
                (string) ($r->exam_name ?? ''),
                (string) ($r->exam_type ?? ''),
                (string) ($r->session_year ?? ''),
                (string) ($r->max_marks_theory ?? ''),
                (string) ($r->max_marks_practical ?? ''),
                (string) ($r->fail_percent ?? ''),
                (string) ($r->is_ser ?? ''),
                (string) ($r->created_by ?? ''),
            ]);
            $groups[$key] ??= [];
            $groups[$key][] = (int) $r->id;
        }

        foreach ($groups as $ids) {
            if (count($ids) === 0) {
                continue;
            }
            DB::table('academic_exam')
                ->whereIn('id', $ids)
                ->update(['exam_group_id' => (string) Str::uuid()]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('academic_exam', 'exam_group_id')) {
            Schema::table('academic_exam', function (Blueprint $table) {
                $table->dropIndex(['exam_group_id']);
                $table->dropColumn('exam_group_id');
            });
        }
    }
};

