<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicel', 'fuel_opening_odometer')) {
            Schema::table('vehicel', function (Blueprint $table) {
                $table->decimal('fuel_opening_odometer', 12, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicel', 'fuel_opening_odometer')) {
            Schema::table('vehicel', function (Blueprint $table) {
                $table->dropColumn('fuel_opening_odometer');
            });
        }
    }
};
