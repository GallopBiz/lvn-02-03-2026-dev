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
        Schema::create('busstop', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('area_name', 200);
            $table->string('bus_stop_name', 200);
            $table->string('latitude', 50)->nullable();
            $table->string('langitude', 50)->nullable();
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('busstop');
    }
};
