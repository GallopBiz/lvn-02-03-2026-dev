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
        Schema::create('scholarbusassign', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('student_id_select_p');
            $table->string('pick_shedule_name', 50)->nullable();
            $table->string('pick_up_routes', 50)->nullable();
            $table->string('pickup_area_name', 50)->nullable();
            $table->string('pickup_bus_stop_names', 50)->nullable();
            $table->string('pickup_bus_no', 50)->nullable();
            $table->string('drop_shedule_name', 50)->nullable();
            $table->string('drop_up_route', 50)->nullable();
            $table->string('drop_area_name', 50)->nullable();
            $table->string('drop_bus_stop_name', 50)->nullable();
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->string('latLngInput', 100)->nullable();
            $table->string('studentaddcheck', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scholarbusassign');
    }
};
