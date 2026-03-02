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
        Schema::table('hrms_employee_loans', function (Blueprint $table) {
            $table->integer('duration_months'); // Total loan duration in months
            $table->integer('original_duration_months'); // Original duration for reference
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrms_employee_loans', function (Blueprint $table) {
            $table->dropColumn('duration_months');
            $table->dropColumn('original_duration_months');
        });
    }
};
