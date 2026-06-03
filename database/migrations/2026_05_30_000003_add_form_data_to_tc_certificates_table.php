<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('dynamic')->table('tc_certificates', function (Blueprint $table) {
            if (!Schema::connection('dynamic')->hasColumn('tc_certificates', 'tc_form_data')) {
                $table->json('tc_form_data')->nullable()->after('remarks');
            }
        });
    }

    public function down()
    {
        Schema::connection('dynamic')->table('tc_certificates', function (Blueprint $table) {
            if (Schema::connection('dynamic')->hasColumn('tc_certificates', 'tc_form_data')) {
                $table->dropColumn('tc_form_data');
            }
        });
    }
};
