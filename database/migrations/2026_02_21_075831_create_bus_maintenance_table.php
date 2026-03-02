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
        Schema::create('bus_maintenance', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('vehicle_no', 50)->nullable();
            $table->date('maintenance_date')->nullable();
            $table->integer('maintenance_group_id');
            $table->integer('maintenance_head_id');
            $table->decimal('amount', 10, 0);
            $table->text('remarks')->nullable();
            $table->string('invoice_file', 50)->nullable();
            $table->date('invoice_date')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bus_maintenance');
    }
};
