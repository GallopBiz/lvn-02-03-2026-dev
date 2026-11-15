<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('student_late_fee_adjustments')) {
            Schema::create('student_late_fee_adjustments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id')->index();
                $table->string('scholar_no', 50)->index();
                $table->string('student_name', 150)->nullable();
                $table->string('class_name', 50)->nullable();
                $table->string('term', 20)->default('all'); // '1', '2', '3', '4', or 'all'
                $table->decimal('original_late_fee', 10, 2)->default(0.00);
                $table->decimal('adjusted_late_fee', 10, 2)->default(0.00);
                $table->string('status', 20)->default('adjusted'); // 'waived', 'adjusted'
                $table->string('reason', 255)->nullable();
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->string('admin_name', 100)->nullable();
                $table->string('session_name', 50)->nullable();
                $table->timestamps();

                $table->unique(['student_id', 'term'], 'uniq_student_term');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_late_fee_adjustments');
    }
};

