<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connections = ['mysql', 'dynamic'];
        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('academic_attendance') && Schema::connection($conn)->hasTable('teacher_subjects')) {
                    DB::connection($conn)->statement("
                        UPDATE academic_attendance a
                        JOIN teacher_subjects ts ON (
                            ts.class_id = a.class_id
                            AND ts.section_name = a.section_name
                            AND LOWER(TRIM(ts.role)) = 'class teacher'
                            AND (ts.is_delete = 0 OR ts.is_delete IS NULL)
                        )
                        SET a.Teacher_id = ts.teacher_id
                        WHERE a.Teacher_id IS NULL OR a.Teacher_id = 0
                    ");
                }
            } catch (\Throwable $e) {
                // Ignore connection-specific errors
            }
        }

        try {
            $databases = DB::select("SHOW DATABASES LIKE '20%'");
            foreach ($databases as $dbObj) {
                $dbName = array_values((array) $dbObj)[0];
                try {
                    $hasAtt = DB::select("SHOW TABLES FROM `{$dbName}` LIKE 'academic_attendance'");
                    $hasTs = DB::select("SHOW TABLES FROM `{$dbName}` LIKE 'teacher_subjects'");
                    if (!empty($hasAtt) && !empty($hasTs)) {
                        DB::statement("
                            UPDATE `{$dbName}`.academic_attendance a
                            JOIN `{$dbName}`.teacher_subjects ts ON (
                                ts.class_id = a.class_id
                                AND ts.section_name = a.section_name
                                AND LOWER(TRIM(ts.role)) = 'class teacher'
                                AND (ts.is_delete = 0 OR ts.is_delete IS NULL)
                            )
                            SET a.Teacher_id = ts.teacher_id
                            WHERE a.Teacher_id IS NULL OR a.Teacher_id = 0
                        ");
                    }
                } catch (\Throwable $e) {
                    // Ignore database errors
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    public function down(): void
    {
        // No rollback needed for data backfill
    }
};
