<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('dynamic')->table('marks', function (Blueprint $table) {
            if (!Schema::connection('dynamic')->hasColumn('marks', 'roll_no')) {
                $table->string('roll_no')->nullable()->after('student_id');
            }
            if (!Schema::connection('dynamic')->hasColumn('marks', 'scholar_no')) {
                $table->string('scholar_no')->nullable()->after('roll_no');
            }
            if (!Schema::connection('dynamic')->hasColumn('marks', 'mark_theory')) {
                $table->string('mark_theory')->nullable()->after('subject_marks');
            }
            if (!Schema::connection('dynamic')->hasColumn('marks', 'mark_practical')) {
                $table->string('mark_practical')->nullable()->after('mark_theory');
            }
            if (!Schema::connection('dynamic')->hasColumn('marks', 'total_marks')) {
                $table->string('total_marks')->nullable()->after('mark_practical');
            }
            if (!Schema::connection('dynamic')->hasColumn('marks', 'grade')) {
                $table->string('grade')->nullable()->after('total_marks');
            }
            if (!Schema::connection('dynamic')->hasColumn('marks', 'result')) {
                $table->string('result')->nullable()->after('grade');
            }
            if (!Schema::connection('dynamic')->hasColumn('marks', 'overall_grade')) {
                $table->string('overall_grade')->nullable()->after('result');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('dynamic')->table('marks', function (Blueprint $table) {
            foreach (['overall_grade', 'result', 'grade', 'total_marks', 'mark_practical', 'mark_theory', 'scholar_no', 'roll_no'] as $column) {
                if (Schema::connection('dynamic')->hasColumn('marks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
