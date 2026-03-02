<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hrms_holidays', function (Blueprint $table) {
            $table->dropColumn('HolidayDate');
            $table->dropColumn('is_delete');
            $table->date('HolidayStartDate')->nullable()->after('HolidayName');
            $table->date('HolidayEndDate')->nullable()->after('HolidayStartDate');
            $table->string('HolidayDescription')->nullable()->after('HolidayEndDate');
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
        Schema::table('hrms_holidays', function (Blueprint $table) {
            $table->dropColumn('HolidayStartDate');
            $table->dropColumn('HolidayEndDate');
        });
    }
};
