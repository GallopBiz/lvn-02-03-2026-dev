<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Set all roles and permissions guard_name to 'web'
        DB::table('roles')->update(['guard_name' => 'web']);
        DB::table('permissions')->update(['guard_name' => 'web']);
    }

    public function down()
    {
        // No rollback
    }
};
