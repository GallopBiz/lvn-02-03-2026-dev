<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hrms_leave_reset_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index('created_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hrms_leave_reset_histories');
    }
};
