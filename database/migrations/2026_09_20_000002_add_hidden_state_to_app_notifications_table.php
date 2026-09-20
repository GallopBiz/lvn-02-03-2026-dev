<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('is_read');
            $table->timestamp('hidden_at')->nullable()->after('read_at');
            $table->index(['user_id', 'is_hidden']);
        });
    }

    public function down(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_hidden']);
            $table->dropColumn(['is_hidden', 'hidden_at']);
        });
    }
};
