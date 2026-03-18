<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Remove duplicate permissions (keep the one with the lowest id)
        $duplicates = DB::table('permissions')
            ->select('name', DB::raw('MIN(id) as min_id'))
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('permissions')
                ->where('name', $dup->name)
                ->where('id', '!=', $dup->min_id)
                ->delete();
        }

        // Now update all guard_name to 'web'
        DB::table('permissions')->update(['guard_name' => 'web']);
        DB::table('roles')->update(['guard_name' => 'web']);
    }

    public function down()
    {
        // No rollback
    }
};
