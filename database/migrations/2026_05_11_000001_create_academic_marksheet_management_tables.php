<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::connection('dynamic')->hasTable('academic_marksheets')) {
            Schema::connection('dynamic')->create('academic_marksheets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('exam_id')->nullable();
                $table->string('session_year')->nullable();
                $table->json('snapshot')->nullable();
                $table->string('status')->default('draft');
                $table->timestamp('printed_at')->nullable();
                $table->timestamps();
                $table->index(['student_id', 'class_id', 'exam_id'], 'academic_marksheet_student_lookup');
            });
        }
    }

    public function down(): void
    {
        Schema::connection('dynamic')->dropIfExists('academic_marksheets');
    }
};
