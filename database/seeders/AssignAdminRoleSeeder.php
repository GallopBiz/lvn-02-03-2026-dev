<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure permissions exist
        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
        ];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Ensure Admin role exists
        $role = Role::findOrCreate('Admin');
        $role->syncPermissions($permissions);

        // Assign Admin role to all users with student_name = 'Admin' or type = 'admin'
        $admins = User::where('student_name', 'Admin')
            ->orWhere('type', 'admin')
            ->get();
        foreach ($admins as $user) {
            $user->assignRole('Admin');
        }
    }
}
