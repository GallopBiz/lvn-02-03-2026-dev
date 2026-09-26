<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('dynamic')->create('student_tc_files', function (Blueprint $table) {
            $table->id();
            $table->string('scholar_no', 50)->index();
            $table->string('session_name', 20)->index();
            $table->string('file_path', 500);
            $table->string('original_filename', 255)->nullable();
            $table->unsignedInteger('file_size');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('uploaded_by')->nullable()->index();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
            $table->unique(['scholar_no', 'session_name']);
        });
    }
    public function down(): void { Schema::connection('dynamic')->dropIfExists('student_tc_files'); }
};
