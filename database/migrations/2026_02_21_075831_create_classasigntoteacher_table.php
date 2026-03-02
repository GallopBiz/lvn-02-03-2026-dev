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
        Schema::create('classasigntoteacher', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('Class', 200);
            $table->string('Section', 200);
            $table->string('Teacher_1', 200)->nullable();
            $table->string('Teacher_2', 200)->nullable();
            $table->integer('is_delete')->default(0)->comment('0-none, 1-delete');
            $table->dateTime('create_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
            $table->string('teacher_namee', 50)->nullable();
            $table->string('teacher_name', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classasigntoteacher');
    }
};
