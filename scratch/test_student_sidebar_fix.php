<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$configSidebar = config('sidebar');

function getRoleNameForUserTest($user) {
    if (!$user) return 'Student';
    if (method_exists($user, 'getRoleNames')) {
        $roleName = $user->getRoleNames()->first();
        if (!empty($roleName)) return $roleName;
    }
    if (!empty($user->role) && $user->role !== 'Admin') return $user->role;
    if (isset($user->type) && strtolower(trim($user->type)) !== 'a') return 'Student';
    return 'Student';
}

function getAllowedMenuForUserTest($user) {
    if (!$user) return [];
    $roleName = getRoleNameForUserTest($user);
    if (!$roleName || $roleName === 'Student') return []; // Don't use DB role_menus for Student
    $roleId = \DB::table('roles')->where('name', $roleName)->value('id');
    if (!$roleId) return [];
    $roleMenu = \App\Models\RoleMenu::where('role_id', $roleId)->first();
    return $roleMenu ? json_decode($roleMenu->menu, true) : [];
}

function filterMenuByRoleNameTest($menu, $roleName) {
    $filtered = [];
    foreach ($menu as $item) {
        $roles = $item['roles'] ?? [];
        if (!empty($roles) && !in_array($roleName, $roles)) {
            continue;
        }
        $filteredItem = $item;
        if (!empty($item['children'])) {
            $children = filterMenuByRoleNameTest($item['children'], $roleName);
            if (empty($children)) {
                continue;
            }
            $filteredItem['children'] = $children;
        }
        $filtered[] = $filteredItem;
    }
    return $filtered;
}

// Simulate student user
$mockStudentUser = (object)['id' => 1, 'type' => 's', 'role' => null];

$allowedMenu = getAllowedMenuForUserTest($mockStudentUser);
$menu = !empty($allowedMenu)
    ? filterMenuByAllowed($configSidebar, $allowedMenu)
    : filterMenuByRoleNameTest($configSidebar, getRoleNameForUserTest($mockStudentUser));

echo "Calculated role: " . getRoleNameForUserTest($mockStudentUser) . "\n";
echo "Top level menu titles for student:\n";
foreach ($menu as $m) {
    echo "- " . $m['title'] . "\n";
}
