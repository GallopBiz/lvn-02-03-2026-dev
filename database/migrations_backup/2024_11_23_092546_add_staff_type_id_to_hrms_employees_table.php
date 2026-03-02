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
        Schema::table('hrms_employees', function (Blueprint $table) {
            //
            $table->foreignId('staff_type_id')
            ->constrained('hrms_staff_type') // Reference the hrms_staff_type table
            ->onDelete('restrict'); // Restrict deletion of related staff_type records
            $table->dropColumn('employment_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrms_employees', function (Blueprint $table) {
            //

            $table->dropForeign(['staff_type_id']);
            $table->dropColumn('staff_type_id');
            $table->string('employment_type');
        });
    }
};
