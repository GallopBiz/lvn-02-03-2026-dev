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
        Schema::create('hrms_staff_type', function (Blueprint $table) {
            $table->id();
            $table->string('staff_type_name');
            $table->unsignedBigInteger('shift_type_id'); // Foreign key column
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
        Schema::dropIfExists('hrms_staff_type');
    }
};
