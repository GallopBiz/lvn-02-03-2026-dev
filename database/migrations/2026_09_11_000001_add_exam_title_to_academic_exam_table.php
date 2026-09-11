<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academic_exam') && !Schema::hasColumn('academic_exam', 'exam_title')) {
            Schema::table('academic_exam', function (Blueprint $table) {
                $table->string('exam_title', 150)->nullable()->after('exam_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('academic_exam') && Schema::hasColumn('academic_exam', 'exam_title')) {
            Schema::table('academic_exam', function (Blueprint $table) {
                $table->dropColumn('exam_title');
            });
        }
    }
};