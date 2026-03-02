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
        Schema::create('subhead', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('class_group', 200)->nullable();
            $table->string('head_name', 200)->nullable();
            $table->string('sub_head_name', 200)->nullable();
            $table->string('display_order', 200)->nullable();
            $table->string('visibility', 200)->nullable();
            $table->string('e1', 20)->nullable();
            $table->string('e2', 20)->nullable();
            $table->string('e3', 20)->nullable();
            $table->string('e4', 20)->nullable();
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('create_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subhead');
    }
};
