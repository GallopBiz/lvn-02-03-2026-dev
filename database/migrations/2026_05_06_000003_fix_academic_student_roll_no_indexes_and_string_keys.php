<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $database = DB::getDatabaseName();

        $this->dropIndexIfExists($database, 'unique_roll');
        $this->dropIndexIfExists($database, 'student_roll_unique');

        DB::statement('ALTER TABLE academic_student_roll_no MODIFY class_id VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE academic_student_roll_no MODIFY section_id VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE academic_student_roll_no MODIFY session_id VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE academic_student_roll_no MODIFY roll_no VARCHAR(100) NOT NULL');

        $hasStudentExamUnique = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'academic_student_roll_no')
            ->where('index_name', 'student_exam_roll_unique')
            ->exists();

        if (!$hasStudentExamUnique) {
            DB::statement('ALTER TABLE academic_student_roll_no ADD UNIQUE student_exam_roll_unique (student_id, class_id, section_id, session_id, exam_id)');
        }

        $hasLookupIndex = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'academic_student_roll_no')
            ->where('index_name', 'class_section_session_exam_idx')
            ->exists();

        if (!$hasLookupIndex) {
            DB::statement('ALTER TABLE academic_student_roll_no ADD INDEX class_section_session_exam_idx (class_id, section_id, session_id, exam_id)');
        }
    }

    public function down(): void
    {
        $database = DB::getDatabaseName();

        $this->dropIndexIfExists($database, 'class_section_session_exam_idx');
    }

    private function dropIndexIfExists(string $database, string $indexName): void
    {
        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'academic_student_roll_no')
            ->where('index_name', $indexName)
            ->exists();

        if ($exists) {
            DB::statement("ALTER TABLE academic_student_roll_no DROP INDEX {$indexName}");
        }
    }
};
