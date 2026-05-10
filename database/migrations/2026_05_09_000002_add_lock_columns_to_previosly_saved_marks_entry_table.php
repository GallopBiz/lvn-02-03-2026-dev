<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('previosly_saved_marks_entry')) {
            Schema::table('previosly_saved_marks_entry', function (Blueprint $table) {
                if (!Schema::hasColumn('previosly_saved_marks_entry', 'is_locked')) {
                    $table->boolean('is_locked')->default(false);
                }
                if (!Schema::hasColumn('previosly_saved_marks_entry', 'locked_at')) {
                    $table->timestamp('locked_at')->nullable();
                }
                if (!Schema::hasColumn('previosly_saved_marks_entry', 'locked_by')) {
                    $table->unsignedBigInteger('locked_by')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('previosly_saved_marks_entry')) {
            Schema::table('previosly_saved_marks_entry', function (Blueprint $table) {
                foreach (['is_locked', 'locked_at', 'locked_by'] as $column) {
                    if (Schema::hasColumn('previosly_saved_marks_entry', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
