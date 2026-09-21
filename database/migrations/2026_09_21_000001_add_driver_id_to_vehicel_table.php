<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicel', 'driver_id')) {
            Schema::table('vehicel', function (Blueprint $table) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('machine');
                $table->index('driver_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicel', 'driver_id')) {
            Schema::table('vehicel', function (Blueprint $table) {
                $table->dropIndex(['driver_id']);
                $table->dropColumn('driver_id');
            });
        }
    }
};