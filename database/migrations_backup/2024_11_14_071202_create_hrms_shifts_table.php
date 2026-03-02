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
        Schema::create('hrms_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_type_id'); // Foreign key column
            $table->time('start_time'); // Start time of the shift
            $table->time('end_time'); // End time of the shift
            $table->integer('late_coming_threshold')->default(0); // Threshold in minutes
            $table->timestamps();
            $table->softDeletes(); // Soft delete support

            $table->foreign('shift_type_id')->references('id')->on('hrms_shift_types')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrms_shifts');
    }
};
