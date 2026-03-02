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
        Schema::create('vehicel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('callno', 100)->nullable();
            $table->string('vehicelno', 100)->nullable();
            $table->string('vehiceltype', 100)->nullable();
            $table->string('nature', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->date('purchase')->nullable();
            $table->string('capacity', 100)->nullable();
            $table->string('standard', 100)->nullable();
            $table->string('imei', 100)->nullable();
            $table->string('machine', 100)->nullable();
            $table->string('studentrelated', 100)->nullable();
            $table->string('scrapped', 100)->nullable();
            $table->integer('is_delete')->default(0)->comment('0 none : 1 Delete');
            $table->string('rtopaper', 55)->nullable();
            $table->date('validfrom')->nullable();
            $table->date('validto')->nullable();
            $table->string('rtopaper_file', 50)->nullable();
            $table->string('fitnesspaper', 50)->nullable();
            $table->date('fitness_validfrom')->nullable();
            $table->date('fitness_validto')->nullable();
            $table->string('fitnesspaper_file', 50)->nullable();
            $table->string('gps_tracking_url', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicel');
    }
};
