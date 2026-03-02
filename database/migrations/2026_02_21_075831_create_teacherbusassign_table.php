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
        Schema::create('teacherbusassign', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('student_id_select_p');
            $table->string('pick_shedule_name', 100)->nullable();
            $table->string('pick_up_routes', 100)->nullable();
            $table->string('pickup_area_name', 100)->nullable();
            $table->string('pickup_bus_stop_names', 100)->nullable();
            $table->string('pickup_bus_no', 100)->nullable();
            $table->string('drop_shedule_name', 100)->nullable();
            $table->string('drop_up_route', 100)->nullable();
            $table->string('drop_area_name', 100)->nullable();
            $table->string('drop_bus_stop_name', 100)->nullable();
            $table->integer('is_delete')->default(0)->comment('0 none, 1 delete');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacherbusassign');
    }
};
