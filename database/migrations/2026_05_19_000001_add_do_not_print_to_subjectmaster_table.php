<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (
            Schema::connection('dynamic')->hasTable('subjectmaster')
            && !Schema::connection('dynamic')->hasColumn('subjectmaster', 'do_not_print_in_main_scholastic_area')
        ) {
            Schema::connection('dynamic')->table('subjectmaster', function (Blueprint $table) {
                $table->boolean('do_not_print_in_main_scholastic_area')->default(false)->after('practical');
            });
        }
    }

    public function down()
    {
        if (
            Schema::connection('dynamic')->hasTable('subjectmaster')
            && Schema::connection('dynamic')->hasColumn('subjectmaster', 'do_not_print_in_main_scholastic_area')
        ) {
            Schema::connection('dynamic')->table('subjectmaster', function (Blueprint $table) {
                $table->dropColumn('do_not_print_in_main_scholastic_area');
            });
        }
    }
};
