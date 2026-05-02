<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exams') && !Schema::hasTable('academic_exam')) {
            Schema::rename('exams', 'academic_exam');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('academic_exam') && !Schema::hasTable('exams')) {
            Schema::rename('academic_exam', 'exams');
        }
    }
};

