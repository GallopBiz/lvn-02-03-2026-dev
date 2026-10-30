<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('dynamic');

        if (!$schema->hasTable('academic_attendance_monthly_locks')) {
            $schema->create('academic_attendance_monthly_locks', function (Blueprint $table) {
                $table->id();
                $table->string('academic_session', 50)->nullable();
                $table->string('class_id', 50);
                $table->string('section_name', 100);
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('month');
                $table->boolean('is_locked')->default(true);
                $table->timestamp('locked_at')->nullable();
                $table->unsignedBigInteger('locked_by')->nullable();
                $table->timestamp('unlocked_at')->nullable();
                $table->unsignedBigInteger('unlocked_by')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->unique(['class_id', 'section_name', 'year', 'month'], 'att_month_lock_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::connection('dynamic')->dropIfExists('academic_attendance_monthly_locks');
    }
};
