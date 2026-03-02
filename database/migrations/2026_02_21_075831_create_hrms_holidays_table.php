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
        Schema::create('hrms_holidays', function (Blueprint $table) {
            $table->bigInteger('HolidayID', true);
            $table->string('HolidayName', 200)->nullable();
            $table->date('HolidayStartDate')->nullable();
            $table->date('HolidayEndDate')->nullable();
            $table->string('HolidayDescription')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
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
        Schema::dropIfExists('hrms_holidays');
    }
};
