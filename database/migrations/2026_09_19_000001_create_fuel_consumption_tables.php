<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = Schema::connection('dynamic')->hasTable('vehicel') ? 'dynamic' : null;

        if (!Schema::connection($connection)->hasColumn('vehicel', 'fuel_opening_odometer')) {
            Schema::connection($connection)->table('vehicel', function (Blueprint $table) {
                $table->decimal('fuel_opening_odometer', 12, 2)->nullable();
            });
        }

        if (!Schema::connection($connection)->hasTable('fuel_stations')) {
            Schema::connection($connection)->create('fuel_stations', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::connection($connection)->hasTable('fuel_entries')) {
            Schema::connection($connection)->create('fuel_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vehicle_id');
                $table->unsignedBigInteger('driver_id')->nullable();
                $table->unsignedBigInteger('fuel_station_id');
                $table->date('fuel_date');
                $table->string('fuel_type', 30);
                $table->decimal('quantity', 12, 3);
                $table->string('quantity_unit', 20);
                $table->decimal('rate', 12, 2);
                $table->decimal('total_amount', 14, 2);
                $table->decimal('previous_odometer', 12, 2);
                $table->decimal('current_odometer', 12, 2);
                $table->decimal('distance', 12, 2);
                $table->decimal('efficiency', 12, 3)->nullable();
                $table->string('bill_number', 100)->nullable();
                $table->string('payment_mode', 30)->nullable();
                $table->string('payment_reference', 150)->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['vehicle_id', 'fuel_date']);
                $table->index('fuel_type');
            });
        }
    }

    public function down(): void
    {
        $connection = Schema::connection('dynamic')->hasTable('vehicel') ? 'dynamic' : null;

        Schema::connection($connection)->dropIfExists('fuel_entries');
        Schema::connection($connection)->dropIfExists('fuel_stations');

        if (Schema::connection($connection)->hasColumn('vehicel', 'fuel_opening_odometer')) {
            Schema::connection($connection)->table('vehicel', function (Blueprint $table) {
                $table->dropColumn('fuel_opening_odometer');
            });
        }
    }
};
