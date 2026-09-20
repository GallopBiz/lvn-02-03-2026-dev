<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'insurance_license_no', 'insurance_validfrom', 'insurance_validto',
            'permit_license_no', 'permit_validfrom', 'permit_validto',
            'tax_license_no', 'tax_validfrom', 'tax_validto',
            'puc_license_no', 'puc_validfrom', 'puc_validto',
            'gprs_license_no', 'gprs_validfrom', 'gprs_validto',
        ];

        foreach ($columns as $column) {
            if (!Schema::hasColumn('vehicel', $column)) {
                Schema::table('vehicel', function (Blueprint $table) use ($column) {
                    if (str_ends_with($column, '_validfrom') || str_ends_with($column, '_validto')) {
                        $table->date($column)->nullable();
                    } else {
                        $table->string($column, 100)->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $columns = [
            'insurance_license_no', 'insurance_validfrom', 'insurance_validto',
            'permit_license_no', 'permit_validfrom', 'permit_validto',
            'tax_license_no', 'tax_validfrom', 'tax_validto',
            'puc_license_no', 'puc_validfrom', 'puc_validto',
            'gprs_license_no', 'gprs_validfrom', 'gprs_validto',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('vehicel', $column)) {
                Schema::table('vehicel', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
