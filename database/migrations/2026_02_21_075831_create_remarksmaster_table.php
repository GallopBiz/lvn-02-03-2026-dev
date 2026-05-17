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
        Schema::create('academic_remarksmaster', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('not_show', 20)->nullable();
            $table->string('remark', 255)->nullable();
            $table->timestamp('created_date')->useCurrent();
            $table->integer('is_delete')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('academic_remarksmaster');
    }
};
