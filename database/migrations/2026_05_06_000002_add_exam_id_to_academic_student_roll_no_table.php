<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_student_roll_no', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_student_roll_no', 'exam_id')) {
                $table->unsignedBigInteger('exam_id')->nullable()->after('session_id');
            }
        });

        $database = DB::getDatabaseName();
        $hasOldUnique = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'academic_student_roll_no')
            ->where('index_name', 'student_roll_unique')
            ->exists();

        if ($hasOldUnique) {
            DB::statement('ALTER TABLE academic_student_roll_no DROP INDEX student_roll_unique');
        }

        $hasNewUnique = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'academic_student_roll_no')
            ->where('index_name', 'student_exam_roll_unique')
            ->exists();

        if (!$hasNewUnique) {
            DB::statement('ALTER TABLE academic_student_roll_no ADD UNIQUE student_exam_roll_unique (student_id, class_id, section_id, session_id, exam_id)');
        }
    }

    public function down(): void
    {
        $database = DB::getDatabaseName();
        $hasNewUnique = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'academic_student_roll_no')
            ->where('index_name', 'student_exam_roll_unique')
            ->exists();

        if ($hasNewUnique) {
            DB::statement('ALTER TABLE academic_student_roll_no DROP INDEX student_exam_roll_unique');
        }

        Schema::table('academic_student_roll_no', function (Blueprint $table) {
            if (Schema::hasColumn('academic_student_roll_no', 'exam_id')) {
                $table->dropColumn('exam_id');
            }
        });

        DB::statement('ALTER TABLE academic_student_roll_no ADD UNIQUE student_roll_unique (student_id, class_id, section_id, session_id)');
    }
};
