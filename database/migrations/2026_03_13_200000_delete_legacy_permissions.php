<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete permissions that do NOT contain a dash (legacy, non-unique names)
        DB::table('permissions')->whereRaw("name NOT LIKE '%-%'")->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback for deleted permissions
    }
};
