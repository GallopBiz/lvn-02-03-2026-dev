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
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('annual_entitlement')->nullable();
            $table->tinyInteger('reset_month')->default(6);
            $table->json('rules')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_carry_forward')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->integer('apply_before_days')->nullable();
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
        Schema::dropIfExists('hrms_leave_types');
    }
};
