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
        Schema::create('fees_types_master', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('fees_type');
            $table->string('remark')->nullable();
            $table->string('session');
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : Deleted');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fees_types_master');
    }
};
