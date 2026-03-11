<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MigrateAdminStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch all admin/staff from student_registration table
        $admins = DB::table('student_registration')
            ->whereIn('type', ['admin', 'staff']) // Adjust column name/type as needed
            ->get();

        foreach ($admins as $admin) {
            // Check if already exists in users table
            if (!User::where('email', $admin->email ?? null)->exists()) {
                User::create([
                    'name' => $admin->student_name,
                    'email' => $admin->email ?? $admin->form_number . '@example.com',
                    'password' => $admin->password ?? Hash::make('defaultPassword'),
                    'form_number' => $admin->form_number,
                    // Add other fields as needed
                ]);
            }
        }
    }
}
