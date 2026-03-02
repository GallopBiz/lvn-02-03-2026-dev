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
        Schema::create('gps_data1', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('vehicle_name', 100)->nullable();
            $table->string('gps', 10)->nullable();
            $table->string('vehicle_no', 20)->nullable();
            $table->string('branch', 100)->nullable();
            $table->string('vehicletype', 10)->nullable();
            $table->string('status', 10)->nullable();
            $table->string('speed', 10)->nullable();
            $table->string('ign', 10)->nullable();
            $table->string('battery_percentage', 10)->nullable();
            $table->string('power', 10)->nullable();
            $table->text('location')->nullable();
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
        Schema::dropIfExists('gps_data1');
    }
};
