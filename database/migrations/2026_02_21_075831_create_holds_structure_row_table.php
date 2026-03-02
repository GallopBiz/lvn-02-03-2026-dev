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
        Schema::create('holds_structure_row', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('fees_date', 155);
            $table->string('due_date', 155);
            $table->string('term', 155);
            $table->string('account_name', 155);
            $table->string('fees', 155);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('holds_structure_row');
    }
};
