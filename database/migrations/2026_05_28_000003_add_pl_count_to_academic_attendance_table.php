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
            if (!Schema::hasColumn('academic_attendance', 'pl_count')) {
                $table->unsignedInteger('pl_count')->default(0)->after('half_day_count');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('academic_attendance') || !Schema::hasColumn('academic_attendance', 'pl_count')) {
            return;
        }

        Schema::table('academic_attendance', function (Blueprint $table) {
            $table->dropColumn('pl_count');
        });
    }
};
