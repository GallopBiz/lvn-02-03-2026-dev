<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('dynamic')->hasTable('marks') && !Schema::connection('dynamic')->hasTable('academic_students_marks')) {
            Schema::connection('dynamic')->rename('marks', 'academic_students_marks');
        }
    }

    public function down(): void
    {
        if (Schema::connection('dynamic')->hasTable('academic_students_marks') && !Schema::connection('dynamic')->hasTable('marks')) {
            Schema::connection('dynamic')->rename('academic_students_marks', 'marks');
        }
    }
};
