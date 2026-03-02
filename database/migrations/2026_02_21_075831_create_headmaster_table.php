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
        Schema::create('headmaster', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('class_name')->nullable();
            $table->text('group_name')->nullable();
            $table->text('head_name')->nullable();
            $table->text('display_order')->nullable();
            $table->text('applicable_to')->nullable();
            $table->text('is_elective')->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('headmaster');
    }
};
