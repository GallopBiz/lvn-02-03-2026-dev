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
        Schema::create('subheads', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('class_group')->nullable();
            $table->text('head_name')->nullable();
            $table->text('sub_head_name')->nullable();
            $table->text('display_order')->nullable();
            $table->text('visibility')->nullable();
            $table->text('entry_type_e1')->nullable();
            $table->text('entry_type_e2')->nullable();
            $table->text('entry_type_e3')->nullable();
            $table->text('entry_type_e4')->nullable();
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : Delete');
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
        Schema::dropIfExists('subheads');
    }
};
