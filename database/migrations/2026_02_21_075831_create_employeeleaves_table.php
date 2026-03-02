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
        Schema::create('employeeleaves', function (Blueprint $table) {
            $table->integer('Emp_ID', true);
            $table->text('CL_Balance')->nullable();
            $table->text('ML_Balance')->nullable();
            $table->text('EL_Balance')->nullable();
            $table->integer('is_delete')->default(0)->comment('0 for none, 1 for delete');
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
        Schema::dropIfExists('employeeleaves');
    }
};
