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
        Schema::create('shift', function (Blueprint $table) {
            $table->integer('Shift_ID', true);
            $table->integer('Shift_Type_ID')->nullable()->index('Shift_Type_ID');
            $table->string('Start_Time', 10)->nullable();
            $table->string('End_Time', 10)->nullable();
            $table->string('Late_Coming_Threshold', 10)->nullable();
            $table->dateTime('updated_at')->useCurrent();
            $table->dateTime('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift');
    }
};
