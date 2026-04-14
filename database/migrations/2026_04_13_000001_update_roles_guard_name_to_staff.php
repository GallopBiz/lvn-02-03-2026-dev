<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all roles to use 'staff' as guard_name
        DB::table('roles')->update(['guard_name' => 'staff']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, revert to 'web' or previous value if needed
        DB::table('roles')->update(['guard_name' => 'web']);
    }
};
