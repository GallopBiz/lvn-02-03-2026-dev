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
        Schema::create('subject_combinations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('combination_name')->nullable();
            $table->string('class_id')->nullable();
            $table->text('is_academic_comb')->nullable();
            $table->text('alise_name')->nullable();
            $table->text('streams')->nullable();
            $table->text('combination_type')->nullable()->comment('a-Academic, n-non Academic');
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : delete');
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
        Schema::dropIfExists('subject_combinations');
    }
};
