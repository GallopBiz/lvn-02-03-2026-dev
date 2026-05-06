<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_student_roll_no', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('class_id', 50);
            $table->string('section_id', 50);
            $table->string('session_id', 50);
            $table->string('roll_no', 100);
            $table->timestamps();

            $table->unique(['student_id', 'class_id', 'section_id', 'session_id'], 'student_roll_unique');
            $table->index(['class_id', 'section_id', 'session_id'], 'class_section_session_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_student_roll_no');
    }
};
