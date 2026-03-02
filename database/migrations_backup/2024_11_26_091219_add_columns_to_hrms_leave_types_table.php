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
        Schema::table('hrms_leave_types', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('rules'); // Leave is paid or unpaid
            $table->boolean('is_carry_forward')->default(false)->after('is_paid');
            $table->integer('apply_before_days')->nullable(); // Minimum days before leave can be applied
            $table->boolean('allow_backdate')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrms_leave_types', function (Blueprint $table) {
            $table->dropColumn('is_paid');
            $table->dropColumn('apply_before_days');
            $table->dropColumn('allow_backdate');
            $table->dropColumn('is_carry_forward');
        });
    }
};
