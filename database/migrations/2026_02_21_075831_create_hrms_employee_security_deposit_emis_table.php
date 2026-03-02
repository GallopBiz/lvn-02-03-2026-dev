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
        Schema::create('hrms_employee_security_deposit_emis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('security_deposit_id')->index('security_deposit_id');
            $table->date('emi_date');
            $table->decimal('emi_amount', 10);
            $table->decimal('principal_component', 10);
            $table->enum('status', ['pending', 'paid', 'paused'])->default('pending');
            $table->string('payment_mode')->nullable();
            $table->decimal('paid_amount', 10)->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('hrms_employee_security_deposit_emis');
    }
};
