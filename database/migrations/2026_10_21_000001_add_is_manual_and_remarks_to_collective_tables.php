<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('academic_attendance_collectives')) {
            Schema::table('academic_attendance_collectives', function (Blueprint $table) {
                if (!Schema::hasColumn('academic_attendance_collectives', 'is_manual')) {
                    $table->tinyInteger('is_manual')->default(0)->after('is_delete');
                }
                if (!Schema::hasColumn('academic_attendance_collectives', 'remarks')) {
                    $table->string('remarks', 255)->nullable()->after('is_manual');
                }
            });
        }

        if (Schema::hasTable('academic_attendance_collective_details')) {
            Schema::table('academic_attendance_collective_details', function (Blueprint $table) {
                if (!Schema::hasColumn('academic_attendance_collective_details', 'remarks')) {
                    $table->string('remarks', 255)->nullable()->after('is_below_minimum');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('academic_attendance_collectives')) {
            Schema::table('academic_attendance_collectives', function (Blueprint $table) {
                if (Schema::hasColumn('academic_attendance_collectives', 'remarks')) {
                    $table->dropColumn('remarks');
                }
                if (Schema::hasColumn('academic_attendance_collectives', 'is_manual')) {
                    $table->dropColumn('is_manual');
                }
            });
        }

        if (Schema::hasTable('academic_attendance_collective_details')) {
            Schema::table('academic_attendance_collective_details', function (Blueprint $table) {
                if (Schema::hasColumn('academic_attendance_collective_details', 'remarks')) {
                    $table->dropColumn('remarks');
                }
            });
        }
    }
};
