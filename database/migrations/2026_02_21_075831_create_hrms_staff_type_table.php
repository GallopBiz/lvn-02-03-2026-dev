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
            $table->bigIncrements('id');
            $table->string('staff_type_name');
            $table->unsignedBigInteger('shift_type_id')->index('hrms_staff_type_shift_type_id_foreign');
            $table->timestamps();
            $table->softDeletes();
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
