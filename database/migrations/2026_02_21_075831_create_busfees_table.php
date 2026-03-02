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
        Schema::create('busfees', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('select_batch', 20)->nullable();
            $table->string('busfeestypename', 50)->nullable();
            $table->string('date', 15)->nullable();
            $table->integer('amount')->nullable();
            $table->string('select_option', 50)->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('created_at')->default('0000-00-00 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('busfees');
    }
};
