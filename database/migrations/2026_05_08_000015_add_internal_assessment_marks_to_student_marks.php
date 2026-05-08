<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Schema::connection('dynamic')->hasTable('academic_students_marks')
            ? 'academic_students_marks'
            : 'marks';

        if (Schema::connection('dynamic')->hasTable($tableName) && !Schema::connection('dynamic')->hasColumn($tableName, 'internal_assessment_marks')) {
            Schema::connection('dynamic')->table($tableName, function (Blueprint $table) {
                $table->json('internal_assessment_marks')->nullable()->after('overall_grade');
            });
        }
    }

    public function down(): void
    {
        foreach (['academic_students_marks', 'marks'] as $tableName) {
            if (Schema::connection('dynamic')->hasTable($tableName) && Schema::connection('dynamic')->hasColumn($tableName, 'internal_assessment_marks')) {
                Schema::connection('dynamic')->table($tableName, function (Blueprint $table) {
                    $table->dropColumn('internal_assessment_marks');
                });
            }
        }
    }
};
