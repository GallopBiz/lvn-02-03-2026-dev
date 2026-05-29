<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('academic_attendance')) {
            return;
        }

        Schema::table('academic_attendance', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_attendance', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('half_day_count');
            }

            if (!Schema::hasColumn('academic_attendance', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('is_locked');
            }

            if (!Schema::hasColumn('academic_attendance', 'locked_by')) {
                $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('academic_attendance')) {
            return;
        }

        Schema::table('academic_attendance', function (Blueprint $table) {
            foreach (['locked_by', 'locked_at', 'is_locked'] as $column) {
                if (Schema::hasColumn('academic_attendance', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
