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
        Schema::create('busattandence', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('DC_name', 200);
            $table->string('Bus_no', 200);
            $table->longText('json_str');
            $table->integer('is_delete')->default(0)->comment('0 none, 1 delete');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('update_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('busattandence');
    }
};
