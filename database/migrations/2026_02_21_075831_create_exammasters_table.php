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
        Schema::create('exammasters', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('exam_name')->nullable();
            $table->text('exam_type')->nullable();
            $table->text('class_id')->nullable();
            $table->text('stream_id')->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exammasters');
    }
};
