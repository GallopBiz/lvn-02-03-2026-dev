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
        Schema::create('maintenanceheadmaster', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('maintenance_group_name', 100);
            $table->string('maintenance_head_name', 200)->nullable();
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maintenanceheadmaster');
    }
};
