<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('academic_exam', 'deleted_at')) {
            Schema::table('academic_exam', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->index()->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('academic_exam', 'deleted_at')) {
            Schema::table('academic_exam', function (Blueprint $table) {
                $table->dropIndex(['deleted_at']);
                $table->dropColumn('deleted_at');
            });
        }
    }
};

