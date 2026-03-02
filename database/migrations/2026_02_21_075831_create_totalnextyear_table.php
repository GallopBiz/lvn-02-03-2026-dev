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
        Schema::create('totalnextyear', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('fees_date', 155);
            $table->string('due_date', 155);
            $table->string('totalnextyear', 155);
            $table->string('account_name', 155);
            $table->string('fees', 155);
            $table->integer('scholar_no')->nullable();
            $table->string('received_type', 20)->nullable();
            $table->string('reference_number', 50)->nullable();
            $table->integer('receipt_number')->nullable();
            $table->integer('is_delete')->default(0)->comment('Active : 0 and Delete : 1');
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
        Schema::dropIfExists('totalnextyear');
    }
};
