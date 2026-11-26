<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('hrms_leave_types')
            ->where('id', 6)
            ->orWhere('name', 'Leave Without Pay')
            ->update(['name' => 'Leave Application']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('hrms_leave_types')
            ->where('id', 6)
            ->orWhere('name', 'Leave Application')
            ->update(['name' => 'Leave Without Pay']);
    }
};
