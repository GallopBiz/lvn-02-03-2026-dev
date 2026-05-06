<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_student_roll_no', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_student_roll_no', 'room_no')) {
                $table->string('room_no', 50)->nullable()->after('roll_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('academic_student_roll_no', function (Blueprint $table) {
            if (Schema::hasColumn('academic_student_roll_no', 'room_no')) {
                $table->dropColumn('room_no');
            }
        });
    }
};
