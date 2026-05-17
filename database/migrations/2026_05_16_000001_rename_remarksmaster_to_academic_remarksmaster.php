<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('remarksmaster') && !Schema::hasTable('academic_remarksmaster')) {
            Schema::rename('remarksmaster', 'academic_remarksmaster');
        }

        if (Schema::hasTable('academic_remarksmaster') && Schema::hasColumn('academic_remarksmaster', 'remark')) {
            DB::statement('ALTER TABLE academic_remarksmaster MODIFY remark VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('academic_remarksmaster') && !Schema::hasTable('remarksmaster')) {
            Schema::rename('academic_remarksmaster', 'remarksmaster');
        }

        if (Schema::hasTable('remarksmaster') && Schema::hasColumn('remarksmaster', 'remark')) {
            DB::statement('ALTER TABLE remarksmaster MODIFY remark VARCHAR(20) NULL');
        }
    }
};
