<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('dynamic')->hasTable('academic_student_remarks')) {
            Schema::connection('dynamic')->table('academic_student_remarks', function (Blueprint $table) {
                if (!Schema::connection('dynamic')->hasColumn('academic_student_remarks', 'total_marks')) {
                    $table->decimal('total_marks', 10, 2)->nullable()->after('remark_text');
                }
                if (!Schema::connection('dynamic')->hasColumn('academic_student_remarks', 'total_obtained')) {
                    $table->decimal('total_obtained', 10, 2)->nullable()->after('total_marks');
                }
                if (!Schema::connection('dynamic')->hasColumn('academic_student_remarks', 'grade')) {
                    $table->string('grade', 20)->nullable()->after('total_obtained');
                }
                if (!Schema::connection('dynamic')->hasColumn('academic_student_remarks', 'result_remark')) {
                    $table->string('result_remark', 255)->nullable()->after('grade');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::connection('dynamic')->hasTable('academic_student_remarks')) {
            Schema::connection('dynamic')->table('academic_student_remarks', function (Blueprint $table) {
                foreach (['result_remark', 'grade', 'total_obtained', 'total_marks'] as $column) {
                    if (Schema::connection('dynamic')->hasColumn('academic_student_remarks', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
