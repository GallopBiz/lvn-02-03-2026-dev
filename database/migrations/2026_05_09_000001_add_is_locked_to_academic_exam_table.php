<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academic_exam') && !Schema::hasColumn('academic_exam', 'is_locked')) {
            Schema::table('academic_exam', function (Blueprint $table) {
                $table->boolean('is_locked')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('academic_exam') && Schema::hasColumn('academic_exam', 'is_locked')) {
            Schema::table('academic_exam', function (Blueprint $table) {
                $table->dropColumn('is_locked');
            });
        }
    }
};
