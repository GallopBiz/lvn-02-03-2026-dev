<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $stringColumns = [
            'rtopaper' => 55,
            'rtopaper_file' => 100,
            'fitnesspaper' => 100,
            'fitnesspaper_file' => 100,
            'insurance_license_no' => 100,
            'permit_license_no' => 100,
            'tax_license_no' => 100,
            'puc_license_no' => 100,
            'gprs_license_no' => 100,
            'gps_tracking_url' => 200,
        ];

        $dateColumns = [
            'validfrom', 'validto',
            'fitness_validfrom', 'fitness_validto',
            'insurance_validfrom', 'insurance_validto',
            'permit_validfrom', 'permit_validto',
            'tax_validfrom', 'tax_validto',
            'puc_validfrom', 'puc_validto',
            'gprs_validfrom', 'gprs_validto',
        ];

        foreach ($stringColumns as $column => $length) {
            if (!Schema::hasColumn('vehicel', $column)) {
                Schema::table('vehicel', function (Blueprint $table) use ($column, $length) {
                    $table->string($column, $length)->nullable();
                });
            }
        }

        foreach ($dateColumns as $column) {
            if (!Schema::hasColumn('vehicel', $column)) {
                Schema::table('vehicel', function (Blueprint $table) use ($column) {
                    $table->date($column)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        // Existing document columns are preserved because earlier migrations may own them.
    }
};
