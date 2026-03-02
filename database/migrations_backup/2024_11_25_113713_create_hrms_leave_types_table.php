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
        Schema::create('hrms_leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Casual Leave, Medical Leave
            $table->integer('annual_entitlement')->nullable(); // Default annual entitlement
            $table->tinyInteger('reset_month')->default(6); // Month of annual reset (June by default)
            $table->json('rules')->nullable();
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
        Schema::dropIfExists('hrms_leave_types');
    }
};
