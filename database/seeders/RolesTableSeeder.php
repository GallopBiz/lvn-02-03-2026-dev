<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Admin Staff',
            'Academic',
            'Support',
            'External',
            'student',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }
    }
}
