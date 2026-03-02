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
        Schema::create('hrms_employee_addresses', function (Blueprint $table) {
            $table->id();
        $table->foreignId('employee_id')->constrained('hrms_employees')->onDelete('restrict');
        $table->string('address_type'); // Current, Permanent
        $table->text('address_line');
        $table->string('city');
        $table->string('tehsil')->nullable();
        $table->string('district');
        $table->string('pin_code');
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
        Schema::dropIfExists('hrms_employee_addresses');
    }
};
