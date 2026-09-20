<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 100);
            $table->string('title');
            $table->text('message');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_type', 100)->nullable();
            $table->string('event_key', 191);
            $table->timestamp('created_at')->useCurrent();
            $table->date('expires_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('url')->nullable();

            $table->unique(['user_id', 'event_key']);
            $table->index(['user_id', 'is_read']);
            $table->index(['type', 'entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
