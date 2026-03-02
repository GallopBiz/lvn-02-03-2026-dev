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
        Schema::create('generate_duechartstatus', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('student_id');
            $table->string('class_name', 10);
            $table->string('sectionname', 50)->nullable();
            $table->string('status', 10);
            $table->integer('amount');
            $table->string('session_name', 50)->nullable();
            $table->json('json_str')->nullable();
            $table->string('is_rte', 20)->nullable();
            $table->timestamp('updated_date')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generate_duechartstatus');
    }
};
