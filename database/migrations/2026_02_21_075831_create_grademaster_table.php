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
        Schema::create('grademaster', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('grading_name')->nullable();
            $table->text('applicable')->nullable();
            $table->text('min_per')->nullable();
            $table->text('max_per')->nullable();
            $table->text('grade')->nullable();
            $table->json('groups')->nullable();
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : Delete');
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
        Schema::dropIfExists('grademaster');
    }
};
