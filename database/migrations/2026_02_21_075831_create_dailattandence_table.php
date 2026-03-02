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
        Schema::create('dailattandence', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('Teacher_id', 200)->nullable();
            $table->string('section_name', 200)->nullable();
            $table->string('Attandence_date', 200)->nullable();
            $table->string('class_id', 200)->nullable();
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
        Schema::dropIfExists('dailattandence');
    }
};
