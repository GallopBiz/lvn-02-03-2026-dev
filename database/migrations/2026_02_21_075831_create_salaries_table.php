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
        Schema::create('salaries', function (Blueprint $table) {
            $table->bigInteger('SalaryID', true);
            $table->bigInteger('EmployeeID')->nullable()->default(0);
            $table->text('SalaryAmount')->nullable();
            $table->text('EffectiveDate')->nullable();
            $table->text('AttendanceMonth')->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('0 none, 1 delete');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salaries');
    }
};
