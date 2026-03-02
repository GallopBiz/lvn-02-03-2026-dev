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
        Schema::create('groupmaster', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('class_group')->nullable();
            $table->text('primary_group_name')->nullable();
            $table->text('group_name')->nullable();
            $table->text('display_order')->nullable();
            $table->text('health_group')->nullable();
            $table->text('entry_type')->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('0 : Active , 1 : Delete');
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
        Schema::dropIfExists('groupmaster');
    }
};
