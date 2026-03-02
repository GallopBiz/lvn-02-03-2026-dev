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
        Schema::create('initial_leave', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('staff_name')->nullable();
            $table->integer('leave_name')->nullable()->default(0);
            $table->integer('total_allotted')->nullable()->default(0);
            $table->text('assigned_to_employee')->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('	0 none, 1 delete');
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
        Schema::dropIfExists('initial_leave');
    }
};
