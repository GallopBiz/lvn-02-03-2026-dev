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
        Schema::create('busdata', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('vehicle_name', 100);
            $table->string('gps', 10);
            $table->string('vehicle_no', 20);
            $table->string('branch', 100);
            $table->string('vehicletype', 10);
            $table->string('status', 10);
            $table->string('speed', 10);
            $table->string('ign', 10);
            $table->string('battery_percentage', 10);
            $table->string('power', 10);
            $table->text('location');
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->string('bus_url', 225)->nullable();
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : delete');
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
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
        Schema::dropIfExists('busdata');
    }
};
